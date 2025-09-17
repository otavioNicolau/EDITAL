const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function getBlockId() {
  try {
    const block = await prisma.block.findFirst({
      where: {
        name: {
          contains: 'Raciocínio'
        }
      }
    });
    
    if (block) {
      console.log(`ID do bloco "${block.name}": ${block.id}`);
    } else {
      console.log('Bloco não encontrado');
    }
    
  } catch (error) {
    console.error('Erro:', error);
  } finally {
    await prisma.$disconnect();
  }
}

getBlockId();