const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function checkDatabase() {
  try {
    const blocks = await prisma.block.findMany({
      include: {
        topics: {
          include: {
            items: true
          }
        }
      }
    });

    console.log('=== TODOS OS BLOCOS ===');
    blocks.forEach((block, index) => {
      console.log(`${index + 1}. Bloco: "${block.name}" (ID: ${block.id})`);
      console.log(`   Tópicos: ${block.topics.length}`);
      
      block.topics.forEach((topic, topicIndex) => {
        console.log(`   ${topicIndex + 1}. Tópico: "${topic.name}" (ID: ${topic.id})`);
        console.log(`      Itens: ${topic.items.length}`);
        
        topic.items.forEach((item, itemIndex) => {
          console.log(`      ${itemIndex + 1}. Item: "${item.title}" | Status: ${item.status}`);
        });
      });
      console.log('');
    });

    // Buscar especificamente por blocos que contenham "Língua" ou "Português"
    const portugueseBlocks = blocks.filter(b => 
      b.name.toLowerCase().includes('língua') || 
      b.name.toLowerCase().includes('português') ||
      b.name.toLowerCase().includes('portugues')
    );

    console.log('=== BLOCOS DE PORTUGUÊS ENCONTRADOS ===');
    portugueseBlocks.forEach(block => {
      console.log(`Bloco: "${block.name}" (ID: ${block.id})`);
      console.log(`Tópicos: ${block.topics.length}`);
      
      block.topics.forEach(topic => {
        console.log(`- Tópico: "${topic.name}" | Itens: ${topic.items.length}`);
        topic.items.forEach(item => {
          console.log(`  * Item: "${item.title}" | Status: ${item.status}`);
        });
      });
    });

  } catch (error) {
    console.error('Erro:', error);
  } finally {
    await prisma.$disconnect();
  }
}

checkDatabase();