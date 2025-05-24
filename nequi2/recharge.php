<?php
session_start();
include('config/conex.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $monto = floatval($_POST['monto']);
    $user_id = $_SESSION['id'];

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Actualizar saldo
        $update = $conn->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $update->bind_param("di", $monto, $user_id);
        $update->execute();

        // Registrar la recarga en la tabla de transacciones
        $insert = $conn->prepare("INSERT INTO transacciones (origen_id, monto, destino_id) VALUES (?, ?, NULL)");
        $insert->bind_param("id", $user_id, $monto);
        $insert->execute();

        $conn->commit();
        $_SESSION['saldo'] += $monto;
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error al realizar la recarga: " . $e->getMessage();
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
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="recharge-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="monto" class="required">Monto a recargar</label>
                        <div class="amount-input">
                            <input type="number" id="monto" name="monto" step="0.01" required 
                                   min="1" data-tooltip="Ingresa el monto que deseas recargar">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Recargar</button>
                </div>
            </form>

            <div class="footer">
                <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>