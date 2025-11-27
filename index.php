<?php
session_start();
include "db.php";

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['username'])) {
    header("Location: pages/dashboard.php");
    exit;
}

// Ambil beberapa produk terbaru untuk preview (tanpa perlu login)
$products_sql = "SELECT * FROM products ORDER BY id DESC LIMIT 6";
$products_result = mysqli_query($conn, $products_sql);

// Hitung statistik (jika tabel ada)
$stats = [
    'products' => 0,
    'sellers' => 0,
    'users' => 0
];

$products_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if($products_count) {
    $row = mysqli_fetch_assoc($products_count);
    $stats['products'] = $row['count'];
}

$sellers_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='seller'");
if($sellers_count) {
    $row = mysqli_fetch_assoc($sellers_count);
    $stats['sellers'] = $row['count'];
}

$users_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if($users_count) {
    $row = mysqli_fetch_assoc($users_count);
    $stats['users'] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NearBest - Belanja Online Terpercaya</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { 
            background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height:100vh;
        }
        
        .header-nav {
            background:rgba(255,255,255,0.1);
            backdrop-filter:blur(10px);
            padding:20px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        
        .header-nav .logo {
            font-size:32px;
            font-weight:bold;
            color:white;
            text-decoration:none;
        }
        
        .header-nav .auth-links {
            display:flex;
            gap:15px;
        }
        
        .header-nav .auth-links a {
            color:white;
            text-decoration:none;
            padding:8px 20px;
            border:2px solid white;
            border-radius:20px;
            transition:.3s;
        }
        
        .header-nav .auth-links a:hover {
            background:white;
            color:#667eea;
        }
        
        .landing-container {
            max-width:1200px;
            width:100%;
            margin:0 auto;
            padding:60px 20px;
            text-align:center;
            color:white;
        }
        
        .hero-logo {
            font-size:72px;
            font-weight:bold;
            margin-bottom:20px;
            text-shadow:2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .tagline {
            font-size:28px;
            margin-bottom:60px;
            opacity:0.95;
        }
        
        .stats-section {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
            gap:20px;
            margin:40px 0 60px;
        }
        
        .stat-card {
            background:rgba(255,255,255,0.15);
            backdrop-filter:blur(10px);
            padding:25px;
            border-radius:15px;
            border:1px solid rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size:36px;
            font-weight:bold;
            margin-bottom:5px;
        }
        
        .stat-label {
            font-size:14px;
            opacity:0.9;
        }
        
        .features {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
            gap:30px;
            margin:60px 0;
        }
        
        .feature-card {
            background:rgba(255,255,255,0.1);
            backdrop-filter:blur(10px);
            padding:30px;
            border-radius:15px;
            border:1px solid rgba(255,255,255,0.2);
            transition:.3s;
        }
        
        .feature-card:hover {
            transform:translateY(-10px);
            background:rgba(255,255,255,0.15);
        }
        
        .feature-icon {
            font-size:48px;
            margin-bottom:15px;
        }
        
        .feature-card h3 {
            font-size:20px;
            margin-bottom:10px;
        }
        
        .feature-card p {
            font-size:14px;
            opacity:0.9;
        }
        
        .products-preview {
            background:rgba(255,255,255,0.95);
            border-radius:20px;
            padding:40px;
            margin:60px 0;
            color:#333;
        }
        
        .products-preview h2 {
            font-size:32px;
            margin-bottom:30px;
            color:#3b2db2;
        }
        
        .products-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
            gap:20px;
            margin-bottom:30px;
        }
        
        .product-card {
            background:#fff;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 3px 10px rgba(0,0,0,0.1);
            transition:.3s;
            cursor:pointer;
        }
        
        .product-card:hover {
            transform:translateY(-5px);
            box-shadow:0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            width:100%;
            height:180px;
            object-fit:cover;
        }
        
        .product-card-content {
            padding:15px;
        }
        
        .product-card h4 {
            font-size:16px;
            margin-bottom:8px;
            color:#333;
        }
        
        .product-card .price {
            color:#3b2db2;
            font-weight:bold;
            font-size:18px;
        }
        
        .cta-buttons {
            display:flex;
            gap:20px;
            justify-content:center;
            flex-wrap:wrap;
            margin-top:50px;
        }
        
        .btn {
            padding:15px 40px;
            font-size:18px;
            font-weight:600;
            border-radius:50px;
            text-decoration:none;
            transition:.3s;
            border:2px solid white;
            display:inline-block;
        }
        
        .btn-primary {
            background:white;
            color:#667eea;
        }
        
        .btn-primary:hover {
            background:transparent;
            color:white;
            transform:scale(1.05);
        }
        
        .btn-outline {
            background:transparent;
            color:white;
        }
        
        .btn-outline:hover {
            background:white;
            color:#667eea;
            transform:scale(1.05);
        }
        
        .btn-shop {
            background:#3b2db2;
            color:white;
            border:none;
        }
        
        .btn-shop:hover {
            background:#2c2297;
            transform:scale(1.05);
        }
        
        .info-section {
            background:rgba(255,255,255,0.1);
            backdrop-filter:blur(10px);
            padding:40px;
            border-radius:20px;
            margin:40px 0;
            text-align:left;
        }
        
        .info-section h3 {
            font-size:24px;
            margin-bottom:20px;
        }
        
        .info-section p {
            line-height:1.8;
            opacity:0.95;
            margin-bottom:15px;
        }
        
        @media (max-width: 768px) {
            .hero-logo { font-size:48px; }
            .tagline { font-size:20px; }
            .features { grid-template-columns:1fr; }
            .products-grid { grid-template-columns:repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <nav class="header-nav">
        <a href="index.php" class="logo">NearBest</a>
        <div class="auth-links">
            <a href="auth/login.php">Masuk</a>
            <a href="auth/register.php">Daftar</a>
        </div>
    </nav>
    
    <div class="landing-container">
        <div class="hero-logo">NearBest</div>
        <p class="tagline">Temukan Produk Terbaik dengan Harga Terjangkau</p>
        
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['products']; ?>+</div>
                <div class="stat-label">Produk</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['sellers']; ?>+</div>
                <div class="stat-label">Penjual</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['users']; ?>+</div>
                <div class="stat-label">Pengguna</div>
            </div>
        </div>
        
        <?php if(mysqli_num_rows($products_result) > 0): ?>
        <div class="products-preview">
            <h2>🛍️ Produk Terbaru</h2>
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                <div class="product-card" onclick="window.location.href='auth/login.php'">
                    <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-card-content">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <a href="auth/login.php" class="btn btn-shop">Lihat Semua Produk →</a>
        </div>
        <?php endif; ?>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🛍️</div>
                <h3>Belanja Mudah</h3>
                <p>Berbelanja dengan mudah dan nyaman dari mana saja</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Chat Langsung</h3>
                <p>Komunikasi langsung dengan penjual untuk tanya jawab</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🤝</div>
                <h3>Transaksi Aman</h3>
                <p>Transaksi dilakukan langsung antara pembeli dan penjual</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3>Kualitas Terjamin</h3>
                <p>Produk berkualitas dari penjual terpercaya</p>
            </div>
        </div>
        
        <div class="info-section">
            <h3>📌 Tentang NearBest</h3>
            <p>NearBest adalah platform marketplace yang menghubungkan pembeli dan penjual secara langsung. Kami menyediakan platform untuk memudahkan transaksi jual beli dengan sistem komunikasi yang terintegrasi.</p>
            <p><strong>Fitur Utama:</strong></p>
            <ul style="margin-left:20px; line-height:2;">
                <li>🛍️ Belanja produk dari berbagai penjual</li>
                <li>💬 Chat langsung dengan penjual</li>
                <li>📦 Sistem order yang terintegrasi</li>
                <li>🔔 Notifikasi real-time</li>
                <li>👤 Profile dan manajemen toko untuk penjual</li>
            </ul>
        </div>
        
        <div class="cta-buttons">
            <a href="auth/login.php" class="btn btn-primary">Masuk ke Akun</a>
            <a href="auth/register.php" class="btn btn-outline">Daftar Sekarang</a>
        </div>
    </div>
</body>
</html>
