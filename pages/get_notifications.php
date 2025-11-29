<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$username = $_SESSION['username'];

// Hitung notifikasi yang belum dibaca
$sql = "SELECT COUNT(*) as count FROM notifications WHERE user_username = '$username' AND is_read = 0";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

echo json_encode(['count' => (int)$row['count']]);
?>

