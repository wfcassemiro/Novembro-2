<?php
/**
 * Script de Teste do Sistema de Emails
 * Execute via navegador: /test_email_system.php
 * ou via CLI: php test_email_system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Teste do Sistema de Emails</title>";
echo "<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
.test { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { color: #10b981; font-weight: bold; }
.error { color: #ef4444; font-weight: bold; }
.warning { color: #f59e0b; font-weight: bold; }
h1 { color: #333; }
h2 { color: #666; margin-top: 0; }
pre { background: #f8f8f8; padding: 10px; border-radius: 4px; overflow-x: auto; }
.status { display: inline-block; padding: 5px 10px; border-radius: 4px; margin-left: 10px; }
.status.ok { background: #d1fae5; color: #065f46; }
.status.fail { background: #fee2e2; color: #991b1b; }
.status.warn { background: #fef3c7; color: #92400e; }
</style></head><body>";

echo "<h1>🔍 Teste do Sistema de Emails - Translators101</h1>";

// Teste 1: Verificar arquivos essenciais
echo "<div class='test'>";
echo "<h2>1. Verificação de Arquivos</h2>";

$files = [
    '/app/Nov_16/admin/emails.php' => 'Interface principal',
    '/app/Nov_16/config/database.php' => 'Configuração do banco',
    '/app/Nov_16/config/email_config.php' => 'Configuração de email',
    '/app/Nov_16/sql/create_email_logs.sql' => 'Script SQL'
];

foreach ($files as $file => $desc) {
    $exists = file_exists($file);
    $status = $exists ? "<span class='status ok'>✓ OK</span>" : "<span class='status fail'>✗ FALTA</span>";
    echo "<p>{$desc}: <code>{$file}</code> {$status}</p>";
}
echo "</div>";

// Teste 2: Conexão com banco de dados
echo "<div class='test'>";
echo "<h2>2. Conexão com Banco de Dados</h2>";

try {
    require_once __DIR__ . '/config/database.php';
    echo "<p class='success'>✓ Conexão estabelecida com sucesso!</p>";
    
    // Verificar tabelas necessárias
    $tables = ['users', 'upcoming_announcements', 'email_logs'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $exists = $stmt->rowCount() > 0;
            $status = $exists ? "<span class='status ok'>✓ Existe</span>" : "<span class='status fail'>✗ Não existe</span>";
            echo "<p>Tabela <code>{$table}</code>: {$status}</p>";
            
            if ($table === 'email_logs' && !$exists) {
                echo "<p class='warning'>⚠️ Execute o SQL em /sql/create_email_logs.sql</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Erro ao verificar tabela {$table}</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Teste 3: Contar usuários
echo "<div class='test'>";
echo "<h2>3. Estatísticas de Usuários</h2>";

try {
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $total = $stmt->fetchColumn();
    echo "<p>Total de usuários ativos: <strong>{$total}</strong></p>";
    
    // Assinantes
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM users 
        WHERE is_active = 1 
        AND (
            is_subscriber = 1 
            OR role = 'subscriber' 
            OR (subscription_expires IS NOT NULL AND subscription_expires > NOW())
        )
    ");
    $subscribers = $stmt->fetchColumn();
    echo "<p>Assinantes: <strong>{$subscribers}</strong></p>";
    
    // Não assinantes
    $non_subscribers = $total - $subscribers;
    echo "<p>Não assinantes: <strong>{$non_subscribers}</strong></p>";
    
    if ($total > 0) {
        echo "<p class='success'>✓ Sistema pronto para enviar emails!</p>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum usuário ativo encontrado. Cadastre usuários antes de testar.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro ao buscar usuários: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Teste 4: Verificar palestras
echo "<div class='test'>";
echo "<h2>4. Próximas Palestras</h2>";

try {
    $stmt = $pdo->query("
        SELECT * FROM upcoming_announcements 
        WHERE announcement_date >= CURDATE() 
        AND is_active = 1
        ORDER BY announcement_date ASC, lecture_time ASC 
        LIMIT 1
    ");
    $lecture = $stmt->fetch();
    
    if ($lecture) {
        echo "<p class='success'>✓ Próxima palestra encontrada:</p>";
        echo "<ul>";
        echo "<li><strong>Título:</strong> " . htmlspecialchars($lecture['title']) . "</li>";
        echo "<li><strong>Palestrante:</strong> " . htmlspecialchars($lecture['speaker']) . "</li>";
        echo "<li><strong>Data:</strong> " . date('d/m/Y', strtotime($lecture['announcement_date'])) . "</li>";
        echo "<li><strong>Horário:</strong> " . date('H:i', strtotime($lecture['lecture_time'])) . "h</li>";
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ Nenhuma palestra agendada. A funcionalidade de convite automático não estará disponível.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro ao buscar palestras: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Teste 5: Verificar PHPMailer
echo "<div class='test'>";
echo "<h2>5. PHPMailer</h2>";

$phpmailer_available = class_exists('PHPMailer\\PHPMailer\\PHPMailer');

if ($phpmailer_available) {
    echo "<p class='success'>✓ PHPMailer está instalado</p>";
} else {
    echo "<p class='warning'>⚠️ PHPMailer não está instalado</p>";
    echo "<p>O sistema funcionará em <strong>modo simulação</strong>.</p>";
    echo "<p>Para instalar o PHPMailer:</p>";
    echo "<pre>cd /app/Nov_16\ncomposer require phpmailer/phpmailer</pre>";
}

require_once __DIR__ . '/config/email_config.php';
$is_configured = isEmailConfigured();

if ($is_configured) {
    echo "<p class='success'>✓ Credenciais SMTP configuradas</p>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> " . SMTP_HOST . "</li>";
    echo "<li><strong>Porta:</strong> " . SMTP_PORT . "</li>";
    echo "<li><strong>Remetente:</strong> " . SMTP_FROM_EMAIL . "</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠️ Credenciais SMTP não configuradas</p>";
    echo "<p>Configure em: <code>/config/email_config.php</code></p>";
}

if ($phpmailer_available && $is_configured) {
    echo "<p class='success'><strong>✓ Sistema pronto para enviar emails reais!</strong></p>";
} else {
    echo "<p class='warning'><strong>⚠️ Sistema funcionará em modo simulação</strong></p>";
}
echo "</div>";

// Teste 6: Verificar logs de email
echo "<div class='test'>";
echo "<h2>6. Logs de Email</h2>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM email_logs");
    $log_count = $stmt->fetchColumn();
    echo "<p>Total de emails no histórico: <strong>{$log_count}</strong></p>";
    
    if ($log_count > 0) {
        $stmt = $pdo->query("
            SELECT subject, recipient_count, status, created_at 
            FROM email_logs 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $logs = $stmt->fetchAll();
        
        echo "<p>Últimos 5 envios:</p>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f8f8;'>";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #ddd;'>Data</th>";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #ddd;'>Assunto</th>";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #ddd;'>Destinatários</th>";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #ddd;'>Status</th>";
        echo "</tr>";
        
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . date('d/m/Y H:i', strtotime($log['created_at'])) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($log['subject']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $log['recipient_count'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $log['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro ao verificar logs: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='warning'>A tabela email_logs provavelmente não existe. Execute o SQL de criação.</p>";
}
echo "</div>";

// Resumo final
echo "<div class='test' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>";
echo "<h2 style='color: white;'>📊 Resumo</h2>";

$all_ok = true;
$warnings = [];

if (!file_exists('/app/Nov_16/admin/emails.php')) {
    $all_ok = false;
    $warnings[] = "Arquivo emails.php não encontrado";
}

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_logs'");
    if ($stmt->rowCount() === 0) {
        $all_ok = false;
        $warnings[] = "Tabela email_logs não existe";
    }
} catch (Exception $e) {
    $all_ok = false;
    $warnings[] = "Erro ao verificar banco de dados";
}

if ($all_ok) {
    echo "<p style='font-size: 1.2em;'><strong>✅ Sistema Operacional!</strong></p>";
    echo "<p>O sistema de emails está pronto para uso.</p>";
    if (!$phpmailer_available || !$is_configured) {
        echo "<p>⚠️ Funcionando em modo simulação (instale o PHPMailer para envio real)</p>";
    }
    echo "<p><a href='/admin/emails.php' style='color: #ffd700; text-decoration: underline;'>Acessar Sistema de Emails →</a></p>";
} else {
    echo "<p style='font-size: 1.2em;'><strong>⚠️ Ação Necessária</strong></p>";
    echo "<p>Problemas encontrados:</p>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li>{$warning}</li>";
    }
    echo "</ul>";
    echo "<p>Consulte o arquivo <strong>INSTALACAO_MANUAL.md</strong> para instruções.</p>";
}
echo "</div>";

echo "</body></html>";
?>
