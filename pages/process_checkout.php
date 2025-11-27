<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_username = $_SESSION['username'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if(empty($cart)) {
    header("Location: cart.php?error=empty_cart");
    exit;
}

// Hitung total dan kelompokkan produk berdasarkan seller
$total = 0;
$order_items_data = [];
$sellers_data = []; // Array untuk menyimpan data seller per produk

foreach($cart as $product_id => $qty) {
    $product_id_escaped = mysqli_real_escape_string($conn, $product_id);
    $sql = "SELECT p.*, u.username as seller_username 
            FROM products p 
            LEFT JOIN users u ON p.seller_id = u.id 
            WHERE p.id='$product_id_escaped'";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);
    
    if($product) {
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        
        // Ambil seller username (jika ada seller_id, ambil dari users, jika tidak default admin)
        $product_seller = !empty($product['seller_username']) ? $product['seller_username'] : 'admin';
        
        $order_items_data[] = [
            'product_id' => $product_id,
            'product_name' => $product['name'],
            'quantity' => $qty,
            'price' => $product['price'],
            'subtotal' => $subtotal,
            'seller_username' => $product_seller
        ];
        
        // Simpan seller untuk produk ini
        if(!isset($sellers_data[$product_seller])) {
            $sellers_data[$product_seller] = [];
        }
        $sellers_data[$product_seller][] = count($order_items_data) - 1; // Index item
    }
}

// Jika ada produk dari seller berbeda, ambil seller pertama (atau bisa split order nanti)
// Untuk sekarang, ambil seller dari produk pertama
$seller_username = !empty($order_items_data) ? $order_items_data[0]['seller_username'] : 'admin';

// Ambil info toko seller
$seller_store_name = '';
$seller_store_address = '';
if($seller_username != 'admin') {
    $store_sql = "SELECT * FROM stores WHERE seller_username='" . mysqli_real_escape_string($conn, $seller_username) . "' LIMIT 1";
    $store_result = mysqli_query($conn, $store_sql);
    if($store_result && $store = mysqli_fetch_assoc($store_result)) {
        $seller_store_name = $store['store_name'];
        $seller_store_address = $store['store_address'];
    }
}

// Simpan order
$buyer_escaped = mysqli_real_escape_string($conn, $buyer_username);
$seller_escaped = mysqli_real_escape_string($conn, $seller_username);
$total_escaped = mysqli_real_escape_string($conn, $total);
$store_name_escaped = mysqli_real_escape_string($conn, $seller_store_name);
$store_address_escaped = mysqli_real_escape_string($conn, $seller_store_address);

// Cek apakah kolom seller_store_name ada
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'seller_store_name'");
$has_store_columns = $column_check && mysqli_num_rows($column_check) > 0;

if($has_store_columns) {
    $order_sql = "INSERT INTO orders (buyer_username, seller_username, seller_store_name, seller_store_address, total_amount, status) 
                  VALUES ('$buyer_escaped', '$seller_escaped', '$store_name_escaped', '$store_address_escaped', '$total_escaped', 'pending')";
} else {
    $order_sql = "INSERT INTO orders (buyer_username, seller_username, total_amount, status) 
                  VALUES ('$buyer_escaped', '$seller_escaped', '$total_escaped', 'pending')";
}

if(mysqli_query($conn, $order_sql)) {
    $order_id = mysqli_insert_id($conn);
    
    // Simpan order items
    foreach($order_items_data as $item) {
        $product_id_escaped = mysqli_real_escape_string($conn, $item['product_id']);
        $product_name_escaped = mysqli_real_escape_string($conn, $item['product_name']);
        $quantity_escaped = mysqli_real_escape_string($conn, $item['quantity']);
        $price_escaped = mysqli_real_escape_string($conn, $item['price']);
        $subtotal_escaped = mysqli_real_escape_string($conn, $item['subtotal']);
        
        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                     VALUES ('$order_id', '$product_id_escaped', '$product_name_escaped', '$quantity_escaped', '$price_escaped', '$subtotal_escaped')";
        mysqli_query($conn, $item_sql);
    }
    
    // Kirim notifikasi ke seller
    $notif_title = "Order Baru";
    $notif_message = "Anda mendapat order baru dari $buyer_username dengan total Rp " . number_format($total, 0, ',', '.');
    $notif_sql = "INSERT INTO notifications (user_username, title, message, type, link) 
                  VALUES ('$seller_escaped', '$notif_title', '$notif_message', 'info', 'orders.php?order_id=$order_id')";
    mysqli_query($conn, $notif_sql);
    
    // Kirim notifikasi ke buyer
    $buyer_notif_title = "Order Berhasil";
    $buyer_notif_message = "Order Anda telah dibuat. Silakan hubungi seller untuk pembayaran dan pengiriman.";
    $buyer_notif_sql = "INSERT INTO notifications (user_username, title, message, type, link) 
                        VALUES ('$buyer_escaped', '$buyer_notif_title', '$buyer_notif_message', 'success', 'my_orders.php?order_id=$order_id')";
    mysqli_query($conn, $buyer_notif_sql);
    
    // Kosongkan cart
    $_SESSION['cart'] = [];
    
    header("Location: checkout.php?order_id=$order_id&success=1");
    exit;
} else {
    header("Location: cart.php?error=checkout_failed");
    exit;
}
?>

