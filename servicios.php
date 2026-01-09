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

// 1. Lógica para INSERTAR nuevo servicio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $duracion = $_POST['duracion']; // Ej: "1h 30min"

    $stmt = $conn->prepare("INSERT INTO servicios (nombre_servicio, precio, duracion_aprox) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $nombre, $precio, $duracion);
    $mensaje = $stmt->execute() ? "success_add" : "error";
    $stmt->close();
}

// 2. Lógica para ACTUALIZAR servicio existente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $duracion = $_POST['duracion'];

    $stmt = $conn->prepare("UPDATE servicios SET nombre_servicio = ?, precio = ?, duracion_aprox = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $nombre, $precio, $duracion, $id);
    $mensaje = $stmt->execute() ? "success_upd" : "error";
    $stmt->close();
}

// 3. Consultar servicios
$resultado = $conn->query("SELECT * FROM servicios ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --gold: #c5a059; --dark: #1a1a1a; --soft: #fdf5f6; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #eee1e3; position: fixed; height: 100vh; padding: 30px 20px; }
        .main-content { margin-left: 260px; padding: 40px; }
        .card-service { border-radius: 15px; border: 1px solid #eee; transition: 0.3s; background: white; }
        .card-service:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .btn-gold { background: var(--gold); color: white; border: none; }
        .btn-gold:hover { background: #b08d4a; color: white; }
        .add-slot { border: 2px dashed var(--gold); background: var(--soft); color: var(--gold); cursor: pointer; }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <h4 class="fw-bold text-center mb-5" style="font-family: 'Playfair Display'; color: var(--gold);">M.O. Nails</h4>
    <nav class="nav flex-column">
        <a class="nav-link py-3 text-dark" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a class="nav-link py-3 text-dark" href="admin.php"><i class="bi bi-calendar3 me-2"></i> Agenda</a>
        <a class="nav-link py-3 text-dark fw-bold active" href="servicios.php"><i class="bi bi-tags-fill me-2 text-gold"></i> Servicios</a>
        <hr>
        <a class="nav-link py-3 text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Salir</a>
    </nav>
</div>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold" style="font-family: 'Playfair Display';">Catálogo de Servicios</h2>
            <p class="text-muted">Gestiona lo que ofreces en tu local (CC La 14, Local 214).</p>
        </div>
        <button class="btn btn-gold px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalNuevo">
            <i class="bi bi-plus-lg me-2"></i> Nuevo Servicio
        </button>
    </header>

    <?php if(strpos($mensaje, 'success') !== false): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> ¡Datos actualizados correctamente!
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php while($s = $resultado->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-service p-4">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted text-uppercase">Nombre</label>
                            <input type="text" name="nombre" class="form-control border-0 bg-light fw-bold" value="<?php echo htmlspecialchars($s['nombre_servicio']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <label class="small fw-bold text-muted text-uppercase">Precio ($)</label>
                                <input type="number" name="precio" class="form-control border-0 bg-light fw-bold text-gold" value="<?php echo $s['precio']; ?>" required>
                            </div>
                            <div class="col-5">
                                <label class="small fw-bold text-muted text-uppercase">Duración</label>
                                <select name="duracion" class="form-select border-0 bg-light small">
                                    <option value="1h" <?php if($s['duracion_aprox'] == '1h') echo 'selected'; ?>>1h</option>
                                    <option value="1h 30min" <?php if($s['duracion_aprox'] == '1h 30min') echo 'selected'; ?>>1.5h</option>
                                    <option value="2h" <?php if($s['duracion_aprox'] == '2h') echo 'selected'; ?>>2h</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="actualizar" class="btn btn-gold w-100 mt-4 py-2 rounded-3">
                            Actualizar
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="col-md-6 col-lg-4">
            <div class="card card-service add-slot p-4 h-100 d-flex align-items-center justify-content-center text-center" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                <div>
                    <i class="bi bi-plus-circle fs-1"></i>
                    <p class="mt-2 fw-bold">Añadir Slot #<?php echo $resultado->num_rows + 1; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body p-5">
                <h3 class="fw-bold mb-4" style="font-family: 'Playfair Display';">Nuevo Servicio</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Servicio</label>
                        <input type="text" name="nombre" class="form-control p-3 bg-light border-0" placeholder="Ej: Nails Express" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label fw-bold">Precio ($)</label>
                            <input type="number" name="precio" class="form-control p-3 bg-light border-0" placeholder="0.00" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Duración</label>
                            <select name="duracion" class="form-select p-3 bg-light border-0">
                                <option value="1h">1 hora</option>
                                <option value="1h 30min">1.5 horas</option>
                                <option value="2h">2 horas</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="agregar" class="btn btn-gold w-100 p-3 rounded-3 fw-bold">GUARDAR SERVICIO</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>