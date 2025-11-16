<?php
// /app/Nov_16/ajax_provider_rates.php
// Endpoint AJAX opcional para buscar taxas de fornecedores dinamicamente

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dash_database.php';
require_once __DIR__ . '/../config/dash_functions.php';

// Autorização
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json');

$providerId = isset($_GET['provider_id']) ? (int)$_GET['provider_id'] : 0;
$currentUserId = $_SESSION['user_id'] ?? null;

if (!$providerId || !$currentUserId) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

try {
    global $pdo;
    
    // Buscar informações do fornecedor
    $stmtProvider = $pdo->prepare("
        SELECT id, name, currency, services_offered 
        FROM dash_freelancers 
        WHERE id = :id AND user_id = :uid
    ");
    $stmtProvider->execute([
        ':id' => $providerId,
        ':uid' => $currentUserId
    ]);
    
    $provider = $stmtProvider->fetch(PDO::FETCH_ASSOC);
    
    if (!$provider) {
        echo json_encode(['error' => 'Fornecedor não encontrado']);
        exit;
    }
    
    // Buscar todas as taxas do fornecedor
    $stmtRates = $pdo->prepare("
        SELECT service, rate, unit, currency, lang_from, lang_to
        FROM dash_freelancer_rates
        WHERE freelancer_id = :fid
        ORDER BY service ASC
    ");
    $stmtRates->execute([':fid' => $providerId]);
    
    $rates = $stmtRates->fetchAll(PDO::FETCH_ASSOC);
    
    // Extrair serviços únicos
    $services = [];
    $serviceRates = [];
    
    foreach ($rates as $rate) {
        $service = $rate['service'];
        if (!in_array($service, $services)) {
            $services[] = $service;
        }
        
        // Mapear taxa por serviço (pega a primeira taxa encontrada)
        if (!isset($serviceRates[$service])) {
            $serviceRates[$service] = [
                'rate' => $rate['rate'],
                'unit' => $rate['unit'],
                'currency' => $rate['currency'],
                'lang_from' => $rate['lang_from'],
                'lang_to' => $rate['lang_to'],
            ];
        }
    }
    
    // Se não houver taxas no BD, tentar extrair de services_offered
    if (empty($services) && !empty($provider['services_offered'])) {
        // Assume que services_offered pode ser JSON ou texto separado por vírgula
        $servicesText = $provider['services_offered'];
        
        // Tenta decodificar JSON
        $servicesJson = json_decode($servicesText, true);
        if (is_array($servicesJson)) {
            $services = $servicesJson;
        } else {
            // Fallback: separa por vírgula
            $services = array_map('trim', explode(',', $servicesText));
        }
    }
    
    // Resposta
    echo json_encode([
        'success' => true,
        'provider' => [
            'id' => $provider['id'],
            'name' => $provider['name'],
            'currency' => $provider['currency'],
        ],
        'services' => $services,
        'rates' => $serviceRates,
    ]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>
