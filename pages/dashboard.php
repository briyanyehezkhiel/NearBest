<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - NearBest</title>
<style>
    body {
        font-family: Arial;
        background-color: #f6f6f6;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 400px;
        margin: 100px auto;
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    a, button {
        display: block;
        margin-top: 15px;
        text-decoration: none;
        background: #3b2db2;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        transition: .2s;
    }
    a:hover, button:hover {
        opacity: 0.9;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Selamat Datang 👋</h2>
    <p><?php echo $_SESSION['username']; ?></p>

    <a href="shop.php">Belanja Sekarang</a>
    <a href="#">Profil</a>
    <a href="cart.php">Keranjang</a>

    <form action="logout.php" method="POST">
        <button type="submit">Logout</button>
    </form>
</div>

</body>
</html>
