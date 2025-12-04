<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'api/config.php'; // Include config to get PDO object

$current_user_id = $_SESSION['user_id'];
$admin_email = 'mostlyphpsoftware@gmail.com';

// Fetch current user's email to check for admin privileges
try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $current_user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_user || $current_user['email'] !== $admin_email) {
        // Redirect to dashboard or show an access denied message
        header('Location: dashboard.php'); // Redirect to dashboard
        exit();
    }
} catch (PDOException $e) {
    // Log error, but redirect the user as access cannot be determined
    error_log("Database error during admin_users.php authorization: " . $e->getMessage());
    header('Location: dashboard.php'); // Redirect to dashboard
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .users-table-container {
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            white-space: nowrap; /* Prevent content from wrapping in cells */
        }
        .users-table th, .users-table td {
            border: 1px solid #dddfe2;
            padding: 0.75rem;
            text-align: left;
            vertical-align: middle;
        }
        .users-table th {
            background-color: #f0f2f5;
            font-weight: 600;
        }
        .users-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .users-table tr:hover {
            background-color: #f5f5f5;
        }
        .users-table .verification-checkbox {
            margin-right: 0.5rem;
        }
        .users-table .action-btn {
            padding: 0.5rem 1rem;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        .users-table .action-btn:hover {
            background-color: #166fe5;
        }
        .no-users-message {
            text-align: center;
            padding: 2rem;
            color: #606770;
        }

        /* Responsive adjustments for tables */
        @media (max-width: 600px) {
            .users-table {
                display: block;
                width: 100%;
            }
            .users-table thead, .users-table tbody, .users-table th, .users-table td, .users-table tr {
                display: block;
            }
            .users-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .users-table tr {
                border: 1px solid #dddfe2;
                margin-bottom: 1rem;
            }
            .users-table td {
                border: none;
                border-bottom: 1px solid #dddfe2;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            .users-table td:last-child {
                border-bottom: none;
            }
            .users-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Manage Users</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="buy.php">Comprar Código</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <section>
                <h2>User Verification Management</h2>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Email Verified</th>
                                <th>Phone Verified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- User data will be loaded here by JavaScript -->
                            <tr>
                                <td colspan="7" class="no-users-message">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="site-settings">
                <h2>Site Settings</h2>
                <form id="price-setting-form" class="profile-form"> <!-- Reusing profile-form styling -->
                    <div class="form-group">
                        <label for="project-price">Project Price (USD)</label>
                        <input type="number" id="project-price" name="project_price" step="0.01" min="0" required>
                    </div>
                    <button type="submit" id="save-price-btn">Save Price</button>
                </form>
            </section>
        </main>
    </div>

    
    <div id="notification-container"></div>
    <script src="js/api.js"></script> <!-- Assuming some common API utility, adjust if not present -->
    <script src="js/dashboard.js"></script> <!-- Load dashboard JS for video notes panel -->
    <script src="js/admin_users.js"></script>
</body>
</html>
