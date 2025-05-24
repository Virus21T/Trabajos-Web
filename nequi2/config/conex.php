<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "nequi";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear tabla de transacciones si no existe
$create_transactions_table = "CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origen_id INT NOT NULL,
    destino_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (origen_id) REFERENCES usuarios(id),
    FOREIGN KEY (destino_id) REFERENCES usuarios(id)
)";

if (!$conn->query($create_transactions_table)) {
    die("Error al crear la tabla de transacciones: " . $conn->error);
}
?>
