<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Nequi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <img src="img/nequi2.png" alt="Nequi Logo">
            </div>
            <h1 class="form-title">Iniciar Sesión</h1>
            
            <?php
            session_start();
            include('config/conex.php');

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $telefono = $_POST['telefono'];
                $pass = $_POST['password'];

                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = ?");
                $stmt->bind_param("s", $telefono);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($pass, $user['password'])) {
                        $_SESSION['id'] = $user['id'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['saldo'] = $user['saldo'];
                        header("Location: dashboard.php");
                        exit();
                    }
                }
                $error = "Número o clave incorrectos.";
            }
            ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="registration-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono" class="required">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" required 
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                               data-tooltip="Ingresa tu número de teléfono móvil">
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
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>

            <div class="footer">
                <p>¿No tienes una cuenta? <a href="register.php" style="color: #004884; text-decoration: none;">Regístrate</a></p>
            </div>
        </div>
    </div>
</body>
</html>
