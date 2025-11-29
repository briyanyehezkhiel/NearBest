<?php
session_start();
include "../db.php";

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if(!isset($_SESSION['cart'][$id])) { $_SESSION['cart'][$id] = 0; }

    $has_stock = false; $stock_available = null;
    $col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
    if($col && mysqli_num_rows($col)>0){ $has_stock = true; }
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, stock FROM products WHERE id=$id"));
    if($p && $has_stock) { $stock_available = (int)$p['stock']; }

    if(isset($_POST['plus'])) {
        $current = (int)$_SESSION['cart'][$id];
        if($has_stock) {
            if($stock_available <= 0) {
                $_SESSION['cart_notice'] = 'Stok produk habis.';
            } else if($current < $stock_available) {
                $_SESSION['cart'][$id] = $current + 1;
            } else {
                $_SESSION['cart_notice'] = 'Sudah mencapai batas stok.';
            }
        } else {
            $_SESSION['cart'][$id] = $current + 1;
        }
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
