<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('admin');
$titulo='Gestión de Tutorías';
$conn=conectar();
$filtro_estado=$_GET['estado']??'';
$where=$filtro_estado?"WHERE t.estado='$filtro_estado'":'';
$tutorias=$conn->query("
    SELECT t.*, est.nombre AS est_nom, est.apellido AS est_ape,
           tur.nombre AS tur_nom, tur.apellido AS tur_ape, m.nombre AS materia
    FROM tutorias t JOIN usuarios est ON est.id=t.estudiante_id
    JOIN usuarios tur ON tur.id=t.tutor_id JOIN materias m ON m.id=t.materia_id
    $where ORDER BY t.fecha DESC, t.hora DESC
")->fetch_all(MYSQLI_ASSOC);
$conn->close();
$base='../';
include '../includes/header.php';
?>
<div class="hero" style="padding:32px 40px;"><div class="hero-inner"><div><h1>📅 Todas las Tutorías</h1><p>Gestión completa de sesiones.</p></div></div></div>
<div class="tabs-bar">
  <a href="panel.php" class="tab">← Panel</a>
  <a href="tutorias.php" class="tab <?=!$filtro_estado?'active':''?>">Todas</a>
  <a href="tutorias.php?estado=pendiente" class="tab <?=$filtro_estado==='pendiente'?'active':''?>">Pendientes</a>
  <a href="tutorias.php?estado=confirmada" class="tab <?=$filtro_estado==='confirmada'?'active':''?>">Confirmadas</a>
  <a href="tutorias.php?estado=completada" class="tab <?=$filtro_estado==='completada'?'active':''?>">Completadas</a>
  <a href="tutorias.php?estado=cancelada" class="tab <?=$filtro_estado==='cancelada'?'active':''?>">Canceladas</a>
</div>
<div style="max-width:1200px;margin:32px auto;padding:0 40px;">
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead><tr style="background:var(--green-pale);">
        <th style="padding:14px 16px;text-align:left;color:var(--green);">Estudiante</th>
        <th style="padding:14px 16px;text-align:left;">Tutor</th>
        <th style="padding:14px 16px;text-align:left;">Materia</th>
        <th style="padding:14px 16px;text-align:left;">Fecha / Hora</th>
        <th style="padding:14px 16px;text-align:left;">Modalidad</th>
        <th style="padding:14px 16px;text-align:left;">Estado</th>
        <th style="padding:14px 16px;text-align:left;">Acción</th>
      </tr></thead>
      <tbody>
        <?php foreach($tutorias as $t): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:11px 16px;"><?=htmlspecialchars($t['est_nom'].' '.$t['est_ape'])?></td>
          <td style="padding:11px 16px;"><?=htmlspecialchars($t['tur_nom'].' '.$t['tur_ape'])?></td>
          <td style="padding:11px 16px;"><?=htmlspecialchars($t['materia'])?></td>
          <td style="padding:11px 16px;"><?=date('d/m/Y',strtotime($t['fecha']))?> <?=substr($t['hora'],0,5)?></td>
          <td style="padding:11px 16px;"><?=ucfirst($t['modalidad'])?></td>
          <td style="padding:11px 16px;"><span class="badge <?=$t['estado']==='completada'?'badge-available':($t['estado']==='cancelada'?'badge-busy':'')?>"><?=ucfirst($t['estado'])?></span></td>
          <td style="padding:11px 16px;">
            <form method="POST" action="../php/cambiar_estado.php" style="display:flex;gap:6px;">
              <input type="hidden" name="tutoria_id" value="<?=$t['id']?>"/>
              <input type="hidden" name="redirect" value="admin/tutorias.php"/>
              <select name="estado" class="filter-select" style="font-size:12px;padding:5px;">
                <option value="pendiente" <?=$t['estado']==='pendiente'?'selected':''?>>Pendiente</option>
                <option value="confirmada" <?=$t['estado']==='confirmada'?'selected':''?>>Confirmada</option>
                <option value="completada" <?=$t['estado']==='completada'?'selected':''?>>Completada</option>
                <option value="cancelada" <?=$t['estado']==='cancelada'?'selected':''?>>Cancelada</option>
              </select>
              <button type="submit" class="btn-primary" style="padding:5px 12px;font-size:12px;">OK</button>
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
