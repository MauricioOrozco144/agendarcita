<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Limpiar todas las variables de sesión en memoria
$_SESSION = array();

// 2. Si se desea destruir la sesión completamente, también se debe borrar la cookie de sesión.
// Nota: ¡Esto destruirá la sesión y no solo los datos de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir la sesión en el servidor
session_destroy();

// 4. Redirigir con un parámetro de confirmación (opcional)
header("Location: login.php?status=logged_out");
exit();
?>