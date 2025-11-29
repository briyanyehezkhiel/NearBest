<?php
session_start();
include "../db.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
}

// Ambil data user
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
if(empty($username)) {
    header("Location: ../auth/login.php");
    exit;
}

// Pastikan koneksi database ada
if(!isset($conn) || !$conn) {
    die("Database connection error");
}

$username_escaped = mysqli_real_escape_string($conn, $username);
$sql = "SELECT * FROM users WHERE username='$username_escaped' LIMIT 1";
$result = mysqli_query($conn, $sql);

// Cek apakah query berhasil
if(!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

// Debug: Pastikan $user adalah array
if(!is_array($user)) {
    // Jika bukan array, coba fetch lagi atau cek error
    if(mysqli_num_rows($result) == 0) {
        session_destroy();
        header("Location: ../auth/login.php?error=user_not_found");
        exit;
    }
    die("Error: User data is not an array. Please check database connection.");
}

// Jika user tidak ditemukan atau username kosong
if(empty($user) || !isset($user['username']) || empty($user['username'])) {
    session_destroy();
    header("Location: ../auth/login.php?error=invalid_user");
    exit;
}

// Simpan username ke variabel untuk menghindari akses array berulang
// Pastikan $user adalah array dan memiliki username
if(!is_array($user) || !isset($user['username'])) {
    session_destroy();
    header("Location: ../auth/login.php?error=invalid_data");
    exit;
}

$user_username = (string)$user['username'];
$user_email = isset($user['email']) ? (string)$user['email'] : '';
$user_role = isset($user['role']) ? (string)$user['role'] : 'User';

// Hitung jumlah item di cart
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Hitung notifikasi yang belum dibaca (cek dulu apakah tabel ada)
$notification_count = 0;
$username_escaped_notif = mysqli_real_escape_string($conn, $username);

// Cek apakah tabel notifications ada
$table_check_notif = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if($table_check_notif && mysqli_num_rows($table_check_notif) > 0) {
    $notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_username = '$username_escaped_notif' AND is_read = 0";
    $notif_result = mysqli_query($conn, $notif_sql);
    if($notif_result) {
        $notif_row = mysqli_fetch_assoc($notif_result);
        $notification_count = isset($notif_row['count']) ? (int)$notif_row['count'] : 0;
    }
}

// Hitung jumlah order (cek dulu apakah tabel dan kolom ada)
$order_count = 0;
$order_status_counts = ['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
$orders_last7 = [];
$orders_table_exists = false;
$table_check_orders = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if($table_check_orders && mysqli_num_rows($table_check_orders) > 0) {
    $orders_table_exists = true;
    // Cek apakah kolom buyer_username ada
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'buyer_username'");
    if($column_check && mysqli_num_rows($column_check) > 0) {
        $order_sql = "SELECT COUNT(*) as count FROM orders WHERE buyer_username = '$username_escaped_notif'";
    } else {
        $user_id_sql = "SELECT id FROM users WHERE username = '$username_escaped_notif' LIMIT 1";
        $user_id_result = mysqli_query($conn, $user_id_sql);
        if($user_id_result && $user_id_row = mysqli_fetch_assoc($user_id_result)) {
            $user_id = (int)$user_id_row['id'];
            $order_sql = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = '$user_id'";
        } else {
            $order_sql = "SELECT COUNT(*) as count FROM orders WHERE 1=0"; // Return 0
        }
    }
    
    $order_result = mysqli_query($conn, $order_sql);
    if($order_result) {
        $order_row = mysqli_fetch_assoc($order_result);
        $order_count = isset($order_row['count']) ? (int)$order_row['count'] : 0;
    }

    // Hitung status untuk role
    if($user_role==='admin') {
        $st = mysqli_query($conn, "SELECT LOWER(status) s, COUNT(*) c FROM orders GROUP BY LOWER(status)");
    } elseif($user_role==='seller') {
        $st = mysqli_query($conn, "SELECT LOWER(status) s, COUNT(*) c FROM orders WHERE seller_username='$username_escaped_notif' GROUP BY LOWER(status)");
    } else {
        $st = mysqli_query($conn, "SELECT LOWER(status) s, COUNT(*) c FROM orders WHERE buyer_username='$username_escaped_notif' GROUP BY LOWER(status)");
    }
    if($st){ while($r=mysqli_fetch_assoc($st)){ $key=$r['s']; $order_status_counts[$key]= (int)$r['c']; } }

    // Grafik 7 hari terakhir
    for($i=6;$i>=0;$i--){
        $day = date('Y-m-d', strtotime("-$i day"));
        if($user_role==='admin') {
            $q = mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE DATE(created_at)='$day'");
        } elseif($user_role==='seller') {
            $q = mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE seller_username='$username_escaped_notif' AND DATE(created_at)='$day'");
        } else {
            $q = mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE buyer_username='$username_escaped_notif' AND DATE(created_at)='$day'");
        }
        $c = 0; if($q){ $row=mysqli_fetch_assoc($q); $c = (int)$row['c']; }
        $orders_last7[] = ['day'=>$day,'count'=>$c];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - NearBest</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { background:#f6f6f6; }
    
    .container {
        max-width:1100px;
        margin:40px auto;
        padding:0 20px;
    }
    
    .welcome-section {
        background:linear-gradient(135deg, #3b2db2 0%, #2c2297 100%);
        color:white;
        padding:40px;
        border-radius:12px;
        margin-bottom:30px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
    }
    
    .welcome-section h1 {
        font-size:36px;
        margin-bottom:10px;
    }
    
    .welcome-section p {
        font-size:18px;
        opacity:0.9;
    }
    
    .dashboard-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
        gap:20px;
        margin-bottom:30px;
    }
    
    .dashboard-card {
        background:#fff;
        padding:30px;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
        transition:.3s;
        text-align:center;
    }
    
    .dashboard-card:hover {
        transform:translateY(-5px);
        box-shadow:0 5px 20px rgba(0,0,0,0.15);
    }
    
    .card-icon {
        font-size:48px;
        margin-bottom:15px;
    }
    
    .dashboard-card h3 {
        font-size:20px;
        color:#333;
        margin-bottom:10px;
    }
    
    .dashboard-card p {
        color:#666;
        font-size:14px;
        margin-bottom:20px;
    }
    
    .btn {
        display:inline-block;
        padding:12px 24px;
        background:#3b2db2;
        color:white;
        text-decoration:none;
        border-radius:8px;
        font-weight:500;
        transition:.2s;
        border:none;
        cursor:pointer;
        font-size:15px;
    }
    
    .btn:hover {
        background:#2c2297;
    }
    
    .btn-secondary {
        background:#6c757d;
    }
    
    .btn-secondary:hover {
        background:#5a6268;
    }
    
    .btn-danger {
        background:#dc3545;
    }
    
    .btn-danger:hover {
        background:#c82333;
    }
    
    .quick-actions {
        background:#fff;
        padding:30px;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
    }
    
    .quick-actions h2 {
        font-size:24px;
        color:#333;
        margin-bottom:20px;
    }
    
    .action-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
        gap:15px;
    }
    
    .action-item {
        padding:20px;
        background:#f9f9f9;
        border-radius:8px;
        text-align:center;
        border:2px solid transparent;
        transition:.2s;
    }
    
    .action-item:hover {
        border-color:#3b2db2;
        background:#fff;
    }
    
    .action-item a {
        color:#3b2db2;
        text-decoration:none;
        font-weight:500;
        display:block;
    }
    .metrics {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:30px}
    .metric {background:#fff;padding:22px;border-radius:12px;box-shadow:0 3px 12px rgba(0,0,0,.1)}
    .metric h4{color:#666;font-size:14px;margin-bottom:8px}
    .metric .num{font-size:28px;font-weight:bold;color:#3b2db2}
    .chart{background:#fff;padding:22px;border-radius:12px;box-shadow:0 3px 12px rgba(0,0,0,.1)}
    .bars{display:flex;align-items:flex-end;gap:10px;height:160px}
    .bar{width:24px;background:#3b2db2;border-radius:6px 6px 0 0;position:relative}
    .bar span{position:absolute;top:-20px;left:50%;transform:translateX(-50%);font-size:12px;color:#333}
    .bar-day{font-size:11px;color:#666;text-align:center;margin-top:6px}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="welcome-section">
        <h1>Selamat Datang, <?php echo htmlspecialchars($user_username); ?>! 👋</h1>
        <p><?php echo ($user_role==='admin')?'Ringkasan sistem dan aktivitas pesanan':(($user_role==='seller')?'Kelola pesanan dan produk toko Anda':'Mulai belanja dan temukan produk terbaik untuk Anda'); ?></p>
    </div>

    <?php if($orders_table_exists): ?>
    <div class="metrics">
        <div class="metric"><h4>Total Orders</h4><div class="num"><?php echo $order_count; ?></div></div>
        <div class="metric"><h4>Pending</h4><div class="num"><?php echo (int)$order_status_counts['pending']; ?></div></div>
        <div class="metric"><h4>Confirmed</h4><div class="num"><?php echo (int)$order_status_counts['confirmed']; ?></div></div>
        <div class="metric"><h4>Completed</h4><div class="num"><?php echo (int)$order_status_counts['completed']; ?></div></div>
        <div class="metric"><h4>Cancelled</h4><div class="num"><?php echo (int)$order_status_counts['cancelled']; ?></div></div>
    </div>
    <div class="chart">
        <h2 style="font-size:20px;color:#333;margin-bottom:12px;">Orders 7 Hari Terakhir</h2>
        <div class="bars">
            <?php 
            $maxCount = 0; foreach($orders_last7 as $d){ if($d['count']>$maxCount) $maxCount=$d['count']; }
            $scale = $maxCount>0 ? 140/$maxCount : 0; 
            foreach($orders_last7 as $d){ $h = $maxCount>0 ? (int)($d['count']*$scale) : 0; ?>
                <div class="bar" style="height:<?php echo $h; ?>px"><span><?php echo $d['count']; ?></span></div>
            <?php } ?>
        </div>
        <div style="display:flex;gap:10px;justify-content:space-between;margin-top:6px;">
            <?php foreach($orders_last7 as $d){ $label=date('D', strtotime($d['day'])); ?>
            <div class="bar-day"><?php echo $label; ?></div>
            <?php } ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-icon">🛍️</div>
            <h3>Belanja Sekarang</h3>
            <p>Jelajahi koleksi produk terbaru kami</p>
            <a href="shop.php" class="btn">Mulai Belanja</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">🛒</div>
            <h3>Keranjang</h3>
            <p><?php echo $cart_count; ?> item di keranjang Anda</p>
            <a href="cart.php" class="btn">Lihat Keranjang</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">👤</div>
            <h3>Profile</h3>
            <p>Kelola informasi akun Anda</p>
            <a href="profile.php" class="btn">Lihat Profile</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">💬</div>
            <h3>Chat</h3>
            <p>Hubungi admin untuk bantuan</p>
            <a href="chat.php" class="btn">Buka Chat</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">🔔</div>
            <h3>Notifikasi</h3>
            <p><?php echo $notification_count > 0 ? "$notification_count notifikasi baru" : "Tidak ada notifikasi baru"; ?></p>
            <a href="notifications.php" class="btn">Lihat Notifikasi</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">📦</div>
            <h3>Order Saya</h3>
            <p><?php echo $order_count > 0 ? "$order_count order" : "Belum ada order"; ?></p>
            <a href="my_orders.php" class="btn">Lihat Order</a>
        </div>
    </div>
    
    <div class="quick-actions">
        <h2>Akses Cepat</h2>
        <div class="action-grid">
            <div class="action-item">
                <a href="shop.php">🛍️ Shop</a>
            </div>
            <div class="action-item">
                <a href="cart.php">🛒 Keranjang (<?php echo $cart_count; ?>)</a>
            </div>
            <div class="action-item">
                <a href="profile.php">👤 Profile</a>
            </div>
            <div class="action-item">
                <a href="chat.php">💬 Chat</a>
            </div>
            <div class="action-item">
                <a href="my_orders.php">📦 Order Saya</a>
            </div>
            <div class="action-item">
                <a href="notifications.php">🔔 Notifikasi<?php echo $notification_count > 0 ? " ($notification_count)" : ""; ?></a>
            </div>
            <?php if(isset($user_role) && ($user_role==='seller' || $user_role==='admin')): ?>
            <div class="action-item">
                <a href="orders.php">📦 Orders Seller</a>
            </div>
            <div class="action-item">
                <a href="manage_store.php">🏪 Kelola Toko</a>
            </div>
            <?php endif; ?>
            <?php if(isset($user_role) && $user_role==='admin'): ?>
            <div class="action-item">
                <a href="../admin/dashboard.php">🧭 Admin Dashboard</a>
            </div>
            <?php endif; ?>
            <div class="action-item">
                <form action="../logout.php" method="POST" style="margin:0;">
                    <button type="submit" class="btn btn-danger" style="width:100%;">🚪 Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
