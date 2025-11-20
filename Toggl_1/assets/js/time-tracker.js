/**
 * Time Tracker - JavaScript
 * Sistema de rastreamento de tempo
 */

// Configuração da API
const API_URL = window.API_URL || '/dash-t101/api_time_tracker.php';

// Estado Global
const state = {
    runningEntry: null,
    timerInterval: null,
    isPaused: false,
    currentProject: null,
    projects: [],
    tasks: [],
    entries: []
};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('8. DOMContentLoaded disparado - iniciando app');
    initializeApp();
});

function initializeApp() {
    console.log('9. initializeApp() chamado');
    console.log('   - API_URL:', API_URL);
    
    try {
        console.log('10. Carregando projetos...');
        loadProjects();
        
        console.log('11. Carregando entries...');
        loadEntries();
        
        console.log('12. Verificando timer ativo...');
        checkRunningTimer();
        
        console.log('13. Configurando event listeners...');
        setupEventListeners();
        
        console.log('14. Iniciando atualização do timer...');
        startTimerUpdate();
        
        console.log('15. Inicialização completa!');
    } catch (error) {
        console.error('ERRO na inicialização:', error);
    }
}

// ===== EVENT LISTENERS =====
function setupEventListeners() {
    // Timer Controls
    document.getElementById('startButton').addEventListener('click', startTimer);
    document.getElementById('pauseButton').addEventListener('click', pauseTimer);
    document.getElementById('resumeButton').addEventListener('click', resumeTimer);
    document.getElementById('stopButton').addEventListener('click', stopTimer);
    
    // Project Selection
    document.getElementById('timerProject').addEventListener('change', function() {
        const projectId = this.value;
        const taskSelect = document.getElementById('timerTask');
        
        if (projectId) {
            taskSelect.disabled = false;
            loadTasksForSelect(projectId);
        } else {
            taskSelect.disabled = true;
            taskSelect.innerHTML = '<option value="">Selecione uma tarefa</option>';
        }
    });
    
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchTab(tabName);
        });
    });
    
    // Project Form
    document.getElementById('projectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProject();
    });
    
    // Color Presets
    document.querySelectorAll('.color-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.dataset.color;
            document.getElementById('projectColor').value = color;
        });
    });
}

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.toggle('active', content.id === tabName + 'Tab');
    });
    
    // Load data for the active tab
    if (tabName === 'projects') {
        loadProjects();
    } else if (tabName === 'history') {
        loadEntries();
    }
}

// ===== TIMER FUNCTIONS =====
function startTimer() {
    const description = document.getElementById('timerDescription').value;
    const projectId = document.getElementById('timerProject').value || null;
    const taskId = document.getElementById('timerTask').value || null;
    
    const formData = new FormData();
    formData.append('action', 'entry_start');
    formData.append('description', description);
    if (projectId) formData.append('project_id', projectId);
    if (taskId) formData.append('task_id', taskId);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Cronômetro iniciado!', 'success');
            checkRunningTimer();
        } else {
            showNotification(data.error || 'Erro ao iniciar cronômetro', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao iniciar cronômetro', 'error');
    });
}

function pauseTimer() {
    if (!state.runningEntry) return;
    
    const formData = new FormData();
    formData.append('action', 'entry_pause');
    formData.append('id', state.runningEntry.id);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            state.isPaused = true;
            updateTimerUI();
            showNotification('Cronômetro pausado', 'info');
        } else {
            showNotification(data.error || 'Erro ao pausar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao pausar', 'error');
    });
}

function resumeTimer() {
    if (!state.runningEntry) return;
    
    const formData = new FormData();
    formData.append('action', 'entry_resume');
    formData.append('id', state.runningEntry.id);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            state.isPaused = false;
            updateTimerUI();
            showNotification('Cronômetro retomado', 'success');
        } else {
            showNotification(data.error || 'Erro ao retomar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao retomar', 'error');
    });
}

function stopTimer() {
    if (!state.runningEntry) return;
    
    if (!confirm('Deseja parar o cronômetro?')) return;
    
    const formData = new FormData();
    formData.append('action', 'entry_stop');
    formData.append('id', state.runningEntry.id);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Cronômetro parado! Duração: ' + data.duration_formatted, 'success');
            state.runningEntry = null;
            state.isPaused = false;
            resetTimerUI();
            loadEntries();
        } else {
            showNotification(data.error || 'Erro ao parar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao parar', 'error');
    });
}

function checkRunningTimer() {
    fetch(API_URL + '?action=entry_running')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.entry) {
                state.runningEntry = data.entry;
                state.isPaused = data.entry.is_paused || false;
                updateTimerUI();
            } else {
                state.runningEntry = null;
                resetTimerUI();
            }
        })
        .catch(error => {
            console.error('Error checking timer:', error);
        });
}

function startTimerUpdate() {
    state.timerInterval = setInterval(() => {
        if (state.runningEntry && !state.isPaused) {
            updateTimerDisplay();
        }
    }, 1000);
}

function updateTimerDisplay() {
    if (!state.runningEntry) return;
    
    const startTime = new Date(state.runningEntry.start_time.replace(' ', 'T'));
    const now = new Date();
    const elapsed = Math.floor((now - startTime) / 1000);
    const pausedDuration = parseInt(state.runningEntry.paused_duration) || 0;
    
    let duration = elapsed - pausedDuration;
    
    if (state.runningEntry.paused_at) {
        const pausedAt = new Date(state.runningEntry.paused_at.replace(' ', 'T'));
        const currentPause = Math.floor((now - pausedAt) / 1000);
        duration -= currentPause;
    }
    
    document.querySelector('.time-digits').textContent = formatDuration(Math.max(0, duration));
}

function updateTimerUI() {
    if (state.runningEntry) {
        // Preencher campos
        document.getElementById('timerDescription').value = state.runningEntry.description || '';
        if (state.runningEntry.project_id) {
            document.getElementById('timerProject').value = state.runningEntry.project_id;
            document.getElementById('timerProject').disabled = true;
        }
        if (state.runningEntry.task_id) {
            document.getElementById('timerTask').value = state.runningEntry.task_id;
            document.getElementById('timerTask').disabled = true;
        }
        
        // Atualizar info
        let info = '';
        if (state.runningEntry.project_name) {
            info += '<span style=\"color: ' + (state.runningEntry.project_color || '#7B61FF') + '\">';
            info += '● ' + state.runningEntry.project_name;
            if (state.runningEntry.task_name) {
                info += ' - ' + state.runningEntry.task_name;
            }
            info += '</span>';
        }
        document.getElementById('timerInfo').innerHTML = info;
        
        // Mostrar/esconder botões
        document.getElementById('startButton').style.display = 'none';
        
        if (state.isPaused) {
            document.getElementById('pauseButton').style.display = 'none';
            document.getElementById('resumeButton').style.display = 'inline-flex';
        } else {
            document.getElementById('pauseButton').style.display = 'inline-flex';
            document.getElementById('resumeButton').style.display = 'none';
        }
        
        document.getElementById('stopButton').style.display = 'inline-flex';
        
        updateTimerDisplay();
    } else {
        resetTimerUI();
    }
}

function resetTimerUI() {
    document.querySelector('.time-digits').textContent = '00:00:00';
    document.getElementById('timerInfo').innerHTML = '';
    document.getElementById('timerDescription').value = '';
    document.getElementById('timerProject').value = '';
    document.getElementById('timerProject').disabled = false;
    document.getElementById('timerTask').value = '';
    document.getElementById('timerTask').disabled = true;
    
    document.getElementById('startButton').style.display = 'inline-flex';
    document.getElementById('pauseButton').style.display = 'none';
    document.getElementById('resumeButton').style.display = 'none';
    document.getElementById('stopButton').style.display = 'none';
}

// ===== QUICK PROJECT MODAL =====
function openQuickProjectModal() {
    document.getElementById('quickProjectModal').classList.add('active');
    document.getElementById('quickProjectName').focus();
}

function closeQuickProjectModal() {
    document.getElementById('quickProjectModal').classList.remove('active');
    document.getElementById('quickProjectForm').reset();
}

function createQuickProject(event) {
    event.preventDefault();
    
    const name = document.getElementById('quickProjectName').value.trim();
    const client = document.getElementById('quickClientName').value.trim();
    
    if (!name) {
        alert('Nome do projeto é obrigatório');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'project_create_quick');
    formData.append('name', name);
    formData.append('client', client);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeQuickProjectModal();
            loadProjects();
            // Selecionar o projeto criado
            setTimeout(() => {
                document.getElementById('timerProject').value = data.project_id;
            }, 500);
        } else {
            showNotification(data.error || 'Erro ao criar projeto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao criar projeto', 'error');
    });
}

// ===== PROJECTS =====
function loadProjects() {
    const url = API_URL + '?action=project_list';
    console.log('16. loadProjects() - URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('17. Resposta recebida:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('18. Dados recebidos:', data);
            if (data.success) {
                state.projects = data.projects;
                console.log('19. Projetos carregados:', state.projects.length);
                renderProjects();
                updateProjectSelects();
            } else {
                console.error('20. API retornou erro:', data.error);
                alert('Erro ao carregar projetos: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('21. ERRO ao carregar projetos:', error);
            alert('Erro de conexão ao carregar projetos: ' + error.message);
        });
}

function renderProjects() {
    const container = document.getElementById('projectsList');
    
    if (state.projects.length === 0) {
        container.innerHTML = `
            <div class=\"empty-state\">
                <i class=\"fas fa-folder-open\"></i>
                <h3>Nenhum projeto encontrado</h3>
                <p>Crie seu primeiro projeto para começar a rastrear tempo</p>
                <button class=\"btn btn-primary\" onclick=\"openProjectModal()\">
                    <i class=\"fas fa-plus\"></i> Criar Projeto
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = state.projects.map(project => `
        <div class=\"project-card\" style=\"border-left-color: ${project.color};\">
            <div class=\"project-header\">
                <div>
                    <h3 class=\"project-name\">${escapeHtml(project.name)}</h3>
                </div>
                <div class=\"project-actions\">
                    <button class=\"btn btn-sm btn-icon btn-secondary\" 
                            onclick=\"openTasksModal('${project.id}', '${escapeHtml(project.name)}')\">
                        <i class=\"fas fa-tasks\"></i>
                    </button>
                    <button class=\"btn btn-sm btn-icon btn-secondary\" 
                            onclick=\"editProject('${project.id}')\">
                        <i class=\"fas fa-edit\"></i>
                    </button>
                    <button class=\"btn btn-sm btn-icon btn-danger\" 
                            onclick=\"deleteProject('${project.id}')\">
                        <i class=\"fas fa-trash\"></i>
                    </button>
                </div>
            </div>
            <div class=\"project-stats\">
                <div class=\"project-stat\">
                    <span class=\"project-stat-value\">${project.task_count}</span>
                    <span class=\"project-stat-label\">Tarefas</span>
                </div>
                <div class=\"project-stat\">
                    <span class=\"project-stat-value\">${project.entry_count}</span>
                    <span class=\"project-stat-label\">Registros</span>
                </div>
                <div class=\"project-stat\">
                    <span class=\"project-stat-value\">${project.duration_formatted}</span>
                    <span class=\"project-stat-label\">Tempo Total</span>
                </div>
            </div>
        </div>
    `).join('');
}

function updateProjectSelects() {
    const timerSelect = document.getElementById('timerProject');
    const filterSelect = document.getElementById('filterProject');
    
    const options = '<option value=\"\">Selecione um projeto</option>' +
        state.projects.map(p => `<option value=\"${p.id}\">${escapeHtml(p.name)}</option>`).join('');
    
    timerSelect.innerHTML = options;
    filterSelect.innerHTML = '<option value=\"\">Todos os projetos</option>' +
        state.projects.map(p => `<option value=\"${p.id}\">${escapeHtml(p.name)}</option>`).join('');
}

function openProjectModal(projectId = null) {
    const modal = document.getElementById('projectModal');
    const form = document.getElementById('projectForm');
    const title = document.getElementById('projectModalTitle');
    
    form.reset();
    
    if (projectId) {
        title.textContent = 'Editar Projeto';
        const project = state.projects.find(p => p.id === projectId);
        if (project) {
            document.getElementById('projectId').value = project.id;
            document.getElementById('projectName').value = project.name;
            document.getElementById('projectColor').value = project.color;
        }
    } else {
        title.textContent = 'Novo Projeto';
    }
    
    modal.classList.add('active');
}

function closeProjectModal() {
    document.getElementById('projectModal').classList.remove('active');
}

function saveProject() {
    const form = document.getElementById('projectForm');
    const formData = new FormData(form);
    
    const projectId = formData.get('id');
    formData.append('action', projectId ? 'project_update' : 'project_create');
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeProjectModal();
            loadProjects();
        } else {
            showNotification(data.error || 'Erro ao salvar projeto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao salvar projeto', 'error');
    });
}

function editProject(projectId) {
    openProjectModal(projectId);
}

function deleteProject(projectId) {
    if (!confirm('Deseja realmente deletar este projeto?')) return;
    
    const formData = new FormData();
    formData.append('action', 'project_delete');
    formData.append('id', projectId);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadProjects();
        } else {
            showNotification(data.error || 'Erro ao deletar projeto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao deletar projeto', 'error');
    });
}

// ===== TASKS =====
function openTasksModal(projectId, projectName) {
    state.currentProject = projectId;
    document.getElementById('tasksModalTitle').textContent = 'Tarefas: ' + projectName;
    document.getElementById('tasksModal').classList.add('active');
    loadTasks(projectId);
}

function closeTasksModal() {
    document.getElementById('tasksModal').classList.remove('active');
    state.currentProject = null;
}

function loadTasks(projectId) {
    fetch(`${API_URL}?action=task_list&project_id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTasks(data.tasks);
            }
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
        });
}

function loadTasksForSelect(projectId) {
    fetch(`${API_URL}?action=task_list&project_id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const taskSelect = document.getElementById('timerTask');
                taskSelect.innerHTML = '<option value=\"\">Selecione uma tarefa</option>' +
                    data.tasks.map(t => `<option value=\"${t.id}\">${escapeHtml(t.name)}</option>`).join('');
            }
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
        });
}

function renderTasks(tasks) {
    const container = document.getElementById('tasksList');
    
    if (tasks.length === 0) {
        container.innerHTML = '<p style=\"text-align: center; color: rgba(255,255,255,0.5); padding: 20px;\">Nenhuma tarefa criada</p>';
        return;
    }
    
    container.innerHTML = tasks.map(task => `
        <div class=\"task-item\">
            <div>
                <div class=\"task-name\">${escapeHtml(task.name)}</div>
                <div class=\"task-meta\">${task.entry_count} registros • ${task.duration_formatted}</div>
            </div>
            <button class=\"btn btn-sm btn-icon btn-danger\" onclick=\"deleteTask('${task.id}')\">
                <i class=\"fas fa-trash\"></i>
            </button>
        </div>
    `).join('');
}

function createTask() {
    const name = document.getElementById('newTaskName').value.trim();
    
    if (!name) {
        showNotification('Digite o nome da tarefa', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'task_create');
    formData.append('project_id', state.currentProject);
    formData.append('name', name);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            document.getElementById('newTaskName').value = '';
            loadTasks(state.currentProject);
        } else {
            showNotification(data.error || 'Erro ao criar tarefa', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao criar tarefa', 'error');
    });
}

function deleteTask(taskId) {
    if (!confirm('Deseja realmente deletar esta tarefa?')) return;
    
    const formData = new FormData();
    formData.append('action', 'task_delete');
    formData.append('id', taskId);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadTasks(state.currentProject);
        } else {
            showNotification(data.error || 'Erro ao deletar tarefa', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao deletar tarefa', 'error');
    });
}

// ===== ENTRIES =====
function loadEntries() {
    const projectFilter = document.getElementById('filterProject').value;
    let url = API_URL + '?action=entry_list&limit=50';
    
    if (projectFilter) {
        url += '&project_id=' + projectFilter;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                state.entries = data.entries;
                renderEntries();
            }
        })
        .catch(error => {
            console.error('Error loading entries:', error);
        });
}

function renderEntries() {
    const container = document.getElementById('entriesList');
    
    if (state.entries.length === 0) {
        container.innerHTML = `
            <div class=\"empty-state\">
                <i class=\"fas fa-clock\"></i>
                <h3>Nenhum registro encontrado</h3>
                <p>Inicie o cronômetro para começar a rastrear tempo</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = state.entries.map(entry => {
        const date = new Date(entry.start_time.replace(' ', 'T'));
        const dateStr = date.toLocaleDateString('pt-BR');
        const timeStr = date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        
        return `
            <div class=\"entry-item\">
                <div class=\"entry-color\" style=\"background: ${entry.project_color || '#7B61FF'};\"></div>
                <div class=\"entry-details\">
                    <div class=\"entry-description\">${entry.description || 'Sem descrição'}</div>
                    <div class=\"entry-meta\">
                        ${entry.project_name ? '<span><i class=\"fas fa-folder\"></i> ' + escapeHtml(entry.project_name) + '</span>' : ''}
                        ${entry.task_name ? '<span><i class=\"fas fa-tasks\"></i> ' + escapeHtml(entry.task_name) + '</span>' : ''}
                        <span><i class=\"fas fa-calendar\"></i> ${dateStr}</span>
                        <span><i class=\"fas fa-clock\"></i> ${timeStr}</span>
                    </div>
                </div>
                <div class=\"entry-duration\">${entry.duration_formatted}</div>
                <div class=\"entry-actions\">
                    <button class=\"btn btn-sm btn-icon btn-danger\" onclick=\"deleteEntry('${entry.id}')\">
                        <i class=\"fas fa-trash\"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function deleteEntry(entryId) {
    if (!confirm('Deseja realmente deletar este registro?')) return;
    
    const formData = new FormData();
    formData.append('action', 'entry_delete');
    formData.append('id', entryId);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadEntries();
        } else {
            showNotification(data.error || 'Erro ao deletar registro', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao deletar registro', 'error');
    });
}

// ===== UTILITY FUNCTIONS =====
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return String(hours).padStart(2, '0') + ':' + 
           String(minutes).padStart(2, '0') + ':' + 
           String(secs).padStart(2, '0');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '\"': '&quot;',
        \"'\": '&#039;'
    };
    return text.replace(/[&<>\"']/g, m => map[m]);
}

function showNotification(message, type = 'info') {
    // Implementação simples com alert
    // Pode ser substituído por uma biblioteca de notificações
    const icon = type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ';
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // TODO: Implementar toast notifications
    if (type === 'error') {
        alert(icon + ' ' + message);
    }
}
