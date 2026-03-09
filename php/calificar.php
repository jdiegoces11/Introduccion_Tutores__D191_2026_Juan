<?php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
header('Content-Type: application/json');

if (!estaLogueado()) { echo json_encode(['ok'=>false,'mensaje'=>'Sesión expirada.']); exit(); }

$tutoria_id   = intval($_POST['tutoria_id'] ?? 0);
$calificacion = intval($_POST['calificacion'] ?? 0);
$comentario   = trim($_POST['comentario'] ?? '');
$estudiante_id = $_SESSION['usuario_id'];

if (!$tutoria_id || $calificacion < 1 || $calificacion > 5) {
    echo json_encode(['ok'=>false,'mensaje'=>'Datos inválidos.']); exit();
}

$conn = conectar();

// Verificar que la tutoría pertenece al estudiante y está completada
$check = $conn->prepare("SELECT tutor_id FROM tutorias WHERE id=? AND estudiante_id=? AND estado='completada'");
$check->bind_param("ii", $tutoria_id, $estudiante_id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if (!$res) { echo json_encode(['ok'=>false,'mensaje'=>'Tutoría no válida.']); exit(); }

$tutor_id = $res['tutor_id'];

$stmt = $conn->prepare("INSERT INTO comentarios (tutoria_id, estudiante_id, tutor_id, calificacion, comentario) VALUES (?,?,?,?,?)
                        ON DUPLICATE KEY UPDATE calificacion=VALUES(calificacion), comentario=VALUES(comentario)");
$stmt->bind_param("iiiis", $tutoria_id, $estudiante_id, $tutor_id, $calificacion, $comentario);

if ($stmt->execute()) {
    echo json_encode(['ok'=>true,'mensaje'=>'Calificación guardada.']);
} else {
    echo json_encode(['ok'=>false,'mensaje'=>'Error al guardar.']);
}
$conn->close();
