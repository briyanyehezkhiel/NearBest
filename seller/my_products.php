<?php
session_start();
include "../db.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='seller'){
    header("Location: ../auth/login.php");
    exit;
}
$username = $_SESSION['username'];
$u = mysqli_real_escape_string($conn, $username);
$user_id = 0;
$q = mysqli_query($conn, "SELECT id FROM users WHERE username='$u' LIMIT 1");
if($q && $row = mysqli_fetch_assoc($q)) { $user_id = (int)$row['id']; }
$has_stock = false;
$col_stock = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
if($col_stock && mysqli_num_rows($col_stock)>0){ $has_stock = true; }
$select = $has_stock ? "id,name,price,image,stock" : "id,name,price,image";
$products = mysqli_query($conn, "SELECT $select FROM products WHERE seller_id='$user_id' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Produk Saya</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f6f6f6}
.container{max-width:1100px;margin:40px auto;padding:0 20px}
.header{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);margin-bottom:20px}
.header h1{font-size:28px;color:#333}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px}
.card{background:#fff;padding:15px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);text-align:center}
.card img{width:100%;height:160px;object-fit:cover;border-radius:10px}
.price{margin:8px 0;font-size:16px;font-weight:bold;color:#3b2db2}
.btn{display:inline-block;margin-top:8px;padding:8px 14px;background:#3b2db2;color:#fff;text-decoration:none;border-radius:8px}
.btn:hover{background:#2c2297}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container">
    <div class="header"><h1>Produk Saya</h1></div>
    <div class="grid">
        <?php while($p = mysqli_fetch_assoc($products)): ?>
        <div class="card">
            <img src="../assets/images/<?=htmlspecialchars($p['image'])?>">
            <h3><?=htmlspecialchars($p['name'])?></h3>
            <div class="price">Rp <?=number_format($p['price'])?></div>
            <div style="color:#666;font-size:13px;">Stok: <?=isset($p['stock']) ? (int)$p['stock'] : 0?></div>
            <a class="btn" href="edit_product.php?id=<?=htmlspecialchars($p['id'])?>">Edit</a>
        </div>
        <?php endwhile; ?>
    </div>
    
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
