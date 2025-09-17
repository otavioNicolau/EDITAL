const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function verifyItems() {
  try {
    console.log('=== VERIFICAÇÃO DE ITENS NO BANCO ===');
    
    // Buscar todos os blocos com seus tópicos e itens
    const blocks = await prisma.block.findMany({
      include: {
        topics: {
          include: {
            items: true,
          },
        },
      },
    });
    
    console.log(`\nTotal de blocos: ${blocks.length}`);
    
    blocks.forEach((block) => {
      console.log(`\n--- BLOCO: ${block.name} ---`);
      console.log(`ID: ${block.id}`);
      console.log(`Tópicos: ${block.topics.length}`);
      
      // Coletar todos os itens de todos os tópicos do bloco
      const allItems = block.topics.flatMap((topic) => topic.items);
      
      console.log(`Total de itens: ${allItems.length}`);
      
      if (allItems.length > 0) {
        const itemsByStatus = {
          TO_STUDY: allItems.filter(item => item.status === 'TO_STUDY').length,
          IN_PROGRESS: allItems.filter(item => item.status === 'IN_PROGRESS').length,
          DONE: allItems.filter(item => item.status === 'DONE').length,
        };
        
        console.log('Itens por status:');
        console.log(`  TO_STUDY: ${itemsByStatus.TO_STUDY}`);
        console.log(`  IN_PROGRESS: ${itemsByStatus.IN_PROGRESS}`);
        console.log(`  DONE: ${itemsByStatus.DONE}`);
        
        // Mostrar detalhes dos itens
        console.log('\nDetalhes dos itens:');
        allItems.forEach((item, index) => {
          console.log(`  ${index + 1}. ${item.title} (${item.status}) - Tópico: ${item.topicId}`);
        });
      } else {
        console.log('Nenhum item encontrado neste bloco.');
      }
    });
    
    // Verificar especificamente o bloco "Raciocínio Lógico-Matemático"
    const mathBlock = blocks.find(block => block.name.includes('Raciocínio'));
    if (mathBlock) {
      console.log('\n=== VERIFICAÇÃO ESPECÍFICA DO BLOCO RACIOCÍNIO LÓGICO-MATEMÁTICO ===');
      const mathItems = mathBlock.topics.flatMap(topic => topic.items);
      console.log(`Total de itens no bloco de Raciocínio: ${mathItems.length}`);
      
      if (mathItems.length > 0) {
        console.log('\nItens encontrados:');
        mathItems.forEach((item, index) => {
          console.log(`${index + 1}. ${item.title}`);
          console.log(`   Status: ${item.status}`);
          console.log(`   Kind: ${item.kind}`);
          console.log(`   Tópico ID: ${item.topicId}`);
          console.log(`   Criado em: ${item.createdAt}`);
          console.log('');
        });
      }
    }
    
  } catch (error) {
    console.error('Erro ao verificar itens:', error);
  } finally {
    await prisma.$disconnect();
  }
}

verifyItems();