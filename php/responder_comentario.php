<?php
// php/responder_comentario.php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
requiereLogin();

$comentario_id = intval($_POST['comentario_id'] ?? 0);
$respuesta     = trim($_POST['respuesta'] ?? '');
$tipo          = $_POST['tipo'] ?? 'tutor'; // 'tutor' o 'docente'
$redirect      = $_POST['redirect'] ?? 'index.php';

if ($comentario_id && $respuesta) {
    $conn = conectar();
    if ($tipo === 'docente' && $_SESSION['rol'] === 'admin') {
        $stmt = $conn->prepare("UPDATE comentarios SET respuesta_docente=? WHERE id=?");
    } else {
        $stmt = $conn->prepare("UPDATE comentarios SET respuesta_tutor=? WHERE id=? AND tutor_id=?");
        // Para tutor verificar que es su comentario
        if ($_SESSION['rol'] === 'tutor') {
            $stmt = $conn->prepare("UPDATE comentarios SET respuesta_tutor=? WHERE id=? AND tutor_id=?");
            $stmt->bind_param("sii", $respuesta, $comentario_id, $_SESSION['usuario_id']);
            $stmt->execute();
            $conn->close();
            header("Location: ../$redirect");
            exit();
        }
    }
    $stmt->bind_param("si", $respuesta, $comentario_id);
    $stmt->execute();
    $conn->close();
}
header("Location: ../$redirect");
exit();
