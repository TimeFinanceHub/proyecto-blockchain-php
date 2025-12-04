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
    <title>Messages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="/favicon.png" type="image/png">
    <style>
        .messages-container {
            display: flex;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 600px;
        }
        .conversations-list {
            flex: 1;
            border-right: 1px solid #eee;
            background-color: #f8f8f8;
            padding: 1rem;
            overflow-y: auto;
        }
        .conversations-list h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 0.5rem;
        }
        .conversation-item {
            padding: 0.75rem 0.5rem;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .conversation-item:hover, .conversation-item.active {
            background-color: #e6f3ff;
        }
        .conversation-item:last-child {
            border-bottom: none;
        }
        .conversation-item .username {
            font-weight: bold;
            color: #333;
        }
        .message-view {
            flex: 2;
            display: flex;
            flex-direction: column;
            padding: 1rem;
        }
        .message-view-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .message-view-header h3 {
            margin: 0;
            color: #1877f2;
        }
        .messages-display {
            flex-grow: 1;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }
        .message-item {
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            max-width: 80%;
            word-wrap: break-word;
        }
        .message-item.sent {
            align-self: flex-end;
            background-color: #e6f3ff;
            border: 1px solid #cce5ff;
        }
        .message-item.received {
            align-self: flex-start;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
        }
        .message-item .sender {
            font-weight: bold;
            font-size: 0.9rem;
            color: #1c1e21;
        }
        .message-item .timestamp {
            font-size: 0.75rem;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }
        .message-form {
            display: flex;
            gap: 0.5rem;
            margin-top: auto; /* Push to bottom */
        }
        .message-form textarea {
            flex-grow: 1;
            padding: 0.75rem;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            resize: none;
            min-height: 40px;
        }
        .message-form button {
            width: auto;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        .no-conversation-selected {
            text-align: center;
            color: #606770;
            padding: 2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .messages-container {
                flex-direction: column;
                min-height: auto;
            }
            .conversations-list {
                border-right: none;
                border-bottom: 1px solid #eee;
                max-height: 200px; /* Limit height on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Messages</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="messages.php">Messages</a>
                <a href="chain_union.php">Unión de Cadenas</a>
                <a href="gui_documentation.html">Guía de Uso</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="messages-container">
            <div class="conversations-list">
                <h2>Conversations</h2>
                <div class="user-search-area" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="text" id="user-search-input" placeholder="Search users..." style="flex-grow: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <button id="user-search-btn" style="padding: 0.5rem 1rem;">Search</button>
                </div>
                <div id="user-search-results" style="margin-bottom: 1rem; border: 1px solid #eee; border-radius: 4px; max-height: 150px; overflow-y: auto;">
                    <!-- Search results will be loaded here -->
                </div>
                <div id="conversations">
                    <p class="no-conversation-selected">Loading conversations...</p>
                </div>
            </div>
            <div class="message-view">
                <div id="message-view-header" class="message-view-header" style="display:none;">
                    <h3>Chat with <span id="chat-with-username"></span></h3>
                </div>
                <div id="messages-display" class="messages-display">
                    <p class="no-conversation-selected" id="no-convo-selected">Select a conversation to start chatting.</p>
                </div>
                <form id="message-form" class="message-form" style="display:none;">
                    <textarea id="message-content" placeholder="Type your message..." required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        </main>
    </div>
    
    <div id="notification-container"></div>
    <script src="js/api.js"></script>
    <script>
        const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
    </script>
    <script src="js/messages.js"></script>
</body>
</html>
