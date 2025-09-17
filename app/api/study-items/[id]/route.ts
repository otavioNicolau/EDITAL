import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const studyItem = await prisma.studyItem.findUnique({
      where: {
        id
      },
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            block: {
              select: {
                id: true,
                name: true
              }
            }
          }
        }
      }
    })

    if (!studyItem) {
      return NextResponse.json(
        { error: 'Item de estudo não encontrado' },
        { status: 404 }
      )
    }

    return NextResponse.json(studyItem)
  } catch (error) {
    console.error('Erro ao buscar item de estudo:', error)
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
    const { title, content, kind, status, url, metadata } = body

    if (!title?.trim()) {
      return NextResponse.json(
        { error: 'Título é obrigatório' },
        { status: 400 }
      )
    }

    if (!content?.trim()) {
      return NextResponse.json(
        { error: 'Conteúdo é obrigatório' },
        { status: 400 }
      )
    }

    const validKinds = ['SUMMARY', 'QUESTION', 'LAW', 'VIDEO']
    if (kind && !validKinds.includes(kind)) {
      return NextResponse.json(
        { error: 'Tipo inválido' },
        { status: 400 }
      )
    }

    const validStatuses = ['PENDING', 'COMPLETED', 'REVIEWING']
    if (status && !validStatuses.includes(status)) {
      return NextResponse.json(
        { error: 'Status inválido' },
        { status: 400 }
      )
    }

    const studyItem = await prisma.studyItem.update({
      where: {
        id
      },
      data: {
        title: title.trim(),
        notes: content?.trim() || null,
        kind: kind || undefined,
        status: status || undefined,
        url: url?.trim() || null,
        tags: metadata || null
      },
      include: {
        topic: {
          select: {
            id: true,
            name: true,
            block: {
              select: {
                id: true,
                name: true
              }
            }
          }
        }
      }
    })

    return NextResponse.json(studyItem)
  } catch (error) {
    console.error('Erro ao atualizar item de estudo:', error)
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
    // Verificar se o item existe
    const existingItem = await prisma.studyItem.findUnique({
      where: {
        id
      }
    })

    if (!existingItem) {
      return NextResponse.json(
        { error: 'Item de estudo não encontrado' },
        { status: 404 }
      )
    }

    // Deletar o item de estudo (reviews são relacionadas ao tópico, não ao item)
    await prisma.studyItem.delete({
      where: {
        id
      }
    })

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error('Erro ao deletar item de estudo:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}