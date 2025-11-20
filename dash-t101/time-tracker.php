<?php
/**
 * Time Tracker - Interface Principal
 * Sistema de rastreamento de tempo para tradutores
 * INTEGRADO com dash_projects existente
 */

// Ativar exibição de erros (desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir autenticação e funções necessárias
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Time Tracker - Rastreamento de Tempo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Importar CSS do site -->
    <link rel="stylesheet" href="/vision/assets/css/style.css">
    <link rel="stylesheet" href="/vision/assets/css/time-tracker.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Incluir Header/Sidebar -->
<?php if (file_exists(__DIR__ . '/vision/includes/header.php')): ?>
    <?php include __DIR__ . '/vision/includes/header.php'; ?>
<?php endif; ?>

<div class="time-tracker-container">
    <!-- Header -->
    <div class="tracker-header">
        <div class="header-content">
            <h1><i class="fas fa-stopwatch"></i> Time Tracker</h1>
            <div class="header-actions">
                <a href="/dash-t101/report_time_tracker.php" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a>
                <a href="/dash-t101/projects.php" class="btn btn-secondary">
                    <i class="fas fa-folder"></i> Gerenciar Projetos
                </a>
            </div>
        </div>
    </div>

    <!-- Cronômetro Principal -->
    <div class="timer-section glass-card">
        <div class="timer-display" id="timerDisplay">
            <div class="time-digits">00:00:00</div>
            <div class="timer-info" id="timerInfo"></div>
        </div>
        
        <div class="timer-controls">
            <div class="timer-input-group">
                <input type="text" 
                       id="timerDescription" 
                       class="timer-input" 
                       placeholder="O que você está fazendo?">
                
                <select id="timerProject" class="timer-select">
                    <option value="">Selecione um projeto</option>
                </select>
                
                <select id="timerTask" class="timer-select" disabled>
                    <option value="">Selecione uma tarefa</option>
                </select>
                
                <button class="btn btn-icon btn-primary" onclick="openQuickProjectModal()" title="Criar Projeto Rápido">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            
            <div class="timer-buttons">
                <button id="startButton" class="btn btn-primary btn-timer">
                    <i class="fas fa-play"></i> Iniciar
                </button>
                <button id="pauseButton" class="btn btn-warning btn-timer" style="display: none;">
                    <i class="fas fa-pause"></i> Pausar
                </button>
                <button id="resumeButton" class="btn btn-success btn-timer" style="display: none;">
                    <i class="fas fa-play"></i> Retomar
                </button>
                <button id="stopButton" class="btn btn-danger btn-timer" style="display: none;">
                    <i class="fas fa-stop"></i> Parar
                </button>
            </div>
        </div>
    </div>

    <!-- Aba de Histórico -->
    <div class="content-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="history">
                <i class="fas fa-history"></i> Histórico
            </button>
        </div>

        <!-- Tab: Histórico -->
        <div class="tab-content active" id="historyTab">
            <div class="section-header">
                <h2>Registros Recentes</h2>
                <div class="filter-group">
                    <select id="filterProject" class="filter-select">
                        <option value="">Todos os projetos</option>
                    </select>
                    <button class="btn btn-sm btn-primary" onclick="loadEntries()">
                        <i class="fas fa-sync"></i> Atualizar
                    </button>
                </div>
            </div>
            
            <div id="entriesList" class="entries-list">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Criar Projeto Rápido -->
<div id="quickProjectModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3>Criar Projeto Rápido</h3>
            <button class="modal-close" onclick="closeQuickProjectModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="quickProjectForm" onsubmit="createQuickProject(event)">
                <div class="form-group">
                    <label for="quickProjectName">Nome do Projeto *</label>
                    <input type="text" 
                           id="quickProjectName" 
                           class="form-control" 
                           placeholder="Ex: Tradução de Manual Técnico"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="quickClientName">Cliente (Opcional)</label>
                    <input type="text" 
                           id="quickClientName" 
                           class="form-control" 
                           placeholder="Nome do cliente">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeQuickProjectModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Criar e Selecionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tarefas do Projeto -->
<div id="tasksModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="tasksModalTitle">Tarefas do Projeto</h3>
            <button class="modal-close" onclick="closeTasksModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="tasks-header">
                <input type="text" 
                       id="newTaskName" 
                       class="form-control" 
                       placeholder="Nova tarefa">
                <button class="btn btn-primary" onclick="createTask()">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </div>
            
            <div id="tasksList" class="tasks-list">
                <!-- Tarefas serão carregadas aqui -->
            </div>
        </div>
    </div>
</div>

<script>
// DEBUG: Configuração e logs
console.log('========== TIME TRACKER DEBUG ==========');
console.log('1. Script inline carregado');

// Configuração da API
const API_URL = window.API_URL || '/dash-t101/api_time_tracker.php';
console.log('2. API_URL configurado:', API_URL);

// Verificar se jQuery está disponível (se necessário)
console.log('3. jQuery disponível?', typeof jQuery !== 'undefined' ? 'SIM' : 'NÃO');

// Testar fetch
console.log('4. Fetch disponível?', typeof fetch !== 'undefined' ? 'SIM' : 'NÃO');

// Log quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('5. DOM Content Loaded - página carregada');
    console.log('6. Elementos importantes:');
    console.log('   - timerProject:', document.getElementById('timerProject') ? 'EXISTE' : 'NÃO EXISTE');
    console.log('   - projectsList:', document.getElementById('projectsList') ? 'EXISTE' : 'NÃO EXISTE');
    console.log('   - entriesList:', document.getElementById('entriesList') ? 'EXISTE' : 'NÃO EXISTE');
});

// Interceptar erros
window.addEventListener('error', function(e) {
    console.error('ERRO CAPTURADO:', e.message, 'em', e.filename, 'linha', e.lineno);
});
</script>
<script src="/vision/assets/js/time-tracker.js?v=<?php echo time(); ?>"></script>
<script>
console.log('7. time-tracker.js carregado (se não houver erros acima)');
</script>

<?php if (file_exists(__DIR__ . '/vision/includes/footer.php')): ?>
    <?php include __DIR__ . '/vision/includes/footer.php'; ?>
<?php endif; ?>

</body>
</html>