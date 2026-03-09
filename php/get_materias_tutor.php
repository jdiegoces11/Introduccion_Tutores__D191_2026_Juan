<?php
// php/get_materias_tutor.php
require_once '../includes/conexion.php';
header('Content-Type: application/json');

$tutor_id = intval($_GET['tutor_id'] ?? 0);
if (!$tutor_id) { echo json_encode([]); exit(); }

$conn = conectar();
$stmt = $conn->prepare("SELECT m.id, m.nombre FROM materias m JOIN tutor_materias tm ON tm.materia_id=m.id WHERE tm.tutor_id=? ORDER BY m.nombre");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

echo json_encode($result);
