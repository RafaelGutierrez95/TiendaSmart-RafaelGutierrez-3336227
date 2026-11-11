<?php
// probar_conexion.php

// 1. Llamar al archivo de configuracion que acabamos de crear
require_once 'config/database.php';

// 2. Instanciar la clase y ejecutar el metodo de conexion
$database = new Database();
$conexion = $database->getConnection();

// 3. Validar si la conexion fue exitosa
if ($conexion) {
    echo "<h1>Conexion exitosa a la base de datos Tienda Smart!!</h1>";
    echo "<p>El puente entre PHP y MySQL esta funcionando perfectamente.</p>";
} else {
    echo "<h1>Error en la conexion X</h1>";
}
?>