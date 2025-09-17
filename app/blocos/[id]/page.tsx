'use client'

import { useState, useEffect } from 'react'
import { useRouter, useParams } from 'next/navigation'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { 
  ArrowLeft,
  Plus, 
  Edit, 
  Trash2, 
  Eye, 
  Play,
  Search,
  Filter,
  MoreVertical,
  CheckCircle,
  Clock,
  Target,
  BookOpen
} from 'lucide-react'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { 
  TOPIC_STATUS_LABELS, 
  TOPIC_STATUS_COLORS,
  getTopicStatusLabel,
  getTopicStatusColor
} from '@/lib/constants'

interface Topic {
  id: string
  name: string
  description?: string
  status: 'PLANNED' | 'STUDYING' | 'COMPLETED'
  order: number
  items: Array<{
    id: string
    kind: string
  }>
}

interface Block {
  id: string
  name: string
  description?: string
  order: number
  topics: Topic[]
}

export default function BlockDetailPage() {
  const router = useRouter()
  const params = useParams()
  const blockId = params.id as string
  
  const [block, setBlock] = useState<Block | null>(null)
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [statusFilter, setStatusFilter] = useState<string>('ALL')
  const [showCreateDialog, setShowCreateDialog] = useState(false)
  const [editingTopic, setEditingTopic] = useState<Topic | null>(null)
  const [deletingTopic, setDeletingTopic] = useState<Topic | null>(null)
  const [newTopicName, setNewTopicName] = useState('')
  const [newTopicDescription, setNewTopicDescription] = useState('')
  const [newTopicStatus, setNewTopicStatus] = useState<'PLANNED' | 'STUDYING' | 'COMPLETED'>('PLANNED')

  useEffect(() => {
    if (blockId) {
      fetchBlock()
    }
  }, [blockId])

  const fetchBlock = async () => {
    setLoading(true)
    try {
      const response = await fetch(`/api/blocks/${blockId}`)
      if (response.ok) {
        const data = await response.json()
        setBlock(data)
      } else {
        console.error('Erro ao carregar bloco')
      }
    } catch (error) {
      console.error('Erro ao carregar bloco:', error)
    } finally {
      setLoading(false)
    }
  }

  const createTopic = async () => {
    if (!newTopicName.trim()) return
    
    try {
      const response = await fetch('/api/topics', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: newTopicName,
          description: newTopicDescription,
          status: newTopicStatus,
          blockId: blockId,
          order: (block?.topics.length || 0) + 1
        })
      })
      
      if (response.ok) {
        setNewTopicName('')
        setNewTopicDescription('')
        setNewTopicStatus('PLANNED')
        setShowCreateDialog(false)
        fetchBlock()
      }
    } catch (error) {
      console.error('Erro ao criar tópico:', error)
    }
  }

  const updateTopic = async () => {
    if (!editingTopic || !newTopicName.trim()) return
    
    try {
      const response = await fetch(`/api/topics/${editingTopic.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: newTopicName,
          description: newTopicDescription,
          status: newTopicStatus
        })
      })
      
      if (response.ok) {
        setEditingTopic(null)
        setNewTopicName('')
        setNewTopicDescription('')
        fetchBlock()
      }
    } catch (error) {
      console.error('Erro ao atualizar tópico:', error)
    }
  }

  const deleteTopic = async () => {
    if (!deletingTopic) return
    
    try {
      const response = await fetch(`/api/topics/${deletingTopic.id}`, {
        method: 'DELETE'
      })
      
      if (response.ok) {
        setDeletingTopic(null)
        fetchBlock()
      }
    } catch (error) {
      console.error('Erro ao excluir tópico:', error)
    }
  }

  const getStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'PLANNED': return 'secondary'
      case 'STUDYING': return 'outline'
      case 'COMPLETED': return 'default'
      default: return 'secondary'
    }
  }

  const getBlockProgress = () => {
    if (!block?.topics.length) return 0
    const completed = block.topics.filter(t => t.status === 'COMPLETED').length
    return Math.round((completed / block.topics.length) * 100)
  }

  const filteredTopics = block?.topics.filter(topic => {
    const matchesSearch = topic.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         topic.description?.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesStatus = statusFilter === 'ALL' || topic.status === statusFilter
    return matchesSearch && matchesStatus
  }) || []

  if (loading) {
    return (
      <div className="container-fluid py-4">
        <div className="row">
          <div className="col-12">
            <div className="d-flex align-items-center mb-4">
              <div className="placeholder-glow">
                <span className="placeholder col-3"></span>
              </div>
            </div>
            <div className="card border-0 shadow-sm mb-4">
              <div className="card-body p-4">
                <div className="placeholder-glow">
                  <span className="placeholder col-6 mb-2"></span>
                  <span className="placeholder col-4"></span>
                </div>
              </div>
            </div>
            <div className="row g-3">
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="col-12 col-sm-6 col-lg-4 col-xl-3">
                  <div className="card h-100 shadow-sm border-0">
                    <div className="card-body">
                      <div className="placeholder-glow">
                        <span className="placeholder col-7 mb-2"></span>
                        <span className="placeholder col-4 mb-2"></span>
                        <span className="placeholder col-6"></span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (!block) {
    return (
      <div className="container-fluid py-5">
        <div className="row justify-content-center">
          <div className="col-12 col-md-6 text-center">
            <BookOpen className="text-muted mb-3" style={{width: '4rem', height: '4rem'}} />
            <h3 className="h4 fw-bold text-dark mb-3">Bloco não encontrado</h3>
            <Button onClick={() => router.push('/blocos')} className="btn btn-primary">
              <ArrowLeft className="me-2" style={{width: '1rem', height: '1rem'}} />
              Voltar aos Blocos
            </Button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="container-fluid py-4">
      {/* Breadcrumb */}
      <div className="mb-4">
        <nav aria-label="breadcrumb" className="mb-3">
          <ol className="breadcrumb">
            <li className="breadcrumb-item">
              <Button 
                variant="ghost" 
                onClick={() => router.push('/blocos')}
                className="btn btn-link text-decoration-none p-0"
              >
                <ArrowLeft className="me-1" style={{width: '16px', height: '16px'}} />
                Blocos
              </Button>
            </li>
            <li className="breadcrumb-item active" aria-current="page">{block.name}</li>
          </ol>
        </nav>

        {/* Header do Bloco */}
        <div className="card border-0 shadow-sm mb-4">
          <div className="card-body p-4">
            <div className="row align-items-center">
              <div className="col-12">
                <h1 className="h3 fw-bold text-dark mb-2">{block.name}</h1>
                {block.description && (
                  <p className="text-muted mb-3">{block.description}</p>
                )}
                <div className="progress" style={{height: '8px'}}>
                  <div 
                    className="progress-bar bg-primary" 
                    role="progressbar" 
                    style={{width: `${getBlockProgress()}%`}}
                    aria-valuenow={getBlockProgress()}
                    aria-valuemin={0}
                    aria-valuemax={100}
                  ></div>
                </div>
                <small className="text-muted">{getBlockProgress()}% concluído</small>
              </div>
            </div>
          </div>
        </div>

        {/* Estatísticas */}
        <div className="row g-3 g-lg-4 mb-4">
          <div className="col-12 col-sm-6 col-lg-3">
            <Card className="h-100 shadow-sm border-0">
              <CardContent className="p-4">
                <div className="d-flex align-items-center mb-3">
                  <div className="p-3 bg-primary rounded-3 shadow-sm me-3">
                    <Target className="text-white" style={{width: '24px', height: '24px'}} />
                  </div>
                  <div>
                    <h3 className="h2 fw-bold mb-0">{block.topics.length}</h3>
                    <p className="text-muted small mb-0">Total de Tópicos</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
          
          <div className="col-12 col-sm-6 col-lg-3">
            <Card className="h-100 shadow-sm border-0">
              <CardContent className="p-4">
                <div className="d-flex align-items-center mb-3">
                  <div className="p-3 bg-secondary rounded-3 shadow-sm me-3">
                    <BookOpen className="text-white" style={{width: '24px', height: '24px'}} />
                  </div>
                  <div>
                    <h3 className="h2 fw-bold mb-0">
                      {block.topics.filter(t => t.status === 'PLANNED').length}
                    </h3>
                    <p className="text-muted small mb-0">Planejados</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
          
          <div className="col-12 col-sm-6 col-lg-3">
            <Card className="h-100 shadow-sm border-0">
              <CardContent className="p-4">
                <div className="d-flex align-items-center mb-3">
                  <div className="p-3 bg-success rounded-3 shadow-sm me-3">
                    <CheckCircle className="text-white" style={{width: '24px', height: '24px'}} />
                  </div>
                  <div>
                    <h3 className="h2 fw-bold mb-0">
                      {block.topics.filter(t => t.status === 'COMPLETED').length}
                    </h3>
                    <p className="text-muted small mb-0">Concluídos</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
          
          <div className="col-12 col-sm-6 col-lg-3">
            <Card className="h-100 shadow-sm border-0">
              <CardContent className="p-4">
                <div className="d-flex align-items-center mb-3">
                  <div className="p-3 bg-warning rounded-3 shadow-sm me-3">
                    <Clock className="text-white" style={{width: '24px', height: '24px'}} />
                  </div>
                  <div>
                    <h3 className="h2 fw-bold mb-0">
                      {block.topics.filter(t => t.status === 'STUDYING').length}
                    </h3>
                    <p className="text-muted small mb-0">Em Estudo</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Filtros e Busca */}
        <div className="row align-items-center mb-4">
          <div className="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
            <div className="position-relative">
              <Search className="position-absolute top-50 translate-middle-y text-muted ms-3" style={{width: '16px', height: '16px'}} />
              <Input
                type="text"
                placeholder="Buscar tópicos..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="form-control ps-5"
              />
            </div>
          </div>
          
          <div className="col-12 col-md-6 col-lg-8">
            <div className="d-flex gap-2 justify-content-md-end">
              <DropdownMenu>
                <DropdownMenuTrigger className="btn btn-outline-secondary d-flex align-items-center gap-2">
                  <Filter style={{width: '16px', height: '16px'}} />
                  Status
                  {statusFilter !== 'ALL' && (
                    <span className="badge bg-primary ms-1">
                      {statusFilter === 'PLANNED' ? 'Planejados' :
                       statusFilter === 'STUDYING' ? 'Em Estudo' : 'Concluídos'}
                    </span>
                  )}
                </DropdownMenuTrigger>
                <DropdownMenuContent>
                  <DropdownMenuItem onClick={() => setStatusFilter('ALL')}>
                    Todos
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => setStatusFilter('PLANNED')}>
                    Planejados
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => setStatusFilter('STUDYING')}>
                    Em Estudo
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => setStatusFilter('COMPLETED')}>
                    Concluídos
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
              
              <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
                <DialogTrigger asChild>
                  <Button className="btn btn-primary d-flex align-items-center gap-2">
                    <Plus style={{width: '16px', height: '16px'}} />
                    Novo Tópico
                  </Button>
                </DialogTrigger>
                <DialogContent className="modal-dialog modal-dialog-centered">
                  <div className="modal-content">
                    <DialogHeader className="modal-header">
                      <DialogTitle className="modal-title h5">Criar Novo Tópico</DialogTitle>
                    </DialogHeader>
                    <div className="modal-body">
                      <div className="mb-3">
                        <label className="form-label">Nome do Tópico</label>
                        <Input
                          value={newTopicName}
                          onChange={(e) => setNewTopicName(e.target.value)}
                          placeholder="Ex: Princípios Constitucionais"
                          className="form-control"
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Descrição (opcional)</label>
                        <Input
                          value={newTopicDescription}
                          onChange={(e) => setNewTopicDescription(e.target.value)}
                          placeholder="Descrição do tópico"
                          className="form-control"
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Status</label>
                        <Select value={newTopicStatus} onValueChange={(value: any) => setNewTopicStatus(value)}>
                          <SelectTrigger className="form-select">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="PLANNED">{TOPIC_STATUS_LABELS.PLANNED}</SelectItem>
                            <SelectItem value="STUDYING">{TOPIC_STATUS_LABELS.STUDYING}</SelectItem>
                            <SelectItem value="COMPLETED">{TOPIC_STATUS_LABELS.COMPLETED}</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="modal-footer">
                      <Button onClick={createTopic} className="btn btn-primary">
                        Criar Tópico
                      </Button>
                      <Button 
                        variant="outline" 
                        onClick={() => setShowCreateDialog(false)}
                        className="btn btn-secondary"
                      >
                        Cancelar
                      </Button>
                    </div>
                  </div>
                </DialogContent>
              </Dialog>
            </div>
          </div>
        </div>

        {/* Lista de Tópicos */}
        <div className="row g-3 g-lg-4">
          {filteredTopics.map((topic) => (
            <div key={topic.id} className="col-12 col-sm-6 col-lg-4 col-xl-3">
              <Card className="h-100 shadow-sm border-0">
                <CardHeader className="pb-3">
                  <div className="d-flex justify-content-between align-items-start mb-3">
                    <div className="flex-grow-1">
                      <CardTitle className="h6 fw-semibold text-dark mb-2">
                        {topic.name}
                      </CardTitle>
                      {topic.description && (
                        <p className="text-muted small mb-0">{topic.description}</p>
                      )}
                    </div>
                    
                    <DropdownMenu>
                      <DropdownMenuTrigger className="btn btn-sm btn-outline-secondary border-0">
                        <MoreVertical style={{width: '16px', height: '16px'}} />
                      </DropdownMenuTrigger>
                      <DropdownMenuContent>
                        <DropdownMenuItem 
                          onClick={() => {
                            setEditingTopic(topic)
                            setNewTopicName(topic.name)
                            setNewTopicDescription(topic.description || '')
                            setNewTopicStatus(topic.status)
                          }}
                          className="dropdown-item"
                        >
                          <Edit className="me-2" style={{width: '16px', height: '16px'}} />
                          Editar
                        </DropdownMenuItem>
                        <DropdownMenuItem 
                          onClick={() => setDeletingTopic(topic)}
                          className="dropdown-item text-danger"
                        >
                          <Trash2 className="me-2" style={{width: '16px', height: '16px'}} />
                          Excluir
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                  
                  <div className="d-flex gap-2 flex-wrap">
                    <Badge 
                      variant={getStatusBadgeVariant(topic.status)}
                      className="badge"
                    >
                      {getTopicStatusLabel(topic.status)}
                    </Badge>
                    <Badge variant="outline" className="badge bg-light text-dark">
                      {topic.items?.length || 0} itens
                    </Badge>
                  </div>
                </CardHeader>
                
                <CardContent className="pt-0">
                  <div className="d-grid gap-2">
                    <Button 
                      variant="outline" 
                      size="sm" 
                      className="btn btn-outline-primary btn-sm"
                      onClick={() => router.push(`/topicos/${topic.id}`)}
                    >
                      <Eye className="me-1" style={{width: '16px', height: '16px'}} />
                      Ver Detalhes
                    </Button>
                    <Button 
                      size="sm" 
                      className="btn btn-primary btn-sm"
                      onClick={() => router.push(`/estudo?topic=${topic.id}`)}
                    >
                      <Play className="me-1" style={{width: '16px', height: '16px'}} />
                      Estudar
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          ))}
        </div>

        {/* Estados Vazios */}
        {filteredTopics.length === 0 && block.topics.length === 0 && (
          <div className="container-fluid py-5">
            <div className="row justify-content-center">
              <div className="col-12 col-md-8 col-lg-6">
                <div className="text-center">
                  <div className="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style={{width: '6rem', height: '6rem'}}>
                    <BookOpen className="text-primary" style={{width: '3rem', height: '3rem'}} />
                  </div>
                  <h3 className="h2 fw-bold text-dark mb-3">
                    Comece sua jornada de estudos
                  </h3>
                  <p className="text-muted mb-4 fs-6 lh-base">
                    Este bloco ainda não possui tópicos. Crie seu primeiro tópico para começar a organizar seus estudos de forma eficiente.
                  </p>
                  <div className="d-grid gap-3 d-md-flex justify-content-md-center">
                    <Button 
                      onClick={() => setShowCreateDialog(true)}
                      className="btn btn-primary btn-lg px-4 py-2"
                    >
                      <Plus className="me-2" style={{width: '1.25rem', height: '1.25rem'}} />
                      Criar Primeiro Tópico
                    </Button>
                  </div>
                  <p className="small text-muted mt-3">
                    Organize seus estudos por temas e acompanhe seu progresso
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {filteredTopics.length === 0 && block.topics.length > 0 && (
          <div className="container-fluid py-5">
            <div className="row justify-content-center">
              <div className="col-12 col-md-8 col-lg-6">
                <div className="text-center">
                  <div className="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style={{width: '6rem', height: '6rem'}}>
                    <Search className="text-muted" style={{width: '3rem', height: '3rem'}} />
                  </div>
                  <h3 className="h4 fw-bold text-dark mb-3">
                    Nenhum resultado encontrado
                  </h3>
                  <p className="text-muted mb-4 fs-6 lh-base">
                    Não encontramos tópicos que correspondam aos seus filtros atuais. Tente ajustar os critérios de busca.
                  </p>
                  <div className="d-grid gap-2 d-sm-flex justify-content-sm-center mb-3">
                    <Button 
                      variant="outline" 
                      onClick={() => {
                        setSearchTerm('')
                        setStatusFilter('ALL')
                      }}
                      className="btn btn-outline-secondary"
                    >
                      Limpar Filtros
                    </Button>
                    <Button 
                      onClick={() => setShowCreateDialog(true)}
                      className="btn btn-primary"
                    >
                      <Plus className="me-2" style={{width: '1rem', height: '1rem'}} />
                      Novo Tópico
                    </Button>
                  </div>
                  <p className="small text-muted">
                    Total de {block.topics.length} tópicos disponíveis
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Dialog de Edição */}
        <Dialog open={!!editingTopic} onOpenChange={() => setEditingTopic(null)}>
          <DialogContent className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <DialogHeader className="modal-header">
                <DialogTitle className="modal-title h5">Editar Tópico</DialogTitle>
              </DialogHeader>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">Nome do Tópico</label>
                  <Input
                    value={newTopicName}
                    onChange={(e) => setNewTopicName(e.target.value)}
                    placeholder="Nome do tópico"
                    className="form-control"
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Descrição (opcional)</label>
                  <Input
                    value={newTopicDescription}
                    onChange={(e) => setNewTopicDescription(e.target.value)}
                    placeholder="Descrição do tópico"
                    className="form-control"
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Status</label>
                  <Select value={newTopicStatus} onValueChange={(value: any) => setNewTopicStatus(value)}>
                    <SelectTrigger className="form-select">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="PLANNED">{TOPIC_STATUS_LABELS.PLANNED}</SelectItem>
                      <SelectItem value="STUDYING">{TOPIC_STATUS_LABELS.STUDYING}</SelectItem>
                      <SelectItem value="COMPLETED">{TOPIC_STATUS_LABELS.COMPLETED}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="modal-footer">
                <Button onClick={updateTopic} className="btn btn-primary">
                  Salvar Alterações
                </Button>
                <Button 
                  variant="outline" 
                  onClick={() => setEditingTopic(null)}
                  className="btn btn-secondary"
                >
                  Cancelar
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Dialog de Confirmação de Exclusão */}
        <AlertDialog open={!!deletingTopic} onOpenChange={() => setDeletingTopic(null)}>
          <AlertDialogContent className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <AlertDialogHeader className="modal-header">
                <AlertDialogTitle className="modal-title h5 text-danger">Confirmar Exclusão</AlertDialogTitle>
                <AlertDialogDescription className="text-muted">
                  Tem certeza que deseja excluir o tópico "{deletingTopic?.name}"? 
                  Esta ação não pode ser desfeita e todos os itens de estudo associados também serão removidos.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <div className="modal-footer">
                <AlertDialogCancel className="btn btn-secondary">Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={deleteTopic} className="btn btn-danger">
                  Excluir
                </AlertDialogAction>
              </div>
            </div>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </div>
  )
}