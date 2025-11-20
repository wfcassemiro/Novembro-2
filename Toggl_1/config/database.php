<?php
/**
 * Configuração de Banco de Dados - Time Tracker
 * Reutiliza a configuração existente do site
 */

// Incluir configuração global do site
if (file_exists(__DIR__ . '/../../Nov_16/config/database.php')) {
    require_once __DIR__ . '/../../Nov_16/config/database.php';
} else {
    // Configuração standalone caso o arquivo principal não exista
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_domain', '.translators101.com');
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Lax');
        session_start();
    }

    $host = 'localhost';
    $db   = 'u335416710_t101_db';
    $user = 'u335416710_t101';
    $pass = 'Pa392ap!';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
    }

    // Funções auxiliares de autenticação
    if (!function_exists('isLoggedIn')) {
        function isLoggedIn() {
            return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        }
    }

    if (!function_exists('isSubscriber')) {
        function isSubscriber() {
            return isLoggedIn() && (
                (isset($_SESSION['is_subscriber']) && $_SESSION['is_subscriber'] == 1) ||
                (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'subscriber')
            );
        }
    }
}

// Verificar autenticação para páginas protegidas
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Verificar se é subscriber
function requireSubscriber() {
    requireAuth();
    if (!isSubscriber()) {
        header('Location: /dash-t101/index.php?error=subscription_required');
        exit;
    }
}
