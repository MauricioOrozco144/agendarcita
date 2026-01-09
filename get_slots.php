<?php
include 'config/db.php';

$id_servicio = isset($_GET['id']) ? intval($_GET['id']) : 0;
$fecha_elegida = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

header('Content-Type: application/json');

if ($id_servicio === 0) {
    echo json_encode(['horarios' => [], 'mensaje' => 'Selecciona un servicio']);
    exit;
}

// 1. VERIFICACIÓN DE BLOQUEOS
$sql_bloqueo = "SELECT motivo FROM bloqueos WHERE fecha = ? LIMIT 1";
$stmt_bloq = $conn->prepare($sql_bloqueo);
$stmt_bloq->bind_param("s", $fecha_elegida);
$stmt_bloq->execute();
$res_bloq = $stmt_bloq->get_result();

if ($res_bloq->num_rows > 0) {
    $bloqueo = $res_bloq->fetch_assoc();
    echo json_encode([
        'bloqueado' => true, 
        'mensaje' => "Día no disponible: " . ($bloqueo['motivo'] ?: "Descanso/Festivo"),
        'horarios' => []
    ]);
    exit;
}

// 2. Obtener duración real del servicio
$sql_serv = "SELECT duracion_aprox FROM servicios WHERE id = ?";
$stmt_s = $conn->prepare($sql_serv);
$stmt_s->bind_param("i", $id_servicio);
$stmt_s->execute();
$servicio = $stmt_s->get_result()->fetch_assoc();

// Mapeo preciso de duración en minutos
$duracion_nueva = 60; // Default 1h
if ($servicio) {
    $da = $servicio['duracion_aprox'];
    if (strpos($da, '2h') !== false) $duracion_nueva = 120;
    elseif (strpos($da, '1h 30min') !== false) $duracion_nueva = 90;
    elseif (strpos($da, '30min') !== false) $duracion_nueva = 30;
    elseif (strpos($da, '1h') !== false) $duracion_nueva = 60;
}

// 3. Obtener citas ocupadas
$citas_ocupadas = [];
$sql_citas = "SELECT c.hora, s.duracion_aprox 
              FROM citas c 
              JOIN servicios s ON c.id_servicio = s.id 
              WHERE c.fecha_dia = ? AND c.estado != 'Cancelada'";
$stmt_c = $conn->prepare($sql_citas);
$stmt_c->bind_param("s", $fecha_elegida);
$stmt_c->execute();
$res_citas = $stmt_c->get_result();

while ($fila = $res_citas->fetch_assoc()) {
    $d_ocp = $fila['duracion_aprox'];
    $minutos_ocp = 60; 
    if (strpos($d_ocp, '2h') !== false) $minutos_ocp = 120;
    elseif (strpos($d_ocp, '30min') !== false) $minutos_ocp = 30;
    
    $inicio = strtotime($fecha_elegida . " " . $fila['hora']);
    $fin = $inicio + ($minutos_ocp * 60);
    $citas_ocupadas[] = ['inicio' => $inicio, 'fin' => $fin];
}

// 4. Generación de Slots (Jornada 08:00 a 18:00)
$horarios_libres = [];
$actual = strtotime("$fecha_elegida 08:00");
$cierre = strtotime("$fecha_elegida 18:00");

// Si es el día de hoy, no mostrar horas que ya pasaron
if($fecha_elegida == date('Y-m-d')){
    $hora_minima = strtotime("+30 minutes"); // Margen de 30 min para agendar hoy
    if($actual < $hora_minima) $actual = $hora_minima;
    // Redondear a la siguiente media hora
    $minutos = date('i', $actual);
    if($minutos > 0 && $minutos <= 30) $actual = strtotime(date('Y-m-d H:30', $actual));
    elseif($minutos > 30) $actual = strtotime(date('Y-m-d H:00', strtotime("+1 hour", $actual)));
}

while ($actual + ($duracion_nueva * 60) <= $cierre) {
    $posible_inicio = $actual;
    $posible_fin = $actual + ($duracion_nueva * 60);
    $esta_ocupado = false;

    foreach ($citas_ocupadas as $cita) {
        if ($posible_inicio < $cita['fin'] && $posible_fin > $cita['inicio']) {
            $esta_ocupado = true;
            break;
        }
    }

    if (!$esta_ocupado) {
        $horarios_libres[] = date("H:i", $actual);
    }
    $actual = strtotime("+30 minutes", $actual);
}

echo json_encode([
    'bloqueado' => false,
    'horarios' => $horarios_libres
]);