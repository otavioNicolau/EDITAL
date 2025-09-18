# 🔧 Troubleshooting SQLite na VPS

## 🚨 Problemas Comuns com SQLite na VPS

### 1. Permissões de Arquivo

**Problema:** SQLite não consegue criar ou acessar o arquivo de banco

**Soluções:**

```bash
# Criar diretório prisma se não existir
mkdir -p /var/www/otavio-edital/prisma

# Definir permissões corretas
chown -R www-data:www-data /var/www/otavio-edital/prisma
chmod 755 /var/www/otavio-edital/prisma
chmod 644 /var/www/otavio-edital/prisma/*.db 2>/dev/null || true

# Ou usar permissões mais amplas (menos seguro)
chmod 777 /var/www/otavio-edital/prisma
```

### 2. Caminho do Banco de Dados

**Problema:** DATABASE_URL com caminho incorreto

**Verificar .env atual:**
```bash
cat /var/www/otavio-edital/.env | grep DATABASE_URL
```

**Opções de configuração:**

```bash
# Opção 1: Caminho relativo (recomendado)
DATABASE_URL="file:./prisma/production.db"

# Opção 2: Caminho absoluto
DATABASE_URL="file:/var/www/otavio-edital/prisma/production.db"

# Opção 3: Na raiz do projeto
DATABASE_URL="file:./production.db"
```

### 3. Verificar se SQLite está Instalado

```bash
# Verificar se sqlite3 está disponível
sqlite3 --version

# Se não estiver instalado (Ubuntu/Debian)
sudo apt update
sudo apt install sqlite3

# CentOS/RHEL
sudo yum install sqlite
```

### 4. Testar Conexão Manual

```bash
# Navegar para o diretório do projeto
cd /var/www/otavio-edital

# Testar criação manual do banco
sqlite3 prisma/test.db "CREATE TABLE test (id INTEGER PRIMARY KEY);"

# Verificar se foi criado
ls -la prisma/

# Remover teste
rm prisma/test.db
```

## 🔄 Script de Diagnóstico SQLite

```bash
#!/bin/bash
# Salve como: diagnose-sqlite.sh

echo "=== Diagnóstico SQLite VPS ==="
echo ""

# Verificar se SQLite está instalado
echo "1. Verificando instalação do SQLite..."
if command -v sqlite3 &> /dev/null; then
    echo "✅ SQLite instalado: $(sqlite3 --version)"
else
    echo "❌ SQLite não encontrado"
fi

echo ""

# Verificar diretório do projeto
echo "2. Verificando diretório do projeto..."
PROJECT_DIR="/var/www/otavio-edital"
if [ -d "$PROJECT_DIR" ]; then
    echo "✅ Diretório do projeto existe: $PROJECT_DIR"
    cd "$PROJECT_DIR"
else
    echo "❌ Diretório do projeto não encontrado: $PROJECT_DIR"
    exit 1
fi

echo ""

# Verificar arquivo .env
echo "3. Verificando configuração DATABASE_URL..."
if [ -f ".env" ]; then
    echo "✅ Arquivo .env encontrado"
    DATABASE_URL=$(grep "^DATABASE_URL=" .env | cut -d'=' -f2- | tr -d '"')
    echo "📋 DATABASE_URL: $DATABASE_URL"
else
    echo "❌ Arquivo .env não encontrado"
fi

echo ""

# Verificar diretório prisma
echo "4. Verificando diretório prisma..."
if [ -d "prisma" ]; then
    echo "✅ Diretório prisma existe"
    echo "📋 Permissões do diretório prisma:"
    ls -la prisma/
else
    echo "❌ Diretório prisma não encontrado"
    echo "🔧 Criando diretório prisma..."
    mkdir -p prisma
    chmod 755 prisma
fi

echo ""

# Testar criação de arquivo SQLite
echo "5. Testando criação de arquivo SQLite..."
TEST_DB="prisma/test-$(date +%s).db"
if sqlite3 "$TEST_DB" "CREATE TABLE test (id INTEGER PRIMARY KEY); INSERT INTO test (id) VALUES (1);"; then
    echo "✅ Teste de criação SQLite bem-sucedido"
    # Verificar se consegue ler
    RESULT=$(sqlite3 "$TEST_DB" "SELECT COUNT(*) FROM test;")
    echo "📋 Registros no teste: $RESULT"
    rm "$TEST_DB"
else
    echo "❌ Falha no teste de criação SQLite"
fi

echo ""

# Verificar permissões do usuário atual
echo "6. Verificando usuário e permissões..."
echo "📋 Usuário atual: $(whoami)"
echo "📋 Grupos: $(groups)"
echo "📋 Permissões do diretório atual:"
ls -la .

echo ""
echo "=== Fim do Diagnóstico ==="
```

## 🛠️ Soluções Alternativas

### Opção 1: Migrar para PostgreSQL (Recomendado para Produção)

**1. Instalar PostgreSQL:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install postgresql postgresql-contrib

# Iniciar serviço
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

**2. Criar banco e usuário:**
```bash
sudo -u postgres psql

-- No prompt do PostgreSQL:
CREATE DATABASE otavio_edital;
CREATE USER otavio_user WITH ENCRYPTED PASSWORD 'sua_senha_segura';
GRANT ALL PRIVILEGES ON DATABASE otavio_edital TO otavio_user;
\q
```

**3. Atualizar .env:**
```bash
# Substituir no .env
DATABASE_URL="postgresql://otavio_user:sua_senha_segura@localhost:5432/otavio_edital"
```

**4. Executar migrações:**
```bash
npx prisma migrate deploy
npx prisma generate
```

### Opção 2: SQLite com Configuração Específica

**1. Criar script de configuração SQLite:**
```bash
#!/bin/bash
# Salve como: setup-sqlite-vps.sh

PROJECT_DIR="/var/www/otavio-edital"
cd "$PROJECT_DIR"

# Criar diretório com permissões corretas
mkdir -p prisma
chmod 755 prisma

# Configurar .env para SQLite
cat > .env << 'EOF'
DATABASE_URL="file:./prisma/production.db"
NEXTAUTH_URL="http://localhost:3004"
NEXTAUTH_SECRET="$(openssl rand -base64 32)"
GOOGLE_CLIENT_ID="placeholder"
GOOGLE_CLIENT_SECRET="placeholder"
GITHUB_ID="placeholder"
GITHUB_SECRET="placeholder"
NODE_ENV="production"
EOF

# Executar migrações
npx prisma migrate deploy
npx prisma generate

# Definir permissões finais
chown -R $USER:$USER prisma/
chmod 644 prisma/*.db 2>/dev/null || true

echo "✅ SQLite configurado com sucesso!"
```

### Opção 3: MySQL (Alternativa)

**1. Instalar MySQL:**
```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

**2. Criar banco:**
```bash
sudo mysql

-- No prompt do MySQL:
CREATE DATABASE otavio_edital;
CREATE USER 'otavio_user'@'localhost' IDENTIFIED BY 'sua_senha_segura';
GRANT ALL PRIVILEGES ON otavio_edital.* TO 'otavio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**3. Atualizar .env:**
```bash
DATABASE_URL="mysql://otavio_user:sua_senha_segura@localhost:3306/otavio_edital"
```

## 🔍 Comandos de Verificação

```bash
# Verificar se o processo Node.js consegue acessar o SQLite
node -e "const { PrismaClient } = require('@prisma/client'); const prisma = new PrismaClient(); prisma.\$connect().then(() => console.log('✅ Conexão OK')).catch(e => console.log('❌ Erro:', e.message));"

# Verificar logs do PM2
pm2 logs otavio-edital

# Verificar se o arquivo de banco foi criado
ls -la prisma/

# Testar query direta no banco
sqlite3 prisma/production.db ".tables"
```

## 📋 Checklist de Troubleshooting

- [ ] SQLite instalado no sistema
- [ ] Diretório prisma existe e tem permissões corretas
- [ ] DATABASE_URL configurada corretamente no .env
- [ ] Migrações executadas sem erro
- [ ] Cliente Prisma gerado
- [ ] Arquivo de banco criado em prisma/
- [ ] Permissões de leitura/escrita no arquivo de banco
- [ ] Processo Node.js rodando com usuário correto

## 🆘 Se Nada Funcionar

**Solução de emergência - PostgreSQL rápido:**

```bash
# Instalar e configurar PostgreSQL rapidamente
sudo apt install postgresql -y
sudo -u postgres createdb otavio_edital

# Atualizar .env
sed -i 's|DATABASE_URL=.*|DATABASE_URL="postgresql://postgres@localhost:5432/otavio_edital"|' .env

# Migrar
npx prisma migrate deploy
npx prisma generate

# Testar build
npm run build
```

Esta solução usa PostgreSQL com autenticação peer (sem senha) que é mais simples para configurar rapidamente.

---

**💡 Dica:** PostgreSQL é mais robusto para produção que SQLite. Considere migrar definitivamente se os problemas persistirem.