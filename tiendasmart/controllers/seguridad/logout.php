<?php
// controllers/seguridad/logout.php
session_start();
session_destroy(); // Destruye todas las variables de sesión activas
header("Location: /tiendasmart/index.html"); // Redirige al inicio
exit();
?>