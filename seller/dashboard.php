<?php
    session_start();
if ($_SESSION['role'] != 'seller') {
    header("Location: ../auth/login.php");
    exit;
}
echo "Seller Dashboard";

?>