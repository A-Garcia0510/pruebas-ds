document.getElementById('quienesSomosLink').addEventListener('mouseover', function () {
    document.getElementById('quienesSomosTable').style.display = 'table';
});

document.getElementById('quienesSomosLink').addEventListener('mouseout', function () {
    document.getElementById('quienesSomosTable').style.display = 'none';
});

/*funcion para el menu de servicios de la barra de navegacion*/ 
document.getElementById('tablaservicioLink').addEventListener('mouseover', function () {
    document.getElementById('tablaservicio').style.display = 'table';
});

document.getElementById('tablaservicioLink').addEventListener('mouseout', function () {
    document.getElementById('tablaservicio').style.display = 'none';
});  

/****Para el menu de servicio con variables similares********/
function toggleMenu() {
    const nav = document.querySelector("nav");
    nav.classList.toggle("active");
}

function showTablaServicio() {
    const tablaservicio = document.getElementById("tablaservicio");
    tablaservicio.style.display = "block";
}

function cierreflex() {
    const tablaservicio = document.getElementById("tablaservicio");
    tablaservicio.style.display = "none";
}



/*funcion para el menu de servicios de la barra de navegacion*/ 
document.getElementById('menuTable').addEventListener('mouseover', function () {
    document.getElementById('menuTable').style.display = 'table';
});

document.getElementById('tablaservicioLink').addEventListener('mouseout', function () {
    document.getElementById('menuTable').style.display = 'none';
});  

/****Para el menu de servicio con variables similares********/
function toggleMenu() {
    const nav = document.querySelector("nav");
    nav.classList.toggle("active");
}

function showmenuTable() {
    const tablasmenu = document.getElementById("menuTable");
    tablasmenu.style.display = "block";
}

function cierreflex() {
    const tablasmenu = document.getElementById("menuTable");
    tablasmenu.style.display = "none";
}



document.addEventListener("DOMContentLoaded", function () {
    const menuIcono = document.getElementById("menuIcono");
    menuIcono.addEventListener("click", toggleMenu);

    const tablaservicioLink = document.getElementById("tablaservicioLink");
    tablaservicioLink.addEventListener("click", showTablaServicio);

    const cierreIcono = document.querySelector("#tablaservicio .cierreIcono");
    cierreIcono.addEventListener("click", cierreflex);
});

/************************************************************************************/
function toggleMenu() {
    var navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('active');

}
/*                                                      */
function toggleMenu() {
    const nav = document.querySelector("nav");
    nav.classList.toggle("active");
}
/*                                                      */

function showQuienesSomosInfo() {
    const quienesSomosInfo = document.getElementById("quienesSomosInfo");
    quienesSomosInfo.style.display = "block";
}

/*                                                      */

function closeInfoBox() {
    const quienesSomosInfo = document.getElementById("quienesSomosInfo");
    quienesSomosInfo.style.display = "none";
}
/*                                                      */
document.addEventListener("DOMContentLoaded", function () {
    const menuIcon = document.getElementById("menuIcon");
    menuIcon.addEventListener("click", toggleMenu);

    const quienesSomosLink = document.querySelector("nav ul li:nth-child(2) a");
    quienesSomosLink.addEventListener("click", showQuienesSomosInfo);

    const closeIcon = document.querySelector("#quienesSomosInfo .close-icon");
    closeIcon.addEventListener("click", closeInfoBox);
});


// Define una variable para almacenar los productos en el carrito
const carrito = [];

// Obtén todos los botones "Agregar al Carrito"
const botonesAgregar = document.querySelectorAll('.agregar-al-carrito');

// Agrega un evento de clic a cada botón
botonesAgregar.forEach((boton, index) => {
    boton.addEventListener('click', () => {
    // Aquí debes definir la información del producto (nombre, precio, etc.)
    // Puedes obtener estos datos desde el HTML o una base de datos.
    const producto = {
    nombre: boton.parentElement.querySelector('h2').textContent,
    precio: parseFloat(boton.parentElement.querySelector('p').textContent.split('$')[1]),
    };

    // Agrega el producto al carrito
    carrito.push(producto);

    // Actualiza la cantidad de productos en el carrito
    const cantidadProductos = document.getElementById('cantidad-productos');
    cantidadProductos.textContent = carrito.length;

    // Actualiza la lista de productos en el carrito
    const listaCarrito = document.getElementById('lista-carrito');
    listaCarrito.innerHTML = ''; // Borra la lista anterior

    carrito.forEach((producto) => {
    const item = document.createElement('li');
    item.textContent = producto.nombre + ': $' + producto.precio;
    listaCarrito.appendChild(item);
    });

    // Calcula la factura total
    const facturaTotal = document.getElementById('factura-total');
    const total = carrito.reduce((total, producto) => total + producto.precio, 0);
    facturaTotal.textContent = 'Total: $' + total;
});
});

// funcion de PHP 
$(document).ready(function () {
    // Agregar un controlador de clic a los botones "Agregar al Carrito"
    $('.agregar-al-carrito').click(function () {
        // Obtener información del producto
        var producto = {
            nombre: $(this).closest('.producto').find('h2').text(),
            descripcion: $(this).closest('.producto').find('.descripcion p').text(),
            precio: parseFloat($(this).closest('.producto').find('p').text().split('$')[1])
        };

        // Realizar una solicitud AJAX al archivo PHP
        $.ajax({
            type: 'POST',   
            url: "PHP/php.php", 
            data: JSON.stringify(producto),
            contentType: 'application/json',
            success: function (response) {
                if (response == 'exito'){
                    alert('Producto agreado al carrito con exito');
                }else{
                    alert('Algo salio mal, intentalo de nuevo');
                }    
            },
            error: function (error) {
                alert('Error al agregar el producto al carrito');
            }
        });
    });
});

document.getElementById('menuIcon').addEventListener('mouseover', function () {
    document.getElementById('tutorialLinks').style.display = 'flex';
});

document.getElementById('menuIcon').addEventListener('mouseout', function () {
    document.getElementById('tutorialLinks').style.display = 'none';
});

/*Framework Node JS*/
/* Importar el módulo Express y crear una instancia de la aplicación */
const express = require('express');
const app = express();

/* Definir el número de puerto en el que la aplicación se ejecutará */
const port = 3000;

/* Configurar Express para servir archivos estáticos desde el directorio 'public' */
app.use(express.static('public'));

/* Iniciar el servidor y hacerlo escuchar en el puerto especificado */
app.listen(port, () => {
    console.log(`El servidor está corriendo en http://localhost:${port}`);
});


