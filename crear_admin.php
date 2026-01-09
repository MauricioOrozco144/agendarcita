<?php
include 'config/db.php';

// --- CONFIGURA TUS DATOS AQUÍ ---
$nombre_usuario = 'admin'; // El nombre que quieras para entrar
$password_clara = 'admin1234'; // La contraseña que quieras usar
// --------------------------------

// Generamos el Hash (la versión segura y encriptada)
$password_encriptada = password_hash($password_clara, PASSWORD_DEFAULT);

// Insertamos el usuario en la tabla
$sql = "INSERT INTO usuarios (usuario, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nombre_usuario, $password_encriptada);

if ($stmt->execute()) {
    echo "<h2>✅ ¡Usuario creado con éxito!</h2>";
    echo "<b>Usuario:</b> " . htmlspecialchars($nombre_usuario) . "<br>";
    echo "<b>Contraseña:</b> (La que elegiste arriba)<br><br>";
    echo "<span style='color:red'>⚠️ IMPORTANTE: Borra este archivo (crear_admin.php) de tu servidor ahora mismo.</span>";
} else {
    echo "❌ Error al crear el usuario: " . $conn->error;
}

$stmt->close();
?>