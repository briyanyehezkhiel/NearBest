<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
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
body{font-family:Arial;background:#f6f6f6;margin:0}
.container{
    width:1100px;margin:60px auto;
    background:#fff;padding:25px;border-radius:12px;
    display:flex;gap:40px;
    box-shadow:0 3px 12px rgba(0,0,0,.1);
}
.product-img img{
    width:450px;height:450px;border-radius:12px;
    object-fit:cover; box-shadow:0 3px 10px rgba(0,0,0,.15);
}
.details h2{font-size:32px;margin-bottom:10px;}
.price{color:#3b2db2;font-size:28px;font-weight:bold;margin:10px 0 20px;}
.desc{font-size:15px;line-height:1.5;color:#666;margin-bottom:25px;}

.qty-box{
    width:60px;text-align:center;
    font-size:18px;padding:5px;
    border:1px solid #ccc;border-radius:8px;
}

.qty-wrapper{
    display:flex;align-items:center;gap:10px;margin-bottom:25px;
}

.btn{
    background:#3b2db2;color:white;
    padding:15px 25px;
    border-radius:10px;text-decoration:none;
    font-size:16px;font-weight:bold;
    border:none;cursor:pointer;
    transition:.2s;
}
.btn:hover{opacity:.85;}
</style>

</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container">

    <!-- Left -->
    <div class="product-img">
        <img src="../assets/images/<?php echo $product['image']; ?>" alt="">
    </div>

    <!-- Right -->
    <div class="details">
        <h2><?php echo $product['name']; ?></h2>
        <p class="price">Rp <?php echo number_format($product['price']); ?></p>

        <p class="desc"><?php echo $product['description']; ?></p>

<!-- Category -->
<p style="margin:10px 0;">
    <strong>Category:</strong>
    <?php
        echo (!empty($product['category'])) 
            ? $product['category'] 
            : "<span style='color:#999;'>Belum ada kategori</span>";
    ?>
</p>

<!-- Tags -->
<p style="margin:10px 0;">
    <strong>Tags:</strong>
    <?php
        echo (!empty($product['tags'])) 
            ? $product['tags'] 
            : "<span style='color:#999;'>Belum ada tags</span>";
    ?>
</p>

<hr style="margin:20px 0;">

        <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

            <div class="qty-wrapper">
                <label for="qty">Quantity:</label>
                <input type="number" name="qty" value="1" min="1" class="qty-box">
            </div>

            <button type="submit" name="add" class="btn">Tambah ke Keranjang</button>
        </form>

        <!-- Share Section -->
<p><strong>Bagikan:</strong></p>
<div style="display:flex; gap:10px; align-items:center;">

    <!-- WhatsApp -->
    <a href="https://wa.me/?text=<?php echo urlencode('Lihat produk ini: http://localhost/NearBest/pages/product.php?id='.$product['id']); ?>"
       target="_blank"
       style="background:#25D366;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
       WhatsApp
    </a>

    <!-- Instagram (open profile/share) -->
    <a href="https://www.instagram.com/?url=<?php echo urlencode('http://localhost/NearBest/pages/product.php?id='.$product['id']); ?>"
       target="_blank"
       style="background:#E1306C;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
       Instagram
    </a>

    <!-- Copy Link -->
    <button type="button"
            onclick="copyLink()"
            style="padding:6px 10px;background:#3b2db2;color:white;border:none;border-radius:6px;cursor:pointer;">
        Salin Link
    </button>

</div>

<script>
function copyLink() {
    const link = "<?='http://localhost/NearBest/pages/product.php?id='.$product['id']?>";
    navigator.clipboard.writeText(link);
    alert('Link berhasil disalin!');
}
</script>
    </div>

</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
