'use client'

import { useState, useEffect, Suspense } from 'react'
import { useSearchParams } from 'next/navigation'

interface Topic {
  id: string;
  name: string;
  block: {
    id: string;
    name: string;
    color?: string;
  };
}

function EstudoPageContent() {
  const searchParams = useSearchParams()
  const topicIdFromUrl = searchParams.get('topic') || searchParams.get('topicId')
  
  const [isActive, setIsActive] = useState(false)
  const [time, setTime] = useState(25 * 60) // 25 minutos em segundos
  const [isBreak, setIsBreak] = useState(false)
  const [sessions, setSessions] = useState(0)
  const [selectedTopicId, setSelectedTopicId] = useState<string>(topicIdFromUrl || '')
  const [topics, setTopics] = useState<Topic[]>([])
  const [notes, setNotes] = useState('')
  const [sessionStartTime, setSessionStartTime] = useState<Date | null>(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    fetchTopics()
    fetchTodaySessions()
  }, [])

  // Atualiza o tópico selecionado quando o parâmetro da URL muda
  useEffect(() => {
    if (topicIdFromUrl) {
      setSelectedTopicId(topicIdFromUrl)
    }
  }, [topicIdFromUrl])

  useEffect(() => {
    let interval: NodeJS.Timeout | null = null
    
    if (isActive && time > 0) {
      interval = setInterval(() => {
        setTime(time => time - 1)
      }, 1000)
    } else if (time === 0) {
      // Sessão terminou
      setIsActive(false)
      if (!isBreak) {
        handleSessionComplete()
        setIsBreak(true)
        setTime(5 * 60) // 5 minutos de pausa
      } else {
        setIsBreak(false)
        setTime(25 * 60) // Volta para 25 minutos
      }
    }

    return () => {
      if (interval) clearInterval(interval)
    }
  }, [isActive, time, isBreak])

  const fetchTopics = async () => {
    try {
      const response = await fetch('/api/topics')
      if (response.ok) {
        const data = await response.json()
        setTopics(data)
      }
    } catch (error) {
      console.error('Erro ao carregar tópicos:', error)
    }
  }

  const fetchTodaySessions = async () => {
    try {
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const tomorrow = new Date(today)
      tomorrow.setDate(tomorrow.getDate() + 1)
      
      const response = await fetch(`/api/sessions?startDate=${today.toISOString()}&endDate=${tomorrow.toISOString()}`)
      if (response.ok) {
        const data = await response.json()
        setSessions(data.length)
      }
    } catch (error) {
      console.error('Erro ao carregar sessões:', error)
    }
  }

  const handleSessionComplete = async () => {
    if (!selectedTopicId || !sessionStartTime) return

    try {
      const endTime = new Date()
      const duration = Math.floor((endTime.getTime() - sessionStartTime.getTime()) / 1000 / 60) // em minutos
      
      const response = await fetch('/api/sessions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          topicId: selectedTopicId,
          duration: Math.max(duration, 25), // Mínimo 25 minutos
          startedAt: sessionStartTime.toISOString(),
          endedAt: endTime.toISOString(),
          notes: notes.trim() || undefined,
        }),
      })

      if (response.ok) {
        setSessions(prev => prev + 1)
        setNotes('')
        setSessionStartTime(null)
      }
    } catch (error) {
      console.error('Erro ao salvar sessão:', error)
    }
  }

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }

  const startTimer = () => {
    if (!selectedTopicId) {
      alert('Por favor, selecione um tópico antes de iniciar o estudo.')
      return
    }
    
    if (!sessionStartTime) {
      setSessionStartTime(new Date())
    }
    setIsActive(true)
  }
  
  const pauseTimer = () => setIsActive(false)
  
  const resetTimer = () => {
    setIsActive(false)
    setTime(isBreak ? 5 * 60 : 25 * 60)
    setSessionStartTime(null)
  }

  const saveSession = async () => {
    if (!selectedTopicId) {
      alert('Por favor, selecione um tópico.')
      return
    }

    if (!notes.trim()) {
      alert('Por favor, adicione algumas notas sobre a sessão.')
      return
    }

    setLoading(true)
    try {
      const now = new Date()
      const startTime = sessionStartTime || new Date(now.getTime() - 25 * 60 * 1000) // 25 min atrás se não tiver start time
      
      const response = await fetch('/api/sessions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          topicId: selectedTopicId,
          duration: 25, // Sessão manual de 25 minutos
          startedAt: startTime.toISOString(),
          endedAt: now.toISOString(),
          notes: notes.trim(),
        }),
      })

      if (response.ok) {
        setSessions(prev => prev + 1)
        setNotes('')
        alert('Sessão salva com sucesso!')
      } else {
        throw new Error('Erro ao salvar sessão')
      }
    } catch (error) {
      console.error('Erro ao salvar sessão:', error)
      alert('Erro ao salvar sessão. Tente novamente.')
    } finally {
      setLoading(false)
    }
  }

  const selectedTopic = topics.find(t => t.id === selectedTopicId)

  return (
    <div className="container">
      <div className="row justify-content-center">
        <div className="col-lg-8 col-md-10">
          <div className="text-center mb-4">
            <h1 className="display-4 fw-bold mb-3">⏱️ Sessão de Estudo</h1>
            <p className="lead text-muted">
              {isBreak ? 'Hora do intervalo!' : 'Foque nos seus estudos'}
            </p>
          </div>

          {/* Timer Principal */}
          <div className="card shadow-lg mb-4">
            <div className="card-body text-center py-5">
              <div className={`display-1 font-monospace fw-bold mb-4 ${
                isBreak ? 'text-success' : 'text-primary'
              }`} style={{fontSize: '4rem'}}>
                {formatTime(time)}
              </div>
              
              <div className="d-flex justify-content-center gap-3 mb-4">
                {!isActive ? (
                  <button
                    onClick={startTimer}
                    className="btn btn-success btn-lg px-4 py-2"
                  >
                    ▶️ Iniciar
                  </button>
                ) : (
                  <button
                    onClick={pauseTimer}
                    className="btn btn-warning btn-lg px-4 py-2"
                  >
                    ⏸️ Pausar
                  </button>
                )}
                
                <button
                  onClick={resetTimer}
                  className="btn btn-secondary btn-lg px-4 py-2"
                >
                  🔄 Resetar
                </button>
              </div>

              <small className="text-muted">
                {isBreak ? 'Intervalo' : 'Sessão de estudo'} • {sessions} sessões concluídas hoje
              </small>
            </div>
          </div>

          {/* Seleção de Tópico */}
          <div className="card mb-4">
            <div className="card-header">
              <h5 className="card-title mb-0">📖 Tópico Atual</h5>
            </div>
            <div className="card-body">
              <select 
                className="form-select form-select-lg"
                value={selectedTopicId}
                onChange={(e) => setSelectedTopicId(e.target.value)}
              >
                <option value="">Selecione um tópico para estudar</option>
                {topics.map((topic) => (
                  <option key={topic.id} value={topic.id}>
                    {topic.block.name} - {topic.name}
                  </option>
                ))}
              </select>
              {selectedTopic && (
                <div className="alert alert-primary mt-3" role="alert">
                  <div className="small">
                    <strong>Bloco:</strong> {selectedTopic.block.name}
                  </div>
                  <div className="small">
                    <strong>Tópico:</strong> {selectedTopic.name}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Notas da Sessão */}
          <div className="card mb-4">
            <div className="card-header">
              <h5 className="card-title mb-0">📝 Notas da Sessão</h5>
            </div>
            <div className="card-body">
              <div className="mb-3">
                <textarea
                  className="form-control"
                  rows={4}
                  placeholder="Anote aqui o que você estudou, dúvidas, insights..."
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  style={{resize: 'none'}}
                />
              </div>
              <button 
                onClick={saveSession}
                disabled={loading || !selectedTopicId}
                className="btn btn-primary"
              >
                {loading ? '⏳ Salvando...' : '💾 Salvar Sessão'}
              </button>
            </div>
          </div>

          {/* Estatísticas Rápidas */}
          <div className="row g-3">
            <div className="col-md-4">
              <div className="card text-center">
                <div className="card-body">
                  <div className="display-6 fw-bold text-primary">{sessions}</div>
                  <small className="text-muted">Sessões Hoje</small>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card text-center">
                <div className="card-body">
                  <div className="display-6 fw-bold text-success">{sessions * 25}min</div>
                  <small className="text-muted">Tempo Estudado</small>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card text-center">
                <div className="card-body">
                  <div className="display-6 fw-bold text-warning">0</div>
                  <small className="text-muted">Meta Diária</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default function EstudoPage() {
  return (
    <Suspense fallback={<div>Carregando...</div>}>
      <EstudoPageContent />
    </Suspense>
  )
}
