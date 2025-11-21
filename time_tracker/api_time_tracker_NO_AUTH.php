<?php
/**
 * API Time Tracker - VERSÃO SEM AUTENTICAÇÃO (APENAS PARA TESTES)
 * ⚠️ NÃO USE EM PRODUÇÃO! APENAS PARA DEBUG!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configurações
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/dash_database.php';
require_once __DIR__ . '/config/dash_functions.php';

header('Content-Type: application/json; charset=UTF-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ⚠️ FAKE USER ID PARA TESTES
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    // Em vez de retornar erro, vamos buscar qualquer user_id do banco
    try {
        $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
        $user = $stmt->fetch();
        if ($user) {
            $user_id = $user['id'];
            error_log("⚠️ API NO AUTH: Usando user_id fake: $user_id");
        } else {
            die(json_encode([
                'success' => false,
                'error' => 'Nenhum usuário encontrado no banco de dados para usar como teste'
            ]));
        }
    } catch (Exception $e) {
        die(json_encode([
            'success' => false,
            'error' => 'Erro ao buscar user_id: ' . $e->getMessage()
        ]));
    }
}

/**
 * FUNÇÕES AUXILIARES
 */

function sanitizeInput($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function formatDuration($seconds) {
    $seconds = (int)$seconds;
    $hours   = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs    = $seconds % 60;
    return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' .
           str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' .
           str_pad($secs, 2, '0', STR_PAD_LEFT);
}

function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function jsonSuccess($data = [], $message = '') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'error'   => null,
        'projects' => $data['projects'] ?? null,
        'tasks'    => $data['tasks'] ?? null,
        'entries'  => $data['entries'] ?? null,
        'entry'    => $data['entry'] ?? null,
        'project_id' => $data['project_id'] ?? null,
        'project_name' => $data['project_name'] ?? null,
        'task_id' => $data['task_id'] ?? null,
        'duration' => $data['duration'] ?? null,
        'duration_formatted' => $data['duration_formatted'] ?? null,
        'data'    => $data
    ]);
    exit;
}

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => '',
        'error'   => $message
    ]);
    exit;
}

function calculateDuration($start_time, $end_time, $paused_duration = 0) {
    $start = new DateTime($start_time);
    $end   = new DateTime($end_time);
    $diff  = $end->getTimestamp() - $start->getTimestamp() - (int)$paused_duration;
    return max(0, $diff);
}

// PROCESSAR AÇÕES
try {
    switch ($action) {
        case 'project_list':
            $stmt = $pdo->prepare("
                SELECT
                    id,
                    title as name,
                    client_id,
                    '#7B61FF' as color,
                    status,
                    created_at
                FROM dash_projects
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$user_id]);
            $projects = $stmt->fetchAll();
            
            foreach ($projects as &$project) {
                try {
                    $stmt_tasks = $pdo->prepare("SELECT COUNT(*) FROM time_tasks WHERE project_id = ?");
                    $stmt_tasks->execute([$project['id']]);
                    $project['task_count'] = $stmt_tasks->fetchColumn();
                } catch (Exception $e) {
                    $project['task_count'] = 0;
                }
                
                try {
                    $stmt_entries = $pdo->prepare("
                        SELECT COUNT(*) as entry_count, COALESCE(SUM(duration), 0) as total_duration
                        FROM time_entries WHERE project_id = ?
                    ");
                    $stmt_entries->execute([$project['id']]);
                    $stats = $stmt_entries->fetch();
                    $project['entry_count'] = $stats['entry_count'];
                    $project['total_duration'] = $stats['total_duration'];
                    $project['duration_formatted'] = formatDuration($stats['total_duration']);
                } catch (Exception $e) {
                    $project['entry_count'] = 0;
                    $project['total_duration'] = 0;
                    $project['duration_formatted'] = '00:00:00';
                }
            }
            
            jsonSuccess(['projects' => $projects]);
            break;
            
        case 'project_create_quick':
            $name   = sanitizeInput($_POST['name'] ?? '');
            $client = sanitizeInput($_POST['client'] ?? '');
            
            if (empty($name)) {
                jsonError('Nome do projeto é obrigatório');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO dash_projects
                    (user_id, title, client_id, status, created_at)
                VALUES (?, ?, 0, 'in_progress', NOW())
            ");
            $stmt->execute([$user_id, $name]);
            $project_id = $pdo->lastInsertId();
            
            jsonSuccess([
                'project_id' => $project_id,
                'project_name' => $name
            ], 'Projeto criado com sucesso');
            break;
            
        case 'entry_list':
            $limit = intval($_GET['limit'] ?? 50);
            $project_id = $_GET['project_id'] ?? '';
            
            $sql = "
                SELECT e.*,
                    p.title as project_name,
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
            
            $sql .= " ORDER BY e.start_time DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $entries = $stmt->fetchAll();
            
            foreach ($entries as &$entry) {
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
                $entry['project_color'] = '#7B61FF';
            }
            
            jsonSuccess(['entries' => $entries]);
            break;
            
        case 'entry_running':
            $stmt = $pdo->prepare("
                SELECT e.*,
                    p.title as project_name,
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
            $stmt = $pdo->prepare("SELECT id FROM time_entries WHERE user_id = ? AND is_running = 1");
            $stmt->execute([$user_id]);
            if ($stmt->fetch()) {
                jsonError('Já existe um cronômetro ativo');
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
            
        default:
            jsonError('Ação não implementada nesta versão: ' . $action, 400);
    }
    
} catch (PDOException $e) {
    error_log("API NO AUTH Error (PDO): " . $e->getMessage());
    jsonError('Erro no banco de dados: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("API NO AUTH Error: " . $e->getMessage());
    jsonError('Erro no servidor: ' . $e->getMessage(), 500);
}
