<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($producto) {
        echo json_encode(['stock' => intval($producto['stock'])]);
    } else {
        echo json_encode(['error' => 'Producto no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al verificar el stock']);
}
?> 