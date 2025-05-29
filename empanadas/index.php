<?php
require_once 'config/database.php';

// Obtener productos
$stmt = $conn->query("SELECT * FROM productos WHERE stock > 0");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Empanadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="generar_pdf.js"></script>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">Nuestras Empanadas</h1>

        <p class="text-center lead mb-5">Descubre el auténtico sabor de nuestras empanadas caseras. Preparadas con los ingredientes más frescos y el toque tradicional que te encantará.</p>

        <div class="row">
            <div class="col-md-4">
                <div class="producto-card">
                    <h3>Empanada de Pescado</h3>
                    <p class="descripcion">Deliciosa empanada rellena de pescado fresco</p>
                    <p class="precio">$2.500</p>
                    <button class="btn btn-comprar btn-success" onclick="agregarAlCarrito(1, 'Empanada de Pescado', 2500)">
                        Añadir
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="producto-card">
                    <h3>Empanada de Lentejas</h3>
                    <p class="descripcion">Empanada vegetariana con lentejas y especias</p>
                    <p class="precio">$2.000</p>
                    <button class="btn btn-comprar btn-success" onclick="agregarAlCarrito(2, 'Empanada de Lentejas', 2000)">
                        Añadir
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="producto-card">
                    <h3>Empanada de Maíz</h3>
                    <p class="descripcion">Empanada dulce rellena de maíz tierno y queso</p>
                    <p class="precio">$3.000</p>
                    <button class="btn btn-comprar btn-success" onclick="agregarAlCarrito(3, 'Empanada de Maíz', 3000)">
                        Añadir
                    </button>
                </div>
            </div>
        </div>

        <!-- Carrito de Compras -->
        <div id="carrito" class="mt-5" style="display: none;">
            <h2 class="text-center mb-4">Tu Pedido</h2>
            <div class="table-responsive">
                <table class="table table-bordered pedido-detalle-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="carrito-items">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2"><strong id="total-carrito">$0</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Formulario de Pedido -->
            <form id="formPedido" class="mt-4" onsubmit="realizarPedido(event)">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nombre_cliente" class="form-label">Nombre del Cliente</label>
                        <input type="text" class="form-control" id="nombre_cliente" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="direccion" class="form-label">Dirección de Entrega</label>
                        <input type="text" class="form-control" id="direccion" required>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">Realizar Pedido</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container text-center mt-4">
        <a href="historial_pedidos.php" class="btn btn-secondary me-2">Ver Historial de Pedidos</a>
        <a href="inventario.php" class="btn btn-secondary">Control de Inventario</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let carrito = [];

        function agregarAlCarrito(id, nombre, precio) {
            // Verificar stock disponible
            fetch('verificar_stock.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.stock > 0) {
                        const itemExistente = carrito.find(item => item.id === id);
                        
                        if (itemExistente) {
                            // Verificar si al agregar una unidad más no excede el stock
                            if (itemExistente.cantidad < data.stock) {
                                itemExistente.cantidad++;
                            } else {
                                alert('No hay suficiente stock disponible');
                                return;
                            }
                        } else {
                            carrito.push({
                                id: id,
                                nombre: nombre,
                                precio: precio,
                                cantidad: 1
                            });
                        }
                        
                        actualizarCarrito();
                        document.getElementById('carrito').style.display = 'block';
                    } else {
                        alert('No hay stock disponible para este producto');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al verificar el stock');
                });
        }

        function actualizarCarrito() {
            const tbody = document.getElementById('carrito-items');
            tbody.innerHTML = '';
            let total = 0;

            carrito.forEach((item, index) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;

                tbody.innerHTML += `
                    <tr>
                        <td>${item.nombre}</td>
                        <td>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="actualizarCantidad(${index}, -1)">-</button>
                                <input type="number" class="form-control text-center" value="${item.cantidad}" 
                                       onchange="cambiarCantidad(${index}, this.value)" min="1">
                                <button type="button" class="btn btn-outline-secondary" onclick="actualizarCantidad(${index}, 1)">+</button>
                            </div>
                        </td>
                        <td>$${item.precio.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td>$${subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${index})">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('total-carrito').textContent = `$${total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Mostrar u ocultar la sección del carrito
            if (carrito.length > 0) {
                document.getElementById('carrito').style.display = 'block';
            } else {
                document.getElementById('carrito').style.display = 'none';
            }
        }

        function actualizarCantidad(index, cambio) {
            const nuevaCantidad = carrito[index].cantidad + cambio;
            
            // Verificar stock antes de actualizar
            fetch('verificar_stock.php?id=' + carrito[index].id)
                .then(response => response.json())
                .then(data => {
                    if (nuevaCantidad > 0 && nuevaCantidad <= data.stock) {
                        carrito[index].cantidad = nuevaCantidad;
                        actualizarCarrito();
                    } else if (nuevaCantidad > data.stock) {
                        alert('No hay suficiente stock disponible');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al verificar el stock');
                });
        }

        function cambiarCantidad(index, nuevaCantidad) {
            nuevaCantidad = parseInt(nuevaCantidad);
            
            // Verificar stock antes de actualizar
            fetch('verificar_stock.php?id=' + carrito[index].id)
                .then(response => response.json())
                .then(data => {
                    if (nuevaCantidad > 0 && nuevaCantidad <= data.stock) {
                        carrito[index].cantidad = nuevaCantidad;
                        actualizarCarrito();
                    } else if (nuevaCantidad > data.stock) {
                        alert('No hay suficiente stock disponible');
                        actualizarCarrito(); // Actualizar para mostrar la cantidad correcta
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al verificar el stock');
                });
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            actualizarCarrito();
            if (carrito.length === 0) {
                document.getElementById('carrito').style.display = 'none';
            }
        }

        function realizarPedido(event) {
            event.preventDefault();
            
            if (carrito.length === 0) {
                alert('Agrega productos al carrito primero');
                return;
            }

            const nombreCliente = document.getElementById('nombre_cliente').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const direccion = document.getElementById('direccion').value.trim();

            if (!nombreCliente || !telefono || !direccion) {
                alert('Por favor, completa todos los campos (Nombre, Teléfono, Dirección).');
                return;
            }

            const formData = {
                nombre_cliente: nombreCliente,
                telefono: telefono,
                direccion: direccion,
                items: carrito
            };

            // Deshabilitar el botón de submit para evitar múltiples envíos
            const submitButton = event.target.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando...'; // Cambiar texto del botón
            }

            fetch('procesar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                // Re-habilitar el botón después de la respuesta (éxito o error)
                if (submitButton) {
                     submitButton.disabled = false;
                     submitButton.textContent = 'Realizar Pedido';
                }

                if (!response.ok) {
                    // Intenta leer el cuerpo de la respuesta para un mensaje de error más específico del servidor
                    return response.json().then(err => {
                        throw new Error(err.message || 'Error desconocido del servidor');
                    }).catch(() => {
                         // Si no se puede parsear como JSON, usa el estado HTTP
                        throw new Error(`Error de red o servidor: ${response.status} ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message + '\nID del Pedido: ' + data.pedido_id);
                    // Limpiar el carrito y el formulario
                    carrito = [];
                    actualizarCarrito();
                    document.getElementById('formPedido').reset();

                    // Redirigir al historial de pedidos
                    window.location.href = 'historial_pedidos.php';
                } else {
                    // Si el servidor reporta un error
                    alert('Error al procesar el pedido: ' + data.message);
                }
            })
            .catch(error => {
                // Esto captura errores de red o problemas con la respuesta fetch
                console.error('Error en fetch al procesar pedido:', error);
                 // Re-habilitar el botón en caso de error en la promesa fetch
                 if (submitButton) {
                     submitButton.disabled = false;
                     submitButton.textContent = 'Realizar Pedido';
                 }
                // Muestra un mensaje de error amigable al usuario
                alert('Ocurrió un error de comunicación con el servidor al procesar el pedido. Por favor, inténtalo de nuevo.');
            });
        }
    </script>
</body>
</html>