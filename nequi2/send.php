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

    $dest = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
    $dest->bind_param("s", $telefono);
    $dest->execute();
    $result = $dest->get_result();

    if ($result->num_rows === 1) {
        $to = $result->fetch_assoc()['id'];

        $conn->begin_transaction();
        $conn->query("UPDATE usuarios SET saldo = saldo - $monto WHERE id = $from AND saldo >= $monto");
        $conn->query("UPDATE usuarios SET saldo = saldo + $monto WHERE id = $to");
        $conn->query("INSERT INTO transacciones (origen_id, destino_id, monto) VALUES ($from, $to, $monto)");
        $conn->commit();

        $_SESSION['saldo'] -= $monto;
        $success = "Transferencia exitosa.";
    } else {
        $error = "Número de destino no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Dinero - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Enviar Dinero</h1>
            
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

            <form method="POST" class="send-form">
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
                        <label for="monto" class="required">Monto a enviar</label>
                        <div class="amount-input">
                            <input type="number" id="monto" name="monto" step="0.01" required 
                                   min="1" data-tooltip="Ingresa el monto que deseas enviar">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enviar Dinero</button>
                </div>
            </form>

            <div class="footer">
                <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>