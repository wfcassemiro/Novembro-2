<?php
/**
 * Time Tracker - Interface Principal
 * Sistema de rastreamento de tempo para tradutores
 */

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
    <link rel="stylesheet" href="/dash-t101/style.css">
    <link rel="stylesheet" href="/Toggl_1/assets/css/time-tracker.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Incluir Header/Sidebar se existir -->
<?php if (file_exists(__DIR__ . '/../dash-t101/includes/header.php')): ?>
    <?php include __DIR__ . '/../dash-t101/includes/header.php'; ?>
<?php endif; ?>

<div class="time-tracker-container">
    <!-- Header -->
    <div class="tracker-header">
        <div class="header-content">
            <h1><i class="fas fa-stopwatch"></i> Time Tracker</h1>
            <div class="header-actions">
                <a href="/Toggl_1/reports.php" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i> Relatórios
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

    <!-- Aba de Projetos e Histórico -->
    <div class="content-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="history">
                <i class="fas fa-history"></i> Histórico
            </button>
            <button class="tab-btn" data-tab="projects">
                <i class="fas fa-folder"></i> Projetos
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

        <!-- Tab: Projetos -->
        <div class="tab-content" id="projectsTab">
            <div class="section-header">
                <h2>Meus Projetos</h2>
                <button class="btn btn-primary" onclick="openProjectModal()">
                    <i class="fas fa-plus"></i> Novo Projeto
                </button>
            </div>
            
            <div id="projectsList" class="projects-grid">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Criar/Editar Projeto -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="projectModalTitle">Novo Projeto</h3>
            <button class="modal-close" onclick="closeProjectModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="projectForm">
                <input type="hidden" id="projectId" name="id">
                
                <div class="form-group">
                    <label for="projectName">Nome do Projeto *</label>
                    <input type="text" 
                           id="projectName" 
                           name="name" 
                           class="form-control" 
                           placeholder="Ex: Tradução de Manual Técnico"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="projectColor">Cor do Projeto</label>
                    <div class="color-picker">
                        <input type="color" 
                               id="projectColor" 
                               name="color" 
                               class="color-input" 
                               value="#7B61FF">
                        <div class="color-presets">
                            <button type="button" class="color-preset" data-color="#7B61FF" style="background: #7B61FF;"></button>
                            <button type="button" class="color-preset" data-color="#FF6B9D" style="background: #FF6B9D;"></button>
                            <button type="button" class="color-preset" data-color="#4ECDC4" style="background: #4ECDC4;"></button>
                            <button type="button" class="color-preset" data-color="#FFE66D" style="background: #FFE66D;"></button>
                            <button type="button" class="color-preset" data-color="#FF6B6B" style="background: #FF6B6B;"></button>
                            <button type="button" class="color-preset" data-color="#95E1D3" style="background: #95E1D3;"></button>
                            <button type="button" class="color-preset" data-color="#FFA07A" style="background: #FFA07A;"></button>
                            <button type="button" class="color-preset" data-color="#87CEEB" style="background: #87CEEB;"></button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
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

<script src="/Toggl_1/assets/js/time-tracker.js"></script>

<?php if (file_exists(__DIR__ . '/../dash-t101/includes/footer.php')): ?>
    <?php include __DIR__ . '/../dash-t101/includes/footer.php'; ?>
<?php endif; ?>

</body>
</html>
