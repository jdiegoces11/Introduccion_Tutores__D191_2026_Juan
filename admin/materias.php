<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereRol('admin');
$titulo='Materias';
$conn=conectar();
$msg='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    if($_POST['accion']==='agregar'){
        $nom=trim($_POST['nombre']); $cod=trim($_POST['codigo']);
        if($nom){
            $s=$conn->prepare("INSERT INTO materias(nombre,codigo) VALUES(?,?)");
            $s->bind_param("ss",$nom,$cod); $s->execute();
            $msg='Materia agregada.';
        }
    } elseif($_POST['accion']==='toggle'){
        $mid=intval($_POST['mid']);
        $conn->query("UPDATE materias SET activa=NOT activa WHERE id=$mid");
    }
}

$materias=$conn->query("SELECT * FROM materias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$conn->close();
$base='../';
include '../includes/header.php';
?>
<div class="hero" style="padding:32px 40px;"><div class="hero-inner"><div><h1>📚 Materias</h1><p>Administra las materias disponibles para tutoría.</p></div></div></div>
<div style="max-width:900px;margin:32px auto;padding:0 40px;display:grid;grid-template-columns:1fr 300px;gap:24px;">
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead><tr style="background:var(--green-pale);"><th style="padding:14px 16px;text-align:left;color:var(--green);">Materia</th><th style="padding:14px 16px;">Código</th><th style="padding:14px 16px;">Estado</th><th style="padding:14px 16px;">Acción</th></tr></thead>
      <tbody>
        <?php foreach($materias as $m): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:11px 16px;font-weight:600;"><?=htmlspecialchars($m['nombre'])?></td>
          <td style="padding:11px 16px;text-align:center;color:var(--text-muted);"><?=htmlspecialchars($m['codigo']??'')?></td>
          <td style="padding:11px 16px;text-align:center;"><span style="color:<?=$m['activa']?'var(--green)':'#c0392b'?>;font-weight:600;"><?=$m['activa']?'Activa':'Inactiva'?></span></td>
          <td style="padding:11px 16px;text-align:center;">
            <form method="POST"><input type="hidden" name="accion" value="toggle"/><input type="hidden" name="mid" value="<?=$m['id']?>"/>
            <button type="submit" class="btn-outline" style="padding:5px 10px;font-size:12px;"><?=$m['activa']?'Desactivar':'Activar'?></button></form>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <div class="card-title">Agregar materia</div>
    <?php if($msg): ?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
    <form method="POST">
      <input type="hidden" name="accion" value="agregar"/>
      <div class="form-group"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" required placeholder="Ej: Cálculo III"/></div>
      <div class="form-group"><label class="form-label">Código (opcional)</label><input type="text" name="codigo" class="form-control" placeholder="Ej: MAT303"/></div>
      <button type="submit" class="btn-confirm" style="width:100%;">+ Agregar</button>
    </form>
  </div>
</div>
<script src="../js/main.js"></script>
</body></html>
