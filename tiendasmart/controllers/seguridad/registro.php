<?php
// controllers/seguridad/registro.php
require_once '../../config/database.php';

function generarTokenSeguro($longitud = 16) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    $max = strlen($caracteres) - 1;
    for ($i = 0; $i < $longitud; $i++) {
        $token .= $caracteres[random_int(0, $max)];
    }
    return $token;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre           = trim($_POST['nombre'] ?? '');
    $telefono         = trim($_POST['telefono'] ?? '');
    $direccion        = trim($_POST['direccion'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nombre) || empty($telefono) || empty($direccion) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: /tiendasmart/views/seguridad/registro.html?alerta=campos_vacios");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: /tiendasmart/views/seguridad/registro.html?alerta=password_no_coincide");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: /tiendasmart/views/seguridad/registro.html?alerta=password_corta");
        exit();
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $id_rol = 3; 
    
    // Token alfanumérico real de 16 caracteres
    $token_recuperacion = generarTokenSeguro(16);

    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        try {
            $sql = "INSERT INTO usuarios (nombre, correo, password, telefono, direccion, id_rol, token_recuperacion) 
                    VALUES (:nombre, :correo, :password, :telefono, :direccion, :id_rol, :token_recuperacion)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $email);
            $stmt->bindParam(':password', $password_hashed);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->bindParam(':token_recuperacion', $token_recuperacion);
            
            if ($stmt->execute()) {
                header("Location: /tiendasmart/views/seguridad/login.html?alerta=registro_exitoso&token=" . $token_recuperacion);
                exit();
            }
            
        } catch(PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
                header("Location: /tiendasmart/views/seguridad/registro.html?alerta=correo_duplicado");
                exit();
            } else {
                header("Location: /tiendasmart/views/seguridad/registro.html?alerta=error_servidor");
                exit();
            }
        }
    }
}
?>