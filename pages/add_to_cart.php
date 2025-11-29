<?php
session_start();
include "../db.php";

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    if($qty < 1) { $qty = 1; }

    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $has_stock = false; $stock_available = null;
    $col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
    if($col && mysqli_num_rows($col)>0){ $has_stock = true; }
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, stock FROM products WHERE id=$id"));
    if($p && $has_stock) { $stock_available = (int)$p['stock']; }

    $current = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
    $new_total = $current + $qty;
    if($has_stock) {
        if($stock_available <= 0) {
            $_SESSION['cart_notice'] = 'Stok produk habis. Tidak bisa ditambahkan.';
        } else {
            if($new_total > $stock_available) {
                $_SESSION['cart'][$id] = $stock_available;
                $_SESSION['cart_notice'] = 'Jumlah melebihi stok. Disesuaikan ke maksimal stok.';
            } else {
                $_SESSION['cart'][$id] = $new_total;
            }
        }
    } else {
        $_SESSION['cart'][$id] = $new_total;
    }
}

header("Location: cart.php");
exit;
