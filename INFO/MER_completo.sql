-- Tablas existentes del MER
CREATE TABLE `Usuario` (
  `usuario_ID` INT(6) AUTO_INCREMENT,
  `nombre` VARCHAR(50),
  `apellidos` VARCHAR(50),
  `correo` VARCHAR(50),
  `contraseña` VARCHAR(255), 
  `ROL` ENUM("Estudiante", "Empleado", "Administrador"),
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` TIMESTAMP NULL,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  PRIMARY KEY (`usuario_ID`)
);

CREATE TABLE `Compra` (
  `compra_ID` INT(7) AUTO_INCREMENT,
  `usuario_ID` INT(6),
  `fecha_compra` DATETIME,
  `total` DECIMAL(10,2),
  `estado` ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
  PRIMARY KEY (`compra_ID`),
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

CREATE TABLE `Producto` (
  `producto_ID` INT(7) AUTO_INCREMENT,
  `nombre_producto` VARCHAR(50),
  `descripcion` VARCHAR(100),
  `categoria` VARCHAR(100),
  `precio` DECIMAL(10,2), 
  `cantidad` INT(3),
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  PRIMARY KEY (`producto_ID`)
);

CREATE TABLE `Detalle_Compra` (
  `detalle_compra_ID` INT(7) AUTO_INCREMENT,
  `compra_ID` INT(7),
  `producto_ID` INT(7),
  `cantidad` INT(5),
  `precio_unitario` DECIMAL(10,2),
  PRIMARY KEY (`detalle_compra_ID`),
  FOREIGN KEY (`compra_ID`) REFERENCES `Compra`(`compra_ID`),
  FOREIGN KEY (`producto_ID`) REFERENCES `Producto`(`producto_ID`)
);

CREATE TABLE `Carro` (
  `carro_ID` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_ID` INT NOT NULL,
  `producto_ID` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `fecha_agregado` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`),
  FOREIGN KEY (`producto_ID`) REFERENCES `Producto`(`producto_ID`)
);

-- Nuevas tablas para el sistema de café personalizado
CREATE TABLE `custom_coffee_components` (
  `componente_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `tipo` ENUM('base', 'leche', 'endulzante', 'topping') NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `unidad` VARCHAR(20) NOT NULL,
  `medida_sugerida` DECIMAL(10,2) NOT NULL COMMENT 'Medida sugerida en la unidad especificada',
  `descripcion` TEXT,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `custom_coffee_recipes` (
  `receta_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `usuario_ID` INT NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `precio_total` DECIMAL(10,2) NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

CREATE TABLE `custom_coffee_recipe_details` (
  `detalle_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `receta_ID` INT NOT NULL,
  `componente_ID` INT NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`receta_ID`) REFERENCES `custom_coffee_recipes`(`receta_ID`),
  FOREIGN KEY (`componente_ID`) REFERENCES `custom_coffee_components`(`componente_ID`)
);

CREATE TABLE `custom_coffee_orders` (
  `orden_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `usuario_ID` INT NOT NULL,
  `receta_ID` INT NULL,
  `fecha_pedido` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `precio_total` DECIMAL(10,2) NOT NULL,
  `estado` ENUM('pendiente', 'preparando', 'completado', 'cancelado') DEFAULT 'pendiente',
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`),
  FOREIGN KEY (`receta_ID`) REFERENCES `custom_coffee_recipes`(`receta_ID`) ON DELETE SET NULL
);

CREATE TABLE `custom_coffee_order_details` (
  `detalle_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `orden_ID` INT NOT NULL,
  `componente_ID` INT NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`orden_ID`) REFERENCES `custom_coffee_orders`(`orden_ID`),
  FOREIGN KEY (`componente_ID`) REFERENCES `custom_coffee_components`(`componente_ID`)
);

-- Nuevas tablas para el sistema de reseñas y moderación
CREATE TABLE `product_reviews` (
  `review_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `producto_ID` INT NOT NULL,
  `usuario_ID` INT NOT NULL,
  `contenido` TEXT NOT NULL,
  `calificacion` INT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
  `estado` ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`producto_ID`) REFERENCES `Producto`(`producto_ID`),
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

CREATE TABLE `review_ratings` (
  `rating_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `review_ID` INT NOT NULL,
  `calificacion` INT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
  `comentario` TEXT,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`review_ID`) REFERENCES `product_reviews`(`review_ID`)
);

CREATE TABLE `review_reports` (
  `reporte_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `review_ID` INT NOT NULL,
  `usuario_ID` INT NOT NULL,
  `razon` TEXT NOT NULL,
  `estado` ENUM('pendiente', 'resuelto', 'desestimado') DEFAULT 'pendiente',
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`review_ID`) REFERENCES `product_reviews`(`review_ID`),
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

CREATE TABLE `review_moderation_log` (
  `log_ID` INT PRIMARY KEY AUTO_INCREMENT,
  `review_ID` INT NOT NULL,
  `moderador_ID` INT NOT NULL,
  `accion` ENUM('aprobar', 'rechazar', 'reportar', 'desestimar') NOT NULL,
  `comentario` TEXT,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`review_ID`) REFERENCES `product_reviews`(`review_ID`),
  FOREIGN KEY (`moderador_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

-- Triggers

-- Trigger para actualizar el último acceso del usuario
DROP TRIGGER IF EXISTS `after_usuario_login`;
DELIMITER //
CREATE TRIGGER `after_usuario_login`
AFTER UPDATE ON `Usuario`
FOR EACH ROW
BEGIN
    IF NEW.ultimo_acceso IS NOT NULL THEN
        UPDATE `Usuario` 
        SET ultimo_acceso = CURRENT_TIMESTAMP 
        WHERE usuario_ID = NEW.usuario_ID;
    END IF;
END //
DELIMITER ;

-- Trigger para verificar stock antes de crear un pedido con receta
DROP TRIGGER IF EXISTS `before_order_recipe_insert`;
DELIMITER //
CREATE TRIGGER `before_order_recipe_insert`
BEFORE INSERT ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    DECLARE stock_insuficiente BOOLEAN;
    
    IF NEW.receta_ID IS NOT NULL THEN
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
    END IF;
END //
DELIMITER ;

-- Trigger para verificar stock antes de crear un pedido directo
DROP TRIGGER IF EXISTS `before_order_direct_insert`;
DELIMITER //
CREATE TRIGGER `before_order_direct_insert`
BEFORE INSERT ON `custom_coffee_order_details`
FOR EACH ROW
BEGIN
    DECLARE stock_actual INT;
    
    SELECT stock INTO stock_actual
    FROM custom_coffee_components
    WHERE componente_ID = NEW.componente_ID;
    
    IF stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para el componente';
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar stock después de crear un pedido con receta
DROP TRIGGER IF EXISTS `after_order_recipe_insert`;
DELIMITER //
CREATE TRIGGER `after_order_recipe_insert`
AFTER INSERT ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    IF NEW.receta_ID IS NOT NULL THEN
        -- Actualizar stock usando una subconsulta para obtener el stock actual
        UPDATE custom_coffee_components c
        INNER JOIN custom_coffee_recipe_details d ON c.componente_ID = d.componente_ID
        SET c.stock = (
            SELECT stock - d.cantidad 
            FROM custom_coffee_components 
            WHERE componente_ID = c.componente_ID
        )
        WHERE d.receta_ID = NEW.receta_ID;
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar stock después de crear un pedido directo
DROP TRIGGER IF EXISTS `after_order_direct_insert`;
DELIMITER //
CREATE TRIGGER `after_order_direct_insert`
AFTER INSERT ON `custom_coffee_order_details`
FOR EACH ROW
BEGIN
    DECLARE es_pedido_receta BOOLEAN;
    
    -- Verificar si el pedido usa una receta
    SELECT EXISTS (
        SELECT 1 FROM custom_coffee_orders 
        WHERE orden_ID = NEW.orden_ID 
        AND receta_ID IS NOT NULL
    ) INTO es_pedido_receta;
    
    -- Solo actualizar stock si NO es un pedido con receta
    IF NOT es_pedido_receta THEN
        UPDATE custom_coffee_components
        SET stock = stock - NEW.cantidad
        WHERE componente_ID = NEW.componente_ID
        AND stock >= NEW.cantidad;
    END IF;
END //
DELIMITER ;

-- Trigger para verificar pedidos activos antes de eliminar una receta
DROP TRIGGER IF EXISTS `before_recipe_delete`;
DELIMITER //
CREATE TRIGGER `before_recipe_delete`
BEFORE DELETE ON `custom_coffee_recipes`
FOR EACH ROW
BEGIN
    DECLARE pedidos_activos INT;
    
    SELECT COUNT(*) INTO pedidos_activos
    FROM custom_coffee_orders
    WHERE receta_ID = OLD.receta_ID 
    AND estado IN ('pendiente', 'preparando');
    
    IF pedidos_activos > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede eliminar la receta porque tiene pedidos activos';
    END IF;
END //
DELIMITER ;

-- Triggers para el sistema de reseñas

-- Trigger para actualizar fecha_modificacion en product_reviews
DROP TRIGGER IF EXISTS `after_review_update`;
DELIMITER //
CREATE TRIGGER `after_review_update`
AFTER UPDATE ON `product_reviews`
FOR EACH ROW
BEGIN
    IF NEW.estado != OLD.estado THEN
        UPDATE `product_reviews` 
        SET fecha_modificacion = CURRENT_TIMESTAMP 
        WHERE review_ID = NEW.review_ID;
    END IF;
END //
DELIMITER ;

-- Trigger para verificar que un usuario solo pueda calificar una reseña una vez
DROP TRIGGER IF EXISTS `before_rating_insert`;
DELIMITER //
CREATE TRIGGER `before_rating_insert`
BEFORE INSERT ON `review_ratings`
FOR EACH ROW
BEGIN
    DECLARE rating_exists INT;
    
    SELECT COUNT(*) INTO rating_exists
    FROM `review_ratings`
    WHERE review_ID = NEW.review_ID;
    
    IF rating_exists > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Esta reseña ya ha sido calificada';
    END IF;
END //
DELIMITER ;

-- Trigger para verificar que un usuario no pueda reportar su propia reseña
DROP TRIGGER IF EXISTS `before_report_insert`;
DELIMITER //
CREATE TRIGGER `before_report_insert`
BEFORE INSERT ON `review_reports`
FOR EACH ROW
BEGIN
    DECLARE review_author INT;
    
    SELECT usuario_ID INTO review_author
    FROM `product_reviews`
    WHERE review_ID = NEW.review_ID;
    
    IF review_author = NEW.usuario_ID THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No puedes reportar tu propia reseña';
    END IF;
END //
DELIMITER ;

-- Trigger para restaurar stock cuando se cancela un pedido
DROP TRIGGER IF EXISTS `after_order_cancel`;
DELIMITER //
CREATE TRIGGER `after_order_cancel`
AFTER UPDATE ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    IF NEW.estado = 'cancelado' AND OLD.estado != 'cancelado' THEN
        -- Si el pedido usa una receta, restaurar stock basado en la receta
        IF NEW.receta_ID IS NOT NULL THEN
            UPDATE custom_coffee_components c
            INNER JOIN custom_coffee_recipe_details d ON c.componente_ID = d.componente_ID
            SET c.stock = c.stock + d.cantidad
            WHERE d.receta_ID = NEW.receta_ID;
        -- Si es un pedido directo, restaurar stock basado en los detalles
        ELSE
            UPDATE custom_coffee_components c
            INNER JOIN custom_coffee_order_details d ON c.componente_ID = d.componente_ID
            SET c.stock = c.stock + d.cantidad
            WHERE d.orden_ID = NEW.orden_ID;
        END IF;
    END IF;
END //
DELIMITER ;

-- Datos iniciales para componentes de café con medidas sugeridas
INSERT INTO `custom_coffee_components` (`nombre`, `tipo`, `precio`, `stock`, `unidad`, `medida_sugerida`, `descripcion`) VALUES
-- Bases
('Espresso', 'base', 1500, 100, 'ml', 30.00, 'Café espresso puro - 30ml por taza'),
('Americano', 'base', 1800, 100, 'ml', 250.00, 'Espresso con agua caliente - 250ml por taza'),
('Cappuccino', 'base', 2000, 100, 'ml', 180.00, 'Espresso con leche espumada - 180ml por taza'),
-- Leches
('Leche Entera', 'leche', 500, 100, 'ml', 120.00, 'Leche de vaca entera - 120ml por taza'),
('Leche Descremada', 'leche', 500, 100, 'ml', 120.00, 'Leche de vaca descremada - 120ml por taza'),
('Leche de Almendras', 'leche', 800, 50, 'ml', 120.00, 'Leche vegetal de almendras - 120ml por taza'),
-- Endulzantes
('Azúcar', 'endulzante', 0, 1000, 'g', 10.00, 'Azúcar blanca - 10g por taza'),
('Stevia', 'endulzante', 200, 100, 'g', 2.00, 'Endulzante natural - 2g por taza'),
('Miel', 'endulzante', 300, 50, 'ml', 15.00, 'Miel de abeja natural - 15ml por taza'),
-- Toppings
('Crema Batida', 'topping', 500, 50, 'g', 30.00, 'Crema batida fresca - 30g por taza'),
('Chocolate', 'topping', 400, 100, 'g', 10.00, 'Polvo de chocolate - 10g por taza'),
('Canela', 'topping', 200, 100, 'g', 5.00, 'Polvo de canela - 5g por taza');


--esto si tienes datos anteriores OJOOOO
-- Agregar nuevas columnas para medidas
ALTER TABLE `custom_coffee_components`
ADD COLUMN `unidad` VARCHAR(20) NOT NULL DEFAULT 'ml' AFTER `stock`,
ADD COLUMN `medida_sugerida` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `unidad`;

-- Actualizar registros con las medidas correctas
UPDATE `custom_coffee_components` SET 
    `unidad` = 'ml',
    `medida_sugerida` = 30.00
WHERE `nombre` = 'Espresso' AND `tipo` = 'base';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'ml',
    `medida_sugerida` = 250.00
WHERE `nombre` = 'Americano' AND `tipo` = 'base';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'ml',
    `medida_sugerida` = 180.00
WHERE `nombre` = 'Cappuccino' AND `tipo` = 'base';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'ml',
    `medida_sugerida` = 120.00
WHERE `nombre` IN ('Leche Entera', 'Leche Descremada', 'Leche de Almendras') AND `tipo` = 'leche';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'g',
    `medida_sugerida` = 10.00
WHERE `nombre` = 'Azúcar' AND `tipo` = 'endulzante';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'g',
    `medida_sugerida` = 2.00
WHERE `nombre` = 'Stevia' AND `tipo` = 'endulzante';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'ml',
    `medida_sugerida` = 15.00
WHERE `nombre` = 'Miel' AND `tipo` = 'endulzante';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'g',
    `medida_sugerida` = 30.00
WHERE `nombre` = 'Crema Batida' AND `tipo` = 'topping';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'g',
    `medida_sugerida` = 10.00
WHERE `nombre` = 'Chocolate' AND `tipo` = 'topping';

UPDATE `custom_coffee_components` SET 
    `unidad` = 'g',
    `medida_sugerida` = 5.00
WHERE `nombre` = 'Canela' AND `tipo` = 'topping'; 