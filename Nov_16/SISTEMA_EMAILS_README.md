# 📧 Sistema de Emails - Translators101

## 📋 Visão Geral

Sistema completo de envio de emails para a plataforma Translators101, com funcionalidades de:
- ✅ Envio para todos os usuários, assinantes ou não-assinantes
- ✅ Templates pré-definidos personalizáveis
- ✅ Integração automática com próximas palestras agendadas
- ✅ Personalização de emails com nome do destinatário
- ✅ Histórico completo de envios
- ✅ Suporte para links de acesso (Zoom, Google Meet, etc.)
- ✅ Modo simulação (quando PHPMailer não está configurado)

---

## 🚀 Instalação

### Passo 1: Executar Script de Instalação

```bash
cd /app/Nov_16
bash setup_email_system.sh
```

Este script irá:
1. Instalar o Composer (se necessário)
2. Instalar o PHPMailer via Composer
3. Criar a tabela `email_logs` no banco de dados

### Passo 2: Configurar Credenciais SMTP

Edite o arquivo `/app/Nov_16/config/email_config.php`:

```php
// Exemplo para Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'seu-email@gmail.com');
define('SMTP_PASSWORD', 'sua-senha-de-app');  // ⚠️ Use senha de app, não a senha normal
define('SMTP_FROM_EMAIL', 'seu-email@gmail.com');
define('SMTP_FROM_NAME', 'Translators101');
```

#### Como Gerar Senha de App no Gmail:
1. Acesse: https://myaccount.google.com/security
2. Ative a verificação em duas etapas
3. Vá em "Senhas de app"
4. Gere uma nova senha para "Email"
5. Use essa senha no campo `SMTP_PASSWORD`

---

## 📁 Estrutura de Arquivos

```
/app/Nov_16/
├── admin/
│   └── emails.php               # Interface principal do sistema
├── config/
│   ├── database.php             # Configuração do banco de dados
│   └── email_config.php         # Configuração do SMTP/PHPMailer
├── sql/
│   └── create_email_logs.sql    # SQL para criar tabela de logs
├── vendor/                      # Dependências do Composer (PHPMailer)
├── composer.json                # Gerenciador de dependências
└── setup_email_system.sh        # Script de instalação
```

---

## 🔧 Funcionalidades

### 1. Envio de Emails

**Destinatários disponíveis:**
- **Todos os usuários**: Envia para todos os usuários ativos
- **Apenas assinantes**: Filtra por:
  - `is_subscriber = 1` OU
  - `role = 'subscriber'` OU
  - `subscription_expires > NOW()`
- **Não assinantes**: Usuários que não se encaixam nos critérios acima

**Personalização:**
- Use `[NOME]` no corpo da mensagem para inserir o nome do destinatário
- Use `[LINK]` para inserir o link de acesso informado

### 2. Templates Pré-definidos

O sistema inclui 4 templates prontos:
1. **Boas-vindas**: Para novos usuários
2. **Newsletter**: Novidades da plataforma
3. **Promoção**: Ofertas especiais
4. **Lembrete**: Informações importantes

### 3. Integração com Palestras

Quando há uma palestra agendada, o sistema:
- Exibe automaticamente os detalhes da próxima palestra
- Oferece um botão para gerar template de convite
- Preenche automaticamente:
  - Título da palestra
  - Nome do palestrante
  - Data e horário
  - Descrição

### 4. Histórico de Envios

Tabela com logs de todos os emails enviados:
- Data e hora do envio
- Assunto
- Número de destinatários
- Status (Enviado/Simulado/Falhou)
- Quem enviou
- Visualização do conteúdo

---

## 🗃️ Estrutura do Banco de Dados

### Tabela `email_logs`

```sql
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `recipient_count` int(11) NOT NULL DEFAULT 0,
  `recipient_type` enum('all','subscribers','non_subscribers','selected'),
  `sent_by` varchar(36) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'sent',
  `lecture_id` int(11) DEFAULT NULL,
  `access_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

---

## 🔍 Correções Implementadas

### Problema Original
O arquivo `emails (1).php` tinha o seguinte erro:

```php
// ❌ ERRO: Campo subscription_active não existe
$stmt = $pdo->query("SELECT email, name FROM users WHERE active = 1 AND subscription_active = 1");
```

### Solução Implementada

```php
// ✅ CORRETO: Usa campos que existem na tabela users
$stmt = $pdo->query("
    SELECT email, name FROM users 
    WHERE is_active = 1 
    AND (
        is_subscriber = 1 
        OR role = 'subscriber' 
        OR (subscription_expires IS NOT NULL AND subscription_expires > NOW())
    )
");
```

---

## 📧 Exemplo de Uso

### Enviar Convite para Próxima Palestra

1. Acesse `/admin/emails.php`
2. Na seção "Próxima Palestra Agendada", clique em **"Usar Template de Convite"**
3. Preencha o campo **"Link de Acesso"** com o link do Zoom/Meet
4. Selecione os destinatários (Todos/Assinantes/Não-assinantes)
5. Revise a mensagem (já preenchida automaticamente)
6. Clique em **"Enviar E-mail"**

### Resultado do Email

```
Assunto: Convite: Introdução à Tradução Audiovisual

Olá João Silva,

Temos o prazer de convidá-lo(a) para a nossa próxima palestra:

📌 Título: Introdução à Tradução Audiovisual
👤 Palestrante: Maria Santos
📅 Data: 25/11/2024
🕐 Horário: 19:00h

📝 Sobre a palestra:
Nesta palestra, você aprenderá os fundamentos da tradução audiovisual...

🔗 Link de acesso: https://zoom.us/j/123456789

Não perca esta oportunidade de aprendizado!

Equipe Translators101
```

---

## ⚠️ Modo Simulação

Se o PHPMailer não estiver configurado, o sistema funciona em **modo simulação**:
- ✅ Conta quantos emails seriam enviados
- ✅ Salva o log com status `pending`
- ✅ Exibe mensagem informativa
- ❌ Não envia emails reais

Para sair do modo simulação, configure as credenciais SMTP em `config/email_config.php`.

---

## 🎨 Interface

### Estatísticas em Cards
- Total de usuários ativos
- Número de assinantes
- Número de não-assinantes
- Total de emails enviados

### Card de Próxima Palestra
- Imagem da palestra (se houver)
- Título, palestrante, data e horário
- Botão para usar template automático

### Formulário de Envio
- Seleção de destinatários
- Campo de assunto
- Campo para link de acesso
- Área de texto para mensagem
- Botões de ação

### Histórico
- Tabela com todos os envios
- Filtros e ordenação
- Visualização de conteúdo

---

## 🐛 Troubleshooting

### Erro: "Tabela email_logs não existe"
```bash
mysql -u u335416710_t101_user -pT101@2024Secure u335416710_t101_db < /app/Nov_16/sql/create_email_logs.sql
```

### Erro: "Class 'PHPMailer' not found"
```bash
cd /app/Nov_16
composer require phpmailer/phpmailer
```

### Erro: "SMTP Error: Could not authenticate"
- Verifique as credenciais em `config/email_config.php`
- Use senha de app do Gmail (não a senha normal)
- Certifique-se que a verificação em duas etapas está ativa

### Erro: "Connection refused"
- Verifique se o firewall permite conexões SMTP
- Teste as portas: 587 (TLS) ou 465 (SSL)

---

## 📊 Queries SQL Corrigidas

### Buscar Assinantes

```sql
SELECT email, name FROM users 
WHERE is_active = 1 
AND (
    is_subscriber = 1 
    OR role = 'subscriber' 
    OR (subscription_expires IS NOT NULL AND subscription_expires > NOW())
);
```

### Buscar Não-Assinantes

```sql
SELECT email, name FROM users 
WHERE is_active = 1 
AND (is_subscriber = 0 OR is_subscriber IS NULL)
AND role != 'subscriber'
AND (subscription_expires IS NULL OR subscription_expires <= NOW());
```

### Buscar Próxima Palestra

```sql
SELECT * FROM upcoming_announcements 
WHERE announcement_date >= CURDATE() 
AND is_active = 1
ORDER BY announcement_date ASC, lecture_time ASC 
LIMIT 1;
```

---

## 🔐 Segurança

### Boas Práticas Implementadas:
- ✅ Verificação de admin antes de acessar a página
- ✅ Prepared statements em todas as queries SQL
- ✅ Escape de HTML em outputs
- ✅ Validação de campos obrigatórios
- ✅ Confirmação antes de enviar emails
- ✅ Logs de todas as operações
- ✅ Tratamento de exceções

### Recomendações:
- 🔒 Mantenha as credenciais SMTP seguras
- 🔒 Use HTTPS em produção
- 🔒 Limite o acesso ao arquivo de configuração
- 🔒 Monitore os logs regularmente

---

## 📝 Changelog

### Versão 1.0 (Atual)
- ✅ Sistema completo de envio de emails
- ✅ Correção das queries SQL
- ✅ Integração com PHPMailer
- ✅ Templates personalizáveis
- ✅ Integração com palestras agendadas
- ✅ Histórico de envios
- ✅ Modo simulação
- ✅ Estatísticas de usuários
- ✅ Documentação completa

---

## 🤝 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Consulte os logs de erro do PHP
3. Teste a conexão SMTP manualmente
4. Verifique as permissões de arquivos

---

## 📄 Licença

Sistema proprietário - Translators101
© 2024 - Todos os direitos reservados
