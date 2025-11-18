# 📝 Como Editar e Criar Templates de Email

## 📍 Onde Estão os Templates?

Os templates estão no arquivo: `/app/Nov_16/admin/emails.php`

**Localização exata:** Role até o final do arquivo, na seção `<script>`, dentro da função `useTemplate()`

---

## 🎨 Templates Atuais

### Estrutura dos Templates

```javascript
const templates = {
    welcome: {
        subject: 'Assunto do Email',
        message: 'Corpo da mensagem...'
    },
    newsletter: { ... },
    promotion: { ... },
    reminder: { ... }
};
```

---

## ✏️ Como Editar Templates Existentes

### PASSO 1: Abrir o Arquivo

Abra: `/app/Nov_16/admin/emails.php`

### PASSO 2: Localizar a Seção

Procure por:
```javascript
function useTemplate(type) {
    const templates = {
```

**Ou busque por:** `function useTemplate`

### PASSO 3: Editar Template

**Exemplo - Editar template de Boas-vindas:**

#### Antes:
```javascript
welcome: {
    subject: 'Bem-vindo(a) à Translators101!',
    message: 'Olá [NOME],\n\nSeja bem-vindo(a) à nossa plataforma...'
}
```

#### Depois:
```javascript
welcome: {
    subject: 'Bem-vindo(a) à Plataforma Translators101! 🎉',
    message: 'Olá [NOME],\n\nÉ um prazer ter você conosco!\n\nAqui na Translators101, você terá acesso a:\n\n• Palestras exclusivas\n• Glossários especializados\n• Certificados profissionais\n• E muito mais!\n\nComece explorando nossa videoteca agora mesmo.\n\nAbraços,\nEquipe Translators101'
}
```

### PASSO 4: Salvar

Salve o arquivo e teste no sistema!

---

## ➕ Como Criar Novos Templates

### EXEMPLO 1: Template de "Certificado Disponível"

#### PASSO 1: Adicionar no Objeto templates

Adicione dentro de `const templates = { ... }`:

```javascript
const templates = {
    welcome: { ... },
    newsletter: { ... },
    promotion: { ... },
    reminder: { ... },
    
    // ⬇️ NOVO TEMPLATE
    certificate: {
        subject: 'Seu Certificado está Disponível! 🎓',
        message: 'Olá [NOME],\n\nBoa notícia! Seu certificado já está disponível para download.\n\n📜 Palestra: [Nome da Palestra]\n✅ Status: Concluído com sucesso\n\nAcesse sua área de certificados e faça o download:\n[LINK]\n\nParabéns pela conclusão!\n\nEquipe Translators101'
    }
};
```

#### PASSO 2: Adicionar Card Visual

Procure pela seção:
```html
<!-- Templates Pré-definidos -->
<div class="video-card">
    <h2><i class="fas fa-file-alt"></i> Templates Sugeridos</h2>
```

Adicione um novo card:
```html
<div class="quick-action-card" onclick="useTemplate('certificate')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-blue">
        <i class="fas fa-certificate"></i>
    </div>
    <h3>Certificado</h3>
    <p>Notificar disponibilidade</p>
</div>
```

#### PASSO 3: Salvar e Testar

1. Salve o arquivo
2. Recarregue `/admin/emails.php`
3. O novo card aparecerá nos templates
4. Clique nele para testar!

---

## 🎯 Exemplos de Novos Templates

### Template: "Cancelamento de Palestra"

```javascript
cancelamento: {
    subject: 'Aviso Importante: Palestra Cancelada',
    message: 'Olá [NOME],\n\nInfelizmente, precisamos informar que a palestra agendada foi cancelada.\n\n📌 Palestra: [Título]\n📅 Data Original: [Data]\n\n❓ Motivo: [Explicação]\n\n🔄 Reagendamento: Em breve divulgaremos nova data.\n\nPedimos desculpas pelo transtorno.\n\nEquipe Translators101'
}
```

**Card HTML:**
```html
<div class="quick-action-card" onclick="useTemplate('cancelamento')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-red">
        <i class="fas fa-times-circle"></i>
    </div>
    <h3>Cancelamento</h3>
    <p>Avisar cancelamento</p>
</div>
```

---

### Template: "Pesquisa de Satisfação"

```javascript
pesquisa: {
    subject: 'Sua opinião é importante para nós! 📊',
    message: 'Olá [NOME],\n\nQueremos melhorar cada vez mais nossos serviços!\n\nPoderia dedicar 2 minutos para responder nossa pesquisa de satisfação?\n\n🔗 Link da pesquisa: [LINK]\n\nSua opinião nos ajuda a criar conteúdos ainda melhores.\n\nAgradecemos sua participação!\n\nEquipe Translators101'
}
```

**Card HTML:**
```html
<div class="quick-action-card" onclick="useTemplate('pesquisa')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-purple">
        <i class="fas fa-poll"></i>
    </div>
    <h3>Pesquisa</h3>
    <p>Coletar feedback</p>
</div>
```

---

### Template: "Upgrade de Plano"

```javascript
upgrade: {
    subject: 'Upgrade seu Plano e Tenha Mais Benefícios! ⭐',
    message: 'Olá [NOME],\n\nVocê está aproveitando bem nossa plataforma!\n\nQue tal desbloquear ainda mais recursos?\n\n✨ Com o plano Premium você ganha:\n• Acesso ilimitado a todas as palestras\n• Certificados profissionais\n• Suporte prioritário\n• Glossários exclusivos\n• E muito mais!\n\n🎁 Oferta especial: 20% OFF usando o cupom UPGRADE20\n\nFaça upgrade agora: [LINK]\n\nEquipe Translators101'
}
```

**Card HTML:**
```html
<div class="quick-action-card" onclick="useTemplate('upgrade')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-green">
        <i class="fas fa-arrow-up"></i>
    </div>
    <h3>Upgrade</h3>
    <p>Oferecer plano premium</p>
</div>
```

---

### Template: "Lembrete de Palestra"

```javascript
lembrete_palestra: {
    subject: '⏰ Lembrete: Palestra Começa em 1 Hora!',
    message: 'Olá [NOME],\n\nA palestra que você se inscreveu começa em 1 HORA!\n\n📌 Título: [Título da Palestra]\n👤 Palestrante: [Nome]\n🕐 Horário: [Hora]\n\n🔗 Link de acesso: [LINK]\n\n💡 Dica: Entre 5 minutos antes para testar áudio e vídeo.\n\nNos vemos lá!\n\nEquipe Translators101'
}
```

**Card HTML:**
```html
<div class="quick-action-card" onclick="useTemplate('lembrete_palestra')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-red">
        <i class="fas fa-clock"></i>
    </div>
    <h3>Lembrete</h3>
    <p>Palestra próxima</p>
</div>
```

---

## 🎨 Cores dos Ícones

```javascript
// Adicione uma das classes abaixo ao ícone
quick-action-icon-blue     // Azul
quick-action-icon-purple   // Roxo
quick-action-icon-green    // Verde
quick-action-icon-red      // Vermelho
quick-action-icon-orange   // Laranja (adicionar no CSS)
```

Para adicionar nova cor (ex: laranja):

Adicione no `<style>` do arquivo:
```css
.quick-action-icon-orange { color: #f59e0b; }
```

---

## 📝 Tags Disponíveis nos Templates

Use estas tags na mensagem para personalização automática:

| Tag | Substituição | Exemplo |
|-----|-------------|---------|
| `[NOME]` | Nome do destinatário | "João Silva" |
| `[LINK]` | Link inserido no campo | URL do Zoom/Meet |

**Uso:**
```javascript
message: 'Olá [NOME],\n\nAcesse pelo link: [LINK]'
```

**Resultado enviado:**
```
Olá João Silva,

Acesse pelo link: https://zoom.us/j/123456789
```

---

## 📍 Localização Exata no Código

### Seção de Templates (Linha ~430-480)

```javascript
<script>
// ... outras funções ...

function useTemplate(type) {
    const templates = {
        welcome: {
            subject: '...',
            message: '...'
        },
        // ⬇️ ADICIONE SEUS TEMPLATES AQUI
    };
    
    if (templates[type]) {
        document.getElementById('subject').value = templates[type].subject;
        document.getElementById('message').value = templates[type].message;
        document.getElementById('access_link').value = '';
        document.getElementById('lecture_id').value = '';
    }
}
</script>
```

### Seção de Cards (Linha ~300-350)

```html
<!-- Templates Pré-definidos -->
<div class="video-card">
    <h2><i class="fas fa-file-alt"></i> Templates Sugeridos</h2>
    
    <div class="quick-actions-grid" style="display: grid; ...">
        <!-- Cards existentes -->
        
        <!-- ⬇️ ADICIONE SEUS CARDS AQUI -->
        
    </div>
</div>
```

---

## 🔧 Exemplo Completo: Template "Manutenção"

### 1. Adicionar Template JavaScript

```javascript
const templates = {
    welcome: { ... },
    newsletter: { ... },
    promotion: { ... },
    reminder: { ... },
    
    // NOVO TEMPLATE DE MANUTENÇÃO
    manutencao: {
        subject: '🔧 Manutenção Programada - Translators101',
        message: 'Olá [NOME],\n\nInformamos que realizaremos uma manutenção programada em nossa plataforma:\n\n📅 Data: [Data]\n🕐 Horário: [Horário de início] às [Horário de término]\n⏱️ Duração prevista: [X] horas\n\n🔧 Durante este período:\n• A plataforma ficará temporariamente indisponível\n• Nenhum dado será perdido\n• Tudo voltará ao normal após a manutenção\n\n💡 Objetivo: Melhorias de performance e segurança\n\nAgradecemos sua compreensão!\n\nEquipe Translators101'
    }
};
```

### 2. Adicionar Card HTML

```html
<div class="quick-action-card" onclick="useTemplate('manutencao')" style="cursor: pointer;">
    <div class="quick-action-icon quick-action-icon-orange">
        <i class="fas fa-tools"></i>
    </div>
    <h3>Manutenção</h3>
    <p>Avisar indisponibilidade</p>
</div>
```

### 3. Adicionar Cor Laranja (se ainda não existir)

No `<style>`:
```css
.quick-action-icon-orange { color: #f59e0b; }
```

### 4. Resultado

✅ Card "Manutenção" aparece nos templates  
✅ Ao clicar, preenche o formulário automaticamente  
✅ Mensagem com estrutura profissional  

---

## ✅ Checklist de Novo Template

- [ ] Adicionado no objeto `templates`
- [ ] Nome único (ex: `meu_template`)
- [ ] Subject definido
- [ ] Message definida
- [ ] Card HTML criado
- [ ] Ícone Font Awesome escolhido
- [ ] Cor do ícone definida
- [ ] `onclick="useTemplate('meu_template')"` correto
- [ ] Testado no navegador
- [ ] Funciona corretamente

---

## 🎯 Dicas Profissionais

### ✅ Faça:

1. **Use emojis com moderação**
   ```
   ✅ Bom: "Bem-vindo! 🎉"
   ❌ Excessivo: "🎉🎊✨ Bem-vindo!!! 🎈🎁🎀"
   ```

2. **Mantenha mensagens concisas**
   - Ideal: 150-300 palavras
   - Use parágrafos curtos
   - Bullets para listas

3. **Personalize sempre**
   - Use [NOME] em toda mensagem
   - Torne o email pessoal

4. **Inclua Call-to-Action (CTA)**
   ```
   "Acesse agora: [LINK]"
   "Clique aqui para..."
   "Não perca!"
   ```

5. **Teste antes de usar**
   - Envie para você mesmo
   - Verifique formatação
   - Teste tags [NOME] e [LINK]

### ❌ Evite:

1. **Mensagens muito longas**
   - Pessoas não leem textos enormes em emails

2. **Assuntos genéricos**
   - ❌ "Olá"
   - ✅ "Sua palestra começa em 1 hora!"

3. **Excesso de formatação**
   - Simples é melhor
   - Use `\n\n` para parágrafos

4. **Esquecer [LINK] quando mencionar link**
   ```
   ❌ "Acesse o link enviado"
   ✅ "Acesse: [LINK]"
   ```

---

## 📚 Recursos de Ícones

### Font Awesome (já incluído no sistema)

**Ícones comuns:**
- `fa-envelope` - Email
- `fa-bell` - Notificação
- `fa-certificate` - Certificado
- `fa-graduation-cap` - Educação
- `fa-users` - Comunidade
- `fa-star` - Destaque
- `fa-gift` - Promoção
- `fa-clock` - Tempo
- `fa-calendar` - Evento
- `fa-video` - Vídeo
- `fa-file-alt` - Documento
- `fa-tools` - Manutenção
- `fa-exclamation-triangle` - Alerta

**Ver todos:** https://fontawesome.com/icons

---

## 🔄 Backup Antes de Editar

**Sempre faça backup antes de editar:**

```bash
cp /app/Nov_16/admin/emails.php /app/Nov_16/admin/emails.php.backup
```

**Para restaurar (se algo der errado):**

```bash
cp /app/Nov_16/admin/emails.php.backup /app/Nov_16/admin/emails.php
```

---

## 🎉 Pronto!

Agora você sabe:
- ✅ Onde estão os templates
- ✅ Como editar templates existentes
- ✅ Como criar novos templates
- ✅ Como adicionar cards visuais
- ✅ Como personalizar cores e ícones

**Explore sua criatividade e crie templates incríveis para sua comunicação!** 📧✨
