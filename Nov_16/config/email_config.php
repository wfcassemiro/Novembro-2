<?php
/**
 * Configuração do PHPMailer
 * Sistema: Translators101
 */

// Configurações SMTP
define('SMTP_HOST', 'smtp.gmail.com');  // Ou seu servidor SMTP
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');  // tls ou ssl
define('SMTP_USERNAME', ''); // Será preenchido pelo admin
define('SMTP_PASSWORD', ''); // Será preenchido pelo admin
define('SMTP_FROM_EMAIL', ''); // Email remetente
define('SMTP_FROM_NAME', 'Translators101');

/**
 * Verifica se as credenciais de email estão configuradas
 */
function isEmailConfigured() {
    return !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD) && !empty(SMTP_FROM_EMAIL);
}
