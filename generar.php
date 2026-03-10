<?php
$pass_admin = password_hash('admin123', PASSWORD_DEFAULT);
$pass_tutor = password_hash('tutor123', PASSWORD_DEFAULT);
$pass_est   = password_hash('est123',   PASSWORD_DEFAULT);

require_once 'includes/conexion.php';
$conn = conectar();

$conn->query("UPDATE usuarios SET contrasena='$pass_admin' WHERE correo='admin@uts.edu.co'");
$conn->query("UPDATE usuarios SET contrasena='$pass_tutor' WHERE correo='carlos@uts.edu.co'");
$conn->query("UPDATE usuarios SET contrasena='$pass_tutor' WHERE correo='laura@uts.edu.co'");
$conn->query("UPDATE usuarios SET contrasena='$pass_tutor' WHERE correo='jorge@uts.edu.co'");
$conn->query("UPDATE usuarios SET contrasena='$pass_tutor' WHERE correo='valentina@uts.edu.co'");
$conn->query("UPDATE usuarios SET contrasena='$pass_est'   WHERE correo='ana@uts.edu.co'");
$conn->close();

echo "✅ Listo. <a href='login.php'>Clic aquí para ingresar</a>";
?>
