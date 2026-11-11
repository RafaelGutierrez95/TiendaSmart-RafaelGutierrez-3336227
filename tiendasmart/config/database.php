<?php
// config/database.php

class Database {
    // Definición de las credenciales de conexión
    private $host = "localhost";
    private $db_name = "tienda_smart"; // Reemplaza con el nombre exacto de tu base de datos
    private $username = "root";  // Usuario por defecto en XAMPP
    private $password = "";      // Sin contraseña por defecto en XAMPP
    public $conn;

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            // Se utiliza el método PDO para la conexión, especificando host y nombre de la BD
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Configurar PDO para que lance excepciones en caso de errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Forzar la codificación UTF-8 para evitar problemas con tildes y ñ
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            // Si hay un error, se captura la excepción y se muestra el mensaje
            echo "Error de conexión a la base de datos: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>