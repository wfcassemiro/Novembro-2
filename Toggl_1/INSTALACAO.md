# 📦 Instalação do Time Tracker - Passo a Passo

## 🎯 Configuração Atual

✅ **Database.php atualizado** com suas credenciais corretas:
- Host: `localhost`
- Database: `u335416710_t101_db`
- Usuário: `u335416710_t101`
- Senha: `Pa392ap!`

✅ **Todas as funções de autenticação** do seu site já integradas

---

## 🚀 PASSO 1: Criar Tabelas no Banco de Dados

### Opção A: Via phpMyAdmin (RECOMENDADO)

1. **Acesse seu phpMyAdmin**
2. **Selecione o banco:** `u335416710_t101_db`
3. **Clique na aba "SQL"**
4. **Cole e execute este SQL:**

```sql
-- ========================================
-- TIME TRACKER - SCHEMA
-- Database: u335416710_t101_db
-- ========================================

-- Tabela de Projetos do Time Tracker
CREATE TABLE IF NOT EXISTS `time_projects` (
  `id` varchar(36) NOT NULL PRIMARY KEY,
  `user_id` varchar(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `color` varchar(7) DEFAULT '#7B61FF',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_time_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Tarefas
CREATE TABLE IF NOT EXISTS `time_tasks` (
  `id` varchar(36) NOT NULL PRIMARY KEY,
  `project_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_time_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `time_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Registros de Tempo
CREATE TABLE IF NOT EXISTS `time_entries` (
  `id` varchar(36) NOT NULL PRIMARY KEY,
  `user_id` varchar(36) NOT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `task_id` varchar(36) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0 COMMENT 'Duração em segundos',
  `is_running` tinyint(1) DEFAULT 0,
  `paused_at` datetime DEFAULT NULL,
  `paused_duration` int(11) DEFAULT 0 COMMENT 'Tempo total pausado em segundos',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_is_running` (`is_running`),
  CONSTRAINT `fk_time_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_entries_project` FOREIGN KEY (`project_id`) REFERENCES `time_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_time_entries_task` FOREIGN KEY (`task_id`) REFERENCES `time_tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais para performance
CREATE INDEX idx_user_date ON time_entries(user_id, start_time);
CREATE INDEX idx_project_date ON time_entries(project_id, start_time);
```

5. **Clique em "Executar"**

### Opção B: Via Linha de Comando

```bash
mysql -u u335416710_t101 -p u335416710_t101_db < /app/Toggl_1/sql/schema.sql
# Senha: Pa392ap!
```

---

## ✅ PASSO 2: Verificar Instalação

Execute este SQL para verificar se as tabelas foram criadas:

```sql
SHOW TABLES LIKE 'time_%';
```

**Você deve ver:**
- `time_projects`
- `time_tasks`
- `time_entries`

---

## 🌐 PASSO 3: Acessar o Sistema

### URL de Acesso:
```
http://seu-dominio.com/Toggl_1/time-tracker.php
```

Ou se estiver em localhost:
```
http://localhost/Toggl_1/time-tracker.php
```

### Requisitos:
- ✅ Estar logado no site
- ✅ Ser usuário **subscriber** (ou admin)

---

## 🔧 Estrutura de Pastas Criada

```
/app/Toggl_1/
├── time-tracker.php          # Interface principal
├── api.php                   # API AJAX
├── INSTALACAO.md            # Este arquivo
│
├── sql/
│   └── schema.sql           # SQL das tabelas
│
├── config/
│   └── database.php         # ✅ ATUALIZADO com suas credenciais
│
├── includes/
│   ├── functions.php        # Funções auxiliares
│   └── auth_check.php       # Verificação de autenticação
│
└── assets/
    ├── css/
    │   └── time-tracker.css # Estilos Apple Vision
    └── js/
        └── time-tracker.js  # JavaScript completo
```

---

## 📋 Checklist de Instalação

- [ ] **SQL executado** no banco `u335416710_t101_db`
- [ ] **Tabelas verificadas** (time_projects, time_tasks, time_entries)
- [ ] **Logado como subscriber** no site
- [ ] **Acessado:** `/Toggl_1/time-tracker.php`
- [ ] **Testado:** Criar projeto
- [ ] **Testado:** Iniciar cronômetro

---

## 🎨 Funcionalidades Disponíveis

### ⏱️ Cronômetro
- Iniciar, pausar, retomar e parar
- Associar a projetos e tarefas
- Descrição personalizada

### 📁 Projetos
- Criar com cores personalizadas
- Editar nome e cor
- Ver estatísticas (tarefas, registros, tempo total)
- Deletar (soft delete)

### ✅ Tarefas
- Criar tarefas dentro de projetos
- Associar tempo a tarefas específicas
- Deletar tarefas

### 📊 Histórico
- Ver todos os registros de tempo
- Filtrar por projeto
- Deletar registros

---

## 🚨 Solução de Problemas

### Erro: "Access denied"
**Causa:** Credenciais incorretas no database.php  
**Solução:** Verificar usuário e senha no seu painel de hospedagem

### Erro: "Table doesn't exist"
**Causa:** SQL não foi executado  
**Solução:** Execute o SQL no phpMyAdmin

### Erro: "Not authenticated"
**Causa:** Não está logado ou não é subscriber  
**Solução:** Faça login como usuário subscriber

### Página em branco
**Causa:** Erro de PHP  
**Solução:** Verifique o log de erros do PHP

---

## 📞 Próximos Passos

Após instalar e testar:

1. ✅ **Criar alguns projetos**
2. ✅ **Adicionar tarefas**
3. ✅ **Testar o cronômetro**
4. ✅ **Verificar histórico**
5. ⏳ **Aguardar página de relatórios** (próxima implementação)

---

## 🎉 Pronto!

O sistema está configurado e pronto para uso!

**Acesse:** `/Toggl_1/time-tracker.php`

Se tudo funcionar, me avise para criar a **página de relatórios** com gráficos e exportação de dados! 📊📈
