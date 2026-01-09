<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// 1. Protección de acceso
if (!isset($_SESSION['admin'])) { 
    header("Location: login.php");
    exit();
}

// 2. Validar que recibimos los parámetros necesarios
if (isset($_GET['id']) && isset($_GET['nuevo'])) {
    
    $id = intval($_GET['id']);
    $nuevo_estado = mysqli_real_escape_string($conn, $_GET['nuevo']);

    // 3. Obtener datos de la cita para el mensaje de confirmación
    // Ajusta 'id_cita' o 'id' según el nombre real en tu tabla
    $consulta = mysqli_query($conn, "SELECT * FROM citas WHERE id = $id LIMIT 1");
    $cita = mysqli_fetch_assoc($consulta);

    if (!$cita) {
        header("Location: gestion_agenda.php?error=notfound");
        exit();
    }

    // 4. Ejecutar la actualización
    $sql_update = "UPDATE citas SET estado = '$nuevo_estado' WHERE id = $id";
    
    if (mysqli_query($conn, $sql_update)) {
        
        // --- LÓGICA DE INTEGRACIÓN CON WHATSAPP ---
        if ($nuevo_estado === 'Confirmada') {
            $nombre = $cita['nombre_cliente'];
            $servicio = $cita['servicio'];
            // Formatear fecha para que sea legible (ej: 15 de Enero)
            $fecha = date('d/m/Y', strtotime($cita['fecha_dia']));
            $hora = $cita['hora'];
            $telefono = $cita['telefono'];

            // Limpiar el teléfono (quitar espacios o símbolos si los hay)
            $telefono_limpio = preg_replace('/[^0-9]/', '', $telefono);
            // Si el número no tiene código de país, añadir el de Colombia (57) por defecto
            if (strlen($telefono_limpio) == 10) { $telefono_limpio = "57" . $telefono_limpio; }

            // Mensaje elegante y profesional
            $mensaje = "¡Hola *{$nombre}*! ✨\n\nTe confirmo que tu cita para *{$servicio}* ha sido programada con éxito.\n\n📅 *Fecha:* {$fecha}\n⏰ *Hora:* {$hora}\n📍 *Lugar:* CC La 14, Local 214\n\n📲 *Contacto (WhatsApp):* +57 318 8623371\n\nPor favor, confírmame con un *OK* si recibiste este mensaje. ¡Nos vemos pronto! 💅";

            $url_whatsapp = "https://api.whatsapp.com/send?phone={$telefono_limpio}&text=" . urlencode($mensaje);

            // Redirección inmediata a WhatsApp
            header("Location: $url_whatsapp");
            exit();
        } elseif ($nuevo_estado === 'Cancelada') {
            $nombre = $cita['nombre_cliente'];
            $servicio = isset($cita['servicio']) ? $cita['servicio'] : '';
            $fecha = date('d/m/Y', strtotime($cita['fecha_dia']));
            $hora = $cita['hora'];
            $telefono = $cita['telefono'];

            // Limpiar el teléfono (quitar espacios o símbolos si los hay)
            $telefono_limpio = preg_replace('/[^0-9]/', '', $telefono);
            // Si el número no tiene código de país, añadir el de Colombia (57) por defecto
            if (strlen($telefono_limpio) == 10) { $telefono_limpio = "57" . $telefono_limpio; }

            // Mensaje de cancelación
            $mensaje = "Hola *{$nombre}*,\n\nLamentamos informarte que tu cita para *{$servicio}* programada el *{$fecha}* a las *{$hora}* ha sido *CANCELADA*.\n\nSi deseas reprogramar o tienes dudas, contáctanos por WhatsApp: +57 318 8623371";

            $url_whatsapp = "https://api.whatsapp.com/send?phone={$telefono_limpio}&text=" . urlencode($mensaje);

            // Redirección inmediata a WhatsApp
            header("Location: $url_whatsapp");
            exit();
        } else {
            // Otros estados
            header("Location: gestion_agenda.php?msg=updated");
            exit();
        }

    } else {
        die("Error crítico al actualizar: " . mysqli_error($conn));
    }

} else {
    header("Location: gestion_agenda.php");
    exit();
}
?>