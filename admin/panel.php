<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('admin');

$titulo = 'Panel Administrador';
$u = usuarioActual();
$conn = conectar();

// Estadísticas generales
$s = $conn->query("
    SELECT
      (SELECT COUNT(*) FROM usuarios WHERE rol='estudiante' AND activo=1) AS estudiantes,
      (SELECT COUNT(*) FROM usuarios WHERE rol='tutor' AND activo=1) AS tutores,
      (SELECT COUNT(*) FROM tutorias WHERE estado IN ('pendiente','confirmada') AND fecha >= CURDATE()) AS proximas,
      (SELECT COUNT(*) FROM tutorias WHERE estado='completada') AS completadas,
      (SELECT COUNT(*) FROM tutorias WHERE MONTH(fecha)=MONTH(NOW())) AS este_mes,
      (SELECT COALESCE(AVG(calificacion),0) FROM comentarios) AS promedio_global
")->fetch_assoc();

// Últimas tutorías
$ultimas = $conn->query("
    SELECT t.*, est.nombre AS est_nom, est.apellido AS est_ape,
           tur.nombre AS tur_nom, tur.apellido AS tur_ape,
           m.nombre AS materia
    FROM tutorias t
    JOIN usuarios est ON est.id = t.estudiante_id
    JOIN usuarios tur ON tur.id = t.tutor_id
    JOIN materias m ON m.id = t.materia_id
    ORDER BY t.creada_en DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Comentarios pendientes de respuesta docente
$comentarios = $conn->query("
    SELECT c.*, est.nombre, est.apellido, tur.nombre AS tur_nom, tur.apellido AS tur_ape,
           m.nombre AS materia, t.fecha
    FROM comentarios c
    JOIN usuarios est ON est.id = c.estudiante_id
    JOIN usuarios tur ON tur.id = c.tutor_id
    JOIN tutorias t ON t.id = c.tutoria_id
    JOIN materias m ON m.id = t.materia_id
    ORDER BY c.creado_en DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
$base = '../';
include '../includes/header.php';
?>

<div class="hero" style="padding:32px 40px;">
  <div class="hero-inner">
    <div>
      <h1>Panel Administrador</h1>
      <p>Gestión completa del sistema de tutorías.</p>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-num"><?= $s['estudiantes'] ?></div><div class="stat-label">Estudiantes</div></div>
      <div class="stat"><div class="stat-num"><?= $s['tutores'] ?></div><div class="stat-label">Tutores</div></div>
      <div class="stat"><div class="stat-num"><?= $s['este_mes'] ?></div><div class="stat-label">Tutorías este mes</div></div>
      <div class="stat"><div class="stat-num"><?= round($s['promedio_global'],1) ?: 'N/A' ?></div><div class="stat-label">Calificación promedio</div></div>
    </div>
  </div>
</div>

<div class="tabs-bar">
  <div class="tab active">📊 Resumen</div>
  <a href="usuarios.php" class="tab">👥 Usuarios</a>
  <a href="tutorias.php" class="tab">📅 Tutorías</a>
  <a href="materias.php" class="tab">📚 Materias</a>
  <a href="comentarios.php" class="tab">💬 Comentarios</a>
</div>

<div style="max-width:1200px;margin:32px auto;padding:0 40px;display:grid;grid-template-columns:1.5fr 1fr;gap:24px;">

  <!-- ÚLTIMAS TUTORÍAS -->
  <div>
    <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;">📋 Últimas tutorías registradas</h2>
    <div class="card" style="padding:0;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:var(--green-pale);">
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:var(--green);">Estudiante</th>
            <th style="padding:12px 16px;text-align:left;">Tutor</th>
            <th style="padding:12px 16px;text-align:left;">Materia</th>
            <th style="padding:12px 16px;text-align:left;">Fecha</th>
            <th style="padding:12px 16px;text-align:left;">Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimas as $t): ?>
          <tr style="border-top:1px solid var(--border);">
            <td style="padding:10px 16px;"><?= htmlspecialchars($t['est_nom'].' '.$t['est_ape']) ?></td>
            <td style="padding:10px 16px;"><?= htmlspecialchars($t['tur_nom'].' '.$t['tur_ape']) ?></td>
            <td style="padding:10px 16px;"><?= htmlspecialchars($t['materia']) ?></td>
            <td style="padding:10px 16px;"><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
            <td style="padding:10px 16px;">
              <span class="badge <?= $t['estado']==='completada'?'badge-available':($t['estado']==='cancelada'?'badge-busy':'') ?>">
                <?= ucfirst($t['estado']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <a href="tutorias.php" style="color:var(--green);font-size:13px;display:block;margin-top:8px;">Ver todas →</a>
  </div>

  <!-- COMENTARIOS -->
  <div>
    <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;">💬 Comentarios recientes</h2>
    <?php foreach ($comentarios as $c): ?>
    <div class="card" style="margin-bottom:12px;font-size:13px;">
      <div style="display:flex;justify-content:space-between;">
        <strong><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></strong>
        <span style="color:#f0a500;"><?= str_repeat('★',$c['calificacion']) ?></span>
      </div>
      <div style="color:var(--text-muted);margin:4px 0;">→ Tutor: <?= htmlspecialchars($c['tur_nom'].' '.$c['tur_ape']) ?> · <?= htmlspecialchars($c['materia']) ?></div>
      <?php if ($c['comentario']): ?>
        <p style="margin-top:4px;">"<?= htmlspecialchars($c['comentario']) ?>"</p>
      <?php endif; ?>
      <?php if (!$c['respuesta_docente']): ?>
        <form method="POST" action="../php/responder_comentario.php" style="margin-top:8px;">
          <input type="hidden" name="comentario_id" value="<?= $c['id'] ?>"/>
          <input type="hidden" name="tipo" value="docente"/>
          <input type="hidden" name="redirect" value="admin/panel.php"/>
          <input type="text" name="respuesta" class="form-control" placeholder="Observación del docente…" style="font-size:12px;"/>
          <button type="submit" class="btn-primary" style="margin-top:6px;padding:6px 14px;font-size:12px;">Agregar observación</button>
        </form>
      <?php else: ?>
        <div style="margin-top:6px;color:var(--green);font-size:12px;">📝 Docente: <?= htmlspecialchars($c['respuesta_docente']) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <a href="comentarios.php" style="color:var(--green);font-size:13px;">Ver todos →</a>
  </div>

</div>

<script src="../js/main.js"></script>
</body>
</html>
