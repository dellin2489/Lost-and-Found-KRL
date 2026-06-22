<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_petugas       = $_SESSION['id_petugas'];
    $id_laporan       = mysqli_real_escape_string($con, $_POST['id_laporan']);
    $id_barang_temuan = mysqli_real_escape_string($con, $_POST['id_barang_temuan']);
    $alasan_klaim     = mysqli_real_escape_string($con, $_POST['alasan_klaim']);
    
    // Proses Upload Bukti Penyerahan
    $bukti_penyerahan = '';
    if (isset($_FILES['bukti_penyerahan']) && $_FILES['bukti_penyerahan']['error'] == 0) {
        $ext = pathinfo($_FILES['bukti_penyerahan']['name'], PATHINFO_EXTENSION);
        $bukti_penyerahan = 'bukti_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['bukti_penyerahan']['tmp_name'], 'image/' . $bukti_penyerahan);
    }

    // Waktu saat ini
    $sekarang = date('Y-m-d H:i:s');

    // 1. Insert ke tabel klaim_penyerahan
    $q_insert = mysqli_query($con, "INSERT INTO klaim_penyerahan 
        (id_barang_temuan, id_laporan, id_petugas, tanggal_klaim, alasan_klaim, tanggal_penyerahan, status_klaim, bukti_penyerahan) 
        VALUES 
        ('$id_barang_temuan', '$id_laporan', '$id_petugas', '$sekarang', '$alasan_klaim', '$sekarang', 'Disetujui & Diserahkan', '$bukti_penyerahan')");

    if ($q_insert) {
        // 2. Update status barang_temuan menjadi 'Sudah Dikembalikan'
        mysqli_query($con, "UPDATE barang_temuan SET status = 'Sudah Dikembalikan' WHERE id_barang_temuan = '$id_barang_temuan'");
        
        // 3. Update status laporan_kehilangan menjadi 'Selesai'
        mysqli_query($con, "UPDATE laporan_kehilangan SET status = 'Selesai' WHERE id_laporan = '$id_laporan'");

        header("Location: kelola_laporan.php?update=sukses");
    } else {
        header("Location: kelola_laporan.php?update=gagal");
    }
} else {
    header("Location: kelola_laporan.php");
}
?>