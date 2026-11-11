const SIMBOLO_PESO = String.fromCharCode(36);

document.addEventListener("DOMContentLoaded", async () => { 
    
    // --- MANEJO DE ALERTAS SWEETALERT ---
    const parametrosURL = new URLSearchParams(window.location.search);
    const tipoAlerta = parametrosURL.get('alerta');
    const tokenGenerado = parametrosURL.get('token');

    if (tipoAlerta === 'registro_exitoso' && tokenGenerado) {
        Swal.fire({
            icon: 'success',
            title: '¡Cuenta creada con éxito!',
            html: 'Guarda este código de recuperación en un lugar seguro. Es la <b>única</b> forma de restaurar tu contraseña si la olvidas:<br><br><h3 class="text-success fw-bold border border-2 border-success bg-light px-3 py-2 rounded d-inline-block mt-2" style="letter-spacing: 2px;">' + tokenGenerado + '</h3>',
            confirmButtonColor: '#198754',
            allowOutsideClick: false
        });
        window.history.replaceState(null, null, window.location.pathname);

    } else if (tipoAlerta === 'recuperacion_exitosa' && tokenGenerado) {
        Swal.fire({
            icon: 'success',
            title: '¡Contraseña restaurada!',
            html: 'Tu código anterior ya no sirve. Guarda tu <b>NUEVO</b> código de recuperación de 16 caracteres:<br><br><h3 class="text-success fw-bold border border-2 border-success bg-light px-3 py-2 rounded d-inline-block mt-2" style="letter-spacing: 2px;">' + tokenGenerado + '</h3>',
            confirmButtonColor: '#198754',
            allowOutsideClick: false
        });
        window.history.replaceState(null, null, window.location.pathname);

    } else if (tipoAlerta === 'token_invalido') {
        Swal.fire({
            icon: 'error',
            title: 'Código o correo incorrecto',
            text: 'El correo electrónico o el código de recuperación que ingresaste no coinciden. Verifica e intenta de nuevo.',
            confirmButtonColor: '#dc3545'
        });
        window.history.replaceState(null, null, window.location.pathname);

    } else if (tipoAlerta === 'correo_no_existe') {
        Swal.fire({
            icon: 'error',
            title: 'Cuenta no encontrada',
            text: 'No existe una cuenta registrada con ese correo electrónico.',
            confirmButtonColor: '#dc3545'
        });
        window.history.replaceState(null, null, window.location.pathname);

    } else if (tipoAlerta === 'password_incorrecta') {
        Swal.fire({
            icon: 'error',
            title: 'Contraseña incorrecta',
            text: 'La contraseña ingresada no es válida. Intenta nuevamente.',
            confirmButtonColor: '#dc3545'
        });
        window.history.replaceState(null, null, window.location.pathname);
        
    } else if (tipoAlerta === 'correo_duplicado') {
        Swal.fire({
            icon: 'warning',
            title: 'Correo en uso',
            text: 'Este correo ya está registrado en Tienda Smart. Por favor, intenta iniciar sesión.',
            confirmButtonColor: '#0d6efd'
        });
        window.history.replaceState(null, null, window.location.pathname);
        
    } else if (tipoAlerta === 'campos_vacios') {
        Swal.fire({
            icon: 'error',
            title: 'Campos incompletos',
            text: 'Por favor, asegúrate de llenar todos los campos obligatorios del formulario.',
            confirmButtonColor: '#0d6efd'
        });
        window.history.replaceState(null, null, window.location.pathname);
        
    } else if (tipoAlerta === 'password_no_coincide') {
        Swal.fire({
            icon: 'error',
            title: 'Error en contraseñas',
            text: 'Las contraseñas que ingresaste no coinciden. Por favor, revísalas.',
            confirmButtonColor: '#0d6efd'
        });
        window.history.replaceState(null, null, window.location.pathname);
        
    } else if (tipoAlerta === 'password_corta') {
        Swal.fire({
            icon: 'warning',
            title: 'Contraseña insegura',
            text: 'Tu contraseña debe tener al menos 8 caracteres para ser segura.',
            confirmButtonColor: '#0d6efd'
        });
        window.history.replaceState(null, null, window.location.pathname);
        
    } else if (tipoAlerta === 'error_servidor') {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Ocurrió un problema técnico. Intenta nuevamente más tarde.',
            confirmButtonColor: '#0d6efd'
        });
        window.history.replaceState(null, null, window.location.pathname);
    }

    // 1. Carga de componentes con RUTAS ABSOLUTAS adaptadas para XAMPP
    const cargarComponente = async (url, contenedorId) => {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) return;
        try {
            const respuesta = await fetch('/tiendasmart/' + url);
            if (!respuesta.ok) return; 
            const html = await respuesta.text();
            contenedor.innerHTML = html;
        } catch (error) {
            console.error("Error cargando componente:", error);
        }
    };

    await cargarComponente('components/header.html', 'header-container');
    await cargarComponente('components/footer.html', 'footer-container');

    // Hacer que el Header quede estático (Sticky) en todas las vistas
    const headerContainer = document.getElementById('header-container');
    if (headerContainer) {
        headerContainer.classList.add('sticky-top');
        headerContainer.style.zIndex = '1030';
    }

    // Actualizar burbuja contadora del carrito en el header
    await actualizarContadorHeader();

    // --- VERIFICACIÓN DE SESIÓN DINÁMICA ---
    try {
        const sessionRes = await fetch('/tiendasmart/controllers/seguridad/check_session.php');
        const sessionData = await sessionRes.json();
        
        if (sessionData.logged_in) {
            const loginLink = document.querySelector('a[href="/tiendasmart/views/seguridad/login.html"]');
            if (loginLink) {
                const loginLi = loginLink.parentElement;
                loginLi.innerHTML = 
                    '<div class="dropdown">' +
                        '<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                            '<i class="bi bi-person-check-fill fs-4 text-success me-1"></i>' +
                            '<span class="fw-bold">' + sessionData.nombre + '</span>' +
                        '</a>' +
                        '<ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">' +
                            '<li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>' +
                            '<li><hr class="dropdown-divider"></li>' +
                            '<li><a class="dropdown-item text-danger" href="/tiendasmart/controllers/seguridad/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>' +
                        '</ul>' +
                    '</div>';
            }
        }
    } catch (error) {
        console.error("Error verificando sesión:", error);
    }

    // 2. Lógica global para mostrar/ocultar contraseña
    document.addEventListener("click", (e) => {
        const toggleBtn = e.target.closest(".toggle-password");
        if (!toggleBtn) return;
        
        const targetId = toggleBtn.getAttribute("data-target");
        const passwordInput = document.getElementById(targetId);
        const icon = toggleBtn.querySelector("i");

        if (passwordInput) {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    });

    // Inicializar vistas dinámicas según la página actual
    await cargarProductosIndex();
    await cargarCatalogoDinamico();
    await cargarDetalleProducto();
    await cargarVistaCarrito();
    await cargarResumenPedido();
});

// --- FUNCIÓN PARA ACTUALIZAR EL CONTADOR DEL CARRITO EN EL HEADER ---
const actualizarContadorHeader = async (cantidadDirecta = null) => {
    let totalUnidades = cantidadDirecta;

    if (totalUnidades === null) {
        try {
            const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php?accion=obtener');
            const data = await res.json();
            totalUnidades = data.total_unidades ? parseInt(data.total_unidades, 10) : 0;
        } catch (error) {
            totalUnidades = 0;
        }
    }

    const iconoCarrito = document.querySelector('#header-container a[href*="carrito"], #header-container .bi-cart, #header-container .bi-cart3');
    if (!iconoCarrito) return;

    const contenedorIcono = iconoCarrito.closest('a') ? iconoCarrito.closest('a') : iconoCarrito;
    contenedorIcono.classList.add('position-relative', 'd-inline-block');

    let badge = document.getElementById('badge-carrito-header');
    if (!badge) {
        badge = document.createElement('span');
        badge.id = 'badge-carrito-header';
        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
        badge.style.fontSize = '0.7rem';
        badge.style.transition = 'transform 0.2s ease';
        contenedorIcono.appendChild(badge);
    }

    badge.innerText = totalUnidades;

    if (totalUnidades > 0) {
        badge.style.display = 'inline-block';
        badge.style.transform = 'translate(-50%, -50%) scale(1.3)';
        setTimeout(() => {
            badge.style.transform = 'translate(-50%, -50%) scale(1)';
        }, 200);
    } else {
        badge.style.display = 'none';
    }
};

// --- MAPEO DE IMÁGENES Y CATEGORÍAS DEL CATÁLOGO ---
const obtenerImagenProducto = (codigo) => {
    const mapaImagenes = {
        'PROD-001': '/tiendasmart/assets/images/arroz-diana.jpg',
        'PROD-002': '/tiendasmart/assets/images/leche-colanta.jpg',
        'PROD-003': '/tiendasmart/assets/images/huevos-aa.jpg',
        'PROD-004': '/tiendasmart/assets/images/aceite-gourmet.jpg',
        'PROD-005': '/tiendasmart/assets/images/detergente-ariel.jpg'
    };
    return mapaImagenes[codigo] ? mapaImagenes[codigo] : '/tiendasmart/assets/images/' + codigo + '.jpg';
};

const obtenerNombreCategoria = (prod) => {
    if (prod.nombre_categoria) {
        return prod.nombre_categoria;
    }
    const mapaCategorias = {
        1: 'Víveres',
        2: 'Lácteos',
        3: 'Abarrotes',
        4: 'Aseo',
        5: 'Snacks'
    };
    const id = parseInt(prod.id_categoria, 10);
    return mapaCategorias[id] ? mapaCategorias[id] : 'Víveres';
};

let listaProductosGlobal = [];
let categoriaSeleccionada = 'Todos';

const renderizarProductos = (productos) => {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    if (productos.length === 0) {
        contenedor.innerHTML = 
            '<div class="col-12 text-center py-5 w-100">' +
                '<i class="bi bi-box-seam fs-1 text-muted"></i>' +
                '<p class="text-muted mt-2">No se encontraron productos en esta categoría o búsqueda.</p>' +
            '</div>';
        return;
    }

    contenedor.innerHTML = '';
    productos.forEach(prod => {
        const precioFormateado = SIMBOLO_PESO + parseFloat(prod.precio).toLocaleString('es-CO');
        const rutaImagen = obtenerImagenProducto(prod.codigo);
        const nombreCategoria = obtenerNombreCategoria(prod);
        const stockNum = parseInt(prod.stock_actual, 10);
        const sinStock = stockNum <= 0;
        const badgeColor = sinStock ? 'bg-danger' : 'bg-success';
        const textoBadge = sinStock ? 'Agotado' : prod.estado + ' (' + stockNum + ')';
        const attrDisabled = sinStock ? 'disabled' : '';

        contenedor.innerHTML += 
            '<div class="col">' +
                '<div class="card producto-card h-100 shadow-sm border-0 rounded-4">' +
                    '<span class="badge ' + badgeColor + ' position-absolute top-0 end-0 m-2">' + textoBadge + '</span>' +
                    '<img src="' + rutaImagen + '" class="card-img-top p-3" alt="' + prod.nombre + '" style="height: 200px; object-fit: contain;" onerror="this.src=\'/tiendasmart/assets/images/logo.png\'">' +
                    '<div class="card-body d-flex flex-column text-center">' +
                        '<span class="text-muted small mb-1">' + nombreCategoria + '</span>' +
                        '<h5 class="card-title fw-bold">' + prod.nombre + '</h5>' +
                        '<p class="fs-5 fw-bold text-success mt-auto mb-3">' + precioFormateado + '</p>' +
                        '<div class="d-flex gap-2">' +
                            '<a href="/tiendasmart/views/catalogo/detalle.html?id=' + prod.id_producto + '" class="btn btn-outline-secondary w-50 btn-sm rounded-pill">Ver detalle</a>' +
                            '<button class="btn btn-primary w-50 btn-sm rounded-pill" ' + attrDisabled + ' onclick="agregarAlCarrito(' + prod.id_producto + ', 1)">' +
                                '<i class="bi bi-cart-plus"></i> Agregar' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    });
};

const filtrarCatalogo = () => {
    const inputBusqueda = document.getElementById('buscador-catalogo');
    const texto = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : '';

    const filtrados = listaProductosGlobal.filter(prod => {
        const catProducto = obtenerNombreCategoria(prod);
        let coincideCategoria = false;
        if (categoriaSeleccionada === 'Todos') {
            coincideCategoria = true;
        } else if (catProducto === categoriaSeleccionada) {
            coincideCategoria = true;
        }

        let coincideTexto = false;
        if (prod.nombre.toLowerCase().includes(texto)) {
            coincideTexto = true;
        } else if (prod.codigo.toLowerCase().includes(texto)) {
            coincideTexto = true;
        }

        return coincideCategoria && coincideTexto;
    });

    renderizarProductos(filtrados);
};

// --- CARGAR PRODUCTOS DESTACADOS EN EL INDEX ---
const cargarProductosIndex = async () => {
    const contenedorIndex = document.getElementById('contenedor-productos-index');
    if (!contenedorIndex) return;

    try {
        const respuesta = await fetch('/tiendasmart/controllers/catalogo/obtener_productos.php');
        const productos = await respuesta.json();

        if (!productos || productos.error || productos.length === 0) {
            contenedorIndex.innerHTML = '<p class="text-muted text-center w-100">No hay productos disponibles por el momento.</p>';
            return;
        }

        const destacados = productos.slice(0, 4);
        contenedorIndex.innerHTML = '';

        destacados.forEach(prod => {
            const precioFormateado = SIMBOLO_PESO + parseFloat(prod.precio).toLocaleString('es-CO');
            const rutaImagen = obtenerImagenProducto(prod.codigo);
            const nombreCategoria = obtenerNombreCategoria(prod);
            const stockNum = parseInt(prod.stock_actual, 10);
            const sinStock = stockNum <= 0;
            const badgeColor = sinStock ? 'bg-danger' : 'bg-success';
            const textoBadge = sinStock ? 'Agotado' : prod.estado + ' (' + stockNum + ')';
            const attrDisabled = sinStock ? 'disabled' : '';

            contenedorIndex.innerHTML += 
                '<div class="col">' +
                    '<div class="card producto-card h-100 shadow-sm border-0 rounded-4">' +
                        '<span class="badge ' + badgeColor + ' position-absolute top-0 end-0 m-2">' + textoBadge + '</span>' +
                        '<img src="' + rutaImagen + '" class="card-img-top p-3" alt="' + prod.nombre + '" style="height: 200px; object-fit: contain;" onerror="this.src=\'/tiendasmart/assets/images/logo.png\'">' +
                        '<div class="card-body d-flex flex-column text-center">' +
                            '<span class="text-muted small mb-1">' + nombreCategoria + '</span>' +
                            '<h5 class="card-title fw-bold">' + prod.nombre + '</h5>' +
                            '<p class="fs-5 fw-bold text-success mt-auto mb-3">' + precioFormateado + '</p>' +
                            '<div class="d-flex gap-2">' +
                                '<a href="/tiendasmart/views/catalogo/detalle.html?id=' + prod.id_producto + '" class="btn btn-outline-secondary w-50 btn-sm rounded-pill">Ver detalle</a>' +
                                '<button class="btn btn-primary w-50 btn-sm rounded-pill" ' + attrDisabled + ' onclick="agregarAlCarrito(' + prod.id_producto + ', 1)">' +
                                    '<i class="bi bi-cart-plus"></i> Agregar' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });
    } catch (error) {
        console.error("Error cargando destacados en el index:", error);
    }
};

const cargarCatalogoDinamico = async () => {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    try {
        const respuesta = await fetch('/tiendasmart/controllers/catalogo/obtener_productos.php');
        const productos = await respuesta.json();

        if (productos.error) {
            contenedor.innerHTML = '<p class="text-danger text-center w-100">' + productos.error + '</p>';
            return;
        }

        listaProductosGlobal = productos;
        renderizarProductos(listaProductosGlobal);

        const buscador = document.getElementById('buscador-catalogo');
        if (buscador) {
            buscador.addEventListener('input', filtrarCatalogo);
        }

        const contenedorFiltros = document.getElementById('filtros-categoria');
        if (contenedorFiltros) {
            contenedorFiltros.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-categoria]');
                if (!btn) return;

                contenedorFiltros.querySelectorAll('button').forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-primary');
                });
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'active');

                categoriaSeleccionada = btn.getAttribute('data-categoria');
                filtrarCatalogo();
            });
        }
    } catch (error) {
        console.error("Error cargando el catálogo:", error);
    }
};

let stockMaximoDetalle = 999;

const cargarDetalleProducto = async () => {
    const contenedorDetalle = document.getElementById('contenedor-detalle');
    if (!contenedorDetalle) return;

    const params = new URLSearchParams(window.location.search);
    const idParam = params.get('id');
    const idProducto = idParam ? parseInt(idParam, 10) : 1;

    try {
        const respuesta = await fetch('/tiendasmart/controllers/catalogo/obtener_productos.php?id=' + idProducto);
        const prod = await respuesta.json();

        if (!prod) return;
        if (prod.error) return;

        stockMaximoDetalle = parseInt(prod.stock_actual, 10);

        document.getElementById('detalle-imagen').src = obtenerImagenProducto(prod.codigo);
        document.getElementById('detalle-imagen').alt = prod.nombre;
        document.getElementById('detalle-categoria').innerText = obtenerNombreCategoria(prod);
        document.getElementById('detalle-nombre').innerText = prod.nombre;
        document.getElementById('detalle-precio').innerText = SIMBOLO_PESO + parseFloat(prod.precio).toLocaleString('es-CO');
        document.getElementById('detalle-estado').innerText = prod.estado + ' (' + prod.stock_actual + ' unidades disponibles)';
        document.getElementById('detalle-descripcion').innerText = 'Producto distribuido por ' + prod.proveedor + ' (Código: ' + prod.codigo + '). Garantía de frescura y calidad en Tienda Smart.';

        const btnAgregarDetalle = document.getElementById('btn-agregar-detalle');
        if (btnAgregarDetalle) {
            if (stockMaximoDetalle <= 0) {
                btnAgregarDetalle.disabled = true;
                btnAgregarDetalle.innerText = 'Producto Agotado';
            }
            btnAgregarDetalle.onclick = () => {
                const inputCant = document.getElementById('detalle-cantidad');
                let cant = 1;
                if (inputCant) {
                    cant = parseInt(inputCant.value, 10);
                    if (isNaN(cant) || cant < 1) {
                        cant = 1;
                    }
                }
                agregarAlCarrito(prod.id_producto, cant);
            };
        }
    } catch (error) {
        console.error("Error cargando detalle del producto:", error);
    }
};

// --- LÓGICA DINÁMICA DEL CARRITO Y PEDIDOS ---
const agregarAlCarrito = async (idProducto, cantidad = 1) => {
    try {
        const formData = new FormData();
        formData.append('accion', 'agregar');
        formData.append('id_producto', idProducto);
        formData.append('cantidad', cantidad);

        const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.no_autenticado) {
            Swal.fire({
                icon: 'info',
                title: 'Inicia sesión para comprar',
                text: data.error,
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ir a Iniciar Sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/tiendasmart/views/seguridad/login.html';
                }
            });
            return;
        }

        if (data.ok) {
            await actualizarContadorHeader(data.total_unidades);

            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Agregado al carrito (' + data.total_unidades + ')',
                showConfirmButton: false,
                timer: 1600,
                timerProgressBar: true
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: data.error ? data.error : 'No contamos con más unidades disponibles de este producto.',
                confirmButtonColor: '#0d6efd'
            });
        }
    } catch (error) {
        console.error("Error agregando al carrito:", error);
    }
};

const cargarVistaCarrito = async () => {
    const contenedorItems = document.getElementById('lista-items-carrito');
    if (!contenedorItems) return;

    try {
        const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php?accion=obtener');
        const data = await res.json();

        await actualizarContadorHeader(data.total_unidades);

        if (data.ajustado_por_stock) {
            Swal.fire({
                icon: 'info',
                title: 'Cantidad ajustada al stock disponible',
                text: 'Hemos ajustado las unidades de tu carrito al máximo disponible actualmente en bodega.',
                confirmButtonColor: '#0d6efd'
            });
        }

        const elSubtotal = document.querySelector('.resumen-subtotal');
        const elEnvio = document.querySelector('.resumen-envio');
        const elTotal = document.querySelector('.resumen-total');
        const btnPago = document.getElementById('btn-proceder-pago');

        if (!data.items || data.items.length === 0) {
            contenedorItems.innerHTML = 
                '<div class="text-center py-5">' +
                    '<i class="bi bi-cart-x fs-1 text-muted"></i>' +
                    '<p class="text-muted mt-3 mb-0">Tu carrito está vacío. Agrega productos desde el catálogo.</p>' +
                '</div>';
            if (elSubtotal) elSubtotal.innerText = SIMBOLO_PESO + '0';
            if (elEnvio) elEnvio.innerText = SIMBOLO_PESO + '0';
            if (elTotal) elTotal.innerText = SIMBOLO_PESO + '0';
            if (btnPago) btnPago.disabled = true;
            return;
        }

        if (btnPago) {
            btnPago.disabled = false;
            btnPago.onclick = procesarPagoCarrito;
        }

        contenedorItems.innerHTML = '';
        data.items.forEach(item => {
            const rutaImg = obtenerImagenProducto(item.codigo);
            const precioUnit = SIMBOLO_PESO + parseFloat(item.precio).toLocaleString('es-CO');
            const totalItem = SIMBOLO_PESO + (parseFloat(item.precio) * parseInt(item.cantidad, 10)).toLocaleString('es-CO');
            const stockMax = item.stock_actual ? parseInt(item.stock_actual, 10) : 999;

            contenedorItems.innerHTML += 
                '<div class="item-carrito d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3" data-id="' + item.id_producto + '">' +
                    '<div class="d-flex align-items-center gap-3">' +
                        '<img src="' + rutaImg + '" alt="' + item.nombre + '" class="rounded-3 bg-white p-1" style="width: 70px; height: 70px; object-fit: contain;" onerror="this.src=\'/tiendasmart/assets/images/logo.png\'">' +
                        '<div>' +
                            '<h6 class="fw-bold mb-1">' + item.nombre + '</h6>' +
                            '<span class="text-muted small d-block">' + item.categoria + ' (Máx. ' + stockMax + ' disp.)</span>' +
                            '<p class="precio-unitario text-success fw-bold mb-0">' + precioUnit + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="d-flex align-items-center gap-3">' +
                        '<div class="input-group input-group-sm" style="width: 120px;">' +
                            '<button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadCarrito(' + item.id_producto + ', ' + (parseInt(item.cantidad, 10) - 1) + ')">-</button>' +
                            '<input type="text" class="form-control text-center bg-white" value="' + item.cantidad + '" readonly>' +
                            '<button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadCarrito(' + item.id_producto + ', ' + (parseInt(item.cantidad, 10) + 1) + ')">+</button>' +
                        '</div>' +
                        '<span class="precio-total-item fw-bold text-dark" style="min-width: 80px; text-align: right;">' + totalItem + '</span>' +
                        '<button class="btn btn-outline-danger btn-sm rounded-circle" title="Eliminar producto" onclick="eliminarItemCarrito(' + item.id_producto + ')">' +
                            '<i class="bi bi-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<hr class="text-muted">';
        });

        if (elSubtotal) elSubtotal.innerText = SIMBOLO_PESO + parseFloat(data.subtotal).toLocaleString('es-CO');
        if (elEnvio) elEnvio.innerText = SIMBOLO_PESO + parseFloat(data.envio).toLocaleString('es-CO');
        if (elTotal) elTotal.innerText = SIMBOLO_PESO + parseFloat(data.total).toLocaleString('es-CO');
    } catch (error) {
        console.error("Error cargando vista del carrito:", error);
    }
};

const cambiarCantidadCarrito = async (idProducto, nuevaCantidad) => {
    if (nuevaCantidad < 1) {
        await eliminarItemCarrito(idProducto);
        return;
    }
    const formData = new FormData();
    formData.append('accion', 'actualizar');
    formData.append('id_producto', idProducto);
    formData.append('cantidad', nuevaCantidad);

    const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();

    if (!data.ok && data.error) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock límite alcanzado',
            text: data.error,
            confirmButtonColor: '#0d6efd'
        });
    }

    await cargarVistaCarrito();
};

const eliminarItemCarrito = async (idProducto) => {
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_producto', idProducto);

    await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php', {
        method: 'POST',
        body: formData
    });
    await cargarVistaCarrito();
};

const procesarPagoCarrito = async () => {
    try {
        const formData = new FormData();
        formData.append('accion', 'procesar_pedido');

        const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.no_autenticado) {
            Swal.fire({
                icon: 'info',
                title: 'Inicia sesión para confirmar',
                text: data.error,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Ir a Iniciar Sesión'
            }).then(() => {
                window.location.href = '/tiendasmart/views/seguridad/login.html';
            });
            return;
        }

        if (data.ok) {
            window.location.href = '/tiendasmart/views/pedido/resumen.html';
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención con el stock',
                text: data.error ? data.error : 'No se pudo procesar el pedido.'
            });
            await cargarVistaCarrito();
        }
    } catch (error) {
        console.error("Error procesando el pedido:", error);
    }
};

const cargarResumenPedido = async () => {
    const contenedorResumen = document.getElementById('contenedor-resumen-pedido');
    if (!contenedorResumen) return;

    try {
        const res = await fetch('/tiendasmart/controllers/carrito/gestionar_carrito.php?accion=obtener_pedido');
        const data = await res.json();

        if (data.ok && data.pedido) {
            const p = data.pedido;
            const elOrden = document.getElementById('resumen-orden');
            const elFecha = document.getElementById('resumen-fecha');
            const elMetodo = document.getElementById('resumen-metodo');
            const elDireccion = document.getElementById('resumen-direccion');
            const elTotal = document.getElementById('resumen-total-pagado');

            if (elOrden) elOrden.innerText = p.numero_orden;
            if (elFecha) elFecha.innerText = p.fecha;
            if (elMetodo) elMetodo.innerText = p.metodo_pago;
            if (elDireccion) elDireccion.innerText = p.direccion;
            if (elTotal) elTotal.innerText = SIMBOLO_PESO + parseFloat(p.total).toLocaleString('es-CO');
        }
    } catch (error) {
        console.error("Error cargando el resumen del pedido:", error);
    }
};

// 3. Lógica para los botones de sumar y restar cantidad en detalle.html con tope de stock
document.addEventListener("click", (e) => {
    const contenedorDetalle = e.target.closest("#contenedor-detalle");
    if (!contenedorDetalle) return;

    if (e.target.closest(".input-group")) {
        const btn = e.target.closest("button");
        if (!btn) return;

        const input = btn.parentElement.querySelector("input");
        if (!input) return;

        let valorActual = parseInt(input.value, 10);
        if (isNaN(valorActual)) {
            valorActual = 1;
        }

        if (btn.innerText.includes("+")) {
            if (valorActual < stockMaximoDetalle) {
                input.value = valorActual + 1;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock límite',
                    text: 'Solo contamos con ' + stockMaximoDetalle + ' unidades disponibles de este producto.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        } else if (btn.innerText.includes("-") && valorActual > 1) {
            input.value = valorActual - 1;
        }
    }
});