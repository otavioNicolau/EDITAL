import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import './globals.css'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'Gerenciador de Estudos para Concurso',
  description: 'Organize seu edital verticalizado e acompanhe seu progresso',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="pt-BR">
      <body className={inter.className}>
        <div className="min-vh-100 bg-light">
          <nav className="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div className="container-fluid px-4">
              <h1 className="navbar-brand h4 mb-0 fw-bold">📚 Estudos Concurso</h1>
              <div className="navbar-nav flex-row gap-3">
                <a href="/" className="nav-link text-primary">Dashboard</a>
                <a href="/blocos" className="nav-link text-primary">Blocos</a>
                <a href="/estudo" className="nav-link text-primary">Estudo</a>
                <a href="/revisao" className="nav-link text-primary">Revisão</a>
              </div>
            </div>
          </nav>
          <main className="container-fluid px-4 py-4">
            {children}
          </main>
        </div>
      </body>
    </html>
  )
}
