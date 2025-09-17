'use client'

import { useState, useEffect, Suspense } from 'react'
import { useRouter, useSearchParams } from 'next/navigation'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { 
  BookOpen, 
  Clock, 
  Target, 
  TrendingUp, 
  Calendar, 
  Award,
  Play,
  Plus,
  RefreshCw,
  CheckCircle,
  AlertCircle
} from 'lucide-react'

interface Metrics {
  overview: {
    totalBlocks: number
    totalTopics: number
    totalStudyItems: number
    itemsDueToday: number
    totalHoursStudied: number
    successRate: number
    studyStreak: number
  }
  progress: {
    topicsByStatus: Record<string, number>
  }
  studyActivity: {
    sessionsByDay: Record<string, { count: number; totalMinutes: number }>
    totalSessions: number
    averageSessionLength: number
  }
  reviews: {
    total: number
    successRate: number
    gradeDistribution: Record<number, number>
  }
  upcomingReviews: Array<{
    id: string
    content: string
    dueAt: string
    topic: string
    block: string
    ease: number
  }>
}

function DashboardContent() {
  const router = useRouter()
  const searchParams = useSearchParams()
  const [metrics, setMetrics] = useState<Metrics | null>(null)
  const [loading, setLoading] = useState(true)
  const [showSuccess, setShowSuccess] = useState(false)

  useEffect(() => {
    fetchMetrics()
    
    // Verificar se voltou de uma sessão de revisão
    if (searchParams.get('reviews-completed') === 'true') {
      setShowSuccess(true)
      setTimeout(() => setShowSuccess(false), 5000)
    }
  }, [])

  const fetchMetrics = async () => {
    try {
      const response = await fetch('/api/metrics')
      if (response.ok) {
        const data = await response.json()
        setMetrics(data)
      }
    } catch (error) {
      console.error('Erro ao buscar métricas:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-vh-100 bg-light d-flex align-items-center justify-content-center">
        <div className="text-center">
          <Clock className="mx-auto mb-4 text-primary" size={32} style={{animation: 'spin 1s linear infinite'}} />
          <p className="text-muted">Carregando dashboard...</p>
        </div>
      </div>
    )
  }

  const completedTopics = metrics?.progress.topicsByStatus.COMPLETED || 0
  const totalTopics = metrics?.overview.totalTopics || 0
  const progressPercentage = totalTopics > 0 ? Math.round((completedTopics / totalTopics) * 100) : 0

  return (
    <div className="min-vh-100 bg-light p-4">
      <div className="container-fluid">
        {/* Header */}
        <div className="d-flex align-items-center justify-content-between mb-4">
          <div>
            <h1 className="display-4 fw-bold text-dark">Dashboard</h1>
            <p className="text-muted">
              {new Date().toLocaleDateString('pt-BR', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
              })}
            </p>
          </div>
          
          {showSuccess && (
            <div className="alert alert-success d-flex align-items-center" role="alert">
              <CheckCircle className="me-2" size={20} />
              Revisões concluídas com sucesso!
            </div>
          )}
        </div>

        {/* Métricas Principais */}
        <div className="row g-4 mb-4">
          <div className="col-12 col-md-6 col-lg-3">
            <Card>
              <CardContent className="p-4">
                <div className="d-flex align-items-center justify-content-between">
                  <div>
                    <p className="small fw-medium text-muted">Progresso Geral</p>
                    <p className="h2 fw-bold text-primary">{progressPercentage}%</p>
                    <p className="small text-muted">{completedTopics} de {totalTopics} tópicos</p>
                  </div>
                  <Target className="text-primary" size={32} />
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="col-12 col-md-6 col-lg-3">
            <Card>
              <CardContent className="p-4">
                <div className="d-flex align-items-center justify-content-between">
                  <div>
                    <p className="small fw-medium text-muted">Horas Estudadas</p>
                    <p className="h2 fw-bold text-success">{metrics?.overview.totalHoursStudied || 0}h</p>
                    <p className="small text-muted">últimos 30 dias</p>
                  </div>
                  <Clock className="text-success" size={32} />
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="col-12 col-md-6 col-lg-3">
            <Card>
              <CardContent className="p-4">
                <div className="d-flex align-items-center justify-content-between">
                  <div>
                    <p className="small fw-medium text-muted">Revisões Hoje</p>
                    <p className="h2 fw-bold text-warning">{metrics?.overview.itemsDueToday || 0}</p>
                    <p className="small text-muted">itens pendentes</p>
                  </div>
                  <RefreshCw className="text-warning" size={32} />
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="col-12 col-md-6 col-lg-3">
            <Card>
              <CardContent className="p-4">
                <div className="d-flex align-items-center justify-content-between">
                  <div>
                    <p className="small fw-medium text-muted">Sequência</p>
                    <p className="h2 fw-bold" style={{color: '#6f42c1'}}>{metrics?.overview.studyStreak || 0}</p>
                    <p className="small text-muted">dias consecutivos</p>
                  </div>
                  <Award style={{color: '#6f42c1'}} size={32} />
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <div className="row g-4">
          {/* Próximas Revisões */}
          <div className="col-12 col-lg-8">
            <Card>
            <CardHeader>
              <CardTitle className="d-flex align-items-center gap-2">
                <Calendar className="h-5 w-5" />
                Próximas Revisões
              </CardTitle>
            </CardHeader>
            <CardContent>
              {metrics?.upcomingReviews && metrics.upcomingReviews.length > 0 ? (
                <div className="d-flex flex-column gap-3">
                  {metrics.upcomingReviews.slice(0, 5).map((item) => (
                    <div key={item.id} className="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                      <div className="flex-grow-1">
                        <p className="fw-medium small">{item.topic}</p>
                        <p className="text-muted" style={{fontSize: '0.75rem'}}>{item.block}</p>
                        <p className="text-muted mt-1" style={{fontSize: '0.75rem'}}>{item.content}</p>
                      </div>
                      <div className="text-end">
                        <Badge variant="outline" className="small">
                          {new Date(item.dueAt).toLocaleDateString('pt-BR')}
                        </Badge>
                        <p className="text-muted mt-1" style={{fontSize: '0.75rem'}}>Facilidade: {item.ease.toFixed(1)}</p>
                      </div>
                    </div>
                  ))}
                  
                  {metrics.upcomingReviews.length > 5 && (
                    <p className="text-center small text-muted">
                      +{metrics.upcomingReviews.length - 5} mais itens
                    </p>
                  )}
                </div>
              ) : (
                <div className="text-center py-5 text-muted">
                  <CheckCircle className="h-12 w-12 mx-auto mb-4 text-success" />
                  <p>Nenhuma revisão pendente!</p>
                  <p className="small">Você está em dia com seus estudos.</p>
                </div>
              )}
            </CardContent>
            </Card>
          </div>

          {/* Estatísticas de Performance */}
          <div className="col-12 col-lg-4">
            <Card>
            <CardHeader>
              <CardTitle className="d-flex align-items-center gap-2">
                <TrendingUp className="h-5 w-5" />
                Performance
              </CardTitle>
            </CardHeader>
            <CardContent className="d-flex flex-column gap-4">
              <div>
                <div className="d-flex justify-content-between small mb-2">
                  <span>Taxa de Acerto</span>
                  <span className="fw-medium">{metrics?.overview.successRate || 0}%</span>
                </div>
                <Progress value={metrics?.overview.successRate || 0} className="h-2" />
              </div>
              
              <div className="row g-4 text-center">
                <div className="col-6">
                  <p className="h4 fw-bold text-primary">{metrics?.studyActivity.totalSessions || 0}</p>
                  <p className="text-muted" style={{fontSize: '0.75rem'}}>Sessões (30d)</p>
                </div>
                <div className="col-6">
                  <p className="h4 fw-bold text-success">{metrics?.studyActivity.averageSessionLength || 0}min</p>
                  <p className="text-muted" style={{fontSize: '0.75rem'}}>Média/Sessão</p>
                </div>
              </div>
              
              <div>
                <p className="small fw-medium mb-2">Reviews Realizadas</p>
                <p className="h4 fw-bold" style={{color: '#6f42c1'}}>{metrics?.reviews.total || 0}</p>
                <p className="text-muted" style={{fontSize: '0.75rem'}}>últimos 30 dias</p>
              </div>
            </CardContent>
            </Card>
          </div>
        </div>

        {/* Ações Rápidas */}
        <div className="row g-4">
          <div className="col-12 col-md-6 col-lg-3">
            <Button 
              onClick={() => router.push('/blocos')}
              className="w-100 text-start justify-content-start"
              style={{height: '4rem'}}
              variant="outline"
            >
              <div className="d-flex align-items-center gap-3">
                <Plus className="h-6 w-6 text-primary" />
                <div>
                  <p className="fw-medium mb-0">Gerenciar Blocos</p>
                  <p className="text-muted mb-0" style={{fontSize: '0.75rem'}}>Criar e organizar</p>
                </div>
              </div>
            </Button>
          </div>
          
          <div className="col-12 col-md-6 col-lg-3">
            <Button 
              onClick={() => router.push('/estudo')}
              className="w-100 text-start justify-content-start"
              style={{height: '4rem'}}
              variant="outline"
            >
              <div className="d-flex align-items-center gap-3">
                <Play className="h-6 w-6 text-success" />
                <div>
                  <p className="fw-medium mb-0">Estudar</p>
                  <p className="text-muted mb-0" style={{fontSize: '0.75rem'}}>Iniciar Pomodoro</p>
                </div>
              </div>
            </Button>
          </div>
          
          <div className="col-12 col-md-6 col-lg-3">
            <Button 
              onClick={() => router.push('/revisao')}
              className="w-100 text-start justify-content-start"
              style={{height: '4rem'}}
              variant="outline"
              disabled={!metrics?.overview.itemsDueToday}
            >
              <div className="d-flex align-items-center gap-3">
                <RefreshCw className="h-6 w-6 text-warning" />
                <div>
                  <p className="fw-medium mb-0">Revisar</p>
                  <p className="text-muted mb-0" style={{fontSize: '0.75rem'}}>
                    {metrics?.overview.itemsDueToday || 0} itens hoje
                  </p>
                </div>
              </div>
            </Button>
          </div>
          
          <div className="col-12 col-md-6 col-lg-3">
            <Button 
              onClick={() => router.push('/itens')}
              className="w-100 text-start justify-content-start"
              style={{height: '4rem'}}
              variant="outline"
            >
              <div className="d-flex align-items-center gap-3">
                <BookOpen className="h-6 w-6" style={{color: '#6f42c1'}} />
                <div>
                  <p className="fw-medium mb-0">Itens de Estudo</p>
                  <p className="text-muted mb-0" style={{fontSize: '0.75rem'}}>Resumos, questões, leis</p>
                </div>
              </div>
            </Button>
          </div>
          
          <div className="col-12 col-md-6 col-lg-3">
            <Button 
              onClick={fetchMetrics}
              className="w-100 text-start justify-content-start"
              style={{height: '4rem'}}
              variant="outline"
            >
              <div className="d-flex align-items-center gap-3">
                <AlertCircle className="h-6 w-6 text-secondary" />
                <div>
                  <p className="fw-medium mb-0">Atualizar</p>
                  <p className="text-muted mb-0" style={{fontSize: '0.75rem'}}>Recarregar dados</p>
                </div>
              </div>
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default function Dashboard() {
  return (
    <Suspense fallback={<div>Carregando...</div>}>
      <DashboardContent />
    </Suspense>
  )
}
