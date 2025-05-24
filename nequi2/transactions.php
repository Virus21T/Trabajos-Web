<?php
session_start();
include('config/conex.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Obtener transacciones enviadas
$sent_query = "
    SELECT t.*, u.telefono as destino_telefono, u.nombre as destino_nombre
    FROM transacciones t
    INNER JOIN usuarios u ON t.destino_id = u.id
    WHERE t.origen_id = ?
    ORDER BY t.fecha DESC
";

$sent_stmt = $conn->prepare($sent_query);
$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();

// Obtener transacciones recibidas
$received_query = "
    SELECT t.*, u.telefono as origen_telefono, u.nombre as origen_nombre
    FROM transacciones t
    INNER JOIN usuarios u ON t.origen_id = u.id
    WHERE t.destino_id = ?
    ORDER BY t.fecha DESC
";

$received_stmt = $conn->prepare($received_query);
$received_stmt->bind_param("i", $user_id);
$received_stmt->execute();
$received_result = $received_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Transacciones - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Historial de Transacciones</h1>

            <div class="transactions-section">
                <h2>Transacciones Enviadas</h2>
                <div class="transactions-table-container">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Destinatario</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sent_result->num_rows > 0) : ?>
                                <?php while ($row = $sent_result->fetch_assoc()) : ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['destino_nombre']) ?><br>
                                            <small><?= htmlspecialchars($row['destino_telefono']) ?></small>
                                        </td>
                                        <td class="amount negative">-$<?= number_format($row['monto'], 2) ?></td>
                                        <td class="date"><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="no-transactions">No hay transferencias enviadas</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="transactions-section">
                <h2>Transacciones Recibidas</h2>
                <div class="transactions-table-container">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Remitente</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($received_result->num_rows > 0) : ?>
                                <?php while ($row = $received_result->fetch_assoc()) : ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['origen_nombre']) ?><br>
                                            <small><?= htmlspecialchars($row['origen_telefono']) ?></small>
                                        </td>
                                        <td class="amount positive">+$<?= number_format($row['monto'], 2) ?></td>
                                        <td class="date"><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="no-transactions">No hay transferencias recibidas</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>