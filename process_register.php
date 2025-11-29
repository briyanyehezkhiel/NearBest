<?php
include "db.php";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        echo "Registrasi berhasil! <a href='index.php'>Login sekarang</a>";
    } else {
        echo "Gagal registrasi: " . mysqli_error($conn);
    }
}
?>
