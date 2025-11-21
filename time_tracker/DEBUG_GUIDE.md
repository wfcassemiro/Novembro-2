# 🐛 Guia de Debug - Time Tracker

## 🚀 Como Usar os Arquivos de Debug

### Passo 1: Fazer Upload dos Arquivos de Debug

Faça upload destes arquivos adicionais para o servidor:

```
/dash-t101/
├── api_time_tracker_debug.php   ← API com logs extensivos
└── view_logs.php                ← Visualizador de logs (interface web)
```

### Passo 2: Ativar Modo Debug no time-tracker.php

O arquivo `time-tracker.php` já está configurado para usar a API debug.

Verifique se esta linha está presente:

```javascript
window.API_URL = '/dash-t101/api_time_tracker_debug.php';
```

### Passo 3: Abrir o Visualizador de Logs

Em uma nova aba, acesse:

```
https://v.translators101.com/dash-t101/view_logs.php
```

Esta página:
- ✅ Atualiza automaticamente a cada 3 segundos
- ✅ Mostra todos os logs em tempo real
- ✅ Destaca erros em vermelho
- ✅ Mostra estatísticas (erros, sucessos, linhas)

### Passo 4: Usar o Time Tracker

Com o visualizador de logs aberto, vá para:

```
https://v.translators101.com/dash-t101/time-tracker.php
```

Tente as ações que estão dando erro:
1. Carregar projetos
2. Criar um novo projeto
3. Iniciar o timer

### Passo 5: Analisar os Logs

No visualizador, você verá logs detalhados de TUDO que acontece:

#### Logs que você deve ver:

**Ao carregar a página:**
```
[2024-11-20 22:00:00] ===== API TIME TRACKER DEBUG INICIADA =====
[2024-11-20 22:00:00] Método: GET
[2024-11-20 22:00:00] Action recebida: project_list
[2024-11-20 22:00:00] User ID da sessão: seu-user-id-aqui
[2024-11-20 22:00:00] ✅ database.php incluído com sucesso
[2024-11-20 22:00:00] ✅ Conexão PDO funcionando
```

**Se tudo estiver OK:**
```
[2024-11-20 22:00:01] Projetos encontrados: 5
[2024-11-20 22:00:01] Retornando projetos processados: 5
```

**Se houver erro, você verá:**
```
[2024-11-20 22:00:01] ❌ ERRO PDO
    message: SQLSTATE[42S02]: Base table or view not found...
    file: /path/to/api_time_tracker_debug.php
    line: 234
```

---

## 🔍 Problemas Comuns e Como Identificar

### Problema 1: "Usuário não autenticado"

**No log você verá:**
```
User ID da sessão: NULL
❌ User ID não definido - retornando erro
```

**Solução:**
- Faça login no sistema primeiro
- Ou temporariamente desabilite a verificação de autenticação

**Para desabilitar autenticação (APENAS PARA TESTES):**

Em `api_time_tracker_debug.php`, comente estas linhas:

```php
// if (!$user_id) {
//     debugLog("❌ User ID não definido - retornando erro");
//     die(json_encode([...]));
// }

// E adicione isto para usar um user_id fake:
if (!$user_id) {
    $user_id = 'debug-user'; // USER ID FAKE PARA TESTES
    debugLog("⚠️ Usando user_id fake para debug: $user_id");
}
```

### Problema 2: "Tabela não existe"

**No log você verá:**
```
❌ Tabela dash_projects NÃO EXISTE
```

**Solução:**
Execute o SQL para criar a tabela:
```bash
mysql -u user -p database < sql/create_time_tracker_tables.sql
```

### Problema 3: "Erro de conexão com banco"

**No log você verá:**
```
❌ ERRO ao incluir database.php
    message: SQLSTATE[HY000] [1045] Access denied...
```

**Solução:**
Verifique as credenciais em `config/database.php`:
```php
$host = 'localhost';
$db   = 'u335416710_t101_db';  // ← Correto?
$user = 'u335416710_t101';     // ← Correto?
$pass = 'Pa392ap!';            // ← Correto?
```

### Problema 4: "Query SQL com erro"

**No log você verá:**
```
❌ ERRO PDO
    message: SQLSTATE[42S22]: Column not found: Unknown column 'title'...
```

**Solução:**
A coluna não existe. Verifique a estrutura da tabela:
```sql
DESCRIBE dash_projects;
```

### Problema 5: "Função não definida"

**No log você verá:**
```
❌ ERRO GENÉRICO
    message: Call to undefined function formatDuration()...
```

**Solução:**
A função não foi carregada. Verifique se `dash_functions.php` foi incluído.

---

## 📊 Entendendo os Logs

### Estrutura de um Log

```
[2024-11-20 22:00:00] Mensagem do log
    Dados adicionais (se houver)
    em formato estruturado
--------------------------------------------------------------------------------
```

### Símbolos Usados

- `✅` - Operação bem-sucedida
- `❌` - Erro encontrado
- `⚠️` - Aviso/Atenção
- `=====` - Seção importante

### Seções do Log

1. **INICIADA** - Informações da requisição (método, URI, GET, POST)
2. **Sessão** - Dados da sessão do usuário
3. **Includes** - Carregamento dos arquivos PHP
4. **ACTION** - Processamento da ação específica
5. **ERRO** - Detalhes completos do erro (se houver)
6. **FIM** - Conclusão da execução

---

## 🛠️ Comandos Úteis

### Ver logs via Terminal (SSH)

```bash
# Ver logs em tempo real
tail -f /tmp/time_tracker_debug.log

# Ver últimas 50 linhas
tail -50 /tmp/time_tracker_debug.log

# Procurar por erros
grep "❌" /tmp/time_tracker_debug.log

# Limpar logs
echo "" > /tmp/time_tracker_debug.log
```

### Verificar Permissões

```bash
# Verificar se o arquivo de log pode ser criado
touch /tmp/time_tracker_debug.log
ls -la /tmp/time_tracker_debug.log

# Ajustar permissões se necessário
chmod 666 /tmp/time_tracker_debug.log
```

---

## 🎯 Fluxo de Debug Recomendado

1. **Abrir visualizador de logs** (`view_logs.php`)
2. **Limpar logs antigos** (botão "Limpar Logs")
3. **Executar ação no Time Tracker** que está com problema
4. **Voltar ao visualizador** e analisar os logs
5. **Identificar o erro** específico
6. **Aplicar solução** baseada nos logs
7. **Testar novamente**

---

## 🚨 Problemas com os Arquivos de Debug

### Logs não aparecem em view_logs.php

**Verificar:**
1. Arquivo `/tmp/time_tracker_debug.log` existe?
2. Servidor tem permissão de escrita em `/tmp/`?
3. API debug está sendo chamada? (verifique console do navegador)

**Solução:**
```bash
# Criar arquivo manualmente
sudo touch /tmp/time_tracker_debug.log
sudo chmod 666 /tmp/time_tracker_debug.log
```

### API debug não é chamada

**Verificar:**
1. `time-tracker.php` tem `window.API_URL = '.../api_time_tracker_debug.php'`?
2. Arquivo `api_time_tracker_debug.php` foi enviado para o servidor?
3. Cache do navegador? (Ctrl+Shift+R para forçar reload)

---

## 🔄 Voltando ao Normal

Após resolver o problema:

### 1. No time-tracker.php

Mude de volta para a API normal:

```javascript
// De:
window.API_URL = '/dash-t101/api_time_tracker_debug.php';

// Para:
window.API_URL = '/dash-t101/api_time_tracker.php';
```

### 2. Remover logs

```bash
rm /tmp/time_tracker_debug.log
```

### 3. (Opcional) Remover arquivos de debug

```bash
rm /dash-t101/api_time_tracker_debug.php
rm /dash-t101/view_logs.php
```

---

## 📝 Reportando Problemas

Se ainda não conseguir resolver, colete estas informações:

1. **Logs completos** de `/tmp/time_tracker_debug.log`
2. **Screenshot** do erro no navegador
3. **Console do navegador** (F12 → Console → erros em vermelho)
4. **Versão do PHP**: `php -v`
5. **Estrutura da tabela**: `DESCRIBE dash_projects;`

---

## 💡 Dicas

- ✅ Mantenha `view_logs.php` aberto em outra aba
- ✅ Limpe os logs antes de cada teste
- ✅ Anote os erros que aparecem
- ✅ Teste uma ação de cada vez
- ✅ Verifique o console do navegador também (F12)

---

**Versão:** 2.0 Debug  
**Arquivo de Logs:** `/tmp/time_tracker_debug.log`  
**Visualizador:** `view_logs.php`
