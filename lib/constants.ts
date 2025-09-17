import { BookOpen, FileText, Scale, Video } from 'lucide-react'
import { ItemKind, ItemStatus } from '@prisma/client'

// Ícones para tipos de itens de estudo
export const KIND_ICONS = {
  SUMMARY: BookOpen,
  QUESTION: FileText,
  LAW: Scale,
  VIDEO: Video,
  OTHER: FileText,
} as const

// Labels para tipos de itens de estudo
export const KIND_LABELS = {
  SUMMARY: 'Resumo',
  QUESTION: 'Questão',
  LAW: 'Lei',
  VIDEO: 'Vídeo',
  OTHER: 'Outro',
} as const

// Labels para status de itens
export const STATUS_LABELS = {
  PENDING: 'Pendente',
  COMPLETED: 'Concluído',
  REVIEWING: 'Revisando',
  TO_STUDY: 'Para Estudar',
} as const

// Cores para status de itens
export const STATUS_COLORS = {
  PENDING: 'bg-yellow-100 text-yellow-800',
  COMPLETED: 'bg-green-100 text-green-800',
  REVIEWING: 'bg-blue-100 text-blue-800',
  TO_STUDY: 'bg-gray-100 text-gray-800',
} as const

// Status de tópicos
export const TOPIC_STATUS_LABELS = {
  PLANNED: 'Planejado',
  STUDYING: 'Estudando',
  COMPLETED: 'Concluído',
} as const

export const TOPIC_STATUS_COLORS = {
  PLANNED: 'bg-gray-100 text-gray-800',
  STUDYING: 'bg-blue-100 text-blue-800',
  COMPLETED: 'bg-green-100 text-green-800',
} as const

// Grades de revisão
export const GRADE_LABELS = {
  0: 'Blackout completo',
  1: 'Resposta incorreta, mas lembrei ao ver a resposta',
  2: 'Resposta incorreta, mas foi fácil lembrar',
  3: 'Resposta correta, mas com dificuldade',
  4: 'Resposta correta, após hesitação',
  5: 'Resposta correta, fácil'
} as const

export const GRADE_COLORS = {
  0: 'bg-red-500',
  1: 'bg-red-400',
  2: 'bg-orange-400',
  3: 'bg-yellow-400',
  4: 'bg-blue-400',
  5: 'bg-green-500'
} as const

// Funções utilitárias
export const getKindIcon = (kind: ItemKind) => KIND_ICONS[kind]
export const getKindLabel = (kind: ItemKind) => KIND_LABELS[kind]
export const getStatusLabel = (status: ItemStatus | string) => STATUS_LABELS[status as keyof typeof STATUS_LABELS] || status
export const getStatusColor = (status: ItemStatus | string) => STATUS_COLORS[status as keyof typeof STATUS_COLORS] || 'bg-gray-100 text-gray-800'
export const getTopicStatusLabel = (status: string) => TOPIC_STATUS_LABELS[status as keyof typeof TOPIC_STATUS_LABELS] || status
export const getTopicStatusColor = (status: string) => TOPIC_STATUS_COLORS[status as keyof typeof TOPIC_STATUS_COLORS] || 'bg-gray-100 text-gray-800'
export const getGradeLabel = (grade: number) => GRADE_LABELS[grade as keyof typeof GRADE_LABELS] || `Grade ${grade}`
export const getGradeColor = (grade: number) => GRADE_COLORS[grade as keyof typeof GRADE_COLORS] || 'bg-gray-500'