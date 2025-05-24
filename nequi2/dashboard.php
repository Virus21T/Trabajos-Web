<?php
session_start();
include('config/conex.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Obtener el saldo actualizado
$stmt = $conn->prepare("SELECT saldo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$_SESSION['saldo'] = $user['saldo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
            <div class="balance-display">
                Saldo Actual: $<?= number_format($_SESSION['saldo'], 2) ?>
            </div>
            <div class="dashboard-actions">
                <a href="recharge.php" class="btn btn-primary">Recargar Saldo</a>
                <a href="transfer.php" class="btn btn-primary">Transferir Dinero</a>
                <a href="transactions.php" class="btn btn-primary">Ver Transacciones</a>
                <a href="logout.php" class="btn btn-primary">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>