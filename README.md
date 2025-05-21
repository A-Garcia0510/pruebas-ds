# Café-VT 🎓☕

## Descripción
Café-VT es una plataforma web moderna diseñada específicamente para la comunidad universitaria, ofreciendo un sistema de pedidos y gestión de productos de cafetería. Desarrollada con PHP siguiendo el patrón MVC, proporciona una experiencia de usuario intuitiva y eficiente.

## Características Principales 🌟

### Para Estudiantes y Personal
- 🛍️ Catálogo de productos con categorías
- 🛒 Carrito de compras intuitivo
- 👤 Sistema de autenticación seguro
- 📱 Diseño responsivo para todos los dispositivos
- 💳 Gestión de pedidos en tiempo real

### Para Administradores
- 📊 Panel de control para gestión de productos
- 📈 Control de inventario
- 👥 Gestión de usuarios
- 📝 Registro de ventas y estadísticas

## Tecnologías Utilizadas 🛠️

- **Backend**: PHP 8.x
- **Frontend**: HTML5, CSS3, JavaScript
- **Base de Datos**: MySQL
- **Arquitectura**: MVC (Modelo-Vista-Controlador)
- **Diseño**: Responsive Design

## Estructura del Proyecto 📁

```
cafe-vt/
├── app/                    # Código principal de la aplicación
│   ├── config/            # Configuraciones
│   ├── controllers/       # Controladores
│   ├── models/           # Modelos
│   ├── views/            # Vistas
│   └── helpers/          # Helpers y utilidades
├── public/                # Archivos públicos
│   ├── css/              # Estilos
│   ├── js/               # Scripts
│   └── img/              # Imágenes
└── vendor/               # Dependencias
```

## Requisitos del Sistema 📋

- PHP 8.0 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Composer (Gestor de dependencias)

## Instalación 🚀

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/cafe-vt.git
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar la base de datos:
- Crear una base de datos MySQL
- Importar el archivo `INFO/MER.sql`
- Configurar las credenciales en `app/config/config.php`

4. Configurar el servidor web:
- Apuntar el DocumentRoot a la carpeta `public/`
- Asegurarse de que mod_rewrite esté habilitado (Apache)

## Contribuir 🤝

Las contribuciones son bienvenidas. Por favor, sigue estos pasos:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia 📄

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Contacto 📧

- Equipo de Desarrollo: [equipo@cafe-vt.com](mailto:equipo@cafe-vt.com)
- Sitio Web: [www.cafe-vt.com](https://www.cafe-vt.com)

---
Desarrollado con ❤️ para la comunidad universitaria/Usuarios