<?php
session_start();
include "../db.php";
if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','seller'])){
    header("Location: ../auth/login.php");
    exit;
}
if(isset($_POST['delete_product']) && isset($_POST['id'])){
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
}
$has_stock=false; $col_stock=mysqli_query($conn,"SHOW COLUMNS FROM products LIKE 'stock'"); if($col_stock&&mysqli_num_rows($col_stock)>0){$has_stock=true;}
if(isset($_POST['update_product'])){
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (int)$_POST['price'];
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';
    $tags = isset($_POST['tags']) ? mysqli_real_escape_string($conn, $_POST['tags']) : '';
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $expired_date = mysqli_real_escape_string($conn, $_POST['expired_date']);
    if($has_stock){
        mysqli_query($conn, "UPDATE products SET name='$name', price='$price', image='$image', category='$category', tags='$tags', stock='$stock', expired_date='$expired_date'WHERE id='$id'");
    } else {
        mysqli_query($conn, "UPDATE products SET name='$name', price='$price', image='$image', category='$category', tags='$tags', expired_date='$expired_date'WHERE id='$id'");
    }
}
if(isset($_POST['create_product'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (int)$_POST['price'];
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $expired_date = mysqli_real_escape_string($conn, $_POST['expired_date']);
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $uid_res = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' LIMIT 1");
    $uid = 0; if($uid_res && $r=mysqli_fetch_assoc($uid_res)){ $uid=(int)$r['id']; }
    if($name!=='' && $price>0){
        if($has_stock){
           mysqli_query($conn, "INSERT INTO products (name,price,image,description,category,tags,stock,expired_date,seller_id) VALUES ('$name','$price','$image','$description','$category','$tags','$stock','$expired_date','$uid')");
        } else {
            mysqli_query($conn, "INSERT INTO products (name,price,image,description,category,tags,stock,expired_date,seller_id) VALUES ('$name','$price','$image','$description','$category','$tags','$stock','$expired_date','$uid')");

        }
    }
}
// Filter produk: admin melihat semua, seller melihat miliknya
$select = $has_stock? "p.id,p.name,p.price,p.image,p.stock,p.category,p.tags,p.expired_date,u.username as seller": "p.id,p.name,p.price,p.image,p.category,p.tags,p.expired_date,u.username as seller";
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$allowed_per = [5,10,20,50]; if(!in_array($per_page,$allowed_per)) $per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; if($page<1) $page=1;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$whereParts = [];
if($_SESSION['role']==='seller'){
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $uid_res = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' LIMIT 1");
    $uid = 0; if($uid_res && $r=mysqli_fetch_assoc($uid_res)){ $uid=(int)$r['id']; }
    $whereParts[] = "p.seller_id='$uid'";
}
if($q!==''){ $q_escaped = mysqli_real_escape_string($conn, $q); $whereParts[] = "(p.name LIKE '%$q_escaped%' OR p.tags LIKE '%$q_escaped%' OR p.category LIKE '%$q_escaped%')"; }
$whereSql = count($whereParts)>0 ? ('WHERE '.implode(' AND ',$whereParts)) : '';
$countRes = mysqli_query($conn, "SELECT COUNT(*) c FROM products p LEFT JOIN users u ON p.seller_id=u.id $whereSql");
$total = 0; if($countRes){ $row=mysqli_fetch_assoc($countRes); $total=(int)$row['c']; }
$offset = ($page-1)*$per_page;
$products = mysqli_query($conn, "SELECT $select FROM products p LEFT JOIN users u ON p.seller_id=u.id $whereSql ORDER BY p.id DESC LIMIT $per_page OFFSET $offset");
$row_no_start = $offset + 1;
$total_pages = $per_page>0 ? max(1, (int)ceil($total/$per_page)) : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Products</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f6f6f6}
.container{max-width:1100px;margin:40px auto;padding:0 20px}
.header{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);margin-bottom:20px}
.header h1{font-size:28px;color:#333}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 3px 15px rgba(0,0,0,.1)}
th{background:#3b2db2;color:#fff;text-align:left;padding:12px}
td{padding:12px;border-bottom:1px solid #eee}
.btn{padding:8px 12px;background:#dc3545;color:#fff;text-decoration:none;border:none;border-radius:6px;cursor:pointer}
img{width:60px;height:60px;object-fit:cover;border-radius:8px}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container">
    <div class="header"><h1>Kelola Products</h1></div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:12px">
        <label style="display:flex;gap:6px;align-items:center">Per Halaman
            <select name="per_page" style="padding:6px;border:1px solid #ddd;border-radius:6px">
                <option value="5"<?= $per_page===5?' selected':'' ?>>5</option>
                <option value="10"<?= $per_page===10?' selected':'' ?>>10</option>
                <option value="20"<?= $per_page===20?' selected':'' ?>>20</option>
                <option value="50"<?= $per_page===50?' selected':'' ?>>50</option>
            </select>
        </label>
        <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari nama/tag/kategori" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1">
        <input type="hidden" name="page" value="1">
        <button class="btn" style="background:#3b2db2">Terapkan</button>
        <div style="margin-left:auto;color:#666">Total: <?= (int)$total ?> produk</div>
    </form>
    <?php 
    $tag_suggestions = [];
    $tag_rows = mysqli_query($conn, "SELECT tags FROM products WHERE tags IS NOT NULL AND tags<>''");
    if($tag_rows){
        while($tr = mysqli_fetch_assoc($tag_rows)){
            $parts = array_map('trim', explode(',', $tr['tags']));
            foreach($parts as $t){ if($t!==''){ $tag_suggestions[$t] = true; } }
        }
    }
    ?>
    <table>
        <tr><th>No</th><th>Gambar</th><th>Nama</th><th>Harga</th><th>Stok</th><th>Kedaluwarsa</th><th>Seller</th><th>Aksi</th></tr>
        <?php $i=0; while($p = mysqli_fetch_assoc($products)): ?>
        <tr>
            <td><?= $row_no_start + $i ?></td>
            <td><img src="../assets/images/<?=htmlspecialchars($p['image'])?>"></td>
            <td><?=htmlspecialchars($p['name'])?></td>
            <td>Rp <?=number_format($p['price'])?></td>
            <td><?=isset($p['stock']) ? (int)$p['stock'] : 0?></td>
            <td>
            <?php
            $today = date('Y-m-d');
            $exp = $p['expired_date'];
            if (!$exp) {
                echo "<span style='color:#666'>-</span>";
            } else if ($exp < $today) {
                echo "<span style='color:red;font-weight:bold'>Expired ($exp)</span>";
            } else if ($exp === $today) {
                echo "<span style='color:orange;font-weight:bold'>Expired Hari Ini</span>";
            } else if ($exp <= date('Y-m-d', strtotime('+7 days'))) {
                echo "<span style='color:blue'>Hampir Expired ($exp)</span>";
            } else {
                echo "<span style='color:green'>$exp</span>";
            }
            ?>
        </td>
            <td><?=htmlspecialchars($p['seller']?:'admin')?></td>
            <td>
                <button class="btn" style="background:#3b2db2;margin-bottom:8px" 
                    data-id="<?=htmlspecialchars($p['id'])?>"
                    data-name="<?=htmlspecialchars($p['name'])?>"
                    data-price="<?=htmlspecialchars($p['price'])?>"
                    data-image="<?=htmlspecialchars($p['image'])?>"
                    data-category="<?=htmlspecialchars($p['category']??'')?>"
                    data-tags="<?=htmlspecialchars($p['tags']??'')?>"
                    data-expired_date="<?=htmlspecialchars($p['expired_date'])?>"
                    data-stock="<?=isset($p['stock'])?(int)$p['stock']:0?>"
                    onclick="openEditModal(this)">Edit</button>
                <form method="POST" onsubmit="return confirm('Hapus produk?')">
                    <input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">
                    <button class="btn" name="delete_product" value="1">Hapus</button>
                </form>
            </td>
        </tr>
        <?php $i++; ?>
        <?php endwhile; ?>
    </table>

    <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:10px">
        <?php if($page>1): ?>
            <a class="btn" style="background:#6c757d" href="?page=<?= $page-1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q) ?>">Prev</a>
        <?php endif; ?>
        <div style="color:#666">Halaman <?= (int)$page ?> / <?= (int)$total_pages ?></div>
        <?php if($page < $total_pages): ?>
            <a class="btn" style="background:#6c757d" href="?page=<?= $page+1 ?>&per_page=<?= (int)$per_page ?>&q=<?= urlencode($q) ?>">Next</a>
        <?php endif; ?>
    </div>

    <div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);align-items:center;justify-content:center;">
        <div style="background:#fff;width:520px;max-width:90%;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.2);padding:20px;">
            <h3 style="margin-bottom:12px;color:#333;">Edit Produk</h3>
            <form method="POST" id="editForm" style="display:flex;flex-direction:column;gap:10px">
                <input type="hidden" name="id" id="f_id">
                <label>Nama <input name="name" id="f_name" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></label>
                <label>Harga <input type="number" min="0" name="price" id="f_price" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></label>
                <label>Gambar (filename) <input name="image" id="f_image" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></label>
                <label>Kategori 
                    <select name="category" id="f_category" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%">
                        <option value="">Pilih kategori</option>
                        <option value="Foods">Foods</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Snacks">Snacks</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Cleaning">Cleaning</option>
                    </select>
                </label>
                <label>Tags <input name="tags" id="f_tags" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" placeholder="pisahkan dengan koma, contoh: segar, murah"></label>
                <?php if(count($tag_suggestions)>0): ?>
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
                    <?php foreach(array_keys($tag_suggestions) as $tg): ?>
                    <button type="button" onclick="addTag('f_tags','<?=htmlspecialchars($tg)?>')" style="padding:6px 10px;border:1px solid #ddd;border-radius:16px;background:#f8f9fa;cursor:pointer;">#<?=htmlspecialchars($tg)?></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if($has_stock): ?>
                <label>Stok <input type="number" min="0" name="stock" id="f_stock" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></label>
                <?php endif; ?>
                <label>Tanggal Kedaluwarsa<input type="date" name="expired_date" id="f_expired_date" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></label>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px">
                    <button type="button" class="btn" style="background:#6c757d" onclick="closeEditModal()">Batal</button>
                    <button class="btn" name="update_product" value="1" style="background:#3b2db2">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(btn){
        document.getElementById('modal').style.display='flex';
        document.getElementById('f_id').value=btn.dataset.id;
        document.getElementById('f_name').value=btn.dataset.name;
        document.getElementById('f_price').value=btn.dataset.price;
        document.getElementById('f_expired_date').value = btn.dataset.expired_date || '';
        document.getElementById('f_image').value=btn.dataset.image;
        document.getElementById('f_category').value=btn.dataset.category||'';
        var t = document.getElementById('f_tags'); if(t){ t.value = btn.dataset.tags||''; }
        var stockInput=document.getElementById('f_stock');
        if(stockInput){ stockInput.value=btn.dataset.stock; }
    }
    function closeEditModal(){
        document.getElementById('modal').style.display='none';
    }
    </script>
    <script>
    function addTag(inputId, tag){
      var el = document.getElementById(inputId);
      var val = (el.value||'').split(',').map(function(s){return s.trim()}).filter(Boolean);
      if(val.indexOf(tag)===-1){ val.push(tag); }
      el.value = val.join(', ');
    }
    </script>

    <?php 
    // Modal tambah produk
    $has_stock = $has_stock; // reuse flag
    ?>
    <div style="margin-top:20px">
        <button class="btn" style="background:#3b2db2" onclick="document.getElementById('modalCreate').style.display='flex'">Tambah Produk</button>
    </div>
    <div id="modalCreate" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);align-items:center;justify-content:center;">
        <div style="background:#fff;width:520px;max-width:90%;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.2);padding:20px;">
            <h3 style="margin-bottom:12px;color:#333;">Tambah Produk</h3>
            <form method="POST" style="display:flex;flex-direction:column;gap:10px">
                <label>Nama <input name="name" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" required></label>
                <label>Harga <input type="number" min="0" name="price" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" required></label>
                <label>Gambar (filename) <input name="image" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" required></label>
                <label>Deskripsi <textarea name="description" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%"></textarea></label>
                <label>Kategori 
                    <select name="category" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%">
                        <option value="">Pilih kategori</option>
                        <option value="Foods">Foods</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Snacks">Snacks</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Cleaning">Cleaning</option>
                    </select>
                </label>
                <label>Tags <input id="create_tags" name="tags" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" placeholder="pisahkan dengan koma, contoh: segar, murah"></label>
                <?php if(count($tag_suggestions)>0): ?>
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
                    <?php foreach(array_keys($tag_suggestions) as $tg): ?>
                    <button type="button" onclick="addTag('create_tags','<?=htmlspecialchars($tg)?>')" style="padding:6px 10px;border:1px solid #ddd;border-radius:16px;background:#f8f9fa;cursor:pointer;">#<?=htmlspecialchars($tg)?></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if($has_stock): ?>
                <label>Stok <input type="number" min="0" name="stock" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%" value="0"></label>
                <?php endif; ?>
                <label>Tanggal Kedaluwarsa
                <input type="date" name="expired_date" 
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%">
                </label>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px">
                    <button type="button" class="btn" style="background:#6c757d" onclick="document.getElementById('modalCreate').style.display='none'">Batal</button>
                    <button class="btn" name="create_product" value="1" style="background:#3b2db2">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
