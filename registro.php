<?php
require_once 'includes/conexion.php';
require_once 'includes/sesion.php';

if (estaLogueado()) { header("Location: index.php"); exit(); }

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar  = $_POST['confirmar'] ?? '';
    $rol       = $_POST['rol'] ?? 'estudiante';
    $carrera   = trim($_POST['carrera'] ?? '');
    $semestre  = intval($_POST['semestre'] ?? 0);

    if (!$nombre || !$apellido || !$correo || !$contrasena) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!str_ends_with($correo, '@uts.edu.co')) {
    $error = "Correo invalido";
    } elseif (strlen($contrasena) < 6) {
    $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $conn = conectar();
        $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ese correo ya está registrado.";
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, carrera, semestre) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssi", $nombre, $apellido, $correo, $hash, $rol, $carrera, $semestre);
            if ($stmt->execute()) {
                $exito = "¡Cuenta creada exitosamente! Ya puedes ingresar.";
            } else {
                $error = "Error al crear la cuenta. Intenta de nuevo.";
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <title>Registro — Tutorías</title>
  <link rel="stylesheet" href="css/estilos.css"/>
</head>
<body class="auth-body">

<div class="auth-container" style="max-width:520px;">
  <div class="auth-logo">
    <div class="nav-logo-icon" style="margin:0 auto 12px;width:56px;height:56px;font-size:28px;">🎓</div>
    <h1>Crear cuenta</h1>
    <p>Regístrate para acceder al sistema</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($exito): ?>
    <div class="alert alert-success"><?= htmlspecialchars($exito) ?> <a href="login.php">Ingresar</a></div>
  <?php endif; ?>

  <form method="POST" class="auth-form">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Apellido</label>
        <input type="text" name="apellido" class="form-control" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Correo institucional</label>
      <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"/>
    </div>
    <div class="form-group">
      <label class="form-label">Soy</label>
      <select name="rol" class="form-control">
        <option value="estudiante" <?= ($_POST['rol']??'')=='estudiante'?'selected':'' ?>>Estudiante</option>
        <option value="tutor" <?= ($_POST['rol']??'')=='tutor'?'selected':'' ?>>Tutor</option>
      </select>
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Carrera</label>
        <input type="text" name="carrera" class="form-control" value="<?= htmlspecialchars($_POST['carrera'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Semestre</label>
        <input type="number" name="semestre" class="form-control" min="1" max="12" value="<?= htmlspecialchars($_POST['semestre'] ?? '') ?>"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Contraseña</label>
      <input type="password" name="contrasena" class="form-control" required/>
    </div>
    <div class="form-group">
      <label class="form-label">Confirmar contraseña</label>
      <input type="password" name="confirmar" class="form-control" required/>
    </div>
    <button type="submit" class="btn-confirm" style="width:100%;padding:13px;font-size:15px;">
      Crear cuenta
    </button>
  </form>

  <p class="auth-footer">¿Ya tienes cuenta? <a href="login.php">Ingresar</a></p>
</div>

</body>
</html>
