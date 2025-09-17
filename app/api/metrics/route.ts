import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  try {
    // Estatísticas gerais
    const totalBlocks = await prisma.block.count()
    const totalTopics = await prisma.topic.count()
    const totalStudyItems = await prisma.studyItem.count()
    
    // Progresso dos tópicos
    const topicsByStatus = await prisma.topic.groupBy({
      by: ['status'],
      _count: {
        id: true
      }
    })
    
    // Sessões de estudo (últimos 30 dias)
    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
    
    const recentSessions = await prisma.studySession.findMany({
      where: {
        startedAt: {
          gte: thirtyDaysAgo
        }
      },
      select: {
        minutes: true,
        startedAt: true
      }
    })
    
    // Total de horas estudadas (últimos 30 dias)
    const totalMinutesStudied = recentSessions.reduce((total, session) => {
      return total + (session.minutes || 0)
    }, 0)
    const totalHoursStudied = Math.round((totalMinutesStudied / 60) * 10) / 10
    
    // Sessões por dia (últimos 7 dias)
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7)
    
    const weekSessions = await prisma.studySession.findMany({
      where: {
        startedAt: {
          gte: sevenDaysAgo
        }
      },
      select: {
        startedAt: true,
        minutes: true
      }
    })
    
    // Agrupar sessões por dia
    const sessionsByDay = weekSessions.reduce((acc, session) => {
      const date = session.startedAt.toISOString().split('T')[0]
      if (!acc[date]) {
        acc[date] = { count: 0, totalMinutes: 0 }
      }
      acc[date].count++
      acc[date].totalMinutes += session.minutes || 0
      return acc
    }, {} as Record<string, { count: number; totalMinutes: number }>)
    
    // Itens para revisão hoje
    const today = new Date()
    today.setHours(23, 59, 59, 999)
    
    const itemsDueToday = await prisma.review.count({
      where: {
        dueAt: {
          lte: today
        }
      }
    })
    
    // Próximas revisões (próximos 7 dias)
    const nextWeek = new Date()
    nextWeek.setDate(nextWeek.getDate() + 7)
    
    const upcomingReviews = await prisma.review.findMany({
      where: {
        dueAt: {
          gte: new Date(),
          lte: nextWeek
        }
      },
      include: {
        topic: {
          include: {
            block: true
          }
        }
      },
      orderBy: {
        dueAt: 'asc'
      },
      take: 10
    })
    
    // Reviews realizadas (últimos 30 dias)
    const recentReviews = await prisma.review.findMany({
      where: {
        reviewedAt: {
          gte: thirtyDaysAgo
        }
      },
      select: {
        grade: true,
        reviewedAt: true
      }
    })
    
    // Taxa de acerto (notas >= 3)
    const successfulReviews = recentReviews.filter(review => review.grade >= 3).length
    const successRate = recentReviews.length > 0 
      ? Math.round((successfulReviews / recentReviews.length) * 100)
      : 0
    
    // Distribuição de notas
    const gradeDistribution = recentReviews.reduce((acc, review) => {
      acc[review.grade] = (acc[review.grade] || 0) + 1
      return acc
    }, {} as Record<number, number>)
    
    // Streak de estudo (dias consecutivos)
    const studyStreak = await calculateStudyStreak()
    
    return NextResponse.json({
      overview: {
        totalBlocks,
        totalTopics,
        totalStudyItems,
        itemsDueToday,
        totalHoursStudied,
        successRate,
        studyStreak
      },
      progress: {
        topicsByStatus: topicsByStatus.reduce((acc, item) => {
          acc[item.status] = item._count.id
          return acc
        }, {} as Record<string, number>)
      },
      studyActivity: {
        sessionsByDay,
        totalSessions: recentSessions.length,
        averageSessionLength: recentSessions.length > 0 
          ? Math.round(totalMinutesStudied / recentSessions.length)
          : 0
      },
      reviews: {
        total: recentReviews.length,
        successRate,
        gradeDistribution
      },
      upcomingReviews: upcomingReviews.map(item => ({
        id: item.id,
        dueAt: item.dueAt,
        topic: item.topic.name,
        block: item.topic.block.name,
        grade: item.grade,
        easeAfter: item.easeAfter
      }))
    })
  } catch (error) {
    console.error('Erro ao buscar métricas:', error)
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    )
  }
}

// Função auxiliar para calcular streak de estudo
async function calculateStudyStreak(): Promise<number> {
  try {
    const sessions = await prisma.studySession.findMany({
      select: {
        startedAt: true
      },
      orderBy: {
        startedAt: 'desc'
      }
    })
    
    if (sessions.length === 0) return 0
    
    let streak = 0
    let currentDate = new Date()
    currentDate.setHours(0, 0, 0, 0)
    
    // Agrupar sessões por data
    const sessionDates = new Set(
      sessions.map(session => {
        const date = new Date(session.startedAt)
        date.setHours(0, 0, 0, 0)
        return date.getTime()
      })
    )
    
    // Verificar se estudou hoje
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    let checkDate = sessionDates.has(today.getTime()) ? today : new Date(today.getTime() - 24 * 60 * 60 * 1000)
    
    // Contar dias consecutivos
    while (sessionDates.has(checkDate.getTime())) {
      streak++
      checkDate = new Date(checkDate.getTime() - 24 * 60 * 60 * 1000)
    }
    
    return streak
  } catch (error) {
    console.error('Erro ao calcular streak:', error)
    return 0
  }
}