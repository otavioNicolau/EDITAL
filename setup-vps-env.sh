#!/bin/bash

# Script para configurar variáveis de ambiente na VPS
# Execute este script na VPS após fazer o deploy

echo "🔧 Configurando ambiente de produção na VPS..."

# Verificar se estamos no diretório correto
if [ ! -f "package.json" ]; then
    echo "❌ Erro: Execute este script no diretório raiz do projeto"
    exit 1
fi

# Criar arquivo .env se não existir
if [ ! -f ".env" ]; then
    echo "📝 Criando arquivo .env..."
    
    # Gerar NEXTAUTH_SECRET aleatório
    NEXTAUTH_SECRET=$(openssl rand -base64 32)
    
    cat > .env << EOF
# Production Environment Variables
# Generated automatically by setup-vps-env.sh

# Database - SQLite path for production
DATABASE_URL="file:./prisma/production.db"

# NextAuth.js
NEXTAUTH_URL="http://localhost:3000"
NEXTAUTH_SECRET="$NEXTAUTH_SECRET"

# Google OAuth - Configure with your production credentials
GOOGLE_CLIENT_ID="your-production-google-client-id"
GOOGLE_CLIENT_SECRET="your-production-google-client-secret"

# GitHub OAuth - Configure with your production credentials
GITHUB_ID="your-production-github-client-id"
GITHUB_SECRET="your-production-github-client-secret"

# Environment
NODE_ENV="production"
EOF

    echo "✅ Arquivo .env criado com NEXTAUTH_SECRET gerado automaticamente"
else
    echo "ℹ️  Arquivo .env já existe"
fi

# Criar diretório do banco se não existir
echo "📁 Verificando diretório do banco de dados..."
mkdir -p prisma

# Verificar se o Prisma está instalado
echo "🔍 Verificando instalação do Prisma..."
if ! command -v npx &> /dev/null; then
    echo "❌ Erro: Node.js/npm não encontrado"
    exit 1
fi

# Executar migrações do Prisma
echo "🗄️  Executando migrações do banco de dados..."
npx prisma migrate deploy

if [ $? -eq 0 ]; then
    echo "✅ Migrações executadas com sucesso"
else
    echo "⚠️  Erro nas migrações - verifique a configuração do banco"
fi

# Gerar cliente Prisma
echo "⚙️  Gerando cliente Prisma..."
npx prisma generate

if [ $? -eq 0 ]; then
    echo "✅ Cliente Prisma gerado com sucesso"
else
    echo "❌ Erro ao gerar cliente Prisma"
    exit 1
fi

echo ""
echo "🎉 Configuração concluída!"
echo ""
echo "📋 Próximos passos:"
echo "1. Edite o arquivo .env e configure suas credenciais OAuth"
echo "2. Atualize NEXTAUTH_URL com seu domínio real"
echo "3. Execute 'npm run build' para testar o build"
echo "4. Execute 'npm run seed' se precisar popular o banco"
echo ""
echo "🔐 IMPORTANTE: Mantenha o arquivo .env seguro e nunca o commite no git!"