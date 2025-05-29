<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
        exit;
    }

    $pedido_id = $data['id'];

    try {
        $conn->beginTransaction();

        // First, delete related entries in detalle_pedido
        $stmt = $conn->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);

        // Then, delete the order from pedidos
        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Pedido eliminado con éxito!']);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el pedido: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?> 