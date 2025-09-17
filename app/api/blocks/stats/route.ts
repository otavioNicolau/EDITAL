import { NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

// GET /api/blocks/stats - Buscar estatísticas de itens por bloco
export async function GET() {
  try {
    console.log('Iniciando busca de blocos com estatísticas...');
    
    // Garantir conexão com o banco
    await prisma.$connect();
    
    const blocks = await prisma.block.findMany({
      include: {
        topics: {
          include: {
            items: true,
          },
        },
      },
      orderBy: {
        createdAt: 'asc',
      },
    });
    
    console.log('Blocos encontrados:', blocks.length);

    // Processar dados para contar itens por status em cada bloco
    const blocksWithStats = blocks.map((block: any) => {
      // Coletar todos os itens de todos os tópicos do bloco
      const allItems = block.topics.flatMap((topic: any) => topic.items);
      
      // Contar itens por status
      const itemStats = {
        total: allItems.length,
        TO_STUDY: allItems.filter((item: any) => item.status === 'TO_STUDY').length,
        IN_PROGRESS: allItems.filter((item: any) => item.status === 'IN_PROGRESS').length,
        DONE: allItems.filter((item: any) => item.status === 'DONE').length,
      };

      // Contar tópicos por status
      const topicStats = {
        total: block.topics.length,
        PLANNED: block.topics.filter((topic: any) => topic.status === 'PLANNED').length,
        STUDYING: block.topics.filter((topic: any) => topic.status === 'STUDYING').length,
        REVIEWED: block.topics.filter((topic: any) => topic.status === 'REVIEWED').length,
      };

      // Calcular progresso e status do bloco
      const progress = itemStats.total > 0 ? Math.round((itemStats.DONE / itemStats.total) * 100) : 0;
      const status = progress === 100 ? 'Concluído' : progress > 0 ? 'Em Progresso' : 'Não Iniciado';

      return {
        id: block.id,
        name: block.name,
        description: block.description,
        createdAt: block.createdAt,
        updatedAt: block.updatedAt,
        progress,
        status,
        itemStats,
        topicStats,
        topics: block.topics.map((topic: any) => ({
          id: topic.id,
          name: topic.name,
          status: topic.status,
          itemCount: topic.items.length,
        })),
      };
    });

    const response = NextResponse.json(blocksWithStats);
    response.headers.set('Cache-Control', 'no-cache, no-store, must-revalidate');
    response.headers.set('Pragma', 'no-cache');
    response.headers.set('Expires', '0');
    return response;
  } catch (error) {
    console.error('Erro ao buscar estatísticas dos blocos:', error);
    return NextResponse.json(
      { error: 'Erro interno do servidor' },
      { status: 500 }
    );
  }
}