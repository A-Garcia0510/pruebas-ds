-- =====================================================
-- SISTEMA DE FIDELIZACIÓN CAFÉ-VT - VERSIÓN FINAL
-- =====================================================
-- Base de datos: ethos_bd
-- Versión: 1.0 Final
-- Fecha: 2025-06-22
-- Descripción: Sistema básico de fidelización con solo las tablas necesarias
-- =====================================================

-- =====================================================
-- 1. DESACTIVAR VERIFICACIONES DE FOREIGN KEYS
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 2. LIMPIEZA DE ESTRUCTURA EXISTENTE
-- =====================================================

-- Eliminar vistas
DROP VIEW IF EXISTS `v_loyalty_users_analysis`;
DROP VIEW IF EXISTS `v_loyalty_tier_stats`;
DROP VIEW IF EXISTS `v_loyalty_transactions_detail`;

-- Eliminar triggers
DROP TRIGGER IF EXISTS `after_compra_complete`;

-- =====================================================
-- 3. TABLAS ESENCIALES (SOLO LAS QUE SE USAN)
-- =====================================================

-- Tabla principal de usuarios de fidelización
CREATE TABLE IF NOT EXISTS `loyalty_users` (
  `user_id` INT(6) PRIMARY KEY COMMENT 'ID del usuario (FK a Usuario.usuario_ID)',
  `total_points` INT DEFAULT 0 COMMENT 'Puntos totales acumulados',
  `current_tier` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') DEFAULT 'cafe_bronze' COMMENT 'Nivel actual del usuario',
  `join_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro en el programa',
  `last_visit` TIMESTAMP NULL COMMENT 'Última visita del usuario',
  `total_visits` INT DEFAULT 0 COMMENT 'Total de visitas realizadas',
  `total_spent` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total gastado en CLP',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  FOREIGN KEY (`user_id`) REFERENCES `Usuario`(`usuario_ID`) ON DELETE CASCADE
) COMMENT='Tabla principal de usuarios del programa de fidelización';

-- Tabla de configuración de rangos/niveles
CREATE TABLE IF NOT EXISTS `loyalty_tier_config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único de configuración',
  `tier_name` ENUM('cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante') NOT NULL COMMENT 'Nombre del nivel',
  `display_name` VARCHAR(50) NOT NULL COMMENT 'Nombre para mostrar',
  `points_required` INT NOT NULL COMMENT 'Puntos requeridos para alcanzar este nivel',
  `points_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.00 COMMENT 'Multiplicador de puntos para este nivel',
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de descuento base',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de actualización',
  UNIQUE KEY `unique_tier` (`tier_name`)
) COMMENT='Configuración de niveles del programa de fidelización';

-- Tabla de transacciones de puntos
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único de transacción',
  `user_id` INT(6) NOT NULL COMMENT 'ID del usuario',
  `transaction_type` ENUM('earn', 'redeem', 'expire', 'bonus', 'adjustment') NOT NULL COMMENT 'Tipo de transacción',
  `points_amount` INT NOT NULL COMMENT 'Cantidad de puntos (positivo para ganar, negativo para gastar)',
  `order_id` INT NULL COMMENT 'ID de la orden relacionada (FK a Compra.compra_ID)',
  `description` TEXT COMMENT 'Descripción de la transacción',
  `balance_before` INT NOT NULL COMMENT 'Balance antes de la transacción',
  `balance_after` INT NOT NULL COMMENT 'Balance después de la transacción',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la transacción',
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `Compra`(`compra_ID`) ON DELETE SET NULL
) COMMENT='Registro de todas las transacciones de puntos del sistema';

-- Tabla de recompensas disponibles
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

-- Tabla de canjes de recompensas
CREATE TABLE IF NOT EXISTS `loyalty_redemptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único de canje',
  `user_id` INT(6) NOT NULL COMMENT 'ID del usuario que canjeó',
  `reward_id` INT NOT NULL COMMENT 'ID de la recompensa canjeada',
  `points_spent` INT NOT NULL COMMENT 'Puntos gastados en el canje',
  `order_id` INT NULL COMMENT 'ID de la orden donde se usó (FK a Compra.compra_ID)',
  `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del canje',
  FOREIGN KEY (`user_id`) REFERENCES `loyalty_users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `Compra`(`compra_ID`) ON DELETE SET NULL
) COMMENT='Registro de canjes de recompensas realizados por los usuarios';

-- =====================================================
-- 4. DATOS INICIALES (ACTUALIZABLES)
-- =====================================================

-- Configuración de rangos (REPLACE para evitar duplicados)
REPLACE INTO `loyalty_tier_config` 
(`tier_name`, `display_name`, `points_required`, `points_multiplier`, `discount_percent`) VALUES
('cafe_bronze', 'Café Bronze', 0, 1.00, 5.00),
('cafe_plata', 'Café Plata', 5000, 1.20, 10.00),
('cafe_oro', 'Café Oro', 25000, 1.50, 15.00),
('cafe_diamante', 'Café Diamante', 75000, 2.00, 20.00);

-- Recompensas disponibles (REPLACE para evitar duplicados)
REPLACE INTO `loyalty_rewards` (`id`, `name`, `description`, `points_cost`, `discount_percent`, `tier_required`, `max_uses_per_user`) VALUES
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
-- 5. TRIGGER PARA GANAR PUNTOS
-- =====================================================

-- Trigger para ganar puntos por compras completadas
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `after_compra_complete`
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

-- =====================================================
-- 6. ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices para loyalty_users
CREATE INDEX IF NOT EXISTS idx_loyalty_users_tier ON loyalty_users(current_tier);
CREATE INDEX IF NOT EXISTS idx_loyalty_users_points ON loyalty_users(total_points);
CREATE INDEX IF NOT EXISTS idx_loyalty_users_last_visit ON loyalty_users(last_visit);

-- Índices para loyalty_transactions
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_type ON loyalty_transactions(transaction_type);
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_date ON loyalty_transactions(created_at);
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_order ON loyalty_transactions(order_id);

-- Índices para loyalty_rewards
CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_tier_required ON loyalty_rewards(tier_required);
CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_active ON loyalty_rewards(active);

-- Índices para loyalty_redemptions
CREATE INDEX IF NOT EXISTS idx_loyalty_redemptions_user_id ON loyalty_redemptions(user_id);
CREATE INDEX IF NOT EXISTS idx_loyalty_redemptions_reward_id ON loyalty_redemptions(reward_id);
CREATE INDEX IF NOT EXISTS idx_loyalty_redemptions_date ON loyalty_redemptions(redeemed_at);

-- =====================================================
-- 7. REACTIVAR VERIFICACIONES DE FOREIGN KEYS
-- =====================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 8. VERIFICACIÓN FINAL
-- =====================================================

-- Mostrar estadísticas de las tablas creadas
SELECT 
    TABLE_NAME as 'Tabla',
    TABLE_ROWS as 'Registros',
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Tamaño (MB)'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'ethos_bd' 
AND TABLE_NAME LIKE 'loyalty_%'
ORDER BY TABLE_NAME;

-- Mostrar configuración de rangos
SELECT * FROM loyalty_tier_config ORDER BY points_required;

-- Mostrar recompensas disponibles
SELECT name, points_cost, tier_required, discount_percent FROM loyalty_rewards ORDER BY points_cost;

-- =====================================================
-- FIN DEL SCRIPT FINAL
-- ===================================================== 