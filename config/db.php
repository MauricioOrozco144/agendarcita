<?php
// Configuración de parámetros
$host = "localhost";
$user = "root";
$pass = "";
$db   = "agendarcita";

// Creación de la conexión
$conn = mysqli_connect($host, $user, $pass, $db);

// Verificación de la conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer el conjunto de caracteres
mysqli_set_charset($conn, "utf8");

// NOTA: No cerramos la etiqueta ?> 