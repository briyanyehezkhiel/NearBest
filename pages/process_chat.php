<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_POST['message']) && isset($_POST['receiver'])) {
    $sender = $_SESSION['username'];
    $receiver = mysqli_real_escape_string($conn, $_POST['receiver']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    if(!empty($message)) {
        $sql = "INSERT INTO chat_messages (sender_username, receiver_username, message) 
                VALUES ('$sender', '$receiver', '$message')";
        
        if(mysqli_query($conn, $sql)) {
            // Buat notifikasi untuk receiver
            $notif_title = "Pesan Baru";
            $notif_message = "Anda mendapat pesan dari $sender";
            $notif_sql = "INSERT INTO notifications (user_username, title, message, type, link) 
                         VALUES ('$receiver', '$notif_title', '$notif_message', 'chat', 'chat.php?to=$sender')";
            mysqli_query($conn, $notif_sql);
            
            header("Location: chat.php?to=$receiver");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

header("Location: chat.php");
exit;
?>

