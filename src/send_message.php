<?php
session_start();
include('DBconnection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "SELECT shared_key FROM user_shared_keys WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $stmt->bind_result($shared_key_hex);
    $stmt->fetch();
    $shared_key = hex2bin($shared_key_hex);
    $stmt->close();

    if (!$shared_key) {
        die('Shared key not found. Cannot send message.');
    }

    function encrypt_message($message, $shared_key) {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $encrypted_message = openssl_encrypt($message, $cipher, $shared_key, 0, $iv);

        return base64_encode($iv) . ':' . base64_encode($encrypted_message);
    }

    $encrypted_message = encrypt_message($message, $shared_key);

    if ($encrypted_message === false) {
        die('Message encryption failed.');
    }

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("iis", $sender_id, $receiver_id, $encrypted_message);

    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    } else {
        echo "Message sent successfully.";
    }

    $stmt->close();
}

mysqli_close($conn);
?>
