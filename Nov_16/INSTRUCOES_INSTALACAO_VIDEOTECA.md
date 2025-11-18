# Instruções de Instalação - Adicionar à Videoteca

## Arquivos Criados

### 1. `/vision/palestras_agendadas.php`
- Arquivo completo com todas as modificações
- Substitui o arquivo existente

### 2. `/vision/add_to_videoteca.php`
- Novo endpoint para processar a adição à videoteca
- Cria um novo arquivo

## Passos de Instalação

### Passo 1: Backup
```bash
# Faça backup do arquivo original
cp vision/palestras_agendadas.php vision/palestras_agendadas.php.bak
```

### Passo 2: Copiar Arquivos
```bash
# Substitua o arquivo existente
cp Nov_16/vision/palestras_agendadas.php vision/palestras_agendadas.php

# Crie o novo endpoint
cp Nov_16/vision/add_to_videoteca.php vision/add_to_videoteca.php
```

### Passo 3: Verificar Permissões
```bash
# Garanta que os arquivos tenham as permissões corretas
chmod 644 vision/palestras_agendadas.php
chmod 644 vision/add_to_videoteca.php
```

### Passo 4: Não é necessário alterar o banco de dados
O sistema usa o campo `is_active` da tabela `upcoming_announcements`:
- `0` = Inativa
- `1` = Ativa/Agendada
- `2` = Publicada na Videoteca ✅ (NOVO)

## O que foi modificado

### palestras_agendadas.php

#### 1. Lógica de Separação
- Adicionado array `$published` para palestras publicadas
- Palestras com `is_active = 2` são marcadas como publicadas

#### 2. Estatísticas
- Novo card "Publicadas" no dashboard
- Contador de palestras publicadas

#### 3. Tabela
- Badge "✓ Publicada" aparece ao lado do título
- Linha fica com opacidade reduzida
- Status exibe "Publicada" em verde

#### 4. Coluna de Ações
- Novo botão verde "Adicionar à Videoteca"
- Botão fica desabilitado após publicação
- Exibe checkmark em palestras já publicadas

#### 5. Novo Modal "Adicionar à Videoteca"
Campos:
- Info da palestra (pré-preenchido)
- Código embed (textarea)
- Duração em minutos (number input)
- Categoria (select)
- Marcar como destaque (checkbox)

#### 6. JavaScript
- `openVideotecaModal(id)` - Abre modal e carrega dados
- `closeVideotecaModal()` - Fecha modal
- Submit form - Envia dados para endpoint

### add_to_videoteca.php (NOVO)

#### Funcionalidades:
1. Valida autenticação de admin
2. Recebe dados da palestra e do formulário
3. Gera UUID para o ID
4. Insere na tabela `lectures`
5. Marca palestra como publicada (`is_active = 2`)
6. Retorna resposta JSON

#### Campos inseridos na tabela `lectures`:
- `id` (UUID gerado)
- `title` (da palestra)
- `speaker` (da palestra)
- `description` (da palestra)
- `duration_minutes` (do formulário)
- `embed_code` (do formulário)
- `thumbnail_url` (image_path da palestra)
- `category` (do formulário)
- `is_featured` (do formulário)
- `created_at` (NOW())
- `updated_at` (NOW())

## Fluxo de Uso

1. Admin acessa "Palestras Agendadas"
2. Clica no botão verde (ícone de vídeo) na coluna Ações
3. Modal abre com dados pré-carregados
4. Admin preenche:
   - Código embed (obrigatório)
   - Duração em minutos (obrigatório)
   - Categoria (obrigatório)
   - Marcar como destaque (opcional)
5. Clica "Adicionar à Videoteca"
6. Sistema:
   - Insere na tabela `lectures`
   - Marca palestra como publicada (`is_active = 2`)
   - Exibe mensagem de sucesso
   - Recarrega página
7. Palestra agora tem badge "✓ Publicada"
8. Botão de adicionar fica desabilitado
9. Vídeo aparece na videoteca.php

## Testes Recomendados

### Teste 1: Adicionar à Videoteca
- [ ] Abrir palestras agendadas
- [ ] Clicar no botão verde (vídeo)
- [ ] Modal abre com dados corretos
- [ ] Preencher todos os campos
- [ ] Submeter formulário
- [ ] Verificar mensagem de sucesso
- [ ] Página recarrega
- [ ] Badge "Publicada" aparece
- [ ] Botão fica desabilitado

### Teste 2: Verificar na Videoteca
- [ ] Acessar videoteca.php
- [ ] Vídeo aparece na lista
- [ ] Thumbnail correto
- [ ] Título correto
- [ ] Palestrante correto
- [ ] Categoria correta
- [ ] Clicar no vídeo
- [ ] Player exibe corretamente

### Teste 3: Status
- [ ] Contador "Publicadas" atualiza
- [ ] Palestras publicadas têm opacidade reduzida
- [ ] Status exibe "Publicada" em verde

## Troubleshooting

### Erro: "Acesso negado"
**Causa**: Usuário não é admin
**Solução**: Verificar `$_SESSION['is_admin']`

### Erro: "Todos os campos obrigatórios devem ser preenchidos"
**Causa**: Campos vazios no formulário
**Solução**: Preencher código embed, duração e categoria

### Erro: "Duração deve ser maior que zero"
**Causa**: Duração não foi informada ou é 0
**Solução**: Informar duração válida (ex: 60)

### Vídeo não aparece na videoteca
**Causa**: Vídeo foi inserido mas não é exibido
**Soluções**:
1. Verificar se `embed_code` foi salvo corretamente
2. Verificar se `palestra.php` está funcionando
3. Limpar cache do navegador
4. Verificar logs do PHP

### Badge não aparece
**Causa**: `is_active` não foi atualizado
**Solução**: 
```sql
-- Verificar status
SELECT id, title, is_active FROM upcoming_announcements WHERE id = 'ID_DA_PALESTRA';

-- Se necessário, atualizar manualmente
UPDATE upcoming_announcements SET is_active = 2 WHERE id = 'ID_DA_PALESTRA';
```

## Rollback

Se precisar reverter:

```bash
# Restaurar arquivo original
cp vision/palestras_agendadas.php.bak vision/palestras_agendadas.php

# Remover endpoint
rm vision/add_to_videoteca.php

# Resetar status das palestras
UPDATE upcoming_announcements SET is_active = 1 WHERE is_active = 2;
```

## Notas Importantes

1. **UUID**: O ID gerado é um UUID v4 válido
2. **Thumbnail**: Usa o `image_path` da palestra como thumbnail
3. **Descrição**: Usa a descrição da palestra agendada
4. **Status**: `is_active = 2` marca como publicada
5. **Permissões**: Apenas admins podem adicionar
6. **Validação**: Todos os campos obrigatórios são validados

## Contato

Em caso de dúvidas ou problemas:
1. Verificar logs do PHP
2. Verificar console do navegador (F12)
3. Revisar este documento
4. Verificar estrutura do banco de dados

---

**Versão**: 1.0
**Data**: Novembro 2024
**Status**: ✅ Pronto para instalação
