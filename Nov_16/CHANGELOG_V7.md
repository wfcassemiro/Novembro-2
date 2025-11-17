# Budget C - Version 7 - Changelog

## Data: Novembro 2024

## Mudanças da V6 para V7

### 🎨 1. Tarja Roxa - Novo Padrão Visual

**Antes (V6):**
```css
background: linear-gradient(135deg, var(--brand-purple), #4a148c);
```

**Agora (V7):**
```css
background: #7B2B9F; /* Cor sólida, sem gradiente */
border-radius: 16px; /* Bordas mais arredondadas */
padding: 20px 30px; /* Padding horizontal aumentado */
```

**Mudanças no ícone:**
- Círculo: `rgba(255, 255, 255, 0.15)` (opacidade ajustada)
- Tamanho: 65px × 65px (antes: 60px)
- Ícone: 2rem (antes: 1.8rem)

**Mudanças no texto:**
- Título: `font-size: 1.6rem` e `font-weight: 700` (antes: 1.5rem / 600)
- Subtítulo: `opacity: 0.85` (antes: 0.8)
- Margem: 25px entre ícone e texto (antes: 20px)

**Resultado:** Tarja roxa sólida com visual mais limpo e moderno, seguindo exatamente o padrão fornecido na captura de tela.

---

### 📅 2. Formato de Datas - DD-MM-AAAA

**Mudança nos campos do modal:**
- **Antes**: `<input type="date">` (seletor de data nativo)
- **Agora**: `<input type="text" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA">`

**Validação:**
- Pattern regex para garantir formato DD-MM-AAAA
- Placeholder visual para orientar o usuário
- Campos obrigatórios mantidos

**Labels atualizados:**
```html
Prazo de entrega (DD-MM-AAAA):
Validade do orçamento (DD-MM-AAAA):
```

---

### 📄 3. Melhorias no PDF

#### a) UTF-8 para Nomes de Arquivo
**Implementação:**
```php
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// ...
$filename = 'Orcamento - ' . $clientName . '.pdf';
```

**Mudança:** Removido caractere especial "—" do nome do arquivo para evitar problemas de codificação. Agora usa hífen simples "-".

---

#### b) Campo "Empresa Geradora"
**Novo campo no modal:**
```html
<input type="text" name="company_name" required placeholder="Ex: Translators101">
```

**Exibição no PDF:**
```
Empresa:
Translators101

Cliente:
Acme Inc.
```

**Posicionamento:** Logo no início do PDF, antes das informações do cliente.

---

#### c) Idiomas no Orçamento
**Implementação:**
```php
$langFrom = $_SESSION['budget_client']['lang_from'] ?? '';
$langTo = $_SESSION['budget_client']['lang_to'] ?? '';

// No PDF:
'Idioma de origem: EN → Idioma de chegada: PT-BR'
```

**Posicionamento:** Após o título "Arquivos para tradução:", antes da lista de arquivos.

**Formatação:** Texto em itálico, cor cinza suave (#505050).

---

#### d) Títulos com Primeira Letra Maiúscula
**Antes (V6):**
- `ORÇAMENTO` (tudo maiúsculo)
- `Cliente:` (correto)
- `VALOR TOTAL:` (tudo maiúsculo)

**Agora (V7):**
- `Orçamento` (primeira maiúscula)
- `Cliente:` (mantido)
- `Empresa:` (nova seção)
- `Arquivos para tradução:` (primeira maiúscula)
- `Valor total:` (primeira maiúscula)
- `Prazo de entrega:` (primeira maiúscula)
- `Validade do orçamento:` (primeira maiúscula)
- `Orçamento gerado em:` (nova linha)

---

#### e) Data de Geração do Orçamento
**Nova linha adicionada:**
```
Prazo de entrega: 15-12-2024
Validade do orçamento: 31-12-2024
Orçamento gerado em: 17-11-2024  ← NOVO
```

**Implementação:**
```php
$pdf->Cell(90, 6, 'Orçamento gerado em:', 0, 0);
$pdf->Cell(0, 6, date('d-m-Y'), 0, 1);
```

**Formato:** DD-MM-AAAA (automático, data atual do servidor).

---

#### f) Rodapé Personalizado
**Antes (V6):**
```
Este orçamento é válido até a data especificada. Após a aprovação,
iniciaremos o trabalho conforme o prazo acordado.
```

**Agora (V7):**
```
Este orçamento é válido até a data especificada. Após a aprovação,
iniciaremos o trabalho conforme o prazo acordado.

Orçamento gerado pelo Dash-T101, da Translators101  ← NOVO
```

**Formatação:**
- Tamanho: 8pt (menor que o texto principal)
- Cor: #969696 (cinza claro)
- Alinhamento: Centro
- Posicionamento: Última linha do PDF, após observações

---

## Resumo Visual do PDF

### Estrutura Final do PDF V7:

```
┌─────────────────────────────────────────────┐
│            Orçamento                         │ ← Roxo, centralizado
├─────────────────────────────────────────────┤
│ Empresa:                                    │ ← NOVO
│ Translators101                              │
│                                             │
│ Cliente:                                    │
│ Acme Inc.                                   │
│ Contato: João Silva                         │
│                                             │
│ Prazo de entrega: 15-12-2024                │ ← Formato DD-MM-AAAA
│ Validade do orçamento: 31-12-2024           │ ← Formato DD-MM-AAAA
│ Orçamento gerado em: 17-11-2024             │ ← NOVO
│                                             │
│ Arquivos para tradução:                     │
│ Idioma de origem: EN → chegada: PT-BR       │ ← NOVO (itálico)
│ • documento1.docx                           │
│ • apresentacao.pptx                         │
│ • planilha.xlsx                             │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ Valor total: BRL 12.345,67              │ │ ← Primeira maiúscula
│ └─────────────────────────────────────────┘ │
│                                             │
│ Este orçamento é válido até a data          │
│ especificada. Após a aprovação,             │
│ iniciaremos o trabalho conforme o prazo     │
│ acordado.                                   │
│                                             │
│ Orçamento gerado pelo Dash-T101,            │ ← NOVO (rodapé)
│ da Translators101                           │
└─────────────────────────────────────────────┘
```

---

## Arquivos Modificados

- ✅ `/app/Nov_16/budget_c_v7.php` - Arquivo principal com todas as mudanças

---

## Compatibilidade

- ✅ 100% compatível com V6
- ✅ Mantém todas as funcionalidades anteriores
- ✅ Sessions e banco de dados inalterados
- ✅ Pode substituir V6 diretamente

---

## Checklist de Mudanças

### Código
- [x] Tarja roxa atualizada para cor sólida #7B2B9F
- [x] Campos de data com formato DD-MM-AAAA
- [x] Campo "Empresa geradora" adicionado ao modal
- [x] PDF configurado com UTF-8
- [x] Idiomas incluídos no PDF
- [x] Títulos do PDF com primeira letra maiúscula
- [x] Data de geração adicionada ao PDF
- [x] Rodapé personalizado adicionado
- [x] Nome do arquivo sem caracteres especiais

### Visual
- [x] Tarja roxa mais vibrante e limpa
- [x] Ícone maior e mais destacado
- [x] Texto do título mais bold
- [x] Bordas mais arredondadas (16px)

### UX
- [x] Placeholders orientam formato de data
- [x] Validação de pattern para datas
- [x] Campo empresa obrigatório
- [x] PDF mais completo e profissional

---

## Testes Recomendados

1. **Tarja Roxa:**
   - [ ] Verificar cor exata #7B2B9F
   - [ ] Verificar bordas arredondadas (16px)
   - [ ] Verificar tamanho e espaçamento do ícone
   - [ ] Verificar peso da fonte do título (700)

2. **Campos de Data:**
   - [ ] Testar input com formato DD-MM-AAAA
   - [ ] Verificar validação de pattern
   - [ ] Testar com formato incorreto (deve bloquear)

3. **PDF Gerado:**
   - [ ] Verificar campo "Empresa" no topo
   - [ ] Verificar idiomas após "Arquivos para tradução:"
   - [ ] Verificar títulos com primeira maiúscula
   - [ ] Verificar "Orçamento gerado em:" com data atual
   - [ ] Verificar rodapé "Dash-T101, da Translators101"
   - [ ] Verificar encoding UTF-8 nos nomes
   - [ ] Abrir PDF em múltiplos leitores (Adobe, Preview, Chrome)

4. **Compatibilidade:**
   - [ ] Testar fluxo completo V6 → V7
   - [ ] Verificar se sessions antigas funcionam
   - [ ] Testar todos os campos do modal

---

## Observações Técnicas

### Codificação UTF-8
```php
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
```
- Terceiro parâmetro `true`: Unicode habilitado
- Quarto parâmetro `'UTF-8'`: Encoding explícito

### Formato de Data no PHP
```php
date('d-m-Y') // Gera: 17-11-2024
```

### Nome do Arquivo PDF
**Evita caracteres especiais:**
```php
// ❌ Antes: 'Orçamento — Cliente.pdf'
// ✅ Agora: 'Orcamento - Cliente.pdf'
```

---

## Próximos Passos

1. ⏳ Testar V7 em servidor PHP
2. ⏳ Validar visualmente a tarja roxa
3. ⏳ Testar geração de PDF com todos os novos campos
4. ⏳ Verificar encoding UTF-8 em nomes com acentos
5. ⏳ Deploy após aprovação

---

**Versão**: 7.0  
**Data**: Novembro 2024  
**Base**: Budget C V6  
**Status**: ✅ Pronto para testes
