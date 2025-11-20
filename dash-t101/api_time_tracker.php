<?php
/**
 * API Time Tracker
 * Integrado com dash_projects existente
 */

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=UTF-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        // ===== PROJETOS (usando dash_projects) =====
        case 'project_list':
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    project_name as name,
                    client_name,
                    '#7B61FF' as color,
                    status
                FROM dash_projects
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            $projects = $stmt->fetchAll();
            
            // Calcular estatísticas de cada projeto
            foreach ($projects as &$project) {
                // Contar tarefas
                $stmt_tasks = $pdo->prepare("SELECT COUNT(*) FROM time_tasks WHERE project_id = ?");
                $stmt_tasks->execute([$project['id']]);
                $project['task_count'] = $stmt_tasks->fetchColumn();
                
                // Contar entries e tempo total
                $stmt_entries = $pdo->prepare("
                    SELECT COUNT(*) as entry_count, COALESCE(SUM(duration), 0) as total_duration
                    FROM time_entries WHERE project_id = ?
                ");
                $stmt_entries->execute([$project['id']]);
                $stats = $stmt_entries->fetch();
                $project['entry_count'] = $stats['entry_count'];
                $project['total_duration'] = $stats['total_duration'];
                $project['duration_formatted'] = formatDuration($stats['total_duration']);
            }
            
            jsonSuccess(['projects' => $projects]);
            break;

        case 'project_create_quick':
            $name = sanitizeInput($_POST['name'] ?? '');
            $client = sanitizeInput($_POST['client'] ?? '');
            
            if (empty($name)) {
                jsonError('Nome do projeto é obrigatório');
            }
            
            // Criar projeto na tabela dash_projects
            $stmt = $pdo->prepare("
                INSERT INTO dash_projects 
                (user_id, project_name, client_name, status, created_at)
                VALUES (?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$user_id, $name, $client]);
            $project_id = $pdo->lastInsertId();
            
            jsonSuccess(['project_id' => $project_id, 'project_name' => $name], 'Projeto criado com sucesso');
            break;

        // ===== TAREFAS =====
        case 'task_list':
            $project_id = $_GET['project_id'] ?? '';
            
            if (empty($project_id)) {
                jsonError('ID do projeto não fornecido');
            }
            
            $stmt = $pdo->prepare("
                SELECT t.*, 
                       COUNT(e.id) as entry_count,
                       COALESCE(SUM(e.duration), 0) as total_duration
                FROM time_tasks t
                LEFT JOIN time_entries e ON t.id = e.task_id
                WHERE t.project_id = ? AND t.user_id = ? AND t.is_active = 1
                GROUP BY t.id
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$project_id, $user_id]);
            $tasks = $stmt->fetchAll();
            
            foreach ($tasks as &$task) {
                $task['duration_formatted'] = formatDuration($task['total_duration']);
            }
            
            jsonSuccess(['tasks' => $tasks]);
            break;

        case 'task_create':
            $project_id = $_POST['project_id'] ?? '';
            $name = sanitizeInput($_POST['name'] ?? '');
            
            if (empty($project_id) || empty($name)) {
                jsonError('Dados incompletos');
            }
            
            $id = generateUUID();
            $stmt = $pdo->prepare("
                INSERT INTO time_tasks (id, project_id, user_id, name)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$id, $project_id, $user_id, $name]);
            
            jsonSuccess(['task_id' => $id], 'Tarefa criada com sucesso');
            break;

        case 'task_delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                jsonError('ID da tarefa não fornecido');
            }
            
            $stmt = $pdo->prepare("UPDATE time_tasks SET is_active = 0 WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            jsonSuccess([], 'Tarefa deletada com sucesso');
            break;

        // ===== REGISTROS DE TEMPO =====
        case 'entry_list':
            $limit = intval($_GET['limit'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);
            $project_id = $_GET['project_id'] ?? '';
            
            $sql = "
                SELECT e.*, 
                       p.project_name, 
                       t.name as task_name
                FROM time_entries e
                LEFT JOIN dash_projects p ON e.project_id = p.id
                LEFT JOIN time_tasks t ON e.task_id = t.id
                WHERE e.user_id = ?
            ";
            
            $params = [$user_id];
            
            if (!empty($project_id)) {
                $sql .= " AND e.project_id = ?";
                $params[] = $project_id;
            }
            
            $sql .= " ORDER BY e.start_time DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $entries = $stmt->fetchAll();
            
            foreach ($entries as &$entry) {
                // Calcular duração se estiver rodando
                if ($entry['is_running']) {
                    $now = new DateTime();
                    $start = new DateTime($entry['start_time']);
                    $elapsed = $now->getTimestamp() - $start->getTimestamp();
                    
                    if ($entry['paused_at']) {
                        $pausedTime = $now->getTimestamp() - (new DateTime($entry['paused_at']))->getTimestamp();
                        $entry['duration'] = $elapsed - $pausedTime - $entry['paused_duration'];
                    } else {
                        $entry['duration'] = $elapsed - $entry['paused_duration'];
                    }
                }
                
                $entry['duration_formatted'] = formatDuration($entry['duration']);
                $entry['project_color'] = '#7B61FF'; // Cor padrão
            }
            
            jsonSuccess(['entries' => $entries]);
            break;

        case 'entry_running':
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       p.project_name, 
                       t.name as task_name
                FROM time_entries e
                LEFT JOIN dash_projects p ON e.project_id = p.id
                LEFT JOIN time_tasks t ON e.task_id = t.id
                WHERE e.user_id = ? AND e.is_running = 1
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $entry = $stmt->fetch();
            
            if ($entry) {
                // Calcular duração atual
                $now = new DateTime();
                $start = new DateTime($entry['start_time']);
                $elapsed = $now->getTimestamp() - $start->getTimestamp();
                
                if ($entry['paused_at']) {
                    $pausedTime = $now->getTimestamp() - (new DateTime($entry['paused_at']))->getTimestamp();
                    $entry['duration'] = $elapsed - $pausedTime - $entry['paused_duration'];
                    $entry['is_paused'] = true;
                } else {
                    $entry['duration'] = $elapsed - $entry['paused_duration'];
                    $entry['is_paused'] = false;
                }
                
                $entry['duration_formatted'] = formatDuration($entry['duration']);
                $entry['project_color'] = '#7B61FF';
            }
            
            jsonSuccess(['entry' => $entry]);
            break;

        case 'entry_start':
            // Verificar se já existe um cronômetro ativo
            $stmt = $pdo->prepare("SELECT id FROM time_entries WHERE user_id = ? AND is_running = 1");
            $stmt->execute([$user_id]);
            if ($stmt->fetch()) {
                jsonError('Já existe um cronômetro ativo. Pare-o antes de iniciar outro.');
            }
            
            $project_id = $_POST['project_id'] ?? null;
            $task_id = $_POST['task_id'] ?? null;
            $description = sanitizeInput($_POST['description'] ?? '');
            
            $id = generateUUID();
            $start_time = date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("
                INSERT INTO time_entries (id, user_id, project_id, task_id, description, start_time, is_running)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$id, $user_id, $project_id ?: null, $task_id ?: null, $description, $start_time]);
            
            jsonSuccess(['entry_id' => $id], 'Cronômetro iniciado');
            break;

        case 'entry_pause':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                jsonError('ID do registro não fornecido');
            }
            
            $stmt = $pdo->prepare("
                UPDATE time_entries 
                SET paused_at = NOW()
                WHERE id = ? AND user_id = ? AND is_running = 1 AND paused_at IS NULL
            ");
            $stmt->execute([$id, $user_id]);
            
            if ($stmt->rowCount() === 0) {
                jsonError('Registro não encontrado ou já pausado');
            }
            
            jsonSuccess([], 'Cronômetro pausado');
            break;

        case 'entry_resume':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                jsonError('ID do registro não fornecido');
            }
            
            // Calcular tempo pausado e adicionar ao total
            $stmt = $pdo->prepare("
                SELECT paused_at, paused_duration 
                FROM time_entries 
                WHERE id = ? AND user_id = ? AND is_running = 1 AND paused_at IS NOT NULL
            ");
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch();
            
            if (!$entry) {
                jsonError('Registro não encontrado ou não pausado');
            }
            
            $pausedDuration = (new DateTime())->getTimestamp() - (new DateTime($entry['paused_at']))->getTimestamp();
            $totalPaused = $entry['paused_duration'] + $pausedDuration;
            
            $stmt = $pdo->prepare("
                UPDATE time_entries 
                SET paused_at = NULL, paused_duration = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$totalPaused, $id, $user_id]);
            
            jsonSuccess([], 'Cronômetro retomado');
            break;

        case 'entry_stop':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                jsonError('ID do registro não fornecido');
            }
            
            // Obter dados do registro
            $stmt = $pdo->prepare("
                SELECT start_time, paused_at, paused_duration 
                FROM time_entries 
                WHERE id = ? AND user_id = ? AND is_running = 1
            ");
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch();
            
            if (!$entry) {
                jsonError('Registro não encontrado ou já parado');
            }
            
            $end_time = date('Y-m-d H:i:s');
            
            // Se estiver pausado, adicionar tempo de pausa ao total
            $pausedDuration = $entry['paused_duration'];
            if ($entry['paused_at']) {
                $additionalPause = (new DateTime())->getTimestamp() - (new DateTime($entry['paused_at']))->getTimestamp();
                $pausedDuration += $additionalPause;
            }
            
            // Calcular duração total
            $duration = calculateDuration($entry['start_time'], $end_time, $pausedDuration);
            
            $stmt = $pdo->prepare("
                UPDATE time_entries 
                SET end_time = ?, duration = ?, is_running = 0, paused_at = NULL, paused_duration = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$end_time, $duration, $pausedDuration, $id, $user_id]);
            
            jsonSuccess(['duration' => $duration, 'duration_formatted' => formatDuration($duration)], 'Cronômetro parado');
            break;

        case 'entry_delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                jsonError('ID do registro não fornecido');
            }
            
            $stmt = $pdo->prepare("DELETE FROM time_entries WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            jsonSuccess([], 'Registro deletado com sucesso');
            break;

        case 'entry_total':
            $project_id = $_GET['project_id'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? '';
            
            $sql = "
                SELECT 
                    COALESCE(SUM(duration), 0) as total_duration,
                    COUNT(*) as entry_count
                FROM time_entries
                WHERE user_id = ? AND is_running = 0
            ";
            
            $params = [$user_id];
            
            if (!empty($project_id)) {
                $sql .= " AND project_id = ?";
                $params[] = $project_id;
            }
            
            if (!empty($start_date)) {
                $sql .= " AND start_time >= ?";
                $params[] = $start_date . ' 00:00:00';
            }
            
            if (!empty($end_date)) {
                $sql .= " AND start_time <= ?";
                $params[] = $end_date . ' 23:59:59';
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            $result['total_duration_formatted'] = formatDuration($result['total_duration']);
            $result['total_hours'] = round($result['total_duration'] / 3600, 2);
            
            jsonSuccess($result);
            break;

        default:
            jsonError('Ação inválida', 400);
    }
    
} catch (PDOException $e) {
    error_log("Time Tracker API Error: " . $e->getMessage());
    jsonError('Erro no servidor. Tente novamente.', 500);
} catch (Exception $e) {
    error_log("Time Tracker Error: " . $e->getMessage());
    jsonError($e->getMessage(), 400);
}
