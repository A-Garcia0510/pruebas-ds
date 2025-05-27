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
DELIMITER //
CREATE TRIGGER `before_order_recipe_insert`
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
END //
DELIMITER ;

-- Trigger para verificar stock antes de crear un pedido directo
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
DELIMITER //
CREATE TRIGGER `after_order_recipe_insert`
AFTER INSERT ON `custom_coffee_orders`
FOR EACH ROW
BEGIN
    IF NEW.receta_ID IS NOT NULL THEN
        UPDATE custom_coffee_components c
        INNER JOIN custom_coffee_recipe_details d ON c.componente_ID = d.componente_ID
        SET c.stock = c.stock - d.cantidad
        WHERE d.receta_ID = NEW.receta_ID;
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar stock después de crear un pedido directo
DELIMITER //
CREATE TRIGGER `after_order_direct_insert`
AFTER INSERT ON `custom_coffee_order_details`
FOR EACH ROW
BEGIN
    UPDATE custom_coffee_components
    SET stock = stock - NEW.cantidad
    WHERE componente_ID = NEW.componente_ID
    AND stock >= NEW.cantidad;
END //
DELIMITER ;

-- Trigger para verificar pedidos activos antes de eliminar una receta
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

-- Datos iniciales para componentes de café
INSERT INTO `custom_coffee_components` (`nombre`, `tipo`, `precio`, `stock`, `descripcion`) VALUES
-- Bases
('Espresso', 'base', 1500, 100, 'Café espresso puro'),
('Americano', 'base', 1800, 100, 'Espresso con agua caliente'),
('Cappuccino', 'base', 2000, 100, 'Espresso con leche espumada'),
-- Leches
('Leche Entera', 'leche', 500, 100, 'Leche de vaca entera'),
('Leche Descremada', 'leche', 500, 100, 'Leche de vaca descremada'),
('Leche de Almendras', 'leche', 800, 50, 'Leche vegetal de almendras'),
-- Endulzantes
('Azúcar', 'endulzante', 0, 1000, 'Azúcar blanca'),
('Stevia', 'endulzante', 200, 100, 'Endulzante natural'),
('Miel', 'endulzante', 300, 50, 'Miel de abeja natural'),
-- Toppings
('Crema Batida', 'topping', 500, 50, 'Crema batida fresca'),
('Chocolate', 'topping', 400, 100, 'Polvo de chocolate'),
('Canela', 'topping', 200, 100, 'Polvo de canela'); 