'use client'

import { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { 
  Plus, 
  BookOpen, 
  Edit, 
  Trash2, 
  Eye, 
  Play,
  Search,
  Filter,
  MoreVertical,
  CheckCircle,
  X,
  Clock,
  Target,
  Home,
  TrendingUp,
  Award,
  Calendar,
  RotateCcw,
  Circle
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

interface Topic {
  id: string
  name: string
  status: 'PLANNED' | 'STUDYING' | 'REVIEWED'
}

interface Block {
  id: string
  name: string
  description?: string
  order: number
  topics: Topic[]
}

export default function BlocosPage() {
  const router = useRouter()
  const [blocks, setBlocks] = useState<Block[]>([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [statusFilter, setStatusFilter] = useState<string>('all')
  const [showCreateDialog, setShowCreateDialog] = useState(false)
  const [editingBlock, setEditingBlock] = useState<Block | null>(null)
  const [deletingBlock, setDeletingBlock] = useState<Block | null>(null)
  const [newBlockName, setNewBlockName] = useState('')
  const [newBlockDescription, setNewBlockDescription] = useState('')

  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errors, setErrors] = useState<{name?: string; description?: string}>({})

  useEffect(() => {
    fetchBlocks()
  }, [])

  const validateForm = () => {
    const newErrors: {name?: string; description?: string} = {}
    
    if (!newBlockName.trim()) {
      newErrors.name = 'Nome do bloco é obrigatório'
    } else if (newBlockName.trim().length < 3) {
      newErrors.name = 'Nome deve ter pelo menos 3 caracteres'
    }
    

    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSaveBlock = async () => {
    if (!validateForm()) return
    
    setIsSubmitting(true)
    try {
      const url = editingBlock ? `/api/blocks/${editingBlock.id}` : '/api/blocks'
      const method = editingBlock ? 'PUT' : 'POST'
      
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: newBlockName.trim(),
          description: newBlockDescription.trim() || undefined,
        }),
      })
      
      if (!response.ok) {
        throw new Error('Erro ao salvar bloco')
      }
      
      // Recarregar a lista de blocos
      await fetchBlocks()
      
      // Fechar o modal e limpar o formulário
      setShowCreateDialog(false)
      setEditingBlock(null)
      setNewBlockName('')
      setNewBlockDescription('')

      setErrors({})
    } catch (error) {
      console.error('Erro ao salvar bloco:', error)
      setErrors({ name: 'Erro ao salvar bloco. Tente novamente.' })
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleCloseModal = () => {
    setShowCreateDialog(false)
    setEditingBlock(null)
    setNewBlockName('')
    setNewBlockDescription('')

    setErrors({})
  }

  const fetchBlocks = async () => {
    try {
      const timestamp = new Date().getTime();
      const response = await fetch(`/api/blocks?t=${timestamp}`, {
        cache: 'no-cache',
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      })
      if (response.ok) {
        const data = await response.json()
        setBlocks(data)
      }
    } catch (error) {
      console.error('Erro ao buscar blocos:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-vh-100 bg-light p-3 p-sm-4 p-lg-5">
        <div className="container-fluid">
          <div className="d-flex flex-column gap-4">
            <div className="bg-secondary rounded" style={{height: '2rem', width: '33%'}}></div>
            <div className="row g-4">
              {[...Array(6)].map((_, i) => (
                <div key={i} className="col-12 col-lg-6 col-xl-4">
                  <div className="bg-secondary rounded" style={{height: '12rem'}}></div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-vh-100 bg-light py-4">
      <div className="container-fluid">
        {/* Header */}
        <header className="mb-4">
          <div className="bg-primary rounded-3 p-4 p-md-5 text-white shadow-lg" style={{background: 'linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #8b5cf6 100%)'}}>
            {/* Breadcrumb */}
            <nav aria-label="breadcrumb" className="mb-4">
              <ol className="breadcrumb mb-0">
                <li className="breadcrumb-item">
                  <a href="/" className="text-white text-decoration-none d-flex align-items-center gap-1">
                    <Home className="h-4 w-4" />
                    Dashboard
                  </a>
                </li>
                <li className="breadcrumb-item active text-white" aria-current="page">
                  Blocos de Estudo
                </li>
              </ol>
            </nav>
            
            <div className="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
              <div className="d-flex align-items-center gap-3">
                <BookOpen className="h-8 w-8" />
                <div>
                  <h1 className="h2 mb-1">Blocos de Estudo</h1>
                  <p className="mb-0 opacity-75">Organize seus estudos por blocos temáticos e acompanhe seu progresso de forma eficiente e inteligente.</p>
                </div>
              </div>
              <button 
                onClick={() => setShowCreateDialog(true)}
                className="btn btn-light btn-lg rounded-3 d-flex align-items-center gap-2"
              >
                <Plus className="h-5 w-5" />
                Novo Bloco
              </button>
            </div>
          </div>
        </header>

        {/* Estatísticas */}
        <div className="mb-4">
          <h2 className="h4 mb-3">Visão Geral dos Estudos</h2>
          <div className="row g-4">
            <div className="col-12 col-sm-6 col-lg-3">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body text-center">
                  <div className="d-flex align-items-center justify-content-center mb-3">
                    <div className="p-3 bg-primary bg-opacity-10 rounded-circle">
                      <Target className="h-6 w-6 text-primary" />
                    </div>
                  </div>
                  <h3 className="h2 mb-1">{blocks.length}</h3>
                  <p className="text-muted mb-0">Total de Blocos</p>
                </div>
              </div>
            </div>
            <div className="col-12 col-sm-6 col-lg-3">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body text-center">
                  <div className="d-flex align-items-center justify-content-center mb-3">
                    <div className="p-3 bg-success bg-opacity-10 rounded-circle">
                      <CheckCircle className="h-6 w-6 text-success" />
                    </div>
                  </div>
                  <h3 className="h2 mb-1">{blocks.filter(b => b.topics.every(t => t.status === 'REVIEWED')).length}</h3>
                  <p className="text-muted mb-0">Blocos Concluídos</p>
                </div>
              </div>
            </div>
            <div className="col-12 col-sm-6 col-lg-3">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body text-center">
                  <div className="d-flex align-items-center justify-content-center mb-3">
                    <div className="p-3 bg-warning bg-opacity-10 rounded-circle">
                      <Clock className="h-6 w-6 text-warning" />
                    </div>
                  </div>
                  <h3 className="h2 mb-1">{blocks.filter(b => b.topics.some(t => t.status === 'STUDYING')).length}</h3>
                  <p className="text-muted mb-0">Em Progresso</p>
                </div>
              </div>
            </div>
            <div className="col-12 col-sm-6 col-lg-3">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body text-center">
                  <div className="d-flex align-items-center justify-content-center mb-3">
                    <div className="p-3 bg-secondary bg-opacity-10 rounded-circle">
                      <Circle className="h-6 w-6 text-secondary" />
                    </div>
                  </div>
                  <h3 className="h2 mb-1">{blocks.filter(b => b.topics.every(t => t.status === 'PLANNED')).length}</h3>
                  <p className="text-muted mb-0">Não Iniciados</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Grid de Blocos */}
        <div className="mb-4">
          <h2 className="h4 mb-3">Seus Blocos de Estudo</h2>
          <div className="row g-4">
            {blocks.map((block) => {
              const completedTopics = block.topics.filter(t => t.status === 'REVIEWED').length
              const progress = block.topics.length > 0 ? Math.round((completedTopics / block.topics.length) * 100) : 0
              
              return (
                <div key={block.id} className="col-12 col-lg-6 col-xl-4">
                  <Card className="h-100 border-0 shadow-sm">
                    <CardHeader className="pb-3">
                      <div className="d-flex justify-content-between align-items-start">
                        <div className="d-flex align-items-center gap-2 flex-wrap">
                          {/* Badge do BLOCO */}
                          <Badge className={`${
                            ['Língua Portuguesa', 'Raciocínio Lógico-Matemático', 'Informática', 'Noções de Física', 'Ética no Serviço Público', 'Geopolítica Brasileira', 'Língua Estrangeira'].includes(block.name) ? 'bg-info text-white' :
                            block.name === 'Legislação de Trânsito' ? 'bg-warning text-dark' :
                            'bg-danger text-white'
                          }`}>
                            {['Língua Portuguesa', 'Raciocínio Lógico-Matemático', 'Informática', 'Noções de Física', 'Ética no Serviço Público', 'Geopolítica Brasileira', 'Língua Estrangeira'].includes(block.name) ? 'BLOCO 1' :
                            block.name === 'Legislação de Trânsito' ? 'BLOCO 2' :
                            'BLOCO 3'}
                          </Badge>
                          {/* Badge do Status */}
                          <Badge className={`${
                            progress === 100 ? 'bg-success text-white' : 
                            progress > 0 ? 'bg-primary text-white' : 'bg-secondary text-white'
                          }`}>
                            <BookOpen className="h-3 w-3 me-1" />
                            {progress === 100 ? 'Concluído' : progress > 0 ? 'Em Progresso' : 'Não Iniciado'}
                          </Badge>
                        </div>
                        <DropdownMenu>
                          <DropdownMenuTrigger className="btn btn-light btn-sm p-2 border-0" style={{width: '32px', height: '32px'}}>
                            <MoreVertical className="h-4 w-4" />
                          </DropdownMenuTrigger>
                          <DropdownMenuContent>
                            <DropdownMenuItem onClick={() => {
                              setEditingBlock(block)
                              setNewBlockName(block.name)
                              setNewBlockDescription(block.description || '')
                            }}>
                              <Edit className="h-4 w-4 me-2" />
                              Editar
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                              onClick={() => setDeletingBlock(block)}
                              className="text-danger"
                            >
                              <Trash2 className="h-4 w-4 me-2" />
                              Excluir
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                      <CardTitle className="h5 mb-2">{block.name}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-4">
                      <div className="mb-4">
                        {/* Descrição */}
                        {block.description && (
                          <div className="mb-3">
                            <p className="text-muted small lh-base mb-0">
                              {block.description}
                            </p>
                          </div>
                        )}

                        {/* Barra de Progresso */}
                        <div className="mb-3">
                          <div className="d-flex justify-content-between align-items-center mb-2">
                            <span className="small fw-medium text-body">Progresso</span>
                            <span className="small text-muted">{Math.round(progress)}%</span>
                          </div>
                          <div className="progress" style={{height: '8px'}}>
                            <div 
                              className={`progress-bar transition-all ${
                                progress === 100 ? 'bg-success' : 
                                progress > 0 ? 'bg-primary' : 'bg-secondary'
                              }`}
                              role="progressbar"
                              style={{ width: `${progress}%` }}
                              aria-valuenow={progress}
                              aria-valuemin={0}
                              aria-valuemax={100}
                            />
                          </div>
                        </div>

                        {/* Estatísticas dos Tópicos */}
                        <div className="row g-2 mb-3">
                          <div className="col-4">
                            <div className="text-center p-2 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                              <div className="h5 fw-bold text-primary mb-0">{block.topics.length}</div>
                              <div className="small text-primary">Total</div>
                            </div>
                          </div>
                          <div className="col-4">
                            <div className="text-center p-2 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                              <div className="h5 fw-bold text-success mb-0">
                                {block.topics.filter(t => t.status === 'REVIEWED').length}
                              </div>
                              <div className="small text-success">Concluídos</div>
                            </div>
                          </div>
                          <div className="col-4">
                            <div className="text-center p-2 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25">
                              <div className="h5 fw-bold text-warning mb-0">
                                {block.topics.filter(t => t.status === 'STUDYING').length}
                              </div>
                              <div className="small text-warning">Estudando</div>
                            </div>
                          </div>
                        </div>

                        {/* Tópicos Preview */}
                        {block.topics.length > 0 && (
                          <div className="mb-3">
                            <h6 className="small fw-medium text-body mb-2">Tópicos:</h6>
                            {block.topics.slice(0, 3).map((topic) => (
                              <div key={topic.id} className="d-flex align-items-center gap-2 mb-1">
                                <div className={`rounded-circle ${
                                  topic.status === 'REVIEWED' ? 'bg-success' :
                                  topic.status === 'STUDYING' ? 'bg-primary' : 'bg-secondary'
                                }`} style={{width: '8px', height: '8px'}}></div>
                                <span className="text-muted small text-truncate">{topic.name}</span>
                              </div>
                            ))}
                            {block.topics.length > 3 && (
                              <div className="text-muted" style={{fontSize: '0.75rem'}}>
                                +{block.topics.length - 3} tópicos adicionais
                              </div>
                            )}
                          </div>
                        )}
                        
                        {/* Botões de Ação */}
                        <div className="d-flex gap-2">
                          <button
                            onClick={() => router.push(`/blocos/${block.id}`)}
                            className={`btn flex-fill fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 ${
                              progress === 100 
                                ? 'btn-success' 
                                : progress > 0 
                                  ? 'btn-primary' 
                                  : 'btn-secondary'
                            }`}
                          >
                            <Play className="h-4 w-4" />
                            <span>
                              {progress === 100 ? 'Revisar' : progress > 0 ? 'Continuar' : 'Iniciar'}
                            </span>
                          </button>
                          <button
                            onClick={() => router.push(`/blocos/${block.id}`)}
                            className="btn btn-outline-primary rounded-3 d-flex align-items-center justify-content-center"
                            style={{width: '48px', height: '48px'}}
                          >
                            <Eye className="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              )
            })}
          </div>
        </div>

        {/* Dialog para criar/editar bloco */}
        <Dialog open={showCreateDialog || !!editingBlock} onOpenChange={(open) => {
          if (!open) handleCloseModal()
        }}>
          <DialogContent className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <DialogHeader className="modal-header border-bottom">
                <DialogTitle className="modal-title h4 fw-bold text-primary d-flex align-items-center gap-2">
                  {editingBlock ? (
                    <><Edit className="h-5 w-5" />Editar Bloco</>
                  ) : (
                    <><Plus className="h-5 w-5" />Novo Bloco</>
                  )}
                </DialogTitle>
              </DialogHeader>
              
              <div className="modal-body p-4">
                <form onSubmit={(e) => { e.preventDefault(); handleSaveBlock(); }}>
                  <div className="mb-4">
                    <label className="form-label fw-medium text-dark">
                      Nome do Bloco <span className="text-danger">*</span>
                    </label>
                    <Input
                      value={newBlockName}
                      onChange={(e) => {
                        setNewBlockName(e.target.value)
                        if (errors.name) setErrors(prev => ({ ...prev, name: undefined }))
                      }}
                      placeholder="Ex: Língua Portuguesa, Direito Constitucional..."
                      className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                      disabled={isSubmitting}
                      maxLength={100}
                    />
                    {errors.name && (
                      <div className="invalid-feedback d-block">
                        <small className="text-danger">{errors.name}</small>
                      </div>
                    )}
                    <small className="form-text text-muted">
                      {newBlockName.length}/100 caracteres
                    </small>
                  </div>
                  
                  <div className="mb-4">
                    <label className="form-label fw-medium text-dark">
                      Descrição do Bloco
                    </label>
                    <textarea
                      value={newBlockDescription}
                      onChange={(e) => {
                        setNewBlockDescription(e.target.value)
                        if (errors.description) setErrors(prev => ({ ...prev, description: undefined }))
                      }}
                      placeholder="Descreva brevemente o conteúdo deste bloco de estudo..."
                      className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                      disabled={isSubmitting}
                      maxLength={500}
                      rows={3}
                    />
                    {errors.description && (
                      <div className="invalid-feedback d-block">
                        <small className="text-danger">{errors.description}</small>
                      </div>
                    )}
                    <small className="form-text text-muted">
                      {newBlockDescription.length}/500 caracteres
                    </small>
                  </div>

                </form>
              </div>
              
              <div className="modal-footer border-top bg-light">
                <div className="d-flex gap-2 w-100">
                  <Button 
                    variant="outline" 
                    onClick={handleCloseModal}
                    disabled={isSubmitting}
                    className="btn btn-outline-secondary flex-fill"
                  >
                    <X className="h-4 w-4 me-2" />
                    Cancelar
                  </Button>
                  <Button 
                    onClick={handleSaveBlock}
                    disabled={isSubmitting || !newBlockName.trim()}
                    className={`btn flex-fill fw-bold ${
                      editingBlock ? 'btn-primary' : 'btn-success'
                    }`}
                  >
                    {isSubmitting ? (
                      <>
                        <div className="spinner-border spinner-border-sm me-2" role="status">
                          <span className="visually-hidden">Salvando...</span>
                        </div>
                        Salvando...
                      </>
                    ) : (
                      <>
                        {editingBlock ? (
                          <><CheckCircle className="h-4 w-4 me-2" />Salvar Alterações</>
                        ) : (
                          <><Plus className="h-4 w-4 me-2" />Criar Bloco</>
                        )}
                      </>
                    )}
                  </Button>
                </div>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Dialog de confirmação para excluir */}
        <AlertDialog open={!!deletingBlock} onOpenChange={(open) => {
          if (!open) setDeletingBlock(null)
        }}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Excluir Bloco</AlertDialogTitle>
              <AlertDialogDescription>
                Tem certeza que deseja excluir o bloco "{deletingBlock?.name}"?
                Esta ação não pode ser desfeita e todos os tópicos associados também serão removidos.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancelar</AlertDialogCancel>
              <AlertDialogAction onClick={() => {}} className="bg-red-600 hover:bg-red-700">
                Excluir
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </div>
  )
}
