<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'api/config.php'; // Include config to get PDO object

$user_id = $_SESSION['user_id'];
$email_verified = 1; // Default to true

try {
    $stmt = $pdo->prepare("SELECT email_verified FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $email_verified = $user_data['email_verified'];
    }
} catch (PDOException $e) {
    // Log error, but don't prevent dashboard from loading
    error_log("Error fetching email verification status: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <style>
        .verification-alert {
            background-color: #fff3cd; /* Light orange background */
            color: #856404; /* Dark orange text */
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        .verification-alert a {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Dashboard</h1>
            <nav>
                <a href="#blockchain">Blockchain</a>
                <a href="#youtube-gallery">YouTube Gallery</a>
                <a href="#todo-list">To-Do List</a>
                <a href="documentacion.html">Documentación</a>
                <a href="documentacion_email_server.html">Configurar Mail Local</a>
                <a href="#" id="toggle-notes-panel-btn">Notas de Video</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <?php if ($email_verified == 0): ?>
                <div class="verification-alert">
                    Your email address is not verified. Please check your inbox for a verification link.
                    <!-- TODO: Add a button/link to resend verification email -->
                </div>
            <?php endif; ?>
            <section id="blockchain">
                <h2>Blockchain</h2>
                <!-- Blockchain content will go here -->
            </section>

            <section id="youtube-gallery">
                <h2>YouTube Gallery</h2>
                <div class="youtube-form">
                    <input type="text" id="youtube-url" placeholder="Enter YouTube URL">
                    <button id="add-video-button">Add Video</button>
                </div>
                <div id="video-gallery-container"></div>
            </section>

            <section id="todo-list">
                <h2>To-Do List</h2>
                <div class="todo-form">
                    <input type="text" id="new-task-input" placeholder="Add a new task...">
                    <button id="add-task-button">Add Task</button>
                </div>
                <ul id="task-list"></ul>
            </section>
        </main>
    </div>

    <!-- Video Notes Panel -->
    <div id="video-notes-panel" class="video-notes-panel">
        <div class="panel-header">
            <h3>Mis Notas de Video</h3>
            <button id="close-notes-panel-btn" class="close-btn">&times;</button>
        </div>
        <div class="panel-body">
            <form id="video-note-form">
                <input type="hidden" id="note-id" value="">
                <div class="form-group">
                    <label for="note-video-url">URL del Video (Opcional)</label>
                    <input type="text" id="note-video-url" placeholder="URL de YouTube">
                </div>
                 <div class="form-group">
                    <label for="note-timestamp">Marca de tiempo (segundos - Opcional)</label>
                    <input type="number" id="note-timestamp" placeholder="Ej. 120 (para 2:00)">
                </div>
                <div class="form-group">
                    <label for="note-title">Título de la Nota</label>
                    <input type="text" id="note-title" required>
                </div>
                <div class="form-group">
                    <label for="note-content">Contenido de la Nota</label>
                    <textarea id="note-content" rows="5" required></textarea>
                </div>
                <button type="submit" id="save-note-btn">Guardar Nota</button>
            </form>
            <h4>Notas Guardadas:</h4>
            <ul id="notes-list">
                <p class="no-notes-message">No hay notas guardadas aún.</p>
            </ul>
        </div>
    </div>
    
    <script src="js/api.js"></script>
    <script src="js/dashboard.js"></script>
    <div id="notification-container"></div>
</body>
</html>
