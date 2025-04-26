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
    <title><?php echo htmlspecialchars($producto->getName()); ?> - Ethos Coffe</title>
    <style>
        :root {
            --primary-color: #5D4037;
            --secondary-color: #8D6E63;
            --light-color: #EFEBE9;
            --accent-color: #4CAF50;
            --text-color: #3E2723;
            --background-color: #FFF8E1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        /* Header & Navigation */
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
        }
        
        .logo span {
            color: var(--accent-color);
        }
        
        .main-menu {
            display: flex;
            list-style: none;
        }
        
        .main-menu li {
            margin-left: 1.5rem;
        }
        
        .main-menu a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .main-menu a:hover {
            color: var(--accent-color);
        }
        
        .user-actions {
            display: flex;
            align-items: center;
        }
        
        .user-actions .icon {
            margin-left: 1.2rem;
            cursor: pointer;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        /* Producto Detalle Section */
        .page-title {
            text-align: center;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .page-title h1 {
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .volver-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            margin-top: 1rem;
        }
        
        .volver-link:hover {
            color: var(--accent-color);
        }
        
        .volver-link i {
            margin-right: 0.5rem;
        }
        
        .producto-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }
        
        .producto-imagen {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            height: 500px;
        }
        
        .producto-imagen img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .producto-info {
            display: flex;
            flex-direction: column;
        }
        
        .producto-info h2 {
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .categoria {
            display: inline-block;
            background-color: var(--light-color);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .precio {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .stock {
            font-size: 1rem;
            color: #555;
            margin-bottom: 2rem;
        }
        
        .descripcion {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .descripcion h3 {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .descripcion p {
            color: #444;
            line-height: 1.8;
        }
        
        .cantidad-selector {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .cantidad-selector label {
            margin-right: 1rem;
            font-weight: 500;
        }
        
        .cantidad-controles {
            display: flex;
            align-items: center;
            border: 2px solid var(--primary-color);
            border-radius: 50px;
            overflow: hidden;
        }
        
        .cantidad-controles button {
            background-color: var(--light-color);
            border: none;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            transition: background-color 0.3s;
        }
        
        .cantidad-controles button:hover {
            background-color: #e0d8d3;
        }
        
        .cantidad-controles input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
            background-color: white;
        }
        
        .cantidad-controles input:focus {
            outline: none;
        }
        
        /* Remove spinner for number inputs */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .agregar-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            max-width: 300px;
            align-self: flex-start;
        }
        
        .agregar-btn:hover {
            background-color: #388E3C;
            transform: translateY(-2px);
        }
        
        /* Cart Button */
        .carrito-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            z-index: 10;
        }
        
        .carrito-button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .carrito-logo {
            width: 24px;
            height: 24px;
        }
        
        /* Footer */
        footer {
            background-color: white;
            padding-top: 3rem;
            margin-top: 4rem;
        }
        
        .footer-main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        
        .footer-column h3 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            text-decoration: none;
            color: #666;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--accent-color);
        }
        
        .contact-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .contact-btn:hover {
            background-color: var(--secondary-color);
        }
        
        .footer-bottom {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        .footer-bottom-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-info {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .footer-info div {
            margin-right: 2rem;
        }
        
        .footer-info h4 {
            margin-bottom: 0.8rem;
            font-size: 1rem;
        }
        
        .footer-info ul {
            list-style: none;
        }
        
        .footer-info li {
            margin-bottom: 0.5rem;
        }
        
        .footer-info a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-info a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .social-links a:hover {
            background-color: var(--accent-color);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .producto-container {
                grid-template-columns: 1fr;
            }
            
            .producto-imagen {
                height: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .logo {
                margin-bottom: 1rem;
            }
            
            .main-menu {
                margin-bottom: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .main-menu li {
                margin: 0.5rem;
            }
            
            .footer-info {
                flex-direction: column;
            }
            
            .footer-info div {
                margin-bottom: 1.5rem;
            }
            
            .producto-imagen {
                height: 300px;
            }
            
            .agregar-btn {
                max-width: 100%;
            }
        }
    </style>
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