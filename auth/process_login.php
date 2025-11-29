<?php
session_start();
include "../db.php";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($row['role'] == 'seller') {
                header("Location: ../seller/dashboard.php");
            } else {
                header("Location: ../pages/shop.php");
            }
            exit;

        } else {
            echo "Password Salah!";
        }
    } else {
        echo "Username tidak ditemukan!";
    }
}
?>
