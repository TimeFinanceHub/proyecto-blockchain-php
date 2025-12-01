<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Login</h1>
            <nav>
                <a href="register.php">Register</a>
                <a href="blog.php">Blog</a>
                <a href="gui_documentation.html">Guía de Uso</a>
            </nav>
        </header>

        <main>
            <div class="container">
                <form id="login-form">
                    <h2>Login</h2>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </form>
            </div>
        </main>
    </div>
    <script src="js/api.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
