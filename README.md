# 📚 Gerenciador de Estudos para Concurso

Um sistema completo para organizar estudos de concurso público com blocos temáticos, sistema de revisão espaçada (SRS), timer Pomodoro e métricas de progresso.

## 🚀 Funcionalidades

- **Dashboard com Métricas**: Progresso geral, horas estudadas, revisões pendentes
- **Gestão de Blocos e Tópicos**: CRUD completo com filtros e busca
- **Itens de Estudo**: Resumos, questões, leis e vídeos organizados
- **Sistema Pomodoro**: Timer para sessões de estudo focado
- **Revisão SRS**: Sistema de repetição espaçada para fixação
- **Métricas Avançadas**: Acompanhamento detalhado do progresso

## 🛠️ Stack Tecnológica

- **Frontend**: Next.js 15 (App Router), React 19, TypeScript
- **Styling**: Tailwind CSS 4, Shadcn/UI
- **Backend**: Next.js API Routes
- **Banco de Dados**: Prisma + SQLite
- **Validação**: Zod
- **Deploy**: Netlify

## 📦 Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd estudo-concurso
```

2. Instale as dependências:
```bash
npm install
```

3. Configure o banco de dados:
```bash
npm run db:generate
npm run db:migrate
```

4. Popule com dados iniciais (opcional):
```bash
npm run db:seed
```

5. Execute em desenvolvimento:
```bash
npm run dev
```

## 🗃️ Estrutura do Banco

### Modelos Principais

- **Block**: Blocos temáticos (ex: Direito Constitucional)
- **Topic**: Tópicos dentro dos blocos (ex: Princípios Fundamentais)
- **StudyItem**: Itens de estudo (resumos, questões, leis, vídeos)
- **StudySession**: Sessões de estudo com timer
- **Review**: Revisões do sistema SRS

### Enums

- **ItemKind**: SUMMARY, QUESTION, LAW, VIDEO
- **ItemStatus**: PENDING, COMPLETED, REVIEWING
- **TopicStatus**: PLANNED, STUDYING, COMPLETED

## 🌐 Deploy no Netlify

### Configuração Automática

O projeto já está configurado para deploy no Netlify com:

- `netlify.toml` com configurações otimizadas
- Plugin oficial do Next.js (`@netlify/plugin-nextjs`)
- Redirecionamentos para SPA e API routes
- Headers de segurança
- Cache otimizado para assets estáticos

### Passos para Deploy

1. **Conecte seu repositório ao Netlify**:
   - Acesse [netlify.com](https://netlify.com)
   - Clique em "New site from Git"
   - Conecte seu repositório GitHub/GitLab

2. **Configurações de Build** (já configuradas no `netlify.toml`):
   - Build command: `prisma generate && npm run build`
   - Publish directory: `.next`
   - Node version: 18

3. **Variáveis de Ambiente**:
   ```
   DATABASE_URL=file:./dev.db
   NODE_ENV=production
   ```

4. **Deploy**:
   - O deploy será automático a cada push na branch principal
   - Primeira build pode demorar alguns minutos

### Troubleshooting

- **Erro de build**: Verifique se todas as dependências estão no `package.json`
- **Erro de Prisma**: Certifique-se que `prisma generate` está no build command
- **Erro 404**: Verifique os redirecionamentos no `netlify.toml`

## 📱 Páginas Principais

- `/` - Dashboard com métricas e ações rápidas
- `/blocos` - Lista e gestão de blocos de estudo
- `/blocos/[id]` - Detalhes do bloco com tópicos
- `/topicos/[id]` - Detalhes do tópico com itens
- `/itens` - Gestão completa de itens de estudo
- `/estudo` - Timer Pomodoro para sessões
- `/revisao` - Fila de revisão SRS

## 🎯 Comandos Úteis

```bash
# Desenvolvimento
npm run dev              # Servidor de desenvolvimento
npm run build            # Build de produção
npm run start            # Servidor de produção

# Banco de Dados
npm run db:generate      # Gerar cliente Prisma
npm run db:migrate       # Executar migrações
npm run db:studio        # Interface visual do banco
npm run db:seed          # Popular com dados iniciais

# Qualidade
npm run lint             # Verificar código
```

## 📊 Métricas Disponíveis

- **Progresso Geral**: Percentual de conclusão do edital
- **Horas Estudadas**: Total e por período
- **Taxa de Sucesso**: Percentual de acertos nas revisões
- **Sequência de Estudos**: Dias consecutivos estudando
- **Distribuição de Notas**: Análise das avaliações SRS
- **Atividade por Dia**: Sessões e tempo por data

## 🔄 Sistema SRS

O sistema de repetição espaçada utiliza o algoritmo SM-2 simplificado:

- **Notas 0-2**: Item volta para revisão em 1 dia
- **Notas 3-4**: Intervalo moderado (2-4 dias)
- **Nota 5**: Intervalo máximo baseado no fator de facilidade

Cada revisão ajusta o fator de facilidade e o próximo intervalo automaticamente.

## 🎨 Customização

### Cores dos Blocos
Edite as cores no seed ou diretamente no banco:
```typescript
const colors = [
  '#3B82F6', // Azul
  '#10B981', // Verde
  '#F59E0B', // Amarelo
  '#EF4444', // Vermelho
  '#8B5CF6', // Roxo
  '#06B6D4', // Ciano
];
```

### Timer Pomodoro
Ajuste os tempos no componente de estudo:
```typescript
const WORK_TIME = 25 * 60; // 25 minutos
const BREAK_TIME = 5 * 60;  // 5 minutos
```

## 📄 Licença

Este projeto está sob a licença ISC.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

---

**Desenvolvido para otimizar seus estudos para concurso público! 📚✨**