<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)$_GET['id'];
$id_escaped = mysqli_real_escape_string($conn, $id);

// Ambil produk dengan info seller
$sql = "SELECT p.*, u.username as seller_username, u.role as seller_role
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE p.id='$id_escaped'";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if(!$product) {
    header("Location: shop.php?error=product_not_found");
    exit;
}

// Ambil info toko seller
$seller_info = [];
$seller_username = !empty($product['seller_username']) ? $product['seller_username'] : 'admin';

// Cek apakah tabel stores ada sebelum query
$stores_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'stores'");
if($stores_table_check && mysqli_num_rows($stores_table_check) > 0) {
    $store_sql = "SELECT * FROM stores WHERE seller_username='" . mysqli_real_escape_string($conn, $seller_username) . "' LIMIT 1";
    $store_result = mysqli_query($conn, $store_sql);
    if($store_result && $store = mysqli_fetch_assoc($store_result)) {
        $seller_info = $store;
    }
}
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
        <p style="margin:6px 0; color:#666; font-size:14px;">Stok: <?php echo isset($product['stock']) ? (int)$product['stock'] : 0; ?></p>

        <p class="desc"><?php echo $product['description']; ?></p>

<!-- Category -->
<p style="margin:10px 0;">
    <strong>Category:</strong>
    <?php if(!empty($product['category'])): ?>
        <a href="shop.php?category=<?=urlencode($product['category'])?>" style="color:#3b2db2;text-decoration:none;font-weight:500;">
            <?=htmlspecialchars($product['category'])?>
        </a>
    <?php else: ?>
        <span style='color:#999;'>Belum ada kategori</span>
    <?php endif; ?>
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
                <?php $max = isset($product['stock']) ? (int)$product['stock'] : null; ?>
                <input type="number" name="qty" value="1" min="1" <?php if($max !== null){ echo "max=\"$max\""; } ?> class="qty-box" <?php if($max !== null && $max<=0){ echo 'disabled'; } ?>>
            </div>

            <?php if($max !== null && $max<=0): ?>
            <div style="margin-top:10px;color:#c00;font-weight:bold;">Stok habis</div>
            <?php else: ?>
            <button type="submit" name="add" class="btn">Tambah ke Keranjang</button>
            <?php endif; ?>
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

<?php if(true): ?>
<div style="width:1100px;margin:0 auto 30px auto;background:#fff;padding:25px;border-radius:12px;box-shadow:0 3px 12px rgba(0,0,0,.1);">
    <div style="display:flex;align-items:flex-start;gap:20px;">
        <div style="min-width:48px;height:48px;border-radius:10px;background:#e3f2fd;display:flex;align-items:center;justify-content:center;font-size:24px;color:#1976d2;">🏪</div>
        <div style="flex:1;">
            <h3 style="color:#1976d2; margin-bottom:10px; font-size:18px;">Informasi Penjual</h3>
            <?php if(!empty($seller_info)): ?>
                <p style="margin:6px 0; color:#333;"><strong>Nama Toko:</strong> <?php echo htmlspecialchars($seller_info['store_name']); ?></p>
                <p style="margin:6px 0; color:#666; font-size:14px;"><?php echo htmlspecialchars($seller_info['store_address']); ?></p>
                <?php if(!empty($seller_info['store_phone'])): ?>
                <p style="margin:6px 0; color:#666; font-size:14px;"><strong>Telepon:</strong> <?php echo htmlspecialchars($seller_info['store_phone']); ?></p>
                <?php endif; ?>
                <?php if(!empty($seller_info['store_description'])): ?>
                <p style="margin:6px 0; color:#666; font-size:14px;"><?php echo htmlspecialchars($seller_info['store_description']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p style="margin:6px 0; color:#666;"><strong>Penjual:</strong> <?php echo htmlspecialchars($seller_username); ?></p>
            <?php endif; ?>
            <a href="chat.php?to=<?php echo htmlspecialchars($seller_username); ?>"
               style="display:inline-block; margin-top:10px; padding:8px 16px; background:#2196F3; color:white; text-decoration:none; border-radius:6px; font-size:14px; font-weight:500;">💬 Chat dengan Penjual</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>

</body>
</html>
