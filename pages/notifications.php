<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil semua notifikasi user
$sql = "SELECT * FROM notifications WHERE user_username = '$username' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Update semua notifikasi menjadi sudah dibaca
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_username = '$username'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifikasi - NearBest</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { background:#f6f6f6; }
    
    .container {
        max-width:800px;
        margin:40px auto;
        padding:0 20px;
    }
    
    .page-header {
        background:#fff;
        padding:30px;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
        margin-bottom:20px;
    }
    
    .page-header h1 {
        font-size:28px;
        color:#333;
    }
    
    .notifications-list {
        background:#fff;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
        overflow:hidden;
    }
    
    .notification-item {
        padding:20px;
        border-bottom:1px solid #eee;
        transition:.2s;
        cursor:pointer;
    }
    
    .notification-item:hover {
        background:#f9f9f9;
    }
    
    .notification-item:last-child {
        border-bottom:none;
    }
    
    .notification-item.unread {
        background:#f0f7ff;
        border-left:4px solid #3b2db2;
    }
    
    .notification-header {
        display:flex;
        justify-content:space-between;
        align-items:start;
        margin-bottom:8px;
    }
    
    .notification-title {
        font-weight:600;
        color:#333;
        font-size:16px;
    }
    
    .notification-time {
        font-size:12px;
        color:#999;
    }
    
    .notification-message {
        color:#666;
        font-size:14px;
        line-height:1.5;
    }
    
    .notification-type {
        display:inline-block;
        padding:4px 10px;
        border-radius:12px;
        font-size:11px;
        font-weight:500;
        margin-top:8px;
    }
    
    .type-chat {
        background:#e3f2fd;
        color:#1976d2;
    }
    
    .type-info {
        background:#f3e5f5;
        color:#7b1fa2;
    }
    
    .type-success {
        background:#e8f5e9;
        color:#388e3c;
    }
    
    .type-warning {
        background:#fff3e0;
        color:#f57c00;
    }
    
    .empty-notifications {
        text-align:center;
        padding:60px 20px;
        color:#999;
    }
    
    .empty-notifications-icon {
        font-size:64px;
        margin-bottom:20px;
    }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="page-header">
        <h1>🔔 Notifikasi</h1>
    </div>
    
    <div class="notifications-list">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($notif = mysqli_fetch_assoc($result)): ?>
            <div class="notification-item <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>" 
                 onclick="<?php echo $notif['link'] ? "location.href='{$notif['link']}'" : ''; ?>">
                <div class="notification-header">
                    <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                    <div class="notification-time">
                        <?php 
                        $time = strtotime($notif['created_at']);
                        $now = time();
                        $diff = $now - $time;
                        
                        if($diff < 60) {
                            echo 'Baru saja';
                        } elseif($diff < 3600) {
                            echo floor($diff/60) . ' menit lalu';
                        } elseif($diff < 86400) {
                            echo floor($diff/3600) . ' jam lalu';
                        } else {
                            echo date('d M Y', $time);
                        }
                        ?>
                    </div>
                </div>
                <div class="notification-message">
                    <?php echo htmlspecialchars($notif['message']); ?>
                </div>
                <span class="notification-type type-<?php echo $notif['type']; ?>">
                    <?php echo ucfirst($notif['type']); ?>
                </span>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-notifications">
                <div class="empty-notifications-icon">🔕</div>
                <p>Tidak ada notifikasi</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

