<?php
include('DBconnection.php');
session_start();

$receiver_id = intval($_GET['receiver_id']);
$sender_id = $_SESSION['user_id'];

$sql = "SELECT * FROM messages WHERE 
        (sender_id = $sender_id AND receiver_id = $receiver_id) OR 
        (sender_id = $receiver_id AND receiver_id = $sender_id)
        ORDER BY timestamp ASC";

$result = mysqli_query($conn, $sql);
$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row; // Include IV in the response
}

header('Content-Type: application/json');
echo json_encode($messages);
?>
