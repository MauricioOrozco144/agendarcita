<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Protección: solo administradores
if (!isset($_SESSION['admin']) && !isset($_SESSION['admin_auth'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['admin_auth'] ?? $_SESSION['admin'];
$error = '';
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    $current = trim($_POST['current_password'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Token CSRF inválido. Intenta recargar la página.';
    } elseif (empty($current) || empty($new) || empty($confirm)) {
        $error = 'Por favor completa todos los campos.';
    } elseif ($new !== $confirm) {
        $error = 'La nueva contraseña y su confirmación no coinciden.';
    } elseif (strlen($new) < 8) {
        $error = 'La contraseña nueva debe tener al menos 8 caracteres.';
    } else {
        // Obtener hash actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if (password_verify($current, $row['password'])) {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE usuarios SET password = ? WHERE usuario = ?");
                $upd->bind_param("ss", $hash, $user);
                if ($upd->execute()) {
                    // Evitar reuso del token y regenerar session id
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    session_regenerate_id(true);

                    $success = 'Contraseña actualizada con éxito.';
                } else {
                    $error = 'Error al actualizar la contraseña. Intenta de nuevo.';
                }
                $upd->close();
            } else {
                $error = 'La contraseña actual es incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña | Panel Admin</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        :root{--gold:#c5a059;}
        body{background:#f8f9fa;font-family: 'Poppins', sans-serif;}
        .card{border-radius:12px}
        .btn-gold{background:var(--gold);color:#fff;border:none}
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <div class="mb-3 text-center">
                    <h4 class="fw-bold">Cambiar Contraseña</h4>
                    <p class="text-muted small">Usuario: <?php echo htmlspecialchars($user); ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger small py-2"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success small py-2"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nueva contraseña</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <div class="form-text small">Mínimo 8 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gold px-4">Actualizar</button>
                        <a href="admin.php" class="btn btn-outline-secondary">Volver</a>
                    </div>
                </form>
                <hr>
                <p class="small text-muted mb-0">Por seguridad, usa contraseñas fuertes y elimina scripts temporales que no necesitas.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>