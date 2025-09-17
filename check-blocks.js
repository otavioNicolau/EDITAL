const { PrismaClient } = require('@prisma/client');

const prisma = new PrismaClient();

async function checkBlocks() {
  try {
    console.log('🔍 Verificando blocos no banco de dados...');
    
    const blocks = await prisma.block.findMany({
      include: {
        topics: {
          include: {
            _count: {
              select: {
                items: true,
                reviews: true,
              },
            },
          },
        },
        _count: {
          select: {
            topics: true,
          },
        },
      },
      orderBy: {
        createdAt: 'asc',
      },
    });
    
    console.log(`📊 Total de blocos encontrados: ${blocks.length}`);
    
    if (blocks.length === 0) {
      console.log('❌ Nenhum bloco encontrado no banco de dados!');
      console.log('💡 Execute: npm run db:seed para criar os dados');
    } else {
      console.log('✅ Blocos encontrados:');
      blocks.forEach((block, index) => {
        console.log(`${index + 1}. ${block.name} (${block._count.topics} tópicos)`);
      });
    }
    
  } catch (error) {
    console.error('❌ Erro ao verificar blocos:', error);
  } finally {
    await prisma.$disconnect();
  }
}

checkBlocks();