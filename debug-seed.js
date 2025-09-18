const { PrismaClient } = require('@prisma/client')
const fs = require('fs')
const path = require('path')
const { exec } = require('child_process')
const { promisify } = require('util')

const execAsync = promisify(exec)

async function diagnosticVPS() {
  console.log('🔍 === DIAGNÓSTICO DE SEED NA VPS ===')
  console.log('Timestamp:', new Date().toISOString())
  console.log()

  let prisma
  const results = {
    environment: {},
    database: {},
    system: {},
    permissions: {},
    suggestions: []
  }

  try {
    // 1. Verificar Variáveis de Ambiente
    console.log('📋 1. VERIFICANDO VARIÁVEIS DE AMBIENTE')
    results.environment = {
      NODE_ENV: process.env.NODE_ENV || 'undefined',
      DATABASE_URL: process.env.DATABASE_URL ? 'Configurada' : 'Não configurada',
      NEXTAUTH_URL: process.env.NEXTAUTH_URL ? 'Configurada' : 'Não configurada',
      NEXTAUTH_SECRET: process.env.NEXTAUTH_SECRET ? 'Configurada' : 'Não configurada',
      platform: process.platform,
      nodeVersion: process.version,
      cwd: process.cwd()
    }
    
    console.log('   ✅ NODE_ENV:', results.environment.NODE_ENV)
    console.log('   ✅ DATABASE_URL:', results.environment.DATABASE_URL)
    console.log('   ✅ Platform:', results.environment.platform)
    console.log('   ✅ Node Version:', results.environment.nodeVersion)
    console.log()

    // 2. Verificar Arquivos e Permissões
    console.log('📁 2. VERIFICANDO ARQUIVOS E PERMISSÕES')
    
    const envExists = fs.existsSync('.env')
    const prismaExists = fs.existsSync('prisma/schema.prisma')
    const dbExists = fs.existsSync('prisma/dev.db')
    
    results.permissions = {
      envFile: envExists,
      prismaSchema: prismaExists,
      sqliteDb: dbExists
    }
    
    console.log('   📄 Arquivo .env:', envExists ? '✅ Existe' : '❌ Não encontrado')
    console.log('   📄 Schema Prisma:', prismaExists ? '✅ Existe' : '❌ Não encontrado')
    console.log('   📄 Banco SQLite:', dbExists ? '✅ Existe' : '❌ Não encontrado')
    
    if (dbExists) {
      try {
        const stats = fs.statSync('prisma/dev.db')
        console.log('   📊 Tamanho do banco:', (stats.size / 1024).toFixed(2), 'KB')
        console.log('   📅 Última modificação:', stats.mtime.toISOString())
      } catch (err) {
        console.log('   ⚠️  Erro ao ler stats do banco:', err.message)
      }
    }
    console.log()

    // 3. Verificar Informações do Sistema (se disponível)
    console.log('💻 3. VERIFICANDO SISTEMA')
    try {
      if (process.platform === 'linux') {
        const { stdout: memInfo } = await execAsync('free -h')
        const { stdout: diskInfo } = await execAsync('df -h /')
        const { stdout: processInfo } = await execAsync('ps aux | grep -E "(postgres|mysql|nginx)" | grep -v grep')
        
        results.system = {
          memory: memInfo.split('\n')[1],
          disk: diskInfo.split('\n')[1],
          processes: processInfo.split('\n').filter(line => line.trim())
        }
        
        console.log('   💾 Memória:', results.system.memory)
        console.log('   💿 Disco:', results.system.disk)
        console.log('   🔄 Processos relevantes:', results.system.processes.length)
      } else {
        console.log('   ℹ️  Sistema Windows - informações limitadas')
        results.system = { platform: 'windows', info: 'limited' }
      }
    } catch (sysError) {
      console.log('   ⚠️  Não foi possível obter informações do sistema:', sysError.message)
      results.system = { error: sysError.message }
    }
    console.log()

    // 4. Testar Conexão com Banco
    console.log('🗄️  4. TESTANDO CONEXÃO COM BANCO DE DADOS')
    
    if (!process.env.DATABASE_URL) {
      console.log('   ❌ DATABASE_URL não configurada')
      results.suggestions.push('Configurar DATABASE_URL no arquivo .env')
      results.database.status = 'no_url'
    } else {
      try {
        prisma = new PrismaClient()
        await prisma.$connect()
        console.log('   ✅ Conexão estabelecida com sucesso')
        
        // Verificar estrutura do banco
        const blockCount = await prisma.block.count()
        const topicCount = await prisma.topic.count()
        const studyItemCount = await prisma.studyItem.count()
        
        results.database = {
          status: 'connected',
          counts: {
            blocks: blockCount,
            topics: topicCount,
            studyItems: studyItemCount
          }
        }
        
        console.log('   📊 Registros encontrados:')
        console.log('      - Blocos:', blockCount)
        console.log('      - Tópicos:', topicCount)
        console.log('      - Itens de Estudo:', studyItemCount)
        
        if (blockCount === 0 && topicCount === 0 && studyItemCount === 0) {
          console.log('   ⚠️  Banco vazio - seed necessária')
          results.suggestions.push('Executar seed para popular o banco')
        }
        
      } catch (dbError) {
        console.log('   ❌ Erro de conexão:', dbError.message)
        results.database = {
          status: 'error',
          error: dbError.message,
          code: dbError.code
        }
        
        // Sugestões baseadas no tipo de erro
        if (dbError.message.includes('ENOENT')) {
          results.suggestions.push('Arquivo de banco SQLite não encontrado - executar migrações')
        } else if (dbError.message.includes('EACCES')) {
          results.suggestions.push('Problema de permissões - verificar ownership dos arquivos')
        } else if (dbError.message.includes('Connection')) {
          results.suggestions.push('Problema de conectividade - verificar se o banco está rodando')
        }
      }
    }
    console.log()

    // 5. Teste de Operação de Escrita (se conectado)
    if (prisma && results.database.status === 'connected') {
      console.log('✍️  5. TESTANDO OPERAÇÕES DE ESCRITA')
      try {
        // Criar um registro de teste
        const testBlock = await prisma.block.create({
          data: {
            name: 'TESTE_VPS_' + Date.now(),
            description: 'Bloco de teste para diagnóstico da VPS',
            order: 999
          }
        })
        
        console.log('   ✅ Criação de registro: OK')
        
        // Remover o registro de teste
        await prisma.block.delete({
          where: { id: testBlock.id }
        })
        
        console.log('   ✅ Remoção de registro: OK')
        console.log('   ✅ Operações de escrita funcionando normalmente')
        
        results.database.writeTest = 'success'
        
      } catch (writeError) {
        console.log('   ❌ Erro nas operações de escrita:', writeError.message)
        results.database.writeTest = 'failed'
        results.database.writeError = writeError.message
        
        if (writeError.message.includes('READONLY')) {
          results.suggestions.push('Banco em modo somente leitura - verificar permissões')
        } else if (writeError.message.includes('locked')) {
          results.suggestions.push('Banco bloqueado - verificar se há outros processos usando')
        }
      }
      console.log()
    }

  } catch (error) {
    console.log('❌ ERRO GERAL:', error.message)
    results.generalError = error.message
  } finally {
    if (prisma) {
      await prisma.$disconnect()
    }
  }

  // 6. Resumo e Sugestões
  console.log('📋 === RESUMO DO DIAGNÓSTICO ===')
  console.log('Status do Banco:', results.database.status || 'unknown')
  console.log('Plataforma:', results.environment.platform)
  console.log('Ambiente:', results.environment.NODE_ENV)
  console.log()
  
  if (results.suggestions.length > 0) {
    console.log('💡 SUGESTÕES PARA CORREÇÃO:')
    results.suggestions.forEach((suggestion, index) => {
      console.log(`   ${index + 1}. ${suggestion}`)
    })
    console.log()
  }
  
  console.log('🔧 PRÓXIMOS PASSOS RECOMENDADOS:')
  if (results.database.status === 'connected') {
    console.log('   1. Testar endpoint /api/test-vps na aplicação')
    console.log('   2. Executar seed via /api/seed')
    console.log('   3. Monitorar logs da aplicação')
  } else {
    console.log('   1. Corrigir configuração do banco de dados')
    console.log('   2. Executar: npx prisma migrate deploy')
    console.log('   3. Verificar permissões dos arquivos')
    console.log('   4. Reiniciar aplicação')
  }
  
  console.log()
  console.log('📄 Relatório completo salvo em: diagnostic-results.json')
  
  // Salvar relatório
  fs.writeFileSync('diagnostic-results.json', JSON.stringify(results, null, 2))
  
  return results
}

// Executar diagnóstico
if (require.main === module) {
  diagnosticVPS().catch(console.error)
}

module.exports = { diagnosticVPS }