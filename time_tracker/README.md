# Time Tracker - Sistema de Rastreamento de Tempo

Sistema integrado de rastreamento de tempo para tradutores, desenvolvido para o site Translators 101.

## 📁 Estrutura de Arquivos

```
time_tracker/
├── time-tracker.php           # Interface principal do usuário
├── api_time_tracker.php       # API backend (REST)
├── includes/
│   └── auth_check.php        # Verificação de autenticação
├── config/
│   ├── database.php          # Configuração do banco de dados
│   ├── dash_database.php     # Configuração adicional (compatibilidade)
│   └── dash_functions.php    # Funções auxiliares do dashboard
├── vision/assets/js/
│   └── time-tracker-v2.js    # JavaScript frontend
└── sql/
    └── create_time_tracker_tables.sql  # Script de criação das tabelas
```

## 🚀 Instalação

### Passo 1: Criar as Tabelas no Banco de Dados

Execute o arquivo SQL para criar as tabelas necessárias:

```bash
mysql -u u335416710_t101 -p u335416710_t101_db < sql/create_time_tracker_tables.sql
```

Ou importe manualmente via phpMyAdmin:
1. Acesse phpMyAdmin
2. Selecione o banco `u335416710_t101_db`
3. Vá em "Importar"
4. Selecione o arquivo `sql/create_time_tracker_tables.sql`
5. Clique em "Executar"

### Passo 2: Upload dos Arquivos

Faça upload dos arquivos para o servidor seguindo esta estrutura:

```
/v/dash-t101/
├── time-tracker.php
├── api_time_tracker.php
├── includes/
│   └── auth_check.php
└── config/
    ├── database.php
    ├── dash_database.php
    └── dash_functions.php

/vision/assets/js/
└── time-tracker-v2.js
```

**IMPORTANTE:** 
- Os arquivos PHP devem estar em `/dash-t101/` no servidor
- O JavaScript deve estar em `/vision/assets/js/`
- Mantenha a estrutura exata de pastas

### Passo 3: Configurar Permissões

```bash
chmod 644 time-tracker.php
chmod 644 api_time_tracker.php
chmod 644 includes/auth_check.php
chmod 644 config/*.php
chmod 644 /vision/assets/js/time-tracker-v2.js
```

### Passo 4: Verificar Credenciais do Banco

Edite o arquivo `config/database.php` e verifique as credenciais:

```php
$host = 'localhost';
$db   = 'u335416710_t101_db';
$user = 'u335416710_t101';
$pass = 'Pa392ap!';  // ← Verifique se está correto
```

### Passo 5: Testar a Instalação

1. Acesse: `https://v.translators101.com/dash-t101/time-tracker.php`
2. Verifique se a página carrega sem erros
3. Abra o console do navegador (F12)
4. Verifique se há erros de JavaScript

## 🧪 Testando Funcionalidades

### Teste 1: API de Projetos
```bash
curl -X GET "https://v.translators101.com/dash-t101/api_time_tracker.php?action=project_list" \
  --cookie "sua_sessao_aqui"
```

### Teste 2: Criar Projeto Rápido
1. Clique no botão "+" ao lado do seletor de projetos
2. Preencha o nome do projeto
3. Clique em "Criar e Selecionar"
4. Verifique se o projeto aparece na lista

### Teste 3: Iniciar/Parar Timer
1. Selecione um projeto
2. Digite uma descrição
3. Clique em "Iniciar"
4. Aguarde alguns segundos
5. Clique em "Parar"
6. Verifique se o registro aparece no histórico

## 🐛 Resolução de Problemas

### Problema: "Usuário não autenticado"
**Solução:** Certifique-se de estar logado no sistema. A API requer uma sessão ativa.

### Problema: "Erro ao carregar projetos"
**Solução:** 
1. Verifique se as tabelas foram criadas corretamente
2. Verifique as credenciais do banco de dados em `config/database.php`
3. Verifique os logs de erro do PHP: `/var/log/php_errors.log`

### Problema: Erro 404 no JavaScript
**Solução:** 
1. Verifique se o arquivo `time-tracker-v2.js` está em `/vision/assets/js/`
2. Limpe o cache do navegador (Ctrl+Shift+R)
3. Verifique as permissões do arquivo

### Problema: Modal não abre
**Solução:** 
1. Abra o console do navegador (F12)
2. Verifique se há erros de JavaScript
3. Verifique se o arquivo `time-tracker-v2.js` está carregando
4. Limpe o cache do CDN/Cloudflare

## 📊 Estrutura do Banco de Dados

### Tabela: time_tasks
Armazena as tarefas dentro dos projetos.

```sql
CREATE TABLE time_tasks (
  id VARCHAR(36) PRIMARY KEY,
  project_id INT(11) NOT NULL,
  user_id VARCHAR(36) NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Tabela: time_entries
Armazena os registros de tempo rastreados.

```sql
CREATE TABLE time_entries (
  id VARCHAR(36) PRIMARY KEY,
  user_id VARCHAR(36) NOT NULL,
  project_id INT(11),
  task_id VARCHAR(36),
  description TEXT,
  start_time DATETIME NOT NULL,
  end_time DATETIME,
  duration INT(11) DEFAULT 0 COMMENT 'Duração em segundos',
  is_running TINYINT(1) DEFAULT 0,
  paused_at DATETIME,
  paused_duration INT(11) DEFAULT 0 COMMENT 'Tempo total pausado em segundos',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🔧 Configuração Avançada

### Desabilitar Autenticação (Apenas para Testes)

No arquivo `time-tracker.php`, comente a linha:

```php
// requireAuth();  // ← Comentado para testes
```

**ATENÇÃO:** Nunca faça isso em produção!

### Habilitar Logs de Debug

No arquivo `api_time_tracker.php`, os logs já estão ativados. Para visualizar:

```bash
tail -f /var/log/php_errors.log
```

## 📝 Notas Importantes

1. **Integração com dash_projects:** O Time Tracker usa a tabela `dash_projects` existente. A coluna `title` é mapeada como `name` nos projetos.

2. **UUID vs Auto-increment:** Tarefas e registros usam UUIDs (VARCHAR 36) para compatibilidade e escalabilidade.

3. **Sessões:** O sistema usa sessões PHP para autenticação. Certifique-se de que as sessões estejam configuradas corretamente.

4. **Cache:** O arquivo JavaScript tem cache busting automático (`?v=timestamp`). Isso força o navegador a carregar a versão mais recente.

## 🎯 Próximos Passos

Após a instalação bem-sucedida:

1. **Criar CSS:** Adicione ou ajuste o arquivo `/vision/assets/css/time-tracker.css` para estilização
2. **Relatórios:** Implemente a página `report_time_tracker.php` para visualização de relatórios
3. **Exportação:** Adicione funcionalidade de exportação para CSV/PDF
4. **Notificações:** Implemente toast notifications em vez de alerts

## 📞 Suporte

Para problemas ou dúvidas:
- Verifique os logs do servidor
- Inspecione o console do navegador
- Revise este README
- Entre em contato com o desenvolvedor

---

**Versão:** 2.0  
**Data:** 20/11/2024  
**Desenvolvido para:** Translators 101 (v.translators101.com)
