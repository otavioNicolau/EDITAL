#!/usr/bin/env node

/**
 * Script para executar seed durante o build no Netlify
 * Este script faz uma requisição HTTP para a API de seed
 */

const https = require('https');
const http = require('http');

const SITE_URL = process.env.URL || process.env.DEPLOY_PRIME_URL || 'http://localhost:3000';
const API_ENDPOINT = `${SITE_URL}/api/seed`;

console.log('🌱 Executando seed via API durante o build...');
console.log('URL da API:', API_ENDPOINT);

// Função para fazer requisição HTTP
function makeRequest(url, method = 'POST') {
  return new Promise((resolve, reject) => {
    const isHttps = url.startsWith('https');
    const client = isHttps ? https : http;
    
    const options = {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'Netlify-Build-Script'
      },
      timeout: 30000 // 30 segundos de timeout
    };

    const req = client.request(url, options, (res) => {
      let data = '';
      
      res.on('data', (chunk) => {
        data += chunk;
      });
      
      res.on('end', () => {
        try {
          const response = JSON.parse(data);
          resolve({ statusCode: res.statusCode, data: response });
        } catch (error) {
          resolve({ statusCode: res.statusCode, data: data });
        }
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    req.on('timeout', () => {
      req.destroy();
      reject(new Error('Request timeout'));
    });

    req.end();
  });
}

// Função principal
async function runSeed() {
  try {
    console.log('📡 Fazendo requisição para a API de seed...');
    
    const response = await makeRequest(API_ENDPOINT, 'POST');
    
    if (response.statusCode === 200) {
      console.log('✅ Seed executada com sucesso!');
      console.log('Resposta:', response.data);
    } else {
      console.log(`⚠️ API retornou status ${response.statusCode}`);
      console.log('Resposta:', response.data);
    }
    
  } catch (error) {
    console.log('❌ Erro ao executar seed via API:', error.message);
    console.log('⚠️ Continuando o build mesmo com erro na seed...');
  }
}

// Executar apenas se chamado diretamente
if (require.main === module) {
  runSeed();
}

module.exports = { runSeed };