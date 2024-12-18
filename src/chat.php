<?php

include('DBconnection.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_GET['receiver_id']);
$hashed_password = @$_SESSION['password']; // Pass hashed password securely


// Fetch receiver's email
$sql = "SELECT email FROM users WHERE id = $receiver_id";
$result = mysqli_query($conn, $sql);
$receiver_email = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['email'] : 'Unknown User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chat with <?= htmlspecialchars($receiver_email) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="sender_id" content="<?= $sender_id ?>">
    <meta name="receiver_id" content="<?= $receiver_id ?>">
    <meta name="user_password" content="<?= htmlspecialchars($hashed_password) ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <link rel="stylesheet" href="chat.css">
    <script src="chat.js" defer></script>
</head>
<body>
    <header class="header">
        <nav>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="chat_selection.php">User Selection</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <div class="AryaTel">AryaTel</div>

    <div class="chat-container">
        <h2>Chat with <?= htmlspecialchars($receiver_email) ?></h2>
        <div id="chat-box"></div>
        <form id="send-message-form" onsubmit="event.preventDefault(); sendMessage();">
            <input type="text" id="message" placeholder="Type a message" required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>

