<?php
require_once 'includes/conexion.php';
require_once 'includes/sesion.php';

if (estaLogueado()) { header("Location: index.php"); exit(); }

$error = '';
$exito = '';

$conn = conectar();
$materias = $conn->query("SELECT * FROM materias WHERE activa=1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $correo      = trim($_POST['correo'] ?? '');
    $contrasena  = $_POST['contrasena'] ?? '';
    $confirmar   = $_POST['confirmar'] ?? '';
    $rol         = $_POST['rol'] ?? 'estudiante';
    $carrera     = trim($_POST['carrera'] ?? '');
    $semestre    = intval($_POST['semestre'] ?? 0);
    $sala        = trim($_POST['sala'] ?? '');
    $modalidad   = $_POST['modalidad'] ?? 'presencial';
    $dias        = $_POST['dias'] ?? [];
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin    = $_POST['hora_fin'] ?? '';
    $materias_sel = $_POST['materias_ids'] ?? [];

    if (!$nombre || !$apellido || !$correo || !$contrasena) {
        $error = "Todos los campos obligatorios deben estar llenos.";
    } elseif (!str_ends_with($correo, '@uts.edu.co')) {
        $error = "Solo se aceptan correos institucionales @uts.edu.co";
    } elseif ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($rol === 'tutor' && empty($materias_sel)) {
        $error = "Debes seleccionar al menos una materia que dictas.";
    } elseif ($rol === 'tutor' && empty($dias)) {
        $error = "Debes seleccionar al menos un día de disponibilidad.";
    } else {
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
                $nuevo_id = $conn->insert_id;

                if ($rol === 'tutor') {
                    foreach ($materias_sel as $mid) {
                        $mid = intval($mid);
                        $ms = $conn->prepare("INSERT INTO tutor_materias (tutor_id, materia_id) VALUES (?,?)");
                        $ms->bind_param("ii", $nuevo_id, $mid);
                        $ms->execute();
                    }
                    foreach ($dias as $dia) {
                        $hs = $conn->prepare("INSERT INTO horarios_tutor (tutor_id, dia_semana, hora_inicio, hora_fin, sala, modalidad) VALUES (?,?,?,?,?,?)");
                        $hs->bind_param("isssss", $nuevo_id, $dia, $hora_inicio, $hora_fin, $sala, $modalidad);
                        $hs->execute();
                    }
                }
                $exito = "¡Cuenta creada exitosamente! Ya puedes ingresar.";
            } else {
                $error = "Error al crear la cuenta. Intenta de nuevo.";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro — Tutorías UTS</title>
  <link rel="stylesheet" href="css/estilos.css"/>
  <style>
    .tutor-fields { display:none; }
    .tutor-fields.visible { display:block; }
    .dias-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:8px; }
    .dia-item { display:flex; align-items:center; gap:6px; font-size:13px; padding:6px; border:1px solid var(--border); border-radius:8px; cursor:pointer; }
    .dia-item:hover { border-color:var(--green); background:var(--green-pale); }
    .materias-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:8px; margin-top:8px; }
    .materia-item { display:flex; align-items:center; gap:6px; font-size:13px; padding:8px 10px; border:1px solid var(--border); border-radius:8px; cursor:pointer; }
    .materia-item:hover { border-color:var(--green); background:var(--green-pale); }
    .seccion-tutor { background:var(--green-pale); border:1px solid var(--green-mid); border-radius:10px; padding:16px; margin-bottom:16px; }
    .seccion-titulo { font-size:13px; font-weight:700; color:var(--green); margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px; }
  </style>
</head>
<body class="auth-body">

<div class="auth-container" style="max-width:540px;">
  <div class="auth-logo">
    <div class="nav-logo-icon" style="margin:0 auto 12px;width:56px;height:56px;font-size:28px;">🎓</div>
    <h1>Crear cuenta</h1>
    <p>Regístrate para acceder al sistema de tutorías UTS</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($exito): ?>
    <div class="alert alert-success"><?= htmlspecialchars($exito) ?> <a href="login.php">Ingresar aquí</a></div>
  <?php endif; ?>

  <form method="POST" class="auth-form">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Apellido *</label>
        <input type="text" name="apellido" class="form-control" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"/>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Correo institucional *</label>
      <input type="email" name="correo" class="form-control" required placeholder="nombre@uts.edu.co" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"/>
    </div>

    <div class="form-group">
      <label class="form-label">Soy *</label>
      <select name="rol" class="form-control" id="selectRol" onchange="toggleTutor(this.value)">
        <option value="estudiante" <?= ($_POST['rol']??'estudiante')==='estudiante'?'selected':'' ?>>Estudiante</option>
        <option value="tutor"      <?= ($_POST['rol']??'')==='tutor'?'selected':'' ?>>Tutor</option>
      </select>
    </div>

    <div id="camposEstudiante" style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Carrera</label>
        <input type="text" name="carrera" class="form-control" placeholder="Ej: Ing. de Sistemas" value="<?= htmlspecialchars($_POST['carrera'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Semestre</label>
        <input type="number" name="semestre" class="form-control" min="1" max="12" value="<?= htmlspecialchars($_POST['semestre'] ?? '') ?>"/>
      </div>
    </div>

    <!-- CAMPOS SOLO PARA TUTOR -->
    <div class="tutor-fields <?= ($_POST['rol']??'')==='tutor'?'visible':'' ?>" id="camposTutor">

      <div class="seccion-tutor">
        <div class="seccion-titulo">📚 Materias que dictas *</div>
        <div class="materias-grid">
          <?php foreach ($materias as $m): ?>
            <label class="materia-item">
              <input type="checkbox" name="materias_ids[]" value="<?= $m['id'] ?>"
                <?= in_array($m['id'], $_POST['materias_ids'] ?? []) ? 'checked' : '' ?>/>
              <?= htmlspecialchars($m['nombre']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="seccion-tutor">
        <div class="seccion-titulo">🗓️ Disponibilidad *</div>

        <div class="form-group">
          <label class="form-label">Días disponibles</label>
          <div class="dias-grid">
            <?php foreach (['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'] as $dia): ?>
              <label class="dia-item">
                <input type="checkbox" name="dias[]" value="<?= $dia ?>"
                  <?= in_array($dia, $_POST['dias'] ?? []) ? 'checked' : '' ?>/>
                <?= $dia ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Hora inicio</label>
            <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '08:00') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Hora fin</label>
            <input type="time" name="hora_fin" class="form-control" value="<?= htmlspecialchars($_POST['hora_fin'] ?? '12:00') ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sala o lugar</label>
          <input type="text" name="sala" class="form-control" placeholder="Ej: Sala B-204, Biblioteca..." value="<?= htmlspecialchars($_POST['sala'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label class="form-label">Modalidad</label>
          <select name="modalidad" class="form-control">
            <option value="presencial" <?= ($_POST['modalidad']??'')==='presencial'?'selected':'' ?>>Presencial</option>
            <option value="virtual"    <?= ($_POST['modalidad']??'')==='virtual'?'selected':'' ?>>Virtual</option>
            <option value="ambas"      <?= ($_POST['modalidad']??'')==='ambas'?'selected':'' ?>>Ambas</option>
          </select>
        </div>
      </div>

    </div>

    <div class="form-group">
      <label class="form-label">Contraseña</label>
      <input type="password" name="contrasena" class="form-control" required/>
    </div>
    <div class="form-group">
      <label class="form-label">Confirmar contraseña *</label>
      <input type="password" name="confirmar" class="form-control" required/>
    </div>

    <button type="submit" class="btn-confirm" style="width:100%;padding:13px;font-size:15px;">
      Crear cuenta
    </button>
  </form>

  <p class="auth-footer">¿Ya tienes cuenta? <a href="login.php">Ingresar aquí</a></p>
</div>

<script>
function toggleTutor(rol) {
  const camposTutor = document.getElementById('camposTutor');
  const camposEstudiante = document.getElementById('camposEstudiante');
  if (rol === 'tutor') {
    camposTutor.classList.add('visible');
    camposEstudiante.style.display = 'none';
  } else {
    camposTutor.classList.remove('visible');
    camposEstudiante.style.display = 'grid';
  }
}
</script>
</body>
</html>
