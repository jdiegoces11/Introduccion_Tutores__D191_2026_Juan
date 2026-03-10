<?php

// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');       
// define('DB_PASS', '');            
// define('DB_NAME', 'tutorias_db');

define('DB_HOST', 'sql306.infinityfree.com');
define('DB_USER', 'if0_41350252');
define('DB_PASS', 'zHVUka8yc2X');
define('DB_NAME', 'if0_41350252_tutorias');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
