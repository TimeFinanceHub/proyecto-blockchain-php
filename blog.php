<?php
session_start();
// No authentication check here for now, as public posts should be visible without login.
// Authentication will be checked in js/blog.js for private content and post management.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Public & Private Posts</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="/favicon.png" type="image/png">
    <style>
        .blog-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .post {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .post-title {
            color: #1877f2;
            margin-top: 0;
            margin-bottom: 0.5rem;
        }
        .post-author {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .post-content {
            line-height: 1.6;
            margin-bottom: 1rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .post-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .post-actions button {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 4px;
            width: auto;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .edit-post-btn {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .edit-post-btn:hover {
            background-color: #218838;
        }
        .delete-post-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .delete-post-btn:hover {
            background-color: #c82333;
        }
        .no-posts-message {
            text-align: center;
            color: #606770;
            padding: 2rem 0;
        }
        .post-form-section {
            background-color: #f0f2f5;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .post-form-section h3 {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .post-form-section .form-group {
            margin-bottom: 1rem;
        }
        .post-form-section .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .post-form-section .form-group input[type="text"],
        .post-form-section .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            max-width: 100%;
        }
        .post-form-section .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .post-form-section .form-group input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .post-form-section button[type="submit"] {
            width: auto;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .post-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .post-section-header h2 {
            margin: 0;
            border-bottom: none;
            padding-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Blog</h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="#public-posts">Muro Público</a>
                <a href="#private-posts">Mis Posts Privados</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="buy.php">Comprar Código</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Registro</a>
                <a href="#public-posts">Muro Público</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="buy.php">Comprar Código</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="blog-container">
            <!-- Post Creation Form (Visible only if logged in) -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <section class="post-form-section">
                <h3>Crear Nuevo Post</h3>
                <form id="post-form">
                    <input type="hidden" id="post-id" value="">
                    <div class="form-group">
                        <label for="post-title">Título del Post</label>
                        <input type="text" id="post-title" required>
                    </div>
                    <div class="form-group">
                        <label for="post-content">Contenido</label>
                        <textarea id="post-content" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="post-is-public">
                        <label for="post-is-public">Hacer público</label>
                    </div>
                    <button type="submit" id="save-post-btn">Publicar Post</button>
                </form>
            </section>
            <?php endif; ?>

            <!-- Public Posts Section -->
            <section id="public-posts">
                <div class="post-section-header">
                    <h2>Muro Público</h2>
                </div>
                <div id="public-posts-list">
                    <p class="no-posts-message">Cargando posts públicos...</p>
                </div>
            </section>

            <!-- Private Posts Section (Visible only if logged in) -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <section id="private-posts">
                <div class="post-section-header">
                    <h2>Mis Posts Privados</h2>
                </div>
                <div id="private-posts-list">
                    <p class="no-posts-message">Cargando posts privados...</p>
                </div>
            </section>
            <?php endif; ?>

        </main>
    </div>
    
    <div id="notification-container"></div>
    <script src="js/api.js"></script> 
    <script>
        const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
    </script>
    <script src="js/blog.js"></script>
</body>
</html>
