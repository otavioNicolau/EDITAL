# 🔧 Troubleshooting: Erro de Seed na VPS

## 📋 Diagnóstico Local vs Produção

### ✅ Status Local (Funcionando)
- ✅ DATABASE_URL definida
- ✅ Conexão com banco estabelecida
- ✅ Todas as tabelas existem (15 blocos, 229 tópicos, 6 itens)
- ✅ Operações de leitura e escrita funcionando

### ❌ Problema na VPS
O erro "Erro ao executar seed" indica que há um problema específico no ambiente de produção.

## 🔍 Possíveis Causas na VPS

### 1. **Configuração do Banco de Dados**
- **Causa**: `DATABASE_URL` não configurada corretamente na VPS
- **Solução**: Verificar configuração do PostgreSQL/MySQL na VPS
- **Verificar**: Banco de dados instalado e rodando

### 2. **Variáveis de Ambiente Não Configuradas**
- **Verificar na VPS**:
  - `DATABASE_URL`
  - `NEXTAUTH_URL`
  - `NEXTAUTH_SECRET`
  - Credenciais OAuth (Google, GitHub)

### 3. **Permissões de Arquivo**
- **Causa**: Usuário da aplicação sem permissão para acessar banco
- **Solução**: Configurar permissões adequadas para SQLite ou usuário do banco

### 4. **Migrações Não Executadas**
- **Causa**: Schema do banco não criado na produção
- **Solução**: Executar `npx prisma migrate deploy`

## 🛠️ Passos para Resolver

### Passo 1: Verificar Status da VPS
```bash
# Conectar via SSH
ssh usuario@ip-da-vps

# Verificar serviços rodando
sudo systemctl status postgresql  # ou mysql
sudo systemctl status nginx
sudo systemctl status pm2
```

### Passo 2: Configurar Banco de Dados
```bash
# Para PostgreSQL
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo -u postgres createuser --interactive
sudo -u postgres createdb nome_do_banco

# Para MySQL
sudo apt install mysql-server
sudo mysql_secure_installation
```

### Passo 3: Atualizar Variáveis de Ambiente
```bash
# Criar arquivo .env na VPS
echo "DATABASE_URL=postgresql://user:pass@localhost:5432/db" > .env
echo "NEXTAUTH_URL=https://seu-dominio.com" >> .env
echo "NEXTAUTH_SECRET=sua_chave_secreta_forte" >> .env

# Ou configurar no PM2
pm2 set DATABASE_URL "postgresql://user:pass@localhost:5432/db"
```

### Passo 4: Instalar Dependências e Configurar
```bash
# Na pasta do projeto
npm install
npx prisma generate
npx prisma migrate deploy

# Configurar permissões (para SQLite)
sudo chown -R www-data:www-data /caminho/para/projeto
sudo chmod 755 /caminho/para/projeto
```

### Passo 5: Reiniciar Aplicação
```bash
# Reiniciar com PM2
pm2 restart all

# Ou reiniciar serviços
sudo systemctl restart nginx
```

## 🔧 Scripts de Diagnóstico para VPS

### Script de Verificação Completa
```bash
#!/bin/bash
# check-vps-health.sh

echo "=== Diagnóstico da VPS ==="

# Verificar serviços
echo "\n1. Status dos Serviços:"
sudo systemctl is-active postgresql || echo "PostgreSQL: INATIVO"
sudo systemctl is-active mysql || echo "MySQL: INATIVO"
sudo systemctl is-active nginx || echo "Nginx: INATIVO"

# Verificar Node.js
echo "\n2. Runtime:"
node --version || echo "Node.js não instalado"
npm --version || echo "NPM não instalado"

# Verificar banco
echo "\n3. Conectividade do Banco:"
if [ -f ".env" ]; then
  echo "Arquivo .env encontrado"
  grep -q "DATABASE_URL" .env && echo "DATABASE_URL configurada" || echo "DATABASE_URL não encontrada"
else
  echo "Arquivo .env não encontrado"
fi
```

### Endpoint de Teste para VPS
```javascript
// app/api/test-vps/route.ts
import { prisma } from '@/lib/prisma'
import { NextResponse } from 'next/server'

export async function GET() {
  const diagnostics = {
    timestamp: new Date().toISOString(),
    environment: {},
    database: {},
    system: {}
  }

  try {
    // Informações do ambiente
    diagnostics.environment = {
      NODE_ENV: process.env.NODE_ENV,
      DATABASE_URL: process.env.DATABASE_URL ? 'Configurada' : 'Não configurada',
      platform: process.platform,
      nodeVersion: process.version
    }

    // Testar conexão
    await prisma.$connect()
    
    // Verificar tabelas
    const counts = {
      blocks: await prisma.block.count(),
      topics: await prisma.topic.count(),
      items: await prisma.studyItem.count()
    }
    
    diagnostics.database = {
      status: 'connected',
      counts
    }
    
    return NextResponse.json(diagnostics)
  } catch (error) {
    diagnostics.database = {
      status: 'error',
      error: error.message
    }
    return NextResponse.json(diagnostics, { status: 500 })
  }
}
```

## 📞 Próximos Passos

1. **Imediato**: Executar script de diagnóstico na VPS
2. **Curto prazo**: Configurar banco de dados adequadamente
3. **Médio prazo**: Implementar monitoramento de logs
4. **Longo prazo**: Otimizar performance e backup automático

## 🆘 Comandos de Emergência

```bash
# Verificar status dos serviços
sudo systemctl status postgresql nginx pm2

# Reiniciar serviços
sudo systemctl restart postgresql nginx
pm2 restart all

# Verificar logs
pm2 logs
tail -f /var/log/nginx/error.log

# Verificar status do banco via API
curl https://seu-dominio.com/api/test-vps

# Executar seed manualmente
curl -X POST https://seu-dominio.com/api/seed
```

## 📚 Recursos Úteis

- [DigitalOcean VPS Setup](https://www.digitalocean.com/community/tutorials)
- [PM2 Process Manager](https://pm2.keymetrics.io/docs/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Prisma VPS Deployment](https://www.prisma.io/docs/guides/deployment)

---

**💡 Dica**: O problema mais comum em VPS é a configuração incorreta do banco de dados ou permissões inadequadas. Verificar sempre os logs do sistema primeiro.