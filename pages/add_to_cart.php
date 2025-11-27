<?php
session_start();

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $qty = $_POST['qty'];

    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if(isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
}

header("Location: cart.php");
exit;
