<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente = trim($_POST['cliente']);
    $telefono = trim($_POST['telefono']);
    $id_servicio = intval($_POST['id_servicio']);
    $fecha_dia = $_POST['fecha_dia']; 
    $hora_cita = $_POST['hora'];      
    
    if (empty($cliente) || empty($telefono) || empty($hora_cita)) {
        echo "<script>alert('Por favor completa todos los campos.'); window.history.back();</script>";
        exit;
    }

    // 1. VERIFICAR BLOQUEO ADMINISTRATIVO
    $stmt_bloq = $conn->prepare("SELECT id FROM bloqueos WHERE fecha = ?");
    $stmt_bloq->bind_param("s", $fecha_dia);
    $stmt_bloq->execute();
    if ($stmt_bloq->get_result()->num_rows > 0) {
        echo "<script>alert('Lo sentimos, este día no está disponible.'); window.location.href='index.php';</script>";
        exit;
    }

    // 2. OBTENER DATOS DEL SERVICIO Y CALCULAR DURACIÓN REAL
    $stmt_serv = $conn->prepare("SELECT nombre_servicio, duracion_aprox, precio FROM servicios WHERE id = ?");
    $stmt_serv->bind_param("i", $id_servicio);
    $stmt_serv->execute();
    $datos_serv = $stmt_serv->get_result()->fetch_assoc();
    
    if (!$datos_serv) {
        echo "<script>alert('Error: Servicio no válido.'); window.location.href='index.php';</script>";
        exit;
    }
    
    $nombre_servicio = $datos_serv['nombre_servicio'];
    $da = $datos_serv['duracion_aprox'];

    // Mapeo exacto de duración (Coherente con get_slots.php)
    $duracion = 60; 
    if (strpos($da, '2h') !== false) $duracion = 120;
    elseif (strpos($da, '1h 30min') !== false) $duracion = 90;
    elseif (strpos($da, '30min') !== false) $duracion = 30;
    elseif (strpos($da, '1h') !== false) $duracion = 60;

    $fecha_inicio = $fecha_dia . " " . $hora_cita . ":00";
    $fecha_fin = date('Y-m-d H:i:s', strtotime($fecha_inicio . " + $duracion minutes"));

    // 3. VALIDACIÓN DE CRUCE DE HORARIOS (Lógica mejorada)
    // Buscamos si existe alguna cita cuya franja horaria se solape con la nueva
    $sql_validar = "SELECT c.id FROM citas c
                    JOIN servicios s ON c.id_servicio = s.id
                    WHERE c.estado != 'Cancelada' AND c.fecha_dia = ? 
                    AND (
                        (CONCAT(c.fecha_dia, ' ', c.hora) < ?) 
                        AND (DATE_ADD(CONCAT(c.fecha_dia, ' ', c.hora), INTERVAL 
                            (CASE 
                                WHEN s.duracion_aprox LIKE '%2h%' THEN 120 
                                WHEN s.duracion_aprox LIKE '%1h 30min%' THEN 90
                                WHEN s.duracion_aprox LIKE '%30min%' THEN 30
                                ELSE 60 
                            END) MINUTE) > ?)
                    )";
    
    $stmt_val = $conn->prepare($sql_validar);
    $stmt_val->bind_param("sss", $fecha_dia, $fecha_fin, $fecha_inicio);
    $stmt_val->execute();
    
    if ($stmt_val->get_result()->num_rows > 0) {
        echo "<script>alert('Este horario se acaba de ocupar. Por favor elige otro.'); window.history.back();</script>";
        exit;
    }

    // 4. INSERCIÓN
    $sql_insertar = "INSERT INTO citas (id_servicio, nombre_cliente, telefono, fecha_dia, hora, estado) 
                     VALUES (?, ?, ?, ?, ?, 'Confirmada')";
    
    $stmt_ins = $conn->prepare($sql_insertar);
    $stmt_ins->bind_param("issss", $id_servicio, $cliente, $telefono, $fecha_dia, $hora_cita);
    
    if ($stmt_ins->execute()) {
        $id_cita_generada = $conn->insert_id;
        
        // 5. PREPARAR MENSAJE DE WHATSAPP
        $fecha_f = date('d/m/Y', strtotime($fecha_dia));
        $msj = "✨ *CONFIRMACIÓN DE CITA* ✨\n\n";
        $msj .= "Hola *$cliente*,\n";
        $msj .= "Tu cita en *Monica Orozco Nails* ha sido agendada.\n\n";
        $msj .= "💅 *Servicio:* $nombre_servicio\n";
        $msj .= "📅 *Fecha:* $fecha_f\n";
        $msj .= "⏰ *Hora:* $hora_cita\n";
        $msj .= "📍 *Lugar:* CC La 14, Local 214\n\n";
        $msj .= "¡Te esperamos!\n\n";
        $msj .= "📲 *Contacto (WhatsApp):* +57 318 8623371";
        
        $tel_wa = preg_replace('/[^0-9]/', '', $telefono);
        if(strlen($tel_wa) == 10) $tel_wa = "57" . $tel_wa;

        $url_wa = "https://api.whatsapp.com/send?phone=$tel_wa&text=" . urlencode($msj);
        
        echo "<script>
                alert('¡Cita agendada con éxito!');
                window.open('$url_wa', '_blank');
                window.location.href='index.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}