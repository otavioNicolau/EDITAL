'use client';

import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { ArrowLeft, Plus, Edit, Trash2, Play } from 'lucide-react';
import { ItemKind, ItemStatus } from '@prisma/client';
import { 
  KIND_ICONS, 
  KIND_LABELS, 
  STATUS_LABELS, 
  STATUS_COLORS,
  getKindIcon,
  getKindLabel,
  getStatusLabel,
  getStatusColor
} from '@/lib/constants';

interface StudyItem {
  id: string;
  title: string;
  content: string;
  kind: ItemKind;
  status: ItemStatus;
  url?: string;
  metadata?: string;
  createdAt: string;
  updatedAt: string;
}

interface Topic {
  id: string;
  name: string;
  description?: string;
  status: string;
  tags?: string;
  block: {
    id: string;
    name: string;
    color?: string;
  };
  studyItems: StudyItem[];
  _count: {
    items: number;
    reviews: number;
    sessions: number;
  };
}

// Constantes movidas para @/lib/constants

export default function TopicDetailPage() {
  const params = useParams();
  const router = useRouter();
  const [topic, setTopic] = useState<Topic | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedKind, setSelectedKind] = useState<ItemKind | 'ALL'>('ALL');
  const [selectedStatus, setSelectedStatus] = useState<ItemStatus | 'ALL'>('ALL');
  const [showAddForm, setShowAddForm] = useState(false);
  const [newItem, setNewItem] = useState({
    title: '',
    content: '',
    kind: 'SUMMARY' as ItemKind,
    url: '',
  });

  useEffect(() => {
    fetchTopic();
  }, [params.id]);

  const fetchTopic = async () => {
    try {
      setLoading(true);
      const response = await fetch(`/api/topics?id=${params.id}`);
      if (!response.ok) throw new Error('Erro ao carregar tópico');
      
      const topics = await response.json();
      const topicData = topics.find((t: Topic) => t.id === params.id);
      
      if (!topicData) {
        throw new Error('Tópico não encontrado');
      }
      
      // Buscar itens de estudo do tópico
      const itemsResponse = await fetch(`/api/study-items?topicId=${params.id}`);
      if (itemsResponse.ok) {
        const items = await itemsResponse.json();
        topicData.studyItems = items;
      }
      
      setTopic(topicData);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro desconhecido');
    } finally {
      setLoading(false);
    }
  };

  const handleAddItem = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await fetch('/api/study-items', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ...newItem,
          topicId: params.id,
        }),
      });

      if (!response.ok) throw new Error('Erro ao criar item');

      setNewItem({ title: '', content: '', kind: 'SUMMARY', url: '' });
      setShowAddForm(false);
      fetchTopic(); // Recarregar dados
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Erro ao criar item');
    }
  };

  const handleDeleteItem = async (itemId: string) => {
    if (!confirm('Tem certeza que deseja deletar este item?')) return;

    try {
      const response = await fetch(`/api/study-items?id=${itemId}`, {
        method: 'DELETE',
      });

      if (!response.ok) throw new Error('Erro ao deletar item');

      fetchTopic(); // Recarregar dados
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Erro ao deletar item');
    }
  };

  const handleStartStudy = () => {
    router.push(`/estudo?topicId=${params.id}`);
  };

  const handleStartReview = () => {
    router.push(`/revisao?topicId=${params.id}`);
  };

  const filteredItems = topic?.studyItems.filter(item => {
    const kindMatch = selectedKind === 'ALL' || item.kind === selectedKind;
    const statusMatch = selectedStatus === 'ALL' || item.status === selectedStatus;
    return kindMatch && statusMatch;
  }) || [];

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Carregando tópico...</p>
        </div>
      </div>
    );
  }

  if (error || !topic) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <p className="text-red-600 mb-4">{error || 'Tópico não encontrado'}</p>
          <button
            onClick={() => router.back()}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Voltar
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-vh-100 bg-light">
      {/* Header */}
      <div className="bg-white shadow-sm border-bottom">
        <div className="container-fluid">
          <div className="row align-items-center py-3">
            <div className="col-12 col-lg-8">
              <div className="d-flex align-items-center">
                <button
                  onClick={() => router.back()}
                  className="btn btn-outline-secondary me-3 d-flex align-items-center justify-content-center"
                  style={{ width: '40px', height: '40px' }}
                >
                  <ArrowLeft className="h-5 w-5" />
                </button>
                <div>
                  <h1 className="h4 fw-semibold text-dark mb-1">{topic.name}</h1>
                  <p className="text-muted small mb-0">{topic.block.name}</p>
                </div>
              </div>
            </div>
            <div className="col-12 col-lg-4 mt-3 mt-lg-0">
              <div className="d-flex flex-column flex-lg-row gap-2 justify-content-lg-end">
                <button
                  onClick={handleStartStudy}
                  className="btn btn-primary d-flex align-items-center justify-content-center gap-2"
                >
                  <Play className="h-4 w-4" />
                  <span>Estudar</span>
                </button>
                <button
                  onClick={handleStartReview}
                  className="btn btn-success"
                >
                  Revisar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="container-fluid px-3 px-md-4 py-4">
        {/* Stats */}
        <div className="row g-3 g-md-4 mb-4">
          <div className="col-6 col-lg-3">
            <div className="card h-100 border-0 shadow-sm">
              <div className="card-body text-center">
                <div className="h2 fw-bold text-primary mb-1">{topic._count.items}</div>
                <div className="small text-muted">Itens de Estudo</div>
              </div>
            </div>
          </div>
          <div className="col-6 col-lg-3">
            <div className="card h-100 border-0 shadow-sm">
              <div className="card-body text-center">
                <div className="h2 fw-bold text-success mb-1">{topic._count.sessions}</div>
                <div className="small text-muted">Sessões</div>
              </div>
            </div>
          </div>
          <div className="col-6 col-lg-3">
            <div className="card h-100 border-0 shadow-sm">
              <div className="card-body text-center">
                <div className="h2 fw-bold" style={{ color: '#6f42c1' }}>{topic._count.reviews}</div>
                <div className="small text-muted">Revisões</div>
              </div>
            </div>
          </div>
          <div className="col-6 col-lg-3">
            <div className="card h-100 border-0 shadow-sm">
              <div className="card-body text-center">
                <div className={`h2 fw-bold mb-1 ${
                  topic.status === 'COMPLETED' ? 'text-success' :
                  topic.status === 'IN_PROGRESS' ? 'text-warning' : 'text-muted'
                }`}>
                  {topic.status === 'COMPLETED' ? '100%' :
                   topic.status === 'IN_PROGRESS' ? '50%' : '0%'}
                </div>
                <div className="small text-muted">Progresso</div>
              </div>
            </div>
          </div>
        </div>

        {/* Filters and Add Button */}
        <div className="card border-0 shadow-sm mb-4">
          <div className="card-body">
            <div className="row align-items-center g-3">
              <div className="col-12 col-md-8">
                <div className="row g-3">
                  <div className="col-12 col-sm-6">
                    <select
                      value={selectedKind}
                      onChange={(e) => setSelectedKind(e.target.value as ItemKind | 'ALL')}
                      className="form-select"
                    >
                      <option value="ALL">Todos os tipos</option>
                      {Object.entries(KIND_LABELS).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                      ))}
                    </select>
                  </div>
                  <div className="col-12 col-sm-6">
                    <select
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value as ItemStatus | 'ALL')}
                      className="form-select"
                    >
                      <option value="ALL">Todos os status</option>
                      {Object.entries(STATUS_LABELS).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>
              <div className="col-12 col-md-4">
                <div className="d-grid d-md-flex justify-content-md-end">
                  <button
                    onClick={() => setShowAddForm(true)}
                    className="btn btn-primary d-flex align-items-center justify-content-center gap-2"
                  >
                    <Plus className="h-4 w-4" />
                    <span>Adicionar Item</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Add Form */}
        {showAddForm && (
          <div className="card border-0 shadow-sm mb-4">
            <div className="card-header bg-primary text-white">
              <h5 className="card-title mb-0">Adicionar Item de Estudo</h5>
            </div>
            <div className="card-body">
              <form onSubmit={handleAddItem}>
                <div className="row g-3 mb-3">
                  <div className="col-12 col-md-6">
                    <label className="form-label fw-medium">
                      Título
                    </label>
                    <input
                      type="text"
                      value={newItem.title}
                      onChange={(e) => setNewItem({ ...newItem, title: e.target.value })}
                      className="form-control"
                      required
                      placeholder="Digite o título do item"
                    />
                  </div>
                  <div className="col-12 col-md-6">
                    <label className="form-label fw-medium">
                      Tipo
                    </label>
                    <select
                      value={newItem.kind}
                      onChange={(e) => setNewItem({ ...newItem, kind: e.target.value as ItemKind })}
                      className="form-select"
                    >
                      {Object.entries(KIND_LABELS).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                      ))}
                    </select>
                  </div>
                </div>
                <div className="mb-3">
                  <label className="form-label fw-medium">
                    Conteúdo
                  </label>
                  <textarea
                    value={newItem.content}
                    onChange={(e) => setNewItem({ ...newItem, content: e.target.value })}
                    rows={4}
                    className="form-control"
                    required
                    placeholder="Descreva o conteúdo do item de estudo"
                  />
                </div>
                {newItem.kind === 'VIDEO' && (
                  <div className="mb-3">
                    <label className="form-label fw-medium">
                      URL do Vídeo
                    </label>
                    <input
                      type="url"
                      value={newItem.url}
                      onChange={(e) => setNewItem({ ...newItem, url: e.target.value })}
                      className="form-control"
                      placeholder="https://exemplo.com/video"
                    />
                  </div>
                )}
                <div className="d-flex gap-2 flex-column flex-sm-row">
                  <button
                    type="submit"
                    className="btn btn-primary"
                  >
                    Adicionar
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowAddForm(false)}
                    className="btn btn-secondary"
                  >
                    Cancelar
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Study Items */}
        <div className="row g-3">
          {filteredItems.length === 0 ? (
            <div className="col-12">
              <div className="card border-0 shadow-sm text-center">
                <div className="card-body py-5">
                  <p className="text-muted mb-3">Nenhum item de estudo encontrado.</p>
                  <button
                    onClick={() => setShowAddForm(true)}
                    className="btn btn-primary"
                  >
                    Adicionar Primeiro Item
                  </button>
                </div>
              </div>
            </div>
          ) : (
            filteredItems.map((item) => {
              const IconComponent = getKindIcon(item.kind);
              return (
                <div key={item.id} className="col-12">
                  <div className="card border-0 shadow-sm h-100">
                    <div className="card-body">
                      <div className="row align-items-start">
                        <div className="col-auto">
                          <div className="p-2 bg-primary bg-opacity-10 rounded">
                            <IconComponent className="h-5 w-5 text-primary" />
                          </div>
                        </div>
                        <div className="col">
                          <div className="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h5 className="card-title mb-0">{item.title}</h5>
                            <span className={`badge ${getStatusColor(item.status).replace('bg-', 'bg-').replace('text-', 'text-')}`}>
                              {getStatusLabel(item.status)}
                            </span>
                            <span className="badge bg-light text-dark">
                              {getKindLabel(item.kind)}
                            </span>
                          </div>
                          <p className="card-text text-muted mb-3">{item.content}</p>
                          {item.url && (
                            <a
                              href={item.url}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="btn btn-outline-primary btn-sm"
                            >
                              Ver recurso →
                            </a>
                          )}
                        </div>
                        <div className="col-auto">
                          <div className="btn-group" role="group">
                            <button 
                              className="btn btn-outline-secondary btn-sm"
                              title="Editar"
                            >
                              <Edit className="h-4 w-4" />
                            </button>
                            <button
                              onClick={() => handleDeleteItem(item.id)}
                              className="btn btn-outline-danger btn-sm"
                              title="Excluir"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </div>
    </div>
  );
}