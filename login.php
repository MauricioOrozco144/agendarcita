<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Si ya está logueado, mandarlo directo al admin
if (isset($_SESSION['admin_auth'])) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización básica de entrada
    $user = trim($_POST['usuario']);
    $pass = trim($_POST['password']);

    if (!empty($user) && !empty($pass)) {
        // 2. USO DE SENTENCIAS PREPARADAS (Evita Inyección SQL)
        $stmt = $conn->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user_data = $result->fetch_assoc()) {
            // 3. VERIFICACIÓN SEGURA DE CONTRASEÑA
            // Verifica si el hash guardado coincide con lo que el usuario escribió
            if (password_verify($pass, $user_data['password'])) {
                
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);

                $_SESSION['admin_auth'] = $user_data['usuario'];
                $_SESSION['admin_id'] = $user_data['id']; // Útil para auditoría
                $_SESSION['admin'] = $user_data['usuario']; // Compatibilidad heredada

                header("Location: admin.php");
                exit();
            } else {
                $error = "Credenciales incorrectas.";
            }
        } else {
            $error = "Credenciales incorrectas.";
        }
        $stmt->close();
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Monica Orozco</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        :root { --gold: #c5a059; --dark: #121212; }
        body { 
            background: linear-gradient(135deg, var(--dark) 0%, #252525 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
        }
        .card-login { 
            border-radius: 15px; 
            border-top: 5px solid var(--gold); 
            background-color: #fff;
        }
        .btn-gold { 
            background-color: var(--gold); 
            color: white; 
            font-weight: bold; 
            border: none;
            transition: 0.3s;
        }
        .btn-gold:hover { background-color: #b08d4a; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card card-login shadow-lg p-4">
                <div class="text-center mb-4">
                    <img src="assets/img/logo.png" alt="Logo" style="width: 80px;">
                    <h4 class="mt-3 fw-bold">ADMIN NAILS</h4>
                    <p class="text-muted small">Panel de Gestión de Citas</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger py-2 small text-center">
                        <?php echo htmlspecialchars($error); // Protección XSS ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Usuario</label>
                        <input type="text" name="usuario" class="form-control" placeholder="Ingrese su usuario" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 py-2 mb-3">INICIAR SESIÓN</button>
                    <div class="text-center">
                        <a href="index.php" class="text-muted small text-decoration-none">← Volver al sitio público</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>