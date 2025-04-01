<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'autoload.php';

// Importar clases necesarias
use App\Core\Database\MySQLDatabase;
use App\Shop\Repositories\ProductRepository;

// Verificar si el usuario está logueado
if (!isset($_SESSION['correo'])) {
    echo "<script>
            alert('Debes iniciar sesión para ver los productos de la tienda.');
            window.location.href='../login.html';
          </script>";
    exit();
}

// Cargar configuración
$config = require_once '../src/Config/Config.php';

// Crear instancia de la base de datos
$db = new MySQLDatabase($config['database']);

// Crear instancia del repositorio de productos
$productRepository = new ProductRepository($db);

// Obtener todas las categorías
$categorias = $productRepository->getAllCategories();

// Obtener todos los productos para usar en JavaScript
$productos = $productRepository->findAll();

// Convertir objetos Product a arrays para JSON
$productosArray = [];
foreach ($productos as $producto) {
    $productosArray[] = [
        'producto_ID' => $producto->getId(),
        'nombre_producto' => $producto->getName(),
        'descripcion' => $producto->getDescription(),
        'precio' => $producto->getPrice(),
        'cantidad' => $producto->getStock(),
        'categoria' => $producto->getCategory()
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos de Cafetería</title>
    <link rel="stylesheet" href="../CSS/producto.css"> 
</head>
<body>
    <h1>Productos de Cafetería</h1>

    <div class="categoria-bar">
        <button onclick="mostrarProductos('todos')">Todos</button>
        <?php foreach ($categorias as $categoria): ?>
            <button onclick="mostrarProductos('<?php echo $categoria; ?>')"><?php echo $categoria; ?></button>
        <?php endforeach; ?>
    </div>
    
    <div id="productos"></div>

    <button id="carrito-btn" class="carrito-button" onclick="window.location.href='carrito.php'">
        <img src="../IMG-2/carro.png" alt="Carrito" class="carrito-logo">
        Carrito
    </button>

    <script>
        // Obtener todos los productos desde el servidor
        const productos = <?php echo json_encode($productosArray); ?>;

        function mostrarProductos(categoria) {
            const productosDiv = document.getElementById('productos');
            productosDiv.innerHTML = '';
            let productosFiltrados = categoria === 'todos' ? productos : productos.filter(p => p.categoria === categoria);

            if (productosFiltrados.length === 0) {
                productosDiv.innerHTML = '<p>No se encontraron productos en esta categoría.</p>';
                return;
            }

            productosFiltrados.forEach(producto => {
                const nombre_imagen = producto.nombre_producto.toLowerCase().replace(/ /g, '_') + '.jpg';
                productosDiv.innerHTML += `
                    <div class='producto'>
                        <img src='../IMG/${nombre_imagen}' alt='${producto.nombre_producto}' />
                        <div>
                            <h2>${producto.nombre_producto}</h2>
                            <p class="precio">Precio: $${parseFloat(producto.precio).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                            <p>Cantidad: ${producto.cantidad}</p>
                            <p>Ingredientes: ${producto.descripcion}</p>
                            <button class="agregar" onclick="agregarAlCarrito(${producto.producto_ID}, 1)">Agregar al Carrito</button>
                        </div>
                    </div>
                `;
            });
        }

        function agregarAlCarrito(productoID, cantidad) {
            fetch('agregar_carro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ producto_ID: productoID, cantidad: cantidad })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Producto agregado al carrito.');
                } else {
                    alert('Error al agregar producto: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Mostrar todos los productos al cargar la página
        mostrarProductos('todos');
    </script>
</body>
</html>