<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id = (int) $_GET['id'];

mysqli_query($con, "DELETE FROM laporan_kehilangan WHERE id_laporan = $id");

header("Location: kelola_laporan.php?hapus=sukses");
exit();