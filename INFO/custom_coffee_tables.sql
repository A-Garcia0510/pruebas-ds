-- Tablas para el sistema de Cafés Personalizados
-- Creado: [Fecha actual]

-- Tabla de componentes para cafés personalizados
CREATE TABLE `custom_coffee_components` (
    `componente_ID` INT(7) AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `tipo` ENUM('base', 'leche', 'endulzante', 'topping') NOT NULL,
    `precio` DECIMAL(10,2) NOT NULL,
    `stock` INT(3) DEFAULT 0,
    `descripcion` VARCHAR(100),
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`componente_ID`),
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recetas de café personalizadas
CREATE TABLE `custom_coffee_recipes` (
    `receta_ID` INT(7) AUTO_INCREMENT,
    `usuario_ID` INT(6) NOT NULL,
    `nombre` VARCHAR(50) NOT NULL,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `precio_total` DECIMAL(10,2) NOT NULL,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `es_favorita` BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`receta_ID`),
    FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`) ON DELETE CASCADE,
    INDEX `idx_usuario` (`usuario_ID`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalles de recetas
CREATE TABLE `custom_coffee_recipe_details` (
    `detalle_ID` INT(7) AUTO_INCREMENT,
    `receta_ID` INT(7) NOT NULL,
    `componente_ID` INT(7) NOT NULL,
    `cantidad` INT(2) NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `orden` INT(2) NOT NULL COMMENT 'Orden de agregado de los componentes',
    PRIMARY KEY (`detalle_ID`),
    FOREIGN KEY (`receta_ID`) REFERENCES `custom_coffee_recipes`(`receta_ID`) ON DELETE CASCADE,
    FOREIGN KEY (`componente_ID`) REFERENCES `custom_coffee_components`(`componente_ID`),
    INDEX `idx_receta` (`receta_ID`),
    INDEX `idx_componente` (`componente_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de órdenes de café personalizado
CREATE TABLE `custom_coffee_orders` (
    `orden_ID` INT(7) AUTO_INCREMENT,
    `usuario_ID` INT(6) NOT NULL,
    `receta_ID` INT(7) NULL,
    `fecha_pedido` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado` ENUM('pendiente', 'preparando', 'completado', 'cancelado') DEFAULT 'pendiente',
    `precio_total` DECIMAL(10,2) NOT NULL,
    `notas` VARCHAR(200),
    PRIMARY KEY (`orden_ID`),
    FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`),
    FOREIGN KEY (`receta_ID`) REFERENCES `custom_coffee_recipes`(`receta_ID`),
    INDEX `idx_usuario` (`usuario_ID`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_fecha` (`fecha_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalles de órdenes de café personalizado
CREATE TABLE `custom_coffee_order_details` (
    `detalle_ID` INT(7) AUTO_INCREMENT,
    `orden_ID` INT(7) NOT NULL,
    `componente_ID` INT(7) NOT NULL,
    `cantidad` INT(2) NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`detalle_ID`),
    FOREIGN KEY (`orden_ID`) REFERENCES `custom_coffee_orders`(`orden_ID`) ON DELETE CASCADE,
    FOREIGN KEY (`componente_ID`) REFERENCES `custom_coffee_components`(`componente_ID`),
    INDEX `idx_orden` (`orden_ID`),
    INDEX `idx_componente` (`componente_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modificación de la tabla Detalle_Compra para soportar cafés personalizados
ALTER TABLE `Detalle_Compra`
ADD COLUMN `es_cafe_personalizado` BOOLEAN DEFAULT FALSE,
ADD COLUMN `orden_cafe_ID` INT(7) NULL,
ADD FOREIGN KEY (`orden_cafe_ID`) REFERENCES `custom_coffee_orders`(`orden_ID`) ON DELETE SET NULL;

-- Datos iniciales para componentes de café
INSERT INTO `custom_coffee_components` (`nombre`, `tipo`, `precio`, `stock`, `descripcion`) VALUES
-- Bases
('Espresso', 'base', 1500.00, 100, 'Café espresso puro'),
('Americano', 'base', 1800.00, 100, 'Espresso con agua caliente'),
('Cappuccino', 'base', 2000.00, 100, 'Espresso con leche espumada'),
-- Leches
('Leche Entera', 'leche', 500.00, 100, 'Leche de vaca entera'),
('Leche Descremada', 'leche', 500.00, 100, 'Leche de vaca descremada'),
('Leche de Almendras', 'leche', 800.00, 50, 'Leche vegetal de almendras'),
-- Endulzantes
('Azúcar', 'endulzante', 0.00, 1000, 'Azúcar blanca'),
('Stevia', 'endulzante', 200.00, 100, 'Endulzante natural'),
('Miel', 'endulzante', 300.00, 50, 'Miel de abeja natural'),
-- Toppings
('Crema Batida', 'topping', 500.00, 50, 'Crema batida fresca'),
('Chocolate', 'topping', 400.00, 100, 'Polvo de chocolate'),
('Canela', 'topping', 200.00, 100, 'Polvo de canela');

-- Crear trigger para actualizar stock de componentes
DELIMITER //
CREATE TRIGGER after_custom_coffee_order
AFTER INSERT ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    UPDATE custom_coffee_components c
    INNER JOIN custom_coffee_recipe_details d ON c.componente_ID = d.componente_ID
    SET c.stock = c.stock - d.cantidad
    WHERE d.receta_ID = NEW.receta_ID;
END//
DELIMITER ;

-- Crear trigger para validar stock antes de ordenar
DELIMITER //
CREATE TRIGGER before_custom_coffee_order
BEFORE INSERT ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    DECLARE stock_insuficiente BOOLEAN;
    
    SELECT EXISTS (
        SELECT 1
        FROM custom_coffee_components c
        INNER JOIN custom_coffee_recipe_details d ON c.componente_ID = d.componente_ID
        WHERE d.receta_ID = NEW.receta_ID
        AND c.stock < d.cantidad
    ) INTO stock_insuficiente;
    
    IF stock_insuficiente THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para uno o más componentes';
    END IF;
END//
DELIMITER ; 