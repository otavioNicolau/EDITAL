'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye,
  ExternalLink,
  BookOpen
} from 'lucide-react';
import { ItemKind, ItemStatus } from '@prisma/client';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
  topic: {
    id: string;
    name: string;
    block: {
      id: string;
      name: string;
      color?: string;
    };
  };
}

interface Topic {
  id: string;
  name: string;
  block: {
    id: string;
    name: string;
  };
}

// Constantes movidas para @/lib/constants

export default function StudyItemsPage() {
  const router = useRouter();
  const [studyItems, setStudyItems] = useState<StudyItem[]>([]);
  const [topics, setTopics] = useState<Topic[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [kindFilter, setKindFilter] = useState<string>('all');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [topicFilter, setTopicFilter] = useState<string>('all');
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [editingItem, setEditingItem] = useState<StudyItem | null>(null);
  const [deletingItem, setDeletingItem] = useState<StudyItem | null>(null);
  const [viewingItem, setViewingItem] = useState<StudyItem | null>(null);
  
  // Form states
  const [formData, setFormData] = useState({
    title: '',
    content: '',
    kind: 'SUMMARY' as ItemKind,
    status: 'TO_STUDY' as ItemStatus,
    topicId: '',
    url: '',
    metadata: ''
  });

  useEffect(() => {
    fetchStudyItems();
    fetchTopics();
  }, []);

  const fetchStudyItems = async () => {
    try {
      const response = await fetch('/api/study-items');
      if (response.ok) {
        const data = await response.json();
        setStudyItems(data);
      }
    } catch (error) {
      console.error('Erro ao buscar itens de estudo:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchTopics = async () => {
    try {
      const response = await fetch('/api/topics');
      if (response.ok) {
        const data = await response.json();
        setTopics(data);
      }
    } catch (error) {
      console.error('Erro ao buscar tópicos:', error);
    }
  };

  const createItem = async () => {
    if (!formData.title || !formData.content || !formData.topicId) return;

    try {
      const response = await fetch('/api/study-items', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      if (response.ok) {
        await fetchStudyItems();
        setShowCreateDialog(false);
        resetForm();
      }
    } catch (error) {
      console.error('Erro ao criar item:', error);
    }
  };

  const updateItem = async () => {
    if (!editingItem || !formData.title || !formData.content) return;

    try {
      const response = await fetch('/api/study-items', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: editingItem.id, ...formData })
      });

      if (response.ok) {
        await fetchStudyItems();
        setEditingItem(null);
        resetForm();
      }
    } catch (error) {
      console.error('Erro ao atualizar item:', error);
    }
  };

  const deleteItem = async () => {
    if (!deletingItem) return;

    try {
      const response = await fetch(`/api/study-items?id=${deletingItem.id}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        await fetchStudyItems();
        setDeletingItem(null);
      }
    } catch (error) {
      console.error('Erro ao deletar item:', error);
    }
  };

  const resetForm = () => {
    setFormData({
      title: '',
      content: '',
      kind: 'SUMMARY',
      status: 'TO_STUDY',
      topicId: '',
      url: '',
      metadata: ''
    });
  };

  const openEditDialog = (item: StudyItem) => {
    setEditingItem(item);
    setFormData({
      title: item.title,
      content: item.content,
      kind: item.kind,
      status: item.status,
      topicId: item.topic.id,
      url: item.url || '',
      metadata: item.metadata || ''
    });
  };

  const filteredItems = studyItems.filter(item => {
    const searchMatch = item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                       item.content.toLowerCase().includes(searchTerm.toLowerCase()) ||
                       item.topic.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                       item.topic.block.name.toLowerCase().includes(searchTerm.toLowerCase());
    
    const kindMatch = kindFilter === 'all' || item.kind === kindFilter;
    const statusMatch = statusFilter === 'all' || item.status === statusFilter;
    const topicMatch = topicFilter === 'all' || item.topic.id === topicFilter;
    
    return searchMatch && kindMatch && statusMatch && topicMatch;
  });



  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <BookOpen className="h-8 w-8 animate-pulse mx-auto mb-4 text-blue-600" />
          <p className="text-gray-600">Carregando itens de estudo...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      <div className="max-w-7xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">📝 Itens de Estudo</h1>
            <p className="text-gray-600">Gerencie resumos, questões, leis e vídeos</p>
          </div>
          
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger className="btn d-inline-flex align-items-center justify-content-center gap-2 text-nowrap btn-primary">
              <Plus className="h-4 w-4" />
              Novo Item
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>Criar Novo Item de Estudo</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium">Título</label>
                    <Input
                      value={formData.title}
                      onChange={(e) => setFormData(prev => ({ ...prev, title: e.target.value }))}
                      placeholder="Título do item"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium">Tópico</label>
                    <Select value={formData.topicId} onValueChange={(value) => setFormData(prev => ({ ...prev, topicId: value }))}>
                      <SelectTrigger>
                        <SelectValue placeholder="Selecione um tópico" />
                      </SelectTrigger>
                      <SelectContent>
                        {topics.map(topic => (
                          <SelectItem key={topic.id} value={topic.id}>
                            {topic.block.name} - {topic.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium">Tipo</label>
                    <Select value={formData.kind} onValueChange={(value) => setFormData(prev => ({ ...prev, kind: value as ItemKind }))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="SUMMARY">📖 Resumo</SelectItem>
                        <SelectItem value="QUESTION">❓ Questão</SelectItem>
                        <SelectItem value="LAW">⚖️ Lei</SelectItem>
                        <SelectItem value="VIDEO">🎥 Vídeo</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <label className="text-sm font-medium">Status</label>
                    <Select value={formData.status} onValueChange={(value) => setFormData(prev => ({ ...prev, status: value as ItemStatus }))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="TO_STUDY">⏳ Para Estudar</SelectItem>
                        <SelectItem value="IN_PROGRESS">🔄 Em Progresso</SelectItem>
                        <SelectItem value="DONE">✅ Concluído</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                
                <div>
                  <label className="text-sm font-medium">Conteúdo</label>
                  <Textarea
                    value={formData.content}
                    onChange={(e) => setFormData(prev => ({ ...prev, content: e.target.value }))}
                    placeholder="Conteúdo do item de estudo"
                    rows={4}
                  />
                </div>
                
                <div>
                  <label className="text-sm font-medium">URL (opcional)</label>
                  <Input
                    value={formData.url}
                    onChange={(e) => setFormData(prev => ({ ...prev, url: e.target.value }))}
                    placeholder="https://..."
                  />
                </div>
                
                <div className="flex gap-2">
                  <Button onClick={createItem} className="flex-1">
                    Criar Item
                  </Button>
                  <Button 
                    variant="outline" 
                    onClick={() => {
                      setShowCreateDialog(false);
                      resetForm();
                    }}
                  >
                    Cancelar
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        {/* Filtros e Busca */}
        <div className="flex flex-col lg:flex-row gap-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              placeholder="Buscar itens..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>
          
          <div className="flex gap-2">
            <DropdownMenu>
              <DropdownMenuTrigger className="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <Filter className="h-4 w-4" />
                Tipo
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem onClick={() => setKindFilter('all')}>Todos</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setKindFilter('SUMMARY')}>📖 Resumos</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setKindFilter('QUESTION')}>❓ Questões</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setKindFilter('LAW')}>⚖️ Leis</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setKindFilter('VIDEO')}>🎥 Vídeos</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
            
            <DropdownMenu>
              <DropdownMenuTrigger className="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <Filter className="h-4 w-4" />
                Status
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem onClick={() => setStatusFilter('all')}>Todos</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setStatusFilter('PENDING')}>⏳ Pendentes</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setStatusFilter('COMPLETED')}>✅ Concluídos</DropdownMenuItem>
                <DropdownMenuItem onClick={() => setStatusFilter('REVIEWING')}>🔄 Revisando</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
            
            <DropdownMenu>
              <DropdownMenuTrigger className="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <Filter className="h-4 w-4" />
                Tópico
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem onClick={() => setTopicFilter('all')}>Todos</DropdownMenuItem>
                {topics.map(topic => (
                  <DropdownMenuItem key={topic.id} onClick={() => setTopicFilter(topic.id)}>
                    {topic.block.name} - {topic.name}
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-blue-600">{studyItems.length}</div>
              <div className="text-sm text-gray-600">Total de Itens</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-yellow-600">
                {studyItems.filter(item => item.status === ItemStatus.TO_STUDY).length}
              </div>
              <div className="text-sm text-gray-600">Pendentes</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-green-600">
                {studyItems.filter(item => item.status === ItemStatus.DONE).length}
              </div>
              <div className="text-sm text-gray-600">Concluídos</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-blue-600">
                {studyItems.filter(item => item.status === ItemStatus.IN_PROGRESS).length}
              </div>
              <div className="text-sm text-gray-600">Em Revisão</div>
            </CardContent>
          </Card>
        </div>

        {/* Lista de Itens */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredItems.map((item) => (
            <Card key={item.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-2">
                    {React.createElement(getKindIcon(item.kind), { size: 16 })}
                    <Badge variant="outline">{getKindLabel(item.kind)}</Badge>
                  </div>
                  <DropdownMenu>
                    <DropdownMenuTrigger className="btn btn-sm btn-ghost">
                      •••
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                      <DropdownMenuItem onClick={() => setViewingItem(item)}>
                        <Eye className="h-4 w-4 mr-2" />
                        Visualizar
                      </DropdownMenuItem>
                      <DropdownMenuItem onClick={() => openEditDialog(item)}>
                        <Edit className="h-4 w-4 mr-2" />
                        Editar
                      </DropdownMenuItem>
                      {item.url && (
                        <DropdownMenuItem onClick={() => window.open(item.url, '_blank')}>
                          <ExternalLink className="h-4 w-4 mr-2" />
                          Abrir Link
                        </DropdownMenuItem>
                      )}
                      <DropdownMenuItem 
                        onClick={() => setDeletingItem(item)}
                        className="text-red-600"
                      >
                        <Trash2 className="h-4 w-4 mr-2" />
                        Excluir
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                <CardTitle className="text-lg">{item.title}</CardTitle>
                <div className="text-sm text-gray-600">
                  {item.topic.block.name} • {item.topic.name}
                </div>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-gray-700 mb-3 line-clamp-3">
                  {item.content}
                </p>
                <div className="flex items-center justify-between">
                  <Badge className={getStatusColor(item.status)}>
                    {getStatusLabel(item.status)}
                  </Badge>
                  <div className="text-xs text-gray-500">
                    {new Date(item.createdAt).toLocaleDateString('pt-BR')}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {filteredItems.length === 0 && (
          <div className="text-center py-12">
            <BookOpen className="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">Nenhum item encontrado</h3>
            <p className="text-gray-600">Tente ajustar os filtros ou criar um novo item de estudo.</p>
          </div>
        )}

        {/* Dialog de Visualização */}
        <Dialog open={!!viewingItem} onOpenChange={() => setViewingItem(null)}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                {viewingItem && React.createElement(getKindIcon(viewingItem.kind), { size: 16 })}
                {viewingItem?.title}
              </DialogTitle>
            </DialogHeader>
            {viewingItem && (
              <div className="space-y-4">
                <div className="flex items-center gap-2">
                  <Badge variant="outline">{getKindLabel(viewingItem.kind)}</Badge>
                  <Badge className={getStatusColor(viewingItem.status)}>
                    {getStatusLabel(viewingItem.status)}
                  </Badge>
                </div>
                <div>
                  <h4 className="font-medium mb-2">Tópico:</h4>
                  <p className="text-sm text-gray-600">
                    {viewingItem.topic.block.name} • {viewingItem.topic.name}
                  </p>
                </div>
                <div>
                  <h4 className="font-medium mb-2">Conteúdo:</h4>
                  <p className="text-sm text-gray-700 whitespace-pre-wrap">
                    {viewingItem.content}
                  </p>
                </div>
                {viewingItem.url && (
                  <div>
                    <h4 className="font-medium mb-2">Link:</h4>
                    <a 
                      href={viewingItem.url} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline text-sm"
                    >
                      {viewingItem.url}
                    </a>
                  </div>
                )}
                <div className="text-xs text-gray-500">
                  Criado em: {new Date(viewingItem.createdAt).toLocaleString('pt-BR')}
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>

        {/* Dialog de Edição */}
        <Dialog open={!!editingItem} onOpenChange={() => setEditingItem(null)}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Editar Item de Estudo</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium">Título</label>
                  <Input
                    value={formData.title}
                    onChange={(e) => setFormData(prev => ({ ...prev, title: e.target.value }))}
                    placeholder="Título do item"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium">Tópico</label>
                  <Select value={formData.topicId} onValueChange={(value) => setFormData(prev => ({ ...prev, topicId: value }))}>
                    <SelectTrigger>
                      <SelectValue placeholder="Selecione um tópico" />
                    </SelectTrigger>
                    <SelectContent>
                      {topics.map(topic => (
                        <SelectItem key={topic.id} value={topic.id}>
                          {topic.block.name} - {topic.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium">Tipo</label>
                  <Select value={formData.kind} onValueChange={(value) => setFormData(prev => ({ ...prev, kind: value as ItemKind }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="SUMMARY">📖 Resumo</SelectItem>
                      <SelectItem value="QUESTION">❓ Questão</SelectItem>
                      <SelectItem value="LAW">⚖️ Lei</SelectItem>
                      <SelectItem value="VIDEO">🎥 Vídeo</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <label className="text-sm font-medium">Status</label>
                  <Select value={formData.status} onValueChange={(value) => setFormData(prev => ({ ...prev, status: value as ItemStatus }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="PENDING">⏳ Pendente</SelectItem>
                      <SelectItem value="COMPLETED">✅ Concluído</SelectItem>
                      <SelectItem value="REVIEWING">🔄 Revisando</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              <div>
                <label className="text-sm font-medium">Conteúdo</label>
                <Textarea
                  value={formData.content}
                  onChange={(e) => setFormData(prev => ({ ...prev, content: e.target.value }))}
                  placeholder="Conteúdo do item de estudo"
                  rows={4}
                />
              </div>
              
              <div>
                <label className="text-sm font-medium">URL (opcional)</label>
                <Input
                  value={formData.url}
                  onChange={(e) => setFormData(prev => ({ ...prev, url: e.target.value }))}
                  placeholder="https://..."
                />
              </div>
              
              <div className="flex gap-2">
                <Button onClick={updateItem} className="flex-1">
                  Salvar Alterações
                </Button>
                <Button 
                  variant="outline" 
                  onClick={() => {
                    setEditingItem(null);
                    resetForm();
                  }}
                >
                  Cancelar
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Dialog de Confirmação de Exclusão */}
        <Dialog open={!!deletingItem} onOpenChange={() => setDeletingItem(null)}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Confirmar Exclusão</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <p>Tem certeza que deseja excluir o item "{deletingItem?.title}"?</p>
              <p className="text-sm text-gray-600">Esta ação não pode ser desfeita.</p>
              <div className="flex gap-2">
                <Button 
                  onClick={deleteItem} 
                  variant="destructive" 
                  className="flex-1"
                >
                  Excluir
                </Button>
                <Button 
                  variant="outline" 
                  onClick={() => setDeletingItem(null)}
                  className="flex-1"
                >
                  Cancelar
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}