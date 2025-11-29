<?php
include "../db.php";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $u = mysqli_real_escape_string($conn, $username);
    $e = mysqli_real_escape_string($conn, $email);
    $p = mysqli_real_escape_string($conn, $password);
    $has_role = false;
    $col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
    if($col && mysqli_num_rows($col)>0) { $has_role = true; }
    if($has_role) {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$u', '$e', '$p', 'buyer')";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$u', '$e', '$p')";
    }
    $result = mysqli_query($conn, $sql);

    if ($result) {
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $has_role ? 'buyer' : '';
        header("Location: ../pages/shop.php");
        exit;
    } else {
        echo "Gagal registrasi: " . mysqli_error($conn);
    }
}
?>
