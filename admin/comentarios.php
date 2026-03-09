<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('admin');
$titulo='Comentarios';
$conn=conectar();
$coms=$conn->query("
    SELECT c.*, est.nombre AS est_nom, est.apellido AS est_ape,
           tur.nombre AS tur_nom, tur.apellido AS tur_ape,
           m.nombre AS materia, t.fecha
    FROM comentarios c
    JOIN usuarios est ON est.id=c.estudiante_id
    JOIN usuarios tur ON tur.id=c.tutor_id
    JOIN tutorias t ON t.id=c.tutoria_id
    JOIN materias m ON m.id=t.materia_id
    ORDER BY c.creado_en DESC
")->fetch_all(MYSQLI_ASSOC);
$conn->close();
$base='../';
include '../includes/header.php';
?>
<div class="hero" style="padding:32px 40px;"><div class="hero-inner"><div><h1>💬 Todos los Comentarios</h1><p>Reseñas y calificaciones de sesiones completadas.</p></div></div></div>
<div style="max-width:1000px;margin:32px auto;padding:0 40px;display:grid;gap:16px;">
  <?php if(empty($coms)): ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--text-muted);">No hay comentarios aún.</div>
  <?php else: ?>
  <?php foreach($coms as $c): ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
      <div>
        <strong><?=htmlspecialchars($c['est_nom'].' '.$c['est_ape'])?></strong>
        <span style="color:var(--text-muted);font-size:13px;"> sobre <?=htmlspecialchars($c['tur_nom'].' '.$c['tur_ape'])?></span>
        <div style="font-size:12px;color:var(--text-muted);"><?=htmlspecialchars($c['materia'])?> · <?=date('d/m/Y',strtotime($c['fecha']))?></div>
      </div>
      <span style="color:#f0a500;font-size:18px;"><?=str_repeat('★',$c['calificacion']).$c['calificacion'].'/5'?></span>
    </div>
    <?php if($c['comentario']): ?><p style="font-size:13px;margin-bottom:8px;">"<?=htmlspecialchars($c['comentario'])?>"</p><?php endif;?>
    <?php if($c['respuesta_tutor']): ?><div style="font-size:12px;color:var(--green);margin-bottom:4px;">🎓 Tutor: <?=htmlspecialchars($c['respuesta_tutor'])?></div><?php endif;?>
    <?php if($c['respuesta_docente']): ?>
      <div style="font-size:12px;color:#666;">📝 Docente: <?=htmlspecialchars($c['respuesta_docente'])?></div>
    <?php else: ?>
      <form method="POST" action="../php/responder_comentario.php" style="margin-top:10px;display:flex;gap:8px;">
        <input type="hidden" name="comentario_id" value="<?=$c['id']?>"/>
        <input type="hidden" name="tipo" value="docente"/>
        <input type="hidden" name="redirect" value="admin/comentarios.php"/>
        <input type="text" name="respuesta" class="form-control" placeholder="Observación del docente…" style="font-size:12px;"/>
        <button type="submit" class="btn-primary" style="padding:8px 14px;font-size:12px;white-space:nowrap;">Comentar</button>
      </form>
    <?php endif;?>
  </div>
  <?php endforeach;?>
  <?php endif;?>
</div>
<script src="../js/main.js"></script>
</body></html>
