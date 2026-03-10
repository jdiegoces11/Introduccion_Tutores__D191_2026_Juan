<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sesion.php';

// Si ya está logueado, redirigir
if (estaLogueado()) {
    $rol = $_SESSION['rol'];
    if ($rol === 'admin') header("Location: admin/panel.php");
    elseif ($rol === 'tutor') header("Location: tutor/panel.php");
    else header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo    = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo && $contrasena) {
        $conn = conectar();
        $stmt = $conn->prepare("SELECT id, nombre, apellido, contrasena, rol FROM usuarios WHERE correo = ? AND activo = 1");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['rol']        = $user['rol'];
            $_SESSION['correo']     = $correo;

            if ($user['rol'] === 'admin') header("Location: admin/panel.php");
            elseif ($user['rol'] === 'tutor') header("Location: tutor/panel.php");
            else header("Location: index.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
        $conn->close();
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ingresar — Tutorías</title>
  <link rel="stylesheet" href="css/estilos.css"/>
</head>
<body class="auth-body">

<div class="auth-container">
  <div class="auth-logo">
    <div class="nav-logo-icon" style="margin:0 auto 12px;width:56px;height:56px;font-size:28px;">🎓</div>
    <h1>Tutorías Universitarias</h1>
    <p>Ingresa a tu cuenta</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="auth-form">
    <div class="form-group">
      <label class="form-label">Correo institucional</label>
      <input type="email" name="correo" class="form-control" placeholder="usuario@universidad.edu" required
             value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"/>
    </div>
    <div class="form-group">
      <label class="form-label">Contraseña</label>
      <input type="password" name="contrasena" class="form-control" placeholder="••••••••" required/>
    </div>
    <button type="submit" class="btn-confirm" style="width:100%;padding:13px;font-size:15px;">
      Ingresar
    </button>
  </form>

  <p class="auth-footer">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>

  <!-- <div class="auth-demo">
    <p><strong>Cuentas de prueba:</strong></p>
    <p>Admin: admin@uts.edu.co / admin123</p>
    <p>Tutor: carlos@uts.edu.co / tutor123</p>
    <p>Estudiante: ana@uts.edu.co / est123</p>
  </div>
</div>  -->

</body>
</html>
