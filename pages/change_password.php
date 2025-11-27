<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

if(isset($_POST['change_password'])) {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if(empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok!";
    } elseif(strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } else {
        // Cek password lama
        $username_escaped = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT * FROM users WHERE username='$username_escaped' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        
        if(!$result) {
            $error = "Error: " . mysqli_error($conn);
        } else {
            $user = mysqli_fetch_assoc($result);
            
            if(!is_array($user) || !isset($user['password'])) {
                $error = "User tidak ditemukan!";
            } elseif(password_verify($old_password, $user['password'])) {
            // Update password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password='$hashed_password' WHERE username='$username'";
            
            if(mysqli_query($conn, $update_sql)) {
                header("Location: profile.php?success=password");
                exit;
            } else {
                $error = "Gagal mengubah password: " . mysqli_error($conn);
            }
            } else {
                $error = "Password lama salah!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ganti Password - NearBest</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { background:#f6f6f6; }
    
    .container {
        max-width:500px;
        margin:40px auto;
        background:#fff;
        padding:40px;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
    }
    
    h2 {
        font-size:28px;
        color:#333;
        margin-bottom:30px;
        text-align:center;
    }
    
    .form-group {
        margin-bottom:20px;
    }
    
    label {
        display:block;
        margin-bottom:8px;
        color:#666;
        font-weight:500;
        font-size:14px;
    }
    
    input[type="password"] {
        width:100%;
        padding:12px;
        border:1px solid #ddd;
        border-radius:8px;
        font-size:15px;
        transition:.2s;
    }
    
    input[type="password"]:focus {
        outline:none;
        border-color:#3b2db2;
        box-shadow:0 0 0 3px rgba(59, 45, 178, 0.1);
    }
    
    .error-msg {
        background:#f8d7da;
        color:#721c24;
        padding:12px;
        border-radius:8px;
        margin-bottom:20px;
        border:1px solid #f5c6cb;
        font-size:14px;
    }
    
    .btn {
        width:100%;
        padding:12px;
        background:#3b2db2;
        color:white;
        border:none;
        border-radius:8px;
        font-size:16px;
        font-weight:500;
        cursor:pointer;
        transition:.2s;
        margin-top:10px;
    }
    
    .btn:hover {
        background:#2c2297;
    }
    
    .btn-secondary {
        background:#6c757d;
        text-decoration:none;
        display:block;
        text-align:center;
        margin-top:15px;
    }
    
    .btn-secondary:hover {
        background:#5a6268;
    }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container">
    <h2>Ganti Password</h2>
    
    <?php if($error) { ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php } ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="old_password">Password Lama</label>
            <input type="password" id="old_password" name="old_password" required>
        </div>
        
        <div class="form-group">
            <label for="new_password">Password Baru</label>
            <input type="password" id="new_password" name="new_password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password Baru</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        
        <button type="submit" name="change_password" class="btn">Ubah Password</button>
        <a href="profile.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>

