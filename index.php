<?php
require_once 'includes/conexion.php';
require_once 'includes/sesion.php';
requiereRol('estudiante');

$titulo = 'Inicio — Tutorías';
$conn = conectar();

// Filtros
$filtro_materia  = intval($_GET['materia'] ?? 0);
$filtro_modalidad = $_GET['modalidad'] ?? '';
$buscar          = trim($_GET['buscar'] ?? '');

// Construir query de tutores
$where = "WHERE u.rol = 'tutor' AND u.activo = 1";
$params = [];
$types  = '';

if ($filtro_materia > 0) {
    $where .= " AND tm.materia_id = ?";
    $params[] = $filtro_materia;
    $types   .= 'i';
}
if ($filtro_modalidad) {
    $where .= " AND (h.modalidad = ? OR h.modalidad = 'ambas')";
    $params[] = $filtro_modalidad;
    $types   .= 's';
}
if ($buscar) {
    $where .= " AND (u.nombre LIKE ? OR u.apellido LIKE ?)";
    $like = "%$buscar%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$sql = "SELECT DISTINCT u.id, u.nombre, u.apellido, u.carrera, u.semestre,
        COALESCE(AVG(c.calificacion),0) AS promedio,
        COUNT(DISTINCT c.id) AS total_resenas
        FROM usuarios u
        LEFT JOIN tutor_materias tm ON tm.tutor_id = u.id
        LEFT JOIN horarios_tutor h ON h.tutor_id = u.id
        LEFT JOIN comentarios c ON c.tutor_id = u.id
        $where
        GROUP BY u.id
        ORDER BY promedio DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tutores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Materias para filtro
$materias = $conn->query("SELECT * FROM materias WHERE activa=1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

// Estadísticas hero
$stats = $conn->query("
    SELECT
      (SELECT COUNT(*) FROM usuarios WHERE rol='tutor' AND activo=1) AS tutores,
      (SELECT COUNT(*) FROM materias WHERE activa=1) AS materias,
      (SELECT COUNT(*) FROM tutorias WHERE MONTH(fecha)=MONTH(NOW()) AND YEAR(fecha)=YEAR(NOW())) AS sesiones
")->fetch_assoc();

$conn->close();

include 'includes/header.php';
?>

<!-- HERO -->
<div class="hero">
  <div class="hero-inner">
    <div>
      <h1>Agenda tu Tutoría</h1>
      <p>Encuentra el tutor ideal y reserva tu sesión en minutos.</p>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-num"><?= $stats['tutores'] ?></div><div class="stat-label">Tutores activos</div></div>
      <div class="stat"><div class="stat-num"><?= $stats['materias'] ?></div><div class="stat-label">Materias</div></div>
      <div class="stat"><div class="stat-num"><?= $stats['sesiones'] ?></div><div class="stat-label">Sesiones este mes</div></div>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="tabs-bar">
  <div class="tab active">🔍 Ver Tutores</div>
  <a href="mis-tutorias.php" class="tab">📅 Mis Tutorías</a>
</div>

<!-- MAIN -->
<div class="main">

  <!-- FILTROS -->
  <div class="sidebar">
    <div class="card">
      <div class="card-title">Filtros</div>
      <form method="GET">
        <div class="filter-group">
          <label class="filter-label">Buscar tutor</label>
          <input class="filter-input" type="text" name="buscar" placeholder="Nombre o apellido…" value="<?= htmlspecialchars($buscar) ?>"/>
        </div>
        <div class="filter-group">
          <label class="filter-label">Materia</label>
          <select class="filter-select" name="materia">
            <option value="0">Todas las materias</option>
            <?php foreach ($materias as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $filtro_materia==$m['id']?'selected':'' ?>>
                <?= htmlspecialchars($m['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Modalidad</label>
          <select class="filter-select" name="modalidad">
            <option value="">Cualquiera</option>
            <option value="presencial" <?= $filtro_modalidad=='presencial'?'selected':'' ?>>Presencial</option>
            <option value="virtual"    <?= $filtro_modalidad=='virtual'?'selected':'' ?>>Virtual</option>
          </select>
        </div>
        <button type="submit" class="btn-filter">Aplicar filtros</button>
        <?php if ($filtro_materia || $filtro_modalidad || $buscar): ?>
          <a href="index.php" class="btn-filter" style="display:block;text-align:center;text-decoration:none;margin-top:8px;background:var(--gray);color:var(--text);">Limpiar filtros</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- LISTA DE TUTORES -->
  <div class="content">
    <div class="content-header">
      <div>
        <div class="content-title">Tutores Disponibles</div>
        <div class="content-count">Mostrando <?= count($tutores) ?> tutor(es)</div>
      </div>
    </div>

    <?php if (empty($tutores)): ?>
      <div class="card" style="text-align:center;padding:40px;">
        <p style="font-size:40px;margin-bottom:12px;">🔍</p>
        <p style="font-weight:600;">No se encontraron tutores con esos filtros.</p>
        <a href="index.php" style="color:var(--green);">Ver todos</a>
      </div>
    <?php else: ?>
    <div class="tutor-grid">
      <?php foreach ($tutores as $t): ?>
        <?php
          $conn2 = conectar();
          // Materias del tutor
          $mats = $conn2->prepare("SELECT m.nombre FROM materias m JOIN tutor_materias tm ON tm.materia_id=m.id WHERE tm.tutor_id=?");
          $mats->bind_param("i", $t['id']);
          $mats->execute();
          $materias_tutor = $mats->get_result()->fetch_all(MYSQLI_ASSOC);

          // Próximo horario
          $hor = $conn2->prepare("SELECT dia_semana, hora_inicio, hora_fin, sala, modalidad FROM horarios_tutor WHERE tutor_id=? LIMIT 1");
          $hor->bind_param("i", $t['id']);
          $hor->execute();
          $horario = $hor->get_result()->fetch_assoc();
          $conn2->close();

          $iniciales = strtoupper(substr($t['nombre'],0,1).substr($t['apellido'],0,1));
          $prom = round($t['promedio'],1);
          $estrellas = str_repeat('★', $prom) . str_repeat('☆', 5-$prom);
        ?>
        <div class="tutor-card">
          <div class="tutor-header">
            <div class="tutor-avatar"><?= $iniciales ?></div>
            <div class="tutor-info">
              <div class="tutor-name"><?= htmlspecialchars($t['nombre'].' '.$t['apellido']) ?></div>
              <div class="tutor-dept"><?= htmlspecialchars($t['carrera']) ?> · Sem. <?= $t['semestre'] ?></div>
              <div class="tutor-rating">
                <span class="stars"><?= $estrellas ?></span>
                <span class="rating-num"><?= $prom > 0 ? $prom : 'Nuevo' ?></span>
                <span class="rating-count">(<?= $t['total_resenas'] ?> reseñas)</span>
              </div>
            </div>
            <span class="badge badge-available">Disponible</span>
          </div>

          <div class="tutor-subjects">
            <?php foreach (array_slice($materias_tutor, 0, 4) as $mat): ?>
              <span class="subject-tag"><?= htmlspecialchars($mat['nombre']) ?></span>
            <?php endforeach; ?>
          </div>

          <?php if ($horario): ?>
          <div class="tutor-meta">
            <span>📍 <?= htmlspecialchars($horario['sala'] ?? 'Por definir') ?></span>
            <span>🕐 <?= $horario['dia_semana'] ?> <?= substr($horario['hora_inicio'],0,5) ?>–<?= substr($horario['hora_fin'],0,5) ?></span>
            <span><?= $horario['modalidad'] === 'virtual' ? '💻' : ($horario['modalidad']==='ambas'?'💻🏫':'🏫') ?> <?= ucfirst($horario['modalidad']) ?></span>
          </div>
          <?php endif; ?>

          <div class="tutor-footer">
            <button class="btn-outline" onclick="verPerfil(<?= $t['id'] ?>)">Ver perfil</button>
            <button class="btn-primary" onclick="abrirModal(<?= $t['id'] ?>, '<?= addslashes($t['nombre'].' '.$t['apellido']) ?>', '<?= $iniciales ?>', '<?= addslashes($t['carrera']) ?>')">
              Agendar
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL AGENDAR -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <div class="modal-header">
      <h2>Agendar Tutoría</h2>
      <button class="modal-close" onclick="cerrarModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-tutor">
        <div id="modalAvatar" class="tutor-avatar" style="width:48px;height:48px;font-size:15px;flex-shrink:0"></div>
        <div>
          <div class="modal-tutor-name" id="modalNombre"></div>
          <div class="modal-tutor-dept" id="modalCarrera"></div>
        </div>
      </div>

      <form id="formAgendar" method="POST" action="php/agendar.php">
        <input type="hidden" name="tutor_id" id="inputTutorId"/>

        <div class="form-group">
          <label class="form-label">Materia</label>
          <select name="materia_id" id="selectMateria" class="form-control" required>
            <option value="">Cargando materias…</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Fecha</label>
          <input type="date" name="fecha" id="inputFecha" class="form-control" required
                 min="<?= date('Y-m-d', strtotime('+1 day')) ?>"/>
        </div>
        <div class="form-group">
          <label class="form-label">Hora</label>
          <select name="hora" class="form-control" required>
            <option value="">Selecciona una hora</option>
            <?php for ($h=7; $h<=19; $h++): ?>
              <option value="<?= sprintf('%02d:00', $h) ?>"><?= sprintf('%02d:00', $h) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Modalidad</label>
          <select name="modalidad" class="form-control" required>
            <option value="presencial">Presencial</option>
            <option value="virtual">Virtual (Meet)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Notas para el tutor (opcional)</label>
          <textarea name="notas" class="form-control" rows="3" placeholder="Describe qué necesitas trabajar…"></textarea>
        </div>

        <div id="mensajeModal" class="alert" style="display:none"></div>

        <div class="modal-footer" style="padding:0;border:none;margin-top:8px;">
          <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
          <button type="submit" class="btn-confirm">✓ Confirmar Tutoría</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="js/main.js"></script>
<script>
function abrirModal(tutorId, nombre, iniciales, carrera) {
  document.getElementById('inputTutorId').value = tutorId;
  document.getElementById('modalNombre').textContent = nombre;
  document.getElementById('modalCarrera').textContent = carrera;
  document.getElementById('modalAvatar').textContent = iniciales;
  document.getElementById('mensajeModal').style.display = 'none';

  // Cargar materias del tutor vía AJAX
  fetch('php/get_materias_tutor.php?tutor_id=' + tutorId)
    .then(r => r.json())
    .then(data => {
      const sel = document.getElementById('selectMateria');
      sel.innerHTML = '<option value="">Selecciona la materia…</option>';
      data.forEach(m => {
        sel.innerHTML += `<option value="${m.id}">${m.nombre}</option>`;
      });
    });

  document.getElementById('modalOverlay').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}

document.getElementById('formAgendar').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const data = new FormData(form);

  fetch('php/agendar.php', { method: 'POST', body: data })
    .then(r => r.json())
    .then(res => {
      const msg = document.getElementById('mensajeModal');
      if (res.ok) {
        cerrarModal();
        showToast('✅ ¡Tutoría agendada correctamente!');
        form.reset();
      } else {
        msg.style.display = 'block';
        msg.className = 'alert alert-error';
        msg.textContent = res.mensaje;
      }
    });
});

function verPerfil(tutorId) {
  window.location.href = 'perfil-tutor.php?id=' + tutorId;
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});
</script>

</body>
</html>
