'use client'

import { signIn, getProviders } from 'next-auth/react'
import { useEffect, useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Github, Mail, BookOpen } from 'lucide-react'

interface Provider {
  id: string
  name: string
  type: string
  signinUrl: string
  callbackUrl: string
}

export default function SignIn() {
  const [providers, setProviders] = useState<Record<string, Provider> | null>(null)

  useEffect(() => {
    const fetchProviders = async () => {
      const res = await getProviders()
      setProviders(res)
    }
    fetchProviders()
  }, [])

  const getProviderIcon = (providerId: string) => {
    switch (providerId) {
      case 'google':
        return <Mail className="w-5 h-5" />
      case 'github':
        return <Github className="w-5 h-5" />
      default:
        return null
    }
  }

  const getProviderColor = (providerId: string) => {
    switch (providerId) {
      case 'google':
        return 'bg-red-600 hover:bg-red-700'
      case 'github':
        return 'bg-gray-800 hover:bg-gray-900'
      default:
        return 'bg-blue-600 hover:bg-blue-700'
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <div className="flex justify-center mb-4">
            <div className="p-3 bg-blue-100 rounded-full">
              <BookOpen className="w-8 h-8 text-blue-600" />
            </div>
          </div>
          <CardTitle className="text-2xl font-bold text-gray-900">
            Gerenciador de Estudos
          </CardTitle>
          <CardDescription className="text-gray-600">
            Faça login para acessar seu painel de estudos
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {providers &&
            Object.values(providers).map((provider) => (
              <Button
                key={provider.name}
                onClick={() => signIn(provider.id, { callbackUrl: '/' })}
                className={`w-full flex items-center justify-center gap-3 text-white ${getProviderColor(
                  provider.id
                )}`}
                size="lg"
              >
                {getProviderIcon(provider.id)}
                Continuar com {provider.name}
              </Button>
            ))}
          
          <div className="text-center text-sm text-gray-500 mt-6">
            <p>Ao fazer login, você concorda com nossos</p>
            <p>
              <a href="#" className="text-blue-600 hover:underline">
                Termos de Uso
              </a>{' '}
              e{' '}
              <a href="#" className="text-blue-600 hover:underline">
                Política de Privacidade
              </a>
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}