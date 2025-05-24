<?php
session_start();
include('config/conex.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefono = $_POST['telefono'];
    $monto = floatval($_POST['monto']);
    $from = $_SESSION['id'];

    // Verificar si el usuario tiene suficiente saldo
    $check_balance = $conn->prepare("SELECT saldo FROM usuarios WHERE id = ?");
    $check_balance->bind_param("i", $from);
    $check_balance->execute();
    $balance_result = $check_balance->get_result();
    $current_balance = $balance_result->fetch_assoc()['saldo'];

    if ($current_balance < $monto) {
        $error = "Saldo insuficiente para realizar la transferencia.";
    } else {
        // Buscar el usuario destino
        $dest = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
        $dest->bind_param("s", $telefono);
        $dest->execute();
        $result = $dest->get_result();

        if ($result->num_rows === 1) {
            $to = $result->fetch_assoc()['id'];

            // Iniciar transacción
            $conn->begin_transaction();

            try {
                // Actualizar saldo del remitente
                $update_from = $conn->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ?");
                $update_from->bind_param("di", $monto, $from);
                $update_from->execute();

                // Actualizar saldo del destinatario
                $update_to = $conn->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
                $update_to->bind_param("di", $monto, $to);
                $update_to->execute();

                // Registrar la transacción
                $insert_transaction = $conn->prepare("INSERT INTO transacciones (origen_id, destino_id, monto) VALUES (?, ?, ?)");
                $insert_transaction->bind_param("iid", $from, $to, $monto);
                $insert_transaction->execute();

                $conn->commit();
                $_SESSION['saldo'] -= $monto;
                $success = "Transferencia exitosa.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error al realizar la transferencia: " . $e->getMessage();
            }
        } else {
            $error = "Número de destino no encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferir Dinero - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Transferir Dinero</h1>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="transfer-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono" class="required">Número del destinatario</label>
                        <input type="tel" id="telefono" name="telefono" required 
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                               data-tooltip="Ingresa el número de teléfono del destinatario">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="monto" class="required">Monto a transferir</label>
                        <div class="amount-input">
                            <input type="number" id="monto" name="monto" step="0.01" required 
                                   min="1" data-tooltip="Ingresa el monto que deseas transferir">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Transferir</button>
                </div>
            </form>

            <div class="footer">
                <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html> 