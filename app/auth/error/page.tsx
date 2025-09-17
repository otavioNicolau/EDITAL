'use client'

import { Suspense } from 'react'
import { useSearchParams } from 'next/navigation'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertCircle, ArrowLeft } from 'lucide-react'
import Link from 'next/link'

const errors: Record<string, string> = {
  Configuration: 'Erro de configuração do servidor.',
  AccessDenied: 'Acesso negado. Você não tem permissão para acessar este recurso.',
  Verification: 'Token expirado ou inválido. Tente fazer login novamente.',
  Default: 'Ocorreu um erro inesperado. Tente novamente.',
}

function AuthErrorContent() {
  const searchParams = useSearchParams()
  const error = searchParams.get('error')
  
  const errorMessage = error && errors[error] ? errors[error] : errors.Default

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-pink-100 p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <div className="flex justify-center mb-4">
            <div className="p-3 bg-red-100 rounded-full">
              <AlertCircle className="w-8 h-8 text-red-600" />
            </div>
          </div>
          <CardTitle className="text-2xl font-bold text-gray-900">
            Erro de Autenticação
          </CardTitle>
          <CardDescription className="text-gray-600">
            {errorMessage}
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="text-center space-y-3">
            <p className="text-sm text-gray-500">
              Se o problema persistir, entre em contato com o suporte.
            </p>
            
            <div className="flex flex-col gap-2">
              <Link href="/auth/signin">
                <Button className="w-full">
                  Tentar Novamente
                </Button>
              </Link>
              
              <Link href="/" className="flex items-center gap-2">
                <Button variant="outline" className="w-full">
                  <ArrowLeft className="w-4 h-4" />
                  Voltar ao Início
                </Button>
              </Link>
            </div>
          </div>
          
          {error && (
            <div className="mt-6 p-3 bg-gray-50 rounded-lg">
              <p className="text-xs text-gray-500 font-mono">
                Código do erro: {error}
              </p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}

export default function AuthError() {
  return (
    <Suspense fallback={<div>Carregando...</div>}>
      <AuthErrorContent />
    </Suspense>
  )
}