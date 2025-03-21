-- Crear la tabla Usuario
CREATE TABLE `Usuario` (
  `usuario_ID` INT(6) AUTO_INCREMENT,
  `nombre` VARCHAR(50),
  `apellidos` VARCHAR(50),
  `correo` VARCHAR(50),
  `contraseña` VARCHAR(255), 
  `ROL` ENUM("Estudiante", "Profesor", "Empleado"),
  PRIMARY KEY (`usuario_ID`)
);

-- Crear la tabla Compra
CREATE TABLE `Compra` (
  `compra_ID` INT(7) AUTO_INCREMENT,
  `usuario_ID` INT(6),
  `fecha_compra` DATETIME,
  `total` DECIMAL(10,2),
  PRIMARY KEY (`compra_ID`),
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`)
);

-- Crear la tabla Producto
CREATE TABLE `Producto` (
  `producto_ID` INT(7) AUTO_INCREMENT,
  `nombre_producto` VARCHAR(50),
  `descripcion` VARCHAR(100),
  `categoria` VARCHAR(100),
  `precio` DECIMAL(10,2), 
  `cantidad` INT(3),
  PRIMARY KEY (`producto_ID`)
);

-- Crear la tabla Detalle_Compra
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

-- Crear la nueva tabla Carro
CREATE TABLE `Carro` (
  `carro_ID` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_ID` INT NOT NULL,
  `producto_ID` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `fecha_agregado` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_ID`) REFERENCES `Usuario`(`usuario_ID`),
  FOREIGN KEY (`producto_ID`) REFERENCES `Producto`(`producto_ID`)
);
