<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$seller_username = $_SESSION['username'];
$username_escaped = mysqli_real_escape_string($conn, $seller_username);

// Cek apakah user adalah seller
$user_sql = "SELECT role FROM users WHERE username='$username_escaped'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

if(!$user || ($user['role'] != 'seller' && $user['role'] != 'admin')) {
    header("Location: dashboard.php?error=not_seller");
    exit;
}

$success = '';
$error = '';

// Handle form submission
if(isset($_POST['save_store'])) {
    $store_name = mysqli_real_escape_string($conn, $_POST['store_name']);
    $store_address = mysqli_real_escape_string($conn, $_POST['store_address']);
    $store_phone = mysqli_real_escape_string($conn, $_POST['store_phone']);
    $store_email = mysqli_real_escape_string($conn, $_POST['store_email']);
    $store_description = mysqli_real_escape_string($conn, $_POST['store_description']);
    
    // Cek apakah store sudah ada
    $check_sql = "SELECT id FROM stores WHERE seller_username='$username_escaped'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        // Update
        $update_sql = "UPDATE stores SET 
                      store_name='$store_name',
                      store_address='$store_address',
                      store_phone='$store_phone',
                      store_email='$store_email',
                      store_description='$store_description'
                      WHERE seller_username='$username_escaped'";
        if(mysqli_query($conn, $update_sql)) {
            $success = "Info toko berhasil diupdate!";
        } else {
            $error = "Gagal update: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $insert_sql = "INSERT INTO stores (seller_username, store_name, store_address, store_phone, store_email, store_description) 
                      VALUES ('$username_escaped', '$store_name', '$store_address', '$store_phone', '$store_email', '$store_description')";
        if(mysqli_query($conn, $insert_sql)) {
            $success = "Info toko berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan: " . mysqli_error($conn);
        }
    }
}

// Ambil data store yang ada
$store_sql = "SELECT * FROM stores WHERE seller_username='$username_escaped' LIMIT 1";
$store_result = mysqli_query($conn, $store_sql);
$store = mysqli_fetch_assoc($store_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Toko - NearBest</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
body { background:#f6f6f6; }
.container { max-width:800px; margin:40px auto; padding:0 20px; }
.page-header { background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
.page-header h1 { font-size:28px; color:#333; }
.form-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,0.1); }
.form-group { margin-bottom:20px; }
.form-group label { display:block; margin-bottom:8px; color:#333; font-weight:500; }
.form-group input, .form-group textarea { width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:15px; }
.form-group textarea { min-height:100px; resize:vertical; }
.form-group input:focus, .form-group textarea:focus { outline:none; border-color:#3b2db2; box-shadow:0 0 0 3px rgba(59, 45, 178, 0.1); }
.success-msg { background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #c3e6cb; }
.error-msg { background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #f5c6cb; }
.btn { padding:12px 30px; background:#3b2db2; color:white; text-decoration:none; border-radius:8px; font-weight:500; border:none; cursor:pointer; font-size:15px; transition:.2s; }
.btn:hover { background:#2c2297; }
.btn-secondary { background:#6c757d; }
.btn-secondary:hover { background:#5a6268; }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="page-header">
        <h1>🏪 Kelola Info Toko</h1>
    </div>
    
    <?php if($success): ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-box">
        <form method="POST">
            <div class="form-group">
                <label for="store_name">Nama Toko *</label>
                <input type="text" id="store_name" name="store_name" value="<?php echo isset($store['store_name']) ? htmlspecialchars($store['store_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="store_address">Alamat Toko *</label>
                <textarea id="store_address" name="store_address" required><?php echo isset($store['store_address']) ? htmlspecialchars($store['store_address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="store_phone">Nomor Telepon</label>
                <input type="text" id="store_phone" name="store_phone" value="<?php echo isset($store['store_phone']) ? htmlspecialchars($store['store_phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="store_email">Email Toko</label>
                <input type="email" id="store_email" name="store_email" value="<?php echo isset($store['store_email']) ? htmlspecialchars($store['store_email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="store_description">Deskripsi Toko</label>
                <textarea id="store_description" name="store_description"><?php echo isset($store['store_description']) ? htmlspecialchars($store['store_description']) : ''; ?></textarea>
            </div>
            
            <button type="submit" name="save_store" class="btn">Simpan Info Toko</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

