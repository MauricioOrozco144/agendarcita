<?php
include 'config/db.php';

$id_cita = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensaje = "";
$error = false;

// 1. Si se envía el formulario de confirmación de cancelación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar_cancelar'])) {
    $id_confirm = intval($_POST['id_cita']);
    $tel_verificar = trim($_POST['telefono']);

    // Verificamos que el teléfono coincida con la cita para mayor seguridad
    $stmt = $conn->prepare("SELECT id FROM citas WHERE id = ? AND telefono LIKE ?");
    // Buscamos coincidencia parcial por si el teléfono se guardó con o sin prefijo
    $tel_param = "%$tel_verificar%"; 
    $stmt->bind_param("is", $id_confirm, $tel_param);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Procedemos a cancelar
        $update = $conn->prepare("UPDATE citas SET estado = 'Cancelada' WHERE id = ?");
        $update->bind_param("i", $id_confirm);
        
        if ($update->execute()) {
            $mensaje = "✅ Tu cita ha sido cancelada con éxito. ¡Esperamos verte pronto en otra ocasión! Si necesitas reprogramar o tienes dudas, contáctanos por WhatsApp: +57 318 8623371";
        } else {
            $mensaje = "❌ Hubo un error al procesar la cancelación. Intenta más tarde.";
            $error = true;
        }
    } else {
        $mensaje = "❌ El número de teléfono no coincide con nuestros registros para esta cita.";
        $error = true;
    }
}

// 2. Obtener datos de la cita para mostrar en pantalla antes de cancelar
$datos_cita = null;
if ($id_cita > 0) {
    $stmt_info = $conn->prepare("SELECT c.fecha_dia, c.hora, s.nombre_servicio, c.nombre_cliente 
                                 FROM citas c 
                                 JOIN servicios s ON c.id_servicio = s.id 
                                 WHERE c.id = ? AND c.estado != 'Cancelada'");
    $stmt_info->bind_param("i", $id_cita);
    $stmt_info->execute();
    $datos_cita = $stmt_info->get_result()->fetch_assoc();
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
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fdf5f6; display: flex; align-items: center; min-height: 100vh; }
        .cancel-card { background: white; border-radius: 20px; border-top: 5px solid #c5a059; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; max-width: 500px; margin: auto; }
        h2 { font-family: 'Playfair Display', serif; color: #4a4a4a; }
        .btn-cancelar { background-color: #dc3545; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-cancelar:hover { background-color: #bb2d3b; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container">
    <div class="cancel-card text-center">
        <?php if ($mensaje): ?>
            <div class="alert <?php echo $error ? 'alert-danger' : 'alert-success'; ?> mb-4">
                <?php echo $mensaje; ?>
            </div>
            <a href="index.php" class="btn btn-outline-secondary w-100">Volver al inicio</a>
            <?php if (!$error): ?>
                <a href="https://api.whatsapp.com/send?phone=573188623371&text=Hola%20Monica%20Orozco%20Nails%20,%20necesito%20reprogramar%20mi%20cita" class="btn btn-success w-100 mt-2">Contactar por WhatsApp</a>
            <?php endif; ?>
        
        <?php elseif ($datos_cita): ?>
            <h2 class="mb-4">Gestionar Cita</h2>
            <p class="text-muted mb-4">Hola <strong><?php echo htmlspecialchars($datos_cita['nombre_cliente']); ?></strong>, ¿estás segura de que deseas cancelar tu cita?</p>
            
            <div class="bg-light p-3 rounded mb-4 text-start">
                <small class="text-uppercase fw-bold text-muted d-block">Servicio</small>
                <div class="mb-2"><?php echo htmlspecialchars($datos_cita['nombre_servicio']); ?></div>
                
                <small class="text-uppercase fw-bold text-muted d-block">Fecha y Hora</small>
                <div><?php echo date('d/m/Y', strtotime($datos_cita['fecha_dia'])); ?> a las <?php echo $datos_cita['hora']; ?></div>
            </div>

            <form method="POST">
                <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Confirma tu WhatsApp registrado:</label>
                    <input type="tel" name="telefono" class="form-control" placeholder="Tu número de teléfono" required>
                </div>
                <button type="submit" name="confirmar_cancelar" class="btn btn-cancelar shadow-sm">
                    SÍ, CANCELAR MI CITA
                </button>
                <a href="index.php" class="d-block mt-3 text-muted small text-decoration-none">No, mantener mi cita</a>
            </form>

        <?php else: ?>
            <div class="alert alert-warning">
                Esta cita ya no existe, fue cancelada previamente o el enlace es inválido.
            </div>
            <a href="index.php" class="btn btn-gold w-100" style="background:#c5a059; color:white;">Ir a Inicio</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>