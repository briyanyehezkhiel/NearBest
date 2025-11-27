<?php
session_start();
include "../db.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add'])) {
    $id = $_POST['id'];
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 1;
    } else {
        $_SESSION['cart'][$id]++;
    }
}

$cart = $_SESSION['cart'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Keranjang - NearBest</title>
<style>
    body { font-family: Arial; background:#f6f6f6; margin:0; }
    table {
        width: 600px; margin: 50px auto;
        background:#fff; border-radius:10px; padding:20px;
        border-collapse: collapse;
    }
    td, th { padding:10px; }
</style>
</head>
<body>

<h2 style="text-align:center;margin-top:20px;">Keranjang Belanja 🛒</h2>

<table>
    <tr>
        <th>Produk</th>
        <th>Qty</th>
        <th>Total</th>
    </tr>

<?php
$total = 0;
foreach ($cart as $id => $qty) {
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $product = mysqli_fetch_assoc($result);
    $subtotal = $product['price'] * $qty;
    $total += $subtotal;
?>
<tr>
    <td><?php echo $product['name']; ?></td>
    <td><?php echo $qty; ?></td>
    <td>Rp <?php echo number_format($subtotal); ?></td>
</tr>
<?php } ?>

<tr>
<td colspan="2"><strong>Total</strong></td>
<td><strong>Rp <?php echo number_format($total); ?></strong></td>
</tr>

</table>

</body>
</html>
