<?php
if(!isset($_SESSION)) { session_start(); }
$notification_count = 0;
$cart_qty_total = 0;
if(isset($_SESSION['username'])) {
    @include __DIR__ . '/../db.php';
    if(isset($conn) && $conn) {
        $username = $_SESSION['username'];
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
        if($table_check && mysqli_num_rows($table_check) > 0) {
            $notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_username = '$username' AND is_read = 0";
            $notif_result = mysqli_query($conn, $notif_sql);
            if($notif_result) { $notif_row = mysqli_fetch_assoc($notif_result); $notification_count = isset($notif_row['count']) ? (int)$notif_row['count'] : 0; }
        }
    }
    if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $cid=>$q){ $cart_qty_total += (int)$q; }
    }
}
?>
<style>
.nav-icon{margin-left:20px;color:white;text-decoration:none;display:flex;align-items:center;gap:6px;font-weight:500;position:relative}
.nav-icon .label{max-width:0;opacity:0;overflow:hidden;transition:all .2s ease}
.nav-icon:hover .label{max-width:140px;opacity:1}
</style>
<nav style="
    background:#3b2db2;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:1000;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
">
    <div style="
        max-width:1100px;
        margin:0 auto;
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:15px 0px;
        color:white;
    ">
        <a href="../pages/shop.php" style="font-size:28px; font-weight:bold; color:white; text-decoration:none;">NearBest</a>
        <div style="display:flex; align-items:center; gap:5px;">
            <?php if(isset($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
            <a href="../admin/dashboard.php" class="nav-icon" style="font-weight:600">🧭 <span class="label">Dashboard</span></a>
            <?php elseif(isset($_SESSION['role']) && $_SESSION['role']==='seller'): ?>
            <a href="../seller/dashboard.php" class="nav-icon" style="font-weight:600">🧭 <span class="label">Dashboard</span></a>
            <?php endif; ?>

            <a href="../pages/cart.php" class="nav-icon">
                <span style="display:inline-block;width:22px;height:22px;background:white;color:#3b2db2;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">🛒</span>
                <span class="label">Keranjang</span>
                <?php if($cart_qty_total>0): ?>
                <span style="position:absolute;top:-6px;right:-10px;background:#ff4444;color:white;border-radius:50%;min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;padding:0 4px;"><?= $cart_qty_total>99?'99+':$cart_qty_total ?></span>
                <?php endif; ?>
            </a>

            <a href="../pages/my_orders.php" class="nav-icon">📦 <span class="label">Order</span></a>

            <a href="../pages/chat.php" class="nav-icon">💬 <span class="label">Chat</span></a>

            <a href="../pages/notifications.php" class="nav-icon">🔔 <span class="label">Notifikasi</span>
                <?php if($notification_count > 0): ?>
                <span style="position:absolute;top:-8px;right:-12px;background:#ff4444;color:white;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;"><?php echo $notification_count > 9 ? '9+' : $notification_count; ?></span>
                <?php endif; ?>
            </a>

            <a href="../pages/profile.php" class="nav-icon">👤 <span class="label">Profile</span></a>
        </div>
    </div>
</nav>

<!-- Spacer supaya konten tidak tertutup navbar -->
<div style="height:65px;"></div>

<script>
// Auto update notifikasi badge setiap 5 detik
<?php if(isset($_SESSION['username'])): ?>
setInterval(function() {
    const xhr = new XMLHttpRequest();
    // Deteksi path berdasarkan current location
    const currentPath = window.location.pathname;
    let apiPath = 'pages/get_notifications.php';
    if(currentPath.includes('/pages/')) {
        apiPath = 'get_notifications.php';
    }
    xhr.open('GET', apiPath, true);
    xhr.onload = function() {
        if(xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            const badge = document.querySelector('a[href*="notifications"] span');
            const link = document.querySelector('a[href*="notifications"]');
            
            if(data.count > 0) {
                if(badge) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                } else if(link) {
                    const newBadge = document.createElement('span');
                    newBadge.style.cssText = 'position:absolute;top:-8px;right:-12px;background:#ff4444;color:white;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;';
                    newBadge.textContent = data.count > 9 ? '9+' : data.count;
                    link.appendChild(newBadge);
                }
            } else {
                if(badge) badge.remove();
            }
        }
    };
    xhr.send();
}, 5000);
<?php endif; ?>
</script>
