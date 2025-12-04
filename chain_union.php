<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Union Dynamic</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="/favicon.png" type="image/png">
    <style>
        .union-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .union-container h1 {
            text-align: center;
            color: #1c1e21;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #dddfe2;
            padding-bottom: 10px;
        }
        .union-container h2 {
            color: #1c1e21;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dddfe2;
            padding-bottom: 5px;
        }
        .union-container p {
            line-height: 1.6;
            margin-bottom: 1rem;
            text-align: left;
        }
        .union-visual {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 3rem 0;
            padding: 2rem;
            background-color: #f0f2f5;
            border-radius: 8px;
            border: 1px dashed #ccc;
        }
        .chain-segment {
            width: 150px;
            height: 50px;
            background-color: #1877f2;
            margin: 5px 0;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            position: relative;
            transition: transform 0.5s ease-out;
        }
        .chain-segment.left {
            transform: translateX(-100px);
        }
        .chain-segment.right {
            transform: translateX(100px);
        }
        .chain-segment.merged {
            transform: translateX(0);
            background-color: #28a745;
        }
        .union-point {
            font-size: 2rem;
            margin: 20px 0;
            color: #666;
            transition: opacity 1s ease-in-out;
            opacity: 0;
        }
        .union-point.active {
            opacity: 1;
        }
        .final-chain {
            width: 250px;
            height: 70px;
            background-color: #28a745;
            margin-top: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transform: scale(0);
            transition: transform 0.5s ease-out;
        }
        .final-chain.active {
            transform: scale(1);
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .chain-segment {
                width: 100px;
                height: 40px;
                font-size: 0.8rem;
            }
            .chain-segment.left {
                transform: translateX(-50px);
            }
            .chain-segment.right {
                transform: translateX(50px);
            }
            .union-point {
                font-size: 1.5rem;
            }
            .final-chain {
                width: 180px;
                height: 60px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Blockchain Union Dynamic</h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="blog.php">Blog</a>
                <a href="profile.php">Profile</a>
                <a href="messages.php">Messages</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="buy.php">Comprar Código</a>
                <a href="#" id="toggle-notes-panel-btn">Notas de Video</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Registro</a>
                <a href="blog.php">Blog</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="buy.php">Comprar Código</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="union-container">
            <h1>La Dinámica de Unión de Cadenas de Bloques</h1>
            <p>
                Este proyecto no solo implementa una blockchain robusta, sino que también explora la <span class="important">"Unión de Cadenas de Bloques"</span>, un concepto clave para la interoperabilidad y escalabilidad en el ecosistema blockchain. Aunque la implementación a gran escala es compleja, aquí visualizamos la <span class="important">ilusión</span> de cómo diferentes flujos de datos o cadenas independientes pueden converger para formar una entidad más grande y potente.
            </p>
            <p>
                Imagina múltiples cadenas de bloques, cada una con su propia información y propósito, operando en paralelo. La dinámica de unión permite que estas cadenas se fusionen, compartiendo y validando datos de una manera que aumenta la seguridad, la eficiencia y las posibilidades de aplicación. Esta característica es fundamental para futuros sistemas descentralizados, donde diversas aplicaciones necesitan interactuar sin fricción.
            </p>

            <div class="union-visual">
                <div class="chain-segment" id="segment-a">Cadena A</div>
                <div class="chain-segment" id="segment-b">Cadena B</div>
                <div class="chain-segment" id="segment-c">Cadena C</div>
                <div class="union-point" id="union-point">... Uniendo ...</div>
                <div class="final-chain" id="final-chain">Cadena Unificada</div>
            </div>

            <p>
                Esta visualización es una metáfora de cómo la tecnología blockchain puede escalar y evolucionar. Al permitir que distintas "historias" (cadenas) se entrelacen de forma segura, se abre la puerta a soluciones innovadoras que trascienden las limitaciones de las blockchains individuales.
            </p>
            <p>
                Es una muestra del potencial de la ingeniería detrás de sistemas descentralizados, diseñada para captar la atención y demostrar la visión futurista de este proyecto.
            </p>
            <button id="trigger-union" class="buy-button" style="margin-top: 2rem;">Simular Unión</button>
        </main>
    </div>
    
    <div id="notification-container"></div>
    <script src="js/api.js"></script>
    <script src="js/chain_union.js"></script>
</body>
</html>
