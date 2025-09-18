# 🔧 Correção do Erro de Build na VPS

## ❌ Problema Identificado

O erro `DATABASE_URL environment variable is not defined` ocorre porque:
- A VPS não possui um arquivo `.env` com as variáveis de ambiente necessárias
- O Next.js precisa das variáveis durante o processo de build
- O NextAuth.js está tentando acessar a configuração do banco durante a compilação

## ✅ Solução Rápida

### 1. Criar arquivo .env na VPS

Na VPS, no diretório do projeto (`/var/www/otavio-edital`), execute:

```bash
# Navegar para o diretório do projeto
cd /var/www/otavio-edital

# Criar arquivo .env básico
cat > .env << 'EOF'
# Database
DATABASE_URL="file:./prisma/production.db"

# NextAuth.js
NEXTAUTH_URL="http://localhost:3000"
NEXTAUTH_SECRET="$(openssl rand -base64 32)"

# OAuth (configure depois)
GOOGLE_CLIENT_ID="placeholder"
GOOGLE_CLIENT_SECRET="placeholder"
GITHUB_ID="placeholder"
GITHUB_SECRET="placeholder"

# Environment
NODE_ENV="production"
EOF
```

### 2. Executar o script automatizado

Alternativamente, use o script que criamos:

```bash
# Dar permissão de execução
chmod +x setup-vps-env.sh

# Executar o script
./setup-vps-env.sh
```

### 3. Testar o build

```bash
# Tentar o build novamente
npm run build
```

## 🔧 Solução Detalhada

### Arquivos Criados

1. **`.env.production`** - Template com configurações para produção
2. **`setup-vps-env.sh`** - Script automatizado para configurar ambiente
3. **`VPS_BUILD_FIX.md`** - Este documento com instruções

### Configurações Importantes

#### DATABASE_URL
```bash
# Para SQLite em produção
DATABASE_URL="file:./prisma/production.db"

# Para PostgreSQL (se preferir)
# DATABASE_URL="postgresql://user:password@localhost:5432/database"
```

#### NEXTAUTH_SECRET
```bash
# Gerar uma chave segura
NEXTAUTH_SECRET="$(openssl rand -base64 32)"
```

#### NEXTAUTH_URL
```bash
# Para desenvolvimento local na VPS
NEXTAUTH_URL="http://localhost:3000"

# Para domínio público (quando configurar)
# NEXTAUTH_URL="https://seu-dominio.com"
```

## 🚀 Próximos Passos

### 1. Configurar OAuth (Opcional)

Se quiser usar autenticação social:

```bash
# Google OAuth
GOOGLE_CLIENT_ID="seu-client-id-real"
GOOGLE_CLIENT_SECRET="seu-client-secret-real"

# GitHub OAuth
GITHUB_ID="seu-github-id-real"
GITHUB_SECRET="seu-github-secret-real"
```

### 2. Configurar Banco de Dados

```bash
# Criar diretório se não existir
mkdir -p prisma

# Executar migrações
npx prisma migrate deploy

# Gerar cliente Prisma
npx prisma generate
```

### 3. Testar Aplicação

```bash
# Build
npm run build

# Iniciar em produção
npm start

# Ou em desenvolvimento
npm run dev
```

## 🔍 Verificações

### Verificar se .env existe
```bash
ls -la .env
cat .env
```

### Verificar variáveis carregadas
```bash
node -e "console.log(process.env.DATABASE_URL)"
```

### Verificar banco de dados
```bash
npx prisma db push
npx prisma studio
```

## ⚠️ Troubleshooting

### Se ainda der erro:

1. **Verificar permissões**:
   ```bash
   chmod 644 .env
   chown www-data:www-data .env
   ```

2. **Verificar sintaxe do .env**:
   - Não usar espaços ao redor do `=`
   - Usar aspas para valores com espaços
   - Uma variável por linha

3. **Verificar se o Next.js carrega o .env**:
   ```bash
   # Adicionar debug no next.config.js
   console.log('DATABASE_URL:', process.env.DATABASE_URL)
   ```

4. **Usar variáveis de ambiente do sistema**:
   ```bash
   export DATABASE_URL="file:./prisma/production.db"
   npm run build
   ```

## 📝 Notas Importantes

- ✅ O arquivo `.env` deve estar no diretório raiz do projeto
- ✅ Nunca commitar o `.env` no git (já está no .gitignore)
- ✅ Usar valores reais em produção
- ✅ Manter o `NEXTAUTH_SECRET` seguro e único
- ✅ Configurar HTTPS em produção para OAuth

## 🎯 Resultado Esperado

Após seguir estas instruções:
- ✅ Build do Next.js deve funcionar sem erros
- ✅ Aplicação deve iniciar corretamente
- ✅ Banco de dados deve estar acessível
- ✅ Autenticação deve funcionar (se configurada)