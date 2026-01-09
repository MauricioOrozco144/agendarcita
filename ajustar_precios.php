<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Seguridad: Solo admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// Lógica para actualizar el precio cuando se envía el formulario
if (isset($_POST['update_price'])) {
    $id_servicio = intval($_POST['id_ser']);
    $nuevo_precio = mysqli_real_escape_string($conn, $_POST['new_price']);

    $sql_update = "UPDATE servicios SET precio = '$nuevo_precio' WHERE id = $id_servicio";
    
    if (mysqli_query($conn, $sql_update)) {
        $mensaje = "<div class='alert alert-success animate__animated animate__fadeIn'>✓ Precio actualizado correctamente</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . mysqli_error($conn) . "</div>";
    }
}

// Consultar todos los servicios
$servicios = mysqli_query($conn, "SELECT * FROM servicios ORDER BY nombre_servicio ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Precios | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --gold: #c5a059; --soft-pink: #fdf5f6; --text: #4a4a4a; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: var(--text); }
        .main-container { max-width: 800px; margin: 60px auto; padding: 20px; }
        .card-prices { background: white; border-radius: 25px; border: 1px solid #eee1e3; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.03); }
        .btn-gold { background: var(--gold); color: white; border: none; padding: 8px 20px; border-radius: 10px; transition: 0.3s; }
        .btn-gold:hover { background: #b08d4a; transform: translateY(-2px); color: white; }
        .price-input { border: 2px solid var(--soft-pink); border-radius: 10px; font-weight: 600; color: var(--gold); }
        .price-input:focus { border-color: var(--gold); box-shadow: none; }
        .service-name { font-weight: 600; color: #333; }
    </style>
</head>
<body>

<div class="main-container">
    <a href="dashboard.php" class="text-decoration-none text-muted mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
    </a>

    <div class="card-prices">
        <header class="mb-5 text-center">
            <h2 class="fw-bold" style="font-family: 'Playfair Display';">Lista de Precios</h2>
            <p class="text-muted small text-uppercase letter-spacing-1">Administra el valor de tus servicios e insumos</p>
        </header>

        <?php echo $mensaje; ?>

        <div class="table-responsive">
            <table class="table align-middle table-borderless">
                <thead>
                    <tr class="text-muted small border-bottom">
                        <th class="pb-3">TÉCNICA / SERVICIO</th>
                        <th class="pb-3" width="180">VALOR ($)</th>
                        <th class="pb-3 text-end">ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = mysqli_fetch_assoc($servicios)): ?>
                    <tr class="border-bottom">
                        <form method="POST">
                            <td class="py-4">
                                <div class="service-name"><?php echo $s['nombre_servicio']; ?></div>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i> <?php echo $s['duracion_aprox']; ?></small>
                            </td>
                            <td class="py-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 price-input">$</span>
                                    <input type="number" name="new_price" class="form-control border-start-0 price-input" 
                                           value="<?php echo $s['precio']; ?>" step="0.01" required>
                                </div>
                            </td>
                            <td class="py-4 text-end">
                                <input type="hidden" name="id_ser" value="<?php echo $s['id']; ?>">
                                <button type="submit" name="update_price" class="btn btn-gold shadow-sm">
                                    <i class="bi bi-save me-1"></i> Guardar
                                </button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 rounded-4" style="background-color: var(--soft-pink);">
            <p class="small m-0 text-muted">
                <i class="bi bi-info-circle-fill me-2 text-gold"></i> 
                <strong>Nota:</strong> Los precios ingresados aquí se reflejarán inmediatamente en el <strong>Resumen Mensual</strong> de tu Dashboard.
            </p>
        </div>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>