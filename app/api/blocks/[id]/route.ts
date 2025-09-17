import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const block = await prisma.block.findUnique({
      where: {
        id
      },
      include: {
        topics: {
          include: {
            items: {
              select: {
                id: true,
                kind: true
              }
            }
          },
          orderBy: {
            createdAt: 'asc'
          }
        }
      }
    })

    if (!block) {
      return NextResponse.json(
        { error: 'Bloco não encontrado' },
        { status: 404 }
      )
    }

    return NextResponse.json(block)
  } catch (error) {
    console.error('Erro ao buscar bloco:', error)
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
    const { name } = body

    if (!name?.trim()) {
      return NextResponse.json(
        { error: 'Nome é obrigatório' },
        { status: 400 }
      )
    }

    const block = await prisma.block.update({
      where: {
        id
      },
      data: {
        name: name.trim()
      },
      include: {
        topics: {
          include: {
            items: {
              select: {
                id: true,
                kind: true
              }
            }
          },
          orderBy: {
            createdAt: 'asc'
          }
        }
      }
    })

    return NextResponse.json(block)
  } catch (error) {
    console.error('Erro ao atualizar bloco:', error)
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
    // Verificar se o bloco existe
    const existingBlock = await prisma.block.findUnique({
      where: {
        id
      },
      include: {
        topics: {
          include: {
            items: true
          }
        }
      }
    })

    if (!existingBlock) {
      return NextResponse.json(
        { error: 'Bloco não encontrado' },
        { status: 404 }
      )
    }

    // Deletar em cascata: studyItems -> topics -> block
    for (const topic of existingBlock.topics) {
      // Deletar todos os itens de estudo do tópico
      await prisma.studyItem.deleteMany({
        where: {
          topicId: topic.id
        }
      })
    }

    // Deletar todos os tópicos do bloco
    await prisma.topic.deleteMany({
      where: {
        blockId: id
      }
    })

    // Deletar o bloco
    await prisma.block.delete({
      where: {
        id
      }
    })

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error('Erro ao deletar bloco:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}