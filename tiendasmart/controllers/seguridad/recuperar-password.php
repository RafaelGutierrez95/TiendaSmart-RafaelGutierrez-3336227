<?php
// controllers/seguridad/recuperar-password.php
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
    $email            = trim($_POST['email'] ?? '');
    $token            = trim($_POST['token'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: /tiendasmart/views/seguridad/recuperar-password.html?alerta=campos_vacios");
        exit();
    }
    if ($password !== $confirm_password) {
        header("Location: /tiendasmart/views/seguridad/recuperar-password.html?alerta=password_no_coincide");
        exit();
    }
    if (strlen($password) < 8) {
        header("Location: /tiendasmart/views/seguridad/recuperar-password.html?alerta=password_corta");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        try {
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND token_recuperacion = :token LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':correo', $email);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_usuario = $usuario['id_usuario'];

                // Generar nuevo token alfanumérico de 16 caracteres exactos
                $nuevo_token = generarTokenSeguro(16);
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                $sqlUpdate = "UPDATE usuarios SET password = :password, token_recuperacion = :nuevo_token WHERE id_usuario = :id_usuario";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':password', $password_hashed);
                $stmtUpdate->bindParam(':nuevo_token', $nuevo_token);
                $stmtUpdate->bindParam(':id_usuario', $id_usuario);

                if ($stmtUpdate->execute()) {
                    header("Location: /tiendasmart/views/seguridad/login.html?alerta=recuperacion_exitosa&token=" . $nuevo_token);
                    exit();
                }
            } else {
                header("Location: /tiendasmart/views/seguridad/recuperar-password.html?alerta=token_invalido");
                exit();
            }
        } catch(PDOException $e) {
            header("Location: /tiendasmart/views/seguridad/recuperar-password.html?alerta=error_servidor");
            exit();
        }
    }
}
?>