-- =====================================================
-- SISTEMA DE FIDELIZACIÓN COMPLETO - CAFÉ-VT
-- Script SQL Unificado y Actualizado
-- Incluye: Tablas, Triggers, Vistas, Procedimientos, Sistema de Cupones
-- Base de datos: ethos_bd
-- =====================================================

-- =====================================================
-- 0. LIMPIAR ESTRUCTURA EXISTENTE
-- =====================================================

-- Eliminar vistas existentes
DROP VIEW IF EXISTS `v_loyalty_users_analysis`;
DROP VIEW IF EXISTS `v_loyalty_tier_stats`;
DROP VIEW IF EXISTS `v_loyalty_transactions_detail`;
DROP VIEW IF EXISTS `v_loyalty_active_coupons`;

-- Eliminar procedimientos existentes
DROP PROCEDURE IF EXISTS `sp_check_and_update_tiers`;
DROP PROCEDURE IF EXISTS `sp_generate_daily_analytics`;
DROP PROCEDURE IF EXISTS `sp_generate_birthday_coupon`;
DROP PROCEDURE IF EXISTS `sp_cleanup_expired_coupons`;

-- Eliminar funciones existentes
DROP FUNCTION IF EXISTS `fn_points_to_next_tier`;
DROP FUNCTION IF EXISTS `fn_progress_percentage`;

-- Eliminar triggers existentes
DROP TRIGGER IF EXISTS `after_loyalty_user_points_update`;
DROP TRIGGER IF EXISTS `after_compra_complete`;
DROP TRIGGER IF EXISTS `after_reward_redemption`;

-- Eliminar índices existentes (solo los que no están en foreign keys)
DROP INDEX IF EXISTS `idx_loyalty_users_tier` ON `loyalty_users`;
DROP INDEX IF EXISTS `idx_loyalty_users_points` ON `loyalty_users`;
DROP INDEX IF EXISTS `idx_loyalty_users_last_visit` ON `loyalty_users`;
DROP INDEX IF EXISTS `idx_loyalty_users_referral` ON `loyalty_users`;
DROP INDEX IF EXISTS `idx_loyalty_transactions_type` ON `loyalty_transactions`;
DROP INDEX IF EXISTS `idx_loyalty_transactions_date` ON `loyalty_transactions`;
DROP INDEX IF EXISTS `idx_loyalty_coupons_code` ON `loyalty_coupons`;
DROP INDEX IF EXISTS `idx_loyalty_coupons_valid` ON `loyalty_coupons`;
DROP INDEX IF EXISTS `idx_loyalty_notifications_type` ON `loyalty_notifications`;
DROP INDEX IF EXISTS `idx_loyalty_notifications_read` ON `loyalty_notifications`;

-- Eliminar tablas existentes (en orden inverso por dependencias)
DROP TABLE IF EXISTS `loyalty_user_analytics`;
DROP TABLE IF EXISTS `loyalty_notifications`;
DROP TABLE IF EXISTS `loyalty_tier_config`;
DROP TABLE IF EXISTS `loyalty_tier_history`;
DROP TABLE IF EXISTS `loyalty_redemptions`;
DROP TABLE IF EXISTS `loyalty_coupons`;
DROP TABLE IF EXISTS `loyalty_rewards`;
DROP TABLE IF EXISTS `loyalty_transactions`;
DROP TABLE IF EXISTS `loyalty_users`;

-- =====================================================
-- 1. TABLAS PRINCIPALES
-- =====================================================

-- Tabla principal de usuarios de fidelización
CREATE TABLE IF NOT EXISTS `loyalty_users` (
  `user_id` INT(6) PRIMARY KEY,
  `total_points` INT DEFAULT 0,
  `current_tier` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') DEFAULT 'cafe_bronze',
  `score` DECIMAL(10,2) DEFAULT 0.00,
  `join_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_visit` TIMESTAMP NULL,
  `total_visits` INT DEFAULT 0,
  `total_spent` DECIMAL(10,2) DEFAULT 0.00,
  `favorite_products` TEXT,
  `referral_code` VARCHAR(20) UNIQUE,
  `referred_by` VARCHAR(20) NULL,
  `points_expiry_date` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Usuario`(`usuario_ID`) ON DELETE CASCADE
);

-- Tabla de transacciones de puntos
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(6) NOT NULL,
  `transaction_type` ENUM('earn', 'redeem', 'expire', 'referral', 'bonus', 'adjustment') NOT NULL,
  `points_amount` INT NOT NULL,
  `order_id` INT NULL,
  `description` TEXT,
  `balance_before` INT NOT NULL,
  `balance_after` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `Compra`(`compra_ID`) ON DELETE SET NULL
);

-- Tabla de recompensas disponibles
CREATE TABLE IF NOT EXISTS `loyalty_rewards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `points_cost` INT NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL,
  `tier_required` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') DEFAULT 'cafe_bronze',
  `max_uses_per_user` INT DEFAULT 1,
  `active` BOOLEAN DEFAULT TRUE,
  `expiry_date` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de cupones de descuento
CREATE TABLE IF NOT EXISTS `loyalty_coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(6) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `max_uses` INT DEFAULT 1,
  `used_count` INT DEFAULT 0,
  `valid_from` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `valid_until` TIMESTAMP NULL,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE
);

-- Tabla de canjes de recompensas
CREATE TABLE IF NOT EXISTS `loyalty_redemptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(6) NOT NULL,
  `reward_id` INT NOT NULL,
  `points_spent` INT NOT NULL,
  `order_id` INT NULL,
  `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `Compra`(`compra_ID`) ON DELETE SET NULL
);

-- Tabla de historial de cambios de rango
CREATE TABLE IF NOT EXISTS `loyalty_tier_history` (
  `history_ID` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `old_tier` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') NOT NULL,
  `new_tier` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') NOT NULL,
  `points_at_change` INT NOT NULL,
  `score_at_change` DECIMAL(10,2) NOT NULL,
  `change_reason` ENUM('points_threshold', 'manual_adjustment', 'system_correction') DEFAULT 'points_threshold',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE
);

-- =====================================================
-- 2. TABLAS DE CONFIGURACIÓN Y ANÁLISIS
-- =====================================================

-- Tabla de configuración de rangos
CREATE TABLE IF NOT EXISTS `loyalty_tier_config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tier_name` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') NOT NULL,
  `display_name` VARCHAR(50) NOT NULL,
  `points_required` INT NOT NULL,
  `points_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.00,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `free_coffees_per_month` INT NOT NULL DEFAULT 0,
  `priority_access` BOOLEAN NOT NULL DEFAULT FALSE,
  `exclusive_rewards` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_tier` (`tier_name`)
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS `loyalty_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(6) NOT NULL,
  `notification_type` ENUM('points_earned', 'level_up', 'points_expiry', 'reward_available', 'birthday', 'inactive_reminder') NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `data` JSON NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE
);

-- Tabla de análisis de usuarios
CREATE TABLE IF NOT EXISTS `loyalty_user_analytics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(6) NOT NULL,
  `analysis_date` DATE NOT NULL,
  `current_tier` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') NOT NULL,
  `total_points` INT NOT NULL,
  `points_to_next_tier` INT NOT NULL,
  `progress_percentage` DECIMAL(5,2) NOT NULL,
  `total_visits` INT NOT NULL,
  `total_spent` DECIMAL(10,2) NOT NULL,
  `avg_order_value` DECIMAL(10,2) NOT NULL,
  `last_visit_days` INT NOT NULL,
  `churn_risk_score` DECIMAL(3,2) NOT NULL,
  `engagement_score` DECIMAL(3,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_date` (`user_id`, `analysis_date`)
);

-- =====================================================
-- 3. DATOS INICIALES
-- =====================================================

-- Insertar configuración de rangos con nuevos umbrales
INSERT IGNORE INTO `loyalty_tier_config` 
(`tier_name`, `display_name`, `points_required`, `points_multiplier`, `discount_percent`, `free_coffees_per_month`, `priority_access`, `exclusive_rewards`) VALUES
('cafe_bronze', 'Café Bronze', 0, 1.00, 5.00, 0, FALSE, FALSE),
('cafe_plata', 'Café Plata', 5000, 1.20, 10.00, 1, TRUE, FALSE),
('cafe_oro', 'Café Oro', 25000, 1.50, 15.00, 2, TRUE, TRUE),
('cafe_diamante', 'Café Diamante', 75000, 2.00, 20.00, 3, TRUE, TRUE);

-- Insertar recompensas con costos actualizados para CLP
INSERT IGNORE INTO `loyalty_rewards` (`id`, `name`, `description`, `points_cost`, `discount_percent`, `tier_required`, `max_uses_per_user`) VALUES
(1, 'Café Americano Gratis', 'Café americano gratis para empezar el día (valor: $1.500 CLP)', 1500, 100.00, 'cafe_bronze', 1),
(2, '10% Descuento en tu Próximo Café', '10% de descuento en tu próxima compra (máximo $5.000 CLP)', 1000, 10.00, 'cafe_bronze', 3),
(3, 'Cappuccino Especial Gratis', 'Cappuccino con arte latte gratis (valor: $2.000 CLP)', 2000, 100.00, 'cafe_plata', 2),
(4, '20% Descuento en Menú Completo', '20% de descuento en tu próxima compra (máximo $10.000 CLP)', 5000, 20.00, 'cafe_oro', 2),
(5, 'Experiencia Café-VT Completa', 'Café + postre + snack gratis (valor: $8.000 CLP)', 8000, 100.00, 'cafe_diamante', 1),
(6, 'Descuento 25% VIP', '25% de descuento en compras (máximo $15.000 CLP)', 7500, 25.00, 'cafe_oro', 1),
(7, 'Experiencia Premium', 'Café gourmet + postre premium + snack (valor: $12.000 CLP)', 12000, 100.00, 'cafe_diamante', 1),
(8, 'Pack Familiar', '4 cafés + 2 postres + 2 snacks (valor: $20.000 CLP)', 20000, 100.00, 'cafe_diamante', 1),
(9, 'Descuento 30% Diamante', '30% de descuento en compras (máximo $25.000 CLP)', 15000, 30.00, 'cafe_diamante', 2);

-- =====================================================
-- 4. VISTAS
-- =====================================================

-- Vista de resumen de usuarios con análisis completo
DROP VIEW IF EXISTS `v_loyalty_users_analysis`;
CREATE VIEW `v_loyalty_users_analysis` AS
SELECT 
    u.usuario_ID,
    u.nombre,
    u.apellidos,
    u.correo,
    lu.current_tier,
    lu.total_points,
    lu.total_visits,
    lu.total_spent,
    lu.join_date,
    lu.last_visit,
    tc.points_required as tier_threshold,
    tc.points_multiplier,
    tc.discount_percent,
    tc.free_coffees_per_month,
    tc.priority_access,
    tc.exclusive_rewards,
    CASE 
        WHEN lu.current_tier = 'cafe_bronze' THEN 5000 - lu.total_points
        WHEN lu.current_tier = 'cafe_plata' THEN 25000 - lu.total_points
        WHEN lu.current_tier = 'cafe_oro' THEN 75000 - lu.total_points
        ELSE 0
    END as points_to_next_tier,
    CASE 
        WHEN lu.current_tier = 'cafe_bronze' THEN ROUND((lu.total_points / 5000) * 100, 2)
        WHEN lu.current_tier = 'cafe_plata' THEN ROUND(((lu.total_points - 5000) / 20000) * 100, 2)
        WHEN lu.current_tier = 'cafe_oro' THEN ROUND(((lu.total_points - 25000) / 50000) * 100, 2)
        ELSE 100.00
    END as progress_percentage,
    DATEDIFF(CURRENT_DATE, lu.last_visit) as days_since_last_visit
FROM `Usuario` u
LEFT JOIN `loyalty_users` lu ON u.usuario_ID = lu.user_id
LEFT JOIN `loyalty_tier_config` tc ON lu.current_tier = tc.tier_name;

-- Vista de estadísticas por rango
DROP VIEW IF EXISTS `v_loyalty_tier_stats`;
CREATE VIEW `v_loyalty_tier_stats` AS
SELECT 
    lu.current_tier,
    tc.display_name,
    COUNT(*) as user_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM loyalty_users), 2) as percentage,
    AVG(lu.total_points) as avg_points,
    AVG(lu.total_spent) as avg_spent,
    AVG(lu.total_visits) as avg_visits,
    SUM(lu.total_points) as total_points_issued,
    SUM(lu.total_spent) as total_revenue
FROM `loyalty_users` lu
LEFT JOIN `loyalty_tier_config` tc ON lu.current_tier = tc.tier_name
GROUP BY lu.current_tier, tc.display_name
ORDER BY tc.points_required;

-- Vista de transacciones detalladas
DROP VIEW IF EXISTS `v_loyalty_transactions_detail`;
CREATE VIEW `v_loyalty_transactions_detail` AS
SELECT 
    lt.id,
    u.nombre,
    u.apellidos,
    lt.transaction_type,
    lt.points_amount,
    lt.description,
    lt.created_at
FROM `loyalty_transactions` lt
JOIN `loyalty_users` lu ON lt.user_id = lu.user_id
JOIN `Usuario` u ON lu.user_id = u.usuario_ID;

-- Vista de cupones activos
DROP VIEW IF EXISTS `v_loyalty_active_coupons`;
CREATE VIEW `v_loyalty_active_coupons` AS
SELECT 
    lc.id,
    u.nombre,
    u.apellidos,
    lc.code,
    lc.discount_type,
    lc.discount_value,
    lc.min_order_amount,
    lc.max_uses,
    lc.used_count,
    lc.valid_from,
    lc.valid_until,
    lc.active
FROM `loyalty_coupons` lc
JOIN `loyalty_users` lu ON lc.user_id = lu.user_id
JOIN `Usuario` u ON lu.user_id = u.usuario_ID
WHERE lc.active = TRUE 
AND (lc.valid_until IS NULL OR lc.valid_until > CURRENT_TIMESTAMP)
AND lc.used_count < lc.max_uses;

-- =====================================================
-- 5. TRIGGERS
-- =====================================================

-- Trigger para ganar puntos por compras completadas
DROP TRIGGER IF EXISTS `after_compra_complete`;
DELIMITER //
CREATE TRIGGER `after_compra_complete`
AFTER UPDATE ON `Compra`
FOR EACH ROW
BEGIN
    DECLARE loyalty_user_exists INT;
    DECLARE points_to_add INT;
    DECLARE current_balance INT;
    DECLARE tier_multiplier DECIMAL(3,2);
    
    IF NEW.estado = 'completado' AND OLD.estado != 'completado' THEN
        SELECT COUNT(*) INTO loyalty_user_exists FROM `loyalty_users` WHERE `user_id` = NEW.usuario_ID;
        
        IF loyalty_user_exists > 0 THEN
            SELECT 
                CASE `current_tier`
                    WHEN 'cafe_diamante' THEN 2.0
                    WHEN 'cafe_oro' THEN 1.5
                    WHEN 'cafe_plata' THEN 1.2
                    ELSE 1.0
                END INTO tier_multiplier
            FROM `loyalty_users` WHERE `user_id` = NEW.usuario_ID;
            
            -- 1 punto por cada 100 unidades monetarias
            SET points_to_add = FLOOR((NEW.total / 100) * tier_multiplier);
            
            SELECT `total_points` INTO current_balance FROM `loyalty_users` WHERE `user_id` = NEW.usuario_ID;

            INSERT INTO `loyalty_transactions` 
            (`user_id`, `transaction_type`, `points_amount`, `order_id`, `description`, `balance_before`, `balance_after`)
            VALUES (
                NEW.usuario_ID,
                'earn',
                points_to_add,
                NEW.compra_ID,
                CONCAT('Puntos ganados por compra #', NEW.compra_ID, ' (', points_to_add, ' pts)'),
                current_balance,
                current_balance + points_to_add
            );
            
            UPDATE `loyalty_users` 
            SET 
                `total_points` = `total_points` + points_to_add,
                `total_visits` = `total_visits` + 1,
                `total_spent` = `total_spent` + NEW.total,
                `last_visit` = CURRENT_TIMESTAMP
            WHERE `user_id` = NEW.usuario_ID;
        END IF;
    END IF;
END //
DELIMITER ;

-- Trigger para generar cupón al canjear recompensa
DROP TRIGGER IF EXISTS `after_reward_redemption`;
DELIMITER //
CREATE TRIGGER `after_reward_redemption`
AFTER INSERT ON `loyalty_redemptions`
FOR EACH ROW
BEGIN
    DECLARE reward_discount DECIMAL(5,2);
    DECLARE reward_name VARCHAR(100);
    DECLARE coupon_code VARCHAR(20);
    
    -- Obtener información de la recompensa
    SELECT discount_percent, name INTO reward_discount, reward_name
    FROM loyalty_rewards WHERE id = NEW.reward_id;
    
    -- Generar código único para el cupón
    SET coupon_code = CONCAT('REWARD', NEW.id, '_', FLOOR(RAND() * 1000));
    
    -- Si la recompensa tiene descuento, crear cupón
    IF reward_discount > 0 THEN
        INSERT INTO loyalty_coupons 
        (user_id, code, discount_type, discount_value, min_order_amount, max_uses, valid_until)
        VALUES (
            NEW.user_id,
            coupon_code,
            'percentage',
            reward_discount,
            0.00,
            1,
            DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
        );
    END IF;
END //
DELIMITER ;

-- =====================================================
-- 6. PROCEDIMIENTOS ALMACENADOS
-- =====================================================

-- Procedimiento para verificar y actualizar rangos
DELIMITER //
CREATE PROCEDURE `sp_check_and_update_tiers`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE user_id_var INT;
    DECLARE current_tier_var VARCHAR(20);
    DECLARE total_points_var INT;
    DECLARE new_tier_var VARCHAR(20);
    
    DECLARE user_cursor CURSOR FOR 
        SELECT user_id, current_tier, total_points 
        FROM loyalty_users;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN user_cursor;
    
    read_loop: LOOP
        FETCH user_cursor INTO user_id_var, current_tier_var, total_points_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Determinar nuevo rango
        SET new_tier_var = CASE 
            WHEN total_points_var >= 75000 THEN 'cafe_diamante'
            WHEN total_points_var >= 25000 THEN 'cafe_oro'
            WHEN total_points_var >= 5000 THEN 'cafe_plata'
            ELSE 'cafe_bronze'
        END;
        
        -- Actualizar si es diferente
        IF new_tier_var != current_tier_var THEN
            UPDATE loyalty_users 
            SET current_tier = new_tier_var, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = user_id_var;
            
            -- Registrar en historial
            INSERT INTO loyalty_tier_history 
            (user_id, old_tier, new_tier, points_at_change, score_at_change)
            VALUES (user_id_var, current_tier_var, new_tier_var, total_points_var, 0);
        END IF;
    END LOOP;
    
    CLOSE user_cursor;
END //
DELIMITER ;

-- Procedimiento para generar análisis diario
DELIMITER //
CREATE PROCEDURE `sp_generate_daily_analytics`()
BEGIN
    INSERT IGNORE INTO loyalty_user_analytics 
    (user_id, analysis_date, current_tier, total_points, points_to_next_tier, 
     progress_percentage, total_visits, total_spent, avg_order_value, 
     last_visit_days, churn_risk_score, engagement_score)
    SELECT 
        lu.user_id,
        CURRENT_DATE,
        lu.current_tier,
        lu.total_points,
        CASE 
            WHEN lu.current_tier = 'cafe_bronze' THEN 5000 - lu.total_points
            WHEN lu.current_tier = 'cafe_plata' THEN 25000 - lu.total_points
            WHEN lu.current_tier = 'cafe_oro' THEN 75000 - lu.total_points
            ELSE 0
        END as points_to_next_tier,
        CASE 
            WHEN lu.current_tier = 'cafe_bronze' THEN ROUND((lu.total_points / 5000) * 100, 2)
            WHEN lu.current_tier = 'cafe_plata' THEN ROUND(((lu.total_points - 5000) / 20000) * 100, 2)
            WHEN lu.current_tier = 'cafe_oro' THEN ROUND(((lu.total_points - 25000) / 50000) * 100, 2)
            ELSE 100.00
        END as progress_percentage,
        lu.total_visits,
        lu.total_spent,
        CASE WHEN lu.total_visits > 0 THEN lu.total_spent / lu.total_visits ELSE 0 END as avg_order_value,
        DATEDIFF(CURRENT_DATE, lu.last_visit) as last_visit_days,
        CASE 
            WHEN DATEDIFF(CURRENT_DATE, lu.last_visit) > 90 THEN 0.9
            WHEN DATEDIFF(CURRENT_DATE, lu.last_visit) > 60 THEN 0.7
            WHEN DATEDIFF(CURRENT_DATE, lu.last_visit) > 30 THEN 0.5
            ELSE 0.1
        END as churn_risk_score,
        CASE 
            WHEN lu.total_visits >= 10 AND lu.total_spent >= 1000 THEN 0.9
            WHEN lu.total_visits >= 5 AND lu.total_spent >= 500 THEN 0.7
            WHEN lu.total_visits >= 2 AND lu.total_spent >= 200 THEN 0.5
            ELSE 0.3
        END as engagement_score
    FROM loyalty_users lu;
END //
DELIMITER ;

-- Procedimiento para generar cupón de cumpleaños
DELIMITER //
CREATE PROCEDURE `sp_generate_birthday_coupon`(IN user_id_param INT)
BEGIN
    DECLARE coupon_code VARCHAR(20);
    DECLARE user_tier VARCHAR(20);
    DECLARE discount_value DECIMAL(10,2);
    
    -- Obtener rango del usuario
    SELECT current_tier INTO user_tier FROM loyalty_users WHERE user_id = user_id_param;
    
    -- Determinar descuento según rango
    SET discount_value = CASE 
        WHEN user_tier = 'cafe_diamante' THEN 25.00
        WHEN user_tier = 'cafe_oro' THEN 20.00
        WHEN user_tier = 'cafe_plata' THEN 15.00
        ELSE 10.00
    END;
    
    -- Generar código único
    SET coupon_code = CONCAT('BDAY', user_id_param, '_', FLOOR(RAND() * 1000));
    
    -- Crear cupón
    INSERT INTO loyalty_coupons 
    (user_id, code, discount_type, discount_value, min_order_amount, max_uses, valid_until)
    VALUES (
        user_id_param,
        coupon_code,
        'percentage',
        discount_value,
        0.00,
        1,
        DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)
    );
    
    -- Crear notificación
    INSERT INTO loyalty_notifications 
    (user_id, notification_type, title, message, is_read)
    VALUES (
        user_id_param,
        'birthday',
        '¡Feliz Cumpleaños!',
        CONCAT('¡Feliz cumpleaños! Te hemos regalado un cupón del ', discount_value, '% de descuento. Código: ', coupon_code),
        FALSE
    );
END //
DELIMITER ;

-- Procedimiento para limpiar cupones expirados
DELIMITER //
CREATE PROCEDURE `sp_cleanup_expired_coupons`()
BEGIN
    UPDATE loyalty_coupons 
    SET active = FALSE 
    WHERE valid_until < CURRENT_TIMESTAMP AND active = TRUE;
END //
DELIMITER ;

-- =====================================================
-- 7. FUNCIONES
-- =====================================================

-- Función para calcular puntos hasta el siguiente rango
DELIMITER //
CREATE FUNCTION `fn_points_to_next_tier`(current_points INT, current_tier VARCHAR(20))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE points_needed INT;
    
    SET points_needed = CASE 
        WHEN current_tier = 'cafe_bronze' THEN 5000 - current_points
        WHEN current_tier = 'cafe_plata' THEN 25000 - current_points
        WHEN current_tier = 'cafe_oro' THEN 75000 - current_points
        ELSE 0
    END;
    
    RETURN GREATEST(points_needed, 0);
END //
DELIMITER ;

-- Función para calcular porcentaje de progreso
DELIMITER //
CREATE FUNCTION `fn_progress_percentage`(current_points INT, current_tier VARCHAR(20))
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE progress DECIMAL(5,2);
    
    SET progress = CASE 
        WHEN current_tier = 'cafe_bronze' THEN ROUND((current_points / 5000) * 100, 2)
        WHEN current_tier = 'cafe_plata' THEN ROUND(((current_points - 5000) / 20000) * 100, 2)
        WHEN current_tier = 'cafe_oro' THEN ROUND(((current_points - 25000) / 50000) * 100, 2)
        ELSE 100.00
    END;
    
    RETURN LEAST(progress, 100.00);
END //
DELIMITER ;

-- =====================================================
-- 8. ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices para loyalty_users
CREATE INDEX idx_loyalty_users_tier ON loyalty_users(current_tier);
CREATE INDEX idx_loyalty_users_points ON loyalty_users(total_points);
CREATE INDEX idx_loyalty_users_last_visit ON loyalty_users(last_visit);
CREATE INDEX idx_loyalty_users_referral ON loyalty_users(referral_code);

-- Índices para loyalty_transactions (user_id ya tiene índice por foreign key)
CREATE INDEX idx_loyalty_transactions_type ON loyalty_transactions(transaction_type);
CREATE INDEX idx_loyalty_transactions_date ON loyalty_transactions(created_at);
CREATE INDEX idx_loyalty_transactions_order ON loyalty_transactions(order_id);

-- Índices para loyalty_coupons (user_id ya tiene índice por foreign key)
CREATE INDEX idx_loyalty_coupons_code ON loyalty_coupons(code);
CREATE INDEX idx_loyalty_coupons_valid ON loyalty_coupons(valid_until, active);

-- Índices para loyalty_notifications (user_id ya tiene índice por foreign key)
CREATE INDEX idx_loyalty_notifications_type ON loyalty_notifications(notification_type);
CREATE INDEX idx_loyalty_notifications_read ON loyalty_notifications(is_read, sent_at);

-- =====================================================
-- 9. EJECUTAR ACTUALIZACIONES INICIALES
-- =====================================================

-- Actualizar rangos de usuarios existentes
CALL sp_check_and_update_tiers();

-- Generar análisis inicial
CALL sp_generate_daily_analytics();

-- Limpiar cupones expirados
CALL sp_cleanup_expired_coupons();

-- =====================================================
-- 10. VERIFICACIÓN FINAL
-- =====================================================

-- Mostrar estadísticas actualizadas
SELECT 
    current_tier,
    COUNT(*) as user_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM loyalty_users), 2) as percentage
FROM loyalty_users 
GROUP BY current_tier 
ORDER BY 
    CASE current_tier
        WHEN 'cafe_bronze' THEN 1
        WHEN 'cafe_plata' THEN 2
        WHEN 'cafe_oro' THEN 3
        WHEN 'cafe_diamante' THEN 4
    END;

-- Mostrar configuración de rangos
SELECT * FROM loyalty_tier_config ORDER BY points_required;

-- Mostrar recompensas disponibles
SELECT name, points_cost, tier_required, discount_percent FROM loyalty_rewards ORDER BY points_cost;

-- =====================================================
-- FIN DEL SCRIPT
-- ===================================================== 