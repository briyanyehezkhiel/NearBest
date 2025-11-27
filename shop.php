<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>NearBest Shop</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }

    body { background:#f6f6f6; }

    /* Navbar */
    nav {
        background: #3b2db2;
        padding: 15px 100px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        color:white;
    }
    nav .brand {
        font-size:28px;
        font-weight:bold;
    }
    nav .menu a {
        margin-left:20px;
        color:white;
        text-decoration:none;
        font-weight:500;
    }
    nav .menu a:hover { opacity:.8; }

    /* Banner */
    .banner {
        width:100%;
        height:350px;
        background:url('assets/images/banner1.jpg');
        background-size:cover;
        background-position:center;
        display:flex;
        align-items:center;
        color:white;
        padding-left:100px;
    }
    .banner h1 { font-size:48px; max-width:300px; }
    .banner p {
        font-size:20px; margin:10px 0;
        max-width:350px;
    }
    .btn-shop {
        background:white;
        color:#3b2db2;
        padding:10px 20px;
        border-radius:8px;
        text-decoration:none;
        font-weight:bold;
    }

    /* Product Section */
    .container {
        width:1100px;
        margin:50px auto;
    }
    .title-sec {
        font-size:26px;
        margin-bottom:20px;
    }
    .products {
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:25px;
    }
    .card {
        background:#fff;
        padding:15px;
        border-radius:12px;
        text-align:center;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
        transition:.3s;
    }
    .card:hover { transform:translateY(-5px); }

    .card img {
        width:100%;
        height:180px;
        object-fit:cover;
        border-radius:10px;
    }
    .price {
        margin:8px 0;
        font-size:18px;
        font-weight:bold;
        color:#3b2db2;
    }
    .btn {
        display:inline-block;
        margin-top:8px;
        padding:8px 14px;
        background:#3b2db2;
        color:white;
        text-decoration:none;
        border-radius:8px;
        transition:.2s;
    }
    .btn:hover { background:#2c2297; }

</style>
</head>
<body>

<nav>
    <div class="brand">NearBest</div>
    <div class="menu">
        <a href="shop.php">Shop</a>
        <a href="cart.php">Keranjang</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="banner">
    <div>
        <h1>50% OFF</h1>
        <p>Lorem ipsum dolor sit amet consectetur.</p>
        <a href="shop.php" class="btn-shop">Shop Now</a>
    </div>
</div>

<div class="container">
    <h2 class="title-sec">Product List</h2>

    <div class="products">
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <div class="card">
            <img src="assets/images/<?php echo $row['image']; ?>">
            <h3><?php echo $row['name']; ?></h3>
            <p class="price">Rp <?php echo number_format($row['price']); ?></p>
            <a href="product.php?id=<?php echo $row['id']; ?>" class="btn">
                Detail Produk
            </a>
        </div>
        <?php } ?>
    </div>
    <!-- Promo Section -->
<div style="margin-top:60px; padding:40px; background:#3b2db2; color:white;
           border-radius:12px; text-align:center;">
    <h2>23% off in all products</h2>
    <p>Shop Now! Promo Terbatas</p>
    <a href="shop.php"
       style="background:white;color:#3b2db2;padding:10px 20px;border-radius:8px;
              text-decoration:none;font-weight:bold;">Shop Now</a>
</div>
</div>

</body>
</html>
