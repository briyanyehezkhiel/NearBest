<?php
session_start();

if(isset($_POST['id'])) {
    $id = $_POST['id'];

    if(isset($_POST['plus'])) {
        $_SESSION['cart'][$id]++;
    }

    if(isset($_POST['minus'])) {
        if($_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        } else {
            unset($_SESSION['cart'][$id]); // hapus kalau qty 0
        }
    }
}

header("Location: cart.php");
exit;
