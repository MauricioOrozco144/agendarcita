<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitización de datos
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre_cliente']);
    $estrellas = intval($_POST['estrellas']);
    $comentario = mysqli_real_escape_string($conn, $_POST['comentario']);

    // 2. Inserción incluyendo la función NOW() para la fecha
    $sql = "INSERT INTO resenas (nombre_cliente, estrellas, comentario, fecha) 
            VALUES ('$nombre', $estrellas, '$comentario', NOW())";

    if (mysqli_query($conn, $sql)) {
        // 3. Redirección con mensaje de éxito
        echo "<script>
                alert('¡Gracias por tu opinión! Tu reseña ha sido publicada.');
                window.location.href = 'index.php#reseñas';
              </script>";
    } else {
        echo "Error al publicar la reseña: " . mysqli_error($conn);
    }
}
?>