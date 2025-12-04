<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Register</h1>
            <nav>
                <a href="login.php">Login</a>
                <a href="blog.php">Blog</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <a href="buy.php">Comprar Código</a>
            </nav>
        </header>

        <main>
            <div class="container">
                <form id="register-form">
                    <h2>Register</h2>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Register</button>
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        </main>
    </div>
    <script src="js/api.js"></script>
    <script src="js/register.js"></script>
</body>
</html>
