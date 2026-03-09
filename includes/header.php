<?php
// includes/header.php — Navbar común para todas las páginas
require_once __DIR__ . '/sesion.php';
$u = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $titulo ?? 'Tutorías Universitarias' ?></title>
  <link rel="stylesheet" href="<?= $base ?? '' ?>css/estilos.css"/>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-icon">🎓</div>
    <div>
      <div class="nav-logo-text">Tutorías Universitarias</div>
      <div class="nav-logo-sub">Portal Académico</div>
    </div>
  </div>

  <div class="nav-links">
    <?php if ($u['rol'] === 'estudiante'): ?>
      <a href="<?= $base ?? '' ?>index.php">Inicio</a>
      <a href="<?= $base ?? '' ?>mis-tutorias.php">Mis Tutorías</a>
    <?php elseif ($u['rol'] === 'tutor'): ?>
      <a href="<?= $base ?? '' ?>tutor/panel.php">Mi Panel</a>
      <a href="<?= $base ?? '' ?>tutor/citas.php">Mis Citas</a>
    <?php elseif ($u['rol'] === 'admin'): ?>
      <a href="<?= $base ?? '' ?>admin/panel.php">Panel Admin</a>
      <a href="<?= $base ?? '' ?>admin/usuarios.php">Usuarios</a>
      <a href="<?= $base ?? '' ?>admin/tutorias.php">Tutorías</a>
    <?php endif; ?>
  </div>

  <div class="nav-right">
    <?php if (estaLogueado()): ?>
      <div class="avatar"><?= htmlspecialchars(substr($u['nombre'],0,2)) ?></div>
      <span class="nav-user-name"><?= htmlspecialchars($u['nombre']) ?></span>
      <a href="<?= $base ?? '' ?>logout.php" class="btn-logout">Salir</a>
    <?php else: ?>
      <a href="<?= $base ?? '' ?>login.php" class="btn-logout">Ingresar</a>
    <?php endif; ?>
  </div>
</nav>
