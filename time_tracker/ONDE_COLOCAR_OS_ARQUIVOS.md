# 📁 ONDE COLOCAR OS ARQUIVOS - Guia Visual

## ⚠️ PROBLEMA ATUAL

Você está recebendo **erro 404** porque os arquivos **NÃO ESTÃO NO SERVIDOR**.

O erro mostra:
```
GET https://v.translators101.com/dash-t101/api_time_tracker_debug.php 404 (Not Found)
```

Isso significa: **O arquivo não existe neste caminho!**

---

## ✅ SOLUÇÃO: Colocar Arquivos nos Locais Corretos

### Opção 1: Descobrir Onde Está o time-tracker.php Atual

**Passo 1:** Acesse este arquivo no servidor:
```
https://v.translators101.com/dash-t101/CHECK_FILES.php
```

**Passo 2:** Esta página vai mostrar:
- ✅ Onde o arquivo atual está localizado
- ✅ Quais arquivos estão faltando
- ✅ Onde você precisa fazer upload
- ✅ Links para testar

---

## 📂 Estrutura Esperada no Servidor

Os arquivos devem estar **NO MESMO DIRETÓRIO** onde está o `time-tracker.php`:

```
/caminho/no/servidor/dash-t101/
│
├── time-tracker.php              ← Você já tem este
├── api_time_tracker.php          ← API principal
├── api_time_tracker_debug.php    ← API debug (FALTANDO)
├── view_logs.php                 ← Visualizador (FALTANDO)
├── test_installation.php         ← Teste
├── CHECK_FILES.php               ← Verificador (NOVO)
│
├── includes/
│   └── auth_check.php
│
└── config/
    ├── database.php
    ├── dash_database.php
    └── dash_functions.php
```

---

## 🚀 Como Fazer Upload

### Via FTP/SFTP (Recomendado)

1. **Conecte-se ao servidor** via FTP (FileZilla, WinSCP, etc.)

2. **Navegue até a pasta** onde está o `time-tracker.php`
   - Geralmente: `/public_html/v/dash-t101/` ou `/htdocs/dash-t101/`

3. **Faça upload destes arquivos** (da pasta `/app/time_tracker/`):
   ```
   api_time_tracker_debug.php
   view_logs.php
   CHECK_FILES.php
   ```

4. **Verifique as permissões** dos arquivos:
   - Permissão: `644` (rw-r--r--)
   - Comando: `chmod 644 *.php`

---

### Via cPanel File Manager

1. **Acesse cPanel** → File Manager

2. **Navegue até** `public_html/v/dash-t101/` (ou onde está o time-tracker.php)

3. **Clique em "Upload"**

4. **Selecione os arquivos**:
   - `api_time_tracker_debug.php`
   - `view_logs.php`
   - `CHECK_FILES.php`

5. **Aguarde o upload** completar

---

### Via Terminal SSH

Se você tem acesso SSH:

```bash
# 1. Navegar para o diretório
cd /caminho/para/dash-t101/

# 2. Verificar se time-tracker.php existe
ls -la time-tracker.php

# 3. Copiar arquivos da pasta local
cp /app/time_tracker/api_time_tracker_debug.php .
cp /app/time_tracker/view_logs.php .
cp /app/time_tracker/CHECK_FILES.php .

# 4. Ajustar permissões
chmod 644 *.php

# 5. Verificar
ls -la
```

---

## 🔍 Como Verificar se Funcionou

### Teste 1: Verificar se os arquivos estão no lugar

Acesse:
```
https://v.translators101.com/dash-t101/CHECK_FILES.php
```

Você deve ver:
- ✅ Todos os arquivos marcados como "OK"
- ✅ URLs para testar

### Teste 2: Testar a API Debug

Acesse diretamente:
```
https://v.translators101.com/dash-t101/api_time_tracker_debug.php?action=project_list
```

**Resultado esperado:**
```json
{
  "success": false,
  "error": "Usuário não autenticado",
  "debug_session": {...}
}
```

**Se ver isso:** ✅ Arquivo está no lugar correto!

**Se ver "404" ou página de erro:** ❌ Arquivo não está no servidor

### Teste 3: Abrir o Visualizador

Acesse:
```
https://v.translators101.com/dash-t101/view_logs.php
```

Deve abrir uma página bonita mostrando logs (mesmo que vazia).

---

## 🐛 Problemas Comuns

### Problema 1: "Ainda recebo 404"

**Causas possíveis:**
1. Arquivo não foi enviado
2. Arquivo está em pasta errada
3. Nome do arquivo está errado (maiúsculas/minúsculas)
4. Cache do navegador

**Soluções:**
1. Reenvie o arquivo
2. Use `CHECK_FILES.php` para confirmar o caminho
3. Verifique o nome exato do arquivo
4. Force reload: Ctrl+Shift+R

### Problema 2: "Página em branco"

**Causa:** Erro de sintaxe PHP

**Solução:**
1. Verifique os logs do servidor: `/var/log/php_errors.log`
2. Ou ative exibição de erros no arquivo:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Problema 3: "Permissões negadas"

**Causa:** Arquivo não tem permissão de leitura

**Solução:**
```bash
chmod 644 api_time_tracker_debug.php
chmod 644 view_logs.php
```

---

## 📝 Checklist Rápido

Antes de testar de novo, confirme:

- [ ] Arquivos foram enviados para o servidor
- [ ] Estão no mesmo diretório que `time-tracker.php`
- [ ] Nomes dos arquivos estão corretos (exatamente como abaixo)
  - `api_time_tracker_debug.php` (com underscores)
  - `view_logs.php`
  - `CHECK_FILES.php`
- [ ] Permissões estão corretas (644)
- [ ] Cache do navegador foi limpo (Ctrl+Shift+R)
- [ ] `CHECK_FILES.php` mostra todos como ✅

---

## 🎯 Fluxo Correto

```
1. Fazer upload dos arquivos
   ↓
2. Acessar CHECK_FILES.php para verificar
   ↓
3. Se tudo OK, abrir view_logs.php
   ↓
4. Em outra aba, abrir time-tracker.php
   ↓
5. Usar o Time Tracker
   ↓
6. Voltar para view_logs.php para ver o que aconteceu
```

---

## 💡 Dica Importante

**NÃO edite os arquivos no servidor** se não souber o que está fazendo.

**Use os arquivos DA PASTA `/app/time_tracker/`** - eles já estão corretos e testados.

Apenas faça **UPLOAD** deles para o servidor, sem modificações.

---

## 🆘 Precisa de Ajuda?

1. **Primeiro:** Execute `CHECK_FILES.php` e me envie o resultado
2. **Segundo:** Me diga qual método de upload você está usando (FTP, cPanel, SSH)
3. **Terceiro:** Mostre o caminho exato onde o `time-tracker.php` está no seu servidor

---

**LEMBRE-SE:** O erro 404 significa simplesmente que o arquivo não está onde o navegador está procurando. É só uma questão de colocar no lugar certo! 🎯
