<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('tutor');
$titulo = 'Mis Citas';
$u = usuarioActual();
$conn = conectar();
$stmt = $conn->prepare("
    SELECT t.*, est.nombre AS est_nom, est.apellido AS est_ape, m.nombre AS materia
    FROM tutorias t
    JOIN usuarios est ON est.id=t.estudiante_id
    JOIN materias m ON m.id=t.materia_id
    WHERE t.tutor_id=?
    ORDER BY t.fecha DESC, t.hora DESC
");
$stmt->bind_param("i",$u['id']);
$stmt->execute();
$citas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
$base='../';
include '../includes/header.php';
?>
<div class="hero" style="padding:32px 40px;">
  <div class="hero-inner"><div><h1>📋 Todas mis citas</h1><p>Historial completo de sesiones de tutoría.</p></div></div>
</div>
<div style="max-width:1100px;margin:32px auto;padding:0 40px;">
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--green-pale);">
          <th style="padding:14px 16px;text-align:left;color:var(--green);">Estudiante</th>
          <th style="padding:14px 16px;text-align:left;">Materia</th>
          <th style="padding:14px 16px;text-align:left;">Fecha</th>
          <th style="padding:14px 16px;text-align:left;">Hora</th>
          <th style="padding:14px 16px;text-align:left;">Modalidad</th>
          <th style="padding:14px 16px;text-align:left;">Estado</th>
          <th style="padding:14px 16px;text-align:left;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($citas as $c): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:12px 16px;font-weight:600;"><?=htmlspecialchars($c['est_nom'].' '.$c['est_ape'])?></td>
          <td style="padding:12px 16px;"><?=htmlspecialchars($c['materia'])?></td>
          <td style="padding:12px 16px;"><?=date('d/m/Y',strtotime($c['fecha']))?></td>
          <td style="padding:12px 16px;"><?=substr($c['hora'],0,5)?></td>
          <td style="padding:12px 16px;"><?=ucfirst($c['modalidad'])?></td>
          <td style="padding:12px 16px;"><span class="badge <?=$c['estado']==='completada'?'badge-available':($c['estado']==='cancelada'?'badge-busy':'')?>"><?=ucfirst($c['estado'])?></span></td>
          <td style="padding:12px 16px;">
            <form method="POST" action="../php/cambiar_estado.php" style="display:flex;gap:6px;">
              <input type="hidden" name="tutoria_id" value="<?=$c['id']?>"/>
              <input type="hidden" name="redirect" value="tutor/citas.php"/>
              <select name="estado" class="filter-select" style="font-size:12px;padding:5px 8px;">
                <option value="pendiente" <?=$c['estado']==='pendiente'?'selected':''?>>Pendiente</option>
                <option value="confirmada" <?=$c['estado']==='confirmada'?'selected':''?>>Confirmada</option>
                <option value="completada" <?=$c['estado']==='completada'?'selected':''?>>Completada</option>
                <option value="cancelada" <?=$c['estado']==='cancelada'?'selected':''?>>Cancelada</option>
              </select>
              <button type="submit" class="btn-primary" style="padding:5px 12px;font-size:12px;">Guardar</button>
            </form>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<script src="../js/main.js"></script>
</body></html>
