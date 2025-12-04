<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="/favicon.png" type="image/png">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .profile-form .form-group {
            margin-bottom: 1.5rem;
        }
        .profile-form .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .profile-form .form-group input[type="text"],
        .profile-form .form-group input[type="email"],
        .profile-form .form-group input[type="tel"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .profile-form .form-group input:focus {
            outline: none;
            border-color: #1877f2;
            box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.2);
        }
        .profile-form button[type="submit"] {
            width: auto;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            background-color: #1877f2;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .profile-form button[type="submit"]:hover {
            background-color: #166fe5;
        }
        .profile-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        .profile-info-item:last-child {
            border-bottom: none;
        }
        .profile-info-item span:first-child {
            font-weight: 600;
            color: #606770;
        }
        .profile-info-item span:last-child {
            color: #333;
        }
        .verified-status {
            color: #28a745;
            font-weight: bold;
        }
        .unverified-status {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>User Profile</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="messages.php">Messages</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="profile-container">
            <h2>Your Profile Information</h2>
            <div id="profile-details">
                <p>Loading profile...</p>
            </div>
            <p style="text-align: center; margin-top: 20px;">
                <a href="cv.html" target="_blank" style="color: #1877f2; text-decoration: none; font-weight: bold;">Ver mi CV (Habilidades del Proyecto)</a>
            </p>

            <h2>Edit Profile</h2>
            <form id="profile-form" class="profile-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="instagram_handle">Instagram Handle (Optional)</label>
                    <input type="text" id="instagram_handle" name="instagram_handle">
                </div>
                <button type="submit">Update Profile</button>
            </form>
        </main>
    </div>
    
    <div id="notification-container"></div>
    <script src="js/api.js"></script>
    <script src="js/profile.js"></script>
</body>
</html>
