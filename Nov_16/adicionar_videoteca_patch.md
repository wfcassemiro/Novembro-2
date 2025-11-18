# Patch para Adicionar Palestras à Videoteca

## Arquivos a serem modificados:

### 1. palestras_agendadas.php

#### Adicionar no final da coluna "Ações" (após o botão de Excluir):

```php
<!-- Botão: Adicionar à Videoteca -->
<button onclick="openVideotecaModal(<?php echo $announcement['id']; ?>)" 
        class="action-btn" 
        style="background: linear-gradient(135deg, #10b981, #059669);"
        title="Adicionar à Videoteca">
    <i class="fas fa-video"></i>
</button>
```

#### Adicionar o Modal antes do fechamento de `</body>`:

```html
<!-- Modal: Adicionar à Videoteca -->
<div id="videotecaModal" class="vision-modal">
    <div class="vision-modal-content">
        <div class="vision-modal-header">
            <h3><i class="fas fa-video"></i> Adicionar à Videoteca</h3>
            <button type="button" class="vision-modal-close" onclick="closeVideotecaModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="videotecaForm" class="vision-modal-form">
            <input type="hidden" id="palestra_id" name="palestra_id">
            
            <div class="form-group">
                <label for="palestra_info">Palestra:</label>
                <div id="palestra_info" style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; color: var(--text-secondary);">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
            
            <div class="form-group">
                <label for="embed_code">Código Embed do Vídeo: *</label>
                <textarea id="embed_code" name="embed_code" required class="vision-input" 
                          rows="6" placeholder="Cole aqui o código embed (iframe) do vídeo"></textarea>
                <small style="color: var(--text-secondary); font-size: 0.85rem;">
                    Ex: &lt;iframe src="..." width="560" height="315"&gt;&lt;/iframe&gt;
                </small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="duration_minutes">Duração (minutos): *</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" required 
                           class="vision-input" min="1" placeholder="Ex: 60">
                </div>
                
                <div class="form-group">
                    <label for="category">Categoria: *</label>
                    <select id="category" name="category" required class="vision-select">
                        <option value="">-- Selecione --</option>
                        <option value="TAV">TAV</option>
                        <option value="Literária">Literária</option>
                        <option value="Jurídica">Jurídica</option>
                        <option value="Médica">Médica</option>
                        <option value="Técnica">Técnica</option>
                        <option value="Geral">Geral</option>
                        <option value="Palestras">Palestras</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_featured" name="is_featured" value="1">
                    Marcar como Destaque
                </label>
            </div>
            
            <div class="vision-modal-actions">
                <button type="button" class="vision-btn vision-btn-secondary" onclick="closeVideotecaModal()">
                    Cancelar
                </button>
                <button type="submit" class="vision-btn vision-btn-success">
                    <i class="fas fa-check"></i> Adicionar à Videoteca
                </button>
            </div>
        </form>
    </div>
</div>
```

#### Adicionar JavaScript antes do fechamento de `</script>`:

```javascript
// ===== Adicionar à Videoteca =====
let currentPalestraData = null;

function openVideotecaModal(id) {
    // Busca os dados da palestra
    fetch(`/vision/manage_announcements.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPalestraData = data.data;
                
                // Preenche o form
                document.getElementById('palestra_id').value = id;
                document.getElementById('palestra_info').innerHTML = `
                    <strong>${data.data.title}</strong><br>
                    <small>Palestrante: ${data.data.speaker}</small>
                `;
                
                // Limpa outros campos
                document.getElementById('embed_code').value = '';
                document.getElementById('duration_minutes').value = '';
                document.getElementById('category').value = '';
                document.getElementById('is_featured').checked = false;
                
                // Abre o modal
                document.getElementById('videotecaModal').classList.add('active');
            } else {
                alert('Erro ao carregar dados da palestra');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados da palestra');
        });
}

function closeVideotecaModal() {
    document.getElementById('videotecaModal').classList.remove('active');
    currentPalestraData = null;
}

// Submit do formulário
document.getElementById('videotecaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Adiciona dados da palestra atual
    if (currentPalestraData) {
        formData.append('title', currentPalestraData.title);
        formData.append('speaker', currentPalestraData.speaker);
        formData.append('description', currentPalestraData.description);
        formData.append('image_path', currentPalestraData.image_path || '');
    }
    
    fetch('/vision/add_to_videoteca.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Palestra adicionada à Videoteca com sucesso!');
            closeVideotecaModal();
            window.location.reload();
        } else {
            alert('❌ Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao adicionar à videoteca');
    });
});

// Fechar modal ao clicar fora
document.getElementById('videotecaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVideotecaModal();
    }
});
```

#### Adicionar CSS no `<style>`:

```css
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
```

---

### 2. Criar novo arquivo: `/vision/add_to_videoteca.php`

```php
<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Verifica autenticação e permissão de admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Recebe dados do formulário
    $palestra_id = $_POST['palestra_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $speaker = $_POST['speaker'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_path = $_POST['image_path'] ?? '';
    $embed_code = $_POST['embed_code'] ?? '';
    $duration_minutes = intval($_POST['duration_minutes'] ?? 0);
    $category = $_POST['category'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validações
    if (empty($palestra_id) || empty($title) || empty($speaker) || empty($embed_code) || empty($category)) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
    }
    
    if ($duration_minutes <= 0) {
        throw new Exception('Duração deve ser maior que zero');
    }
    
    // Gera UUID para o ID
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Prepara thumbnail_url (usa image_path se disponível)
    $thumbnail_url = !empty($image_path) ? $image_path : null;
    
    // Insere na tabela lectures
    $sql = "INSERT INTO lectures (
        id, title, speaker, description, duration_minutes, 
        embed_code, thumbnail_url, category, is_featured, 
        created_at, updated_at
    ) VALUES (
        :id, :title, :speaker, :description, :duration_minutes,
        :embed_code, :thumbnail_url, :category, :is_featured,
        NOW(), NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $uuid,
        ':title' => $title,
        ':speaker' => $speaker,
        ':description' => $description,
        ':duration_minutes' => $duration_minutes,
        ':embed_code' => $embed_code,
        ':thumbnail_url' => $thumbnail_url,
        ':category' => $category,
        ':is_featured' => $is_featured
    ]);
    
    // Marca a palestra original como "publicada" (is_active = 2)
    // 0 = inativa, 1 = ativa/agendada, 2 = publicada na videoteca
    $updateSql = "UPDATE upcoming_announcements SET is_active = 2 WHERE id = :id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([':id' => $palestra_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Palestra adicionada à videoteca com sucesso',
        'lecture_id' => $uuid
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

---

### 3. Modificar `palestras_agendadas.php` - Lógica de separação

Alterar a lógica de separação para considerar `is_active = 2` como publicadas:

```php
// Separa em: upcoming/active, past, e published
$upcoming_active = [];
$past = [];
$published = [];

foreach ($all_announcements as $announcement) {
    $announcement_timestamp = strtotime($announcement['announcement_date']);
    $today_timestamp = strtotime(date('Y-m-d'));
    
    if ($announcement['is_active'] == 2) {
        // Publicadas na videoteca
        $published[] = $announcement;
    } elseif ($announcement_timestamp < $today_timestamp) {
        // Passadas
        $past[] = $announcement;
    } else {
        // Futuras/ativas
        $upcoming_active[] = $announcement;
    }
}

// Exibição: upcoming_active -> past -> published
$display_announcements = array_merge($upcoming_active, $past, $published);
```

E adicionar badge visual para publicadas:

```php
<td style="<?php echo ($announcement['is_active'] == 2) ? 'opacity: 0.6;' : ''; ?>">
    <?php echo htmlspecialchars($announcement['title']); ?>
    <?php if ($announcement['is_active'] == 2): ?>
        <span style="display: inline-block; padding: 2px 8px; background: #10b981; color: white; 
                     border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">
            ✓ Publicada
        </span>
    <?php endif; ?>
</td>
```

E desabilitar o botão "Adicionar à Videoteca" para palestras já publicadas:

```php
<?php if ($announcement['is_active'] != 2): ?>
<!-- Botão: Adicionar à Videoteca -->
<button onclick="openVideotecaModal(<?php echo $announcement['id']; ?>)" 
        class="action-btn" 
        style="background: linear-gradient(135deg, #10b981, #059669);"
        title="Adicionar à Videoteca">
    <i class="fas fa-video"></i>
</button>
<?php else: ?>
<!-- Botão desabilitado -->
<button class="action-btn" disabled 
        style="background: #6b7280; cursor: not-allowed; opacity: 0.5;"
        title="Já publicada na videoteca">
    <i class="fas fa-check"></i>
</button>
<?php endif; ?>
```

---

## Instruções de Instalação:

1. Aplicar modificações no `palestras_agendadas.php`
2. Criar o arquivo `/vision/add_to_videoteca.php`
3. Testar o fluxo completo
4. Verificar se os vídeos aparecem na videoteca.php

## Campos da tabela lectures preenchidos:
- ✅ id (UUID gerado)
- ✅ title (da palestra)
- ✅ speaker (da palestra)
- ✅ description (da palestra)
- ✅ duration_minutes (input do usuário)
- ✅ embed_code (input do usuário)
- ✅ thumbnail_url (image_path da palestra)
- ✅ category (select do usuário)
- ✅ is_featured (checkbox do usuário)
- ✅ created_at (NOW())
- ✅ updated_at (NOW())
