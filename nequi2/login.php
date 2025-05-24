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
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Incluir la conexión a la base de datos
            require_once('config/conex.php');

            $error = '';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (empty($_POST['telefono']) || empty($_POST['password'])) {
                    $error = "Por favor, complete todos los campos.";
                } else {
                    $telefono = trim($_POST['telefono']);
                    $password = $_POST['password'];

                    try {
                        // Preparar la consulta
                        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = ?");
                        if (!$stmt) {
                            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
                        }

                        // Vincular parámetros
                        $stmt->bind_param("s", $telefono);
                        
                        // Ejecutar la consulta
                        if (!$stmt->execute()) {
                            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
                        }

                        $result = $stmt->get_result();

                        if ($result->num_rows === 1) {
                            $user = $result->fetch_assoc();
                            
                            // Verificar la contraseña
                            if (password_verify($password, $user['password'])) {
                                // Iniciar sesión
                                $_SESSION['id'] = $user['id'];
                                $_SESSION['nombre'] = $user['nombre'];
                                $_SESSION['saldo'] = $user['saldo'];
                                
                                // Redirigir al dashboard
                                header("Location: dashboard.php");
                                exit();
                            } else {
                                $error = "Contraseña incorrecta.";
                            }
                        } else {
                            $error = "Número de teléfono no encontrado.";
                        }
                    } catch (Exception $e) {
                        $error = "Error: " . $e->getMessage();
                    }
                }
            }
            ?>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
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