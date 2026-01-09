<?php
// No es necesario incluir db.php aquí si ya se incluyó en el archivo que llama a la función
// pero aseguramos que la conexión exista.

function generarHorariosDisponibles($fecha, $id_servicio, $conn) {
    // 1. Obtener duración del servicio seleccionado
    $res = mysqli_query($conn, "SELECT duracion_minutos FROM servicios WHERE id_servicio = $id_servicio");
    $servicio = mysqli_fetch_assoc($res);
    
    if (!$servicio) return []; // Retornar vacío si el servicio no existe
    
    $duracion_nueva = $servicio['duracion_minutos'];

    $inicio_jornada = "08:00";
    $fin_jornada = "18:00";
    $intervalo = 30; 
    
    $horarios_disponibles = [];
    $actual = strtotime("$fecha $inicio_jornada");
    $cierre = strtotime("$fecha $fin_jornada");
    $ahora = time(); // Tiempo actual del servidor

    // 2. Traer citas agendadas (EXCLUYENDO las canceladas)
    $citas_hoy = [];
    $res_citas = mysqli_query($conn, "SELECT fecha_hora, s.duracion_minutos 
                                      FROM citas c 
                                      JOIN servicios s ON c.id_servicio = s.id_servicio 
                                      WHERE DATE(fecha_hora) = '$fecha' 
                                      AND estado != 'Cancelada'"); // Importante: ignorar canceladas
                                      
    while($row = mysqli_fetch_assoc($res_citas)) {
        $citas_hoy[] = [
            'inicio' => strtotime($row['fecha_hora']),
            'fin' => strtotime($row['fecha_hora'] . " + " . $row['duracion_minutos'] . " minutes")
        ];
    }

    // 3. Probar cada slot
    while ($actual + ($duracion_nueva * 60) <= $cierre) {
        $nueva_cita_inicio = $actual;
        $nueva_cita_fin = $actual + ($duracion_nueva * 60);
        $cruce = false;

        // VALIDACIÓN EXTRA: No permitir horas que ya pasaron si la fecha es HOY
        if ($nueva_cita_inicio < $ahora && $fecha == date('Y-m-d')) {
            $cruce = true;
        }

        if (!$cruce) {
            foreach ($citas_hoy as $cita) {
                // Lógica de colisión
                if ($nueva_cita_inicio < $cita['fin'] && $nueva_cita_fin > $cita['inicio']) {
                    $cruce = true;
                    break;
                }
            }
        }

        if (!$cruce) {
            $horarios_disponibles[] = date("H:i", $actual);
        }
        $actual = strtotime("+30 minutes", $actual);
    }
    return $horarios_disponibles;
}
?>