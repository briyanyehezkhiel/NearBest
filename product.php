<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo $product['name']; ?> - NearBest</title>
<style>
    body { font-family: Arial; background: #f6f6f6; margin: 0; }
    .container { width: 600px; margin: 50px auto; background:#fff; padding:20px; border-radius:10px; }
    img { width:100%; border-radius:10px; }
    .price { font-size:20px; font-weight:bold; color: #3b2db2; }
    button {
        background:#3b2db2; color:white; border:none;
        padding:10px 15px; border-radius:8px;
        cursor:pointer; font-size:16px; width:100%;
    }
</style>
</head>
<body>

<div class="container">
    <h2><?php echo $product['name']; ?></h2>
    <img src="assets/images/<?php echo $product['image']; ?>" alt="">
    <p class="price">Rp <?php echo number_format($product['price']); ?></p>
    <p><?php echo $product['description']; ?></p>

    <form action="cart.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <button type="submit" name="add">Tambah ke Keranjang</button>
    </form>
</div>

</body>
</html>
