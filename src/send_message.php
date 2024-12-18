<?php
include('DBconnection.php');
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$sender_id = mysqli_real_escape_string($conn, $data['sender_id']);
$receiver_id = mysqli_real_escape_string($conn, $data['receiver_id']);
$encrypted_message = mysqli_real_escape_string($conn, $data['message']);
$iv = mysqli_real_escape_string($conn, $data['iv']);

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, encrypted_message, iv) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $sender_id, $receiver_id, $encrypted_message, $iv);
$stmt->execute();

$stmt->close();
$conn->close();
?>
