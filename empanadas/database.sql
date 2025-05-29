CREATE DATABASE IF NOT EXISTS empanadas_db;
USE empanadas_db;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pdf_data LONGBLOB,
    pdf_nombre VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Insertar productos iniciales
INSERT INTO productos (nombre, descripcion, precio, stock) VALUES
('Empanada de Pescado', 'Deliciosa empanada rellena de pescado fresco con cebolla y especias', 2500, 100),
('Empanada de Lentejas', 'Empanada vegetariana rellena de lentejas y verduras', 2000, 100),
('Empanada de Maíz', 'Empanada dulce rellena de maíz tierno y queso', 3000, 100); 