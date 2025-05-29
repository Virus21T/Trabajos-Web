<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer y decodificar los datos JSON enviados desde el frontend
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Validar que los datos esenciales están presentes
    if (!isset($data['nombre_cliente'], $data['telefono'], $data['direccion'], $data['items']) || !is_array($data['items']) || empty($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Datos de pedido incompletos o formato incorrecto.']);
        exit;
    }

    $nombre_cliente = $data['nombre_cliente'];
    $telefono = $data['telefono'];
    $direccion = $data['direccion'];
    $items = $data['items'];
    $total_pedido = 0;

    try {
        $conn->beginTransaction();

        // Validar y calcular el total basado en los precios de la base de datos
        $valid_items = [];
        foreach ($items as $item) {
            if (!isset($item['id'], $item['cantidad']) || !is_numeric($item['id']) || !is_numeric($item['cantidad']) || $item['cantidad'] <= 0) {
                throw new Exception('Formato de ítems del pedido inválido.');
            }

            // Obtener precio del producto desde la base de datos para evitar manipulaciones en el frontend
            $stmt = $conn->prepare("SELECT id, precio, stock FROM productos WHERE id = ?");
            $stmt->execute([$item['id']]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception('Producto con ID ' . $item['id'] . ' no encontrado.');
            }

            if ($producto['stock'] < $item['cantidad']) {
                 throw new Exception('Stock insuficiente para el producto ' . htmlspecialchars($producto['nombre']) . '. Stock disponible: ' . $producto['stock']);
            }

            $subtotal = $producto['precio'] * $item['cantidad'];
            $total_pedido += $subtotal;

            // Guardar información validada para la inserción
            $valid_items[] = [
                'producto_id' => $producto['id'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $producto['precio']
            ];
        }

        // Insertar pedido principal
        $stmt = $conn->prepare("INSERT INTO pedidos (nombre_cliente, telefono, direccion, total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre_cliente, $telefono, $direccion, $total_pedido]);
        $pedido_id = $conn->lastInsertId();

        // Insertar detalles del pedido y actualizar stock
        $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

        foreach ($valid_items as $item) {
            $stmt_detalle->execute([
                $pedido_id,
                $item['producto_id'],
                $item['cantidad'],
                $item['precio_unitario']
            ]);

            $stmt_stock->execute([$item['cantidad'], $item['producto_id']]);
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pedido realizado con éxito',
            'pedido_id' => $pedido_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        // Log the error for debugging (optional, but recommended)
        // error_log("Error processing order: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar el pedido: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?> 