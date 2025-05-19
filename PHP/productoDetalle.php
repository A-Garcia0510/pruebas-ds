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
            alert('Debes iniciar sesión para ver los detalles del producto.');
            window.location.href='../login.html';
          </script>";
    exit();
}

// Verificar si se proporciona un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('Producto no encontrado.');
            window.location.href='productos.php';
          </script>";
    exit();
}

$productoId = (int)$_GET['id'];

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

// Obtener el producto específico por ID
$producto = $productRepository->findById($productoId);

// Verificar si el producto existe
if (!$producto) {
    echo "<script>
            alert('Producto no encontrado.');
            window.location.href='productos.php';
          </script>";
    exit();
}

// Preparar el nombre de la imagen
$nombre_imagen = strtolower(str_replace(' ', '_', $producto->getName())) . '.jpg';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/productoDetalle.css">
    <title><?php echo htmlspecialchars($producto->getName()); ?> - Ethos Coffe</title>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Ethos<span>Coffe</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="productos.php">Menú</a></li>
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
        <h1>Detalle del Producto</h1>
        <a href="productos.php" class="volver-link">
            <i>←</i> Volver a la tienda
        </a>
    </div>
    
    <div class="producto-container">
        <div class="producto-imagen">
            <?php
            // Intentar cargar la imagen del producto, si existe
            $imagen_ruta = "../IMG-P/" . $nombre_imagen;
            if (file_exists($imagen_ruta)) {
                echo "<img src=\"{$imagen_ruta}\" alt=\"" . htmlspecialchars($producto->getName()) . "\">";
            } else {
                // Si no existe, usar un placeholder
                echo "<img src=\"/api/placeholder/500/500\" alt=\"" . htmlspecialchars($producto->getName()) . "\">";
            }
            ?>
        </div>
        
        <div class="producto-info">
            <h2><?php echo htmlspecialchars($producto->getName()); ?></h2>
            <span class="categoria"><?php echo htmlspecialchars($producto->getCategory()); ?></span>
            <p class="precio">$<?php echo number_format($producto->getPrice(), 0, ',', '.'); ?></p>
            <p class="stock">Disponibilidad: <?php echo $producto->getStock(); ?> unidades</p>
            
            <div class="cantidad-selector">
                <label for="cantidad">Cantidad:</label>
                <div class="cantidad-controles">
                    <button type="button" onclick="decrementarCantidad()">-</button>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $producto->getStock(); ?>">
                    <button type="button" onclick="incrementarCantidad(<?php echo $producto->getStock(); ?>)">+</button>
                </div>
            </div>
            
            <button class="agregar-btn" onclick="agregarAlCarrito(<?php echo $producto->getId(); ?>)">
                Agregar al Carrito
            </button>
            
            <div class="descripcion">
                <h3>Descripción:</h3>
                <p><?php echo htmlspecialchars($producto->getDescription()); ?></p>
            </div>
        </div>
    </div>
    
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
        function decrementarCantidad() {
            const cantidadInput = document.getElementById('cantidad');
            if (cantidadInput.value > 1) {
                cantidadInput.value = parseInt(cantidadInput.value) - 1;
            }
        }
        
        function incrementarCantidad(maxStock) {
            const cantidadInput = document.getElementById('cantidad');
            if (parseInt(cantidadInput.value) < maxStock) {
                cantidadInput.value = parseInt(cantidadInput.value) + 1;
            }
        }
        
        function agregarAlCarrito(productoID) {
            const cantidad = parseInt(document.getElementById('cantidad').value);
            
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
    </script>
</body>
</html>