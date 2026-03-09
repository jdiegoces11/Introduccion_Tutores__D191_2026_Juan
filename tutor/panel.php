<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('tutor');

$titulo = 'Panel del Tutor';
$u = usuarioActual();
$conn = conectar();

// Estadísticas del tutor
$stats = $conn->prepare("
    SELECT
      COUNT(CASE WHEN estado IN ('pendiente','confirmada') AND fecha >= CURDATE() THEN 1 END) AS proximas,
      COUNT(CASE WHEN estado = 'completada' THEN 1 END) AS completadas,
      COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) AS canceladas,
      COALESCE(AVG(c.calificacion),0) AS promedio
    FROM tutorias t
    LEFT JOIN comentarios c ON c.tutoria_id = t.id
    WHERE t.tutor_id = ?
");
$stats->bind_param("i", $u['id']);
$stats->execute();
$s = $stats->get_result()->fetch_assoc();

// Próximas citas
$citas = $conn->prepare("
    SELECT t.*, est.nombre AS est_nombre, est.apellido AS est_apellido, m.nombre AS materia
    FROM tutorias t
    JOIN usuarios est ON est.id = t.estudiante_id
    JOIN materias m ON m.id = t.materia_id
    WHERE t.tutor_id = ? AND t.estado IN ('pendiente','confirmada') AND t.fecha >= CURDATE()
    ORDER BY t.fecha ASC, t.hora ASC
    LIMIT 10
");
$citas->bind_param("i", $u['id']);
$citas->execute();
$proximas = $citas->get_result()->fetch_all(MYSQLI_ASSOC);

// Comentarios recibidos
$coms = $conn->prepare("
    SELECT c.*, est.nombre, est.apellido, m.nombre AS materia, t.fecha
    FROM comentarios c
    JOIN usuarios est ON est.id = c.estudiante_id
    JOIN tutorias t ON t.id = c.tutoria_id
    JOIN materias m ON m.id = t.materia_id
    WHERE c.tutor_id = ?
    ORDER BY c.creado_en DESC
    LIMIT 5
");
$coms->bind_param("i", $u['id']);
$coms->execute();
$comentarios = $coms->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
$base = '../';
include '../includes/header.php';
?>

<div class="hero" style="padding:32px 40px;">
  <div class="hero-inner">
    <div>
      <h1>Hola, <?= htmlspecialchars(explode(' ',$u['nombre'])[0]) ?> 👋</h1>
      <p>Bienvenido a tu panel de tutor.</p>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-num"><?= $s['proximas'] ?></div><div class="stat-label">Próximas citas</div></div>
      <div class="stat"><div class="stat-num"><?= $s['completadas'] ?></div><div class="stat-label">Completadas</div></div>
      <div class="stat"><div class="stat-num"><?= round($s['promedio'],1) ?: 'N/A' ?></div><div class="stat-label">Calificación</div></div>
    </div>
  </div>
</div>

<div style="max-width:1100px;margin:32px auto;padding:0 40px;display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <!-- PRÓXIMAS CITAS -->
  <div>
    <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;">🗓️ Próximas citas</h2>
    <?php if (empty($proximas)): ?>
      <div class="card" style="text-align:center;padding:32px;color:var(--text-muted);">No tienes citas próximas</div>
    <?php else: ?>
      <?php foreach ($proximas as $c): ?>
      <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div>
            <div style="font-weight:700;"><?= htmlspecialchars($c['est_nombre'].' '.$c['est_apellido']) ?></div>
            <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($c['materia']) ?></div>
            <div style="font-size:13px;margin-top:6px;">
              📅 <?= date('d/m/Y', strtotime($c['fecha'])) ?>
              🕐 <?= substr($c['hora'],0,5) ?>
              · <?= ucfirst($c['modalidad']) ?>
            </div>
            <?php if ($c['notas']): ?>
              <div style="font-size:12px;margin-top:6px;color:var(--text-muted);">💬 <?= htmlspecialchars($c['notas']) ?></div>
            <?php endif; ?>
          </div>
          <form method="POST" action="../php/cambiar_estado.php">
            <input type="hidden" name="tutoria_id" value="<?= $c['id'] ?>"/>
            <input type="hidden" name="redirect" value="tutor/panel.php"/>
            <select name="estado" class="filter-select" style="font-size:12px;" onchange="this.form.submit()">
              <option value="pendiente"   <?= $c['estado']==='pendiente'?'selected':'' ?>>Pendiente</option>
              <option value="confirmada"  <?= $c['estado']==='confirmada'?'selected':'' ?>>Confirmada</option>
              <option value="completada"  <?= $c['estado']==='completada'?'selected':'' ?>>Completada</option>
              <option value="cancelada"   <?= $c['estado']==='cancelada'?'selected':'' ?>>Cancelada</option>
            </select>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
      <a href="citas.php" style="color:var(--green);font-size:13px;">Ver todas las citas →</a>
    <?php endif; ?>
  </div>

  <!-- COMENTARIOS RECIBIDOS -->
  <div>
    <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;">⭐ Comentarios recientes</h2>
    <?php if (empty($comentarios)): ?>
      <div class="card" style="text-align:center;padding:32px;color:var(--text-muted);">Aún no tienes comentarios</div>
    <?php else: ?>
      <?php foreach ($comentarios as $c): ?>
      <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <div style="font-weight:600;"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></div>
          <span style="color:#f0a500;"><?= str_repeat('★',$c['calificacion']) ?><?= str_repeat('☆',5-$c['calificacion']) ?></span>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;"><?= htmlspecialchars($c['materia']) ?> · <?= date('d/m/Y',strtotime($c['fecha'])) ?></div>
        <?php if ($c['comentario']): ?>
          <p style="font-size:13px;">"<?= htmlspecialchars($c['comentario']) ?>"</p>
        <?php endif; ?>
        <?php if (!$c['respuesta_tutor']): ?>
          <form method="POST" action="../php/responder_comentario.php" style="margin-top:8px;">
            <input type="hidden" name="comentario_id" value="<?= $c['id'] ?>"/>
            <input type="hidden" name="redirect" value="tutor/panel.php"/>
            <input type="text" name="respuesta" class="form-control" placeholder="Responder al estudiante…" style="font-size:12px;"/>
            <button type="submit" class="btn-primary" style="margin-top:6px;padding:6px 14px;font-size:12px;">Responder</button>
          </form>
        <?php else: ?>
          <div style="margin-top:8px;font-size:12px;color:var(--green);">✅ Tu respuesta: <?= htmlspecialchars($c['respuesta_tutor']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<script src="../js/main.js"></script>
</body>
</html>
