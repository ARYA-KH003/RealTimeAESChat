<?php
session_start();
include('DBconnection.php');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$current_user_id = $_SESSION['user_id'];
$chat_user_id = mysqli_real_escape_string($conn, $_GET['user']);

function decrypt_message($encrypted_data, $shared_key) {
    $cipher = "aes-256-cbc";

    if (strpos($encrypted_data, ':') === false) {
        return "Error: Invalid encrypted data format.";
    }

    list($iv_base64, $encrypted_message_base64) = explode(':', $encrypted_data, 2);

    $iv = base64_decode($iv_base64);
    $encrypted_message = base64_decode($encrypted_message_base64);

    if ($iv === false || $encrypted_message === false) {
        return "Error: Invalid IV or encrypted message format.";
    }

    $decrypted_message = openssl_decrypt($encrypted_message, $cipher, $shared_key, 0, $iv);

    if ($decrypted_message === false) {
        return "Error: Message decryption failed. OpenSSL Error: " . openssl_error_string();
    }

    return $decrypted_message;
}

$sql = "SELECT shared_key FROM user_shared_keys WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id);
$stmt->execute();
$stmt->bind_result($shared_key_hex);
$stmt->fetch();
$shared_key = hex2bin($shared_key_hex);
$stmt->close();

if (!$shared_key) {
    die('Shared key not found. Please restart the chat session.');
}

$sql = "SELECT sender_id, message, timestamp FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $is_sender = ($row['sender_id'] == $current_user_id);
    $decrypted_message = decrypt_message($row['message'], $shared_key);
    ?>
    <div class="<?php echo $is_sender ? 'message-sent' : 'message-received'; ?>">
        <p><?php echo htmlspecialchars($decrypted_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <small><?php echo $row['timestamp']; ?></small>
    </div>
    <?php
}

$stmt->close();
mysqli_close($conn);
?>
