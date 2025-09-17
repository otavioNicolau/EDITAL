'use client'

import { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Clock, BookOpen, CheckCircle, XCircle, AlertCircle } from 'lucide-react'
import { StudyItem, Topic, Block } from '@prisma/client'
import { 
  GRADE_LABELS, 
  GRADE_COLORS,
  getGradeLabel,
  getGradeColor
} from '@/lib/constants'

type StudyItemWithRelations = StudyItem & {
  topic: Topic & {
    block: Block
  }
}

type ReviewGrade = 0 | 1 | 2 | 3 | 4 | 5

// Constantes movidas para @/lib/constants

export default function RevisaoPage() {
  const router = useRouter()
  const [studyItems, setStudyItems] = useState<StudyItemWithRelations[]>([])
  const [currentItemIndex, setCurrentItemIndex] = useState(0)
  const [showAnswer, setShowAnswer] = useState(false)
  const [selectedGrade, setSelectedGrade] = useState<ReviewGrade | null>(null)
  const [notes, setNotes] = useState('')
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [reviewsCompleted, setReviewsCompleted] = useState(0)

  useEffect(() => {
    fetchDueItems()
  }, [])

  const fetchDueItems = async () => {
    try {
      const response = await fetch('/api/study-items?due=true')
      if (response.ok) {
        const items = await response.json()
        setStudyItems(items)
      }
    } catch (error) {
      console.error('Erro ao buscar itens para revisão:', error)
    } finally {
      setLoading(false)
    }
  }

  const submitReview = async () => {
    if (selectedGrade === null) return

    setSubmitting(true)
    try {
      const currentItem = studyItems[currentItemIndex]
      
      const response = await fetch('/api/reviews', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          studyItemId: currentItem.id,
          grade: selectedGrade,
          notes: notes.trim() || undefined
        })
      })

      if (response.ok) {
        setReviewsCompleted(prev => prev + 1)
        
        // Notificar outras páginas que os blocos foram atualizados
        localStorage.setItem('blocks-updated', Date.now().toString())
        
        // Próximo item ou finalizar
        if (currentItemIndex < studyItems.length - 1) {
          setCurrentItemIndex(prev => prev + 1)
          setShowAnswer(false)
          setSelectedGrade(null)
          setNotes('')
        } else {
          // Todas as revisões concluídas
          router.push('/?reviews-completed=true')
        }
      }
    } catch (error) {
      console.error('Erro ao submeter revisão:', error)
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <Clock className="h-8 w-8 animate-spin mx-auto mb-4 text-blue-600" />
          <p className="text-gray-600">Carregando itens para revisão...</p>
        </div>
      </div>
    )
  }

  if (studyItems.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <Card className="w-full max-w-md">
          <CardContent className="text-center p-8">
            <CheckCircle className="h-16 w-16 text-green-500 mx-auto mb-4" />
            <h2 className="text-2xl font-bold mb-2">Parabéns!</h2>
            <p className="text-gray-600 mb-6">Não há itens para revisar no momento.</p>
            <Button onClick={() => router.push('/')} className="w-full">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Voltar ao Dashboard
            </Button>
          </CardContent>
        </Card>
      </div>
    )
  }

  const currentItem = studyItems[currentItemIndex]
  const progress = ((currentItemIndex + 1) / studyItems.length) * 100

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <Button
            variant="ghost"
            onClick={() => router.push('/')}
            className="flex items-center gap-2"
          >
            <ArrowLeft className="h-4 w-4" />
            Dashboard
          </Button>
          
          <div className="text-center">
            <h1 className="text-2xl font-bold">Revisão SRS</h1>
            <p className="text-gray-600">
              {currentItemIndex + 1} de {studyItems.length} itens
            </p>
          </div>
          
          <div className="text-right">
            <p className="text-sm text-gray-600">Concluídas</p>
            <p className="text-2xl font-bold text-green-600">{reviewsCompleted}</p>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="w-full bg-gray-200 rounded-full h-2 mb-6">
          <div 
            className="bg-blue-600 h-2 rounded-full transition-all duration-300"
            style={{ width: `${progress}%` }}
          />
        </div>

        {/* Item Card */}
        <Card className="mb-6">
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle className="text-lg">
                  {currentItem.topic.block.name} - {currentItem.topic.name}
                </CardTitle>
                <div className="flex items-center gap-2 mt-2">
                  <Badge variant="outline">
                    <BookOpen className="h-3 w-3 mr-1" />
                    {currentItem.kind}
                  </Badge>
                  <Badge variant="secondary">
                    Tópico: {currentItem.topic.name}
                  </Badge>
                </div>
              </div>
            </div>
          </CardHeader>
          
          <CardContent>
            <div className="space-y-4">
              <div>
                <h3 className="font-semibold mb-2">Pergunta/Conteúdo:</h3>
                <div className="bg-gray-50 p-4 rounded-lg">
                  <p className="whitespace-pre-wrap">{currentItem.notes || currentItem.title}</p>
                </div>
              </div>

              {showAnswer && (
                <div>
                  <h3 className="font-semibold mb-2">Resposta:</h3>
                  <div className="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <p className="whitespace-pre-wrap">{currentItem.notes || 'Sem resposta disponível'}</p>
                  </div>
                </div>
              )}

              {!showAnswer ? (
                <Button 
                  onClick={() => setShowAnswer(true)}
                  className="w-full"
                >
                  Mostrar Resposta
                </Button>
              ) : (
                <div className="space-y-4">
                  {/* Grade Selection */}
                  <div>
                    <h3 className="font-semibold mb-3">Como foi sua performance?</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                      {Object.keys(GRADE_LABELS).map((grade) => (
                        <Button
                          key={grade}
                          variant={selectedGrade === Number(grade) ? "default" : "outline"}
                          onClick={() => setSelectedGrade(Number(grade) as ReviewGrade)}
                          className={`text-left justify-start h-auto p-3 ${
                            selectedGrade === Number(grade) ? getGradeColor(Number(grade)) : ''
                          }`}
                        >
                          <div>
                            <div className="font-semibold">Nota {grade}</div>
                            <div className="text-sm opacity-90">{getGradeLabel(Number(grade))}</div>
                          </div>
                        </Button>
                      ))}
                    </div>
                  </div>

                  {/* Notes */}
                  <div>
                    <h3 className="font-semibold mb-2">Observações (opcional):</h3>
                    <Textarea
                      value={notes}
                      onChange={(e) => setNotes(e.target.value)}
                      placeholder="Adicione suas observações sobre esta revisão..."
                      rows={3}
                    />
                  </div>

                  {/* Submit Button */}
                  <Button
                    onClick={submitReview}
                    disabled={selectedGrade === null || submitting}
                    className="w-full"
                    size="lg"
                  >
                    {submitting ? (
                      <>
                        <Clock className="h-4 w-4 mr-2 animate-spin" />
                        Processando...
                      </>
                    ) : currentItemIndex < studyItems.length - 1 ? (
                      'Próximo Item'
                    ) : (
                      'Finalizar Revisões'
                    )}
                  </Button>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Quick Stats */}
        <div className="grid grid-cols-3 gap-4">
          <Card>
            <CardContent className="text-center p-4">
              <BookOpen className="h-6 w-6 mx-auto mb-2 text-blue-600" />
              <p className="text-sm text-gray-600">Restantes</p>
              <p className="text-xl font-bold">{studyItems.length - currentItemIndex - 1}</p>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="text-center p-4">
              <CheckCircle className="h-6 w-6 mx-auto mb-2 text-green-600" />
              <p className="text-sm text-gray-600">Concluídas</p>
              <p className="text-xl font-bold">{reviewsCompleted}</p>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="text-center p-4">
              <AlertCircle className="h-6 w-6 mx-auto mb-2 text-orange-600" />
              <p className="text-sm text-gray-600">Progresso</p>
              <p className="text-xl font-bold">{Math.round(progress)}%</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
