import { NextResponse } from 'next/server'
import { PrismaClient } from '@prisma/client'
import { exec } from 'child_process'
import { promisify } from 'util'
import fs from 'fs'

const execAsync = promisify(exec)
const prisma = new PrismaClient()

export async function GET() {
  const diagnostics = {
    timestamp: new Date().toISOString(),
    environment: {},
    database: {},
    system: {},
    files: {},
    errors: [],
    suggestions: []
  }

  try {
    // 1. Informações do Ambiente
    diagnostics.environment = {
      NODE_ENV: process.env.NODE_ENV,
      DATABASE_URL: process.env.DATABASE_URL ? 'Configurada' : 'Não configurada',
      NEXTAUTH_URL: process.env.NEXTAUTH_URL ? 'Configurada' : 'Não configurada',
      NEXTAUTH_SECRET: process.env.NEXTAUTH_SECRET ? 'Configurada' : 'Não configurada',
      platform: process.platform,
      nodeVersion: process.version,
      cwd: process.cwd()
    }

    // 2. Verificar Arquivos Importantes
    try {
      diagnostics.files = {
        envFile: fs.existsSync('.env'),
        prismaSchema: fs.existsSync('prisma/schema.prisma'),
        sqliteDb: fs.existsSync('prisma/dev.db'),
        packageJson: fs.existsSync('package.json')
      }

      // Informações do banco SQLite se existir
      if (diagnostics.files.sqliteDb) {
        const stats = fs.statSync('prisma/dev.db')
        diagnostics.files.dbSize = `${(stats.size / 1024).toFixed(2)} KB`
        diagnostics.files.dbLastModified = stats.mtime.toISOString()
      }
    } catch (fileError) {
      diagnostics.errors.push(`Erro ao verificar arquivos: ${fileError.message}`)
    }

    // 3. Teste de Conexão com Banco de Dados
    if (!process.env.DATABASE_URL) {
      diagnostics.database = {
        status: 'no_url',
        error: 'DATABASE_URL não configurada'
      }
      diagnostics.errors.push('DATABASE_URL não configurada')
      diagnostics.suggestions.push('Configurar DATABASE_URL no arquivo .env')
    } else {
      try {
        await prisma.$connect()
        
        // Contar registros nas tabelas principais
        const counts = {
          blocks: await prisma.block.count(),
          topics: await prisma.topic.count(),
          studyItems: await prisma.studyItem.count()
        }
        
        diagnostics.database = {
          status: 'connected',
          counts,
          totalRecords: counts.blocks + counts.topics + counts.studyItems
        }

        // Verificar se o banco está vazio
        if (diagnostics.database.totalRecords === 0) {
          diagnostics.suggestions.push('Banco vazio - executar seed para popular dados')
        }

        // Teste de operação de escrita
        try {
          const testBlock = await prisma.block.create({
            data: {
              name: `TESTE_VPS_${Date.now()}`,
              description: 'Teste de escrita na VPS',
              order: 999
            }
          })

          await prisma.block.delete({
            where: { id: testBlock.id }
          })

          diagnostics.database.writeTest = 'success'
        } catch (writeError) {
          diagnostics.database.writeTest = 'failed'
          diagnostics.database.writeError = writeError.message
          diagnostics.errors.push(`Erro de escrita: ${writeError.message}`)
          
          if (writeError.message.includes('READONLY')) {
            diagnostics.suggestions.push('Banco em modo somente leitura - verificar permissões')
          } else if (writeError.message.includes('locked')) {
            diagnostics.suggestions.push('Banco bloqueado - verificar processos concorrentes')
          }
        }
        
      } catch (dbError) {
        diagnostics.database = {
          status: 'error',
          error: dbError.message,
          code: dbError.code
        }
        diagnostics.errors.push(`Erro de conexão: ${dbError.message}`)
        
        // Sugestões baseadas no tipo de erro
        if (dbError.message.includes('ENOENT')) {
          diagnostics.suggestions.push('Arquivo de banco não encontrado - executar migrações')
        } else if (dbError.message.includes('EACCES')) {
          diagnostics.suggestions.push('Problema de permissões - verificar ownership dos arquivos')
        } else if (dbError.message.includes('Connection')) {
          diagnostics.suggestions.push('Problema de conectividade - verificar se o banco está rodando')
        }
      }
    }

    // 4. Informações do Sistema (apenas em Linux)
    if (process.platform === 'linux') {
      try {
        const { stdout: memInfo } = await execAsync('free -h')
        const { stdout: diskInfo } = await execAsync('df -h /')
        
        diagnostics.system = {
          memory: memInfo.split('\n')[1]?.trim(),
          disk: diskInfo.split('\n')[1]?.trim(),
          platform: 'linux'
        }

        // Verificar processos relevantes
        try {
          const { stdout: processInfo } = await execAsync('ps aux | grep -E "(postgres|mysql|nginx|pm2)" | grep -v grep')
          diagnostics.system.relevantProcesses = processInfo.split('\n').filter(line => line.trim()).length
        } catch (processError) {
          diagnostics.system.processCheck = 'failed'
        }
        
      } catch (sysError) {
        diagnostics.system = {
          error: `Não foi possível obter informações do sistema: ${sysError.message}`,
          platform: process.platform
        }
      }
    } else {
      diagnostics.system = {
        platform: process.platform,
        info: 'Informações limitadas (não Linux)'
      }
    }

    // 5. Análise e Recomendações
    const analysis = {
      overallStatus: 'unknown',
      readyForSeed: false,
      criticalIssues: [],
      recommendations: []
    }

    if (diagnostics.database.status === 'connected') {
      if (diagnostics.database.writeTest === 'success') {
        analysis.overallStatus = 'healthy'
        analysis.readyForSeed = true
        analysis.recommendations.push('Sistema funcionando - pode executar seed')
      } else {
        analysis.overallStatus = 'partial'
        analysis.criticalIssues.push('Problemas de escrita no banco')
      }
    } else {
      analysis.overallStatus = 'critical'
      analysis.criticalIssues.push('Não foi possível conectar ao banco')
      analysis.recommendations.push('Corrigir configuração do banco antes de prosseguir')
    }

    if (!diagnostics.files.envFile) {
      analysis.criticalIssues.push('Arquivo .env não encontrado')
    }

    if (!diagnostics.files.prismaSchema) {
      analysis.criticalIssues.push('Schema do Prisma não encontrado')
    }

    diagnostics.analysis = analysis

    return NextResponse.json(diagnostics)
    
  } catch (error) {
    diagnostics.errors.push(`Erro geral: ${error.message}`)
    diagnostics.analysis = {
      overallStatus: 'error',
      readyForSeed: false,
      criticalIssues: ['Erro durante diagnóstico'],
      recommendations: ['Verificar logs da aplicação']
    }
    
    return NextResponse.json(diagnostics, { status: 500 })
  } finally {
    await prisma.$disconnect()
  }
}

// Endpoint POST para executar ações de correção
export async function POST(request: Request) {
  try {
    const { action } = await request.json()
    
    switch (action) {
      case 'migrate':
        try {
          const { stdout, stderr } = await execAsync('npx prisma migrate deploy')
          return NextResponse.json({
            success: true,
            action: 'migrate',
            output: stdout,
            error: stderr
          })
        } catch (error) {
          return NextResponse.json({
            success: false,
            action: 'migrate',
            error: error.message
          }, { status: 500 })
        }
        
      case 'generate':
        try {
          const { stdout, stderr } = await execAsync('npx prisma generate')
          return NextResponse.json({
            success: true,
            action: 'generate',
            output: stdout,
            error: stderr
          })
        } catch (error) {
          return NextResponse.json({
            success: false,
            action: 'generate',
            error: error.message
          }, { status: 500 })
        }
        
      default:
        return NextResponse.json({
          success: false,
          error: 'Ação não reconhecida'
        }, { status: 400 })
    }
    
  } catch (error) {
    return NextResponse.json({
      success: false,
      error: error.message
    }, { status: 500 })
  }
}