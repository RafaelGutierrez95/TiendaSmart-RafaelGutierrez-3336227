<?php
// controllers/carrito/gestionar_carrito.php

session_start();

// Configurar la zona horaria exacta de Colombia
date_default_timezone_set('America/Bogota');

require_once '../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function contarUnidadesCarrito() {
    $totalUnidades = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $cant =
                (int) $item['cantidad'];
            $totalUnidades =
                $totalUnidades +
                $cant;
        }
    }
    return $totalUnidades;
}

function insertarSegunColumnas(
    $conn,
    $tabla,
    $datos
) {
    try {
        $sqlCols =
            "SHOW COLUMNS FROM " .
            $tabla;
        $stmtCols =
            $conn->query($sqlCols);
        $columnas =
            $stmtCols->fetchAll(PDO::FETCH_ASSOC);

        $camposInsertar = [];
        $marcadores = [];
        $valores = [];

        foreach ($columnas as $col) {
            $nombreCol =
                $col['Field'];
            $extra =
                strtolower($col['Extra'] ?? '');

            if (strpos($extra, 'auto_increment') !== false) {
                continue;
            }
            if (strpos($extra, 'generated') !== false) {
                continue;
            }

            if (array_key_exists($nombreCol, $datos)) {
                $camposInsertar[] =
                    $nombreCol;
                $marcador =
                    ':' .
                    $nombreCol;
                $marcadores[] =
                    $marcador;
                $valores[$marcador] =
                    $datos[$nombreCol];
            } elseif ($col['Null'] === 'NO') {
                if ($col['Default'] === null) {
                    $tipo =
                        strtolower($col['Type']);
                    $camposInsertar[] =
                        $nombreCol;
                    $marcador =
                        ':' .
                        $nombreCol;
                    $marcadores[] =
                        $marcador;

                    if (strpos($tipo, 'int') !== false) {
                        $valores[$marcador] = 1;
                    } elseif (strpos($tipo, 'decimal') !== false) {
                        $valores[$marcador] = 0;
                    } elseif (strpos($tipo, 'date') !== false) {
                        $valores[$marcador] = date('Y-m-d H:i:s');
                    } else {
                        $valores[$marcador] = 'Confirmado';
                    }
                }
            }
        }

        if (empty($camposInsertar)) {
            return 0;
        }

        $listaCampos =
            implode(', ', $camposInsertar);
        $listaMarcadores =
            implode(', ', $marcadores);

        $sqlInsert =
            "INSERT INTO " .
            $tabla .
            " (" .
            $listaCampos .
            ") VALUES (" .
            $listaMarcadores .
            ")";

        $stmtInsert =
            $conn->prepare($sqlInsert);
        $stmtInsert->execute($valores);

        return (int) $conn->lastInsertId();
    } catch (Throwable $e) {
        return 0;
    }
}

function obtenerOCrearCarritoBd(
    $conn,
    $idUsuario
) {
    try {
        $sqlCols =
            "SHOW COLUMNS FROM carrito";
        $stmtCols =
            $conn->query($sqlCols);
        $columnas =
            $stmtCols->fetchAll(PDO::FETCH_ASSOC);

        $colPk = 'id_carrito';
        $colCliente = 'id_cliente';

        foreach ($columnas as $c) {
            if ($c['Key'] === 'PRI') {
                $colPk = $c['Field'];
            }
            if ($c['Field'] === 'id_usuario') {
                $colCliente = 'id_usuario';
            } elseif ($c['Field'] === 'id_cliente') {
                $colCliente = 'id_cliente';
            }
        }

        $sqlBuscar =
            "SELECT " .
            $colPk .
            " FROM carrito WHERE " .
            $colCliente .
            " = :id_usu ORDER BY " .
            $colPk .
            " DESC LIMIT 1";
        $stmtB =
            $conn->prepare($sqlBuscar);
        $stmtB->bindParam(
            ':id_usu',
            $idUsuario,
            PDO::PARAM_INT
        );
        $stmtB->execute();
        $fila =
            $stmtB->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            return (int) $fila[$colPk];
        }

        return insertarSegunColumnas(
            $conn,
            'carrito',
            [
                'id_usuario' => $idUsuario,
                'id_cliente' => $idUsuario,
                'fecha' => date('Y-m-d H:i:s'),
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'estado' => 'Activo'
            ]
        );
    } catch (Throwable $e) {
        return 0;
    }
}

function sincronizarDetalleCarritoBd(
    $conn,
    $idCarritoBd,
    $idProducto,
    $cantidad,
    $precioUnitario
) {
    if ($idCarritoBd <= 0) {
        return;
    }
    try {
        $sqlExiste =
            "SELECT cantidad FROM detalle_carrito WHERE id_carrito = :idc AND id_producto = :idp LIMIT 1";
        $stmtEx =
            $conn->prepare($sqlExiste);
        $stmtEx->bindParam(
            ':idc',
            $idCarritoBd,
            PDO::PARAM_INT
        );
        $stmtEx->bindParam(
            ':idp',
            $idProducto,
            PDO::PARAM_INT
        );
        $stmtEx->execute();
        $filaEx =
            $stmtEx->fetch(PDO::FETCH_ASSOC);

        if ($filaEx) {
            $sqlUpd =
                "UPDATE detalle_carrito SET cantidad = :cant WHERE id_carrito = :idc AND id_producto = :idp";
            $stmtUpd =
                $conn->prepare($sqlUpd);
            $stmtUpd->bindParam(
                ':cant',
                $cantidad,
                PDO::PARAM_INT
            );
            $stmtUpd->bindParam(
                ':idc',
                $idCarritoBd,
                PDO::PARAM_INT
            );
            $stmtUpd->bindParam(
                ':idp',
                $idProducto,
                PDO::PARAM_INT
            );
            $stmtUpd->execute();
        } else {
            $subtotalItem =
                (float) $precioUnitario *
                (int) $cantidad;

            insertarSegunColumnas(
                $conn,
                'detalle_carrito',
                [
                    'id_carrito' => $idCarritoBd,
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => (float) $precioUnitario,
                    'precio' => (float) $precioUnitario,
                    'subtotal' => $subtotalItem
                ]
            );
        }
    } catch (Throwable $e) {
        // Continúa en caso de restricción específica
    }
}

$db = new Database();
$conn =
    $db->getConnection();

if ($conn) {
    try {
        $conn->exec("SET time_zone = '-05:00'");
    } catch (Throwable $e) {
        // Continúa con date_default_timezone_set de PHP
    }
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$accion =
    $_REQUEST['accion'] ?? 'obtener';

if ($accion === 'agregar') {
    // Exigir sesión iniciada para poder agregar productos al carrito
    if (empty($_SESSION['id_usuario'])) {
        echo json_encode([
            'ok' => false,
            'no_autenticado' => true,
            'error' => 'Debes iniciar sesión en tu cuenta para agregar productos al carrito.'
        ]);
        exit();
    }

    $idUsuarioActual =
        (int) $_SESSION['id_usuario'];
    $idProducto =
        (int) ($_POST['id_producto'] ?? 0);
    $cantidad =
        (int) ($_POST['cantidad'] ?? 1);

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    if ($idProducto > 0) {
        if ($conn) {
            $sql =
                "SELECT p.id_producto, p.id_categoria, c.nombre_categoria, p.codigo, p.nombre, p.precio, p.stock_actual " .
                "FROM productos p " .
                "LEFT JOIN categorias c ON p.id_categoria = c.id_categoria " .
                "WHERE p.id_producto = :id LIMIT 1";
            $stmt =
                $conn->prepare($sql);
            $stmt->bindParam(
                ':id',
                $idProducto,
                PDO::PARAM_INT
            );
            $stmt->execute();
            $prod =
                $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prod) {
                $stockDisponible =
                    (int) $prod['stock_actual'];
                $idStr =
                    (string) $idProducto;
                $cantidadEnCarrito = 0;

                if (isset($_SESSION['carrito'][$idStr])) {
                    $cantidadEnCarrito =
                        (int) $_SESSION['carrito'][$idStr]['cantidad'];
                }

                $nuevaCant =
                    $cantidadEnCarrito +
                    $cantidad;

                if ($stockDisponible <= 0) {
                    echo json_encode([
                        'ok' => false,
                        'error' => 'Este producto se encuentra agotado en bodega.',
                        'stock_disponible' => 0,
                        'total_unidades' => contarUnidadesCarrito()
                    ]);
                    exit();
                }

                if ($nuevaCant > $stockDisponible) {
                    $mensajeStock =
                        'No contamos con más stock de ' .
                        $prod['nombre'] .
                        '. Solo hay ' .
                        $stockDisponible .
                        ' unidades disponibles (ya tienes ' .
                        $cantidadEnCarrito .
                        ' en tu carrito).';
                    echo json_encode([
                        'ok' => false,
                        'error' => $mensajeStock,
                        'stock_disponible' => $stockDisponible,
                        'total_unidades' => contarUnidadesCarrito()
                    ]);
                    exit();
                }

                if (isset($_SESSION['carrito'][$idStr])) {
                    $_SESSION['carrito'][$idStr]['cantidad'] =
                        $nuevaCant;
                    $_SESSION['carrito'][$idStr]['stock_actual'] =
                        $stockDisponible;
                } else {
                    $_SESSION['carrito'][$idStr] = [
                        'id_producto' => (int) $prod['id_producto'],
                        'codigo' => $prod['codigo'],
                        'nombre' => $prod['nombre'],
                        'categoria' => $prod['nombre_categoria'] ?? 'Víveres',
                        'precio' => (float) $prod['precio'],
                        'cantidad' => $nuevaCant,
                        'stock_actual' => $stockDisponible
                    ];
                }

                $idCarritoBd =
                    obtenerOCrearCarritoBd(
                        $conn,
                        $idUsuarioActual
                    );

                sincronizarDetalleCarritoBd(
                    $conn,
                    $idCarritoBd,
                    $idProducto,
                    $nuevaCant,
                    (float) $prod['precio']
                );

                echo json_encode([
                    'ok' => true,
                    'nombre' => $prod['nombre'],
                    'total_unidades' => contarUnidadesCarrito()
                ]);
                exit();
            }
        }
    }

    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo agregar el producto',
        'total_unidades' => contarUnidadesCarrito()
    ]);
    exit();
}

if ($accion === 'actualizar') {
    $idProducto =
        (string) ($_POST['id_producto'] ?? '');
    $cantidad =
        (int) ($_POST['cantidad'] ?? 1);

    if (isset($_SESSION['carrito'][$idProducto])) {
        $idNum =
            (int) $idProducto;
        $precioUnit =
            (float) $_SESSION['carrito'][$idProducto]['precio'];

        if ($cantidad > 0) {
            $stockReal = 9999;
            $nombreProd =
                $_SESSION['carrito'][$idProducto]['nombre'];

            if ($conn) {
                $sqlSt =
                    "SELECT nombre, stock_actual FROM productos WHERE id_producto = :id LIMIT 1";
                $stmtSt =
                    $conn->prepare($sqlSt);
                $stmtSt->bindParam(
                    ':id',
                    $idNum,
                    PDO::PARAM_INT
                );
                $stmtSt->execute();
                $filaSt =
                    $stmtSt->fetch(PDO::FETCH_ASSOC);

                if ($filaSt) {
                    $stockReal =
                        (int) $filaSt['stock_actual'];
                    $nombreProd =
                        $filaSt['nombre'];
                }
            }

            if ($cantidad > $stockReal) {
                $_SESSION['carrito'][$idProducto]['cantidad'] =
                    $stockReal;
                $_SESSION['carrito'][$idProducto]['stock_actual'] =
                    $stockReal;

                if ($conn) {
                    if (!empty($_SESSION['id_usuario'])) {
                        $idCar =
                            obtenerOCrearCarritoBd(
                                $conn,
                                (int) $_SESSION['id_usuario']
                            );
                        sincronizarDetalleCarritoBd(
                            $conn,
                            $idCar,
                            $idNum,
                            $stockReal,
                            $precioUnit
                        );
                    }
                }

                $msgLimite =
                    'No contamos con más stock de ' .
                    $nombreProd .
                    '. El máximo disponible es de ' .
                    $stockReal .
                    ' unidades.';

                echo json_encode([
                    'ok' => false,
                    'error' => $msgLimite,
                    'stock_disponible' => $stockReal,
                    'total_unidades' => contarUnidadesCarrito()
                ]);
                exit();
            }

            $_SESSION['carrito'][$idProducto]['cantidad'] =
                $cantidad;
            $_SESSION['carrito'][$idProducto]['stock_actual'] =
                $stockReal;

            if ($conn) {
                if (!empty($_SESSION['id_usuario'])) {
                    $idCar =
                        obtenerOCrearCarritoBd(
                            $conn,
                            (int) $_SESSION['id_usuario']
                        );
                    sincronizarDetalleCarritoBd(
                        $conn,
                        $idCar,
                        $idNum,
                        $cantidad,
                        $precioUnit
                    );
                }
            }
        } else {
            unset($_SESSION['carrito'][$idProducto]);
        }
    }

    echo json_encode([
        'ok' => true,
        'total_unidades' => contarUnidadesCarrito()
    ]);
    exit();
}

if ($accion === 'eliminar') {
    $idProducto =
        (string) ($_POST['id_producto'] ?? '');

    if (isset($_SESSION['carrito'][$idProducto])) {
        $idNum =
            (int) $idProducto;
        unset($_SESSION['carrito'][$idProducto]);

        if ($conn) {
            if (!empty($_SESSION['id_usuario'])) {
                try {
                    $idCar =
                        obtenerOCrearCarritoBd(
                            $conn,
                            (int) $_SESSION['id_usuario']
                        );
                    $sqlDel =
                        "DELETE FROM detalle_carrito WHERE id_carrito = :idc AND id_producto = :idp";
                    $stmtDel =
                        $conn->prepare($sqlDel);
                    $stmtDel->bindParam(
                        ':idc',
                        $idCar,
                        PDO::PARAM_INT
                    );
                    $stmtDel->bindParam(
                        ':idp',
                        $idNum,
                        PDO::PARAM_INT
                    );
                    $stmtDel->execute();
                } catch (Throwable $e) {
                    // Continúa si ya fue eliminado
                }
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'total_unidades' => contarUnidadesCarrito()
    ]);
    exit();
}

if ($accion === 'procesar_pedido') {
    if (empty($_SESSION['id_usuario'])) {
        echo json_encode([
            'ok' => false,
            'no_autenticado' => true,
            'error' => 'Debes iniciar sesión para confirmar tu pedido.'
        ]);
        exit();
    }

    if (empty($_SESSION['carrito'])) {
        echo json_encode([
            'ok' => false,
            'error' => 'Tu carrito está vacío'
        ]);
        exit();
    }

    if ($conn) {
        foreach ($_SESSION['carrito'] as $keyId => $itemVal) {
            $idVerif =
                (int) $itemVal['id_producto'];
            $cantSolicitada =
                (int) $itemVal['cantidad'];

            $sqlVerif =
                "SELECT nombre, stock_actual FROM productos WHERE id_producto = :id LIMIT 1";
            $stmtVerif =
                $conn->prepare($sqlVerif);
            $stmtVerif->bindParam(
                ':id',
                $idVerif,
                PDO::PARAM_INT
            );
            $stmtVerif->execute();
            $prodBd =
                $stmtVerif->fetch(PDO::FETCH_ASSOC);

            if ($prodBd) {
                $stockBd =
                    (int) $prodBd['stock_actual'];
                if ($cantSolicitada > $stockBd) {
                    $_SESSION['carrito'][$keyId]['cantidad'] =
                        $stockBd;
                    $msgErr =
                        'El producto ' .
                        $prodBd['nombre'] .
                        ' solo cuenta con ' .
                        $stockBd .
                        ' unidades en stock. Hemos ajustado tu carrito.';
                    echo json_encode([
                        'ok' => false,
                        'error' => $msgErr
                    ]);
                    exit();
                }
            }
        }
    }

    $idUsuario =
        (int) $_SESSION['id_usuario'];
    $nombreCliente =
        $_SESSION['nombre'] ?? 'Cliente Tienda Smart';
    $direccionCliente =
        'Sede Principal';

    if ($conn) {
        $sqlUser =
            "SELECT id_usuario, nombre, direccion FROM usuarios WHERE id_usuario = :id LIMIT 1";
        $stmtU =
            $conn->prepare($sqlUser);
        $stmtU->bindParam(
            ':id',
            $idUsuario,
            PDO::PARAM_INT
        );
        $stmtU->execute();
        $uRow =
            $stmtU->fetch(PDO::FETCH_ASSOC);

        if ($uRow) {
            $nombreCliente =
                $uRow['nombre'];
            if (!empty($uRow['direccion'])) {
                $direccionCliente =
                    $uRow['direccion'];
            }
        }
    }

    $subtotal = 0;
    $itemsPedido = [];

    foreach ($_SESSION['carrito'] as $item) {
        $sub =
            (float) $item['precio'] *
            (int) $item['cantidad'];
        $subtotal =
            $subtotal +
            $sub;
        $itemsPedido[] = $item;
    }

    $envio = 500;
    $totalPagar =
        $subtotal +
        $envio;
    $fechaActual =
        date('Y-m-d H:i:s');
    $codigoOrden =
        'TS-' .
        rand(10000, 99999);

    $idPedidoInsertado = 0;

    if ($conn) {
        $idPedidoInsertado =
            insertarSegunColumnas(
                $conn,
                'pedidos',
                [
                    'id_usuario' => $idUsuario,
                    'id_cliente' => $idUsuario,
                    'fecha' => $fechaActual,
                    'fecha_pedido' => $fechaActual,
                    'total' => $totalPagar,
                    'monto_total' => $totalPagar,
                    'valor_total' => $totalPagar,
                    'direccion_envio' => $direccionCliente,
                    'direccion' => $direccionCliente,
                    'metodo_pago' => 'Pago Contraentrega',
                    'estado' => 'Confirmado'
                ]
            );

        if ($idPedidoInsertado > 0) {
            $codigoOrden =
                'TS-000' .
                $idPedidoInsertado;
        }

        foreach ($itemsPedido as $it) {
            $idProdItem =
                (int) $it['id_producto'];
            $cantItem =
                (int) $it['cantidad'];
            $precioItem =
                (float) $it['precio'];
            $subItem =
                $precioItem *
                $cantItem;

            if ($idPedidoInsertado > 0) {
                insertarSegunColumnas(
                    $conn,
                    'detalle_pedido',
                    [
                        'id_pedido' => $idPedidoInsertado,
                        'id_producto' => $idProdItem,
                        'cantidad' => $cantItem,
                        'precio_unitario' => $precioItem,
                        'precio' => $precioItem,
                        'subtotal' => $subItem
                    ]
                );
            }

            try {
                $sqlStock =
                    "UPDATE productos SET stock_actual = GREATEST(stock_actual - :cant, 0) WHERE id_producto = :idp";
                $stmtStock =
                    $conn->prepare($sqlStock);
                $stmtStock->bindParam(
                    ':cant',
                    $cantItem,
                    PDO::PARAM_INT
                );
                $stmtStock->bindParam(
                    ':idp',
                    $idProdItem,
                    PDO::PARAM_INT
                );
                $stmtStock->execute();
            } catch (Throwable $e) {
                // Continuar si el stock no requiere ajuste
            }
        }

        if ($idPedidoInsertado > 0) {
            insertarSegunColumnas(
                $conn,
                'facturas',
                [
                    'id_pedido' => $idPedidoInsertado,
                    'id_usuario' => $idUsuario,
                    'id_cliente' => $idUsuario,
                    'numero_factura' => $codigoOrden,
                    'codigo' => $codigoOrden,
                    'fecha' => $fechaActual,
                    'fecha_emision' => $fechaActual,
                    'fecha_factura' => $fechaActual,
                    'subtotal' => $subtotal,
                    'total' => $totalPagar,
                    'monto_total' => $totalPagar,
                    'metodo_pago' => 'Pago Contraentrega',
                    'estado' => 'Pagada'
                ]
            );
        }
    }

    $_SESSION['ultimo_pedido'] = [
        'numero_orden' => '#' . $codigoOrden,
        'fecha' => date('d/m/Y h:i A'),
        'metodo_pago' => 'Pago Contraentrega',
        'direccion' => $direccionCliente,
        'cliente' => $nombreCliente,
        'subtotal' => $subtotal,
        'envio' => $envio,
        'total' => $totalPagar,
        'items' => $itemsPedido
    ];

    $_SESSION['carrito'] = [];

    echo json_encode([
        'ok' => true,
        'numero_orden' => '#' . $codigoOrden,
        'total_unidades' => 0
    ]);
    exit();
}

if ($accion === 'obtener_pedido') {
    if (isset($_SESSION['ultimo_pedido'])) {
        echo json_encode([
            'ok' => true,
            'pedido' => $_SESSION['ultimo_pedido']
        ]);
        exit();
    }

    echo json_encode([
        'ok' => false,
        'error' => 'No hay pedido reciente'
    ]);
    exit();
}

// Acción por defecto: obtener carrito sincronizando con el stock real en BD
$huboAjusteStock = false;

if ($conn) {
    foreach ($_SESSION['carrito'] as $k => $v) {
        $idProdCheck =
            (int) $v['id_producto'];
        $sqlCheck =
            "SELECT stock_actual FROM productos WHERE id_producto = :id LIMIT 1";
        $stmtCheck =
            $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(
            ':id',
            $idProdCheck,
            PDO::PARAM_INT
        );
        $stmtCheck->execute();
        $rowCheck =
            $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($rowCheck) {
            $stockMax =
                (int) $rowCheck['stock_actual'];
            $_SESSION['carrito'][$k]['stock_actual'] =
                $stockMax;

            if ($stockMax <= 0) {
                unset($_SESSION['carrito'][$k]);
                $huboAjusteStock = true;
            } elseif ((int) $v['cantidad'] > $stockMax) {
                $_SESSION['carrito'][$k]['cantidad'] =
                    $stockMax;
                $huboAjusteStock = true;
            }
        }
    }
}

$lista =
    array_values($_SESSION['carrito']);
$subtotalCalc = 0;

foreach ($lista as $elemento) {
    $subtotalCalc =
        $subtotalCalc +
        ((float) $elemento['precio'] * (int) $elemento['cantidad']);
}

$costoEnvio =
    count($lista) > 0 ? 500 : 0;
$totalCalc =
    $subtotalCalc +
    $costoEnvio;

echo json_encode([
    'ok' => true,
    'items' => $lista,
    'subtotal' => $subtotalCalc,
    'envio' => $costoEnvio,
    'total' => $totalCalc,
    'total_unidades' => contarUnidadesCarrito(),
    'ajustado_por_stock' => $huboAjusteStock
]);
?>