#!/bin/bash
set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo "==========================================="
echo "=== 🔍 Diagnóstico SQLite VPS ==="
echo "==========================================="
echo ""

# Verificar se SQLite está instalado
log_info "1. Verificando instalação do SQLite..."
if command -v sqlite3 &> /dev/null; then
    VERSION=$(sqlite3 --version | cut -d' ' -f1)
    log_success "SQLite instalado: $VERSION"
else
    log_error "SQLite não encontrado"
    log_info "Para instalar: sudo apt install sqlite3"
fi

echo ""

# Verificar diretório do projeto
log_info "2. Verificando diretório do projeto..."
PROJECT_DIR="/var/www/otavio-edital"
if [ -d "$PROJECT_DIR" ]; then
    log_success "Diretório do projeto existe: $PROJECT_DIR"
    cd "$PROJECT_DIR"
else
    log_error "Diretório do projeto não encontrado: $PROJECT_DIR"
    log_info "Verifique se o caminho está correto"
    exit 1
fi

echo ""

# Verificar arquivo .env
log_info "3. Verificando configuração DATABASE_URL..."
if [ -f ".env" ]; then
    log_success "Arquivo .env encontrado"
    if grep -q "^DATABASE_URL=" .env; then
        DATABASE_URL=$(grep "^DATABASE_URL=" .env | cut -d'=' -f2- | tr -d '"')
        log_info "DATABASE_URL configurada: $DATABASE_URL"
        
        # Verificar se é SQLite
        if [[ $DATABASE_URL == file:* ]]; then
            log_success "Configuração SQLite detectada"
            # Extrair caminho do arquivo
            DB_PATH=$(echo $DATABASE_URL | sed 's/file://')
            log_info "Caminho do banco: $DB_PATH"
        else
            log_warning "DATABASE_URL não é SQLite: $DATABASE_URL"
        fi
    else
        log_error "DATABASE_URL não encontrada no .env"
    fi
else
    log_error "Arquivo .env não encontrado"
    log_info "Execute o script setup-vps-env.sh primeiro"
fi

echo ""

# Verificar diretório prisma
log_info "4. Verificando diretório prisma..."
if [ -d "prisma" ]; then
    log_success "Diretório prisma existe"
    log_info "Conteúdo do diretório prisma:"
    ls -la prisma/ | while read line; do
        echo "    $line"
    done
    
    # Verificar permissões
    PRISMA_PERMS=$(stat -c "%a" prisma/)
    log_info "Permissões do diretório prisma: $PRISMA_PERMS"
    
    if [ "$PRISMA_PERMS" -ge "755" ]; then
        log_success "Permissões adequadas"
    else
        log_warning "Permissões podem ser insuficientes"
        log_info "Execute: chmod 755 prisma/"
    fi
else
    log_error "Diretório prisma não encontrado"
    log_info "Criando diretório prisma..."
    mkdir -p prisma
    chmod 755 prisma
    log_success "Diretório prisma criado"
fi

echo ""

# Verificar schema.prisma
log_info "5. Verificando schema.prisma..."
if [ -f "prisma/schema.prisma" ]; then
    log_success "Schema Prisma encontrado"
    
    # Verificar provider no schema
    if grep -q 'provider.*=.*"sqlite"' prisma/schema.prisma; then
        log_success "Provider SQLite configurado no schema"
    else
        log_warning "Provider SQLite não encontrado no schema"
        log_info "Verifique se o schema está configurado para SQLite"
    fi
else
    log_error "Schema Prisma não encontrado"
fi

echo ""

# Testar criação de arquivo SQLite
log_info "6. Testando criação de arquivo SQLite..."
if command -v sqlite3 &> /dev/null; then
    TEST_DB="prisma/test-$(date +%s).db"
    if sqlite3 "$TEST_DB" "CREATE TABLE test (id INTEGER PRIMARY KEY); INSERT INTO test (id) VALUES (1);" 2>/dev/null; then
        log_success "Teste de criação SQLite bem-sucedido"
        
        # Verificar se consegue ler
        RESULT=$(sqlite3 "$TEST_DB" "SELECT COUNT(*) FROM test;" 2>/dev/null)
        log_info "Registros no teste: $RESULT"
        
        # Verificar permissões do arquivo criado
        TEST_PERMS=$(stat -c "%a" "$TEST_DB")
        log_info "Permissões do arquivo teste: $TEST_PERMS"
        
        rm "$TEST_DB"
        log_success "Arquivo teste removido"
    else
        log_error "Falha no teste de criação SQLite"
        log_info "Verifique permissões do diretório prisma/"
    fi
else
    log_warning "SQLite não disponível para teste"
fi

echo ""

# Verificar usuário e permissões
log_info "7. Verificando usuário e permissões..."
log_info "Usuário atual: $(whoami)"
log_info "Grupos: $(groups | tr ' ' ', ')"
log_info "UID/GID: $(id)"

# Verificar permissões do diretório do projeto
PROJECT_PERMS=$(stat -c "%a" .)
log_info "Permissões do diretório do projeto: $PROJECT_PERMS"

echo ""

# Verificar se existe arquivo de banco atual
log_info "8. Verificando arquivos de banco existentes..."
DB_FILES=$(find . -name "*.db" -type f 2>/dev/null || true)
if [ -n "$DB_FILES" ]; then
    log_success "Arquivos de banco encontrados:"
    echo "$DB_FILES" | while read db_file; do
        if [ -f "$db_file" ]; then
            DB_SIZE=$(stat -c%s "$db_file")
            DB_PERMS=$(stat -c "%a" "$db_file")
            log_info "  $db_file (${DB_SIZE} bytes, permissões: $DB_PERMS)"
        fi
    done
else
    log_warning "Nenhum arquivo de banco encontrado"
fi

echo ""

# Testar conexão Prisma (se possível)
log_info "9. Testando conexão Prisma..."
if [ -f "node_modules/@prisma/client/index.js" ]; then
    log_info "Cliente Prisma encontrado, testando conexão..."
    
    # Criar script de teste temporário
    cat > test-prisma.js << 'EOF'
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

prisma.$connect()
  .then(() => {
    console.log('✅ Conexão Prisma bem-sucedida');
    return prisma.$disconnect();
  })
  .catch((error) => {
    console.log('❌ Erro na conexão Prisma:', error.message);
    process.exit(1);
  });
EOF

    if node test-prisma.js 2>/dev/null; then
        log_success "Conexão Prisma funcionando"
    else
        log_error "Falha na conexão Prisma"
        log_info "Execute: npx prisma generate"
    fi
    
    rm test-prisma.js
else
    log_warning "Cliente Prisma não encontrado"
    log_info "Execute: npm install && npx prisma generate"
fi

echo ""

# Verificar logs recentes
log_info "10. Verificando logs do PM2..."
if command -v pm2 &> /dev/null; then
    if pm2 list | grep -q "otavio-edital"; then
        log_info "Últimas linhas do log do PM2:"
        pm2 logs otavio-edital --lines 5 --nostream 2>/dev/null | tail -10 || log_warning "Não foi possível acessar logs do PM2"
    else
        log_warning "Processo otavio-edital não encontrado no PM2"
    fi
else
    log_warning "PM2 não encontrado"
fi

echo ""
echo "==========================================="
echo "=== 📋 RESUMO DO DIAGNÓSTICO ==="
echo "==========================================="

# Gerar resumo
echo ""
log_info "Resumo dos problemas encontrados:"

# Verificar problemas comuns
PROBLEMS_FOUND=0

if ! command -v sqlite3 &> /dev/null; then
    log_error "SQLite não instalado"
    echo "  Solução: sudo apt install sqlite3"
    ((PROBLEMS_FOUND++))
fi

if [ ! -f ".env" ]; then
    log_error "Arquivo .env não encontrado"
    echo "  Solução: Execute ./setup-vps-env.sh"
    ((PROBLEMS_FOUND++))
fi

if [ ! -d "prisma" ]; then
    log_error "Diretório prisma não encontrado"
    echo "  Solução: mkdir -p prisma && chmod 755 prisma"
    ((PROBLEMS_FOUND++))
fi

if [ ! -f "node_modules/@prisma/client/index.js" ]; then
    log_error "Cliente Prisma não gerado"
    echo "  Solução: npm install && npx prisma generate"
    ((PROBLEMS_FOUND++))
fi

if [ $PROBLEMS_FOUND -eq 0 ]; then
    log_success "Nenhum problema crítico encontrado!"
    echo ""
    log_info "Se ainda há problemas, tente:"
    echo "  1. npx prisma migrate deploy"
    echo "  2. npx prisma generate"
    echo "  3. npm run build"
    echo "  4. pm2 restart otavio-edital"
else
    echo ""
    log_warning "$PROBLEMS_FOUND problema(s) encontrado(s). Resolva-os e execute o diagnóstico novamente."
fi

echo ""
log_info "Para mais detalhes, consulte: SQLITE_VPS_TROUBLESHOOTING.md"
echo "==========================================="