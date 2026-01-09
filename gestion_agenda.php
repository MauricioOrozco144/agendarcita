<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

// Consulta optimizada: Trae las citas ordenadas por fecha y hora
$query = "SELECT * FROM citas ORDER BY fecha_dia DESC, hora ASC";
$resultado = mysqli_query($conn, $query);

// Contadores para el Dashboard
$hoy = date('Y-m-d');
$res_hoy = mysqli_query($conn, "SELECT COUNT(*) as total FROM citas WHERE fecha_dia = '$hoy' AND estado = 'Confirmada'");
$total_hoy = mysqli_fetch_assoc($res_hoy)['total'];

$res_pend = mysqli_query($conn, "SELECT COUNT(*) as total FROM citas WHERE estado = 'Pendiente'");
$total_pendientes = mysqli_fetch_assoc($res_pend)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Profesional | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { 
            --gold: #c5a059; 
            --soft-pink: #fdf5f6; 
            --text-main: #4a4a4a;
            --border: #eee1e3;
        }
        
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: var(--text-main); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background: white; border-right: 1px solid var(--border); padding: 30px 20px; position: fixed; height: 100vh; }
        .sidebar .brand { font-family: 'Playfair Display', serif; color: var(--gold); font-size: 1.5rem; margin-bottom: 40px; text-align: center; }
        .nav-link { color: var(--text-main); padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: var(--soft-pink); color: var(--gold); }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        
        /* Stats Cards */
        .card-stat { background: white; border: 1px solid var(--border); border-radius: 15px; padding: 25px; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .card-stat:hover { transform: translateY(-5px); border-color: var(--gold); }
        .stat-number { font-size: 2.2rem; font-weight: bold; color: var(--gold); display: block; line-height: 1; }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 600; }

        /* Table Design */
        .table-container { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .table thead { background: var(--soft-pink); }
        .table th { border: none; padding: 18px 15px; color: var(--gold); font-size: 0.85rem; letter-spacing: 0.5px; }
        .table td { padding: 18px 15px; border-bottom: 1px solid #f9f9f9; }
        
        /* Status Badges */
        .badge-status { padding: 8px 14px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .bg-cancelada { background: #ffebeb; color: #d9534f; }
        .bg-confirmada { background: #ebf9f1; color: #2ecc71; }
        .bg-pendiente { background: #fff8eb; color: #f39c12; }

        /* Actions */
        .btn-action { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; font-size: 1.1rem; }
        .btn-check-custom { background: #ebf9f1; color: #2ecc71; border: 1px solid #d4f0df; }
        .btn-check-custom:hover { background: #2ecc71; color: white; }
        .btn-x-custom { background: #ffebeb; color: #d9534f; border: 1px solid #f9d6d6; }
        .btn-x-custom:hover { background: #d9534f; color: white; }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <div class="brand fw-bold text-uppercase">M.O. Nails</div>
    <nav class="nav flex-column">
        <a class="nav-link active" href="gestion_agenda.php"><i class="bi bi-calendar-check me-2"></i> Agenda</a>
        <a class="nav-link" href="index.php"><i class="bi bi-house-heart me-2"></i> Ver Web</a>
        <hr class="my-4 opacity-50">
        <a class="nav-link text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Salir</a>
    </nav>
</div>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h2 class="fw-bold m-0" style="font-family: 'Playfair Display'; color: #333;">Gestión de Agenda</h2>
            <p class="text-muted small">Panel de control de servicios y citas</p>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm py-2 px-4 rounded-pill small animate__animated animate__fadeIn">
                <i class="bi bi-check-circle-fill me-2"></i> ¡Cita actualizada con éxito!
            </div>
        <?php endif; ?>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card-stat">
                <span class="stat-label">Citas para hoy</span>
                <span class="stat-number"><?php echo $total_hoy; ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <span class="stat-label">Pendientes por revisión</span>
                <span class="stat-number"><?php echo $total_pendientes; ?></span>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold"><i class="bi bi-list-stars me-2 text-gold"></i> Listado de Turnos Recientes</h6>
            <button class="btn btn-light btn-sm rounded-circle" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise text-gold"></i>
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">CLIENTE</th>
                        <th>SERVICIO</th>
                        <th>FECHA Y HORA</th>
                        <th class="text-center">ESTADO</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?php echo $row['nombre_cliente']; ?></div>
                            <small class="text-muted"><i class="bi bi-whatsapp me-1"></i><?php echo $row['telefono']; ?></small>
                        </td>
                        <td>
                            <span class="small py-1 px-3 rounded-pill bg-light text-muted border" style="font-size: 0.75rem;">
                                <?php echo $row['servicio']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo date('d M, Y', strtotime($row['fecha_dia'])); ?></div>
                            <div class="small fw-bold text-gold"><?php echo $row['hora']; ?></div>
                        </td>
                        <td class="text-center">
                            <?php 
                                $status_class = "bg-pendiente";
                                if($row['estado'] == 'Cancelada') $status_class = "bg-cancelada";
                                if($row['estado'] == 'Confirmada') $status_class = "bg-confirmada";
                            ?>
                            <span class="badge-status <?php echo $status_class; ?>">
                                <?php echo $row['estado']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="actualizar_estado.php?id=<?php echo $row['id']; ?>&nuevo=Confirmada" 
                                   class="btn-action btn-check-custom text-decoration-none" 
                                   title="Confirmar y enviar WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="actualizar_estado.php?id=<?php echo $row['id']; ?>&nuevo=Cancelada" 
                                   class="btn-action btn-x-custom text-decoration-none"
                                   title="Cancelar cita">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>