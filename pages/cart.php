<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data cart dari session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Shopping Cart - NearBest</title>

<style>
body {background:#f6f6f6;font-family:Arial;margin:0;}
.container {width:1100px;margin:50px auto;background:#fff;padding:25px;border-radius:12px;box-shadow:0 3px 10px rgba(0,0,0,.1);}
table {width:100%;border-collapse:collapse;margin-bottom:30px;}
th {text-align:left;background:#3b2db2;color:white;padding:12px;font-size:15px;}
td {padding:15px;border-bottom:1px solid #eee;font-size:14px;}
img {width:70px;border-radius:10px;object-fit:cover;}
.qty-box {width:50px;text-align:center;border:1px solid #ccc;border-radius:8px;padding:5px;}
.totals-box {background:#f0f0f0;padding:20px;border-radius:12px;margin-top:20px;}
.btn {padding:10px 20px;background:#3b2db2;color:white;text-decoration:none;border-radius:8px;font-weight:600;}
.btn:hover {opacity:.8;}
.right {text-align:right;font-size:18px;font-weight:bold;}
</style>

</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container">
<h2 style="margin-bottom:20px;">Shopping Cart</h2>

<?php if(count($cart) > 0){ ?>
<table>
<tr>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Total</th>
</tr>

<?php foreach($cart as $id => $qty):
    $q = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $row = mysqli_fetch_assoc($q);

    $price = $row['price'];
    $sub = $price * $qty;
    $total += $sub;
?>
<tr>
    <td>
        <img src="../assets/images/<?php echo $row['image']; ?>"> 
        <?php echo $row['name']; ?>
    </td>
    <td>Rp <?php echo number_format($price); ?></td>
    <td>
    <form action="update_cart.php" method="POST" style="display:flex;gap:8px;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <button type="submit" name="minus" style="padding:5px 10px;">-</button>

        <input type="text" name="qty" value="<?php echo $qty; ?>" class="qty-box">

        <button type="submit" name="plus" style="padding:5px 10px;">+</button>
    </form>
</td>

    <td>Rp <?php echo number_format($sub); ?></td>
</tr>
<?php endforeach; ?>

</table>

<div class="totals-box">
    <p class="right">Subtotal: Rp <?php echo number_format($total); ?></p>
    <p class="right">Total: Rp <?php echo number_format($total); ?></p>
    <br>
    <form action="process_checkout.php" method="POST" style="float:right;">
        <button type="submit" class="btn" style="border:none;cursor:pointer;">Proceed to Checkout</button>
    </form>
</div>

<?php } else { ?>
    <p>Keranjang masih kosong!</p>
<?php } ?>

</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
