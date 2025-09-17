import { prisma } from '@/lib/prisma'
import { NextResponse } from 'next/server'

const blocks = [
  {
    name: "Língua Portuguesa",
    topics: [
      "Compreensão e interpretação de textos",
      "Coesão e coerência textual",
      "Pontuação",
      "Crase",
      "Concordância verbal e nominal",
      "Regência verbal e nominal",
      "Sintaxe"
    ]
  },
  {
    name: "Raciocínio Lógico-Matemático",
    topics: [
      "Funções",
      "Progressões Aritméticas e Geométricas",
      "Probabilidade e Estatística",
      "Análise Combinatória",
      "Geometria",
      "Porcentagem e Juros"
    ]
  },
  {
    name: "Informática",
    topics: [
      "Segurança da Informação",
      "Cloud Computing",
      "Inteligência Artificial/Big Data/IoT",
      "Sistemas Operacionais",
      "Redes de Computadores",
      "Banco de Dados"
    ]
  },
  {
    name: "Física",
    topics: [
      "Mecânica",
      "Termodinâmica",
      "Eletromagnetismo",
      "Óptica",
      "Ondas"
    ]
  },
  {
    name: "Ética no Serviço Público",
    topics: [
      "Princípios éticos",
      "Código de Ética Profissional",
      "Conflito de interesses",
      "Transparência pública"
    ]
  },
  {
    name: "Geopolítica",
    topics: [
      "Relações internacionais",
      "Blocos econômicos",
      "Conflitos mundiais",
      "Geografia política"
    ]
  },
  {
    name: "Línguas Estrangeiras",
    topics: [
      "Inglês - Interpretação de textos",
      "Inglês - Gramática",
      "Espanhol - Interpretação de textos",
      "Espanhol - Gramática"
    ]
  },
  {
    name: "Legislação de Trânsito",
    topics: [
      "Código de Trânsito Brasileiro",
      "Infrações e penalidades",
      "Sinalização",
      "Direção defensiva"
    ]
  },
  {
    name: "Direito Administrativo",
    topics: [
      "Princípios da Administração Pública",
      "Atos administrativos",
      "Licitações e contratos",
      "Serviços públicos",
      "Responsabilidade civil do Estado"
    ]
  },
  {
    name: "Direito Constitucional",
    topics: [
      "Princípios fundamentais",
      "Direitos e garantias fundamentais",
      "Organização do Estado",
      "Organização dos Poderes",
      "Controle de constitucionalidade"
    ]
  },
  {
    name: "Direito Penal",
    topics: [
      "Teoria geral do crime",
      "Crimes contra a pessoa",
      "Crimes contra o patrimônio",
      "Crimes contra a Administração Pública",
      "Penas e medidas de segurança"
    ]
  },
  {
    name: "Direito Processual Penal",
    topics: [
      "Inquérito policial",
      "Ação penal",
      "Provas",
      "Prisões e medidas cautelares",
      "Procedimentos"
    ]
  },
  {
    name: "Legislação Especial",
    topics: [
      "Lei de Drogas",
      "Estatuto da Criança e do Adolescente",
      "Lei Maria da Penha",
      "Crimes hediondos",
      "Lavagem de dinheiro"
    ]
  },
  {
    name: "Direitos Humanos",
    topics: [
      "Declaração Universal dos Direitos Humanos",
      "Pactos internacionais",
      "Sistema interamericano",
      "Grupos vulneráveis",
      "Políticas públicas"
    ]
  }
]

// GET /api/seed - Verificar status do banco
export async function GET() {
  try {
    const blocksCount = await prisma.block.count()
    const topicsCount = await prisma.topic.count()
    const itemsCount = await prisma.studyItem.count()
    
    return NextResponse.json({
      status: blocksCount > 0 ? 'seeded' : 'empty',
      counts: {
        blocks: blocksCount,
        topics: topicsCount,
        items: itemsCount
      }
    })
  } catch (error) {
    console.error('Erro ao verificar status do banco:', error)
    return NextResponse.json(
      { error: 'Erro ao verificar status do banco' },
      { status: 500 }
    )
  }
}

export async function POST() {
  try {
    console.log('🌱 Iniciando seed do banco de dados via API...')

    // Verificar se já existem dados no banco
    const existingBlocks = await prisma.block.count()
    if (existingBlocks > 0) {
      console.log('✅ Banco já possui dados, pulando seed.')
      return NextResponse.json({ 
        success: true,
        message: 'Banco já possui dados, seed não executada',
        blocksCount: existingBlocks 
      })
    }

    // Limpar dados existentes (só por segurança)
    await prisma.review.deleteMany()
    await prisma.studySession.deleteMany()
    await prisma.studyItem.deleteMany()
    await prisma.topic.deleteMany()
    await prisma.block.deleteMany()

    // Criar blocos e tópicos
    for (let i = 0; i < blocks.length; i++) {
      const blockData = blocks[i]
      
      const block = await prisma.block.create({
        data: {
          name: blockData.name,
          order: i + 1,
        }
      })

      // Criar tópicos para cada bloco
      for (const topicName of blockData.topics) {
        await prisma.topic.create({
          data: {
            name: topicName,
            blockId: block.id,
            weight: 1,
            status: 'PLANNED'
          }
        })
      }
    }

    const totalBlocks = await prisma.block.count()
    const totalTopics = await prisma.topic.count()

    console.log(`✅ Seed executada com sucesso! ${totalBlocks} blocos e ${totalTopics} tópicos criados.`)

    return NextResponse.json({
      success: true,
      message: `Seed executado com sucesso! Criados ${totalBlocks} blocos e ${totalTopics} tópicos.`,
      blocksCreated: totalBlocks,
      topicsCreated: totalTopics
    })
  } catch (error) {
    console.error('❌ Erro ao executar seed:', error)
    return NextResponse.json(
      { success: false, error: 'Erro ao executar seed', details: error instanceof Error ? error.message : 'Erro desconhecido' },
      { status: 500 }
    )
  }
}
