# Diagrama de Clases del Proyecto

```mermaid
classDiagram
    %% Core Classes
    class App {
        +static App $app
        +Router $router
        +Request $request
        +Response $response
        +Container $container
        +array $config
        +__construct(array $config)
        +run()
        -initializeContainer()
        -initializeDatabase()
    }

    class Container {
        +array $bindings
        -array $instances
        +bind($abstract, $concrete, $shared)
        +singleton($abstract, $concrete)
        +resolve($abstract)
        +has($abstract)
        +clear()
    }

    class Router {
        -array $routes
        -$notFoundHandler
        -Request $request
        -Response $response
        -Container $container
        +get($path, $callback)
        +post($path, $callback)
        +resolve()
    }

    %% Interfaces
    class RequestInterface {
        <<interface>>
        +getMethod()
        +getPath()
        +getBody()
    }

    class ResponseInterface {
        <<interface>>
        +setStatusCode($code)
        +redirect($url)
        +json($data)
    }

    class DatabaseInterface {
        <<interface>>
        +query($sql)
        +prepare($sql)
        +lastInsertId()
    }

    %% Controllers
    class BaseController {
        #RequestInterface $request
        #ResponseInterface $response
        #Container $container
        #array $config
        +__construct(RequestInterface, ResponseInterface, Container)
        #render($view, $data)
    }

    class CartController {
        -CartService $cartService
        -ProductRepository $productRepository
        -PurchaseRepository $purchaseRepository
        -PurchaseService $purchaseService
        -ProductService $productService
        -CartCommandInvoker $commandInvoker
        +index()
        +getItems()
        +addItem()
        +removeItem()
        +checkout()
    }

    class AuthController {
        -DatabaseInterface $database
        -Authenticator $authenticator
        -UserRepository $userRepository
        +login()
        +authenticate()
        +register()
        +logout()
    }

    %% Services
    class CartService {
        -DatabaseInterface $db
        -ProductRepository $productRepository
        -array $items
        +addItem($productId, $quantity)
        +removeItem($productId)
        +getItems()
        +clear()
    }

    class ProductService {
        -ProductRepository $productRepository
        +getAllProducts()
        +getProductById($id)
        +getProductsByCategory($category)
    }

    class PurchaseService {
        -DatabaseInterface $db
        -CartService $cartService
        -ProductRepository $productRepository
        -PurchaseRepository $purchaseRepository
        +createPurchase($userId)
        +getUserPurchases($userId)
    }

    %% Repositories
    class ProductRepository {
        -DatabaseInterface $db
        +findAll($limit)
        +findById($id)
        +getAllCategories()
    }

    class PurchaseRepository {
        -DatabaseInterface $db
        +create($data)
        +findByUserId($userId)
        +findById($id)
    }

    %% Models
    class Product {
        +int $id
        +string $name
        +float $price
        +string $category
        +int $stock
    }

    class CartItem {
        +int $productId
        +int $quantity
        +float $price
    }

    class Purchase {
        +int $id
        +int $userId
        +float $total
        +string $status
    }

    %% Relationships
    App --> Container : uses
    App --> Router : creates
    App --> Request : creates
    App --> Response : creates

    Container --> RequestInterface : resolves
    Container --> ResponseInterface : resolves
    Container --> DatabaseInterface : resolves

    Router --> Request : uses
    Router --> Response : uses
    Router --> Container : uses

    BaseController --> RequestInterface : uses
    BaseController --> ResponseInterface : uses
    BaseController --> Container : uses

    CartController --> BaseController : extends
    CartController --> CartService : uses
    CartController --> ProductRepository : uses
    CartController --> PurchaseRepository : uses
    CartController --> PurchaseService : uses
    CartController --> ProductService : uses
    CartController --> CartCommandInvoker : uses

    AuthController --> BaseController : extends
    AuthController --> DatabaseInterface : uses
    AuthController --> Authenticator : uses
    AuthController --> UserRepository : uses

    CartService --> DatabaseInterface : uses
    CartService --> ProductRepository : uses

    ProductService --> ProductRepository : uses

    PurchaseService --> DatabaseInterface : uses
    PurchaseService --> CartService : uses
    PurchaseService --> ProductRepository : uses
    PurchaseService --> PurchaseRepository : uses

    ProductRepository --> DatabaseInterface : uses
    PurchaseRepository --> DatabaseInterface : uses

    Request ..|> RequestInterface : implements
    Response ..|> ResponseInterface : implements
    MySQLDatabase ..|> DatabaseInterface : implements
```

## Notas del Diagrama

1. **Core Classes**
   - `App`: Clase principal que inicializa la aplicación
   - `Container`: Gestiona la inyección de dependencias
   - `Router`: Maneja el enrutamiento de la aplicación

2. **Interfaces**
   - `RequestInterface`: Define la interfaz para las peticiones HTTP
   - `ResponseInterface`: Define la interfaz para las respuestas HTTP
   - `DatabaseInterface`: Define la interfaz para la base de datos

3. **Controllers**
   - `BaseController`: Controlador base con funcionalidad común
   - `CartController`: Maneja operaciones del carrito
   - `AuthController`: Gestiona la autenticación

4. **Services**
   - `CartService`: Lógica de negocio del carrito
   - `ProductService`: Lógica de negocio de productos
   - `PurchaseService`: Lógica de negocio de compras

5. **Repositories**
   - `ProductRepository`: Acceso a datos de productos
   - `PurchaseRepository`: Acceso a datos de compras

6. **Models**
   - `Product`: Modelo de producto
   - `CartItem`: Modelo de item del carrito
   - `Purchase`: Modelo de compra

## Relaciones Principales

1. **Inyección de Dependencias**
   - El `Container` resuelve todas las dependencias
   - Los controladores reciben sus dependencias vía constructor
   - Los servicios reciben sus dependencias vía constructor

2. **Herencia**
   - Los controladores extienden de `BaseController`
   - Las implementaciones concretas implementan interfaces

3. **Composición**
   - `App` compone `Container`, `Router`, `Request` y `Response`
   - Los servicios componen sus respectivos repositorios
   - Los controladores componen los servicios que necesitan 