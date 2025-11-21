<?php
/**
 * Teste Direto da API - Mostra exatamente o que está acontecendo
 */

session_start();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste Direto da API</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .box {
            background: #252526;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #007acc;
        }
        h1 { color: #007acc; }
        h2 { color: #4ec9b0; margin-top: 0; }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #3e3e42;
        }
        .btn {
            background: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        .btn:hover { background: #1177bb; }
        .success { color: #89d185; }
        .error { color: #f48771; }
        .warning { color: #d7ba7d; }
    </style>
</head>
<body>
    <h1>🧪 Teste Direto da API - Time Tracker</h1>
    
    <div class="box">
        <h2>1️⃣ Informações da Sessão</h2>
        <pre><?php print_r($_SESSION); ?></pre>
        <p><b>Session ID:</b> <?php echo session_id(); ?></p>
        <p><b>User ID:</b> <span class="<?php echo isset($_SESSION['user_id']) ? 'success' : 'error'; ?>">
            <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '❌ NÃO DEFINIDO'; ?>
        </span></p>
    </div>
    
    <div class="box">
        <h2>2️⃣ Teste via PHP (Backend)</h2>
        <p>Testando API diretamente do servidor (mesma sessão):</p>
        <?php
        // Incluir a API debug
        ob_start();
        $_GET['action'] = 'project_list';
        include __DIR__ . '/api_time_tracker_debug.php';
        $api_response = ob_get_clean();
        
        echo '<pre>' . htmlspecialchars($api_response) . '</pre>';
        ?>
    </div>
    
    <div class="box">
        <h2>3️⃣ Teste via JavaScript (Frontend)</h2>
        <p>Testando API como o navegador faria:</p>
        <button class="btn" onclick="testarAPI()">🧪 Testar Agora</button>
        <button class="btn" onclick="testarCreateProject()">➕ Testar Criar Projeto</button>
        <div id="resultado"></div>
    </div>
    
    <div class="box">
        <h2>4️⃣ Cookies do Navegador</h2>
        <pre id="cookies"></pre>
    </div>
    
    <script>
        // Mostrar cookies
        document.getElementById('cookies').textContent = document.cookie || 'Nenhum cookie encontrado';
        
        function testarAPI() {
            const resultado = document.getElementById('resultado');
            resultado.innerHTML = '<p class="warning">⏳ Testando...</p>';
            
            console.log('=== TESTE DA API ===');
            console.log('URL:', '/dash-t101/api_time_tracker_debug.php?action=project_list');
            
            fetch('/dash-t101/api_time_tracker_debug.php?action=project_list', {
                method: 'GET',
                credentials: 'include', // Importante: incluir cookies
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Status:', response.status);
                console.log('Headers:', response.headers);
                return response.text();
            })
            .then(text => {
                console.log('Resposta completa:', text);
                
                let html = '<h3>Resposta da API:</h3>';
                html += '<pre>' + escapeHtml(text) + '</pre>';
                
                try {
                    const data = JSON.parse(text);
                    html += '<h3>JSON Parseado:</h3>';
                    html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    
                    if (data.success) {
                        html += '<p class="success">✅ API funcionou! ' + (data.projects ? data.projects.length + ' projetos encontrados' : '') + '</p>';
                    } else {
                        html += '<p class="error">❌ API retornou erro: ' + data.error + '</p>';
                        if (data.debug_session) {
                            html += '<h3>Debug Session:</h3>';
                            html += '<pre>' + JSON.stringify(data.debug_session, null, 2) + '</pre>';
                        }
                    }
                } catch (e) {
                    html += '<p class="error">❌ Erro ao parsear JSON: ' + e.message + '</p>';
                }
                
                resultado.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro:', error);
                resultado.innerHTML = '<p class="error">❌ Erro: ' + error.message + '</p>';
            });
        }
        
        function testarCreateProject() {
            const resultado = document.getElementById('resultado');
            resultado.innerHTML = '<p class="warning">⏳ Criando projeto de teste...</p>';
            
            const formData = new FormData();
            formData.append('action', 'project_create_quick');
            formData.append('name', 'Projeto de Teste ' + new Date().toLocaleTimeString());
            formData.append('client', 'Cliente Teste');
            
            console.log('=== TESTE CREATE PROJECT ===');
            
            fetch('/dash-t101/api_time_tracker_debug.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => {
                console.log('Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Resposta:', text);
                
                let html = '<h3>Resposta do Create:</h3>';
                html += '<pre>' + escapeHtml(text) + '</pre>';
                
                try {
                    const data = JSON.parse(text);
                    html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    
                    if (data.success) {
                        html += '<p class="success">✅ Projeto criado! ID: ' + data.project_id + '</p>';
                    } else {
                        html += '<p class="error">❌ Erro: ' + data.error + '</p>';
                    }
                } catch (e) {
                    html += '<p class="error">❌ Erro ao parsear: ' + e.message + '</p>';
                }
                
                resultado.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro:', error);
                resultado.innerHTML = '<p class="error">❌ Erro: ' + error.message + '</p>';
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    
    <div class="box">
        <h2>📋 Diagnóstico Rápido</h2>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p class="error">❌ <b>PROBLEMA ENCONTRADO:</b> user_id não está definido na sessão!</p>
            <p><b>Solução:</b> Você precisa fazer login no sistema primeiro.</p>
            <p><b>Alternativa temporária:</b> Desabilitar verificação de autenticação na API debug.</p>
        <?php else: ?>
            <p class="success">✅ user_id definido: <?php echo $_SESSION['user_id']; ?></p>
            <p>Se a API ainda não funcionar via JavaScript, pode ser problema de cookies/sessão.</p>
        <?php endif; ?>
    </div>
    
    <div class="box">
        <h2>🔧 Solução Temporária (se não estiver logado)</h2>
        <p>Se você não estiver logado e quiser testar mesmo assim, edite o arquivo:</p>
        <pre>api_time_tracker_debug.php</pre>
        <p>E comente/modifique esta seção:</p>
        <pre>if (!$user_id) {
    // Comentar estas linhas:
    // debugLog("❌ User ID não definido");
    // die(json_encode([...]));
    
    // E adicionar:
    $user_id = '<?php echo $_SESSION['user_id'] ?? 'FAKE-USER-ID'; ?>';
    debugLog("⚠️ Usando user_id fake: $user_id");
}</pre>
    </div>
    
    <div class="box">
        <p><a href="time-tracker.php" class="btn">◀ Voltar ao Time Tracker</a></p>
        <p><a href="view_logs.php" class="btn">📋 Ver Logs Completos</a></p>
    </div>
</body>
</html>
