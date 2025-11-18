# 📧 Como Usar o Sistema de Emails

## 🎯 Visão Rápida

O Sistema de Emails permite enviar comunicações para seus usuários de forma fácil e organizada. Você pode:
- ✉️ Enviar emails para todos os usuários ou grupos específicos
- 📅 Criar convites automáticos para palestras agendadas
- 📝 Usar templates prontos ou criar suas próprias mensagens
- 📊 Ver estatísticas e histórico completo

---

## 🚀 Acesso Rápido

1. **URL de Acesso:** `/admin/emails.php`
2. **Requisito:** Precisa estar logado como administrador
3. **Tempo de Configuração:** 2-5 minutos

---

## 📋 Passo a Passo: Enviar Email

### 1️⃣ Escolha os Destinatários

Na seção "Enviar E-mail", selecione:

- **Todos os usuários** → Envia para todos os usuários ativos
- **Apenas assinantes** → Apenas para quem tem assinatura ativa
- **Não assinantes** → Usuários sem assinatura

💡 **Dica:** O sistema mostra quantos usuários receberão o email entre parênteses.

### 2️⃣ Preencha o Assunto

Digite um assunto claro e direto. Exemplos:
- ✅ "Convite: Palestra sobre Tradução Médica"
- ✅ "Novidade: Nova funcionalidade disponível"
- ✅ "Lembrete: Palestra hoje às 19h"

### 3️⃣ Adicione um Link (Opcional)

Se sua mensagem inclui um link (Zoom, Google Meet, página da plataforma):
1. Cole o link completo no campo "Link de Acesso"
2. Use `[LINK]` no corpo da mensagem onde quer que o link apareça

**Exemplo:**
```
Link de Acesso: https://zoom.us/j/123456789

Mensagem:
Acesse a palestra pelo link: [LINK]
```

### 4️⃣ Escreva a Mensagem

**Personalize com tags:**
- `[NOME]` → Será substituído pelo nome de cada destinatário
- `[LINK]` → Será substituído pelo link de acesso (se fornecido)

**Exemplo de mensagem:**
```
Olá [NOME],

Convidamos você para nossa próxima palestra sobre Tradução Audiovisual!

Data: 25/11/2024
Horário: 19:00h

Acesse pelo link: [LINK]

Até lá!
Equipe Translators101
```

### 5️⃣ Enviar

1. Clique em **"Enviar E-mail"**
2. Confirme o envio
3. Aguarde a mensagem de confirmação

---

## 🎨 Usar Templates Prontos

Na seção "Templates Sugeridos", clique em:

### 📮 Boas-vindas
Para novos usuários que acabaram de se cadastrar

### 📰 Newsletter
Para compartilhar novidades e atualizações da plataforma

### 💰 Promoção
Para divulgar ofertas e descontos especiais

### 🔔 Lembrete
Para avisos importantes e lembretes

**Como usar:**
1. Clique no template desejado
2. O formulário será preenchido automaticamente
3. Edite conforme necessário
4. Envie!

---

## 📅 Enviar Convite para Próxima Palestra

Se há uma palestra agendada, você verá um card destacado no topo.

### Passos:

1. Clique em **"Usar Template de Convite para Esta Palestra"**
2. O formulário será preenchido com:
   - ✅ Título da palestra
   - ✅ Nome do palestrante
   - ✅ Data e horário
   - ✅ Descrição
3. **Adicione o link de acesso** (Zoom, Meet, etc.)
4. Escolha os destinatários
5. Envie!

**Resultado:**
```
Olá João Silva,

Temos o prazer de convidá-lo(a) para a nossa próxima palestra:

📌 Título: Introdução à Tradução Audiovisual
👤 Palestrante: Maria Santos
📅 Data: 25/11/2024
🕐 Horário: 19:00h

📝 Sobre a palestra:
Nesta palestra, você aprenderá...

🔗 Link de acesso: https://zoom.us/j/123456789

Equipe Translators101
```

---

## 📊 Ver Histórico de Envios

Na seção "Histórico de Envios", você pode:

- 📅 Ver data e hora de cada envio
- 📧 Ver assunto e quantidade de destinatários
- ✅ Verificar status (Enviado/Simulado/Falhou)
- 👁️ Visualizar conteúdo completo do email
- 👤 Ver quem enviou

**Para ver o conteúdo:**
Clique no ícone 👁️ na coluna "Ações"

---

## 📈 Entender as Estatísticas

No topo da página, você vê 4 cards:

### 👥 Total de Usuários
Quantidade de usuários ativos na plataforma

### ⭐ Assinantes
Usuários com assinatura ativa (pagantes)

### 👤 Não Assinantes
Usuários sem assinatura (gratuitos)

### 📨 E-mails Enviados
Total de emails no histórico

---

## ⚠️ Modo Simulação vs. Envio Real

### 🟡 Modo Simulação

**Quando acontece:**
- PHPMailer não está instalado OU
- Credenciais SMTP não foram configuradas

**O que acontece:**
- ✅ Sistema conta quantos emails seriam enviados
- ✅ Registra no histórico com status "Simulado"
- ✅ Mostra todas as funcionalidades
- ❌ **NÃO envia emails reais**

**Aviso exibido:**
> ⚠️ PHPMailer não está configurado. Os emails serão apenas simulados.

### 🟢 Envio Real

**Quando acontece:**
- PHPMailer instalado E
- Credenciais SMTP configuradas

**O que acontece:**
- ✅ Envia emails reais para todos os destinatários
- ✅ Personaliza com nome de cada usuário
- ✅ Registra no histórico com status "Enviado"
- ✅ Mostra quantidade de sucessos e falhas

---

## 💡 Dicas e Boas Práticas

### ✅ Faça:

1. **Teste primeiro em modo simulação**
   - Verifique quantos destinatários
   - Revise a mensagem com calma

2. **Use personalização**
   - Sempre use `[NOME]` para tornar o email mais pessoal

3. **Seja claro no assunto**
   - Assuntos claros têm maior taxa de abertura

4. **Inclua informações importantes**
   - Data, horário e link de acesso sempre visíveis

5. **Revise antes de enviar**
   - Verifique ortografia e formatação

### ❌ Evite:

1. **Enviar sem revisar**
   - Emails enviados não podem ser recolhidos

2. **Assuntos genéricos**
   - ❌ "Olá"
   - ✅ "Convite: Palestra sobre TAV hoje às 19h"

3. **Textos muito longos**
   - Mantenha mensagens concisas e objetivas

4. **Esquecer o link**
   - Se mencionar um link, sempre incluí-lo

5. **Ignorar as estatísticas**
   - Verifique quantos destinatários antes de enviar

---

## 🔧 Solução de Problemas

### Problema: "Nenhum destinatário encontrado"

**Causa:** Não há usuários que correspondem ao filtro selecionado

**Solução:**
1. Verifique se há usuários cadastrados
2. Tente selecionar "Todos os usuários"
3. Verifique o status dos usuários no banco

### Problema: Email não personaliza [NOME]

**Causa:** Tag `[NOME]` está escrita errada ou falta no banco

**Solução:**
- Verifique se escreveu exatamente `[NOME]` (maiúsculas)
- Confirme que os usuários têm nome cadastrado

### Problema: Link não aparece

**Causa:** Tag `[LINK]` não foi usada na mensagem

**Solução:**
1. Cole o link no campo "Link de Acesso"
2. Digite `[LINK]` onde quer que o link apareça na mensagem

### Problema: Sistema em modo simulação

**Causa:** PHPMailer não configurado

**Solução:**
1. Instale o PHPMailer (veja INSTALACAO_MANUAL.md)
2. Configure credenciais SMTP em `/config/email_config.php`

---

## 🎓 Exemplos Práticos

### Exemplo 1: Newsletter Mensal

```
Destinatários: Todos os usuários
Assunto: Newsletter Translators101 - Novembro 2024

Mensagem:
Olá [NOME],

Confira as novidades deste mês:

✨ 3 novas palestras adicionadas
📚 Glossário atualizado com 500+ termos
🎓 Certificados agora disponíveis em PDF

Acesse nossa plataforma e explore todo o conteúdo!

Equipe Translators101
```

### Exemplo 2: Convite para Palestra

```
Destinatários: Apenas assinantes
Assunto: Palestra Exclusiva: Tradução Jurídica - Amanhã 19h
Link: https://zoom.us/j/123456789

Mensagem:
Olá [NOME],

Sua presença é importante na nossa palestra exclusiva!

📌 Tema: Tradução Jurídica na Prática
👤 Palestrante: Dr. João Silva
📅 Data: 25/11/2024
🕐 Horário: 19:00h

🔗 Link da palestra: [LINK]

Nos vemos lá!
Equipe Translators101
```

### Exemplo 3: Promoção

```
Destinatários: Não assinantes
Assunto: 🎉 50% OFF - Oferta por Tempo Limitado!

Mensagem:
Olá [NOME],

Aproveite nossa oferta especial!

🎯 50% de desconto na assinatura anual
⏰ Válido até 30/11/2024
✨ Acesso completo a todas as palestras

Use o cupom: TRADU50

Não perca!
Equipe Translators101
```

---

## 📞 Precisa de Ajuda?

Consulte estes arquivos:
- 📖 **SISTEMA_EMAILS_README.md** → Documentação técnica completa
- 🔧 **INSTALACAO_MANUAL.md** → Guia de instalação passo a passo
- 🧪 **test_email_system.php** → Teste o sistema (acesse via navegador)

---

**🎉 Pronto! Agora você sabe tudo sobre o sistema de emails!**

Comece enviando um email de teste para você mesmo e explore todas as funcionalidades.
