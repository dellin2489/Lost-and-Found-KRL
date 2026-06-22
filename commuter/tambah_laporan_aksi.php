<?php
session_start();
include 'connection.php';

// Pastikan hanya petugas yang bisa akses
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_petugas   = $_SESSION['id_petugas'];
    $nama_barang  = mysqli_real_escape_string($con, trim($_POST['nama_barang']));
    $kategori     = mysqli_real_escape_string($con, trim($_POST['kategori']));
    $deskripsi    = mysqli_real_escape_string($con, trim($_POST['deskripsi']));
    $tanggal      = mysqli_real_escape_string($con, trim($_POST['tanggal_hilang']));
    $waktu        = mysqli_real_escape_string($con, trim($_POST['waktu_hilang']));
    $lokasi       = mysqli_real_escape_string($con, trim($_POST['lokasi_terakhir']));
    
    
    $status = 'Mencari'; 

    // Proses Upload Foto (Opsional)
    $foto_referensi = NULL;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto_nama = time() . '_' . basename($_FILES['foto']['name']);
        $foto_tmp  = $_FILES['foto']['tmp_name'];
        $folder    = 'image/' . $foto_nama;

        // Pastikan folder 'image/' sudah ada
        if (move_uploaded_file($foto_tmp, $folder)) {
            $foto_referensi = $foto_nama;
        }
    }

    
    if ($foto_referensi) {
        $query = "INSERT INTO laporan_kehilangan 
                  (id_pelapor, id_petugas, nama_barang, kategori, deskripsi_ciri_ciri, tanggal_hilang, waktu_hilang, lokasi_terakhir, foto_referensi, status) 
                  VALUES 
                  (NULL, '$id_petugas', '$nama_barang', '$kategori', '$deskripsi', '$tanggal', '$waktu', '$lokasi', '$foto_referensi', '$status')";
    } else {
        $query = "INSERT INTO laporan_kehilangan 
                  (id_pelapor, id_petugas, nama_barang, kategori, deskripsi_ciri_ciri, tanggal_hilang, waktu_hilang, lokasi_terakhir, foto_referensi, status) 
                  VALUES 
                  (NULL, '$id_petugas', '$nama_barang', '$kategori', '$deskripsi', '$tanggal', '$waktu', '$lokasi', NULL, '$status')";
    }

    // Eksekusi Query
    if (mysqli_query($con, $query)) {
        header("Location: kelola_laporan.php?tambah=sukses");
    } else {
        
        header("Location: kelola_laporan.php?tambah=gagal");
    }
} else {
    header("Location: kelola_laporan.php");
}
?>