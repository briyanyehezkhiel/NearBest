<?php
if(!isset($_SESSION)) { session_start(); }

// Hitung notifikasi yang belum dibaca
$notification_count = 0;
if(isset($_SESSION['username'])) {
    @include __DIR__ . '/../db.php';
    if(isset($conn) && $conn) {
        $username = $_SESSION['username'];
        // Cek apakah tabel notifications ada
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
        if($table_check && mysqli_num_rows($table_check) > 0) {
            $notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_username = '$username' AND is_read = 0";
            $notif_result = mysqli_query($conn, $notif_sql);
            if($notif_result) {
                $notif_row = mysqli_fetch_assoc($notif_result);
                $notification_count = isset($notif_row['count']) ? (int)$notif_row['count'] : 0;
            }
        }
    }
}
?>
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
            <a href="../pages/cart.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Keranjang</a>
            <a href="../pages/my_orders.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">📦 Order Saya</a>
            <a href="../pages/chat.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;position:relative;">💬 Chat</a>
            <a href="../pages/notifications.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;position:relative;">
                🔔 Notifikasi
                <?php if($notification_count > 0): ?>
                <span style="
                    position:absolute;
                    top:-8px;
                    right:-12px;
                    background:#ff4444;
                    color:white;
                    border-radius:50%;
                    width:20px;
                    height:20px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:11px;
                    font-weight:bold;
                "><?php echo $notification_count > 9 ? '9+' : $notification_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../pages/profile.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Profile</a>
            <a href="../logout.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Logout</a>
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
