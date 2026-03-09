<?php
// =============================================
// FUNCIONES DE SESIÓN Y AUTENTICACIÓN
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Redirige si NO está logueado
function requiereLogin() {
    if (!estaLogueado()) {
        header("Location: login.php");
        exit();
    }
}

// Redirige si NO tiene el rol requerido
function requiereRol($rol) {
    requiereLogin();
    if ($_SESSION['rol'] !== $rol) {
        header("Location: index.php");
        exit();
    }
}

// Datos del usuario en sesión
function usuarioActual() {
    return [
        'id'     => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? '',
        'rol'    => $_SESSION['rol'] ?? '',
        'correo' => $_SESSION['correo'] ?? '',
    ];
}

// Iniciales para el avatar
function iniciales($nombre, $apellido) {
    return strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
}
?>
