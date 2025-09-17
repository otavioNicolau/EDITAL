const fetch = require('node-fetch');

async function debugAPI() {
  try {
    console.log('🔍 Testando API /api/blocks...');
    
    const response = await fetch('http://localhost:3000/api/blocks');
    
    console.log(`📊 Status da resposta: ${response.status}`);
    console.log(`📊 Headers:`, response.headers.raw());
    
    if (response.ok) {
      const data = await response.json();
      console.log(`✅ Dados recebidos: ${data.length} blocos`);
      
      if (data.length > 0) {
        console.log('\n📋 Estrutura do primeiro bloco:');
        console.log(JSON.stringify(data[0], null, 2));
        
        console.log('\n📋 Lista de todos os blocos:');
        data.forEach((block, index) => {
          console.log(`${index + 1}. ${block.name} - ${block.topics?.length || 0} tópicos`);
        });
      } else {
        console.log('❌ Nenhum bloco retornado pela API!');
      }
    } else {
      console.log('❌ Erro na API:', response.statusText);
      const errorText = await response.text();
      console.log('Detalhes do erro:', errorText);
    }
    
  } catch (error) {
    console.error('❌ Erro ao testar API:', error.message);
  }
}

debugAPI();