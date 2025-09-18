#!/usr/bin/env bash
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

# Função para verificar se o serviço está rodando
check_service_health() {
    local port=$1
    local name=$2
    
    log_info "Verificando saúde do serviço $name na porta $port..."
    
    # Aguarda 10 segundos para o serviço inicializar
    sleep 10
    
    # Testa se a porta está respondendo
    if curl -f -s "http://localhost:$port" > /dev/null 2>&1; then
        log_success "Serviço $name está respondendo na porta $port"
        return 0
    else
        log_warning "Serviço $name não está respondendo na porta $port"
        return 1
    fi
}

# Função para configurar ambiente de produção
setup_production_env() {
    local dir=$1
    
    log_info "Configurando ambiente de produção para $dir..."
    
    # Verifica se existe .env
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            log_info "Copiando .env.production para .env"
            cp .env.production .env
        elif [ -f ".env.example" ]; then
            log_warning "Copiando .env.example para .env - CONFIGURE AS VARIÁVEIS!"
            cp .env.example .env
        else
            log_error "Nenhum arquivo de ambiente encontrado!"
            return 1
        fi
    fi
    
    # Gera NEXTAUTH_SECRET se não existir
    if ! grep -q "NEXTAUTH_SECRET=" .env || grep -q "NEXTAUTH_SECRET=\"\"" .env; then
        log_info "Gerando NEXTAUTH_SECRET..."
        SECRET=$(openssl rand -base64 32)
        sed -i "s/NEXTAUTH_SECRET=.*/NEXTAUTH_SECRET=\"$SECRET\"/g" .env
    fi
    
    # Configura NODE_ENV para produção
    sed -i "s/NODE_ENV=.*/NODE_ENV=\"production\"/g" .env
    
    log_success "Ambiente configurado com sucesso"
}

# Função para executar migrações do Prisma
run_prisma_migrations() {
    log_info "Executando migrações do Prisma..."
    
    if [ -f "prisma/schema.prisma" ]; then
        # Executa migrações
        npx prisma migrate deploy
        
        # Gera cliente Prisma
        npx prisma generate
        
        log_success "Migrações do Prisma executadas com sucesso"
    else
        log_warning "Schema do Prisma não encontrado, pulando migrações"
    fi
}

echo "==========================================="
echo "=== Atualizando todos os projetos Next.js ==="
echo "==========================================="

# Lista de apps: nome-da-pasta | nome-no-PM2 | porta
apps=(
  "otavio-edital|otavio-edital|3004"
  "dayane-edital|dayane-edital|3002"
  "darlan-edital|darlan-edital|3003"
)

# Contador de sucessos e falhas
success_count=0
fail_count=0
failed_apps=()

for entry in "${apps[@]}"; do
  IFS="|" read -r dir pm2_name port <<< "$entry"

  echo ""
  echo "==========================================="
  log_info "Atualizando $pm2_name (porta $port)..."
  echo "==========================================="

  # Verifica se o diretório existe
  if [ ! -d "/var/www/$dir" ]; then
    log_error "Diretório /var/www/$dir não encontrado!"
    ((fail_count++))
    failed_apps+=("$pm2_name - Diretório não encontrado")
    continue
  fi

  cd /var/www/$dir

  # Backup do .env atual (se existir)
  if [ -f ".env" ]; then
    log_info "Fazendo backup do .env atual..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
  fi

  # Puxa última versão do git
  log_info "Atualizando código do repositório..."
  if ! git pull; then
    log_error "Falha ao executar git pull para $pm2_name"
    ((fail_count++))
    failed_apps+=("$pm2_name - Git pull falhou")
    continue
  fi

  # Configura ambiente de produção
  if ! setup_production_env "$dir"; then
    log_error "Falha ao configurar ambiente para $pm2_name"
    ((fail_count++))
    failed_apps+=("$pm2_name - Configuração de ambiente falhou")
    continue
  fi

  # Instala dependências (usando lockfile)
  log_info "Instalando dependências..."
  if ! npm ci; then
    log_error "Falha ao instalar dependências para $pm2_name"
    ((fail_count++))
    failed_apps+=("$pm2_name - npm ci falhou")
    continue
  fi

  # Executa migrações do Prisma
  if ! run_prisma_migrations; then
    log_error "Falha nas migrações do Prisma para $pm2_name"
    ((fail_count++))
    failed_apps+=("$pm2_name - Migrações Prisma falharam")
    continue
  fi

  # Rebuilda o Next.js
  log_info "Executando build do Next.js..."
  if ! npm run build; then
    log_error "Falha no build para $pm2_name"
    ((fail_count++))
    failed_apps+=("$pm2_name - Build falhou")
    continue
  fi

  # Para o serviço atual (se estiver rodando)
  log_info "Parando serviço atual..."
  pm2 stop "$pm2_name" 2>/dev/null || true

  # Reinicia no PM2
  log_info "Iniciando serviço no PM2..."
  if pm2 reload "$pm2_name" 2>/dev/null; then
    log_success "Serviço $pm2_name recarregado com sucesso"
  else
    log_info "Iniciando novo processo no PM2..."
    if pm2 start "npm run start -- -p $port" --name "$pm2_name"; then
      log_success "Serviço $pm2_name iniciado com sucesso"
    else
      log_error "Falha ao iniciar $pm2_name no PM2"
      ((fail_count++))
      failed_apps+=("$pm2_name - PM2 start falhou")
      continue
    fi
  fi

  # Verifica saúde do serviço
  if check_service_health "$port" "$pm2_name"; then
    log_success "$pm2_name atualizado e funcionando corretamente!"
    ((success_count++))
  else
    log_warning "$pm2_name foi atualizado mas pode não estar funcionando corretamente"
    ((success_count++))
  fi
done

echo ""
echo "==========================================="
echo "=== RESUMO DA ATUALIZAÇÃO ==="
echo "==========================================="
log_success "Projetos atualizados com sucesso: $success_count"
if [ $fail_count -gt 0 ]; then
  log_error "Projetos com falha: $fail_count"
  echo ""
  log_error "Detalhes das falhas:"
  for failed_app in "${failed_apps[@]}"; do
    echo "  - $failed_app"
  done
fi

echo ""
log_info "Status dos serviços PM2:"
pm2 status

echo ""
if [ $fail_count -eq 0 ]; then
  log_success "Todos os projetos foram atualizados com sucesso! 🎉"
  exit 0
else
  log_warning "Alguns projetos falharam na atualização. Verifique os logs acima."
  exit 1
fi