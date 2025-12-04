<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Project Code</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .buy-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .buy-container h1 {
            text-align: center;
            color: #1c1e21;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #dddfe2;
            padding-bottom: 10px;
        }
        .buy-container h2 {
            color: #1c1e21;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dddfe2;
            padding-bottom: 5px;
        }
        .buy-container p {
            line-height: 1.6;
            margin-bottom: 1rem;
            text-align: left; /* Override global p alignment */
        }
        .buy-container ul {
            margin-left: 20px;
            margin-bottom: 1rem;
            text-align: left; /* Override global p alignment */
        }
        .buy-container li {
            margin-bottom: 0.5rem;
        }
        .price-section {
            text-align: center;
            margin: 3rem 0;
            padding: 1.5rem;
            background-color: #e7f3ff;
            border-radius: 8px;
            border: 1px solid #cce5ff;
        }
        .price-section .price {
            font-size: 3rem;
            font-weight: bold;
            color: #1877f2;
            margin-bottom: 1rem;
        }
        .buy-button {
            width: auto;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .buy-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Buy Project Code</h1>
            <nav>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="blog.php">Blog</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="buy.php">Comprar Código</a>
            </nav>
        </header>

        <main class="buy-container">
            <h1>Adquiere el Código Fuente Completo del Proyecto</h1>
            <p>
                Este proyecto ofrece una solución completa para una aplicación web moderna, construida con las mejores prácticas en PHP, JavaScript (Vanilla JS), HTML y CSS. Adquiere el código fuente completo y obtén acceso ilimitado para estudiar, modificar y desplegar tu propia versión de esta robusta plataforma.
            </p>

            <h2>Características Destacadas del Proyecto:</h2>
            <ul>
                <li><strong>Sistema de Usuarios Completo:</strong> Registro, Login, Logout, Gestión de Perfiles, Verificación manual por administrador (Email y Teléfono).</li>
                <li><strong>Módulo de Blockchain:</strong> Implementación básica de una blockchain con minado de bloques y visualización de la cadena.</li>
                <li><strong>Galería de Videos de YouTube:</strong> Gestión CRUD de tus videos favoritos de YouTube.</li>
                <li><strong>Lista de Tareas (To-Do List):</strong> Un práctico gestor de tareas con funcionalidades CRUD.</li>
                <li><strong>Notas de Video:</strong> Crea y gestiona notas asociadas a momentos específicos de tus videos.</li>
                <li><strong>Blog Dinámico:</strong> Publica posts públicos y privados con funcionalidades CRUD completas.</li>
                <li><strong>Panel de Administración de Usuarios:</strong> Herramienta exclusiva para el administrador para gestionar y verificar usuarios.</li>
                <li><strong>APIs RESTful:</strong> Backend robusto en PHP con APIs para todas las funcionalidades.</li>
                <li><strong>Frontend Interactivo:</strong> Construido con Vanilla JS, HTML5 y CSS3, con diseño responsivo.</li>
                <li><strong>Base de Datos MySQL:</strong> Gestión eficiente de datos con PDO.</li>
                <li><strong>Documentación Detallada:</strong> Guía de uso de la interfaz gráfica del usuario.</li>
            </ul>

            <div class="price-section">
                <p>Precio del Código Fuente:</p>
                <p class="price" id="display-price">$199.99 USD</p> <!-- Added ID -->
                <button id="buy-now-button" class="buy-button">Comprar Ahora</button>
                <p style="font-size: 0.9rem; color: #666; margin-top: 1rem;">
                    Al hacer clic en "Comprar Ahora", se simulará una compra exitosa y se iniciará la descarga del proyecto completo en formato ZIP.
                </p>
            </div>

            <h2>¿Qué Incluye tu Compra?</h2>
            <ul>
                <li>Acceso inmediato al código fuente completo del proyecto.</li>
                <li>Base de datos SQL (<code>database.sql</code>) para la configuración inicial.</li>
                <li>Todas las dependencias necesarias.</li>
                <li>Libertad para modificar y adaptar el código a tus necesidades.</li>
            </ul>

            <p>¡No pierdas la oportunidad de tener en tus manos este completo proyecto y potenciar tus habilidades!</p>
        </main>
    </div>
    
    <div id="notification-container"></div>
    <script src="js/api.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const displayPriceElement = document.getElementById('display-price');
            if (displayPriceElement) {
                try {
                    const response = await fetch('api/settings.php');
                    const result = await response.json();
                    if (result.status === 'success' && result.project_price !== undefined) {
                        displayPriceElement.textContent = `$${parseFloat(result.project_price).toFixed(2)} USD`;
                    } else {
                        console.error('Error fetching price:', result.message || 'Unknown error');
                        displayPriceElement.textContent = '$N/A USD'; // Fallback
                    }
                } catch (error) {
                    console.error('Network error fetching price:', error);
                    displayPriceElement.textContent = '$N/A USD'; // Fallback
                }
            }
        });
    </script>
    <script src="js/buy.js"></script>
</body>
</html>
