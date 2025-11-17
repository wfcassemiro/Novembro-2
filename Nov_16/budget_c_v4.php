<?php
// /app/Nov_16/budget_c_v4.php
// Versão 4 - Correção de valores e lista de arquivos

session_start();

// Autoload do Composer - CAMINHOS CORRIGIDOS
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dash_database.php';
require_once __DIR__ . '/../config/dash_functions.php';

// Autorização
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Inicializar sessões
if (!isset($_SESSION['analyses'])) $_SESSION['analyses'] = [];
if (!isset($_SESSION['budget_errors'])) $_SESSION['budget_errors'] = [];
if (!isset($_SESSION['budget_notices'])) $_SESSION['budget_notices'] = [];

// Pesos padrão
if (!isset($_SESSION['wc_weights'])) {
    $_SESSION['wc_weights'] = [
        '100%' => 0.1,
        '95-99%' => 0.2,
        '85-94%' => 0.4,
        '75-84%' => 0.6,
        '50-74%' => 0.8,
        'No Match' => 1.0,
    ];
}

// Estado do orçamento
if (!isset($_SESSION['budget_client'])) {
    $_SESSION['budget_client'] = [
        'client_id' => null,
        'client_name' => '',
        'currency' => '',
        'markup_pct' => 30.0,
        'tax_pct' => 11.5,
        'service' => 'translation',
        'lang_from' => null,
        'lang_to' => null,
    ];
}

// Estado de Custos
if (!isset($_SESSION['budget_costs']) || !isset($_SESSION['budget_costs']['items'])) {
    $_SESSION['budget_costs'] = ['items' => []];
}

// Estado de progresso
if (!isset($_SESSION['budget_flow_step'])) {
    $_SESSION['budget_flow_step'] = 1;
}

// Normaliza arquivos uploadados
function normalize_uploaded_files($filesField) {
    $normalized = [];
    if (!isset($filesField['name'])) return $normalized;

    if (is_array($filesField['name'])) {
        $count = count($filesField['name']);
        for ($i = 0; $i < $count; $i++) {
            $name = $filesField['name'][$i] ?? '';
            $tmp = $filesField['tmp_name'][$i] ?? '';
            $err = $filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $filesField['size'][$i] ?? 0;

            if ($name === '' && $tmp === '') continue;
            if ($err === UPLOAD_ERR_NO_FILE) continue;

            $normalized[] = [
                'name' => $name,
                'type' => $filesField['type'][$i] ?? '',
                'tmp_name' => $tmp,
                'error' => $err,
                'size' => $size,
            ];
        }
        return $normalized;
    }

    $name = $filesField['name'];
    $tmp = $filesField['tmp_name'] ?? '';
    $err = $filesField['error'] ?? UPLOAD_ERR_NO_FILE;
    $size = $filesField['size'] ?? 0;

    if ($name !== '' && $tmp !== '' && $err === UPLOAD_ERR_OK) {
        $normalized[] = [
            'name' => $name,
            'type' => $filesField['type'] ?? '',
            'tmp_name' => $tmp,
            'error' => $err,
            'size' => $size,
        ];
    }

    return $normalized;
}

// Função para converter valor BR para float
function parseBRLFloat($value) {
    if (empty($value)) return 0.0;
    // Remove espaços
    $value = trim($value);
    // Troca vírgula por ponto (0,15 → 0.15)
    $value = str_replace(',', '.', $value);
    // Remove pontos que sejam separadores de milhar (1.000 → 1000, mas mantém 0.15)
    // Se houver mais de um ponto, remove todos exceto o último
    $parts = explode('.', $value);
    if (count($parts) > 2) {
        // Múltiplos pontos: 1.000.000,50 virou 1.000.000.50
        // Junta tudo exceto o último
        $value = implode('', array_slice($parts, 0, -1)) . '.' . end($parts);
    }
    return (float)$value;
}

// ==================== HANDLERS AJAX ====================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['ajax_action'];
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($action) {
            case 'update_client':
                $clientState = $_SESSION['budget_client'];
                $clientState['client_id'] = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
                $clientState['service'] = !empty($_POST['service']) ? trim($_POST['service']) : 'translation';
                $clientState['lang_from'] = $_POST['lang_from'] !== '' ? $_POST['lang_from'] : null;
                $clientState['lang_to'] = $_POST['lang_to'] !== '' ? $_POST['lang_to'] : null;
                $clientState['markup_pct'] = isset($_POST['markup_pct']) ? (float)str_replace(',', '.', $_POST['markup_pct']) : 30.0;
                $clientState['tax_pct'] = isset($_POST['tax_pct']) ? (float)str_replace(',', '.', $_POST['tax_pct']) : 11.5;
                $clientState['currency'] = isset($_POST['currency']) ? trim($_POST['currency']) : '';

                global $pdo;
                if ($clientState['client_id']) {
                    $stmt = $pdo->prepare("SELECT name, default_currency FROM dash_clients WHERE id = :id");
                    $stmt->execute([':id' => $clientState['client_id']]);
                    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $clientState['client_name'] = $row['name'];
                        if ($clientState['currency'] === '' && !empty($row['default_currency'])) {
                            $clientState['currency'] = $row['default_currency'];
                        }
                    }
                }

                if ($clientState['currency'] === '') {
                    $clientState['currency'] = 'BRL';
                }

                $_SESSION['budget_client'] = $clientState;
                $_SESSION['budget_flow_step'] = max($_SESSION['budget_flow_step'], 2);
                
                $response['success'] = true;
                $response['message'] = 'Cliente configurado com sucesso';
                $response['next_step'] = 2;
                break;

            case 'update_weights':
                $keys = ['100%', '95-99%', '85-94%', '75-84%', '50-74%', 'No Match'];
                $newWeights = [];
                foreach ($keys as $k) {
                    $field = 'w_' . preg_replace('/[^0-9A-Za-z]/', '_', $k);
                    $val = isset($_POST[$field]) ? (float)str_replace(',', '.', $_POST[$field]) : 0.0;
                    if ($val < 0) $val = 0.0;
                    $newWeights[$k] = $val;
                }
                $_SESSION['wc_weights'] = $newWeights;
                $_SESSION['budget_flow_step'] = max($_SESSION['budget_flow_step'], 3);
                
                $response['success'] = true;
                $response['message'] = 'Pesos atualizados com sucesso';
                $response['next_step'] = 3;
                break;

            case 'add_cost':
                $provider_id = $_POST['provider_id'] ?? 'outro';
                $service = $_POST['cost_service'] ?? 'Tradução';
                
                // CORREÇÃO: Usar função de parsing correta
                $cost_value = parseBRLFloat($_POST['cost_value'] ?? '0');
                
                $provider_name = 'Custo Diverso';
                if ($provider_id === 'interno') {
                    $provider_name = 'Interno';
                } elseif ($provider_id !== 'outro') {
                    global $pdo;
                    $stmt = $pdo->prepare("SELECT name FROM dash_freelancers WHERE id = :id");
                    $stmt->execute([':id' => $provider_id]);
                    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $provider_name = $row['name'];
                    }
                }

                if ($cost_value > 0) {
                    $_SESSION['budget_costs']['items'][] = [
                        'provider_id' => $provider_id,
                        'provider_name' => $provider_name,
                        'service' => $service,
                        'unit_cost' => $cost_value
                    ];
                    
                    $response['success'] = true;
                    $response['message'] = 'Custo adicionado com sucesso';
                    $response['cost_item'] = end($_SESSION['budget_costs']['items']);
                    $response['cost_index'] = count($_SESSION['budget_costs']['items']) - 1;
                } else {
                    throw new Exception('O valor do custo deve ser maior que zero.');
                }
                break;

            case 'remove_cost':
                $index = (int)($_POST['cost_index'] ?? -1);
                if (isset($_SESSION['budget_costs']['items'][$index])) {
                    array_splice($_SESSION['budget_costs']['items'], $index, 1);
                    $response['success'] = true;
                    $response['message'] = 'Custo removido';
                } else {
                    throw new Exception('Custo não encontrado');
                }
                break;

            case 'calculate_budget':
                if (empty($_SESSION['budget_costs']['items'])) {
                    throw new Exception('Adicione pelo menos um custo antes de calcular');
                }
                
                $_SESSION['budget_flow_step'] = 5;
                
                // Calcular totais de análises
                $totalWords = 0;
                $totalSegments = 0;
                $weightedSum = 0;
                $totalPages = 0;

                if (!empty($_SESSION['analyses'])) {
                    foreach ($_SESSION['analyses'] as $analysis) {
                        $totalWords += $analysis['wordCount'];
                        $totalSegments += $analysis['segmentCount'];
                        $weightedSum += $analysis['weightedWordCount'] ?? 0;
                        $totalPages += $analysis['estimatedPages'] ?? 0;
                    }
                }

                // CÁLCULO CORRETO: cada serviço multiplica por base diferente
                $custoTotal = 0.0;
                $costBreakdown = [];

                foreach ($_SESSION['budget_costs']['items'] as $item) {
                    $service = $item['service'];
                    $unitCost = $item['unit_cost'];
                    $totalCost = 0.0;

                    switch ($service) {
                        case 'Tradução':
                            $totalCost = $unitCost * $weightedSum;
                            $costBreakdown[] = [
                                'provider' => $item['provider_name'],
                                'service' => $service,
                                'calculation' => number_format($unitCost, 4, ',', '.') . " × $weightedSum palavras ponderadas",
                                'total' => $totalCost
                            ];
                            break;

                        case 'Pós-edição':
                        case 'Revisão':
                            $totalCost = $unitCost * $totalWords;
                            $costBreakdown[] = [
                                'provider' => $item['provider_name'],
                                'service' => $service,
                                'calculation' => number_format($unitCost, 4, ',', '.') . " × $totalWords palavras",
                                'total' => $totalCost
                            ];
                            break;

                        case 'Diagramação':
                            $totalCost = $unitCost * $totalPages;
                            $costBreakdown[] = [
                                'provider' => $item['provider_name'],
                                'service' => $service,
                                'calculation' => number_format($unitCost, 2, ',', '.') . " × $totalPages páginas",
                                'total' => $totalCost
                            ];
                            break;

                        default:
                            // Serviço desconhecido, usa valor unitário como fixo
                            $totalCost = $unitCost;
                            $costBreakdown[] = [
                                'provider' => $item['provider_name'],
                                'service' => $service,
                                'calculation' => "Valor fixo",
                                'total' => $totalCost
                            ];
                            break;
                    }

                    $custoTotal += $totalCost;
                }

                $markupPct = $_SESSION['budget_client']['markup_pct'] ?? 30.0;
                $taxPct = $_SESSION['budget_client']['tax_pct'] ?? 11.5;

                $subtotalSemImposto = $custoTotal * (1 + ($markupPct / 100));
                $valorImposto = $subtotalSemImposto * ($taxPct / 100);
                $sugestaoCliente = $subtotalSemImposto + $valorImposto;

                $currencyLabel = $_SESSION['budget_client']['currency'] !== '' ? $_SESSION['budget_client']['currency'] : 'BRL';

                $response['success'] = true;
                $response['message'] = 'Orçamento calculado com sucesso';
                $response['results'] = [
                    'totalWords' => $totalWords,
                    'totalSegments' => $totalSegments,
                    'weightedSum' => $weightedSum,
                    'totalPages' => $totalPages,
                    'custoTotal' => $custoTotal,
                    'subtotal' => $subtotalSemImposto,
                    'impostos' => $valorImposto,
                    'precoFinal' => $sugestaoCliente,
                    'currency' => $currencyLabel,
                    'markupPct' => $markupPct,
                    'taxPct' => $taxPct,
                    'costBreakdown' => $costBreakdown
                ];
                break;

            default:
                throw new Exception('Ação inválida');
        }
    } catch (Throwable $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Upload de arquivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files']) && !isset($_POST['ajax_action'])) {
    require_once __DIR__ . '/processor.php';
    
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'analyses' => []];
    
    try {
        $files = normalize_uploaded_files($_FILES['files']);
        
        if (empty($files)) {
            throw new Exception('Nenhum arquivo válido foi selecionado.');
        }

        $processed_any = false;
        foreach ($files as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) continue;
            
            try {
                $processor = new DocumentProcessor();
                $result = $processor->process($f['tmp_name'], $f['name']);
                
                if (($result['wordCount'] ?? 0) > 0) {
                    $weights = $_SESSION['wc_weights'];
                    $weighted = 0;
                    $segTotal = max(1, (int)($result['segmentCount'] ?? 0));
                    
                    foreach ($result['fuzzy'] as $row) {
                        $seg = (int)$row['segments'];
                        $approxWords = ($result['wordCount'] ?? 0) * ($seg / $segTotal);
                        $w = $weights[$row['category']] ?? 1.0;
                        $weighted += $approxWords * $w;
                    }
                    
                    $result['weightedWordCount'] = (int)round($weighted);
                    $result['estimatedPages'] = max(1, (int)round($result['wordCount'] / 250));
                    
                    $_SESSION['analyses'][] = $result;
                    $response['analyses'][] = $result;
                    $processed_any = true;
                }
            } catch (Throwable $e) {
                // Log error but continue
            } finally {
                if (is_uploaded_file($f['tmp_name']) || file_exists($f['tmp_name'])) {
                    @unlink($f['tmp_name']);
                }
            }
        }

        if ($processed_any) {
            $_SESSION['budget_flow_step'] = max($_SESSION['budget_flow_step'], 4);
            $response['success'] = true;
            $response['message'] = 'Arquivos processados com sucesso';
            $response['next_step'] = 4;
        } else {
            throw new Exception('Nenhum arquivo foi processado. Tente novamente.');
        }
    } catch (Throwable $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Limpar orçamento
if (isset($_GET['clear'])) {
    unset($_SESSION['analyses']);
    unset($_SESSION['budget_costs']);
    $_SESSION['budget_flow_step'] = 1;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// ==================== DADOS PARA RENDERIZAÇÃO ====================

$budgetClient = $_SESSION['budget_client'];
$budgetCosts = $_SESSION['budget_costs'];
$currentStep = $_SESSION['budget_flow_step'] ?? 1;

$clientsList = [];
$providersList = [];
$currenciesList = [];

try {
    global $pdo;
    $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($currentUserId) {
        $stmt = $pdo->prepare("SELECT id, name, default_currency FROM dash_clients WHERE user_id = :uid ORDER BY name ASC");
        $stmt->execute([':uid' => $currentUserId]);
        $clientsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtProviders = $pdo->prepare("SELECT id, name FROM dash_freelancers WHERE user_id = :uid ORDER BY name ASC");
        $stmtProviders->execute([':uid' => $currentUserId]);
        $providersList = $stmtProviders->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtCurrency = $pdo->prepare("SELECT setting_key, setting_value FROM dash_settings WHERE user_id = :uid AND setting_key LIKE 'rate_%'");
        $stmtCurrency->execute([':uid' => $currentUserId]);
        $currencySettings = $stmtCurrency->fetchAll(PDO::FETCH_ASSOC);
        
        $currenciesList = ['BRL'];
        foreach ($currencySettings as $cs) {
            $currency = strtoupper(str_replace('rate_', '', $cs['setting_key']));
            if (!in_array($currency, $currenciesList)) {
                $currenciesList[] = $currency;
            }
        }
    }
} catch (Throwable $e) {
    // Log error
}

$page_title = 'Orçamentos - Dash-T101';
$page_description = "Gere orçamentos com upload múltiplo, fuzzy matches e ponderação.";

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<style>
.main-content { padding-bottom: 100px; }

/* Header com ícone centralizado verticalmente */
.profile-header-card {
    display: flex;
    align-items: center;
    justify-content: center;
}
.header-icon-container {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    width: 60px; 
    height: 60px;
    display: flex; 
    align-items: center; 
    justify-content: center;
    flex-shrink: 0;
}
.header-text-container { margin-left: 20px; }
.header-text-container h2 {
    margin: 0 0 5px 0;
    padding: 0;
    font-size: 1.5rem;
    color: #fff;
    font-weight: 600;
    border: none;
}
.header-text-container p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
}

.report-nav-buttons { display: flex; gap: 15px; margin-bottom: 20px; }

/* Cards Grid - 2 colunas */
.cards-grid-2col {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.video-card {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.05));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.video-card > h2 {
    margin: 0;
    padding: 25px 30px 20px;
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.video-card:hover {
    background-color: rgba(74, 20, 140, 0.15);
    border-color: rgba(170, 100, 255, 0.25);
}

.video-card.disabled {
    opacity: 0.4;
    pointer-events: none;
    filter: grayscale(0.5);
}

/* Card completado com cabeçalho verde */
.video-card.completed > h2 {
    background: linear-gradient(90deg, #22c55e, #16a34a);
    color: white;
    border-bottom: none;
    border-radius: 20px 20px 0 0;
}

.vision-form-refined {
    padding: 0px 30px 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.vision-form-refined .form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.vision-form-refined .form-row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.vision-form-refined .form-row-cost {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 15px;
    margin-bottom: 15px;
    align-items: flex-end;
}

.vision-form-refined .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.vision-form-refined .form-group.form-group-full { grid-column: 1 / -1; }

.vision-form-refined .form-group label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.vision-input, .vision-select {
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 12px 16px;
    color: var(--text-primary);
    font-size: 0.95rem;
    width: 100%;
}

.vision-form-refined .form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    padding-top: 15px;
    margin-top: auto;
}

.vision-btn {
    background: var(--brand-purple);
    color: white;
    border: 1px solid var(--brand-purple);
    border-radius: 20px;
    padding: 12px 24px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.vision-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.vision-btn-primary { background: var(--brand-purple); border-color: var(--brand-purple); }
.vision-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-add-cost {
    padding: 12px 16px;
    border-radius: 12px;
    background: var(--accent-blue);
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.vision-table-container {
    margin: 0px 30px 20px;
    overflow-x: auto;
}

.vision-table { width: 100%; border-collapse: collapse; }
.vision-table th {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 12px 16px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-align: left;
}
.vision-table td {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    font-size: 0.95rem;
}
.vision-table tr:hover { background: rgba(255, 255, 255, 0.03); }

.file-list {
    padding: 15px 30px;
    max-height: 300px;
    overflow-y: auto;
}

.file-list-title {
    padding: 10px 30px 0px;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
    margin-bottom: 8px;
}

.file-item-name {
    flex-grow: 1;
    color: var(--text-primary);
}

.file-item-remove {
    color: #ef4444;
    cursor: pointer;
    padding: 5px 10px;
    font-weight: 600;
}

.progress-container {
    padding: 20px 30px;
    display: none;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: rgba(0,0,0,0.3);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--brand-purple), var(--accent-blue));
    transition: width 0.3s ease;
    width: 0%;
}

.progress-text {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.final-cost-breakdown {
    padding: 20px 30px;
    text-align: center;
}

.final-cost-breakdown .total {
    color: #4ade80;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.final-cost-breakdown .sub-line {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-top: 5px;
}

#resultsSection {
    display: none;
}
</style>

<div class="main-content">

    <div class="video-card profile-header-card" style="background: linear-gradient(135deg, var(--brand-purple), #4a148c); border: none; margin-bottom: 24px;">
        <div class="header-icon-container">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="header-text-container">
            <h2>Orçamentos — Análise de Fuzzy Matches</h2>
            <p>Fluxo guiado para geração de orçamentos profissionais</p>
        </div>
    </div>

    <div class="report-nav-buttons">
        <a href="index.php" class="vision-btn vision-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar ao Dash-T101</span>
        </a>
        <a href="?clear=1" class="vision-btn vision-btn-secondary" onclick="return confirm('Limpar este orçamento e começar um novo?')">
            <i class="fas fa-trash-restore"></i>
            <span>Novo orçamento</span>
        </a>
    </div>

    <!-- LINHA 1: Cliente + Pesos -->
    <div class="cards-grid-2col">
        
        <!-- CARD: Cliente -->
        <div class="video-card <?= $currentStep < 1 ? 'disabled' : '' ?> <?= $currentStep > 1 ? 'completed' : '' ?>" id="cardCliente">
            <h2><i class="fas fa-user-circle"></i> Cliente</h2>
            <form id="formCliente" class="vision-form-refined">
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="client_id">Nome</label>
                        <select id="client_id" name="client_id" class="vision-input">
                            <option value="">– Selecione um cliente –</option>
                            <?php foreach ($clientsList as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= $budgetClient['client_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Serviço</label>
                        <select name="service" class="vision-input">
                            <option value="translation" <?= $budgetClient['service'] === 'translation' ? 'selected' : '' ?>>Tradução</option>
                            <option value="interpretacao" <?= $budgetClient['service'] === 'interpretacao' ? 'selected' : '' ?>>Interpretação</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>De</label>
                        <input type="text" name="lang_from" class="vision-input" placeholder="ex: EN"
                            value="<?= htmlspecialchars($budgetClient['lang_from'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Para</label>
                        <input type="text" name="lang_to" class="vision-input" placeholder="ex: PT-BR"
                            value="<?= htmlspecialchars($budgetClient['lang_to'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Moeda</label>
                        <select name="currency" class="vision-input">
                            <?php foreach ($currenciesList as $curr): ?>
                            <option value="<?= htmlspecialchars($curr) ?>" <?= $budgetClient['currency'] === $curr ? 'selected' : '' ?>>
                                <?= htmlspecialchars($curr) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Markup (%)</label>
                        <input type="number" step="0.1" name="markup_pct" class="vision-input"
                            value="<?= htmlspecialchars($budgetClient['markup_pct']) ?>" placeholder="30">
                    </div>

                    <div class="form-group">
                        <label>Impostos (%)</label>
                        <input type="number" step="0.1" name="tax_pct" class="vision-input"
                            value="<?= htmlspecialchars($budgetClient['tax_pct']) ?>" placeholder="11.5">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="vision-btn vision-btn-primary">
                        <i class="fas fa-check"></i>
                        <span>Confirmar Cliente</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- CARD: Pesos -->
        <div class="video-card <?= $currentStep < 2 ? 'disabled' : '' ?> <?= $currentStep > 2 ? 'completed' : '' ?>" id="cardPesos">
            <h2><i class="fas fa-balance-scale"></i> Pesos por faixa</h2>
            <?php $w = $_SESSION['wc_weights']; ?>
            <form id="formPesos" class="vision-form-refined">
                <div class="form-row">
                    <div class="form-group">
                        <label>100%</label>
                        <input class="vision-input" name="w_100_" type="number" step="0.01" value="<?= htmlspecialchars($w['100%']) ?>">
                    </div>
                    <div class="form-group">
                        <label>95-99%</label>
                        <input class="vision-input" name="w_95_99_" type="number" step="0.01" value="<?= htmlspecialchars($w['95-99%']) ?>">
                    </div>
                    <div class="form-group">
                        <label>85-94%</label>
                        <input class="vision-input" name="w_85_94_" type="number" step="0.01" value="<?= htmlspecialchars($w['85-94%']) ?>">
                    </div>
                    <div class="form-group">
                        <label>75-84%</label>
                        <input class="vision-input" name="w_75_84_" type="number" step="0.01" value="<?= htmlspecialchars($w['75-84%']) ?>">
                    </div>
                    <div class="form-group">
                        <label>50-74%</label>
                        <input class="vision-input" name="w_50_74_" type="number" step="0.01" value="<?= htmlspecialchars($w['50-74%']) ?>">
                    </div>
                    <div class="form-group">
                        <label>No Match</label>
                        <input class="vision-input" name="w_No_Match" type="number" step="0.01" value="<?= htmlspecialchars($w['No Match']) ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="vision-btn vision-btn-primary">
                        <i class="fas fa-check"></i>
                        <span>OK</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- LINHA 2: Arquivos + Custos -->
    <div class="cards-grid-2col">
        
        <!-- CARD: Selecionar Arquivos -->
        <div class="video-card <?= $currentStep < 3 ? 'disabled' : '' ?> <?= $currentStep > 3 ? 'completed' : '' ?>" id="cardArquivos">
            <h2><i class="fas fa-cloud-upload-alt"></i> Selecionar arquivos</h2>
            
            <form id="formArquivos" class="vision-form-refined" enctype="multipart/form-data">
                <input id="fileInput" type="file" name="files[]" accept=".docx,.pptx,.xlsx,.xls,.txt,.pdf,.html,.htm,.csv,.md" multiple style="display:none;">
                
                <!-- Lista de arquivos já processados (da sessão) -->
                <?php if (!empty($_SESSION['analyses'])): ?>
                <div class="file-list-title">Arquivos adicionados:</div>
                <div class="file-list">
                    <?php foreach ($_SESSION['analyses'] as $index => $analysis): ?>
                    <div class="file-item">
                        <span class="file-item-name">
                            <i class="far fa-file"></i> <?= htmlspecialchars($analysis['fileName']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Lista de arquivos recém-selecionados (antes do upload) -->
                <div id="selectedFilesTitle" class="file-list-title" style="display:none;">Arquivos selecionados:</div>
                <div id="fileListContainer" class="file-list" style="display:none;"></div>
                
                <div class="form-actions" style="justify-content: center;">
                    <button type="button" id="btnSelectFiles" class="vision-btn vision-btn-secondary" onclick="document.getElementById('fileInput').click();">
                        <i class="fas fa-file-upload"></i>
                        <span><?= empty($_SESSION['analyses']) ? 'Selecionar arquivos' : 'Adicionar arquivos' ?></span>
                    </button>
                    
                    <button type="submit" class="vision-btn vision-btn-primary" id="btnCalcFuzzy" style="display:none;">
                        <i class="fas fa-calculator"></i>
                        <span>Calcular fuzzy matches</span>
                    </button>
                </div>
                
                <div id="progressContainer" class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text" id="progressText">Processando...</div>
                </div>
            </form>
        </div>

        <!-- CARD: Custos -->
        <div class="video-card <?= $currentStep < 4 ? 'disabled' : '' ?> <?= $currentStep > 4 ? 'completed' : '' ?>" id="cardCustos">
            <h2><i class="fas fa-wallet"></i> Custos do Projeto</h2>
            
            <div id="costsTableContainer">
                <?php if (!empty($budgetCosts['items'])): ?>
                <div class="vision-table-container">
                    <table class="vision-table">
                        <thead>
                            <tr>
                                <th>Fornecedor</th>
                                <th>Serviço</th>
                                <th>Valor Unitário</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="costsTableBody">
                            <?php foreach ($budgetCosts['items'] as $index => $item): ?>
                            <tr data-cost-index="<?= $index ?>">
                                <td><?= htmlspecialchars($item['provider_name']) ?></td>
                                <td><?= htmlspecialchars($item['service']) ?></td>
                                <td><?= htmlspecialchars(number_format($item['unit_cost'], 4, ',', '.')) ?></td>
                                <td>
                                    <button class="vision-btn vision-btn-secondary btn-remove-cost" data-index="<?= $index ?>" style="padding: 6px 10px; font-size: 0.8rem;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="padding: 20px 30px; text-align: center; color: var(--text-secondary);">
                    Nenhum custo adicionado.
                </div>
                <?php endif; ?>
            </div>

            <form id="formCusto" class="vision-form-refined" style="padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.06);">
                <div class="form-row-cost">
                    <div class="form-group">
                        <label for="provider_id">Fornecedor</label>
                        <select id="provider_id" name="provider_id" class="vision-input">
                            <option value="interno">Interno</option>
                            <option value="outro">Outro Custo Diverso</option>
                            <optgroup label="Fornecedores">
                                <?php foreach ($providersList as $s): ?>
                                <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cost_service">Serviço</label>
                        <select id="cost_service" name="cost_service" class="vision-input">
                            <option value="Tradução">Tradução</option>
                            <option value="Pós-edição">Pós-edição</option>
                            <option value="Revisão">Revisão</option>
                            <option value="Diagramação">Diagramação</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cost_value">Valor Unitário</label>
                        <input type="text" name="cost_value" id="cost_value" class="vision-input" placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-add-cost">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>
            </form>

            <div class="form-actions" id="costsBtnContainer" style="<?= empty($budgetCosts['items']) ? 'display:none;' : '' ?> justify-content: center; padding-bottom: 20px;">
                <button id="btnCalculateBudget" class="vision-btn vision-btn-primary">
                    <i class="fas fa-calculator"></i>
                    <span>Calcular orçamento</span>
                </button>
            </div>
        </div>

    </div>

    <!-- RESULTADOS -->
    <div id="resultsSection" <?= $currentStep >= 5 ? 'style="display:block;"' : '' ?>>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 20px;">
            
            <div class="video-card">
                <h2><i class="fas fa-chart-pie"></i> Resumo</h2>
                <div class="vision-form-refined">
                    <div class="form-row" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="form-group">
                            <label>Total de palavras</label>
                            <div class="vision-input" style="background:transparent;border:none;">
                                <strong id="resultTotalWords">0</strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Total de segmentos</label>
                            <div class="vision-input" style="background:transparent;border:none;">
                                <strong id="resultTotalSegments">0</strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Total ponderado</label>
                            <div class="vision-input" style="background:transparent;border:none;">
                                <strong id="resultWeightedSum">0</strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Total de páginas</label>
                            <div class="vision-input" style="background:transparent;border:none;">
                                <strong id="resultTotalPages">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="video-card">
                <h2><i class="fas fa-coins"></i> Custo Total</h2>
                <div class="vision-form-refined" style="justify-content: center; padding-top: 20px;">
                    <div class="form-group">
                        <div class="vision-input" style="background:transparent; border:none; text-align:center; padding: 20px 0;">
                            <strong style="color: #FFB74D; font-size: 1.8rem;" id="resultCustoTotal">R$ 0,00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="video-card">
                <h2><i class="fas fa-calculator"></i> Preço sugerido</h2>
                <div class="vision-form-refined final-cost-breakdown">
                    <div class="total" id="resultPrecoFinal">R$ 0,00</div>
                    <div class="sub-line">
                        Subtotal (Custo + Markup <span id="resultMarkupPct">30</span>%): 
                        <strong id="resultSubtotal">R$ 0,00</strong>
                    </div>
                    <div class="sub-line">
                        Impostos (<span id="resultTaxPct">11.5</span>%): 
                        <strong id="resultImpostos">R$ 0,00</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedFiles = [];
    let hasFilesSelected = <?= !empty($_SESSION['analyses']) ? 'true' : 'false' ?>;
    
    const formCliente = document.getElementById('formCliente');
    const formPesos = document.getElementById('formPesos');
    const formArquivos = document.getElementById('formArquivos');
    const formCusto = document.getElementById('formCusto');
    const fileInput = document.getElementById('fileInput');
    const fileListContainer = document.getElementById('fileListContainer');
    const selectedFilesTitle = document.getElementById('selectedFilesTitle');
    const btnSelectFiles = document.getElementById('btnSelectFiles');
    const btnCalcFuzzy = document.getElementById('btnCalcFuzzy');
    const btnCalculateBudget = document.getElementById('btnCalculateBudget');
    const progressContainer = document.getElementById('progressContainer');
    
    const cardCliente = document.getElementById('cardCliente');
    const cardPesos = document.getElementById('cardPesos');
    const cardArquivos = document.getElementById('cardArquivos');
    const cardCustos = document.getElementById('cardCustos');
    
    function enableCard(cardElement) {
        cardElement.classList.remove('disabled');
    }
    
    function markCardCompleted(cardElement) {
        cardElement.classList.add('completed');
    }
    
    function formatBRL(value) {
        return parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 4
        });
    }
    
    // Form Cliente
    if (formCliente) {
        formCliente.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(formCliente);
            formData.append('ajax_action', 'update_client');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    markCardCompleted(cardCliente);
                    enableCard(cardPesos);
                }
            });
        });
    }
    
    // Form Pesos
    if (formPesos) {
        formPesos.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(formPesos);
            formData.append('ajax_action', 'update_weights');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    markCardCompleted(cardPesos);
                    enableCard(cardArquivos);
                }
            });
        });
    }
    
    // File Input
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files.length > 0) {
                selectedFiles = Array.from(fileInput.files);
                renderFileList();
                btnCalcFuzzy.style.display = 'inline-flex';
                
                if (hasFilesSelected) {
                    btnSelectFiles.querySelector('span').textContent = 'Adicionar arquivos';
                } else {
                    hasFilesSelected = true;
                }
            }
        });
    }
    
    function renderFileList() {
        if (selectedFiles.length === 0) {
            fileListContainer.style.display = 'none';
            selectedFilesTitle.style.display = 'none';
            btnCalcFuzzy.style.display = 'none';
            return;
        }

        selectedFilesTitle.style.display = 'block';
        fileListContainer.style.display = 'block';
        fileListContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <span class="file-item-name">
                    <i class="far fa-file"></i> ${file.name}
                </span>
                <span class="file-item-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </span>
            `;
            fileListContainer.appendChild(fileItem);
        });
    }
    
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        renderFileList();
    };
    
    // Form Upload
    if (formArquivos) {
        formArquivos.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (selectedFiles.length === 0) {
                return;
            }
            
            const formData = new FormData();
            selectedFiles.forEach(file => formData.append('files[]', file));
            
            progressContainer.style.display = 'block';
            btnCalcFuzzy.disabled = true;
            
            let progress = 0;
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            const interval = setInterval(() => {
                progress += 10;
                progressFill.style.width = progress + '%';
                progressText.textContent = `Processando... ${progress}%`;
                
                if (progress >= 90) {
                    clearInterval(interval);
                    progressText.textContent = 'Finalizando...';
                }
            }, 200);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(interval);
                progressFill.style.width = '100%';
                progressText.textContent = 'Concluído!';
                
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    progressFill.style.width = '0%';
                    btnCalcFuzzy.disabled = false;
                }, 1000);
                
                if (data.success) {
                    markCardCompleted(cardArquivos);
                    enableCard(cardCustos);
                    selectedFiles = [];
                    renderFileList();
                    
                    // Recarregar para mostrar arquivos na lista
                    setTimeout(() => location.reload(), 1500);
                }
            });
        });
    }
    
    // Form Custo
    if (formCusto) {
        formCusto.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(formCusto);
            formData.append('ajax_action', 'add_cost');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    formCusto.reset();
                    addCostToTable(data.cost_item, data.cost_index);
                    document.getElementById('costsBtnContainer').style.display = 'flex';
                }
            });
        });
    }
    
    function addCostToTable(item, index) {
        let tbody = document.getElementById('costsTableBody');
        if (!tbody) {
            const container = document.getElementById('costsTableContainer');
            container.innerHTML = `
                <div class="vision-table-container">
                    <table class="vision-table">
                        <thead>
                            <tr>
                                <th>Fornecedor</th>
                                <th>Serviço</th>
                                <th>Valor Unitário</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="costsTableBody"></tbody>
                    </table>
                </div>
            `;
            tbody = document.getElementById('costsTableBody');
        }
        
        const row = document.createElement('tr');
        row.dataset.costIndex = index;
        row.innerHTML = `
            <td>${item.provider_name}</td>
            <td>${item.service}</td>
            <td>${formatBRL(item.unit_cost)}</td>
            <td>
                <button class="vision-btn vision-btn-secondary btn-remove-cost" data-index="${index}" style="padding: 6px 10px; font-size: 0.8rem;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        
        row.querySelector('.btn-remove-cost').addEventListener('click', function() {
            removeCost(index, row);
        });
    }
    
    function removeCost(index, rowElement) {
        if (!confirm('Remover este custo?')) return;
        
        const formData = new FormData();
        formData.append('ajax_action', 'remove_cost');
        formData.append('cost_index', index);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                rowElement.remove();
                
                const tbody = document.getElementById('costsTableBody');
                if (tbody && tbody.children.length === 0) {
                    document.getElementById('costsBtnContainer').style.display = 'none';
                }
            }
        });
    }
    
    document.querySelectorAll('.btn-remove-cost').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            const row = this.closest('tr');
            removeCost(index, row);
        });
    });
    
    // Calcular Orçamento
    if (btnCalculateBudget) {
        btnCalculateBudget.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('ajax_action', 'calculate_budget');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    markCardCompleted(cardCustos);
                    displayResults(data.results);
                }
            });
        });
    }
    
    function displayResults(results) {
        document.getElementById('resultTotalWords').textContent = results.totalWords.toLocaleString('pt-BR');
        document.getElementById('resultTotalSegments').textContent = results.totalSegments.toLocaleString('pt-BR');
        document.getElementById('resultWeightedSum').textContent = results.weightedSum.toLocaleString('pt-BR');
        document.getElementById('resultTotalPages').textContent = results.totalPages.toLocaleString('pt-BR');
        
        document.getElementById('resultCustoTotal').textContent = results.currency + ' ' + formatBRL(results.custoTotal);
        document.getElementById('resultSubtotal').textContent = results.currency + ' ' + formatBRL(results.subtotal);
        document.getElementById('resultImpostos').textContent = results.currency + ' ' + formatBRL(results.impostos);
        document.getElementById('resultPrecoFinal').textContent = results.currency + ' ' + formatBRL(results.precoFinal);
        
        document.getElementById('resultMarkupPct').textContent = results.markupPct.toFixed(1);
        document.getElementById('resultTaxPct').textContent = results.taxPct.toFixed(1);
        
        document.getElementById('resultsSection').style.display = 'block';
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<?php
include __DIR__ . '/../vision/includes/footer.php';
?>
