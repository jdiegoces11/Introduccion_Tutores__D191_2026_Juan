<?php
// =============================================
// CONEXIÓN A LA BASE DE DATOS
// Cambia estos datos según tu hosting
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // En InfinityFree cambia esto
define('DB_PASS', '');            // En InfinityFree cambia esto
define('DB_NAME', 'tutorias_db');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
