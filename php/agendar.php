<?php
// php/agendar.php
require_once '../includes/conexion.php';
require_once '../includes/sesion.php';
header('Content-Type: application/json');

if (!estaLogueado()) {
    echo json_encode(['ok'=>false,'mensaje'=>'Sesión expirada. Por favor inicia sesión.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'mensaje'=>'Método no permitido.']);
    exit();
}

$estudiante_id = $_SESSION['usuario_id'];
$tutor_id      = intval($_POST['tutor_id'] ?? 0);
$materia_id    = intval($_POST['materia_id'] ?? 0);
$fecha         = $_POST['fecha'] ?? '';
$hora          = $_POST['hora'] ?? '';
$modalidad     = $_POST['modalidad'] ?? 'presencial';
$notas         = trim($_POST['notas'] ?? '');

// Validaciones
if (!$tutor_id || !$materia_id || !$fecha || !$hora) {
    echo json_encode(['ok'=>false,'mensaje'=>'Completa todos los campos obligatorios.']);
    exit();
}

if (strtotime($fecha) < strtotime('today')) {
    echo json_encode(['ok'=>false,'mensaje'=>'La fecha debe ser hoy o en el futuro.']);
    exit();
}

$conn = conectar();

// Verificar que no exista ya esa cita
$check = $conn->prepare("SELECT id FROM tutorias WHERE tutor_id=? AND fecha=? AND hora=? AND estado NOT IN ('cancelada')");
$check->bind_param("iss", $tutor_id, $fecha, $hora);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['ok'=>false,'mensaje'=>'Ese tutor ya tiene una cita en esa fecha y hora. Elige otro horario.']);
    $conn->close();
    exit();
}

// Insertar la tutoría
$stmt = $conn->prepare("INSERT INTO tutorias (estudiante_id, tutor_id, materia_id, fecha, hora, modalidad, notas) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("iiissss", $estudiante_id, $tutor_id, $materia_id, $fecha, $hora, $modalidad, $notas);

if ($stmt->execute()) {
    echo json_encode(['ok'=>true,'mensaje'=>'Tutoría agendada correctamente.']);
} else {
    echo json_encode(['ok'=>false,'mensaje'=>'Error al guardar. Intenta de nuevo.']);
}

$conn->close();
