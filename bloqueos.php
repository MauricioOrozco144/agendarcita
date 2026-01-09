<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

if (!isset($_SESSION['admin_auth'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// Lógica para AGREGAR un bloqueo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bloquear'])) {
    $fecha = $_POST['fecha'];
    $motivo = trim($_POST['motivo']);

    // INSERT IGNORE evita errores si intenta bloquear la misma fecha dos veces
    $stmt = $conn->prepare("INSERT IGNORE INTO bloqueos (fecha, motivo) VALUES (?, ?)");
    $stmt->bind_param("ss", $fecha, $motivo);
    
    if ($stmt->execute()) {
        $mensaje = "bloqueo_ok";
    }
    $stmt->close();
}

// Lógica para ELIMINAR un bloqueo
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM bloqueos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: bloqueos.php?msg=eliminado");
    exit();
}

$hoy = date('Y-m-d');
$res_bloqueos = $conn->query("SELECT * FROM bloqueos WHERE fecha >= '$hoy' ORDER BY fecha ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloqueos | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --gold: #c5a059; --soft-pink: #fdf5f6; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: #4a4a4a; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #eee1e3; position: fixed; height: 100vh; padding: 30px 20px; }
        .main-content { margin-left: 260px; padding: 40px; }
        .card-custom { background: white; border-radius: 20px; border: 1px solid #eee1e3; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .btn-gold { background: var(--gold); color: white; border-radius: 10px; font-weight: 600; border: none; padding: 10px 20px; }
        .btn-gold:hover { background: #b08d4a; color: white; }
        .nav-link:hover, .nav-link.active { background: var(--soft-pink); color: var(--gold) !important; border-radius: 10px; }
        @media (max-width: 768px) { .sidebar { display: none; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <h4 class="fw-bold text-center mb-5" style="font-family: 'Playfair Display'; color: var(--gold);">M.O. Nails</h4>
    <nav class="nav flex-column">
        <a class="nav-link py-3 text-dark" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a class="nav-link py-3 text-dark" href="admin.php"><i class="bi bi-calendar3 me-2"></i> Agenda Total</a>
        <a class="nav-link py-3 text-dark" href="servicios.php"><i class="bi bi-tags me-2"></i> Servicios</a>
        <a class="nav-link py-3 text-dark active fw-bold" href="bloqueos.php"><i class="bi bi-calendar-x me-2 text-danger"></i> Bloqueos</a>
        <hr>
        <a class="nav-link py-3 text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Salir</a>
    </nav>
</div>

<div class="main-content">
    <header class="mb-5">
        <h2 class="fw-bold" style="font-family: 'Playfair Display';">Gestión de Descansos</h2>
        <p class="text-muted">Controla los días en que el local estará cerrado.</p>
    </header>

    <?php if($mensaje == "bloqueo_ok" || isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> Operación realizada con éxito.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4 text-gold"><i class="bi bi-plus-circle me-2"></i>Bloquear Día</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Fecha</label>
                        <input type="date" name="fecha" class="form-control border-0 bg-light p-3" min="<?php echo $hoy; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Motivo</label>
                        <input type="text" name="motivo" class="form-control border-0 bg-light p-3" placeholder="Ej: Lunes Festivo">
                    </div>
                    <button type="submit" name="bloquear" class="btn btn-gold w-100 shadow-sm">
                        Confirmar Bloqueo
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom p-4 shadow-sm">
                <h5 class="fw-bold mb-4">Fechas Inhabilitadas</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-light text-muted">
                            <tr style="font-size: 0.8rem;">
                                <th>FECHA</th>
                                <th>MOTIVO</th>
                                <th class="text-center">GESTIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($res_bloqueos->num_rows > 0): ?>
                                <?php while($b = $res_bloqueos->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo date('d/m/Y', strtotime($b['fecha'])); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($b['motivo'] ?: 'Descanso'); ?></span></td>
                                        <td class="text-center">
                                            <a href="bloqueos.php?eliminar=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Deseas habilitar este día nuevamente?')">
                                                <i class="bi bi-trash3 me-1"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted small">
                                        <i class="bi bi-calendar-check d-block fs-1 mb-2 opacity-25"></i>
                                        No hay bloqueos futuros registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>