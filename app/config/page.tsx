'use client'

import { useState } from 'react'

interface SeedResponse {
  success: boolean
  message: string
  blocksCreated?: number
  topicsCreated?: number
  blocksCount?: number
  error?: string
  details?: string
}

export default function ConfigPage() {
  const [isLoading, setIsLoading] = useState(false)
  const [response, setResponse] = useState<SeedResponse | null>(null)
  const [error, setError] = useState<string | null>(null)

  const executeSeed = async () => {
    setIsLoading(true)
    setError(null)
    setResponse(null)

    try {
      const res = await fetch('/api/seed', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      })

      const data: SeedResponse = await res.json()
      
      if (res.ok) {
        setResponse(data)
      } else {
        setError(data.error || 'Erro ao executar seed')
      }
    } catch (err) {
      setError('Erro de conexão ao executar seed')
      console.error('Erro:', err)
    } finally {
      setIsLoading(false)
    }
  }

  const checkSeedStatus = async () => {
    setIsLoading(true)
    setError(null)
    setResponse(null)

    try {
      const res = await fetch('/api/seed', {
        method: 'GET',
      })

      const data = await res.json()
      
      if (res.ok) {
        setResponse({
          success: true,
          message: `Status: ${data.status}`,
          blocksCount: data.counts.blocks,
        })
      } else {
        setError('Erro ao verificar status')
      }
    } catch (err) {
      setError('Erro de conexão ao verificar status')
      console.error('Erro:', err)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="container mt-4">
      <div className="row justify-content-center">
        <div className="col-md-8">
          <div className="card">
            <div className="card-header">
              <h2 className="card-title mb-0">
                🔧 Configurações do Sistema
              </h2>
            </div>
            <div className="card-body">
              <div className="mb-4">
                <h5>🌱 Gerenciamento do Banco de Dados</h5>
                <p className="text-muted">
                  Use os botões abaixo para gerenciar os dados iniciais do sistema.
                </p>
              </div>

              <div className="d-flex gap-3 mb-4">
                <button
                  type="button"
                  className="btn btn-primary d-flex align-items-center gap-2"
                  onClick={executeSeed}
                  disabled={isLoading}
                >
                  {isLoading ? (
                    <>
                      <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                      Executando...
                    </>
                  ) : (
                    <>
                      🌱 Executar Seed
                    </>
                  )}
                </button>

                <button
                  type="button"
                  className="btn btn-outline-secondary d-flex align-items-center gap-2"
                  onClick={checkSeedStatus}
                  disabled={isLoading}
                >
                  {isLoading ? (
                    <>
                      <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                      Verificando...
                    </>
                  ) : (
                    <>
                      📊 Verificar Status
                    </>
                  )}
                </button>
              </div>

              {/* Feedback de Sucesso */}
              {response && (
                <div className="alert alert-success" role="alert">
                  <h6 className="alert-heading">✅ Sucesso!</h6>
                  <p className="mb-2">{response.message}</p>
                  {response.blocksCreated && (
                    <p className="mb-1">
                      <strong>Blocos criados:</strong> {response.blocksCreated}
                    </p>
                  )}
                  {response.topicsCreated && (
                    <p className="mb-1">
                      <strong>Tópicos criados:</strong> {response.topicsCreated}
                    </p>
                  )}
                  {response.blocksCount && (
                    <p className="mb-0">
                      <strong>Total de blocos no banco:</strong> {response.blocksCount}
                    </p>
                  )}
                </div>
              )}

              {/* Feedback de Erro */}
              {error && (
                <div className="alert alert-danger" role="alert">
                  <h6 className="alert-heading">❌ Erro!</h6>
                  <p className="mb-0">{error}</p>
                </div>
              )}

              <div className="mt-4">
                <h6>ℹ️ Informações:</h6>
                <ul className="text-muted small">
                  <li><strong>Executar Seed:</strong> Cria os dados iniciais (blocos e tópicos) se o banco estiver vazio</li>
                  <li><strong>Verificar Status:</strong> Mostra quantos dados já existem no banco</li>
                  <li><strong>Proteção:</strong> A seed não será executada se já existirem dados</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}