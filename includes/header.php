<?php
if(!isset($_SESSION)) { session_start(); }
?>
<nav style="
    background:#3b2db2;
    padding: 15px 100px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:white;">
    
    <div style="font-size:28px; font-weight:bold;">NearBest</div>

    <div>
        <a href="../pages/shop.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Shop</a>
        <a href="../pages/cart.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Keranjang</a>
        <a href="../logout.php" style="margin-left:20px;color:white;text-decoration:none;font-weight:500;">Logout</a>
    </div>
</nav>
