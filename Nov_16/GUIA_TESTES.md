# 🧪 Guia de Testes - Budget_c.php

## Checklist Completo de Testes

Use este guia para verificar se todas as funcionalidades estão operacionais após a instalação.

---

## 🎯 Testes Básicos

### ✅ Teste 1: Acesso à Página
- [ ] Acessar `/dash-t101/budget_c.php`
- [ ] Página carrega sem erros
- [ ] Todos os cards estão visíveis
- [ ] Card "Cliente" está ativo (sem opacidade reduzida)
- [ ] Outros cards estão desabilitados (opacidade 40%)

**Resultado esperado:**
```
✅ Cliente (ativo)
🔒 Pesos por faixa (bloqueado)
🔒 Selecionar arquivos (bloqueado)
🔒 Custos do projeto (bloqueado)
❌ Resultados (não visível ainda)
```

---

## 📋 Testes por Passo

### PASSO 1: Cliente

#### Teste 1.1: Select de Cliente
- [ ] Clicar no campo "Nome"
- [ ] Verifica se lista de clientes aparece
- [ ] Selecionar um cliente
- [ ] Verifica se nome é preenchido corretamente

#### Teste 1.2: Select de Serviço
- [ ] Clicar no campo "Serviço"
- [ ] Verifica opções disponíveis: apenas "Tradução" e "Interpretação"
- [ ] ❌ NÃO deve haver "Revisão" ou "Outro" neste campo
- [ ] Selecionar "Tradução"

#### Teste 1.3: Campos de Idioma
- [ ] Digitar no campo "De": "EN"
- [ ] Digitar no campo "Para": "PT-BR"
- [ ] Verifica se aceita texto

#### Teste 1.4: Select de Moeda
- [ ] Clicar no campo "Moeda"
- [ ] Verifica se BRL está presente
- [ ] Verifica se moedas do BD aparecem (USD, EUR, CAD, etc.)
- [ ] Selecionar uma moeda

#### Teste 1.5: Campos Numéricos
- [ ] Digitar "35" no campo Markup
- [ ] Digitar "12.5" no campo Impostos
- [ ] Verifica se aceita decimais

#### Teste 1.6: Confirmar Cliente
- [ ] Clicar em "Confirmar Cliente"
- [ ] Página recarrega
- [ ] Mensagem de sucesso aparece: "Cliente configurado..."
- [ ] Card "Cliente" mostra ✅ verde
- [ ] Card "Pesos por faixa" fica ativo
- [ ] Outros cards permanecem bloqueados

**Screenshot esperado:**
```
✅ Cliente (verde, concluído)
⚪ Pesos por faixa (ativo)
🔒 Selecionar arquivos (bloqueado)
🔒 Custos do projeto (bloqueado)
```

---

### PASSO 2: Pesos por Faixa

#### Teste 2.1: Valores Padrão
- [ ] Verifica valores pré-preenchidos:
  - 100%: 0.1
  - 95-99%: 0.2
  - 85-94%: 0.4
  - 75-84%: 0.6
  - 50-74%: 0.8
  - No Match: 1.0

#### Teste 2.2: Editar Pesos
- [ ] Alterar "100%" para "0.05"
- [ ] Alterar "No Match" para "1.1"
- [ ] Verifica se campos aceitam decimais

#### Teste 2.3: Botão OK
- [ ] Clicar em "OK"
- [ ] Página recarrega
- [ ] Mensagem: "Pesos atualizados. Agora selecione os arquivos."
- [ ] Card "Pesos" mostra ✅ verde
- [ ] Card "Selecionar arquivos" fica ativo

**Screenshot esperado:**
```
✅ Cliente (concluído)
✅ Pesos por faixa (concluído)
⚪ Selecionar arquivos (ativo)
🔒 Custos do projeto (bloqueado)
```

---

### PASSO 3: Selecionar Arquivos

#### Teste 3.1: Botão Selecionar
- [ ] Clicar em "Selecionar arquivos"
- [ ] Dialog do sistema operacional abre
- [ ] Navegar para pasta de teste
- [ ] Selecionar múltiplos arquivos (.docx, .pptx, .xlsx)

#### Teste 3.2: Lista de Arquivos
- [ ] Após selecionar, lista aparece na tela
- [ ] Cada arquivo tem um [X] ao lado
- [ ] Nome completo dos arquivos está visível

**Exemplo esperado:**
```
📄 relatorio.docx              [X]
📄 apresentacao.pptx           [X]
📄 planilha_dados.xlsx         [X]
```

#### Teste 3.3: Remover Arquivo
- [ ] Clicar no [X] de um arquivo
- [ ] Arquivo é removido da lista
- [ ] Outros arquivos permanecem

#### Teste 3.4: Botão Calcular
- [ ] Botão "Calcular fuzzy matches" aparece
- [ ] Botão está habilitado
- [ ] Clicar no botão

#### Teste 3.5: Barra de Progresso
- [ ] Barra de progresso aparece
- [ ] Animação de 0% a 90%+
- [ ] Mensagem "Processando..." visível
- [ ] Ao final: "Finalizando análise de fuzzy matches..."

#### Teste 3.6: Após Processamento
- [ ] Página recarrega
- [ ] Mensagem: "Análise de fuzzy matches concluída..."
- [ ] Card "Selecionar arquivos" mostra ✅ verde
- [ ] Card "Custos do projeto" fica ativo

**Screenshot esperado:**
```
✅ Cliente (concluído)
✅ Pesos por faixa (concluído)
✅ Selecionar arquivos (concluído)
⚪ Custos do projeto (ativo)
```

---

### PASSO 4: Custos do Projeto

#### Teste 4.1: Fornecedor Interno
- [ ] Selecionar "Interno" no campo Fornecedor
- [ ] Digitar "Revisão Interna" no campo Serviço
- [ ] Digitar "150,00" no campo Valor
- [ ] Clicar em "+ Adicionar"
- [ ] Custo aparece na tabela

#### Teste 4.2: Fornecedor Cadastrado
- [ ] Selecionar um fornecedor da lista
- [ ] Campo Valor deve auto-preencher (0,20 ou taxa do BD)
- [ ] Digitar "Tradução" no campo Serviço
- [ ] Ajustar valor se necessário
- [ ] Clicar em "+ Adicionar"
- [ ] Custo aparece na tabela

**Tabela esperada:**
```
┌─────────────┬─────────────────┬────────────┬──────┐
│ Fornecedor  │ Serviço         │ Custo      │ Ação │
├─────────────┼─────────────────┼────────────┼──────┤
│ Interno     │ Revisão Interna │ R$ 150,00  │ [🗑️] │
│ João Silva  │ Tradução        │ R$ 300,00  │ [🗑️] │
└─────────────┴─────────────────┴────────────┴──────┘
```

#### Teste 4.3: Remover Custo
- [ ] Clicar no ícone 🗑️ de um custo
- [ ] Confirmação aparece
- [ ] Confirmar exclusão
- [ ] Custo é removido da tabela

#### Teste 4.4: Múltiplos Custos
- [ ] Adicionar pelo menos 3 custos diferentes:
  - Tradução
  - Revisão
  - Diagramação
- [ ] Todos aparecem na tabela
- [ ] Total é calculado corretamente

#### Teste 4.5: Botão OK
- [ ] Botão "OK - Ver Resultados" aparece
- [ ] Botão só aparece se houver pelo menos 1 custo
- [ ] Clicar no botão
- [ ] Página recarrega
- [ ] Mensagem: "Custos confirmados. Veja os resultados abaixo."
- [ ] Cards de resultados aparecem

**Screenshot esperado:**
```
✅ Cliente (concluído)
✅ Pesos por faixa (concluído)
✅ Selecionar arquivos (concluído)
✅ Custos do projeto (concluído)
⚪ RESULTADOS (visíveis)
```

---

### PASSO 5: Resultados

#### Teste 5.1: Card "Resumo"
- [ ] Card está visível
- [ ] "Total de palavras" mostra número > 0
- [ ] "Total de segmentos" mostra número > 0
- [ ] "Total ponderado" mostra número > 0
- [ ] "Total de páginas" mostra número > 0
- [ ] Valores são números inteiros

**Exemplo esperado:**
```
Total de palavras:  5.420
Total de segmentos:   324
Total ponderado:    3.800
Total de páginas:      22
```

#### Teste 5.2: Card "Custo Total"
- [ ] Valor em destaque (laranja)
- [ ] Mostra moeda selecionada (ex: BRL)
- [ ] Valor = soma de todos os custos adicionados
- [ ] Formato: R$ X.XXX,XX

**Exemplo esperado:**
```
R$ 650,00
```

#### Teste 5.3: Card "Preço Sugerido"
- [ ] Valor em destaque (verde)
- [ ] Mostra breakdown:
  - Subtotal (Custo + Markup)
  - Impostos
- [ ] Cálculo correto:
  - Subtotal = Custo × (1 + Markup%)
  - Impostos = Subtotal × Impostos%
  - Total = Subtotal + Impostos

**Exemplo esperado:**
```
R$ 925,32

Subtotal (Custo + Markup 30%): R$ 845,00
Impostos (11.5%): R$ 80,32
```

#### Teste 5.4: Análises por Arquivo
- [ ] Card "Análises por arquivo" está visível
- [ ] Cada arquivo processado tem um card individual
- [ ] Cada card mostra:
  - Nome do arquivo
  - Tabela de fuzzy matches (categorias, segmentos, %)
  - Palavras, Segmentos, Ponderadas, Páginas
  - Campo editável de páginas
  - Botões [CSV] e [Remover]

#### Teste 5.5: Editar Páginas
- [ ] Alterar número de páginas de um arquivo
- [ ] Clicar no botão 💾 Salvar
- [ ] Página recarrega
- [ ] Mensagem: "Número de páginas atualizado"
- [ ] Total de páginas no Resumo é atualizado

#### Teste 5.6: Botões de Download
- [ ] Botão "CSV consolidado" está visível
- [ ] Botão "XLSX consolidado" está visível
- [ ] Clicar em cada um (verificar se download funciona)

#### Teste 5.7: Remover Arquivo
- [ ] Clicar em "Remover" em um arquivo
- [ ] Confirmação aparece
- [ ] Confirmar
- [ ] Arquivo é removido
- [ ] Totais são recalculados

---

## 🔍 Testes de Validação

### Teste V1: Campos Obrigatórios
- [ ] Tentar avançar sem preencher cliente
- [ ] Verificar se validação impede
- [ ] Mensagem de erro aparece

### Teste V2: Valores Numéricos
- [ ] Digitar texto em campo numérico (Markup, Impostos)
- [ ] Verificar comportamento
- [ ] Valores devem ser convertidos ou rejeitados

### Teste V3: Upload Vazio
- [ ] Clicar em "Calcular fuzzy matches" sem selecionar arquivos
- [ ] Mensagem de erro: "Nenhum arquivo válido foi selecionado."

### Teste V4: Custos Vazios
- [ ] Tentar clicar "OK" sem adicionar custos
- [ ] Botão deve estar desabilitado ou mensagem de erro aparece

---

## 🎨 Testes Visuais

### Teste UI1: Estados dos Cards
- [ ] Cards bloqueados têm opacidade 40%
- [ ] Cards ativos têm opacidade 100%
- [ ] Cards concluídos têm ✅ verde no título
- [ ] Hover nos cards muda cor de fundo

### Teste UI2: Responsividade
- [ ] Testar em tela desktop (1920x1080)
- [ ] Testar em tela menor (1366x768)
- [ ] Testar em tablet (iPad - 768x1024)
- [ ] Layout se adapta corretamente

### Teste UI3: Barra de Progresso
- [ ] Barra tem gradiente roxo-azul
- [ ] Animação é suave
- [ ] Texto "Processando..." está centralizado

### Teste UI4: Tabelas
- [ ] Cabeçalhos têm fundo diferente
- [ ] Linhas alternam cor ao hover
- [ ] Texto está legível
- [ ] Alinhamento está correto

---

## 🔄 Testes de Fluxo Completo

### Cenário 1: Orçamento Simples
```
1. Selecionar cliente "Empresa ABC"
2. Serviço: Tradução
3. De: EN, Para: PT-BR
4. Moeda: BRL
5. Markup: 30%, Impostos: 11.5%
6. Confirmar

7. Manter pesos padrão
8. Clicar OK

9. Selecionar 2 arquivos: doc1.docx, pres.pptx
10. Calcular fuzzy matches
11. Aguardar processamento

12. Adicionar custo:
    - Fornecedor: João Silva
    - Serviço: Tradução
    - Valor: 300,00
13. OK - Ver Resultados

14. Verificar todos os cards de resultado
15. Baixar CSV
16. Novo orçamento
```

### Cenário 2: Orçamento Complexo
```
1. Cliente com moeda diferente (USD)
2. Múltiplos arquivos (5+)
3. Múltiplos custos (Tradução, Revisão, Diagramação)
4. Editar páginas manualmente
5. Remover um arquivo
6. Remover um custo
7. Recalcular
8. Exportar XLSX
```

### Cenário 3: Fluxo de Erro
```
1. Tentar avançar sem cliente → Erro
2. Selecionar arquivo corrompido → Erro tratado
3. Adicionar custo com valor 0 → Erro
4. Editar páginas para 0 → Deve ficar min 1
```

---

## 🗄️ Testes de Banco de Dados

### Teste BD1: Clientes
- [ ] Listar clientes retorna resultados
- [ ] Cliente selecionado tem default_currency
- [ ] Moeda padrão é aplicada

### Teste BD2: Fornecedores
- [ ] Listar fornecedores retorna resultados
- [ ] Fornecedores aparecem em ordem alfabética

### Teste BD3: Moedas
- [ ] Query em dash_settings retorna moedas
- [ ] BRL sempre está presente (hardcoded)
- [ ] Moedas aparecem no select

### Teste BD4: Taxas
- [ ] Buscar taxa de fornecedor funciona
- [ ] Se não encontrar, valor padrão é 0,20
- [ ] Múltiplas taxas por fornecedor são tratadas

---

## 📊 Testes de Cálculo

### Teste C1: Palavras Ponderadas
```
Entrada:
- Total de palavras: 1000
- Segmentos: 100
- Fuzzy: 50% No Match, 50% 100%
- Pesos: No Match=1.0, 100%=0.1

Cálculo esperado:
- No Match: 500 palavras × 1.0 = 500
- 100%: 500 palavras × 0.1 = 50
- Total ponderado: 550

Verificar se resultado bate
```

### Teste C2: Estimativa de Páginas
```
Entrada:
- Arquivo com 2.500 palavras

Cálculo esperado:
- 2.500 / 250 = 10 páginas

Verificar se resultado é 10
```

### Teste C3: Preço Sugerido
```
Entrada:
- Custo total: R$ 650,00
- Markup: 30%
- Impostos: 11,5%

Cálculo esperado:
- Subtotal: 650 × 1,30 = R$ 845,00
- Impostos: 845 × 0,115 = R$ 97,17
- Total: 845 + 97,17 = R$ 942,17

Verificar se resultado bate (aproximado)
```

---

## 🐛 Testes de Regressão

Após qualquer mudança, executar:

- [ ] Teste de fluxo completo (Cenário 1)
- [ ] Todos os cards funcionam
- [ ] Cálculos estão corretos
- [ ] Exportações funcionam
- [ ] Nenhum erro de JavaScript no console
- [ ] Nenhum erro de PHP nos logs

---

## ✅ Aprovação Final

Antes de considerar a implementação completa:

- [ ] Todos os testes básicos passam
- [ ] Pelo menos 2 cenários completos executados
- [ ] Validações funcionam corretamente
- [ ] UI está responsiva
- [ ] Cálculos estão precisos
- [ ] Exportações funcionam
- [ ] Nenhum erro crítico encontrado

---

## 📝 Template de Relatório de Teste

```
=== RELATÓRIO DE TESTE ===
Data: __/__/____
Testador: _____________
Ambiente: Dev / Staging / Prod

RESUMO:
✅ Testes passados: ___
❌ Testes falhos: ___
⚠️ Bugs encontrados: ___

BUGS CRÍTICOS:
1. 
2. 

BUGS MENORES:
1. 
2. 

OBSERVAÇÕES:


APROVADO PARA PRODUÇÃO: SIM / NÃO
Assinatura: _____________
```

---

**Última atualização:** 16/11/2024  
**Status:** Checklist completo ✅
