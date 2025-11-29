<?php
session_start();
include "../db.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: ../auth/login.php");
    exit;
}
if(isset($_POST['set_role']) && isset($_POST['username']) && isset($_POST['role'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $r = mysqli_real_escape_string($conn, $_POST['role']);
    $allowed = ($r==='buyer' || $r==='seller');
    $cur = mysqli_query($conn, "SELECT role FROM users WHERE username='$u' LIMIT 1");
    $row = $cur ? mysqli_fetch_assoc($cur) : null;
    if($allowed && $row && strtolower($row['role'])!=='admin'){
        mysqli_query($conn, "UPDATE users SET role='$r' WHERE username='$u'");
    }
}
if(isset($_POST['delete_user']) && isset($_POST['username'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $cur = mysqli_query($conn, "SELECT role FROM users WHERE username='$u' LIMIT 1");
    $row = $cur ? mysqli_fetch_assoc($cur) : null;
    if($row && strtolower($row['role'])!=='admin'){
        mysqli_query($conn, "DELETE FROM users WHERE username='$u'");
    }
}
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$allowed_per = [5,10,20,50]; if(!in_array($per_page,$allowed_per)) $per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; if($page<1) $page=1;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$whereParts = [];
if($q!==''){ $q_escaped = mysqli_real_escape_string($conn, $q); $whereParts[] = "(username LIKE '%$q_escaped%' OR email LIKE '%$q_escaped%' OR role LIKE '%$q_escaped%')"; }
$whereSql = count($whereParts)>0 ? ('WHERE '.implode(' AND ',$whereParts)) : '';
$countRes = mysqli_query($conn, "SELECT COUNT(*) c FROM users $whereSql");
$total = 0; if($countRes){ $row=mysqli_fetch_assoc($countRes); $total=(int)$row['c']; }
$offset = ($page-1)*$per_page;
$users = mysqli_query($conn, "SELECT id,username,email,role FROM users $whereSql ORDER BY id DESC LIMIT $per_page OFFSET $offset");
$row_no_start = $offset + 1;
$total_pages = $per_page>0 ? max(1, (int)ceil($total/$per_page)) : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Users</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f6f6f6}
.container{max-width:1100px;margin:40px auto;padding:0 20px}
.header{background:#fff;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,.1);margin-bottom:20px}
.header h1{font-size:28px;color:#333}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 3px 15px rgba(0,0,0,.1)}
th{background:#3b2db2;color:#fff;text-align:left;padding:12px}
td{padding:12px;border-bottom:1px solid #eee}
.actions{display:flex;gap:8px}
.btn{padding:8px 12px;background:#3b2db2;color:#fff;text-decoration:none;border:none;border-radius:6px;cursor:pointer}
.btn-secondary{background:#6c757d}
.btn-danger{background:#dc3545}
select{padding:6px;border:1px solid #ddd;border-radius:6px}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container">
    <div class="header"><h1>Kelola Users</h1></div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:12px">
        <label style="display:flex;gap:6px;align-items:center">Per Halaman
            <select name="per_page" style="padding:6px;border:1px solid #ddd;border-radius:6px">
                <option value="5"<?= $per_page===5?' selected':'' ?>>5</option>
                <option value="10"<?= $per_page===10?' selected':'' ?>>10</option>
                <option value="20"<?= $per_page===20?' selected':'' ?>>20</option>
                <option value="50"<?= $per_page===50?' selected':'' ?>>50</option>
            </select>
        </label>
        <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari username/email/role" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1">
        <input type="hidden" name="page" value="1">
        <button class="btn" style="background:#3b2db2">Terapkan</button>
        <div style="margin-left:auto;color:#666">Total: <?= (int)$total ?> users</div>
    </form>
    <table>
        <tr><th>No</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
        <?php $i=0; while($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?= $row_no_start + $i ?></td>
            <td><?=htmlspecialchars($u['username'])?></td>
            <td><?=htmlspecialchars($u['email'])?></td>
            <td><?=htmlspecialchars($u['role'])?></td>
            <td class="actions">
                <?php if($u['role']==='admin'): ?>
                    <div style="padding:8px 12px;border:1px solid #eee;border-radius:6px;color:#666;background:#f8f9fa">admin (tidak dapat diubah)</div>
                <?php else: ?>
                <form method="POST" style="display:flex;gap:8px;align-items:center">
                    <input type="hidden" name="username" value="<?=htmlspecialchars($u['username'])?>">
                    <select name="role">
                        <option value="buyer" <?=$u['role']==='buyer'?'selected':''?>>buyer</option>
                        <option value="seller" <?=$u['role']==='seller'?'selected':''?>>seller</option>
                    </select>
                    <button class="btn" name="set_role" value="1">Simpan</button>
                </form>
                <?php endif; ?>
                <?php if($u['role']!=='admin'): ?>
                <form method="POST" onsubmit="return confirm('Hapus user?')">
                    <input type="hidden" name="username" value="<?=htmlspecialchars($u['username'])?>">
                    <button class="btn btn-danger" name="delete_user" value="1">Hapus</button>
                </form>
                <?php else: ?>
                    <div style="padding:8px 12px;border:1px solid #eee;border-radius:6px;color:#666;background:#f8f9fa">admin (tidak dapat dihapus)</div>
                <?php endif; ?>
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
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
