# 🚀 Configurar Sistema para Produção

## 📧 Passo a Passo para Envio Real de Emails

### PASSO 1: Instalar PHPMailer

#### Opção A: Via Composer (Recomendado)
```bash
cd /app/Nov_16
composer require phpmailer/phpmailer
```

#### Opção B: Download Manual
1. Acesse: https://github.com/PHPMailer/PHPMailer/releases
2. Baixe a versão mais recente (ex: `PHPMailer-6.8.0.zip`)
3. Extraia para: `/app/Nov_16/vendor/phpmailer/phpmailer/`

---

### PASSO 2: Configurar Credenciais SMTP

Edite o arquivo: `/app/Nov_16/config/email_config.php`

#### Para Gmail:

```php
<?php
// Configurações SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'seu-email@gmail.com');           // ⬅️ ALTERE AQUI
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');           // ⬅️ SENHA DE APP
define('SMTP_FROM_EMAIL', 'seu-email@gmail.com');         // ⬅️ ALTERE AQUI
define('SMTP_FROM_NAME', 'Translators101');

function isEmailConfigured() {
    return !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD) && !empty(SMTP_FROM_EMAIL);
}
?>
```

**⚠️ IMPORTANTE:** Use **SENHA DE APP** do Gmail, não sua senha normal!

---

### PASSO 3: Gerar Senha de App do Gmail

#### 3.1. Ativar Verificação em Duas Etapas
1. Acesse: https://myaccount.google.com/security
2. Role até "Verificação em duas etapas"
3. Clique em "Começar" e siga as instruções
4. Configure usando seu celular

#### 3.2. Gerar Senha de App
1. Após ativar 2FA, volte para: https://myaccount.google.com/security
2. Clique em "Senhas de app" (na seção "Verificação em duas etapas")
3. Selecione:
   - **App:** Email
   - **Dispositivo:** Outro (nome personalizado)
   - Digite: "Translators101"
4. Clique em "Gerar"
5. **Copie a senha gerada** (16 caracteres, ex: `abcd efgh ijkl mnop`)
6. Cole no arquivo `email_config.php` no campo `SMTP_PASSWORD`

**Formato da senha:**
```php
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');  // Senha de 16 caracteres com espaços
```

---

### PASSO 4: Testar Configuração

#### Teste Rápido:
1. Acesse: `/admin/emails.php`
2. O aviso amarelo deve ter **desaparecido**
3. Envie um email de teste para você mesmo

#### Teste Completo:
```
1. Destinatários: "Selecionar individualmente"
2. Marque apenas seu próprio usuário
3. OU use "Emails Externos" com seu email
4. Assunto: "Teste de Envio Real"
5. Mensagem: "Este é um teste do sistema."
6. Clique em "Enviar E-mail"
7. Verifique sua caixa de entrada
```

**✅ Sucesso:** Email chega na caixa de entrada  
**⚠️ Spam:** Email vai para spam (normal no início)  
**❌ Erro:** Verifique as credenciais

---

## 🔧 Outras Provedoras de Email

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'seu-email@outlook.com');
define('SMTP_PASSWORD', 'sua-senha');
define('SMTP_FROM_EMAIL', 'seu-email@outlook.com');
define('SMTP_FROM_NAME', 'Translators101');
```

### Yahoo Mail
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'seu-email@yahoo.com');
define('SMTP_PASSWORD', 'senha-de-app-yahoo');
define('SMTP_FROM_EMAIL', 'seu-email@yahoo.com');
define('SMTP_FROM_NAME', 'Translators101');
```

### Servidor SMTP Próprio (cPanel/Hospedagem)
```php
define('SMTP_HOST', 'mail.seudominio.com');
define('SMTP_PORT', 587);                        // ou 465 para SSL
define('SMTP_SECURE', 'tls');                    // ou 'ssl'
define('SMTP_USERNAME', 'contato@seudominio.com');
define('SMTP_PASSWORD', 'senha-do-email');
define('SMTP_FROM_EMAIL', 'contato@seudominio.com');
define('SMTP_FROM_NAME', 'Translators101');
```

**💡 Dica:** Consulte seu provedor de hospedagem para obter os dados SMTP corretos.

---

## ⚠️ Solução de Problemas

### Erro: "SMTP Error: Could not authenticate"
**Causa:** Senha incorreta ou senha de app não gerada

**Solução:**
1. Certifique-se que gerou a senha de app
2. Copie a senha exatamente como aparece
3. Inclua os espaços ou remova-os (ambos funcionam)

---

### Erro: "SMTP connect() failed"
**Causa:** Porta bloqueada ou host incorreto

**Solução:**
1. Tente porta 465 com SSL ao invés de 587 com TLS
2. Verifique se o firewall permite conexão SMTP
3. Teste com outro provedor

---

### Emails vão para Spam
**Causa:** Novo remetente sem reputação

**Solução:**
1. Configure SPF e DKIM no seu domínio
2. Peça aos destinatários para marcar como "não é spam"
3. Use email profissional (domínio próprio) ao invés de Gmail
4. Envie emails regularmente para construir reputação

---

### Erro: "Class 'PHPMailer' not found"
**Causa:** PHPMailer não instalado

**Solução:**
```bash
cd /app/Nov_16
composer require phpmailer/phpmailer
```

---

## ✅ Checklist de Produção

### Antes de Usar:
- [ ] PHPMailer instalado
- [ ] Credenciais SMTP configuradas
- [ ] Senha de app gerada (se Gmail)
- [ ] Teste enviado com sucesso
- [ ] Email recebido na caixa de entrada

### Primeiro Envio Real:
- [ ] Envie para você mesmo primeiro
- [ ] Revise formatação e conteúdo
- [ ] Teste links (se houver)
- [ ] Teste personalização [NOME]
- [ ] Depois envie para grupo pequeno (5-10)
- [ ] Por último, envie para todos

### Manutenção:
- [ ] Monitore taxa de entrega
- [ ] Verifique se emails vão para spam
- [ ] Atualize templates conforme necessário
- [ ] Revise histórico de envios regularmente

---

## 🎯 Resumo Rápido

### 3 Passos Essenciais:

1. **Instalar PHPMailer**
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Configurar SMTP**
   - Edite `/config/email_config.php`
   - Adicione suas credenciais
   - Use senha de app (Gmail)

3. **Testar**
   - Envie email de teste
   - Verifique recebimento
   - Pronto para produção! ✅

---

## 📞 Precisa de Ajuda?

### Se algo não funcionar:

1. **Execute o teste:** `/test_email_system.php`
2. **Verifique logs:** Seção 5 mostra status do PHPMailer
3. **Revise configurações:** Confira cada campo em `email_config.php`
4. **Teste com outro email:** Tente Gmail, depois Outlook

**Após configurar, o aviso amarelo desaparece e você pode enviar emails reais!** 🎉
