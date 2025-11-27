<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data user dari database
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
if(empty($username)) {
    header("Location: ../auth/login.php");
    exit;
}

// Pastikan koneksi database ada
if(!isset($conn) || !$conn) {
    die("Database connection error");
}

$username_escaped = mysqli_real_escape_string($conn, $username);
$sql = "SELECT * FROM users WHERE username='$username_escaped' LIMIT 1";
$result = mysqli_query($conn, $sql);

// Cek apakah query berhasil
if(!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

// Debug: Pastikan $user adalah array
if(!is_array($user)) {
    if(mysqli_num_rows($result) == 0) {
        session_destroy();
        header("Location: ../auth/login.php?error=user_not_found");
        exit;
    }
    die("Error: User data is not an array. Please check database connection.");
}

// Jika user tidak ditemukan atau username kosong
if(empty($user) || !isset($user['username']) || empty($user['username'])) {
    session_destroy();
    header("Location: ../auth/login.php?error=invalid_user");
    exit;
}

// Simpan data ke variabel untuk menghindari akses array berulang
$user_username = (string)$user['username'];
$user_email = isset($user['email']) ? (string)$user['email'] : '';
$role = isset($user['role']) && !empty($user['role']) ? (string)$user['role'] : 'User';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profile - NearBest</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { background:#f6f6f6; }
    
    .container {
        max-width:800px;
        margin:40px auto;
        background:#fff;
        padding:40px;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        text-align:center;
        margin-bottom:40px;
        padding-bottom:30px;
        border-bottom:2px solid #f0f0f0;
    }
    
    .profile-avatar {
        width:120px;
        height:120px;
        background:#3b2db2;
        border-radius:50%;
        margin:0 auto 20px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:48px;
        color:white;
        font-weight:bold;
    }
    
    .profile-header h1 {
        font-size:32px;
        color:#333;
        margin-bottom:10px;
    }
    
    .profile-header .role-badge {
        display:inline-block;
        background:#3b2db2;
        color:white;
        padding:6px 16px;
        border-radius:20px;
        font-size:14px;
        font-weight:500;
    }
    
    .profile-info {
        margin-bottom:30px;
    }
    
    .info-item {
        display:flex;
        padding:20px;
        margin-bottom:15px;
        background:#f9f9f9;
        border-radius:10px;
        border-left:4px solid #3b2db2;
    }
    
    .info-label {
        font-weight:bold;
        color:#666;
        min-width:150px;
        font-size:15px;
    }
    
    .info-value {
        color:#333;
        font-size:15px;
    }
    
    .btn-group {
        display:flex;
        gap:15px;
        margin-top:30px;
    }
    
    .btn {
        flex:1;
        padding:12px 20px;
        background:#3b2db2;
        color:white;
        text-decoration:none;
        border-radius:8px;
        text-align:center;
        font-weight:500;
        transition:.2s;
        border:none;
        cursor:pointer;
        font-size:15px;
    }
    
    .btn:hover {
        background:#2c2297;
    }
    
    .btn-secondary {
        background:#6c757d;
    }
    
    .btn-secondary:hover {
        background:#5a6268;
    }
    
    .success-msg {
        background:#d4edda;
        color:#155724;
        padding:12px;
        border-radius:8px;
        margin-bottom:20px;
        border:1px solid #c3e6cb;
    }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user_username, 0, 1)); ?>
        </div>
        <h1><?php echo htmlspecialchars($user_username); ?></h1>
        <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
    </div>
    
    <?php if(isset($_GET['success']) && $_GET['success'] == 'password') { ?>
        <div class="success-msg">
            Password berhasil diubah!
        </div>
    <?php } ?>
    
    <div class="profile-info">
        <div class="info-item">
            <div class="info-label">Username:</div>
            <div class="info-value"><?php echo htmlspecialchars($user_username); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Email:</div>
            <div class="info-value"><?php echo htmlspecialchars($user_email); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Role:</div>
            <div class="info-value"><?php echo htmlspecialchars($role); ?></div>
        </div>
    </div>
    
    <div class="btn-group">
        <a href="change_password.php" class="btn">Ganti Password</a>
        <a href="shop.php" class="btn btn-secondary">Kembali ke Shop</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>

