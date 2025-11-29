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

// Split order per seller
$buyer_escaped = mysqli_real_escape_string($conn, $buyer_username);
$created_orders = [];

// Cek kolom toko opsional
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'seller_store_name'");
$has_store_columns = $column_check && mysqli_num_rows($column_check) > 0;

foreach($sellers_data as $seller_username => $item_indexes){
    $seller_escaped = mysqli_real_escape_string($conn, $seller_username);
    // Hitung total per seller
    $seller_total = 0;
    foreach($item_indexes as $idx){ $seller_total += (float)$order_items_data[$idx]['subtotal']; }
    $total_escaped = mysqli_real_escape_string($conn, $seller_total);

    // Ambil info toko seller
    $seller_store_name = '';
    $seller_store_address = '';
    $stores_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'stores'");
    if($stores_table_check && mysqli_num_rows($stores_table_check) > 0) {
        $store_sql = "SELECT * FROM stores WHERE seller_username='" . mysqli_real_escape_string($conn, $seller_username) . "' LIMIT 1";
        $store_result = mysqli_query($conn, $store_sql);
        if($store_result && $store = mysqli_fetch_assoc($store_result)) {
            $seller_store_name = $store['store_name'];
            $seller_store_address = $store['store_address'];
        }
    }

    $store_name_escaped = mysqli_real_escape_string($conn, $seller_store_name);
    $store_address_escaped = mysqli_real_escape_string($conn, $seller_store_address);

    // Simpan satu order untuk seller ini
    if($has_store_columns) {
        $order_sql = "INSERT INTO orders (buyer_username, seller_username, seller_store_name, seller_store_address, total_amount, status) 
                      VALUES ('$buyer_escaped', '$seller_escaped', '$store_name_escaped', '$store_address_escaped', '$total_escaped', 'pending')";
    } else {
        $order_sql = "INSERT INTO orders (buyer_username, seller_username, total_amount, status) 
                      VALUES ('$buyer_escaped', '$seller_escaped', '$total_escaped', 'pending')";
    }
    if(!mysqli_query($conn, $order_sql)) { continue; }
    $order_id = mysqli_insert_id($conn);
    $created_orders[] = $order_id;

    // Simpan item untuk seller ini
    foreach($item_indexes as $idx){
        $item = $order_items_data[$idx];
        $product_id_escaped = mysqli_real_escape_string($conn, $item['product_id']);
        $product_name_escaped = mysqli_real_escape_string($conn, $item['product_name']);
        $quantity_escaped = mysqli_real_escape_string($conn, $item['quantity']);
        $price_escaped = mysqli_real_escape_string($conn, $item['price']);
        $subtotal_escaped = mysqli_real_escape_string($conn, $item['subtotal']);
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES ('$order_id', '$product_id_escaped', '$product_name_escaped', '$quantity_escaped', '$price_escaped', '$subtotal_escaped')");
        // Update stock jika ada
        $col_stock = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
        if($col_stock && mysqli_num_rows($col_stock)>0) {
            $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE id='$product_id_escaped' LIMIT 1"));
            if($prod) {
                $current_stock = (int)$prod['stock'];
                $new_stock = $current_stock - (int)$item['quantity'];
                if($new_stock < 0) { $new_stock = 0; }
                mysqli_query($conn, "UPDATE products SET stock='$new_stock' WHERE id='$product_id_escaped' LIMIT 1");
            }
        }
    }

    // Notifikasi ke seller
    $notif_title = "Order Baru";
    $notif_message = "Anda mendapat order baru dari $buyer_username dengan total Rp " . number_format($seller_total, 0, ',', '.');
    mysqli_query($conn, "INSERT INTO notifications (user_username, title, message, type, link) VALUES ('$seller_escaped', '$notif_title', '$notif_message', 'info', 'orders.php?order_id=$order_id')");

    // Notifikasi ke buyer (per seller)
    $buyer_notif_title = "Order Berhasil";
    $buyer_notif_message = "Order Anda ke $seller_username telah dibuat.";
    mysqli_query($conn, "INSERT INTO notifications (user_username, title, message, type, link) VALUES ('$buyer_escaped', '$buyer_notif_title', '$buyer_notif_message', 'success', 'my_orders.php?order_id=$order_id')");
}

// Kosongkan cart
$_SESSION['cart'] = [];

// Redirect ke Order Saya agar melihat semua order per toko
header("Location: my_orders.php?success=1&created=" . urlencode(implode(',', $created_orders)));
exit;
?>

