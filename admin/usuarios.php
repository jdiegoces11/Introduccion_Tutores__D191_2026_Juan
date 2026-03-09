<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('admin');
$titulo='Usuarios';
$conn=conectar();

// Toggle activo
if(isset($_POST['accion']) && $_POST['accion']==='toggle' && isset($_POST['uid'])){
    $uid=intval($_POST['uid']);
    $conn->query("UPDATE usuarios SET activo = NOT activo WHERE id=$uid");
}

$usuarios=$conn->query("SELECT * FROM usuarios ORDER BY rol,nombre")->fetch_all(MYSQLI_ASSOC);
$conn->close();
$base='../';
include '../includes/header.php';
?>
<div class="hero" style="padding:32px 40px;"><div class="hero-inner"><div><h1>👥 Usuarios</h1><p>Gestión de estudiantes, tutores y administradores.</p></div><a href="panel.php" class="btn-confirm" style="text-decoration:none;padding:12px 24px;">← Panel</a></div></div>
<div style="max-width:1100px;margin:32px auto;padding:0 40px;">
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead><tr style="background:var(--green-pale);">
        <th style="padding:14px 16px;text-align:left;color:var(--green);">Nombre</th>
        <th style="padding:14px 16px;text-align:left;">Correo</th>
        <th style="padding:14px 16px;text-align:left;">Rol</th>
        <th style="padding:14px 16px;text-align:left;">Carrera</th>
        <th style="padding:14px 16px;text-align:left;">Estado</th>
        <th style="padding:14px 16px;text-align:left;">Acción</th>
      </tr></thead>
      <tbody>
        <?php foreach($usuarios as $u): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:11px 16px;font-weight:600;"><?=htmlspecialchars($u['nombre'].' '.$u['apellido'])?></td>
          <td style="padding:11px 16px;color:var(--text-muted);"><?=htmlspecialchars($u['correo'])?></td>
          <td style="padding:11px 16px;"><span class="badge <?=$u['rol']==='admin'?'badge-busy':($u['rol']==='tutor'?'badge-available':'')?>"> <?=ucfirst($u['rol'])?></span></td>
          <td style="padding:11px 16px;"><?=htmlspecialchars($u['carrera']??'-')?></td>
          <td style="padding:11px 16px;"><span style="color:<?=$u['activo']?'var(--green)':'#c0392b'?>;font-weight:600;"><?=$u['activo']?'Activo':'Inactivo'?></span></td>
          <td style="padding:11px 16px;">
            <form method="POST">
              <input type="hidden" name="accion" value="toggle"/>
              <input type="hidden" name="uid" value="<?=$u['id']?>"/>
              <button type="submit" class="btn-outline" style="padding:5px 12px;font-size:12px;"><?=$u['activo']?'Desactivar':'Activar'?></button>
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
