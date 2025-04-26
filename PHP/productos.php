<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'autoload.php';

// Importar clases necesarias
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseConfiguration;
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

// Crear objeto de configuración de base de datos
$dbConfig = new DatabaseConfiguration(
    $config['database']['host'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['database']
);

// Crear instancia de la base de datos
$db = new MySQLDatabase($dbConfig);

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
    <title>Productos - Café Aroma</title>
    <link rel="stylesheet" href="../CSS/productos.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Ethos<span>Coffe</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="../Servicios.html">Sobre Nosotros</a></li>
            </ul>
            <div class="user-actions">
                <div class="icon" title="Mi Cuenta">👤</div>
                <div class="icon" title="Favoritos">❤️</div>
                <div class="icon" title="Carrito" onclick="window.location.href='carrito.php'">🛒</div>
            </div>
        </nav>
    </header>
    
    <div class="page-title">
        <h1>Nuestros Productos</h1>
        <p>Descubre nuestras deliciosas especialidades y preparaciones</p>
    </div>

    <div class="categoria-bar">
        <button id="btn-todos" class="active" onclick="mostrarProductos('todos')">Todos</button>
        <?php foreach ($categorias as $categoria): ?>
            <button id="btn-<?php echo strtolower(str_replace(' ', '-', $categoria)); ?>" onclick="mostrarProductos('<?php echo $categoria; ?>')"><?php echo $categoria; ?></button>
        <?php endforeach; ?>
    </div>
    
    <div id="productos"></div>

    <button id="carrito-btn" class="carrito-button" onclick="window.location.href='carrito.php'">
        <img src="../IMG-2/carro.png" alt="Carrito" class="carrito-logo">
        Ver Carrito
    </button>

    <footer>
        <div class="footer-main">
            <div class="footer-column">
                <h3><i>👤</i> Mi cuenta</h3>
                <ul class="footer-links">
                    <li><a href="#">Iniciar sesión</a></li>
                    <li><a href="#">Registrarse</a></li>
                    <li><a href="#">Mis pedidos</a></li>
                    <li><a href="#">Mis favoritos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🏠</i> Nuestros Locales</h3>
                <ul class="footer-links">
                    <li><a href="#">Encuentra tu café</a></li>
                    <li><a href="#">Horarios</a></li>
                    <li><a href="#">Servicios</a></li>
                    <li><a href="#">Eventos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🛒</i> Carrito</h3>
                <ul class="footer-links">
                    <li><a href="carrito.php">Ver carrito</a></li>
                    <li><a href="#">Métodos de pago</a></li>
                    <li><a href="#">Envíos</a></li>
                    <li><a href="#">Condiciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>¿Necesitas ayuda?</h3>
                <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
                <a href="#" class="contact-btn">Contáctanos</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-info">
                    <div>
                        <h4>Corporate</h4>
                        <ul>
                            <li><a href="#">Sobre Nosotros</a></li>
                            <li><a href="#">Nuestras Marcas</a></li>
                            <li><a href="#">Afiliados</a></li>
                            <li><a href="#">Inversores</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4>Tarjetas Regalo</h4>
                        <ul>
                            <li><a href="#">Comprar Tarjetas</a></li>
                            <li><a href="#">Canjear Tarjetas</a></li>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <div class="social-links">
                        <a href="#"><span>IG</span></a>
                        <a href="#"><span>FB</span></a>
                        <a href="#"><span>YT</span></a>
                        <a href="#"><span>TW</span></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Obtener todos los productos desde el servidor
        const productos = <?php echo json_encode($productosArray); ?>;

        function mostrarProductos(categoria) {
            // Actualizar estado de los botones
            document.querySelectorAll('.categoria-bar button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            if (categoria === 'todos') {
                document.getElementById('btn-todos').classList.add('active');
            } else {
                const btnId = 'btn-' + categoria.toLowerCase().replace(/ /g, '-');
                const btn = document.getElementById(btnId);
                if (btn) btn.classList.add('active');
            }

            const productosDiv = document.getElementById('productos');
            productosDiv.innerHTML = '';
            let productosFiltrados = categoria === 'todos' ? productos : productos.filter(p => p.categoria === categoria);

            if (productosFiltrados.length === 0) {
                productosDiv.innerHTML = '<div class="empty-state">No se encontraron productos en esta categoría.</div>';
                return;
            }

            productosFiltrados.forEach(producto => {
                productosDiv.innerHTML += `
                    <div class='producto-tarjeta'>
                        <div class='producto-info'>
                            <h2>${producto.nombre_producto}</h2>
                            <p class="precio">$${parseFloat(producto.precio).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                            <p class="stock">Disponible: ${producto.cantidad} unidades</p>
                            <div class="acciones">
                                <button class="ver-detalle" onclick="verDetalle(${producto.producto_ID})">Ver Detalle</button>
                                <button class="agregar" onclick="agregarAlCarrito(${producto.producto_ID}, 1)">Agregar</button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        function verDetalle(productoID) {
            window.location.href = `productoDetalle.php?id=${productoID}`;
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
                    alert('Producto agregado al carrito con éxito.');
                } else {
                    alert('Error al agregar producto: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Mostrar todos los productos al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            mostrarProductos('todos');
        });
    </script>
</body>
</html>