# 📦 Instalação Manual do Sistema de Emails

## ⚡ Opção 1: Instalação Rápida (Recomendado)

### Passo 1: Criar a Tabela email_logs

Acesse seu painel de controle do MySQL (phpMyAdmin, cPanel, etc.) e execute:

```sql
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `recipient_count` int(11) NOT NULL DEFAULT 0,
  `recipient_type` enum('all','subscribers','non_subscribers','selected') DEFAULT 'all',
  `sent_by` varchar(36) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'sent',
  `lecture_id` int(11) DEFAULT NULL COMMENT 'ID da palestra relacionada, se houver',
  `access_link` varchar(500) DEFAULT NULL COMMENT 'Link de acesso enviado no email',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sent_by` (`sent_by`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_lecture_id` (`lecture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Passo 2: Testar o Sistema (Modo Simulação)

1. Acesse: `/admin/emails.php`
2. O sistema funcionará em **modo simulação**
3. Você poderá testar todas as funcionalidades
4. Os emails não serão enviados, mas serão registrados nos logs

✅ **Pronto!** O sistema já está funcional em modo simulação.

---

## 🚀 Opção 2: Instalação Completa com PHPMailer

Se você deseja enviar emails reais, siga estes passos adicionais:

### Passo 1: Instalar o Composer

Via terminal SSH:

```bash
cd /app/Nov_16
curl -sS https://getcomposer.org/installer | php
php composer.phar require phpmailer/phpmailer
```

OU baixe manualmente:
1. Acesse: https://github.com/PHPMailer/PHPMailer/releases
2. Baixe a versão mais recente
3. Extraia na pasta `/app/Nov_16/vendor/phpmailer/`

### Passo 2: Configurar Credenciais SMTP

Edite o arquivo `/app/Nov_16/config/email_config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'seu-email@gmail.com');
define('SMTP_PASSWORD', 'sua-senha-de-app-do-gmail');
define('SMTP_FROM_EMAIL', 'seu-email@gmail.com');
define('SMTP_FROM_NAME', 'Translators101');
```

### Passo 3: Gerar Senha de App do Gmail

1. Acesse: https://myaccount.google.com/security
2. Ative "Verificação em duas etapas"
3. Clique em "Senhas de app"
4. Selecione "Email" e "Outro"
5. Copie a senha gerada
6. Cole no campo `SMTP_PASSWORD`

---

## 📋 Verificação da Instalação

### Verificar Tabela

Execute no MySQL:

```sql
SHOW TABLES LIKE 'email_logs';
DESCRIBE email_logs;
```

### Verificar Arquivos

Confirme que estes arquivos existem:

```
✅ /app/Nov_16/admin/emails.php
✅ /app/Nov_16/config/database.php
✅ /app/Nov_16/config/email_config.php
✅ /app/Nov_16/sql/create_email_logs.sql
```

### Testar Sistema

1. Acesse `/admin/emails.php`
2. Você deve ver:
   - ✅ Estatísticas de usuários
   - ✅ Formulário de envio
   - ✅ Histórico de envios (vazio inicialmente)
   - ✅ Templates sugeridos

---

## 🔧 Configurações Alternativas de SMTP

### Gmail
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Yahoo
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### SMTP Genérico (provedor de hospedagem)
```php
define('SMTP_HOST', 'mail.seudominio.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

---

## ❓ Perguntas Frequentes

### 1. O sistema funciona sem PHPMailer?
**Sim!** O sistema funciona em modo simulação:
- ✅ Conta destinatários
- ✅ Registra logs
- ✅ Mostra estatísticas
- ❌ Não envia emails reais

### 2. Como saber se está em modo simulação?
Um aviso amarelo aparece no topo da página:
> ⚠️ PHPMailer não está configurado. Os emails serão apenas simulados.

### 3. Posso usar meu próprio servidor SMTP?
**Sim!** Basta configurar as credenciais corretas em `config/email_config.php`.

### 4. Os dados dos usuários estão corretos?
O sistema agora usa os campos corretos:
- `is_active` ao invés de `active`
- `is_subscriber` ao invés de `subscription_active`
- `role` para verificar tipo de usuário
- `subscription_expires` para validar assinaturas

### 5. Como enviar para usuários específicos?
Na versão atual, você pode escolher entre:
- Todos os usuários
- Apenas assinantes
- Não assinantes

Para envio seletivo, você precisará modificar o código.

---

## 🐛 Troubleshooting

### Erro: "Table doesn't exist"
**Solução:** Execute o SQL da tabela `email_logs`

### Erro: "Access denied for user"
**Solução:** Verifique as credenciais em `config/database.php`

### Erro: "Class PHPMailer not found"
**Solução:** Instale o PHPMailer ou use o modo simulação

### Aviso: "Headers already sent"
**Solução:** Verifique se há espaços ou outputs antes de `<?php`

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs de erro do PHP
2. Consulte o arquivo `SISTEMA_EMAILS_README.md`
3. Teste com modo simulação primeiro
4. Verifique permissões de arquivos (644 para .php)

---

## ✅ Checklist de Instalação

- [ ] Tabela `email_logs` criada no banco
- [ ] Arquivo `emails.php` no diretório `/admin/`
- [ ] Arquivo `database.php` no diretório `/config/`
- [ ] Arquivo `email_config.php` no diretório `/config/`
- [ ] Sistema acessível em `/admin/emails.php`
- [ ] Estatísticas exibindo corretamente
- [ ] (Opcional) PHPMailer instalado
- [ ] (Opcional) Credenciais SMTP configuradas
- [ ] (Opcional) Teste de envio realizado

---

**🎉 Parabéns! Seu sistema de emails está pronto para usar!**
