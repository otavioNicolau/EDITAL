import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';
import { applyReview } from '@/lib/srs';

const createReviewSchema = z.object({
  topicId: z.string().min(1, 'ID do tópico é obrigatório'),
  grade: z.union([z.literal(0), z.literal(1), z.literal(2), z.literal(3)]),
  ease: z.number().min(1.3).default(2.5),
  interval: z.number().min(1).default(1),
  dueAt: z.string().datetime().optional(),
});

const updateReviewSchema = z.object({
  topicId: z.string().optional(),
  grade: z.union([z.literal(0), z.literal(1), z.literal(2), z.literal(3)]).optional(),
  ease: z.number().min(1.3).optional(),
  interval: z.number().min(1).optional(),
  dueAt: z.string().datetime().optional(),
});

// GET /api/reviews - Listar revisões
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const topicId = searchParams.get('topicId');
    const due = searchParams.get('due'); // 'true' para revisões vencidas
    const limit = searchParams.get('limit');

    const where: any = {};
    if (topicId) where.topicId = topicId;
    
    if (due === 'true') {
      where.dueAt = {
        lte: new Date(),
      };
    }

    const reviews = await prisma.review.findMany({
      where,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            status: true,
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
        dueAt: 'asc',
      },
      take: limit ? parseInt(limit) : undefined,
    });

    return NextResponse.json(reviews);
  } catch (error) {
    console.error('Erro ao buscar revisões:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// POST /api/reviews - Criar nova revisão ou processar nota
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validatedData = createReviewSchema.parse(body);

    // Verificar se o tópico existe
    const topic = await prisma.topic.findUnique({
      where: { id: validatedData.topicId },
      include: {
        reviews: {
          orderBy: {
            reviewedAt: 'desc',
          },
          take: 1,
        },
      },
    });

    if (!topic) {
      return NextResponse.json(
        { error: 'Tópico não encontrado' },
        { status: 404 }
      );
    }

    // Pegar a última revisão para usar os valores atuais de ease e interval
    const lastReview = topic.reviews[0];
    const currentEase = lastReview?.easeAfter || 2.5;
    const currentInterval = lastReview?.intervalAfter || 1;

    // Aplicar algoritmo SRS
    const srsResult = applyReview({
      grade: validatedData.grade,
      ease: currentEase,
      interval: currentInterval,
    });

    // Criar nova revisão com os valores calculados
    const reviewData = {
      topicId: validatedData.topicId,
      grade: validatedData.grade,
      easeAfter: srsResult.ease,
      intervalAfter: srsResult.interval,
      dueAt: srsResult.dueAt,
    };

    const review = await prisma.review.create({
      data: reviewData,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            status: true,
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

    // Atualizar status do tópico baseado na nota
    let newStatus = topic.status;
    if (validatedData.grade >= 2) {
      newStatus = 'REVIEWED';
    } else if (topic.status === 'PLANNED') {
      newStatus = 'STUDYING';
    }

    if (newStatus !== topic.status) {
      await prisma.topic.update({
        where: { id: validatedData.topicId },
        data: { status: newStatus },
      });
      
      // Notificar que os blocos foram atualizados
      // Isso será capturado pela página de blocos via localStorage
    }

    return NextResponse.json({
      ...review,
      srsResult,
    }, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao criar revisão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// PUT /api/reviews - Atualizar revisão
export async function PUT(request: NextRequest) {
  try {
    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID da revisão é obrigatório' },
        { status: 400 }
      );
    }

    const validatedData = updateReviewSchema.parse(updateData);

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

    // Converter string de data para Date object se fornecida
    const reviewData: any = { ...validatedData };
    if (validatedData.dueAt) {
      reviewData.dueAt = new Date(validatedData.dueAt);
    }

    const review = await prisma.review.update({
      where: { id },
      data: reviewData,
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            status: true,
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

    return NextResponse.json(review);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao atualizar revisão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// DELETE /api/reviews - Deletar revisão
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID da revisão é obrigatório' },
        { status: 400 }
      );
    }

    await prisma.review.delete({
      where: { id },
    });

    return NextResponse.json({ message: 'Revisão deletada com sucesso' });
  } catch (error) {
    console.error('Erro ao deletar revisão:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}