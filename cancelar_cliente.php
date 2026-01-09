<?php
include 'config/db.php';

$mensaje = "";
$procesado = false;

if (isset($_GET['id'])) {
    $id_cita = intval($_GET['id']);
    
    // 1. Verificamos si la cita existe y no ha pasado la fecha
    $consulta = mysqli_query($conn, "SELECT c.*, s.nombre_servicio FROM citas c 
                                     JOIN servicios s ON c.id_servicio = s.id 
                                     WHERE c.id = $id_cita");
    $cita = mysqli_fetch_assoc($consulta);

    if ($cita) {
        if ($cita['estado'] == 'Cancelada') {
            $mensaje = "Esta cita ya había sido cancelada anteriormente.";
            $procesado = true;
        } else {
            // 2. Procesar la cancelación
            $update = mysqli_query($conn, "UPDATE citas SET estado = 'Cancelada' WHERE id = $id_cita");
            if ($update) {
                $mensaje = "Tu cita para <strong>" . $cita['nombre_servicio'] . "</strong> ha sido cancelada con éxito. ¡Esperamos verte pronto!<br><br>Si necesitas reprogramar o tienes dudas, contáctanos por WhatsApp: <strong>+57 318 8623371</strong>";
                $procesado = true;
            } else {
                $mensaje = "Hubo un error al procesar la cancelación. Por favor intenta más tarde.";
            }
        }
    } else {
        $mensaje = "No pudimos encontrar la información de esta cita.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Cita | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #fdf5f6; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .cancel-card { background: white; border-radius: 30px; padding: 40px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.05); max-width: 450px; border: 1px solid #eee1e3; }
        .icon-circle { width: 80px; height: 80px; background: #fff5f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e74c3c; font-size: 2.5rem; }
        .btn-gold { background: #c5a059; color: white; border-radius: 12px; padding: 12px 30px; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-gold:hover { background: #b08d4a; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="cancel-card">
    <div class="icon-circle">
        <i class="bi <?php echo ($procesado) ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?>"></i>
    </div>
    <h3 style="font-family: 'Playfair Display'; font-weight: bold;" class="mb-3">Cancelación de Cita</h3>
    <p class="text-muted mb-4"><?php echo $mensaje; ?></p>
    
    <a href="index.php" class="btn btn-gold shadow-sm">
        Volver al Inicio
    </a>

    <?php if ($procesado): ?>
        <a href="https://api.whatsapp.com/send?phone=573188623371&text=<?php echo urlencode('Hola, necesito ayuda con mi cita (ID: ' . $id_cita . ')'); ?>" class="btn btn-success mt-3">Contactar por WhatsApp</a>
    <?php endif; ?>

</div>

</body>
</html>