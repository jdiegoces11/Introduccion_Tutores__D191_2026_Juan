<?php
require_once 'includes/conexion.php';
require_once 'includes/sesion.php';
requiereRol('estudiante');

$titulo = 'Mis Tutorías';
$u = usuarioActual();
$conn = conectar();

// Cancelar tutoría
if (isset($_POST['accion']) && $_POST['accion'] === 'cancelar' && isset($_POST['tutoria_id'])) {
    $tid = intval($_POST['tutoria_id']);
    $stmt = $conn->prepare("UPDATE tutorias SET estado='cancelada' WHERE id=? AND estudiante_id=?");
    $stmt->bind_param("ii", $tid, $u['id']);
    $stmt->execute();
}

// Cargar tutorías del estudiante
$stmt = $conn->prepare("
    SELECT t.*, u.nombre AS tutor_nombre, u.apellido AS tutor_apellido,
           m.nombre AS materia_nombre,
           c.calificacion, c.comentario, c.id AS comentario_id
    FROM tutorias t
    JOIN usuarios u ON u.id = t.tutor_id
    JOIN materias m ON m.id = t.materia_id
    LEFT JOIN comentarios c ON c.tutoria_id = t.id
    WHERE t.estudiante_id = ?
    ORDER BY t.fecha DESC, t.hora DESC
");
$stmt->bind_param("i", $u['id']);
$stmt->execute();
$tutorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

include 'includes/header.php';
?>

<div class="hero" style="padding:32px 40px;">
  <div class="hero-inner">
    <div>
      <h1>📅 Mis Tutorías</h1>
      <p>Aquí puedes ver, gestionar y calificar tus sesiones.</p>
    </div>
    <a href="index.php" class="btn-confirm" style="text-decoration:none;padding:12px 24px;">+ Agendar nueva</a>
  </div>
</div>

<div style="max-width:1100px;margin:32px auto;padding:0 40px;">

<?php if (empty($tutorias)): ?>
  <div class="card" style="text-align:center;padding:60px;">
    <p style="font-size:48px;margin-bottom:16px;">📭</p>
    <p style="font-size:18px;font-weight:600;margin-bottom:8px;">Aún no tienes tutorías</p>
    <a href="index.php" class="btn-primary" style="display:inline-block;padding:12px 28px;text-decoration:none;">Agendar mi primera tutoría</a>
  </div>
<?php else: ?>

  <?php
  $pendientes  = array_filter($tutorias, fn($t)=>$t['estado']==='pendiente'||$t['estado']==='confirmada');
  $completadas = array_filter($tutorias, fn($t)=>$t['estado']==='completada');
  $canceladas  = array_filter($tutorias, fn($t)=>$t['estado']==='cancelada');
  ?>

  <!-- PRÓXIMAS -->
  <?php if ($pendientes): ?>
  <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;color:var(--text);">🔔 Próximas sesiones</h2>
  <div style="display:grid;gap:12px;margin-bottom:32px;">
    <?php foreach ($pendientes as $t): ?>
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <div style="display:flex;gap:16px;align-items:center;">
        <div class="tutor-avatar" style="width:48px;height:48px;font-size:15px;">
          <?= strtoupper(substr($t['tutor_nombre'],0,1).substr($t['tutor_apellido'],0,1)) ?>
        </div>
        <div>
          <div style="font-weight:700;"><?= htmlspecialchars($t['tutor_nombre'].' '.$t['tutor_apellido']) ?></div>
          <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($t['materia_nombre']) ?></div>
          <div style="font-size:13px;margin-top:4px;">
            📅 <?= date('d/m/Y', strtotime($t['fecha'])) ?>
            🕐 <?= substr($t['hora'],0,5) ?>
            <?= $t['modalidad']==='virtual'?'💻 Virtual':'🏫 Presencial' ?>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <span class="badge <?= $t['estado']==='confirmada'?'badge-available':'badge-busy' ?>">
          <?= ucfirst($t['estado']) ?>
        </span>
        <form method="POST" onsubmit="return confirm('¿Cancelar esta tutoría?')">
          <input type="hidden" name="accion" value="cancelar"/>
          <input type="hidden" name="tutoria_id" value="<?= $t['id'] ?>"/>
          <button type="submit" class="btn-outline" style="color:#c0392b;border-color:#c0392b;padding:7px 14px;">Cancelar</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- COMPLETADAS / CALIFICAR -->
  <?php if ($completadas): ?>
  <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;">✅ Sesiones completadas</h2>
  <div style="display:grid;gap:12px;margin-bottom:32px;">
    <?php foreach ($completadas as $t): ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:16px;align-items:center;">
          <div class="tutor-avatar" style="width:44px;height:44px;font-size:14px;">
            <?= strtoupper(substr($t['tutor_nombre'],0,1).substr($t['tutor_apellido'],0,1)) ?>
          </div>
          <div>
            <div style="font-weight:700;"><?= htmlspecialchars($t['tutor_nombre'].' '.$t['tutor_apellido']) ?></div>
            <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($t['materia_nombre']) ?> · <?= date('d/m/Y', strtotime($t['fecha'])) ?></div>
          </div>
        </div>
        <?php if (!$t['comentario_id']): ?>
          <button class="btn-primary" onclick="abrirCalificacion(<?= $t['id'] ?>)" style="padding:8px 16px;font-size:13px;">
            ⭐ Calificar sesión
          </button>
        <?php else: ?>
          <div style="color:var(--green);font-size:13px;font-weight:600;">
            <?= str_repeat('★',$t['calificacion']) ?> Calificado
          </div>
        <?php endif; ?>
      </div>
      <?php if ($t['comentario']): ?>
        <div style="margin-top:12px;padding:12px;background:var(--green-pale);border-radius:8px;font-size:13px;">
          💬 "<?= htmlspecialchars($t['comentario']) ?>"
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- CANCELADAS -->
  <?php if ($canceladas): ?>
  <h2 style="margin-bottom:16px;font-size:16px;font-weight:700;color:var(--text-muted);">❌ Canceladas</h2>
  <div style="display:grid;gap:12px;">
    <?php foreach ($canceladas as $t): ?>
    <div class="card" style="opacity:0.6;">
      <div style="font-weight:600;"><?= htmlspecialchars($t['tutor_nombre'].' '.$t['tutor_apellido']) ?></div>
      <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($t['materia_nombre']) ?> · <?= date('d/m/Y', strtotime($t['fecha'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

<?php endif; ?>
</div>

<!-- MODAL CALIFICAR -->
<div class="modal-overlay" id="modalCalificacion">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <h2>⭐ Calificar sesión</h2>
      <button class="modal-close" onclick="document.getElementById('modalCalificacion').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body">
      <form id="formCalificar" method="POST" action="php/calificar.php">
        <input type="hidden" name="tutoria_id" id="calTutoriaId"/>
        <div class="form-group">
          <label class="form-label">Calificación</label>
          <div style="display:flex;gap:8px;font-size:28px;">
            <?php for($i=1;$i<=5;$i++): ?>
              <span class="estrella" data-val="<?=$i?>" onclick="setEstrella(<?=$i?>)" style="cursor:pointer;color:#ccc;">★</span>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="calificacion" id="inputCalificacion" value="0"/>
        </div>
        <div class="form-group">
          <label class="form-label">Comentario</label>
          <textarea name="comentario" class="form-control" rows="4" placeholder="¿Cómo fue la sesión? Tu opinión ayuda a otros estudiantes…"></textarea>
        </div>
        <div class="modal-footer" style="padding:0;border:none;">
          <button type="button" class="btn-cancel" onclick="document.getElementById('modalCalificacion').classList.remove('open')">Cancelar</button>
          <button type="submit" class="btn-confirm">Enviar calificación</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>
<script src="js/main.js"></script>
<script>
function abrirCalificacion(tutoriaId) {
  document.getElementById('calTutoriaId').value = tutoriaId;
  document.getElementById('modalCalificacion').classList.add('open');
}
function setEstrella(val) {
  document.getElementById('inputCalificacion').value = val;
  document.querySelectorAll('.estrella').forEach((s,i) => {
    s.style.color = i < val ? '#f0a500' : '#ccc';
  });
}
document.getElementById('formCalificar').addEventListener('submit', function(e){
  e.preventDefault();
  if (!document.getElementById('inputCalificacion').value || document.getElementById('inputCalificacion').value == 0) {
    alert('Por favor selecciona una calificación.'); return;
  }
  fetch('php/calificar.php', {method:'POST', body: new FormData(this)})
    .then(r=>r.json()).then(res=>{
      if(res.ok){ location.reload(); }
      else { alert(res.mensaje); }
    });
});
</script>
</body>
</html>
