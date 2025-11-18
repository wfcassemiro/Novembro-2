#!/bin/bash

echo "========================================="
echo "Instalação do Sistema de Emails"
echo "========================================="
echo ""

# 1. Verificar se composer existe
if ! command -v composer &> /dev/null; then
    echo "❌ Composer não encontrado. Instalando..."
    cd /tmp
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo "✅ Composer instalado"
else
    echo "✅ Composer já instalado"
fi

# 2. Criar diretório vendor se não existir
cd /app/Nov_16
if [ ! -d "vendor" ]; then
    echo "Criando estrutura de diretórios..."
    mkdir -p vendor
fi

# 3. Criar composer.json se não existir
if [ ! -f "composer.json" ]; then
    echo "Criando composer.json..."
    cat > composer.json << 'EOF'
{
    "name": "translators101/email-system",
    "description": "Sistema de emails para Translators101",
    "require": {
        "phpmailer/phpmailer": "^6.8"
    }
}
EOF
    echo "✅ composer.json criado"
fi

# 4. Instalar PHPMailer
echo "Instalando PHPMailer..."
composer require phpmailer/phpmailer
echo "✅ PHPMailer instalado"

# 5. Criar tabela email_logs no banco de dados
echo ""
echo "Criando tabela email_logs no banco de dados..."
mysql -u u335416710_t101_user -pT101@2024Secure u335416710_t101_db < /app/Nov_16/sql/create_email_logs.sql
if [ $? -eq 0 ]; then
    echo "✅ Tabela email_logs criada com sucesso"
else
    echo "⚠️ Erro ao criar tabela (pode já existir)"
fi

echo ""
echo "========================================="
echo "Instalação Concluída!"
echo "========================================="
echo ""
echo "Próximos passos:"
echo "1. Configure as credenciais SMTP em /app/Nov_16/config/email_config.php"
echo "2. Acesse /admin/emails.php para usar o sistema"
echo ""
echo "Campos a configurar:"
echo "  - SMTP_HOST (ex: smtp.gmail.com)"
echo "  - SMTP_USERNAME (seu email)"
echo "  - SMTP_PASSWORD (senha de app do Gmail)"
echo "  - SMTP_FROM_EMAIL (email remetente)"
echo ""
