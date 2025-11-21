-- ========================================
-- Time Tracker - Criação de Tabelas
-- ========================================
-- Execute este arquivo APÓS importar dash_projects.sql
--
-- Este arquivo cria as tabelas necessárias para o Time Tracker:
-- 1. time_tasks - Tarefas dentro dos projetos
-- 2. time_entries - Registros de tempo rastreados
-- ========================================

-- Tabela: time_tasks
CREATE TABLE IF NOT EXISTS `time_tasks` (
  `id` varchar(36) NOT NULL PRIMARY KEY,
  `project_id` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  KEY `idx_project_id` (`project_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_active` (`is_active`),
  
  CONSTRAINT `fk_time_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `dash_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: time_entries
CREATE TABLE IF NOT EXISTS `time_entries` (
  `id` varchar(36) NOT NULL PRIMARY KEY,
  `user_id` varchar(36) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_id` varchar(36) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0 COMMENT 'Duração em segundos',
  `is_running` tinyint(1) DEFAULT 0,
  `paused_at` datetime DEFAULT NULL,
  `paused_duration` int(11) DEFAULT 0 COMMENT 'Tempo total pausado em segundos',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  KEY `idx_user_id` (`user_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_is_running` (`is_running`),
  KEY `idx_start_time` (`start_time`),
  
  CONSTRAINT `fk_time_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_entries_project` FOREIGN KEY (`project_id`) REFERENCES `dash_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_time_entries_task` FOREIGN KEY (`task_id`) REFERENCES `time_tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais para performance
CREATE INDEX `idx_time_entries_running_user` ON `time_entries` (`user_id`, `is_running`);
CREATE INDEX `idx_time_tasks_active_project` ON `time_tasks` (`project_id`, `is_active`);
