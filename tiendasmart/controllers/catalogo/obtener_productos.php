<?php
// controllers/catalogo/obtener_productos.php

require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        if (isset($_GET['id'])) {
            if (is_numeric($_GET['id'])) {
                $id = (int) $_GET['id'];
                $sql = "SELECT p.id_producto, p.id_categoria, c.nombre_categoria, p.codigo, p.nombre, p.precio, p.stock_actual, p.proveedor, p.estado 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                        WHERE p.id_producto = :id LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($producto) {
                    echo json_encode($producto);
                } else {
                    echo json_encode(['error' => 'Producto no encontrado']);
                }
                exit();
            }
        }

        $sql = "SELECT p.id_producto, p.id_categoria, c.nombre_categoria, p.codigo, p.nombre, p.precio, p.stock_actual, p.proveedor, p.estado 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                WHERE p.estado = 'Disponible' 
                ORDER BY p.id_producto ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($productos);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => 'Error al consultar el catalogo']);
    }
}
?>