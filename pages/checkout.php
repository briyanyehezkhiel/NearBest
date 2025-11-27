<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) { 
    header("Location: ../auth/login.php"); 
    exit; 
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$success = isset($_GET['success']) ? $_GET['success'] : 0;

if($order_id > 0 && $success) {
    // Ambil data order
    $order_sql = "SELECT * FROM orders WHERE id='$order_id' AND buyer_username='" . mysqli_real_escape_string($conn, $_SESSION['username']) . "'";
    $order_result = mysqli_query($conn, $order_sql);
    $order = mysqli_fetch_assoc($order_result);
    
    if($order) {
        // Ambil order items
        $items_sql = "SELECT * FROM order_items WHERE order_id='$order_id'";
        $items_result = mysqli_query($conn, $items_sql);
        
        // Ambil info toko seller
        $seller_info = [];
        if(!empty($order['seller_username'])) {
            $store_sql = "SELECT * FROM stores WHERE seller_username='" . mysqli_real_escape_string($conn, $order['seller_username']) . "' LIMIT 1";
            $store_result = mysqli_query($conn, $store_sql);
            if($store_result && $store = mysqli_fetch_assoc($store_result)) {
                $seller_info = $store;
            } else {
                // Jika tidak ada di stores, ambil dari kolom order atau default
                $seller_info = [
                    'store_name' => !empty($order['seller_store_name']) ? $order['seller_store_name'] : $order['seller_username'],
                    'store_address' => !empty($order['seller_store_address']) ? $order['seller_store_address'] : 'Alamat belum diisi'
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout - NearBest</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
body { background:#f6f6f6; }
.container { max-width:800px; margin:40px auto; padding:0 20px; }
.success-box { background:#fff; padding:40px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); text-align:center; margin-bottom:30px; }
.success-icon { font-size:64px; margin-bottom:20px; }
.success-box h2 { color:#333; margin-bottom:15px; font-size:28px; }
.success-box p { color:#666; font-size:16px; line-height:1.6; margin-bottom:10px; }
.info-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.info-box h3 { color:#333; margin-bottom:20px; font-size:20px; }
.order-details { background:#f9f9f9; padding:20px; border-radius:8px; margin-bottom:15px; }
.order-details p { margin:8px 0; color:#666; }
.order-details strong { color:#333; }
.order-items { margin-top:20px; }
.order-item { display:flex; justify-content:space-between; padding:15px; border-bottom:1px solid #eee; }
.order-item:last-child { border-bottom:none; }
.order-item-name { flex:1; }
.order-item-qty { margin:0 20px; }
.order-item-price { font-weight:bold; color:#3b2db2; }
.total-row { display:flex; justify-content:space-between; padding:20px; background:#3b2db2; color:white; border-radius:8px; margin-top:20px; font-size:20px; font-weight:bold; }
.important-note { background:#fff3cd; border-left:4px solid #ffc107; padding:20px; border-radius:8px; margin-top:20px; }
.important-note h4 { color:#856404; margin-bottom:10px; }
.important-note ul { color:#856404; margin-left:20px; }
.important-note li { margin:8px 0; }
.btn { display:inline-block; padding:12px 30px; background:#3b2db2; color:white; text-decoration:none; border-radius:8px; font-weight:500; margin:10px 5px; transition:.2s; }
.btn:hover { background:#2c2297; }
.btn-secondary { background:#6c757d; }
.btn-secondary:hover { background:#5a6268; }
</style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container">
    <?php if($order_id > 0 && $success && isset($order)): ?>
    <div class="success-box">
        <div class="success-icon">✅</div>
        <h2>Order Berhasil Dibuat!</h2>
        <p>Order Anda telah berhasil dibuat. Seller akan menerima notifikasi dan akan menghubungi Anda untuk pembayaran dan pengiriman.</p>
    </div>
    
    <div class="info-box">
        <h3>Detail Order</h3>
        <div class="order-details">
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Tanggal:</strong> <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></p>
            <p><strong>Status:</strong> <span style="background:#ffc107;color:#856404;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:bold;"><?php echo strtoupper($order['status']); ?></span></p>
        </div>
        
        <?php if(!empty($seller_info)): ?>
        <div class="order-details" style="background:#e3f2fd; border-left:4px solid #2196F3; margin-top:15px;">
            <h4 style="color:#1976d2; margin-bottom:10px;">🏪 Informasi Toko Seller</h4>
            <p><strong>Nama Toko:</strong> <?php echo htmlspecialchars($seller_info['store_name']); ?></p>
            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($seller_info['store_address']); ?></p>
            <?php if(!empty($seller_info['store_phone'])): ?>
            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($seller_info['store_phone']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="order-items">
            <h4 style="margin-bottom:15px;">Item yang Dipesan:</h4>
            <?php while($item = mysqli_fetch_assoc($items_result)): ?>
            <div class="order-item">
                <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                <div class="order-item-qty"><?php echo $item['quantity']; ?>x</div>
                <div class="order-item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
            </div>
            <?php endwhile; ?>
            
            <div class="total-row">
                <span>Total:</span>
                <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>
    
    <div class="important-note">
        <h4>📌 Informasi Penting:</h4>
        <ul>
            <li>Seller akan menerima notifikasi tentang order Anda</li>
            <li>Silakan hubungi seller melalui fitur Chat untuk membahas pembayaran dan pengiriman</li>
            <li>Aplikasi ini tidak menangani pembayaran atau pengiriman secara langsung</li>
            <li>Semua transaksi dilakukan langsung antara pembeli dan penjual</li>
        </ul>
    </div>
    
    <div style="text-align:center; margin-top:30px;">
        <a href="chat.php?to=admin" class="btn">💬 Chat dengan Seller</a>
        <a href="my_orders.php" class="btn btn-secondary">📦 Lihat Order Saya</a>
        <a href="shop.php" class="btn btn-secondary">🛍️ Lanjut Belanja</a>
    </div>
    
    <?php else: ?>
    <div class="info-box" style="text-align:center;">
        <h2>Checkout</h2>
        <p style="margin:20px 0;">Silakan kembali ke keranjang untuk melakukan checkout.</p>
        <a href="cart.php" class="btn">Kembali ke Keranjang</a>
    </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
