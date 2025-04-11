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
    <title><?php echo htmlspecialchars($producto->getName()); ?> - Detalle del Producto</title>
    <link rel="stylesheet" href="../CSS/productoDetalle.css">
</head>
<body>
    <header>
        <a href="productos.php" class="volver-btn">← Volver a Productos</a>
        <h1>Detalle del Producto</h1>
    </header>

    <main class="detalle-container">
        <div class="producto-imagen">
            <img src="../IMG-P/<?php echo $nombre_imagen; ?>" alt="<?php echo htmlspecialchars($producto->getName()); ?>">
        </div>
        
        <div class="producto-info">
            <h2><?php echo htmlspecialchars($producto->getName()); ?></h2>
            <p class="categoria">Categoría: <?php echo htmlspecialchars($producto->getCategory()); ?></p>
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
    </main>
    
    <button id="carrito-btn" class="carrito-button" onclick="window.location.href='carrito.php'">
        <img src="../IMG-2/carro.png" alt="Carrito" class="carrito-logo">
        Carrito
    </button>

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
                    alert('Producto agregado al carrito.');
                } else {
                    alert('Error al agregar producto: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>