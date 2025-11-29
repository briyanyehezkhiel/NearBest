<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit;
}

$current_user = $_SESSION['username'];
$receiver = isset($_GET['to']) ? mysqli_real_escape_string($conn, $_GET['to']) : 'admin';

// Ambil pesan baru (belum dibaca) untuk current user
$sql = "SELECT * FROM chat_messages 
        WHERE receiver_username = '$current_user' 
        AND sender_username = '$receiver'
        AND is_read = 0
        ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);

$messages = [];
while($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);
?>

