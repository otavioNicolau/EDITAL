#!/bin/bash

# Script de Verificação de Saúde da VPS para Aplicação Next.js
# Uso: ./check-vps-health.sh

echo "🔍 === DIAGNÓSTICO DE SAÚDE DA VPS ==="
echo "Timestamp: $(date)"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para verificar status
check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
    else
        echo -e "${RED}❌ $1${NC}"
    fi
}

# 1. Verificar Informações do Sistema
echo -e "${BLUE}💻 1. INFORMAÇÕES DO SISTEMA${NC}"
echo "   Sistema: $(uname -a)"
echo "   Uptime: $(uptime)"
echo "   Usuário atual: $(whoami)"
echo "   Diretório atual: $(pwd)"
echo ""

# 2. Verificar Recursos do Sistema
echo -e "${BLUE}📊 2. RECURSOS DO SISTEMA${NC}"
echo "   Memória:"
free -h | head -2
echo ""
echo "   Disco:"
df -h / | tail -1
echo ""
echo "   CPU:"
nproc --all
echo "   Cores disponíveis: $(nproc --all)"
echo ""

# 3. Verificar Serviços
echo -e "${BLUE}🔄 3. STATUS DOS SERVIÇOS${NC}"

# PostgreSQL
if systemctl is-active --quiet postgresql; then
    echo -e "   ${GREEN}✅ PostgreSQL: ATIVO${NC}"
    echo "      Versão: $(sudo -u postgres psql -c 'SELECT version();' 2>/dev/null | head -3 | tail -1 || echo 'Não foi possível obter versão')"
else
    echo -e "   ${RED}❌ PostgreSQL: INATIVO${NC}"
fi

# MySQL
if systemctl is-active --quiet mysql; then
    echo -e "   ${GREEN}✅ MySQL: ATIVO${NC}"
    echo "      Versão: $(mysql --version 2>/dev/null || echo 'Não foi possível obter versão')"
else
    echo -e "   ${YELLOW}⚠️  MySQL: INATIVO${NC}"
fi

# Nginx
if systemctl is-active --quiet nginx; then
    echo -e "   ${GREEN}✅ Nginx: ATIVO${NC}"
    echo "      Versão: $(nginx -v 2>&1 || echo 'Não foi possível obter versão')"
else
    echo -e "   ${RED}❌ Nginx: INATIVO${NC}"
fi

# PM2
if command -v pm2 &> /dev/null; then
    echo -e "   ${GREEN}✅ PM2: INSTALADO${NC}"
    echo "      Processos ativos: $(pm2 list | grep -c 'online' || echo '0')"
    pm2 list --no-color 2>/dev/null | head -5
else
    echo -e "   ${YELLOW}⚠️  PM2: NÃO INSTALADO${NC}"
fi

echo ""

# 4. Verificar Runtime Node.js
echo -e "${BLUE}⚡ 4. RUNTIME NODE.JS${NC}"
if command -v node &> /dev/null; then
    echo -e "   ${GREEN}✅ Node.js instalado${NC}"
    echo "      Versão: $(node --version)"
else
    echo -e "   ${RED}❌ Node.js não encontrado${NC}"
fi

if command -v npm &> /dev/null; then
    echo -e "   ${GREEN}✅ NPM instalado${NC}"
    echo "      Versão: $(npm --version)"
else
    echo -e "   ${RED}❌ NPM não encontrado${NC}"
fi

if command -v npx &> /dev/null; then
    echo -e "   ${GREEN}✅ NPX disponível${NC}"
else
    echo -e "   ${RED}❌ NPX não encontrado${NC}"
fi

echo ""

# 5. Verificar Arquivos da Aplicação
echo -e "${BLUE}📁 5. ARQUIVOS DA APLICAÇÃO${NC}"

# Verificar se estamos no diretório correto
if [ -f "package.json" ]; then
    echo -e "   ${GREEN}✅ package.json encontrado${NC}"
    echo "      Nome: $(cat package.json | grep '"name"' | cut -d'"' -f4)"
    echo "      Versão: $(cat package.json | grep '"version"' | cut -d'"' -f4)"
else
    echo -e "   ${RED}❌ package.json não encontrado${NC}"
    echo -e "   ${YELLOW}⚠️  Certifique-se de estar no diretório da aplicação${NC}"
fi

if [ -f ".env" ]; then
    echo -e "   ${GREEN}✅ Arquivo .env encontrado${NC}"
    echo "      Tamanho: $(wc -l < .env) linhas"
    echo "      Variáveis principais:"
    grep -E "^(DATABASE_URL|NEXTAUTH_URL|NEXTAUTH_SECRET|NODE_ENV)=" .env | sed 's/=.*/=***/' | sed 's/^/         /'
else
    echo -e "   ${RED}❌ Arquivo .env não encontrado${NC}"
fi

if [ -f "prisma/schema.prisma" ]; then
    echo -e "   ${GREEN}✅ Schema Prisma encontrado${NC}"
else
    echo -e "   ${RED}❌ Schema Prisma não encontrado${NC}"
fi

if [ -f "prisma/dev.db" ]; then
    echo -e "   ${GREEN}✅ Banco SQLite encontrado${NC}"
    echo "      Tamanho: $(du -h prisma/dev.db | cut -f1)"
    echo "      Última modificação: $(stat -c %y prisma/dev.db)"
else
    echo -e "   ${YELLOW}⚠️  Banco SQLite não encontrado${NC}"
fi

if [ -d "node_modules" ]; then
    echo -e "   ${GREEN}✅ Dependências instaladas${NC}"
    echo "      Tamanho: $(du -sh node_modules | cut -f1)"
else
    echo -e "   ${RED}❌ Dependências não instaladas${NC}"
fi

echo ""

# 6. Verificar Portas
echo -e "${BLUE}🌐 6. PORTAS EM USO${NC}"
echo "   Portas relevantes:"
ss -tlnp | grep -E ':(3000|5432|3306|80|443)' | while read line; do
    port=$(echo $line | grep -o ':[0-9]*' | head -1 | cut -c2-)
    case $port in
        3000) echo -e "      ${GREEN}✅ Porta 3000 (Next.js): EM USO${NC}" ;;
        5432) echo -e "      ${GREEN}✅ Porta 5432 (PostgreSQL): EM USO${NC}" ;;
        3306) echo -e "      ${GREEN}✅ Porta 3306 (MySQL): EM USO${NC}" ;;
        80) echo -e "      ${GREEN}✅ Porta 80 (HTTP): EM USO${NC}" ;;
        443) echo -e "      ${GREEN}✅ Porta 443 (HTTPS): EM USO${NC}" ;;
    esac
done

if ! ss -tlnp | grep -q ':3000'; then
    echo -e "      ${YELLOW}⚠️  Porta 3000 (Next.js): LIVRE${NC}"
fi

echo ""

# 7. Verificar Conectividade
echo -e "${BLUE}🔗 7. TESTE DE CONECTIVIDADE${NC}"
if curl -s --max-time 5 http://localhost:3000 > /dev/null; then
    echo -e "   ${GREEN}✅ Aplicação respondendo em localhost:3000${NC}"
else
    echo -e "   ${RED}❌ Aplicação não responde em localhost:3000${NC}"
fi

if curl -s --max-time 5 http://localhost:3000/api/health > /dev/null 2>&1; then
    echo -e "   ${GREEN}✅ API health check: OK${NC}"
else
    echo -e "   ${YELLOW}⚠️  API health check: Não disponível${NC}"
fi

echo ""

# 8. Verificar Logs Recentes
echo -e "${BLUE}📋 8. LOGS RECENTES${NC}"
echo "   Últimas 5 linhas do log do sistema:"
journalctl --no-pager -n 5 | sed 's/^/      /'

if command -v pm2 &> /dev/null; then
    echo ""
    echo "   Últimas 5 linhas dos logs do PM2:"
    pm2 logs --lines 5 --no-color 2>/dev/null | tail -5 | sed 's/^/      /'
fi

echo ""

# 9. Resumo e Recomendações
echo -e "${BLUE}📋 === RESUMO E RECOMENDAÇÕES ===${NC}"

# Verificar problemas críticos
problems=()

if ! command -v node &> /dev/null; then
    problems+=("Node.js não instalado")
fi

if [ ! -f ".env" ]; then
    problems+=("Arquivo .env não encontrado")
fi

if [ ! -f "package.json" ]; then
    problems+=("package.json não encontrado - verifique o diretório")
fi

if [ ! -d "node_modules" ]; then
    problems+=("Dependências não instaladas - execute npm install")
fi

if ! ss -tlnp | grep -q ':3000'; then
    problems+=("Aplicação não está rodando na porta 3000")
fi

if [ ${#problems[@]} -eq 0 ]; then
    echo -e "${GREEN}✅ Sistema aparenta estar funcionando corretamente!${NC}"
    echo ""
    echo -e "${BLUE}🔧 Próximos passos recomendados:${NC}"
    echo "   1. Testar endpoint: curl http://localhost:3000/api/test-db"
    echo "   2. Executar seed: curl -X POST http://localhost:3000/api/seed"
    echo "   3. Monitorar logs: pm2 logs ou journalctl -f"
else
    echo -e "${RED}❌ Problemas encontrados:${NC}"
    for problem in "${problems[@]}"; do
        echo -e "   ${RED}• $problem${NC}"
    done
    
    echo ""
    echo -e "${BLUE}🔧 Ações recomendadas:${NC}"
    echo "   1. Corrigir os problemas listados acima"
    echo "   2. Executar: npm install (se necessário)"
    echo "   3. Configurar variáveis de ambiente no .env"
    echo "   4. Iniciar aplicação: npm run dev ou pm2 start"
    echo "   5. Executar migrações: npx prisma migrate deploy"
fi

echo ""
echo -e "${BLUE}📄 Para diagnóstico detalhado do banco, execute:${NC}"
echo "   node debug-seed.js"
echo ""
echo -e "${BLUE}📚 Consulte também:${NC}"
echo "   - TROUBLESHOOTING_SEED_VPS.md"
echo "   - Logs da aplicação: pm2 logs"
echo "   - Logs do sistema: journalctl -f"
echo ""
echo "=== Diagnóstico concluído ==="