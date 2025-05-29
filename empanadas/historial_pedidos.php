<?php
require_once 'config/database.php';

// Procesar eliminación de pedido
if (isset($_POST['eliminar_pedido'])) {
    $pedido_id = intval($_POST['pedido_id']);
    try {
        // Primero eliminar los detalles del pedido
        $stmt = $conn->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);
        
        // Luego eliminar el pedido
        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
        
        $mensaje = "Pedido eliminado correctamente";
    } catch (Exception $e) {
        $error = "Error al eliminar el pedido: " . $e->getMessage();
    }
}

// Procesar actualización de pedido
if (isset($_POST['actualizar_pedido'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $nombre_cliente = $_POST['nombre_cliente'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    
    try {
        $conn->beginTransaction();
        
        // Actualizar datos del pedido
        $stmt = $conn->prepare("UPDATE pedidos SET nombre_cliente = ?, telefono = ?, direccion = ? WHERE id = ?");
        $stmt->execute([$nombre_cliente, $telefono, $direccion, $pedido_id]);
        
        // Actualizar detalles de empanadas
        if (isset($_POST['productos']) && is_array($_POST['productos'])) {
            // Primero eliminar los detalles existentes
            $stmt = $conn->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
            $stmt->execute([$pedido_id]);
            
            // Insertar los nuevos detalles
            $stmt = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['productos'] as $producto_id => $cantidad) {
                if ($cantidad > 0) {
                    $stmt->execute([$pedido_id, $producto_id, $cantidad]);
                }
            }
        }
        
        $conn->commit();
        $mensaje = "Pedido actualizado correctamente";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al actualizar el pedido: " . $e->getMessage();
    }
}

// Obtener todos los productos para el formulario
try {
    $stmt = $conn->query("SELECT * FROM productos ORDER BY nombre");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al obtener los productos: " . $e->getMessage();
}

// Obtener todos los pedidos con sus detalles
try {
    $stmt = $conn->query("
        SELECT p.*, 
               COALESCE(p.nombre_cliente, 'Sin nombre') as nombre,
               COALESCE(p.telefono, 'Sin teléfono') as telefono,
               COALESCE(p.direccion, 'Sin dirección') as direccion,
               (
                   SELECT GROUP_CONCAT(
                       CONCAT(pr.nombre, ' (', dp.cantidad, ')')
                       SEPARATOR ', '
                   )
                   FROM detalle_pedido dp
                   JOIN productos pr ON dp.producto_id = pr.id
                   WHERE dp.pedido_id = p.id
               ) as detalles_empanadas,
               (
                   SELECT COALESCE(SUM(dp.cantidad * pr.precio), 0)
                   FROM detalle_pedido dp
                   JOIN productos pr ON dp.producto_id = pr.id
                   WHERE dp.pedido_id = p.id
               ) as total
        FROM pedidos p
        ORDER BY p.id ASC
    ");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al obtener los pedidos: " . $e->getMessage();
}

// Obtener detalles de empanadas para cada pedido
$detalles_pedidos = [];
foreach ($pedidos as $pedido) {
    $stmt = $conn->prepare("
        SELECT dp.producto_id, dp.cantidad, pr.nombre, pr.precio
        FROM detalle_pedido dp
        JOIN productos pr ON dp.producto_id = pr.id
        WHERE dp.pedido_id = ?
    ");
    $stmt->execute([$pedido['id']]);
    $detalles_pedidos[$pedido['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-center">Historial de Pedidos</h1>
            <a href="index.php" class="btn btn-outline-primary">Volver a la Tienda</a>
        </div>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info">No hay pedidos registrados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Empanadas</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td><?php echo htmlspecialchars($pedido['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['telefono']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['direccion']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['detalles_empanadas'] ?? 'Sin detalles'); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#actualizarModal<?php echo $pedido['id']; ?>">
                                            Actualizar
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este pedido?');">
                                            <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                            <button type="submit" name="eliminar_pedido" class="btn btn-sm btn-danger">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Modal para actualizar -->
                                    <div class="modal fade" id="actualizarModal<?php echo $pedido['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Actualizar Pedido #<?php echo $pedido['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nombre del Cliente:</label>
                                                            <input type="text" name="nombre_cliente" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($pedido['nombre']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Teléfono:</label>
                                                            <input type="tel" name="telefono" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($pedido['telefono']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Dirección:</label>
                                                            <input type="text" name="direccion" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($pedido['direccion']); ?>" required>
                                                        </div>
                                                        
                                                        <h6 class="mt-4">Empanadas:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Empanada</th>
                                                                        <th>Precio</th>
                                                                        <th>Cantidad</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($productos as $producto): 
                                                                        $cantidad = 0;
                                                                        foreach ($detalles_pedidos[$pedido['id']] as $detalle) {
                                                                            if ($detalle['producto_id'] == $producto['id']) {
                                                                                $cantidad = $detalle['cantidad'];
                                                                                break;
                                                                            }
                                                                        }
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                                                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                                                                        <td>
                                                                            <input type="number" name="productos[<?php echo $producto['id']; ?>]" 
                                                                                   class="form-control form-control-sm" 
                                                                                   value="<?php echo $cantidad; ?>" min="0">
                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" name="actualizar_pedido" class="btn btn-primary">Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 