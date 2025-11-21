# 📦 Guia de Instalação do Time Tracker

## ✅ O que Foi Corrigido

Este pacote contém a versão **CORRIGIDA** do Time Tracker com os seguintes problemas resolvidos:

### 1. **Erros de Caminho de Arquivos**
- ❌ **Antes:** `require_once __DIR__ . '/../admin/auth_check.php';`
- ✅ **Depois:** `require_once __DIR__ . '/includes/auth_check.php';`

### 2. **Incompatibilidade com Banco de Dados**
- ❌ **Antes:** API tentava acessar colunas `project_name` e `client_name`
- ✅ **Depois:** API usa a coluna correta `title` da tabela `dash_projects`

### 3. **Arquivos de Configuração Ausentes**
- ✅ Criados: `database.php`, `dash_database.php`, `dash_functions.php`
- ✅ Todas as funções auxiliares implementadas

### 4. **Estrutura de Pastas Corrigida**
- ✅ Estrutura agora segue o padrão do servidor
- ✅ JavaScript no local correto: `/vision/assets/js/`

## 📂 Estrutura do Pacote

```
time_tracker/
├── 📄 time-tracker.php              ← Interface principal (CORRIGIDO)
├── 📄 api_time_tracker.php          ← API backend (CORRIGIDO)
├── 📄 test_installation.php         ← Script de verificação
├── 📄 README.md                     ← Documentação completa
├── 📄 INSTALLATION_GUIDE.md         ← Este arquivo
│
├── 📁 includes/
│   └── auth_check.php              ← Autenticação (CORRIGIDO)
│
├── 📁 config/
│   ├── database.php                ← Conexão DB + funções auth
│   ├── dash_database.php           ← Compatibilidade
│   └── dash_functions.php          ← Funções auxiliares
│
├── 📁 vision/assets/js/
│   └── time-tracker-v2.js          ← JavaScript frontend (SEM ERROS)
│
└── 📁 sql/
    └── create_time_tracker_tables.sql  ← Script de criação das tabelas
```

## 🚀 Instalação Rápida (5 Passos)

### Passo 1: Fazer Upload dos Arquivos

**No servidor, crie esta estrutura:**

```
/v/dash-t101/
├── time-tracker.php
├── api_time_tracker.php
├── test_installation.php
├── includes/
│   └── auth_check.php
└── config/
    ├── database.php
    ├── dash_database.php
    └── dash_functions.php

/vision/assets/js/
└── time-tracker-v2.js
```

**Via FTP/SFTP:**
```bash
# Copie todos os arquivos mantendo a estrutura de pastas
# Certifique-se de que time-tracker-v2.js vai para /vision/assets/js/
```

### Passo 2: Criar as Tabelas

**Via phpMyAdmin:**
1. Acesse phpMyAdmin
2. Selecione o banco `u335416710_t101_db`
3. Vá em "SQL"
4. Cole o conteúdo de `sql/create_time_tracker_tables.sql`
5. Clique em "Executar"

**Via Terminal SSH:**
```bash
mysql -u u335416710_t101 -p u335416710_t101_db < sql/create_time_tracker_tables.sql
```

### Passo 3: Verificar Credenciais

Abra `config/database.php` e confirme:

```php
$host = 'localhost';
$db   = 'u335416710_t101_db';
$user = 'u335416710_t101';
$pass = 'Pa392ap!';  // ← Verifique se está correto
```

### Passo 4: Testar a Instalação

Acesse: `https://v.translators101.com/dash-t101/test_installation.php`

Você verá uma página com verificações automáticas:
- ✅ Arquivos presentes
- ✅ Conexão com banco de dados
- ✅ Tabelas criadas
- ✅ Funções definidas
- ✅ Permissões corretas

### Passo 5: Acessar o Time Tracker

Acesse: `https://v.translators101.com/dash-t101/time-tracker.php`

## 🧪 Testando as Funcionalidades

### Teste 1: Carregar Projetos
1. Abra a página do Time Tracker
2. Abra o Console do navegador (F12)
3. Verifique se há logs `[TT] loadProjects()`
4. Verifique se os projetos aparecem no seletor

### Teste 2: Criar Projeto Rápido
1. Clique no botão **"+"** roxo
2. Digite um nome de projeto
3. Clique em "Criar e Selecionar"
4. **Resultado esperado:** Modal fecha e projeto aparece selecionado

### Teste 3: Iniciar/Parar Timer
1. Selecione um projeto
2. Digite uma descrição (opcional)
3. Clique em **"Iniciar"**
4. Aguarde alguns segundos
5. Clique em **"Parar"**
6. **Resultado esperado:** Registro aparece no histórico

### Teste 4: Ver Histórico
1. Role para baixo até "Registros Recentes"
2. **Resultado esperado:** Você vê os registros de tempo
3. Clique em "Atualizar" para recarregar

## 🐛 Problemas Comuns e Soluções

### ❌ Erro: "Usuário não autenticado"

**Causa:** Você não está logado no sistema

**Solução:**
1. Faça login no site Translators 101
2. Depois acesse o Time Tracker

**Solução Temporária (Apenas para Testes):**
Edite `time-tracker.php` e comente a linha:
```php
// requireAuth();  // ← Comentado temporariamente
```

### ❌ Erro: "Erro ao carregar projetos"

**Causa:** Tabelas não foram criadas ou credenciais incorretas

**Solução:**
1. Execute o script de criação de tabelas (Passo 2)
2. Verifique as credenciais em `config/database.php`
3. Execute `test_installation.php` para verificar

### ❌ Erro 404: time-tracker-v2.js

**Causa:** Arquivo JavaScript não está no local correto

**Solução:**
1. Confirme que o arquivo está em `/vision/assets/js/time-tracker-v2.js`
2. Verifique permissões: `chmod 644 time-tracker-v2.js`
3. Limpe o cache do navegador (Ctrl+Shift+R)

### ❌ Modal não abre

**Causa:** Erro de JavaScript ou cache

**Solução:**
1. Abra o Console (F12)
2. Procure por erros em vermelho
3. Limpe o cache: Ctrl+Shift+Del → Limpar cache
4. Recarregue: Ctrl+Shift+R

### ❌ Erro: "Invalid or unexpected token"

**Causa:** Arquivo JavaScript com caracteres escapados incorretamente

**Solução:**
✅ Este problema **JÁ FOI CORRIGIDO** no `time-tracker-v2.js` fornecido
- Se ainda ocorrer, substitua o arquivo pelo fornecido neste pacote

## 📊 Verificando o Banco de Dados

### Ver tabelas criadas
```sql
SHOW TABLES LIKE 'time_%';
```

**Resultado esperado:**
- `time_tasks`
- `time_entries`

### Ver estrutura das tabelas
```sql
DESCRIBE time_tasks;
DESCRIBE time_entries;
```

### Verificar se há projetos
```sql
SELECT id, title, status FROM dash_projects LIMIT 5;
```

## 🔧 Configurações Avançadas

### Desabilitar Logs de Debug

No `api_time_tracker.php`, remova ou comente:
```php
// error_log("==== API TIME TRACKER CHAMADA ====");
// error_log("Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'CLI'));
```

### Customizar Timeout de Sessão

Em `config/database.php`:
```php
ini_set('session.gc_maxlifetime', 3600); // 1 hora
```

### Adicionar Validação Extra

Em `time-tracker.php`, descomente:
```php
requireAuth();  // Exigir login
requireSubscriber();  // Exigir assinatura
```

## 📝 Checklist de Instalação

Use esta lista para garantir que tudo foi feito:

- [ ] Todos os arquivos foram enviados
- [ ] Estrutura de pastas está correta
- [ ] JavaScript está em `/vision/assets/js/`
- [ ] SQL foi executado (tabelas criadas)
- [ ] Credenciais do banco estão corretas
- [ ] `test_installation.php` mostra todos os checks verdes
- [ ] Página `time-tracker.php` carrega sem erros
- [ ] Console não mostra erros de JavaScript
- [ ] Consigo criar um projeto de teste
- [ ] Consigo iniciar e parar o timer
- [ ] Registros aparecem no histórico

## 🎯 Próximos Passos Após Instalação

1. **Adicionar CSS Customizado** (se necessário)
   - Crie ou edite `/vision/assets/css/time-tracker.css`
   
2. **Criar Página de Relatórios**
   - Implemente `report_time_tracker.php`
   
3. **Testar com Usuários Reais**
   - Peça feedback
   - Ajuste conforme necessário

4. **Backups**
   - Configure backups automáticos do banco de dados
   - Especialmente das tabelas `time_*`

## 📞 Suporte

Se encontrar problemas:

1. **Verifique os logs**
   - PHP: `/var/log/php_errors.log`
   - Apache: `/var/log/apache2/error.log`

2. **Console do navegador**
   - Pressione F12
   - Aba "Console"
   - Procure erros em vermelho

3. **Execute test_installation.php**
   - Mostra status detalhado da instalação

4. **Consulte o README.md**
   - Documentação completa

---

## ✨ Diferenças desta Versão

**ANTES** (Versão com problemas):
- ❌ Erros de caminho de arquivos
- ❌ Colunas do banco incompatíveis
- ❌ Arquivos de configuração ausentes
- ❌ Erro de sintaxe no JavaScript

**AGORA** (Versão corrigida):
- ✅ Todos os caminhos corretos
- ✅ Compatível com dash_projects (coluna `title`)
- ✅ Todos os arquivos de configuração criados
- ✅ JavaScript sem erros de sintaxe
- ✅ Estrutura de pastas correta
- ✅ Script de teste incluído
- ✅ Documentação completa

---

**Versão:** 2.0 (Corrigida)  
**Data:** 20/11/2024  
**Status:** ✅ Pronto para Produção  
**Testado:** Sim  
**Desenvolvido para:** Translators 101
