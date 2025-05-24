<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'nequi';
$username = 'root';
$password = '';

try {
    // Crear conexión usando mysqli
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Establecer charset a utf8
    $conn->set_charset("utf8");

} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}
?> 