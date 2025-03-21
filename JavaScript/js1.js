document.getElementById('quienesSomosLink').addEventListener('mouseover', function () {
    document.getElementById('quienesSomosTable').style.display = 'table';
});

document.getElementById('quienesSomosLink').addEventListener('mouseout', function () {
    document.getElementById('quienesSomosTable').style.display = 'none';
});

function toggleMenu() {
    const nav = document.querySelector("nav");
    nav.classList.toggle("active");
}

function showQuienesSomosInfo() {
    const quienesSomosInfo = document.getElementById("quienesSomosInfo");
    quienesSomosInfo.style.display = "block";
}

function closeInfoBox() {
    const quienesSomosInfo = document.getElementById("quienesSomosInfo");
    quienesSomosInfo.style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
    const menuIcon = document.getElementById("menuIcon");
    menuIcon.addEventListener("click", toggleMenu);

    const quienesSomosLink = document.querySelector("nav ul li:nth-child(2) a");
    quienesSomosLink.addEventListener("click", showQuienesSomosInfo);

    const closeIcon = document.querySelector("#quienesSomosInfo .close-icon");
    closeIcon.addEventListener("click", closeInfoBox);
});


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
                alert(response); // Mostrar mensaje de éxito    
            },
            error: function (error) {
                alert('Error al agregar el producto al carrito');
            }
        });
    });
});

$(document).ready(function () {
    $.ajax({
        type: 'GET',
        url: 'Menú.txt', // Nombre del archivo que contiene los links
        dataType: 'text',
        success: function (data) {
            // Aquí puedes procesar los datos leídos del archivo.txt
            // 'data' contiene el contenido del archivo.txt

            // Ejemplo: dividir los links por líneas y crear hipervínculos
            var links = data.split('\n'); // Suponiendo que cada enlace está en una línea diferente

            // Ahora puedes crear hipervínculos en tu página
            for (var i = 0; i < links.length; i++) {
                var link = $.trim(links[i]); // Quitar espacios en blanco alrededor del enlace
                if (link !== '') {
                    var tutorialLink = $('<a>').attr('href', link).text('Tutorial ' + (i + 1));
                    $('#tutorialLinks').append(tutorialLink);
                }
            }
        },
        error: function (error) {
            alert('Error al leer el archivo.txt');
        }
    });
});
document.getElementById('menuIcon').addEventListener('mouseover', function () {
    const menuTable = document.getElementById('menuTable');
    menuTable.style.display = 'table';
});
document.getElementById('menuIcon').addEventListener('mouseout', function () {
    const menuTable = document.getElementById('menuTable');
    menuTable.style.display = 'none';
});

// Funcion de la barra de productos
document.addEventListener("DOMContentLoaded", function() {
    // Obtén referencias a los elementos
    var productosNavItem = document.getElementById("productosNavItem");
    var productosTable = document.getElementById("productosTable");

    // Agrega un evento de mouseover al elemento del ícono de productos
    productosNavItem.addEventListener("mouseover", function() {
        // Muestra la tabla al pasar el mouse
        productosTable.style.display = "table";
    });

    // Agrega un evento de mouseout al elemento del ícono de productos
    productosNavItem.addEventListener("mouseout", function() {
        // Oculta la tabla al quitar el mouse
        productosTable.style.display = "none";
    });
});
