# Changelog - Budget_c.php (Nov 16, 2024)

## 🎉 Versão 2.0 - Atualização Completa do Fluxo

### 📝 Resumo das Mudanças

Esta atualização implementa um fluxo de orçamento completamente reestruturado, com navegação guiada por passos e melhorias significativas na experiência do usuário.

---

## ✨ Novas Funcionalidades

### 1. Sistema de Fluxo por Passos
- **Adicionado:** Sistema de progresso com 5 etapas sequenciais
  - Passo 1: Cliente
  - Passo 2: Pesos por faixa
  - Passo 3: Selecionar arquivos
  - Passo 4: Custos do projeto
  - Passo 5: Resultados
- **Funcionalidade:** Cards habilitados progressivamente após conclusão do passo anterior
- **Visual:** Indicador de conclusão (✓ verde) em cards completados
- **Session:** Nova variável `$_SESSION['budget_flow_step']` para controle do estado

### 2. Moedas Dinâmicas
- **Adicionado:** Busca automática de moedas cadastradas no banco de dados
- **Fonte:** Tabela `dash_settings` (entries com `setting_key LIKE 'rate_%'`)
- **Moeda base:** BRL sempre disponível
- **Exemplos:** USD, EUR, CAD, GBP, etc.
- **Arquivo SQL:** `moedas_exemplo.sql` para popular moedas

### 3. Estimativa de Páginas
- **Adicionado:** Cálculo automático de páginas por arquivo
- **Fórmula:** 250 palavras = 1 página
- **Campo editável:** Usuário pode ajustar manualmente o número de páginas
- **Persistência:** Salva na sessão e atualiza cálculos
- **Exibição:** Total de páginas mostrado no card "Resumo"

### 4. Lista de Arquivos Selecionados
- **Adicionado:** Lista visual dos arquivos antes do upload
- **Funcionalidade:** Botão "X" para remover arquivos individualmente
- **JavaScript:** Gerenciamento dinâmico de array de arquivos
- **UX:** Confirma seleção antes do processamento

### 5. Barra de Progresso de Upload
- **Adicionado:** Indicador visual durante processamento
- **Animação:** Barra de progresso de 0% a 90%
- **Mensagem:** "Processando... X%" e "Finalizando análise..."
- **Feedback:** Usuário sabe que o sistema está trabalhando

### 6. Campo de Serviço em Custos
- **Adicionado:** Campo para especificar tipo de serviço do fornecedor
- **Exemplos:** Tradução, Revisão, Diagramação
- **Flexibilidade:** Input de texto livre
- **Integração:** Preparado para buscar de `dash_freelancer_rates`

### 7. Endpoint AJAX Opcional
- **Arquivo:** `ajax_provider_rates.php`
- **Funcionalidade:** Busca dinâmica de serviços e taxas de fornecedores
- **Resposta JSON:** Retorna serviços disponíveis e taxas por serviço
- **Uso futuro:** Pode ser integrado ao frontend para auto-complete

---

## 🔄 Alterações em Funcionalidades Existentes

### Card "Cliente" (anteriormente "Cliente e parâmetros")
- **Renomeado:** "Cliente e parâmetros" → "Cliente"
- **Removido:** Campo "Por palavra (Cliente)" do formulário inicial
- **Alterado:** Campo "Moeda" de input texto para SELECT
- **Limitado:** Serviço agora mostra apenas "Tradução" e "Interpretação"
- **Mantido:** Todos os outros campos (Nome, De, Para, Markup, Impostos)

### Card "Pesos por Faixa"
- **Alterado:** Botão "Atualizar pesos" → "OK"
- **Comportamento:** Ao clicar OK, avança para próximo passo
- **Desabilitado:** Card só fica ativo após confirmar cliente

### Card "Selecionar Arquivos"
- **Alterado:** Botão "Gerar orçamento" → "Calcular fuzzy matches"
- **Adicionado:** Sistema de listagem de arquivos
- **Adicionado:** Barra de progresso
- **Desabilitado:** Card só fica ativo após confirmar pesos

### Card "Custos do Projeto"
- **Adicionado:** Coluna "Serviço" na tabela de custos
- **Adicionado:** Campo de serviço no formulário de adição
- **Alterado:** Layout do formulário (grid de 4 colunas)
- **Melhorado:** Botão "OK" só aparece se houver custos adicionados
- **Desabilitado:** Card só fica ativo após upload de arquivos

### Análises por Arquivo
- **Adicionado:** Campo editável de número de páginas
- **Adicionado:** Botão "Salvar" para atualizar páginas
- **Layout:** Grid de 4 colunas (Palavras, Segmentos, Ponderadas, Páginas)
- **Handler:** `update_pages` POST handler

---

## 🎨 Melhorias Visuais

### CSS
- **Nova classe:** `.disabled` para cards desabilitados
  - `opacity: 0.4`
  - `pointer-events: none`
  - `filter: grayscale(0.5)`
- **Estilo:** `.file-list` e `.file-item` para lista de arquivos
- **Estilo:** `.progress-container`, `.progress-bar`, `.progress-fill` para barra de progresso
- **Ícone:** Check verde (✓) nos títulos de cards completados

### JavaScript
- **Função:** `renderFileList()` - Renderiza arquivos selecionados
- **Função:** `removeFile(index)` - Remove arquivo da lista
- **Event:** Submit form com animação de progresso
- **Event:** Auto-fill de taxas ao selecionar fornecedor (preparado)

---

## 🗄️ Alterações no Banco de Dados

### Nenhuma alteração de schema necessária

O sistema utiliza as tabelas existentes:
- `dash_clients`
- `dash_freelancers`
- `dash_freelancer_rates`
- `dash_client_rates`
- `dash_settings`

### Novos dados recomendados:
- Popular `dash_settings` com moedas (ver `moedas_exemplo.sql`)

---

## 📦 Novos Arquivos

1. **budget_c.php** (atualizado)
   - ~800 linhas
   - 5 passos de fluxo implementados
   - JavaScript integrado

2. **processor.php** (atualizado)
   - Método `generateFuzzyMatches()` melhorado
   - Campo `estimatedPages` adicionado no retorno
   - Cálculo: `max(1, round(wordCount / 250))`

3. **README.md** (novo)
   - Documentação completa
   - Instruções de instalação
   - Guia de uso

4. **moedas_exemplo.sql** (novo)
   - Script SQL para popular moedas
   - Exemplos: USD, EUR, CAD, GBP, ARS

5. **ajax_provider_rates.php** (novo)
   - Endpoint para buscar taxas via AJAX
   - Retorna serviços e taxas por fornecedor
   - JSON response

6. **CHANGELOG.md** (este arquivo)
   - Documentação de todas as mudanças

---

## 🔧 Variáveis de Sessão

### Novas
- `$_SESSION['budget_flow_step']` - Passo atual do fluxo (1-5)

### Mantidas
- `$_SESSION['analyses']` - Array de análises de arquivos
- `$_SESSION['budget_client']` - Dados do cliente e parâmetros
- `$_SESSION['budget_costs']` - Array de custos adicionados
- `$_SESSION['wc_weights']` - Pesos por faixa de fuzzy match
- `$_SESSION['budget_errors']` - Mensagens de erro
- `$_SESSION['budget_notices']` - Mensagens informativas

### Alteradas
- `$_SESSION['budget_costs']['items'][]` - Agora inclui campo `service`
- `$_SESSION['analyses'][]` - Agora inclui campo `estimatedPages`

---

## 🐛 Correções de Bugs

- **Corrigido:** Referências inconsistentes a `dash_freelancers` (antes era `dash_suppliers`)
- **Corrigido:** Validação de array vazio em `$_SESSION['budget_costs']['items']`
- **Melhorado:** Tratamento de erros em uploads de arquivo

---

## ⚠️ Breaking Changes

### Nenhuma mudança que quebre compatibilidade

O sistema mantém retrocompatibilidade com:
- Estrutura de banco de dados existente
- Sessões anteriores (valores padrão são aplicados se não existirem)
- URLs e rotas

---

## 🚀 Como Migrar

### Passo 1: Backup
```bash
cp /app/v/dash-t101/budget_c.php /app/v/dash-t101/budget_c.php.bak
cp /app/v/dash-t101/processor.php /app/v/dash-t101/processor.php.bak
```

### Passo 2: Copiar novos arquivos
```bash
cp /app/Nov_16/budget_c.php /app/v/dash-t101/budget_c.php
cp /app/Nov_16/processor.php /app/v/dash-t101/processor.php
```

### Passo 3: Popular moedas (opcional)
```bash
mysql -u seu_usuario -p sua_database < /app/Nov_16/moedas_exemplo.sql
```

### Passo 4: Testar
1. Acesse o sistema
2. Vá para Orçamentos
3. Siga o fluxo passo a passo
4. Verifique todos os cards

---

## 📈 Melhorias Futuras Sugeridas

1. **Modal de Interpretação**
   - Implementar fluxo específico para serviço de interpretação
   - Campos: duração, número de intérpretes, equipamento

2. **AJAX para taxas**
   - Integrar `ajax_provider_rates.php` ao frontend
   - Auto-complete de serviços ao selecionar fornecedor

3. **Upload assíncrono**
   - Implementar upload real com progresso via XHR/Fetch
   - Feedback em tempo real de cada arquivo

4. **Validação avançada**
   - Validação de formulários no frontend (HTML5 + JS)
   - Mensagens de erro mais específicas

5. **Exportação melhorada**
   - PDF com layout profissional
   - Envio por email direto do sistema

6. **Histórico de orçamentos**
   - Salvar orçamentos no banco de dados
   - Listar e recuperar orçamentos anteriores

---

## 👥 Contribuidores

- Desenvolvimento: E1 Agent (Emergent)
- Requisitos: Cliente Dash-T101
- Data: 16 de Novembro de 2024

---

## 📄 Licença

Este código faz parte do sistema proprietário Dash-T101.

---

**Fim do Changelog**
