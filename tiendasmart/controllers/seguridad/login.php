<?php
// controllers/seguridad/login.php

session_start();

require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        header("Location: /tiendasmart/views/seguridad/login.html?alerta=campos_vacios");
        exit();
    }

    if (empty($password)) {
        header("Location: /tiendasmart/views/seguridad/login.html?alerta=campos_vacios");
        exit();
    }

    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        try {
            $sql = "SELECT id_usuario, nombre, password, id_rol FROM usuarios WHERE correo = :correo LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':correo', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $usuario['password'])) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['id_rol'] = $usuario['id_rol'];

                    header("Location: /tiendasmart/index.html");
                    exit();
                } else {
                    header("Location: /tiendasmart/views/seguridad/login.html?alerta=password_incorrecta");
                    exit();
                }
            } else {
                header("Location: /tiendasmart/views/seguridad/login.html?alerta=correo_no_existe");
                exit();
            }
        } catch(PDOException $e) {
            header("Location: /tiendasmart/views/seguridad/login.html?alerta=error_servidor");
            exit();
        }
    }
}
?>