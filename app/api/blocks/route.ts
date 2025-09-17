import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const createBlockSchema = z.object({
  name: z.string().min(1, 'Nome é obrigatório'),
  description: z.string().optional(),
});

const updateBlockSchema = z.object({
  name: z.string().min(1, 'Nome é obrigatório').optional(),
  description: z.string().optional(),
});

// GET /api/blocks - Listar todos os blocos
export async function GET() {
  try {
    // Força revalidação dos dados do banco
    await prisma.$disconnect();
    await prisma.$connect();
    
    const blocks = await prisma.block.findMany({
      include: {
        topics: {
          select: {
            id: true,
            name: true,
            status: true,
            _count: {
              select: {
                items: true,
                reviews: true,
              },
            },
          },
        },
        _count: {
          select: {
            topics: true,
          },
        },
      },
      orderBy: {
        createdAt: 'asc',
      },
    });

    const response = NextResponse.json(blocks);
    response.headers.set('Cache-Control', 'no-cache, no-store, must-revalidate');
    response.headers.set('Pragma', 'no-cache');
    response.headers.set('Expires', '0');
    return response;
  } catch (error) {
    console.error('Erro ao buscar blocos:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// POST /api/blocks - Criar novo bloco
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validatedData = createBlockSchema.parse(body);

    const block = await prisma.block.create({
      data: validatedData,
      include: {
        topics: true,
        _count: {
          select: {
            topics: true,
          },
        },
      },
    });

    return NextResponse.json(block, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao criar bloco:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// PUT /api/blocks - Atualizar bloco
export async function PUT(request: NextRequest) {
  try {
    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID do bloco é obrigatório' },
        { status: 400 }
      );
    }

    const validatedData = updateBlockSchema.parse(updateData);

    const block = await prisma.block.update({
      where: { id },
      data: validatedData,
      include: {
        topics: true,
        _count: {
          select: {
            topics: true,
          },
        },
      },
    });

    return NextResponse.json(block);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Dados inválidos', details: error.issues },
        { status: 400 }
      );
    }

    console.error('Erro ao atualizar bloco:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}

// DELETE /api/blocks - Deletar bloco
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID do bloco é obrigatório' },
        { status: 400 }
      );
    }

    await prisma.block.delete({
      where: { id },
    });

    return NextResponse.json({ message: 'Bloco deletado com sucesso' });
  } catch (error) {
    console.error('Erro ao deletar bloco:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}