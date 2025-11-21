# Changelog - Time Tracker

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [2.0] - 2024-11-20 - VERSÃO CORRIGIDA

### 🔧 Correções Críticas

#### time-tracker.php
- **Corrigido:** Caminho incorreto do auth_check.php
  - Antes: `require_once __DIR__ . '/../admin/auth_check.php';`
  - Depois: `require_once __DIR__ . '/includes/auth_check.php';`
- **Removido:** Código de debug excessivo
- **Simplificado:** Script inline reduzido ao essencial
- **Adicionado:** Cache busting automático no JS (`?v=timestamp`)

#### api_time_tracker.php
- **Corrigido:** Compatibilidade com tabela dash_projects
  - Mudado: `project_name` → `title` (coluna real no BD)
  - Mudado: `client_name` → `client_info` (campo calculado)
- **Corrigido:** Caminhos dos requires
  - Antes: `__DIR__ . '/../config/database.php'`
  - Depois: `__DIR__ . '/config/database.php'`
- **Mantido:** Todos os logs de debug para diagnóstico
- **Ajustado:** Resposta JSON para incluir todos os campos necessários

#### auth_check.php
- **Criado:** Arquivo estava ausente
- **Implementado:** Lógica de verificação de autenticação
- **Incluído:** Carregamento automático de database.php
- **Simplificado:** Código mais limpo e eficiente

### 📁 Arquivos Novos Criados

#### config/database.php
- Configuração completa do banco de dados
- Todas as funções de autenticação (`isLoggedIn`, `isAdmin`, etc.)
- Configuração de sessões para .translators101.com
- Funções específicas do Time Tracker

#### config/dash_database.php
- Cópia do database.php para compatibilidade
- Garante que o sistema funcione com múltiplos includes

#### config/dash_functions.php
- Funções auxiliares do dashboard
- `sanitize()`, `formatCurrency()`, `formatDateBR()`
- `getStatusLabel()`, `getStatusColor()`
- Funções de formatação e helper

#### sql/create_time_tracker_tables.sql
- Script completo para criar tabelas necessárias
- `time_tasks` com chaves estrangeiras
- `time_entries` com todos os campos necessários
- Índices para performance

#### test_installation.php
- Script de verificação automática
- Testa arquivos, banco de dados, funções
- Interface web com status visual
- Guia de próximos passos

### 📄 Documentação

#### README.md
- Documentação completa do sistema
- Instruções de instalação passo a passo
- Resolução de problemas comuns
- Estrutura do banco de dados

#### INSTALLATION_GUIDE.md
- Guia rápido de instalação (5 passos)
- Checklist de verificação
- Problemas comuns e soluções
- Testes de funcionalidades

#### CHANGELOG.md
- Este arquivo
- Histórico de mudanças
- Versões e correções

### 🐛 Bugs Corrigidos

1. **Erro de Sintaxe JavaScript**
   - Problema: 58 ocorrências de `\"` dentro de template literals
   - Solução: Removidas todas as barras invertidas desnecessárias
   - Arquivo: `vision/assets/js/time-tracker-v2.js`

2. **Erro "Failed to open stream"**
   - Problema: Caminhos incorretos nos requires
   - Solução: Ajustados todos os caminhos relativos
   - Arquivos: `time-tracker.php`, `api_time_tracker.php`, `auth_check.php`

3. **Erro "project_name doesn't exist"**
   - Problema: API tentava acessar coluna inexistente
   - Solução: Mudado para `title` (coluna real)
   - Arquivo: `api_time_tracker.php`

4. **Arquivos de Configuração Ausentes**
   - Problema: Sistema dependia de arquivos que não existiam
   - Solução: Criados todos os arquivos necessários
   - Arquivos: `database.php`, `dash_database.php`, `dash_functions.php`

5. **Modal Não Funcionava**
   - Problema: Função `openQuickProjectModal` não era encontrada
   - Solução: JavaScript agora carrega corretamente sem erros de sintaxe
   - Arquivo: `time-tracker-v2.js`

### ✨ Melhorias

- **Performance:** Índices adicionados nas tabelas para queries mais rápidas
- **Segurança:** Uso de prepared statements em todas as queries
- **UX:** Mensagens de erro mais claras e informativas
- **Debug:** Logs estruturados para facilitar troubleshooting
- **Manutenibilidade:** Código mais limpo e documentado

### 📊 Estrutura do Banco de Dados

#### Tabelas Novas
- `time_tasks` - Tarefas dentro dos projetos
- `time_entries` - Registros de tempo

#### Tabelas Existentes Usadas
- `dash_projects` - Projetos do dashboard (integrado)
- `users` - Usuários do sistema

#### Chaves Estrangeiras
- `time_tasks.project_id` → `dash_projects.id`
- `time_tasks.user_id` → `users.id`
- `time_entries.project_id` → `dash_projects.id`
- `time_entries.task_id` → `time_tasks.id`
- `time_entries.user_id` → `users.id`

### 🎯 Funcionalidades Implementadas

- ✅ Timer de contagem regressiva em tempo real
- ✅ Criar projetos rapidamente via modal
- ✅ Associar tarefas aos projetos
- ✅ Pausar e retomar cronômetro
- ✅ Histórico de registros
- ✅ Filtrar por projeto
- ✅ Deletar registros
- ✅ Integração com dash_projects existente

### 🔄 Migrações Necessárias

Se você está atualizando de uma versão anterior:

1. **Execute o SQL:**
   ```bash
   mysql -u user -p database < sql/create_time_tracker_tables.sql
   ```

2. **Substitua todos os arquivos:**
   - Faça backup da versão antiga
   - Copie todos os arquivos novos
   - Mantenha a estrutura de pastas

3. **Verifique credenciais:**
   - Edite `config/database.php`
   - Confirme usuário, senha e banco de dados

4. **Teste a instalação:**
   - Acesse `test_installation.php`
   - Verifique todos os checks

### 📝 Notas de Atualização

- **Compatibilidade:** Versão 2.0 é incompatível com versões anteriores
- **Dados:** Registros de tempo anteriores serão preservados se as tabelas já existirem
- **Sessões:** Sistema usa sessões PHP padrão do site
- **Cache:** Pode ser necessário limpar cache do navegador

### 🚀 Próximas Versões Planejadas

#### [2.1] - Em Desenvolvimento
- [ ] Página de relatórios (`report_time_tracker.php`)
- [ ] Exportação para CSV/PDF
- [ ] Gráficos de produtividade
- [ ] Metas de tempo por projeto
- [ ] Notificações toast (em vez de alerts)

#### [2.2] - Futuro
- [ ] Integração com calendário
- [ ] Timer em segundo plano (Service Worker)
- [ ] App mobile (PWA)
- [ ] Sincronização offline

### 🐞 Problemas Conhecidos

Nenhum problema crítico conhecido nesta versão.

### 🙏 Agradecimentos

- Equipe Translators 101 pelo feedback
- Testadores beta pela paciência

---

## [1.0] - 2024-11-19 - VERSÃO INICIAL (COM PROBLEMAS)

### 📦 Lançamento Inicial
- Interface básica do Time Tracker
- API com endpoints principais
- Integração inicial com dash_projects

### ⚠️ Problemas Identificados
- Erros de caminho nos requires
- Incompatibilidade com estrutura do banco
- Arquivos de configuração ausentes
- Erro de sintaxe no JavaScript
- Modal não funcionava

**Status:** Obsoleta - Use a versão 2.0

---

**Formato:** [Versão] - Data - Descrição  
**Convenção:** Versionamento Semântico (MAJOR.MINOR.PATCH)
