-- Arquivo: moedas_exemplo.sql
-- Script para popular moedas no dash_settings

-- Este script adiciona as moedas mais comuns no sistema
-- Substitua 'SEU_USER_ID' pelo user_id real do usuário

-- Moedas sugeridas (taxas de conversão em relação ao BRL)
-- Valores de exemplo - atualize conforme necessário

-- USD - Dólar Americano
INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES ('SEU_USER_ID', 'rate_usd', '5.20', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = '5.20', updated_at = NOW();

-- EUR - Euro
INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES ('SEU_USER_ID', 'rate_eur', '5.60', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = '5.60', updated_at = NOW();

-- CAD - Dólar Canadense
INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES ('SEU_USER_ID', 'rate_cad', '3.80', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = '3.80', updated_at = NOW();

-- GBP - Libra Esterlina
INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES ('SEU_USER_ID', 'rate_gbp', '6.50', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = '6.50', updated_at = NOW();

-- ARS - Peso Argentino
INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES ('SEU_USER_ID', 'rate_ars', '0.0055', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = '0.0055', updated_at = NOW();

-- Nota: BRL não precisa ser adicionado pois é a moeda base (taxa = 1.0)

-- Para verificar as moedas cadastradas:
-- SELECT * FROM dash_settings WHERE setting_key LIKE 'rate_%';

-- Para adicionar novas moedas, siga o padrão:
-- setting_key = 'rate_' + código_moeda_minúsculo
-- setting_value = taxa de conversão em relação ao BRL
--
-- Exemplo para adicionar Yen Japonês (JPY):
-- INSERT INTO dash_settings (user_id, setting_key, setting_value, created_at, updated_at)
-- VALUES ('SEU_USER_ID', 'rate_jpy', '0.035', NOW(), NOW())
-- ON DUPLICATE KEY UPDATE setting_value = '0.035', updated_at = NOW();
