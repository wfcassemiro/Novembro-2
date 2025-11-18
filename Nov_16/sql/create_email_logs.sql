-- Criação da tabela email_logs para o sistema de emails
-- Database: u335416710_t101_db

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `recipient_count` int(11) NOT NULL DEFAULT 0,
  `recipient_type` enum('all','subscribers','non_subscribers','selected') DEFAULT 'all',
  `sent_by` varchar(36) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'sent',
  `lecture_id` int(11) DEFAULT NULL COMMENT 'ID da palestra relacionada, se houver',
  `access_link` varchar(500) DEFAULT NULL COMMENT 'Link de acesso enviado no email',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sent_by` (`sent_by`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_lecture_id` (`lecture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
