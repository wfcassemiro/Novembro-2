# Budget C - Versão 6
## Sistema de Orçamentos com Análise de Fuzzy Matches

---

## 📋 Resumo das Novidades da V6

A versão 6 adiciona **três novas funcionalidades principais** ao sistema de orçamentos:

1. **🗑️ Remover arquivos individualmente** - Permite remover arquivos já processados sem reiniciar o orçamento
2. **📄 Modal de preparação de PDF** - Interface para configurar os detalhes finais do orçamento antes de gerar o PDF
3. **📥 Geração de PDF profissional** - Cria documento formatado usando TCPDF com todas as informações do orçamento

Além disso, a **tarja roxa do header** foi ajustada para seguir o padrão visual correto usado em `projects.php`.

---

## 🚀 Funcionalidades Completas

### Fluxo de Trabalho

#### 1️⃣ Card: Cliente
- Selecionar cliente existente ou adicionar novo via modal
- Escolher serviço (Tradução ou Interpretação)
- Definir idiomas (De → Para)
- Selecionar moeda de trabalho

#### 2️⃣ Card: Pesos por Faixa
- Configurar pesos para cada faixa de fuzzy match:
  - 100% (match exato)
  - 95-99%
  - 85-94%
  - 75-84%
  - 50-74%
  - No Match

#### 3️⃣ Card: Selecionar Arquivos
- Upload múltiplo de arquivos
- Formatos suportados: `.docx`, `.pptx`, `.xlsx`, `.xls`, `.txt`, `.pdf`, `.html`, `.htm`, `.csv`, `.md`
- Processamento automático com análise de fuzzy matches
- Barra de progresso durante upload
- **✨ NOVO**: Lista de arquivos com botão para remover individualmente

#### 4️⃣ Card: Custos do Projeto
- Adicionar custos por fornecedor:
  - Interno
  - Freelancers cadastrados
  - Outros custos diversos
- Tipos de serviço:
  - **Tradução**: Custo × palavras ponderadas
  - **Pós-edição**: Custo × total de palavras
  - **Revisão**: Custo × total de palavras
  - **Diagramação**: Custo × páginas estimadas
- Configurar Markup (%) e Impostos (%)
- Remover custos adicionados
- Calcular orçamento final

#### 5️⃣ Cards de Resultados

**Resumo:**
- Total de palavras
- Total de segmentos
- Total ponderado
- Total de páginas estimadas

**Custo Total:**
- Soma de todos os custos calculados

**Preço Sugerido:**
- Subtotal (Custo + Markup)
- Impostos
- Preço final
- **✨ NOVO**: Botão "Preparar para enviar" (abre modal de PDF)

---

## 📄 Geração de PDF - Detalhes

### Como Funcionar

1. Após calcular o orçamento, clicar em **"Preparar para enviar"** (botão verde)
2. Modal se abre com dados pré-preenchidos:
   - **Preço final**: Vem do cálculo, mas pode ser editado
   - **Arquivos**: Lista com checkboxes (todos marcados), permite selecionar quais incluir
3. Preencher campos obrigatórios:
   - **Nome do contato**
   - **Prazo de entrega** (data)
   - **Validade do orçamento** (data)
4. Revisar/editar qualquer informação
5. Clicar em **"Gerar PDF"**
6. Download automático do arquivo: `Orçamento — {Nome do Cliente}.pdf`

### Conteúdo do PDF

```
┌─────────────────────────────────────┐
│        ORÇAMENTO                    │ ← Título em roxo, centralizado
├─────────────────────────────────────┤
│ Cliente:                            │
│ {Nome do Cliente}                   │
│ Contato: {Nome do Contato}          │
│                                     │
│ Prazo de Entrega: {Data}            │
│ Validade do Orçamento: {Data}       │
│                                     │
│ Arquivos para Tradução:             │
│ • arquivo1.docx                     │
│ • arquivo2.pdf                      │
│ • arquivo3.xlsx                     │
│                                     │
│ ┌─────────────────────────────────┐ │
│ │ VALOR TOTAL: BRL 12.345,67      │ │ ← Verde, destacado
│ └─────────────────────────────────┘ │
│                                     │
│ Este orçamento é válido até a data  │
│ especificada. Após a aprovação,     │
│ iniciaremos o trabalho conforme o   │
│ prazo acordado.                     │
└─────────────────────────────────────┘
```

### Formatação
- **Moeda**: Conforme configurado no cliente (BRL, USD, EUR, etc.)
- **Números**: Formato brasileiro (1.234,56)
- **Fonte**: Helvetica
- **Cores**: Roxo para títulos (#4A148C), verde para valor (#22C55E)
- **Tamanho**: A4
- **Margens**: 20mm em todos os lados

---

## 🗑️ Remover Arquivos - Detalhes

### Por que?
Em fluxos anteriores, se um arquivo fosse adicionado por engano ou não fosse mais necessário, era preciso reiniciar todo o orçamento. Agora é possível remover arquivos específicos.

### Como usar?
1. Após processar arquivos, a lista "Arquivos adicionados" aparece no card de arquivos
2. Cada arquivo tem um ícone **"×"** vermelho à direita
3. Clicar no ícone
4. Confirmar a ação no popup
5. Página recarrega automaticamente
6. Arquivo foi removido da lista e dos cálculos

### O que acontece?
- O arquivo é removido da sessão (`$_SESSION['analyses']`)
- Cálculos subsequentes não incluem mais esse arquivo
- Outros arquivos não são afetados
- Se remover todos os arquivos, é necessário fazer novo upload

---

## 🎨 Tarja Roxa Corrigida

### Antes
A tarja poderia estar com gradiente diferente ou estrutura inconsistente.

### Agora
```css
background: linear-gradient(135deg, var(--brand-purple), #4a148c);
```

- **Gradiente diagonal**: 135 graus (superior esquerdo → inferior direito)
- **Cores**: Do roxo da marca (var(--brand-purple)) para roxo escuro (#4a148c)
- **Estrutura**:
  - Ícone circular branco semi-transparente com ícone de cifrão
  - Título e subtítulo em branco
  - Padding e bordas arredondadas

### Visual
Agora a tarja está **100% consistente** com o padrão usado em `projects.php` e outros módulos do sistema.

---

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (compatível com 8.x)
- **Frontend**: JavaScript (Vanilla), HTML5, CSS3
- **PDF**: TCPDF (via Composer)
- **Database**: MySQL/MariaDB
- **AJAX**: Fetch API nativa do navegador
- **Session**: PHP Sessions para gerenciamento de estado

---

## 📦 Dependências

### Composer
```json
{
    "require": {
        "tecnickcom/tcpdf": "^6.6"
    }
}
```

### Extensões PHP Necessárias
- `pdo_mysql`
- `json`
- `session`
- `mbstring` (para TCPDF)
- `gd` ou `imagick` (opcional, para TCPDF)

---

## 📂 Estrutura de Arquivos

```
/app/Nov_16/
├── budget_c_v6.php          ← Arquivo principal (NOVO)
├── processor.php            ← Processamento de arquivos e fuzzy match
├── ajax_provider_rates.php  ← Endpoint para buscar taxas de fornecedores
├── CHANGELOG_V6.md          ← Este changelog
├── GUIA_TESTES_V6.md        ← Guia completo de testes
├── README_V6.md             ← Esta documentação
├── README.md                ← Documentação geral
├── README_V2.md             ← Changelog V2
├── CHANGELOG.md             ← Changelogs anteriores
├── FLUXO_VISUAL.md          ← Documentação do fluxo visual
├── INTEGRACAO_AJAX.md       ← Documentação da integração AJAX
├── GUIA_TESTES.md           ← Guia de testes geral
├── INDEX.md                 ← Índice de documentação
└── moedas_exemplo.sql       ← Script SQL de exemplo
```

---

## 🔧 Instalação / Deploy

### Passo 1: Verificar Dependências
```bash
cd /app
composer install
```

### Passo 2: Verificar TCPDF
```bash
composer show tecnickcom/tcpdf
```

Se não estiver instalado:
```bash
composer require tecnickcom/tcpdf
```

### Passo 3: Configurar Banco de Dados
Garantir que as tabelas existem:
- `dash_clients`
- `dash_freelancers`
- `dash_settings`

### Passo 4: Deploy do Arquivo
```bash
# Opção 1: Copiar para produção
cp /app/Nov_16/budget_c_v6.php /app/v/dash-t101/budget_c.php

# Opção 2: Renomear e testar primeiro
cp /app/Nov_16/budget_c_v6.php /app/v/dash-t101/budget_c_test.php
```

### Passo 5: Testar
Seguir o guia completo em `GUIA_TESTES_V6.md`

---

## 🧪 Testes Rápidos

### Teste 1: Header
✅ Acessar página e verificar tarja roxa com gradiente correto

### Teste 2: Remover Arquivo
✅ Upload → Processar → Clicar no "×" → Confirmar → Arquivo removido

### Teste 3: Gerar PDF
✅ Completar fluxo → "Preparar para enviar" → Preencher modal → "Gerar PDF" → PDF baixado

---

## 🐛 Troubleshooting

### PDF não é gerado
**Problema**: Erro ao clicar em "Gerar PDF"

**Soluções**:
1. Verificar se TCPDF está instalado: `composer show tecnickcom/tcpdf`
2. Verificar logs do PHP: `tail -f /var/log/php/error.log`
3. Verificar permissões de escrita no diretório temporário
4. Verificar extensão `mbstring` do PHP

### Modal não abre
**Problema**: Botão "Preparar para enviar" não faz nada

**Soluções**:
1. Abrir console do navegador (F12) e verificar erros JavaScript
2. Verificar se o orçamento foi calculado (step 5)
3. Limpar cache do navegador
4. Verificar se a função `showPdfModal()` está definida

### Arquivo não é removido
**Problema**: Clicar no "×" não remove o arquivo

**Soluções**:
1. Verificar console JavaScript para erros
2. Verificar se requisição AJAX está sendo enviada (Network tab)
3. Verificar se `$_SESSION['analyses']` existe
4. Verificar se o índice está correto

### Tarja roxa não aparece correta
**Problema**: Gradiente ou cores estão erradas

**Soluções**:
1. Verificar se `--brand-purple` está definido no CSS global
2. Limpar cache do navegador
3. Verificar se o CSS está sendo carregado corretamente
4. Inspecionar elemento e verificar estilos aplicados

---

## 📊 Compatibilidade

| Versão | Compatível | Notas |
|--------|------------|-------|
| V5     | ✅ Sim     | Todas as funcionalidades V5 mantidas |
| V4     | ✅ Sim     | Estrutura de dados compatível |
| V3     | ✅ Sim     | Pode migrar sessões |
| V2     | ⚠️ Parcial | Requer adaptação de dados |
| V1     | ❌ Não     | Estrutura muito diferente |

---

## 🔐 Segurança

### Implementações de Segurança

1. **Session-based**: Todos os dados em `$_SESSION`, não em localStorage
2. **CSRF Protection**: Usar tokens CSRF em produção (não implementado nesta versão)
3. **File Upload Validation**: Extensões permitidas filtradas
4. **SQL Prepared Statements**: Todas as queries usam prepared statements
5. **XSS Protection**: `htmlspecialchars()` em todas as saídas
6. **Error Handling**: Try-catch em operações críticas

### Recomendações Adicionais

- [ ] Implementar rate limiting em uploads
- [ ] Adicionar CSRF tokens em formulários
- [ ] Validar tamanho máximo de arquivo
- [ ] Sanitizar nomes de arquivo
- [ ] Implementar log de ações sensíveis

---

## 📈 Performance

### Otimizações Implementadas

1. **AJAX sem reload**: Reduz tempo de resposta
2. **Processamento assíncrono**: Upload com feedback visual
3. **Session storage**: Evita consultas repetidas ao DB
4. **Lazy loading**: Dados carregados sob demanda
5. **Progress indicators**: Feedback ao usuário durante operações longas

### Métricas Esperadas

- Upload e processamento de 1 arquivo: ~2-5 segundos
- Cálculo de orçamento: <1 segundo
- Geração de PDF: 1-3 segundos
- Remoção de arquivo: <1 segundo

---

## 🚧 Roadmap Futuro

### Funcionalidades Sugeridas

- [ ] **Templates de PDF**: Permitir múltiplos layouts
- [ ] **Histórico de orçamentos**: Salvar no banco de dados
- [ ] **Envio por email**: Enviar PDF diretamente ao cliente
- [ ] **Duplicar orçamento**: Copiar orçamento existente
- [ ] **Exportar Excel**: Gerar planilha além do PDF
- [ ] **Múltiplas moedas no PDF**: Mostrar conversões
- [ ] **Assinatura digital**: Assinar PDF com certificado
- [ ] **Drag & drop**: Upload de arquivos por arrastar
- [ ] **Preview do PDF**: Visualizar antes de baixar
- [ ] **Versionamento**: Manter histórico de versões do orçamento

---

## 👥 Créditos

**Desenvolvido para**: Sistema Dash-T101  
**Versão**: 6.0  
**Data**: Novembro 2024  
**Baseado em**: Budget C V5 (melhorias iterativas)

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consultar `GUIA_TESTES_V6.md`
2. Verificar `CHANGELOG_V6.md`
3. Revisar logs de erro (PHP e JavaScript)
4. Testar em ambiente de desenvolvimento primeiro

---

## 📝 Notas de Migração

### De V5 para V6

✅ **Totalmente retrocompatível**  
- Não é necessário migração de dados
- Sessions existentes continuam funcionando
- Banco de dados não requer alterações
- Apenas substituir o arquivo PHP

### Passos de Migração

1. **Backup**: Fazer backup do arquivo atual
```bash
cp budget_c.php budget_c_v5_backup.php
```

2. **Deploy**: Substituir pelo V6
```bash
cp budget_c_v6.php budget_c.php
```

3. **Teste**: Executar testes básicos (ver `GUIA_TESTES_V6.md`)

4. **Rollback** (se necessário):
```bash
cp budget_c_v5_backup.php budget_c.php
```

---

**Fim da Documentação V6** 🎉
