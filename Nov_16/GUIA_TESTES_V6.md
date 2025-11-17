# Guia de Testes - Budget C V6

## Pré-requisitos
- Servidor PHP rodando
- TCPDF instalado via Composer
- Banco de dados configurado
- Usuário logado na aplicação

---

## Teste 1: Verificar Tarja Roxa (Header)

### Objetivo
Confirmar que o header está com o gradiente correto conforme padrão de `projects.php`

### Passos
1. Acessar `budget_c_v6.php`
2. Verificar o header no topo da página

### Resultado Esperado
- ✅ Gradiente roxo de 135deg (do roxo claro ao roxo escuro #4a148c)
- ✅ Ícone de cifrão dentro de círculo branco semi-transparente
- ✅ Título: "Orçamentos — Análise de Fuzzy Matches"
- ✅ Subtítulo: "Fluxo guiado para geração de orçamentos profissionais"
- ✅ Texto em branco, bem legível

---

## Teste 2: Remover Arquivos Individualmente

### Objetivo
Verificar se é possível remover arquivos já processados da lista

### Passos
1. Completar os cards de Cliente e Pesos
2. No card "Selecionar arquivos", fazer upload de 2-3 arquivos
3. Aguardar processamento (barra de progresso)
4. Após o reload, verificar a lista "Arquivos adicionados"
5. Clicar no ícone "×" vermelho ao lado de um dos arquivos
6. Confirmar a ação no popup

### Resultado Esperado
- ✅ Cada arquivo tem um botão "×" vermelho à direita
- ✅ Ao clicar, aparece confirmação: "Remover este arquivo do orçamento?"
- ✅ Após confirmar, a página recarrega
- ✅ O arquivo removido não aparece mais na lista
- ✅ Os outros arquivos permanecem intactos
- ✅ Cálculos subsequentes não incluem o arquivo removido

---

## Teste 3: Abrir Modal de PDF

### Objetivo
Verificar se o modal abre corretamente com dados pré-preenchidos

### Passos
1. Completar todo o fluxo até calcular o orçamento (step 5)
2. No card "Preço sugerido", clicar no botão verde "Preparar para enviar"

### Resultado Esperado
- ✅ Modal abre com animação suave
- ✅ Backdrop escuro com blur
- ✅ Título do modal: "Preparar Orçamento PDF"
- ✅ Campo "Preço final" já preenchido com o valor calculado (ex: "1.234,56")
- ✅ Lista de arquivos aparece com checkboxes, todos marcados
- ✅ Campos de nome, datas vazios (aguardando input)
- ✅ Botão "Gerar PDF" visível e ativo
- ✅ Botão "×" no canto superior direito funciona
- ✅ Tecla ESC fecha o modal
- ✅ Clicar fora do modal (no backdrop) fecha o modal

---

## Teste 4: Editar Dados no Modal

### Objetivo
Verificar se todos os campos podem ser editados

### Passos
1. Abrir o modal de PDF (conforme Teste 3)
2. Editar o campo "Preço final" para um valor diferente (ex: "2.000,00")
3. Desmarcar um dos checkboxes de arquivo
4. Preencher "Nome do contato"
5. Selecionar "Prazo de entrega"
6. Selecionar "Validade do orçamento"

### Resultado Esperado
- ✅ Campo "Preço final" aceita edição e formatação (vírgula para decimais)
- ✅ Checkboxes podem ser marcados/desmarcados livremente
- ✅ Campo "Nome do contato" aceita texto
- ✅ Campos de data abrem seletor de calendário
- ✅ Todos os dados permanecem ao desfocar os campos
- ✅ Formulário valida campos obrigatórios (não permite submit vazio)

---

## Teste 5: Gerar PDF

### Objetivo
Verificar se o PDF é gerado corretamente com todos os dados

### Passos
1. Preencher todos os campos do modal (conforme Teste 4)
2. Deixar pelo menos 2 arquivos marcados
3. Clicar em "Gerar PDF"

### Resultado Esperado
- ✅ Download inicia automaticamente
- ✅ Nome do arquivo: "Orçamento — {Nome do Cliente}.pdf"
- ✅ PDF abre sem erros

**Conteúdo do PDF:**
- ✅ Título "ORÇAMENTO" em roxo, centralizado
- ✅ Seção "Cliente" com nome do cliente
- ✅ "Contato: {nome digitado no modal}"
- ✅ "Prazo de Entrega: {data selecionada}"
- ✅ "Validade do Orçamento: {data selecionada}"
- ✅ Seção "Arquivos para Tradução" com bullets
- ✅ Lista apenas os arquivos que estavam marcados
- ✅ Valor total destacado em verde com moeda (ex: "BRL 2.000,00")
- ✅ Formatação brasileira (vírgula para decimais, ponto para milhares)
- ✅ Texto da observação no rodapé
- ✅ Layout profissional, margens adequadas
- ✅ Todas as fontes legíveis

---

## Teste 6: Validação de Campos Obrigatórios

### Objetivo
Verificar se o formulário valida campos obrigatórios

### Passos
1. Abrir modal de PDF
2. Deixar campos vazios
3. Tentar clicar em "Gerar PDF"

### Resultado Esperado
- ✅ Navegador não permite submit
- ✅ Campos obrigatórios são destacados (comportamento padrão HTML5)
- ✅ Mensagens de erro aparecem nos campos vazios

---

## Teste 7: Fluxo Completo End-to-End

### Objetivo
Testar o fluxo completo desde o início

### Passos
1. Acessar `budget_c_v6.php?clear=1` (limpar sessão)
2. **Card Cliente**: Selecionar cliente, serviço, idiomas, moeda → Confirmar
3. **Card Pesos**: Ajustar pesos se necessário → OK
4. **Card Arquivos**: Upload de 3 arquivos → Calcular fuzzy matches
5. **Remover 1 arquivo** da lista
6. **Card Custos**: 
   - Adicionar Markup: 30%
   - Adicionar Impostos: 11.5%
   - Adicionar 2-3 custos (Tradução, Revisão, Diagramação)
   - Calcular orçamento
7. **Card Preço Sugerido**: Clicar em "Preparar para enviar"
8. **Modal PDF**:
   - Desmarcar 1 arquivo
   - Editar preço final
   - Preencher nome do contato
   - Selecionar datas
   - Gerar PDF
9. Abrir o PDF e verificar todos os dados

### Resultado Esperado
- ✅ Todos os cards são habilitados progressivamente
- ✅ Cards completados ficam com tarja verde
- ✅ Arquivo removido não aparece mais
- ✅ Cálculos estão corretos
- ✅ Modal abre com dados corretos
- ✅ PDF é gerado com sucesso
- ✅ Todos os dados do PDF conferem com o preenchido

---

## Teste 8: Compatibilidade com V5

### Objetivo
Confirmar que funcionalidades anteriores continuam funcionando

### Passos
1. Testar todo o fluxo SEM usar as novas funcionalidades
2. Não remover arquivos
3. Não gerar PDF
4. Apenas seguir o fluxo até o cálculo final

### Resultado Esperado
- ✅ Todo o fluxo V5 funciona normalmente
- ✅ Cálculos mantêm precisão
- ✅ Upload múltiplo funciona
- ✅ Adição/remoção de custos funciona
- ✅ Modal de adicionar cliente funciona

---

## Checklist Final de Verificação

### Visual
- [ ] Tarja roxa com gradiente correto
- [ ] Botão "×" vermelho visível em cada arquivo
- [ ] Botão "Preparar para enviar" verde e destacado
- [ ] Modal centralizado e com backdrop
- [ ] Checkboxes alinhados e funcionais

### Funcional
- [ ] Remover arquivo atualiza a lista
- [ ] Modal pré-preenche preço e arquivos
- [ ] Todos os campos são editáveis
- [ ] Validação de campos obrigatórios
- [ ] PDF gerado com nome correto
- [ ] PDF contém todos os dados corretos
- [ ] Formatação brasileira nos números

### Performance
- [ ] Upload de arquivos sem travamentos
- [ ] Modal abre/fecha rapidamente
- [ ] PDF é gerado em poucos segundos
- [ ] Sem erros no console do navegador
- [ ] Sem warnings no log do PHP

---

## Casos de Erro a Testar

### Erro 1: Tentar gerar PDF sem calcular orçamento
**Esperado**: Botão "Preparar para enviar" só aparece após cálculo bem-sucedido

### Erro 2: Fechar modal sem salvar
**Esperado**: Dados não são perdidos, pode reabrir modal

### Erro 3: Remover todos os arquivos
**Esperado**: Card de custos deve ainda permitir cálculo (se houver custos)

### Erro 4: Upload de arquivo não suportado
**Esperado**: Mensagem de erro apropriada

---

## Logs e Debug

### PHP Errors
Verificar logs do servidor PHP para erros:
```bash
tail -f /var/log/php/error.log
```

### JavaScript Console
Abrir DevTools e verificar:
- Erros de JavaScript
- Requisições AJAX falhadas
- Warnings de console

### Database
Verificar se sessões estão sendo gravadas corretamente:
```sql
SELECT * FROM sessions WHERE user_id = [seu_user_id] ORDER BY created_at DESC LIMIT 1;
```

---

## Contato em Caso de Problemas

Se algum teste falhar, anotar:
1. **Passos exatos** até o erro
2. **Mensagem de erro** (se houver)
3. **Screenshot** do problema
4. **Logs** do PHP e JavaScript console
5. **Navegador e versão** usados

---

**Última atualização**: Novembro 2024  
**Versão do arquivo**: budget_c_v6.php
