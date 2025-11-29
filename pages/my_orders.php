<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_username = $_SESSION['username'];
$username_escaped = mysqli_real_escape_string($conn, $buyer_username);

$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$allowed_per = [5,10,20,50]; if(!in_array($per_page,$allowed_per)) $per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; if($page<1) $page=1;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$whereParts = ["buyer_username='$username_escaped'"];
if($q!==''){ $q_escaped = mysqli_real_escape_string($conn, $q); $whereParts[] = "(seller_username LIKE '%$q_escaped%' OR status LIKE '%$q_escaped%' OR CAST(id AS CHAR) LIKE '%$q_escaped%')"; }
$whereSql = 'WHERE '.implode(' AND ', $whereParts);
$countRes = mysqli_query($conn, "SELECT COUNT(*) c FROM orders $whereSql");
$total = 0; if($countRes){ $row=mysqli_fetch_assoc($countRes); $total=(int)$row['c']; }
$offset = ($page-1)*$per_page;
$result = mysqli_query($conn, "SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$total_pages = $per_page>0 ? max(1, (int)ceil($total/$per_page)) : 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Order Saya - NearBest</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
body { background:#f6f6f6; }
.container { max-width:1100px; margin:40px auto; padding:0 20px; }
.page-header { background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.page-header h1 { font-size:28px; color:#333; }
.order-card { background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.order-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f0f0f0; }
.order-id { font-size:18px; font-weight:bold; color:#3b2db2; }
.order-date { color:#999; font-size:14px; }
.status-badge { padding:6px 15px; border-radius:20px; font-size:12px; font-weight:bold; }
.status-pending { background:#fff3cd; color:#856404; }
.status-confirmed { background:#d1ecf1; color:#0c5460; }
.status-completed { background:#d4edda; color:#155724; }
.status-cancelled { background:#f8d7da; color:#721c24; }
.order-items { margin:20px 0; }
.order-item { display:flex; justify-content:space-between; padding:12px; background:#f9f9f9; border-radius:8px; margin-bottom:10px; }
.order-item-name { flex:1; }
.order-item-qty { margin:0 15px; color:#666; }
.order-item-price { font-weight:bold; color:#3b2db2; }
.order-total { display:flex; justify-content:space-between; padding:15px; background:#3b2db2; color:white; border-radius:8px; font-size:18px; font-weight:bold; margin-top:15px; }
.order-actions { margin-top:15px; display:flex; gap:10px; }
.btn { padding:10px 20px; background:#3b2db2; color:white; text-decoration:none; border-radius:8px; font-size:14px; font-weight:500; transition:.2s; border:none; cursor:pointer; }
.btn:hover { background:#2c2297; }
.btn-secondary { background:#6c757d; }
.btn-secondary:hover { background:#5a6268; }
.empty-orders { text-align:center; padding:60px 20px; background:#fff; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); }
.empty-orders-icon { font-size:64px; margin-bottom:20px; }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="page-header">
        <h1>📦 Order Saya</h1>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:12px">
        <label style="display:flex;gap:6px;align-items:center">Per Halaman
            <select name="per_page" style="padding:6px;border:1px solid #ddd;border-radius:6px">
                <option value="5"<?= $per_page===5?' selected':'' ?>>5</option>
                <option value="10"<?= $per_page===10?' selected':'' ?>>10</option>
                <option value="20"<?= $per_page===20?' selected':'' ?>>20</option>
                <option value="50"<?= $per_page===50?' selected':'' ?>>50</option>
            </select>
        </label>
        <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari seller/status/ID order" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1">
        <input type="hidden" name="page" value="1">
        <button class="btn" style="background:#3b2db2">Terapkan</button>
        <div style="margin-left:auto;color:#666">Total: <?= (int)$total ?> orders</div>
    </form>
    
    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($result)): 
            // Ambil order items
            $order_id = $order['id'];
            $items_sql = "SELECT * FROM order_items WHERE order_id='$order_id'";
            $items_result = mysqli_query($conn, $items_sql);
            
            // Ambil info toko seller
            $seller_info = [];
            if(!empty($order['seller_username'])) {
                $stores_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'stores'");
                if($stores_table_check && mysqli_num_rows($stores_table_check) > 0) {
                    $store_sql = "SELECT * FROM stores WHERE seller_username='" . mysqli_real_escape_string($conn, $order['seller_username']) . "' LIMIT 1";
                    $store_result = mysqli_query($conn, $store_sql);
                    if($store_result && $store = mysqli_fetch_assoc($store_result)) {
                        $seller_info = $store;
                    }
                }
                if(empty($seller_info)) {
                    $seller_info = [
                        'store_name' => !empty($order['seller_store_name']) ? $order['seller_store_name'] : $order['seller_username'],
                        'store_address' => !empty($order['seller_store_address']) ? $order['seller_store_address'] : 'Alamat belum diisi'
                    ];
                }
            }
        ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">Order #<?php echo $order['id']; ?></div>
                    <div class="order-date"><?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></div>
                </div>
                <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php echo strtoupper($order['status']); ?>
                </span>
            </div>
            
            <div class="order-items">
                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                <div class="order-item">
                    <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div class="order-item-qty"><?php echo $item['quantity']; ?>x</div>
                    <div class="order-item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="order-total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
            </div>
            
            <?php if(!empty($seller_info)): ?>
            <div style="background:#e3f2fd; padding:15px; border-radius:8px; margin-top:15px; border-left:4px solid #2196F3;">
                <h4 style="color:#1976d2; margin-bottom:8px; font-size:14px;">🏪 Toko Seller</h4>
                <p style="color:#666; font-size:13px; margin:4px 0;"><strong><?php echo htmlspecialchars($seller_info['store_name']); ?></strong></p>
                <p style="color:#666; font-size:13px; margin:4px 0;"><?php echo htmlspecialchars($seller_info['store_address']); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="order-actions">
                <a href="chat.php?to=<?php echo htmlspecialchars($order['seller_username']); ?>" class="btn">💬 Chat Seller</a>
                <a href="shop.php" class="btn btn-secondary">🛍️ Belanja Lagi</a>
            </div>
        </div>
        <?php endwhile; ?>
        <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin:10px 0 20px">
            <?php if($page>1): ?>
                <a class="btn btn-secondary" href="?page=<?= $page-1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q) ?>">Prev</a>
            <?php endif; ?>
            <div style="color:#666">Halaman <?= (int)$page ?> / <?= (int)$total_pages ?></div>
            <?php if($page < $total_pages): ?>
                <a class="btn btn-secondary" href="?page=<?= $page+1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-orders">
            <div class="empty-orders-icon">📦</div>
            <h2>Belum Ada Order</h2>
            <p style="color:#666; margin:15px 0;">Anda belum memiliki order. Mulai belanja sekarang!</p>
            <a href="shop.php" class="btn">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

