<?php
session_start();
if(!isset($_SESSION['username'])) { header("Location: ../auth/login.php"); exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout - NearBest</title>
<style>
body {font-family:Arial;background:#f6f6f6;margin:0;}
.container {width:600px;margin:60px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 10px rgba(0,0,0,0.1);}
.btn {padding:10px 20px;background:#3b2db2;color:white;text-decoration:none;border-radius:8px;font-weight:600;display:block;text-align:center;margin-top:20px;}
</style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container">
    <h2>Checkout</h2>
    <p>Order Anda sedang diproses...</p>
    <a href="shop.php" class="btn">Lanjut Belanja</a>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
