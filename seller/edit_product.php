<?php
session_start();
include "../db.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='seller'){
    header("Location: ../auth/login.php");
    exit;
}
$username = $_SESSION['username'];
$u = mysqli_real_escape_string($conn, $username);
$user_id = 0;
$q = mysqli_query($conn, "SELECT id FROM users WHERE username='$u' LIMIT 1");
if($q && $row = mysqli_fetch_assoc($q)) { $user_id = (int)$row['id']; }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = null;
// Cek kolom stock
$has_stock = false;
$col_stock = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
if($col_stock && mysqli_num_rows($col_stock)>0){ $has_stock = true; }
if($id>0){
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$id' AND seller_id='$user_id' LIMIT 1");
    $p = mysqli_fetch_assoc($res);
}
if(!$p){ header("Location: my_products.php"); exit; }
$success = '';
$error = '';
if(isset($_POST['save'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (float)$_POST['price'];
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : (isset($p['stock']) ? (int)$p['stock'] : 0);
    if($name!=='' && $price>0){
        if($has_stock){
            $sql = "UPDATE products SET name='$name',price='$price',image='$image',description='$description',category='$category',tags='$tags',stock='$stock' WHERE id='$id' AND seller_id='$user_id'";
        } else {
            $sql = "UPDATE products SET name='$name',price='$price',image='$image',description='$description',category='$category',tags='$tags' WHERE id='$id' AND seller_id='$user_id'";
        }
        if(mysqli_query($conn,$sql)){ $success = 'Produk berhasil diupdate'; } else { $error = mysqli_error($conn); }
    } else { $error = 'Data tidak valid'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Produk</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f6f6f6}
.container{max-width:800px;margin:40px auto;padding:0 20px}
.header{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);margin-bottom:20px}
.header h1{font-size:28px;color:#333}
.form{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1)}
.group{margin-bottom:15px}
label{display:block;margin-bottom:6px;color:#333}
input,textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
.btn{padding:12px 20px;background:#3b2db2;color:#fff;text-decoration:none;border:none;border-radius:8px;cursor:pointer}
.msg{padding:12px;border-radius:8px;margin-bottom:15px}
.ok{background:#d4edda;color:#155724}
.err{background:#f8d7da;color:#721c24}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container">
    <div class="header"><h1>Edit Produk</h1></div>
    <div class="form">
        <?php if($success): ?><div class="msg ok"><?=$success?></div><?php endif; ?>
        <?php if($error): ?><div class="msg err"><?=$error?></div><?php endif; ?>
        <form method="POST">
            <div class="group"><label>Nama</label><input name="name" value="<?=htmlspecialchars($p['name'])?>" required></div>
            <div class="group"><label>Harga</label><input name="price" type="number" step="1" min="0" value="<?=htmlspecialchars($p['price'])?>" required></div>
            <div class="group"><label>Gambar (nama file di assets/images)</label><input name="image" value="<?=htmlspecialchars($p['image'])?>" required></div>
            <div class="group"><label>Deskripsi</label><textarea name="description"><?=htmlspecialchars($p['description'])?></textarea></div>
            <div class="group"><label>Stok Tersedia</label><input name="stock" type="number" step="1" min="0" value="<?=isset($p['stock'])?htmlspecialchars($p['stock']):0?>"></div>
            <div class="group"><label>Kategori</label>
                <?php $cat = isset($p['category']) ? $p['category'] : ''; ?>
                <select name="category" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px">
                    <option value="">Pilih kategori</option>
                    <option value="Foods" <?= $cat==='Foods'?'selected':'' ?>>Foods</option>
                    <option value="Drinks" <?= $cat==='Drinks'?'selected':'' ?>>Drinks</option>
                    <option value="Snacks" <?= $cat==='Snacks'?'selected':'' ?>>Snacks</option>
                    <option value="Dairy" <?= $cat==='Dairy'?'selected':'' ?>>Dairy</option>
                    <option value="Cleaning" <?= $cat==='Cleaning'?'selected':'' ?>>Cleaning</option>
                </select>
            </div>
            <div class="group"><label>Tags</label><input id="tags_input" name="tags" value="<?=htmlspecialchars($p['tags'])?>" placeholder="pisahkan dengan koma, contoh: segar, murah"></div>
            <?php 
            $tag_suggestions = [];
            $tag_rows = mysqli_query($conn, "SELECT tags FROM products WHERE tags IS NOT NULL AND tags<>''");
            if($tag_rows){
                while($tr = mysqli_fetch_assoc($tag_rows)){
                    $parts = array_map('trim', explode(',', $tr['tags']));
                    foreach($parts as $t){ if($t!==''){ $tag_suggestions[$t] = true; } }
                }
            }
            if(count($tag_suggestions)>0): ?>
            <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
                <?php foreach(array_keys($tag_suggestions) as $tg): ?>
                <button type="button" onclick="addTag('tags_input','<?=htmlspecialchars($tg)?>')" style="padding:6px 10px;border:1px solid #ddd;border-radius:16px;background:#f8f9fa;cursor:pointer;">#<?=htmlspecialchars($tg)?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <button class="btn" name="save" value="1">Simpan</button>
            <a class="btn" style="background:#6c757d" href="my_products.php">Kembali</a>
        </form>
    </div>
</div>
<script>
function addTag(inputId, tag){
  var el = document.getElementById(inputId);
  var val = (el.value||'').split(',').map(function(s){return s.trim()}).filter(Boolean);
  if(val.indexOf(tag)===-1){ val.push(tag); }
  el.value = val.join(', ');
}
</script>
<?php include "../includes/footer.php"; ?>
</body>
</html>
