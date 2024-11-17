<?php
session_start();
include('DBconnection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];


function generate_shared_key($user1_id, $user2_id, $conn) {
    $sql = "SELECT shared_key FROM user_shared_keys WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($shared_key_hex);
        $stmt->fetch();
        $stmt->close();
        return hex2bin($shared_key_hex);  
    } else {
        $shared_key = openssl_random_pseudo_bytes(32);
        $shared_key_hex = bin2hex($shared_key);  

        $sql = "INSERT INTO user_shared_keys (user1_id, user2_id, shared_key) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user1_id, $user2_id, $shared_key_hex);
        $stmt->execute();
        $stmt->close();
        
        return $shared_key;  
    }
}


$sql = "SELECT id, email FROM users WHERE id != $current_user_id";
$users = mysqli_query($conn, $sql);

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AryaTel Chat</title>
    <link rel="stylesheet" href="chat.css">
    <script src="chat.js"></script>
</head>
<body>
    <div class="chat-container">
        <h2>Welcome, <?php echo sanitize($_SESSION['email']); ?>!</h2>
        
        <div class="user-list">
            <h3>Users Online</h3>
            <ul>
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                    <li>
                        <a href="chat.php?user=<?php echo $user['id']; ?>">
                            <?php echo sanitize($user['email']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <?php if (isset($_GET['user'])): ?>
            <?php
            $chat_user_id = $_GET['user'];

            $shared_key = generate_shared_key($current_user_id, $chat_user_id, $conn);

            $sql = "SELECT * FROM users WHERE id = $chat_user_id";
            $chat_user = mysqli_query($conn, $sql);
            if (mysqli_num_rows($chat_user) == 1):
                $chat_user = mysqli_fetch_assoc($chat_user);
            ?>
                <div class="chat-box">
                    <h3>Chatting with <?php echo sanitize($chat_user['email']); ?></h3>
                    
                    <div id="chat-messages"></div>

                    <form id="send-message" method="post" action="send_message.php">
                        <input type="hidden" name="receiver_id" value="<?php echo $chat_user_id; ?>">
                        <input type="text" name="message" placeholder="Type your message..." required>
                        <button type="submit">Send</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Select a user to chat with from the list.</p>
        <?php endif; ?>
    </div>
</body>
</html>
