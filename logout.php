<?php
require_once 'includes/sesion.php';
session_destroy();
header("Location: login.php");
exit();
