<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'], $data['nombre_cliente'], $data['telefono'], $data['direccion'], $data['total'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    $pedido_id = $data['id'];
    $nombre_cliente = $data['nombre_cliente'];
    $telefono = $data['telefono'];
    $direccion = $data['direccion'];
    $total = $data['total'];

    try {
        $stmt = $conn->prepare("UPDATE pedidos SET nombre_cliente = ?, telefono = ?, direccion = ?, total = ? WHERE id = ?");
        $stmt->execute([$nombre_cliente, $telefono, $direccion, $total, $pedido_id]);

        echo json_encode(['success' => true, 'message' => 'Pedido actualizado con éxito!']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el pedido: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?> 