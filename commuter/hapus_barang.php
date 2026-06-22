<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id = (int) $_GET['id'];

$q = mysqli_query($con, "SELECT image FROM barang_temuan WHERE id_barang_temuan = $id");
$data = mysqli_fetch_assoc($q);
if($data['image'] != 'default.jpg' && file_exists('image/'.$data['image'])) {
    unlink('image/'.$data['image']);
}

mysqli_query($con, "DELETE FROM barang_temuan WHERE id_barang_temuan = $id");

header("Location: kelola_barang.php?hapus=sukses");
exit();
?>