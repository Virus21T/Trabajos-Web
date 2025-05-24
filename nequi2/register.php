<?php
require_once('config/conex.php');

// Verificar la conexión
if (!$conn) {
    die("Error de conexión: No se pudo conectar a la base de datos");
}

// Verificar si la tabla existe
$check_table = $conn->query("SHOW TABLES LIKE 'usuarios'");
if ($check_table->num_rows == 0) {
    die("Error: La tabla 'usuarios' no existe en la base de datos");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Primero verificamos si el teléfono ya existe
    $check_phone = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
    $check_phone->bind_param("s", $telefono);
    $check_phone->execute();
    $result = $check_phone->get_result();

    if ($result->num_rows > 0) {
        $error = "Este número de teléfono ya está registrado. Por favor, use otro número o inicie sesión.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, telefono, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $telefono, $pass);
        if ($stmt->execute()) {
            $success = "Registro exitoso. <a href='login.php'>Inicia sesión</a>";
        } else {
            $error = "Error al registrar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Registro</h1>
            
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

            <form method="POST" class="registration-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre" class="required">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" required 
                               data-tooltip="Ingresa tu nombre completo">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono" class="required">Número de teléfono</label>
                        <input type="tel" id="telefono" name="telefono" required 
                               data-tooltip="Ingresa tu número de teléfono">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Contraseña</label>
                        <input type="password" id="password" name="password" required 
                               data-tooltip="Ingresa tu contraseña">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </div>
            </form>

            <div class="footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>