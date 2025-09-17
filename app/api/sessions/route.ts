import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const createSessionSchema = z.object({
  topicId: z.string().min(1, 'ID do tópico é obrigatório'),
  duration: z.number().min(1, 'Duração deve ser maior que 0'),
  startedAt: z.string().datetime().optional(),
  endedAt: z.string().datetime().optional(),
  notes: z.string().optional(),
});

const updateSessionSchema = z.object({
  topicId: z.string().optional(),
  duration: z.number().min(1, 'Duração deve ser maior que 0').optional(),
  startedAt: z.string().datetime().optional(),
  endedAt: z.string().datetime().optional(),
  notes: z.string().optional(),
});

// GET /api/sessions - Listar sessões de estudo
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const topicId = searchParams.get('topicId');
    const limit = searchParams.get('limit');
    const startDate = searchParams.get('startDate');
    const endDate = searchParams.get('endDate');

    const where: any = {};
    if (topicId) where.topicId = topicId;
    
    if (startDate || endDate) {
      where.startedAt = {};
      if (startDate) where.startedAt.gte = new Date(startDate);
      if (endDate) where.startedAt.lte = new Date(endDate);
    }

    const sessions = await prisma.studySession.findMany({
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
        startedAt: 'desc',
      },
      take: limit ? parseInt(limit) : undefined,
    });

    return NextResponse.json(sessions);
  } catch (error) {
    console.error('Erro ao buscar sessões:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// POST /api/sessions - Criar nova sessão de estudo
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validatedData = createSessionSchema.parse(body);

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

    // Converter strings de data para Date objects se fornecidas
    const sessionData: any = {
      ...validatedData,
      minutes: validatedData.duration, // Adicionar campo minutes obrigatório
    };
    
    // Remover o campo duration para evitar conflito
    delete sessionData.duration;

    if (validatedData.startedAt) {
      sessionData.startedAt = new Date(validatedData.startedAt);
    }

    if (validatedData.endedAt) {
      sessionData.endedAt = new Date(validatedData.endedAt);
    }

    const session = await prisma.studySession.create({
      data: sessionData,
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

    // Atualizar status do tópico para STUDYING se ainda não foi iniciado
    if (topicExists.status === 'PLANNED') {
      await prisma.topic.update({
        where: { id: validatedData.topicId },
        data: { status: 'STUDYING' },
      });
      
      // Notificar que os blocos foram atualizados
      // Isso será capturado pela página de blocos via localStorage
    }

    return NextResponse.json(session, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao criar sessão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// PUT /api/sessions - Atualizar sessão de estudo
export async function PUT(request: NextRequest) {
  try {
    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID da sessão é obrigatório' },
        { status: 400 }
      );
    }

    const validatedData = updateSessionSchema.parse(updateData);

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

    // Converter strings de data para Date objects se fornecidas
    const sessionData: any = { ...validatedData };

    // Adicionar campo minutes se duration for fornecido
    if (validatedData.duration) {
      sessionData.minutes = validatedData.duration;
      // Remover o campo duration para evitar conflito
      delete sessionData.duration;
    }

    if (validatedData.startedAt) {
      sessionData.startedAt = new Date(validatedData.startedAt);
    }

    if (validatedData.endedAt) {
      sessionData.endedAt = new Date(validatedData.endedAt);
    }

    const session = await prisma.studySession.update({
      where: { id },
      data: sessionData,
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

    return NextResponse.json(session);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao atualizar sessão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// DELETE /api/sessions - Deletar sessão de estudo
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID da sessão é obrigatório' },
        { status: 400 }
      );
    }

    await prisma.studySession.delete({
      where: { id },
    });

    return NextResponse.json({ message: 'Sessão deletada com sucesso' });
  } catch (error) {
    console.error('Erro ao deletar sessão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}