<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
if(empty($role)) {
    $u = mysqli_real_escape_string($conn, $_SESSION['username']);
    $r = mysqli_query($conn, "SELECT role FROM users WHERE username='$u' LIMIT 1");
    if($r && $row = mysqli_fetch_assoc($r)) { $role = $row['role']; $_SESSION['role'] = $role; }
}
if($role !== 'seller' && $role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$seller_username = $_SESSION['username'];
$username_escaped = mysqli_real_escape_string($conn, $seller_username);

// Paginasi & pencarian seperti Kelola Produk
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$allowed_per = [5,10,20,50]; if(!in_array($per_page,$allowed_per)) $per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; if($page<1) $page=1;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$whereParts = [];
if($role !== 'admin'){ $whereParts[] = "seller_username='$username_escaped'"; }
if($q!==''){ $q_escaped = mysqli_real_escape_string($conn, $q); $whereParts[] = "(buyer_username LIKE '%$q_escaped%' OR status LIKE '%$q_escaped%' OR CAST(id AS CHAR) LIKE '%$q_escaped%')"; }
$whereSql = count($whereParts)>0 ? ('WHERE '.implode(' AND ', $whereParts)) : '';
$countRes = mysqli_query($conn, "SELECT COUNT(*) c FROM orders $whereSql");
$total = 0; if($countRes){ $row=mysqli_fetch_assoc($countRes); $total=(int)$row['c']; }
$offset = ($page-1)*$per_page;
$result = mysqli_query($conn, "SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$total_pages = $per_page>0 ? max(1, (int)ceil($total/$per_page)) : 1;

// Handle update status
if(isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = $role === 'admin'
        ? "UPDATE orders SET status='$new_status' WHERE id='$order_id'"
        : "UPDATE orders SET status='$new_status' WHERE id='$order_id' AND seller_username='$username_escaped'";
    if(mysqli_query($conn, $update_sql)) {
        // Kirim notifikasi ke buyer
        $order_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT buyer_username FROM orders WHERE id='$order_id'"));
        if($order_info) {
            $buyer = mysqli_real_escape_string($conn, $order_info['buyer_username']);
            $notif_title = "Status Order Diupdate";
            $notif_message = "Status order #$order_id telah diupdate menjadi: " . strtoupper($new_status);
            $notif_sql = "INSERT INTO notifications (user_username, title, message, type, link) 
                         VALUES ('$buyer', '$notif_title', '$notif_message', 'info', 'my_orders.php?order_id=$order_id')";
            mysqli_query($conn, $notif_sql);
        }
        
        header("Location: orders.php?updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Orders - NearBest</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
body { background:#f6f6f6; }
.container { max-width:1100px; margin:40px auto; padding:0 20px; }
.page-header { background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.page-header h1 { font-size:28px; color:#333; }
.success-msg { background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #c3e6cb; }
.order-card { background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.order-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f0f0f0; }
.order-id { font-size:18px; font-weight:bold; color:#3b2db2; }
.order-info { color:#666; font-size:14px; margin-top:5px; }
.status-form { display:flex; gap:10px; align-items:center; }
.status-select { padding:8px 15px; border:1px solid #ddd; border-radius:8px; font-size:14px; }
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
        <h1>📦 Orders dari Pembeli</h1>
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
        <input type="text" name="q" value="<?=htmlspecialchars($q ?? '')?>" placeholder="Cari buyer/status/ID order" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1">
        <input type="hidden" name="page" value="1">
        <button class="btn" style="background:#3b2db2">Terapkan</button>
        <div style="margin-left:auto;color:#666">Total: <?= (int)$total ?> orders</div>
    </form>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="success-msg">Status order berhasil diupdate!</div>
    <?php endif; ?>
    
    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($result)): 
            // Ambil order items
            $order_id = $order['id'];
            $items_sql = "SELECT * FROM order_items WHERE order_id='$order_id'";
            $items_result = mysqli_query($conn, $items_sql);
        ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">Order #<?php echo $order['id']; ?></div>
                    <div class="order-info">
                        <strong>Pembeli:</strong> <?php echo htmlspecialchars($order['buyer_username']); ?><br>
                        <strong>Tanggal:</strong> <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                </div>
                <form method="POST" class="status-form">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
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
            
            <div class="order-actions">
                <a href="chat.php?to=<?php echo htmlspecialchars($order['buyer_username']); ?>" class="btn">💬 Chat Pembeli</a>
            </div>
        </div>
        <?php endwhile; ?>
        <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin:10px 0 20px">
            <?php if($page>1): ?>
                <a class="btn btn-secondary" href="?page=<?= $page-1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q ?? '') ?>">Prev</a>
            <?php endif; ?>
            <div style="color:#666">Halaman <?= (int)$page ?> / <?= (int)$total_pages ?></div>
            <?php if($page < $total_pages): ?>
                <a class="btn btn-secondary" href="?page=<?= $page+1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q ?? '') ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-orders">
            <div class="empty-orders-icon">📦</div>
            <h2>Belum Ada Order</h2>
            <p style="color:#666; margin:15px 0;">Belum ada order dari pembeli.</p>
        </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

