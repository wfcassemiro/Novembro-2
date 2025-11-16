<?php
// /app/Nov_16/budget_c.php
// Versão atualizada conforme requisitos de 16/11

session_start();

// Autoload do Composer (AJUSTE ESTE CAMINHO)
require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dash_database.php';
require_once __DIR__ . '/../config/dash_functions.php';

// Autorização
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Sessões auxiliares
if (!isset($_SESSION['analyses']))    $_SESSION['analyses'] = [];
if (!isset($_SESSION['budget_errors']))  $_SESSION['budget_errors'] = [];
if (!isset($_SESSION['budget_notices'])) $_SESSION['budget_notices'] = [];

// Pesos padrão
if (!isset($_SESSION['wc_weights'])) {
    $_SESSION['wc_weights'] = [
        '100%'    => 0.1,
        '95-99%'  => 0.2,
        '85-94%'  => 0.4,
        '75-84%'  => 0.6,
        '50-74%'  => 0.8,
        'No Match' => 1.0,
    ];
}

// Estado do orçamento
if (!isset($_SESSION['budget_client'])) {
    $_SESSION['budget_client'] = [
        'client_id'    => null,
        'client_name'  => '',
        'currency'     => '',
        'markup_pct'   => 30.0,
        'tax_pct'      => 11.5,
        'service'      => 'translation',
        'lang_from'    => null,
        'lang_to'      => null,
    ];
}

// Estado de Custos
if (!isset($_SESSION['budget_costs']) || !isset($_SESSION['budget_costs']['items']) || !is_array($_SESSION['budget_costs']['items'])) {
    $_SESSION['budget_costs'] = [
        'items' => []
    ];
}

// NOVO: Estado de progresso do fluxo
if (!isset($_SESSION['budget_flow_step'])) {
    $_SESSION['budget_flow_step'] = 1; // 1=Cliente, 2=Pesos, 3=Upload, 4=Custos, 5=Resultados
}

function redirect_self_base() {
    header('Location: '. strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

/**
 * Normaliza $_FILES['files'] para um array de arquivos
 */
function normalize_uploaded_files($filesField) {
    $normalized = [];

    if (!isset($filesField['name'])) {
        return $normalized;
    }

    // Caso multiple: name é array
    if (is_array($filesField['name'])) {
        $count = count($filesField['name']);
        for ($i = 0; $i < $count; $i++) {
            $name = $filesField['name'][$i] ?? '';
            $tmp  = $filesField['tmp_name'][$i] ?? '';
            $err  = $filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $filesField['size'][$i] ?? 0;

            if ($name === '' && $tmp === '') continue;
            if ($err === UPLOAD_ERR_NO_FILE) continue;

            $normalized[] = [
                'name'     => $name,
                'type'     => $filesField['type'][$i] ?? '',
                'tmp_name' => $tmp,
                'error'    => $err,
                'size'     => $size,
            ];
        }
        return $normalized;
    }

    // Caso único arquivo
    $name = $filesField['name'];
    $tmp  = $filesField['tmp_name'] ?? '';
    $err  = $filesField['error'] ?? UPLOAD_ERR_NO_FILE;
    $size = $filesField['size'] ?? 0;

    if ($name !== '' && $tmp !== '' && $err === UPLOAD_ERR_OK) {
        $normalized[] = [
            'name'     => $name,
            'type'     => $filesField['type'] ?? '',
            'tmp_name' => $tmp,
            'error'    => $err,
            'size'     => $size,
        ];
    }

    return $normalized;
}

// ==================== HANDLERS ====================

// 1. Atualizar Cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client_params'])) {
    $clientState = $_SESSION['budget_client'];
    $clientState['client_id']  = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
    $clientState['service']    = !empty($_POST['service']) ? trim($_POST['service']) : 'translation';
    $clientState['lang_from']  = $_POST['lang_from'] !== '' ? $_POST['lang_from'] : null;
    $clientState['lang_to']    = $_POST['lang_to'] !== '' ? $_POST['lang_to'] : null;
    $clientState['markup_pct'] = isset($_POST['markup_pct']) ? (float)str_replace(',', '.', $_POST['markup_pct']) : 30.0;
    $clientState['tax_pct']    = isset($_POST['tax_pct']) ? (float)str_replace(',', '.', $_POST['tax_pct']) : 11.5;
    $clientState['currency']   = isset($_POST['currency']) ? trim($_POST['currency']) : '';

    // Busca dados do cliente
    try {
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
    } catch (Throwable $e) {
        $_SESSION['budget_notices'][] = 'Erro ao carregar dados do cliente: ' . $e->getMessage();
    }

    if ($clientState['currency'] === '') {
        $clientState['currency'] = 'BRL';
    }

    $_SESSION['budget_client'] = $clientState;
    $_SESSION['budget_flow_step'] = 2; // Avança para pesos
    $_SESSION['budget_notices'][] = 'Cliente configurado. Agora ajuste os pesos por faixa.';
    redirect_self_base();
}

// 2. Atualizar pesos (botão OK)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_weights'])) {
    $keys = ['100%', '95-99%', '85-94%', '75-84%', '50-74%', 'No Match'];
    $newWeights = [];
    foreach ($keys as $k) {
        $field = 'w_' . preg_replace('/[^0-9A-Za-z]/', '_', $k);
        $val   = isset($_POST[$field]) ? (float)str_replace(',', '.', $_POST[$field]) : 0.0;
        if ($val < 0) $val = 0.0;
        $newWeights[$k] = $val;
    }
    $_SESSION['wc_weights'] = $newWeights;
    $_SESSION['budget_flow_step'] = 3; // Avança para upload
    $_SESSION['budget_notices'][] = 'Pesos atualizados. Agora selecione os arquivos.';
    redirect_self_base();
}

// 3. Upload e processamento de arquivos
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_FILES['files'])
    && !isset($_POST['update_weights'])
    && !isset($_POST['update_client_params'])
    && !isset($_POST['add_cost_item'])
) {
    require_once __DIR__ . '/processor.php';
    $files = normalize_uploaded_files($_FILES['files']);
    
    if (empty($files)) {
        $_SESSION['budget_errors'][] = 'Nenhum arquivo válido foi selecionado.';
        redirect_self_base();
    }

    $processed_any = false;
    foreach ($files as $f) {
        if ($f['error'] !== UPLOAD_ERR_OK) continue;
        
        try {
            $processor = new DocumentProcessor();
            $result = $processor->process($f['tmp_name'], $f['name']);
            
            if (($result['wordCount'] ?? 0) > 0) {
                $weights  = $_SESSION['wc_weights'];
                $weighted = 0;
                $segTotal = max(1, (int)($result['segmentCount'] ?? 0));
                
                foreach ($result['fuzzy'] as $row) {
                    $seg = (int)$row['segments'];
                    $approxWords = ($result['wordCount'] ?? 0) * ($seg / $segTotal);
                    $w = $weights[$row['category']] ?? 1.0;
                    $weighted += $approxWords * $w;
                }
                
                $result['weightedWordCount'] = (int)round($weighted);
                
                // NOVO: Estimar páginas (250 palavras = 1 página)
                $result['estimatedPages'] = max(1, (int)round($result['wordCount'] / 250));
                
                $_SESSION['analyses'][] = $result;
                $processed_any = true;
            } else {
                $_SESSION['budget_errors'][] = 'Não foi possível extrair texto de: ' . htmlspecialchars($f['name']);
            }
        } catch (Throwable $e) {
            $_SESSION['budget_errors'][] = 'Erro ao processar ' . htmlspecialchars($f['name']) . ': ' . $e->getMessage();
        } finally {
            if (is_uploaded_file($f['tmp_name']) || file_exists($f['tmp_name'])) {
                @unlink($f['tmp_name']);
            }
        }
    }

    if ($processed_any) {
        $_SESSION['budget_flow_step'] = 4; // Avança para custos
        $_SESSION['budget_notices'][] = 'Análise de fuzzy matches concluída. Agora informe os custos do projeto.';
    } elseif (empty($_SESSION['budget_errors'])) {
        $_SESSION['budget_errors'][] = 'Nenhum arquivo foi processado. Tente novamente.';
    }
    
    redirect_self_base();
}

// 4. Adicionar Item de Custo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost_item'])) {
    $provider_id = $_POST['provider_id'] ?? 'outro';
    $service     = $_POST['cost_service'] ?? 'Tradução';
    $cost_value  = isset($_POST['cost_value']) && $_POST['cost_value'] !== ''
        ? (float)str_replace('.', '', str_replace(',', '.', $_POST['cost_value']))
        : 0.00;
    
    $provider_name = 'Custo Diverso';
    if ($provider_id === 'interno') {
        $provider_name = 'Interno';
    } elseif ($provider_id !== 'outro') {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT name FROM dash_freelancers WHERE id = :id");
            $stmt->execute([':id' => $provider_id]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $provider_name = $row['name'];
            }
        } catch (Throwable $e) {
            $provider_name = 'Fornecedor (Erro)';
        }
    }

    if ($cost_value > 0) {
        $_SESSION['budget_costs']['items'][] = [
            'provider_id'   => $provider_id,
            'provider_name' => $provider_name,
            'service'       => $service,
            'cost'          => $cost_value
        ];
        $_SESSION['budget_notices'][] = 'Custo adicionado com sucesso.';
    } else {
        $_SESSION['budget_errors'][] = 'O valor do custo deve ser maior que zero.';
    }
    redirect_self_base();
}

// 5. Confirmar custos (botão OK)
if (isset($_GET['confirm_costs'])) {
    if (!empty($_SESSION['budget_costs']['items'])) {
        $_SESSION['budget_flow_step'] = 5; // Avança para resultados
        $_SESSION['budget_notices'][] = 'Custos confirmados. Veja os resultados abaixo.';
    } else {
        $_SESSION['budget_errors'][] = 'Adicione pelo menos um custo antes de confirmar.';
    }
    redirect_self_base();
}

// Remover Item de Custo
if (isset($_GET['remove_cost'])) {
    $index = (int)$_GET['remove_cost'];
    if (isset($_SESSION['budget_costs']['items'][$index])) {
        array_splice($_SESSION['budget_costs']['items'], $index, 1);
    }
    redirect_self_base();
}

// Remover análise
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    if (isset($_SESSION['analyses'][$index])) {
        array_splice($_SESSION['analyses'], $index, 1);
    }
    redirect_self_base();
}

// Limpar orçamento
if (isset($_GET['clear'])) {
    unset($_SESSION['analyses']);
    unset($_SESSION['budget_costs']);
    $_SESSION['budget_flow_step'] = 1;
    redirect_self_base();
}

// Atualizar número de páginas manualmente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pages'])) {
    $index = (int)($_POST['analysis_index'] ?? -1);
    $pages = (int)($_POST['pages'] ?? 1);
    
    if (isset($_SESSION['analyses'][$index])) {
        $_SESSION['analyses'][$index]['estimatedPages'] = max(1, $pages);
        $_SESSION['budget_notices'][] = 'Número de páginas atualizado.';
    }
    redirect_self_base();
}

// ==================== DADOS PARA RENDERIZAÇÃO ====================

$budgetClient = $_SESSION['budget_client'];
$budgetCosts  = $_SESSION['budget_costs'];
$currentStep  = $_SESSION['budget_flow_step'] ?? 1;

$clientsList  = [];
$providersList = [];
$currenciesList = [];

try {
    global $pdo;
    $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($currentUserId) {
        // Buscar Clientes
        $stmt = $pdo->prepare("SELECT id, name, default_currency FROM dash_clients WHERE user_id = :uid ORDER BY name ASC");
        $stmt->execute([':uid' => $currentUserId]);
        $clientsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar Fornecedores
        $stmtProviders = $pdo->prepare("SELECT id, name FROM dash_freelancers WHERE user_id = :uid ORDER BY name ASC");
        $stmtProviders->execute([':uid' => $currentUserId]);
        $providersList = $stmtProviders->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar Moedas dinamicamente de dash_settings
        $stmtCurrency = $pdo->prepare("SELECT setting_key, setting_value FROM dash_settings WHERE user_id = :uid AND setting_key LIKE 'rate_%'");
        $stmtCurrency->execute([':uid' => $currentUserId]);
        $currencySettings = $stmtCurrency->fetchAll(PDO::FETCH_ASSOC);
        
        // Base: BRL
        $currenciesList = ['BRL'];
        
        // Adicionar moedas do settings (rate_usd -> USD)
        foreach ($currencySettings as $cs) {
            $currency = strtoupper(str_replace('rate_', '', $cs['setting_key']));
            if (!in_array($currency, $currenciesList)) {
                $currenciesList[] = $currency;
            }
        }
    }
} catch (Throwable $e) {
    $_SESSION['budget_errors'][] = 'Erro ao carregar dados: ' . $e->getMessage();
}

// Cálculo de totais
$totalWords = 0;
$totalSegments = 0;
$weightedSum = 0;
$totalPages = 0;

if (!empty($_SESSION['analyses'])) {
    foreach ($_SESSION['analyses'] as $analysis) {
        $totalWords    += $analysis['wordCount'];
        $totalSegments += $analysis['segmentCount'];
        $weightedSum   += $analysis['weightedWordCount'] ?? 0;
        $totalPages    += $analysis['estimatedPages'] ?? 0;
    }
}

$custoTotal = 0.00;
if (isset($budgetCosts['items']) && is_array($budgetCosts['items'])) {
    $custoTotal = array_sum(array_column($budgetCosts['items'], 'cost'));
}

$markupPct = $budgetClient['markup_pct'] ?? 30.0;
$taxPct = $budgetClient['tax_pct'] ?? 11.5;

$subtotalSemImposto = $custoTotal * (1 + ($markupPct / 100));
$valorImposto = $subtotalSemImposto * ($taxPct / 100);
$sugestaoCliente = $subtotalSemImposto + $valorImposto;

$currencyLabel = $budgetClient['currency'] !== '' ? $budgetClient['currency'] : 'BRL';

// ==================== RENDER ====================

$page_title = 'Orçamentos - Dash-T101';
$page_description = "Gere orçamentos com upload múltiplo, fuzzy matches e ponderação.";

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<style>
.main-content { padding-bottom: 100px; }
.profile-header-card {
    display: flex;
    align-items: center;
    justify-content: center;
}
.header-icon-container {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    width: 60px; height: 60px;
    display: flex; align-items: center; justify-content: center;
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
.alert-success { opacity: 1; transition: opacity 1s ease-out; background: #22c55e; color: #fff; padding: 15px 30px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.alert-error { background: #ef4444; color: #fff; padding: 15px 30px; border-radius: 12px; margin-bottom: 20px; }
.alert-info { background: var(--accent-blue); color: #fff; padding: 15px 30px; border-radius: 12px; font-weight: 500; display: flex; align-items: center; gap: 10px; }

.video-card {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.05));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: background-color 0.3s ease, border-color 0.3s ease;
    margin-bottom: 0;
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
    flex-shrink: 0;
}
.video-card:hover,
.file-analysis:hover {
    background-color: rgba(74, 20, 140, 0.15);
    border-color: rgba(170, 100, 255, 0.25);
}

/* Card desabilitado */
.video-card.disabled {
    opacity: 0.4;
    pointer-events: none;
    filter: grayscale(0.5);
}

.vision-form-refined {
    padding: 0px 30px 0px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.vision-form-refined .form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}
.vision-form-refined .form-row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.vision-form-refined .form-row-cost {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 15px;
    margin-bottom: 20px;
    align-items: flex-end;
}
.vision-form-refined .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 0;
}
.vision-form-refined .form-group.form-group-full { grid-column: 1 / -1; }
.vision-form-refined .form-group label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.vision-input, .vision-select, .vision-textarea {
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
    margin-top: auto;
    justify-content: center;
    padding-top: 20px;
    padding-bottom: 20px;
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
.vision-btn-primary:hover { background: var(--brand-purple-dark); border-color: var(--brand-purple-dark); }
.vision-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-color: rgba(255, 255, 255, 0.2);
}
.vision-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    color: #fff !important;
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
.btn-add-cost:hover {
    background: #3498db;
}

.vision-table-container {
    margin: 0px 30px 20px;
    overflow-x: auto;
    padding-bottom: 10px;
}
.vision-table { width: 100%; border-collapse: collapse; }
.vision-table th {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 14px 18px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-align: left;
}
.vision-table td {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    font-size: 0.95rem;
}
.vision-table tr:hover { background: rgba(255, 255, 255, 0.03); }

.final-cost-breakdown {
    padding: 20px 30px;
    font-size: 1.1rem;
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
.final-cost-breakdown .sub-line strong {
    color: var(--text-primary);
}

.action-buttons-refined { display: flex; gap: 8px; align-items: center; }

/* File list */
.file-list {
    padding: 15px 30px;
    max-height: 300px;
    overflow-y: auto;
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
.file-item-remove:hover {
    color: #dc2626;
}

/* Progress bar */
.progress-container {
    padding: 20px 30px;
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
}
.progress-text {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
}
</style>

<div class="main-content">

    <div class="video-card profile-header-card" style="background: linear-gradient(135deg, var(--brand-purple), #4a148c); border: none; margin-bottom: 24px; height: auto;">
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
        <?php if ($currentStep >= 5 && !empty($_SESSION['analyses'])): ?>
        <a href="?download=consolidated" class="vision-btn vision-btn-secondary">
            <i class="fas fa-file-csv"></i>
            <span>CSV consolidado</span>
        </a>
        <a href="?download=xlsx" class="vision-btn vision-btn-secondary">
            <i class="fas fa-file-excel"></i>
            <span>XLSX consolidado</span>
        </a>
        <?php endif; ?>
        <a href="?clear=1" class="vision-btn vision-btn-secondary" onclick="return confirm('Limpar este orçamento e começar um novo?')">
            <i class="fas fa-trash-restore"></i>
            <span>Novo orçamento</span>
        </a>
    </div>

    <?php if (!empty($_SESSION['budget_errors'])): ?>
    <div class="alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo implode('<br>', array_map('htmlspecialchars', $_SESSION['budget_errors'])); ?>
    </div>
    <?php $_SESSION['budget_errors'] = []; ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['budget_notices'])): ?>
    <div class="alert-info">
        <i class="fas fa-info-circle"></i>
        <?php echo implode('<br>', array_map('htmlspecialchars', $_SESSION['budget_notices'])); ?>
    </div>
    <?php $_SESSION['budget_notices'] = []; ?>
    <?php endif; ?>

    <!-- PASSO 1: CLIENTE -->
    <div class="video-card <?= $currentStep < 1 ? 'disabled' : '' ?>" style="margin-bottom: 20px;">
        <h2>
            <i class="fas fa-user-circle"></i> 
            Cliente
            <?= $currentStep > 1 ? '<i class="fas fa-check-circle" style="color: #22c55e; margin-left: auto;"></i>' : '' ?>
        </h2>
        <form method="POST" class="vision-form-refined">
            <input type="hidden" name="update_client_params" value="1">
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
                    <select name="service" id="service" class="vision-input">
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
                    <select name="currency" id="currency" class="vision-input">
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

    <!-- PASSO 2: PESOS -->
    <div class="video-card <?= $currentStep < 2 ? 'disabled' : '' ?>" style="margin-bottom: 20px;">
        <h2>
            <i class="fas fa-balance-scale"></i> 
            Pesos por faixa
            <?= $currentStep > 2 ? '<i class="fas fa-check-circle" style="color: #22c55e; margin-left: auto;"></i>' : '' ?>
        </h2>
        <?php $w = $_SESSION['wc_weights']; ?>
        <form method="POST" class="vision-form-refined">
            <input type="hidden" name="update_weights" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label>100%</label>
                    <input class="vision-input" name="w_100_" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['100%']); ?>">
                </div>
                <div class="form-group">
                    <label>95-99%</label>
                    <input class="vision-input" name="w_95_99_" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['95-99%']); ?>">
                </div>
                <div class="form-group">
                    <label>85-94%</label>
                    <input class="vision-input" name="w_85_94_" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['85-94%']); ?>">
                </div>
                <div class="form-group">
                    <label>75-84%</label>
                    <input class="vision-input" name="w_75_84_" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['75-84%']); ?>">
                </div>
                <div class="form-group">
                    <label>50-74%</label>
                    <input class="vision-input" name="w_50_74_" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['50-74%']); ?>">
                </div>
                <div class="form-group">
                    <label>No Match</label>
                    <input class="vision-input" name="w_No_Match" type="number" step="0.01"
                        value="<?php echo htmlspecialchars($w['No Match']); ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="vision-btn vision-btn-primary" <?= $currentStep < 2 ? 'disabled' : '' ?>>
                    <i class="fas fa-check"></i>
                    <span>OK</span>
                </button>
            </div>
        </form>
    </div>

    <!-- PASSO 3: SELECIONAR ARQUIVOS -->
    <div class="video-card <?= $currentStep < 3 ? 'disabled' : '' ?>" style="margin-bottom: 20px;">
        <h2>
            <i class="fas fa-cloud-upload-alt"></i> 
            Selecionar arquivos
            <?= $currentStep > 3 ? '<i class="fas fa-check-circle" style="color: #22c55e; margin-left: auto;"></i>' : '' ?>
        </h2>
        
        <form method="POST" enctype="multipart/form-data" class="vision-form-refined" id="uploadForm">
            <input id="fileInput" type="file" name="files[]"
                accept=".docx,.pptx,.xlsx,.xls,.txt,.pdf,.html,.htm,.csv,.md"
                multiple style="display:none;" <?= $currentStep < 3 ? 'disabled' : '' ?>>
            
            <div id="fileListContainer" class="file-list" style="display:none;"></div>
            
            <div class="form-actions" style="justify-content: center;">
                <button type="button" class="vision-btn vision-btn-secondary"
                    onclick="document.getElementById('fileInput').click();" <?= $currentStep < 3 ? 'disabled' : '' ?>>
                    <i class="fas fa-file-upload"></i>
                    <span>Selecionar arquivos</span>
                </button>
                
                <button type="submit" class="vision-btn vision-btn-primary" id="btnCalcFuzzy" style="display:none;">
                    <i class="fas fa-calculator"></i>
                    <span>Calcular fuzzy matches</span>
                </button>
            </div>
            
            <div id="progressContainer" class="progress-container" style="display:none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
                </div>
                <div class="progress-text" id="progressText">Processando...</div>
            </div>
        </form>
    </div>

    <!-- PASSO 4: CUSTOS DO PROJETO -->
    <div class="video-card <?= $currentStep < 4 ? 'disabled' : '' ?>" style="margin-bottom: 20px;">
        <h2>
            <i class="fas fa-wallet"></i> 
            Custos do Projeto
            <?= $currentStep > 4 ? '<i class="fas fa-check-circle" style="color: #22c55e; margin-left: auto;"></i>' : '' ?>
        </h2>
        
        <?php if (!empty($budgetCosts['items'])): ?>
        <div class="vision-table-container">
            <table class="vision-table">
                <thead>
                    <tr>
                        <th>Fornecedor</th>
                        <th>Serviço</th>
                        <th>Custo (<?= $currencyLabel ?>)</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budgetCosts['items'] as $index => $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['provider_name']) ?></td>
                        <td><?= htmlspecialchars($item['service']) ?></td>
                        <td><?= htmlspecialchars(number_format($item['cost'], 2, ',', '.')) ?></td>
                        <td>
                            <a href="?remove_cost=<?= $index ?>" class="vision-btn vision-btn-secondary"
                                style="padding: 6px 10px; font-size: 0.8rem; background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.5);"
                                onclick="return confirm('Remover este custo?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
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

        <form method="POST" class="vision-form-refined" style="padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.06);">
            <input type="hidden" name="add_cost_item" value="1">
            <div class="form-row-cost">
                <div class="form-group">
                    <label for="provider_id">Fornecedor</label>
                    <select id="provider_id" name="provider_id" class="vision-input" <?= $currentStep < 4 ? 'disabled' : '' ?>>
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
                    <input type="text" name="cost_service" id="cost_service" class="vision-input"
                        placeholder="Ex: Tradução, Revisão, Diagramação" <?= $currentStep < 4 ? 'disabled' : '' ?>>
                </div>
                <div class="form-group">
                    <label for="cost_value">Valor (<?= $currencyLabel ?>)</label>
                    <input type="text" name="cost_value" id="cost_value" class="vision-input"
                        placeholder="0,00" <?= $currentStep < 4 ? 'disabled' : '' ?>>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-add-cost" <?= $currentStep < 4 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>
        </form>

        <?php if (!empty($budgetCosts['items'])): ?>
        <div class="form-actions">
            <a href="?confirm_costs=1" class="vision-btn vision-btn-primary">
                <i class="fas fa-check"></i>
                <span>OK - Ver Resultados</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- PASSO 5: RESULTADOS -->
    <?php if ($currentStep >= 5): ?>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); row-gap: 20px; column-gap: 16px; margin-bottom: 20px;">
        
        <!-- Resumo da Análise -->
        <div class="video-card">
            <h2><i class="fas fa-chart-pie"></i> Resumo</h2>
            <div class="vision-form-refined" style="padding-top:0;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Total de palavras</label>
                        <div class="vision-input" style="background:transparent;border:none;">
                            <strong><?= number_format($totalWords, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Total de segmentos</label>
                        <div class="vision-input" style="background:transparent;border:none;">
                            <strong><?= number_format($totalSegments, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Total ponderado</label>
                        <div class="vision-input" style="background:transparent;border:none;">
                            <strong><?= number_format($weightedSum, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>
                <div class="form-row" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label>Total de páginas (estimado)</label>
                        <div class="vision-input" style="background:transparent;border:none;">
                            <strong><?= number_format($totalPages, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custo Total -->
        <div class="video-card">
            <h2><i class="fas fa-coins"></i> Custo Total</h2>
            <div class="vision-form-refined" style="justify-content: center; padding-top: 20px;">
                <div class="form-group form-group-full">
                    <div class="vision-input" style="background:transparent; border:none; text-align:center; padding: 20px 0;">
                        <strong style="color: #FFB74D; font-size: 1.8rem; font-weight: 600;">
                            <?= htmlspecialchars($currencyLabel) ?> <?= number_format($custoTotal, 2, ',', '.') ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preço Sugerido -->
        <div class="video-card">
            <h2><i class="fas fa-calculator"></i> Preço sugerido</h2>
            <div class="vision-form-refined final-cost-breakdown" style="justify-content: center;">
                <div class="total">
                    <?= htmlspecialchars($currencyLabel) ?> <?= number_format($sugestaoCliente, 2, ',', '.') ?>
                </div>
                <div class="sub-line">
                    Subtotal (Custo + Markup <?= number_format($markupPct, 1, ',', '.') ?>%):
                    <strong><?= number_format($subtotalSemImposto, 2, ',', '.') ?></strong>
                </div>
                <div class="sub-line">
                    Impostos (<?= number_format($taxPct, 1, ',', '.') ?>%):
                    <strong><?= number_format($valorImposto, 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Análises por arquivo -->
    <?php if (!empty($_SESSION['analyses'])): ?>
    <div class="video-card">
        <h2><i class="fas fa-folder-open"></i> Análises por arquivo</h2>
        
        <div class="file-cards-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px; padding:16px 24px 24px;">
            <?php foreach ($_SESSION['analyses'] as $index => $analysis): ?>
            <div class="file-analysis" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 12px 14px 4px; display:flex; flex-direction:column; min-height: 220px; transition: background-color 0.3s ease, border-color 0.3s ease;">
                <div class="file-analysis-header" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                    <div class="file-analysis-title" style="font-weight:600; color:var(--text-primary); font-size:0.95rem;">
                        <i class="far fa-file" style="margin-right:6px;"></i>
                        <?= htmlspecialchars($analysis['fileName']) ?>
                    </div>
                </div>

                <div class="vision-table-container" style="margin:8px 0 4px; overflow:hidden;">
                    <table class="vision-table" style="margin:0; font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Seg.</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analysis['fuzzy'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= number_format($row['segments'], 0, ',', '.') ?></td>
                                <td><?= number_format($row['percentage'], 1, ',', '.') ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="vision-form-refined" style="padding:4px 0 8px;">
                    <div class="form-row" style="grid-template-columns: repeat(4, minmax(0,1fr)); margin-bottom:8px;">
                        <div class="form-group">
                            <label style="font-size:0.75rem;">Palavras</label>
                            <div class="vision-input" style="background:transparent;border:none; padding:4px 0;">
                                <strong style="font-size:0.9rem;">
                                    <?= number_format($analysis['wordCount'], 0, ',', '.') ?>
                                </strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem;">Segmentos</label>
                            <div class="vision-input" style="background:transparent;border:none; padding:4px 0;">
                                <strong style="font-size:0.9rem;">
                                    <?= number_format($analysis['segmentCount'], 0, ',', '.') ?>
                                </strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem;">Ponderadas</label>
                            <div class="vision-input" style="background:transparent;border:none; padding:4px 0;">
                                <strong style="font-size:0.9rem;">
                                    <?= number_format($analysis['weightedWordCount'] ?? 0, 0, ',', '.') ?>
                                </strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem;">Páginas</label>
                            <form method="POST" style="display:flex; gap:4px;">
                                <input type="hidden" name="update_pages" value="1">
                                <input type="hidden" name="analysis_index" value="<?= $index ?>">
                                <input type="number" name="pages" value="<?= $analysis['estimatedPages'] ?? 1 ?>"
                                    min="1" class="vision-input" style="padding:4px 6px; font-size:0.85rem; width:60px;">
                                <button type="submit" class="vision-btn vision-btn-secondary" style="padding:4px 8px; font-size:0.7rem; border-radius:8px;">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:0; justify-content:flex-end;">
                        <a href="?download=individual&index=<?= $index ?>" class="vision-btn vision-btn-secondary" style="padding:6px 10px; border-radius:16px; font-size:0.8rem;">
                            <i class="fas fa-download"></i>
                            <span>CSV</span>
                        </a>
                        <a href="?remove=<?= $index ?>" class="vision-btn vision-btn-secondary" style="padding:6px 10px; border-radius:16px; font-size:0.8rem; background:rgba(239,68,68,0.15); border-color:rgba(239,68,68,0.5);"
                            onclick="return confirm('Remover este arquivo do orçamento?')">
                            <i class="fas fa-trash-alt"></i>
                            <span>Remover</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endif; ?>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const fileListContainer = document.getElementById('fileListContainer');
    const btnCalcFuzzy = document.getElementById('btnCalcFuzzy');
    const uploadForm = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');

    let selectedFiles = [];

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length > 0) {
                selectedFiles = Array.from(fileInput.files);
                renderFileList();
                btnCalcFuzzy.style.display = 'inline-flex';
            }
        });
    }

    function renderFileList() {
        if (selectedFiles.length === 0) {
            fileListContainer.style.display = 'none';
            btnCalcFuzzy.style.display = 'none';
            return;
        }

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

    // Simular progresso durante upload
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Selecione pelo menos um arquivo.');
                return;
            }

            // Mostrar barra de progresso
            progressContainer.style.display = 'block';
            btnCalcFuzzy.disabled = true;
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                progressFill.style.width = progress + '%';
                progressText.textContent = `Processando... ${progress}%`;
                
                if (progress >= 90) {
                    clearInterval(interval);
                    progressText.textContent = 'Finalizando análise de fuzzy matches...';
                }
            }, 200);
        });
    }

    // Auto-preenchimento de fornecedor
    const providerSelect = document.getElementById('provider_id');
    const costValueInput = document.getElementById('cost_value');
    const costServiceInput = document.getElementById('cost_service');

    if (providerSelect && costValueInput) {
        providerSelect.addEventListener('change', function() {
            // Aqui você pode implementar lógica para buscar taxa do fornecedor via AJAX
            // Por enquanto, valor padrão
            if (providerSelect.value !== 'interno' && providerSelect.value !== 'outro') {
                costValueInput.value = '0,20';
            } else {
                costValueInput.value = '0,00';
            }
        });
    }
});
</script>

<?php
include __DIR__ . '/../vision/includes/footer.php';
?>
