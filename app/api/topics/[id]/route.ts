import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const topic = await prisma.topic.findUnique({
      where: {
        id
      },
      include: {
        block: {
          select: {
            id: true,
            name: true,
            
          }
        },
        items: {
          orderBy: {
            createdAt: 'asc'
          }
        },
        _count: {
          select: {
            items: true,
            reviews: true,
            sessions: true
          }
        }
      }
    })

    if (!topic) {
      return NextResponse.json(
        { error: 'Tópico não encontrado' },
        { status: 404 }
      )
    }

    return NextResponse.json(topic)
  } catch (error) {
    console.error('Erro ao buscar tópico:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}

export async function PUT(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const body = await request.json()
    const { name, description, status } = body

    if (!name?.trim()) {
      return NextResponse.json(
        { error: 'Nome é obrigatório' },
        { status: 400 }
      )
    }

    const topic = await prisma.topic.update({
      where: {
        id
      },
      data: {
        name: name.trim(),
        status: status || undefined
      },
      include: {
        block: {
          select: {
            id: true,
            name: true,

          }
        },
        items: {
          orderBy: {
            createdAt: 'asc'
          }
        },
        _count: {
          select: {
            items: true,
            reviews: true,
            sessions: true
          }
        }
      }
    })

    return NextResponse.json(topic)
  } catch (error) {
    console.error('Erro ao atualizar tópico:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}

export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    // Verificar se o tópico existe
    const existingTopic = await prisma.topic.findUnique({
      where: {
        id
      },
      include: {
        items: true
      }
    })

    if (!existingTopic) {
      return NextResponse.json(
        { error: 'Tópico não encontrado' },
        { status: 404 }
      )
    }

    // Deletar todos os itens de estudo do tópico
    await prisma.studyItem.deleteMany({
      where: {
        topicId: id
      }
    })

    // Deletar o tópico
    await prisma.topic.delete({
      where: {
        id
      }
    })

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error('Erro ao deletar tópico:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}