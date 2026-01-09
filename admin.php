<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/db.php';

// 2. Verificación estricta de sesión
if (!isset($_SESSION['admin_auth'])) {
    header("Location: login.php");
    exit();
}

// 3. Lógica para Cambiar Estado con Sentencias Preparadas
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];
    
    $nuevo_estado = ($accion === 'confirmar') ? 'Confirmada' : 'Cancelada';
    
    $stmt = $conn->prepare("UPDATE citas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?msg=success");
    } else {
        header("Location: admin.php?msg=error");
    }
    $stmt->close();
    exit();
}

// 4. Filtros de Búsqueda Seguros
$fecha_filtro = $_GET['fecha_filtro'] ?? '';
$where = "";
$params = [];
$types = "";

if (!empty($fecha_filtro)) {
    $where = " WHERE c.fecha_dia = ?";
    $params[] = $fecha_filtro;
    $types .= "s";
}

// 5. Consulta Principal usando Prepared Statements para mayor seguridad
$sql = "SELECT c.id, c.nombre_cliente, c.telefono, s.nombre_servicio AS servicio, 
               s.precio, c.fecha_dia, c.hora, c.estado 
        FROM citas c 
        JOIN servicios s ON c.id_servicio = s.id 
        $where
        ORDER BY c.fecha_dia DESC, c.hora DESC";

$stmt_list = $conn->prepare($sql);

if (!empty($where)) {
    $stmt_list->bind_param($types, ...$params);
}

$stmt_list->execute();
$resultado = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Agenda | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --gold: #c5a059; --soft-pink: #fdf5f6; --dark-text: #2d2d2d; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: var(--dark-text); }
        .navbar-admin { background: white; border-bottom: 1px solid #eee; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-table { background: white; border-radius: 20px; border: 1px solid #eee1e3; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .table thead { background: var(--soft-pink); color: var(--gold); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        
        /* Badges estilizados */
        .badge-status { border-radius: 50px; padding: 6px 14px; font-size: 0.7rem; font-weight: 600; }
        .badge-confirmada { background-color: #d1e7dd; color: #0f5132; }
        .badge-pendiente { background-color: #fff3cd; color: #664d03; }
        .badge-cancelada { background-color: #f8d7da; color: #842029; }
        
        .btn-action { border-radius: 10px; transition: 0.3s; width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; }
        .btn-action:hover { transform: translateY(-2px); }
        .text-gold { color: var(--gold); }
    </style>
</head>
<body>

<nav class="navbar-admin mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h4 class="m-0 fw-bold" style="font-family: 'Playfair Display'; color: var(--gold);">Panel Monica Orozco</h4>
        <div>
            <span class="me-3 small text-muted">Hola, <?php echo htmlspecialchars($_SESSION['admin_auth']); ?></span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary me-2">Estadísticas</a>
            <a href="change_password.php" class="btn btn-sm btn-outline-primary me-2">🔑 Cambiar contraseña</a>
            <a href="logout.php" class="btn btn-sm btn-danger px-3">Salir</a>
        </div>
    </div>
</nav>

<div class="container">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-<?php echo $_GET['msg'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_GET['msg'] == 'success' ? '✅ Cambio guardado correctamente.' : '❌ Hubo un error al procesar.'; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <form class="d-flex gap-2" method="GET">
                <input type="date" name="fecha_filtro" class="form-control w-auto" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
                <button type="submit" class="btn btn-dark px-4">Filtrar Agenda</button>
                <?php if(!empty($fecha_filtro)): ?>
                    <a href="admin.php" class="btn btn-outline-secondary">Ver Todo</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card-table p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Cliente / Contacto</th>
                        <th>Servicio</th>
                        <th>Fecha y Hora</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($f = $resultado->fetch_assoc()): 
                            $clase_estado = 'badge-' . strtolower($f['estado']);
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($f['nombre_cliente']); ?></div>
                                <small class="text-muted"><i class="bi bi-phone"></i> <?php echo htmlspecialchars($f['telefono']); ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($f['servicio']); ?></span></td>
                            <td>
                                <div><i class="bi bi-calendar3 small"></i> <?php echo date('d/m/Y', strtotime($f['fecha_dia'])); ?></div>
                                <div class="fw-bold text-primary small"><i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($f['hora'])); ?></div>
                            </td>
                            <td class="text-gold fw-bold">$<?php echo number_format($f['precio'], 0, ',', '.'); ?></td>
                            <td><span class="badge-status <?php echo $clase_estado; ?>"><?php echo strtoupper($f['estado']); ?></span></td>
                            <td class="text-center">
                                <?php if($f['estado'] === 'Pendiente'): ?>
                                    <a href="admin.php?accion=confirmar&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-success btn-action" title="Confirmar Cita"><i class="bi bi-check-lg"></i></a>
                                <?php endif; ?>
                                
                                <?php if($f['estado'] !== 'Cancelada'): ?>
                                    <a href="admin.php?accion=cancelar&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('¿Seguro que deseas cancelar esta cita?')" title="Anular"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                                
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','',$f['telefono']); ?>?text=Hola%20<?php echo urlencode($f['nombre_cliente']); ?>%2C%20te%20contacto%20de%20Monica%20Orozco%20Nails..." target="_blank" class="btn btn-sm btn-outline-success btn-action" title="Enviar WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron citas para este criterio.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>