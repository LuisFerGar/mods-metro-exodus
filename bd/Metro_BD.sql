CREATE TABLE CLIENTE (
    NOMBRE varchar(100) NOT NULL,
    CORREO varchar(100) NOT NULL,
    USUARIO VARCHAR(50) NOT NULL,
    CONTRA VARCHAR(128) NOT NULL,
    -- Nuevas columnas agregadas durante el proyecto:
    FOTO_PERFIL VARCHAR(255) DEFAULT NULL,
    ROL VARCHAR(20) NOT NULL DEFAULT 'usuario', -- Para diferenciar Admin de Usuario
    PRIMARY KEY (USUARIO)
);

-- Insertar Usuarios de Prueba (Incluyendo tu Admin)
INSERT INTO CLIENTE (NOMBRE, CORREO, USUARIO, CONTRA, ROL) VALUES
('Pedro Pascal', 'pedro@gmail.com', 'PED123', 'QWERTY123456', 'usuario'),
('Carlos Oliveira', 'carlos@gmail.com', 'CAR123', 'QWERTY123456', 'usuario'),
('Artyom Chyornyj', 'artyom@metro.com', 'anton123', 'anton123', 'admin'); -- ESTE ES EL ADMIN

-- --------------------------------------------------------

-- 3. TABLA DE PRODUCTOS (Mods y Suministros)
CREATE TABLE PRODUCTOS (
    COD_PROD INT UNSIGNED NOT NULL AUTO_INCREMENT,
    NOMBRE_PRODUCTO varchar(100) NOT NULL,
    PRECIO DECIMAL(10,2) NOT NULL,
    -- Nuevas columnas para evitar el "Diccionario PHP":
    DESCRIPCION TEXT NULL,
    IMAGEN VARCHAR(255) NOT NULL DEFAULT 'img/placeholder.jpg',
    CATEGORIA VARCHAR(50) NOT NULL DEFAULT 'Suministros',
    PRIMARY KEY (COD_PROD)
);

-- Insertar los Productos con sus datos reales
INSERT INTO PRODUCTOS (COD_PROD, NOMBRE_PRODUCTO, PRECIO, DESCRIPCION, IMAGEN, CATEGORIA) VALUES
(1, 'Kalash "Ranger"', 150.00, 'El fusil de asalto estándar del Metro. Esta versión "Ranger" ha sido modificada con piezas de alta calidad recuperadas de un búnker militar. Ofrece mayor cadencia de fuego.', 'img/mods/kalash.jpg', 'Armas'),
(2, 'Traje Anti-Rad MK3', 120.00, 'Protección superior diseñada para los altos niveles de radiación del Mar Caspio. Incluye visión nocturna de tercera generación y filtros de aire reforzados.', 'img/mods/traje.jpg', 'Trajes'),
(3, 'Ballesta "Helsing"', 80.00, 'Silenciosa y letal. Perfecta para infiltraciones en bases de bandidos sin ser detectado. Sus virotes pueden recuperarse de los cadáveres.', 'img/mods/helsing.jpg', 'Armas');

-- --------------------------------------------------------

-- 4. TABLA DE COMPRAS (Historial)
CREATE TABLE COMPRA (
    ID_COMPRA INT UNSIGNED NOT NULL AUTO_INCREMENT,
    FECHA DATE NOT NULL,
    USUARIO VARCHAR(50) NOT NULL, 
    COD_PROD INT UNSIGNED NOT NULL,
    PRIMARY KEY(ID_COMPRA),
    -- Relaciones con actualización en cascada (Si cambias el nombre de usuario, se actualiza aquí)
    FOREIGN KEY (USUARIO) REFERENCES CLIENTE(USUARIO) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (COD_PROD) REFERENCES PRODUCTOS(COD_PROD) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- --------------------------------------------------------

-- 5. TABLA DE LISTA DE DESEOS (Wishlist) - NUEVA
CREATE TABLE LISTA_DESEOS (
    ID_DESEO INT UNSIGNED NOT NULL AUTO_INCREMENT,
    USUARIO VARCHAR(50) NOT NULL,
    COD_PROD INT UNSIGNED NOT NULL,
    FECHA_AGREGADO DATE DEFAULT CURRENT_DATE,
    PRIMARY KEY (ID_DESEO),
    FOREIGN KEY (USUARIO) REFERENCES CLIENTE(USUARIO) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (COD_PROD) REFERENCES PRODUCTOS(COD_PROD) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Evitar duplicados (Un usuario no puede desear lo mismo 2 veces)
    UNIQUE KEY (USUARIO, COD_PROD)
);

-- --------------------------------------------------------

-- 6. TABLA DE MENSAJES (Contacto) - NUEVA
CREATE TABLE MENSAJES (
    ID_MENSAJE INT UNSIGNED NOT NULL AUTO_INCREMENT,
    NOMBRE_REMITENTE VARCHAR(100),
    EMAIL_REMITENTE VARCHAR(100),
    TIPO_SOLICITUD VARCHAR(50),
    MENSAJE TEXT NOT NULL,
    FECHA DATETIME DEFAULT CURRENT_TIMESTAMP,
    LEIDO BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (ID_MENSAJE)
);

-- 1. Crear la nueva tabla de Categorías
CREATE TABLE CATEGORIAS (
    ID_CATEGORIA INT UNSIGNED NOT NULL AUTO_INCREMENT,
    NOMBRE_CATEGORIA VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (ID_CATEGORIA)
);

-- 2. Insertar tus categorías base
INSERT INTO CATEGORIAS (NOMBRE_CATEGORIA) VALUES 
('Armas'), 
('Trajes'), 
('Suministros');

-- 3. Crear una nueva columna en PRODUCTOS para el ID de la categoría
ALTER TABLE PRODUCTOS ADD COLUMN ID_CATEGORIA INT UNSIGNED DEFAULT 3;

-- 4. Actualizar los productos existentes para enlazarlos con sus IDs correctos
UPDATE PRODUCTOS SET ID_CATEGORIA = 1 WHERE CATEGORIA = 'Armas';
UPDATE PRODUCTOS SET ID_CATEGORIA = 2 WHERE CATEGORIA = 'Trajes';
UPDATE PRODUCTOS SET ID_CATEGORIA = 3 WHERE CATEGORIA = 'Suministros';

-- 5. Crear la Relación (Llave Foránea) para que estén conectadas
ALTER TABLE PRODUCTOS ADD CONSTRAINT fk_categoria 
FOREIGN KEY (ID_CATEGORIA) REFERENCES CATEGORIAS(ID_CATEGORIA) 
ON UPDATE CASCADE ON DELETE RESTRICT;

-- 6. Borrar la columna vieja de texto (Ya no la necesitamos)
ALTER TABLE PRODUCTOS DROP COLUMN CATEGORIA;