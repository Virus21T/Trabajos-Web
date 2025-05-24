<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$error = '';
$success = '';

// Obtener el saldo actual del usuario
$stmt = $conn->prepare("SELECT saldo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$saldo_actual = $user['saldo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto']);
    
    if ($monto <= 0) {
        $error = "El monto debe ser mayor a 0";
    } else {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Actualizar saldo del usuario
            $nuevo_saldo = $saldo_actual + $monto;
            $stmt = $conn->prepare("UPDATE usuarios SET saldo = ? WHERE id = ?");
            $stmt->bind_param("di", $nuevo_saldo, $user_id);
            $stmt->execute();
            
            // Registrar la transacción
            $stmt = $conn->prepare("INSERT INTO transacciones (origen_id, destino_id, monto) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $user_id, $user_id, $monto);
            $stmt->execute();
            
            // Confirmar transacción
            $conn->commit();
            
            $success = "Recarga exitosa de $" . number_format($monto, 2);
            $saldo_actual = $nuevo_saldo;
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            $error = "Error al realizar la recarga: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recargar Saldo - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Recargar Saldo</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="balance-display">
                Saldo actual: $<?php echo number_format($saldo_actual, 2); ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="monto">Monto a recargar</label>
                    <div class="amount-input">
                        <input type="number" id="monto" name="monto" step="0.01" min="0" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Recargar</button>
            </form>
            
            <div class="footer">
                <a href="dashboard.php" class="btn btn-secondary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>