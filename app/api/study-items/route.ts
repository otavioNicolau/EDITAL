import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';
import { ItemKind, ItemStatus } from '@prisma/client';

const createStudyItemSchema = z.object({
  title: z.string().min(1, 'Título é obrigatório'),
  notes: z.string().min(1, 'Conteúdo é obrigatório'),
  kind: z.nativeEnum(ItemKind),
  topicId: z.string().min(1, 'ID do tópico é obrigatório'),
  status: z.nativeEnum(ItemStatus).default(ItemStatus.TO_STUDY),
  url: z.string().url().optional().or(z.literal('')),
  metadata: z.string().optional(),
});

const updateStudyItemSchema = z.object({
  title: z.string().min(1, 'Título é obrigatório').optional(),
  notes: z.string().optional(),
  kind: z.nativeEnum(ItemKind).optional(),
  topicId: z.string().optional(),
  status: z.nativeEnum(ItemStatus).optional(),
  url: z.string().url().optional().or(z.literal('')),
  metadata: z.string().optional(),
});

// GET /api/study-items - Listar itens de estudo
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const topicId = searchParams.get('topicId');
    const kind = searchParams.get('kind') as ItemKind | null;
    const status = searchParams.get('status') as ItemStatus | null;
    const due = searchParams.get('due') === 'true';

    const where: any = {};
    if (topicId) where.topicId = topicId;
    if (kind) where.kind = kind;
    if (status) where.status = status;
    
    if (due) {
      const now = new Date();
      where.dueAt = {
        lte: now
      };
    }

    const studyItems = await prisma.studyItem.findMany({
      where,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            block: {
              select: {
                id: true,
                name: true,

              },
            },
          },
        },
      },
      orderBy: {
        createdAt: 'desc',
      },
    });

    // Mapear 'notes' do banco para 'content' do frontend
    const mappedStudyItems = studyItems.map(item => ({
      ...item,
      content: item.notes,
      notes: undefined
    }));

    return NextResponse.json(mappedStudyItems);
  } catch (error) {
    console.error('Erro ao buscar itens de estudo:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// POST /api/study-items - Criar novo item de estudo
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    // Mapear 'content' do frontend para 'notes' do banco
    const mappedBody = {
      ...body,
      notes: body.content,
    };
    delete mappedBody.content;
    
    const validatedData = createStudyItemSchema.parse(mappedBody);

    // Verificar se o tópico existe
    const topicExists = await prisma.topic.findUnique({
      where: { id: validatedData.topicId },
    });

    if (!topicExists) {
      return NextResponse.json(
        { error: 'Tópico não encontrado' },
        { status: 404 }
      );
    }

    const studyItem = await prisma.studyItem.create({
      data: validatedData,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            block: {
              select: {
                id: true,
                name: true,

              },
            },
          },
        },
      },
    });

    // Mapear 'notes' do banco para 'content' do frontend
    const mappedStudyItem = {
      ...studyItem,
      content: studyItem.notes,
      notes: undefined
    };

    return NextResponse.json(mappedStudyItem, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao criar item de estudo:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// PUT /api/study-items - Atualizar item de estudo
export async function PUT(request: NextRequest) {
  try {
    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID do item é obrigatório' },
        { status: 400 }
      );
    }

    // Mapear 'content' do frontend para 'notes' do banco
    const mappedUpdateData = {
      ...updateData,
    };
    if (updateData.content !== undefined) {
      mappedUpdateData.notes = updateData.content;
      delete mappedUpdateData.content;
    }

    const validatedData = updateStudyItemSchema.parse(mappedUpdateData);

    // Se está mudando o tópico, verificar se existe
    if (validatedData.topicId) {
      const topicExists = await prisma.topic.findUnique({
        where: { id: validatedData.topicId },
      });

      if (!topicExists) {
        return NextResponse.json(
          { error: 'Tópico não encontrado' },
          { status: 404 }
        );
      }
    }

    const studyItem = await prisma.studyItem.update({
      where: { id },
      data: validatedData,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            block: {
              select: {
                id: true,
                name: true,

              },
            },
          },
        },
      },
    });

    // Mapear 'notes' do banco para 'content' do frontend
    const mappedStudyItem = {
      ...studyItem,
      content: studyItem.notes,
      notes: undefined
    };

    return NextResponse.json(mappedStudyItem);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao atualizar item de estudo:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// DELETE /api/study-items - Deletar item de estudo
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID do item é obrigatório' },
        { status: 400 }
      );
    }

    await prisma.studyItem.delete({
      where: { id },
    });

    return NextResponse.json({ message: 'Item de estudo deletado com sucesso' });
  } catch (error) {
    console.error('Erro ao deletar item de estudo:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}