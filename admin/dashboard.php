<?php
session_start();
include "../db.php";
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
$users_count = 0;
$products_count = 0;
$orders_count = 0;
$q_users = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
if($q_users) { $r = mysqli_fetch_assoc($q_users); $users_count = (int)$r['c']; }
$q_products = mysqli_query($conn, "SELECT COUNT(*) as c FROM products");
if($q_products) { $r = mysqli_fetch_assoc($q_products); $products_count = (int)$r['c']; }
$q_orders = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders");
if($q_orders) { $r = mysqli_fetch_assoc($q_orders); $orders_count = (int)$r['c']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f6f6f6}
.container{max-width:1100px;margin:40px auto;padding:0 20px}
.header{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);margin-bottom:20px}
.header h1{font-size:28px;color:#333}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);text-align:center}
.card h3{color:#333;margin-bottom:8px}
.count{font-size:36px;font-weight:bold;color:#3b2db2;margin-bottom:10px}
.links{margin-top:30px;display:flex;gap:10px;flex-wrap:wrap}
.btn{padding:12px 20px;background:#3b2db2;color:#fff;text-decoration:none;border-radius:8px;font-weight:500}
.btn:hover{background:#2c2297}
.btn-secondary{background:#6c757d}
.btn-secondary:hover{background:#5a6268}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container">
    <div class="header"><h1>📊 Admin Dashboard</h1></div>
    <div class="links" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap">
        <a href="manage_users.php" class="btn">👥 Kelola Users</a>
        <a href="manage_products.php" class="btn">🛒 Kelola Products</a>
        <a href="../pages/orders.php" class="btn btn-secondary">📦 Orders Seller</a>
        <a href="../pages/manage_store.php" class="btn btn-secondary">🏪 Kelola Toko</a>
    </div>
    <?php 
    $status_counts = ['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
    $st = mysqli_query($conn, "SELECT LOWER(status) s, COUNT(*) c FROM orders GROUP BY LOWER(status)");
    if($st){ while($r=mysqli_fetch_assoc($st)){ $status_counts[$r['s']] = (int)$r['c']; } }
    $last7=[]; $max=0; for($i=6;$i>=0;$i--){ $day=date('Y-m-d', strtotime("-$i day")); $q=mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE DATE(created_at)='$day'"); $c=0; if($q){ $row=mysqli_fetch_assoc($q); $c=(int)$row['c']; } $last7[]=['day'=>$day,'c'=>$c]; if($c>$max)$max=$c; }
    ?>
    <div class="grid">
        <div class="card"><div class="count"><?=$users_count?></div><h3>Users</h3></div>
        <div class="card"><div class="count"><?=$products_count?></div><h3>Products</h3></div>
        <div class="card"><div class="count"><?=$orders_count?></div><h3>Orders</h3></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin:20px 0;">
        <div class="card"><h3>Status: Pending</h3><div class="count"><?= (int)$status_counts['pending'] ?></div></div>
        <div class="card"><h3>Status: Confirmed</h3><div class="count"><?= (int)$status_counts['confirmed'] ?></div></div>
        <div class="card"><h3>Status: Completed</h3><div class="count"><?= (int)$status_counts['completed'] ?></div></div>
        <div class="card"><h3>Status: Cancelled</h3><div class="count"><?= (int)$status_counts['cancelled'] ?></div></div>
    </div>
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-bottom:10px;">Orders 7 Hari Terakhir</h3>
        <div style="display:flex;align-items:flex-end;gap:10px;height:160px;">
            <?php $scale = $max>0 ? 140/$max : 0; foreach($last7 as $d){ $h=$max>0?(int)($d['c']*$scale):0; ?>
            <div style="width:24px;background:#3b2db2;border-radius:6px 6px 0 0;position:relative;height:<?=$h?>px"><span style="position:absolute;top:-20px;left:50%;transform:translateX(-50%);font-size:12px;"><?=$d['c']?></span></div>
            <?php } ?>
        </div>
        <div style="display:flex;gap:10px;justify-content:space-between;margin-top:6px;color:#666;font-size:12px;">
            <?php foreach($last7 as $d){ echo '<div>'.date('D', strtotime($d['day'])).'</div>'; } ?>
        </div>
    </div>
    
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
