import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';
import { TopicStatus } from '@prisma/client';

const createTopicSchema = z.object({
  name: z.string().min(1, 'Nome é obrigatório'),
  description: z.string().optional(),
  blockId: z.string().min(1, 'ID do bloco é obrigatório'),
  status: z.nativeEnum(TopicStatus).default(TopicStatus.PLANNED),
  tags: z.string().optional(),
});

const updateTopicSchema = z.object({
  name: z.string().min(1, 'Nome é obrigatório').optional(),
  description: z.string().optional(),
  blockId: z.string().optional(),
  status: z.nativeEnum(TopicStatus).optional(),
  tags: z.string().optional(),
});

// GET /api/topics - Listar todos os tópicos ou filtrar por bloco
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const blockId = searchParams.get('blockId');
    const status = searchParams.get('status') as TopicStatus | null;
    const id = searchParams.get('id');

    const where: any = {};
    if (blockId) where.blockId = blockId;
    if (status) where.status = status;
    if (id) where.id = id;

    const topics = await prisma.topic.findMany({
      where,
      include: {
        block: {
          select: {
            id: true,
            name: true,
          },
        },
        items: {
          select: {
            id: true,
            kind: true,
            status: true,
          },
        },
        reviews: {
          select: {
            id: true,
            dueAt: true,
            easeAfter: true,
            intervalAfter: true,
          },
          orderBy: {
            dueAt: 'asc',
          },
        },
        _count: {
          select: {
            items: true,
            reviews: true,
            sessions: true,
          },
        },
      },
      orderBy: {
        createdAt: 'asc',
      },
    });

    return NextResponse.json(topics);
  } catch (error) {
    console.error('Erro ao buscar tópicos:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// POST /api/topics - Criar novo tópico
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validatedData = createTopicSchema.parse(body);

    // Verificar se o bloco existe
    const blockExists = await prisma.block.findUnique({
      where: { id: validatedData.blockId },
    });

    if (!blockExists) {
      return NextResponse.json(
        { error: 'Bloco não encontrado' },
        { status: 404 }
      );
    }

    const topic = await prisma.topic.create({
      data: validatedData,
      include: {
        block: {
          select: {
            id: true,
            name: true,
          },
        },
        _count: {
          select: {
            items: true,
            reviews: true,
            sessions: true,
          },
        },
      },
    });

    return NextResponse.json(topic, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao criar tópico:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// PUT /api/topics - Atualizar tópico
export async function PUT(request: NextRequest) {
  try {
    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID do tópico é obrigatório' },
        { status: 400 }
      );
    }

    const validatedData = updateTopicSchema.parse(updateData);

    // Se está mudando o bloco, verificar se existe
    if (validatedData.blockId) {
      const blockExists = await prisma.block.findUnique({
        where: { id: validatedData.blockId },
      });

      if (!blockExists) {
        return NextResponse.json(
          { error: 'Bloco não encontrado' },
          { status: 404 }
        );
      }
    }

    const topic = await prisma.topic.update({
      where: { id },
      data: validatedData,
      include: {
        block: {
          select: {
            id: true,
            name: true,

          },
        },
        _count: {
          select: {
            items: true,
            reviews: true,
            sessions: true,
          },
        },
      },
    });

    return NextResponse.json(topic);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao atualizar tópico:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// DELETE /api/topics - Deletar tópico
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID do tópico é obrigatório' },
        { status: 400 }
      );
    }

    await prisma.topic.delete({
      where: { id },
    });

    return NextResponse.json({ message: 'Tópico deletado com sucesso' });
  } catch (error) {
    console.error('Erro ao deletar tópico:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}