<?php
header('Content-Type: application/json');
require_once 'config/database.php';

if (!isset($_GET['pedido_id'])) {
    die(json_encode(['error' => 'No se proporcionó ID de pedido']));
}

$pedido_id = intval($_GET['pedido_id']);

try {
    // Obtener datos del pedido
    $stmt = $conn->prepare("
        SELECT p.*, 
               COALESCE(p.nombre_cliente, 'Sin nombre') as nombre_cliente,
               COALESCE(p.telefono, 'Sin teléfono') as telefono,
               COALESCE(p.direccion, 'Sin dirección') as direccion,
               COALESCE(p.estado, 'pendiente') as estado,
               COALESCE(p.notas, '') as notas
        FROM pedidos p 
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die(json_encode(['error' => 'Pedido no encontrado']));
    }

    // Obtener detalles del pedido
    $stmt = $conn->prepare("
        SELECT dp.*, 
               pr.nombre as producto_nombre,
               pr.precio
        FROM detalle_pedido dp
        JOIN productos pr ON dp.producto_id = pr.id
        WHERE dp.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode([
        'pedido' => $pedido,
        'detalles' => $detalles
    ]);

} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage()]));
}
?> 