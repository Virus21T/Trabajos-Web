<?php
require_once 'config/database.php';

// Procesar actualización de stock
if (isset($_POST['actualizar_stock'])) {
    $producto_id = intval($_POST['producto_id']);
    $nuevo_stock = intval($_POST['nuevo_stock']);
    
    try {
        $stmt = $conn->prepare("UPDATE productos SET stock = ? WHERE id = ?");
        $stmt->execute([$nuevo_stock, $producto_id]);
        $mensaje = "Stock actualizado correctamente";
    } catch (Exception $e) {
        $error = "Error al actualizar el stock: " . $e->getMessage();
    }
}

// Obtener productos con estadísticas
try {
    $stmt = $conn->query("
        SELECT 
            p.*,
            COALESCE(SUM(dp.cantidad), 0) as total_vendido,
            COALESCE(AVG(dp.cantidad), 0) as promedio_ventas,
            (
                SELECT COUNT(DISTINCT pedido_id) 
                FROM detalle_pedido 
                WHERE producto_id = p.id
            ) as veces_vendido
        FROM productos p
        LEFT JOIN detalle_pedido dp ON p.id = dp.producto_id
        GROUP BY p.id
        ORDER BY p.nombre
    ");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al obtener los productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-center">Control de Inventario</h1>
            <div>
                <a href="index.php" class="btn btn-outline-primary me-2">Volver a la Tienda</a>
                <a href="historial_pedidos.php" class="btn btn-outline-primary">Ver Pedidos</a>
            </div>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Empanada</th>
                        <th>Stock Actual</th>
                        <th>Total Vendido</th>
                        <th>Promedio por Pedido</th>
                        <th>Veces Vendido</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo $producto['stock']; ?></td>
                        <td><?php echo $producto['total_vendido']; ?></td>
                        <td><?php echo round($producto['promedio_ventas']); ?></td>
                        <td><?php echo $producto['veces_vendido']; ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#actualizarStockModal<?php echo $producto['id']; ?>">
                                Actualizar Stock
                            </button>

                            <!-- Modal para actualizar stock -->
                            <div class="modal fade" id="actualizarStockModal<?php echo $producto['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Actualizar Stock - <?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Stock Actual:</label>
                                                    <input type="number" name="nuevo_stock" class="form-control" 
                                                           value="<?php echo $producto['stock']; ?>" min="0" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" name="actualizar_stock" class="btn btn-primary">Guardar Cambios</button>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 