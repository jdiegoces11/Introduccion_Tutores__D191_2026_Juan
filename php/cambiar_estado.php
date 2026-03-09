<?php
// php/cambiar_estado.php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereLogin();

$tutoria_id = intval($_POST['tutoria_id'] ?? 0);
$estado     = $_POST['estado'] ?? '';
$redirect   = $_POST['redirect'] ?? 'index.php';
$validos    = ['pendiente','confirmada','completada','cancelada'];

if ($tutoria_id && in_array($estado, $validos)) {
    $conn = conectar();
    // Tutor solo puede cambiar sus propias citas; admin puede todas
    if ($_SESSION['rol'] === 'tutor') {
        $stmt = $conn->prepare("UPDATE tutorias SET estado=? WHERE id=? AND tutor_id=?");
        $stmt->bind_param("sii", $estado, $tutoria_id, $_SESSION['usuario_id']);
    } else {
        $stmt = $conn->prepare("UPDATE tutorias SET estado=? WHERE id=?");
        $stmt->bind_param("si", $estado, $tutoria_id);
    }
    $stmt->execute();
    $conn->close();
}
header("Location: ../$redirect");
exit();
