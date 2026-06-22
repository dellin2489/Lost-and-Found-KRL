<?php
session_start();

if (isset($_SESSION['id_pelapor'])) {
    header("Location: pelapor.php");
    exit();
}

if (isset($_SESSION['id_petugas'])) {
    header("Location: petugas.php");
    exit();
}

// Kalau belum login, redirect ke login
header("Location: login.php");
exit();
?>