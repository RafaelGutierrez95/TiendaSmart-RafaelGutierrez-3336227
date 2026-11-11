<?php
// controllers/seguridad/check_session.php
session_start();

// Le decimos al navegador que vamos a enviar datos en formato JSON
header('Content-Type: application/json');

// Verificamos si existe la variable de sesión 'id_usuario'
if (isset($_SESSION['id_usuario'])) {
    // Si existe, enviamos un JSON diciendo que sí hay sesión y mandamos el nombre
    echo json_encode([
        'logged_in' => true,
        'nombre' => $_SESSION['nombre']
    ]);
} else {
    // Si no existe, enviamos un JSON diciendo que no hay sesión
    echo json_encode([
        'logged_in' => false
    ]);
}
?>