<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// 1. Verificación de seguridad
if (!isset($_SESSION['admin_auth'])) {
    header("Location: login.php");
    exit();
}

// Configuración de idioma para fechas en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'esp');
$meses_es = [
    "01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", 
    "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", 
    "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"
];

// --- LÓGICA DE ESTADÍSTICAS (MES ACTUAL) ---
$mes_actual = date('m');
$anio_actual = date('Y');

$sql_stats = "SELECT s.nombre_servicio, COUNT(c.id) as total_citas, SUM(s.precio) as total_dinero 
              FROM citas c 
              JOIN servicios s ON c.id_servicio = s.id 
              WHERE MONTH(c.fecha_dia) = ? AND YEAR(c.fecha_dia) = ? AND c.estado = 'Confirmada'
              GROUP BY s.nombre_servicio";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("ss", $mes_actual, $anio_actual);
$stmt_stats->execute();
$res_stats = $stmt_stats->get_result();

$recaudo_mes = 0;
$total_clientes_mes = 0;
$datos_tabla = [];

while($row = $res_stats->fetch_assoc()) {
    $recaudo_mes += $row['total_dinero'];
    $total_clientes_mes += $row['total_citas'];
    $datos_tabla[] = $row;
}

// --- LÓGICA DE AGENDA DE MAÑANA ---
$manana = date('Y-m-d', strtotime('+1 day'));
// Horarios base para visualización
$bloques_horarios = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

$sql_manana = "SELECT c.*, s.nombre_servicio 
               FROM citas c 
               LEFT JOIN servicios s ON c.id_servicio = s.id 
               WHERE c.fecha_dia = ? AND c.estado != 'Cancelada'
               ORDER BY c.hora ASC";

$stmt_manana = $conn->prepare($sql_manana);
$stmt_manana->bind_param("s", $manana);
$stmt_manana->execute();
$res_manana = $stmt_manana->get_result();

$citas_manana = [];
while($f = $res_manana->fetch_assoc()) {
    // Usamos los primeros 5 caracteres de la hora (HH:MM) para indexar
    $hora_key = substr($f['hora'], 0, 5);
    $citas_manana[$hora_key] = $f;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --gold: #c5a059; --soft-pink: #fdf5f6; --text: #4a4a4a; --border: #eee1e3; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: var(--text); }
        
        /* Sidebar mejorado */
        .sidebar { width: 260px; background: white; border-right: 1px solid var(--border); position: fixed; height: 100vh; padding: 30px 20px; z-index: 100; }
        .main-content { margin-left: 260px; padding: 40px; transition: 0.3s; }
        
        .card-custom { background: white; border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; }
        .stat-card { background: white; border-radius: 15px; padding: 25px; border-left: 5px solid var(--gold); transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        .bg-libre { background-color: #f9f9f9; border-bottom: 1px solid #f1f1f1 !important; }
        .text-gold { color: var(--gold) !important; }
        
        .nav-link { color: var(--text) !important; transition: 0.3s; border-radius: 10px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: var(--soft-pink); color: var(--gold) !important; }
        
        .agenda-scroll { max-height: 500px; overflow-y: auto; }
        
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <div class="text-center mb-5">
        <h4 class="fw-bold text-uppercase" style="font-family: 'Playfair Display'; color: var(--gold); letter-spacing: 2px;">M.O. Nails</h4>
        <small class="text-muted">Panel de Control</small>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link active py-3" href="dashboard.php"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
        <a class="nav-link py-3" href="admin.php"><i class="bi bi-calendar3 me-2"></i> Agenda Total</a>
        <a class="nav-link py-3" href="servicios.php"><i class="bi bi-stars me-2"></i> Servicios</a>
        <a class="nav-link py-3" href="bloqueos.php"><i class="bi bi-calendar-x me-2"></i> Bloqueos</a>
        <hr class="my-4">
        <a class="nav-link py-3 text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Cerrar Sesión</a>
    </nav>
</div>

<div class="main-content">
    <header class="mb-5 d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display';">Vista General</h2>
            <p class="text-muted m-0">Balance de <strong><?php echo $meses_es[$mes_actual]; ?> <?php echo $anio_actual; ?></strong></p>
        </div>
        <div class="text-end">
            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-calendar-event me-2 text-gold"></i><?php echo date('d/m/Y'); ?>
            </div>
        </div>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-xl-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-uppercase fw-bold text-muted">Recaudo Estimado</span>
                        <h2 class="fw-bold mt-1 text-gold">$<?php echo number_format($recaudo_mes, 0, ',', '.'); ?></h2>
                    </div>
                    <i class="bi bi-cash-stack fs-3 text-gold opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="stat-card" style="border-left-color: #2ecc71;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-uppercase fw-bold text-muted">Citas Finalizadas</span>
                        <h2 class="fw-bold mt-1"><?php echo $total_clientes_mes; ?></h2>
                    </div>
                    <i class="bi bi-check2-circle fs-3 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-custom p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0">Desempeño por Servicio</h5>
                    <i class="bi bi-graph-up text-gold"></i>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>SERVICIO</th>
                                <th class="text-center">CANTIDAD</th>
                                <th class="text-end">RECAUDO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($datos_tabla)): ?>
                                <?php foreach($datos_tabla as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($s['nombre_servicio']); ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-soft-pink text-dark border px-3"><?php echo $s['total_citas']; ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-gold">$<?php echo number_format($s['total_dinero'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted small italic">No hay datos registrados en este periodo.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-custom shadow-sm overflow-hidden">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0">Agenda de Mañana</h5>
                    <span class="small fw-bold text-gold"><?php echo date('d/m', strtotime('+1 day')); ?></span>
                </div>
                <div class="agenda-scroll">
                    <?php foreach($bloques_horarios as $hora_base): 
                        $ocupado = isset($citas_manana[$hora_base]);
                    ?>
                        <div class="p-3 d-flex align-items-center <?php echo $ocupado ? 'border-start border-gold border-4' : 'bg-libre'; ?>">
                            <div class="fw-bold me-4 text-dark small" style="width: 60px;">
                                <?php echo date('g:i A', strtotime($hora_base)); ?>
                            </div>
                            <?php if($ocupado): ?>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-uppercase" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($citas_manana[$hora_base]['nombre_cliente']); ?>
                                    </div>
                                    <div class="text-muted extra-small" style="font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($citas_manana[$hora_base]['nombre_servicio']); ?>
                                    </div>
                                </div>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $citas_manana[$hora_base]['telefono']); ?>" target="_blank" class="btn btn-sm btn-success rounded-circle">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            <?php else: ?>
                                <div class="flex-grow-1 text-center small text-muted opacity-25" style="letter-spacing: 1px;">LIBRE</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="p-3 bg-white border-top">
                    <a href="admin.php" class="btn btn-sm btn-gold w-100 text-white">Ver Agenda Completa</a>
                </div>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>