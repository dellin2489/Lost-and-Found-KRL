<?php
session_start();
include 'connection.php';


if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: barang_temuan.php");
    exit();
}

$id_pelapor = (int) $_SESSION['id_pelapor'];
$id_barang  = (int) $_POST['id_barang_temuan'];
$id_laporan = (int) $_POST['id_laporan'];
$alasan     = mysqli_real_escape_string($con, trim($_POST['alasan_klaim']));
$from       = $_POST['from'] ?? 'barang_temuan';
$redirect   = $from === 'pelapor' ? 'pelapor.php' : 'barang_temuan.php';


$cek_laporan = mysqli_query($con,
    "SELECT id_laporan FROM laporan_kehilangan
     WHERE id_laporan = $id_laporan AND id_pelapor = $id_pelapor"
);
if (mysqli_num_rows($cek_laporan) === 0) {
    header("Location: $redirect?klaim=gagal");
    exit();
}


$cek_barang = mysqli_query($con,
    "SELECT status, id_petugas FROM barang_temuan
     WHERE id_barang_temuan = $id_barang"
);
if (mysqli_num_rows($cek_barang) === 0) {
    header("Location: $redirect?klaim=gagal");
    exit();
}
$barang = mysqli_fetch_assoc($cek_barang);
if ($barang['status'] !== 'Tersedia') {
    header("Location: $redirect?klaim=gagal");
    exit();
}

$id_petugas = (int) $barang['id_petugas'];


if (!isset($_FILES['foto_bukti']) || $_FILES['foto_bukti']['error'] !== 0) {
    header("Location: $redirect?klaim=gagal");
    exit();
}

// Upload foto bukti
$ext     = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowed)) {
    header("Location: $redirect?klaim=gagal");
    exit();
}

$nama_file  = 'klaim_' . time() . '_' . $id_pelapor . '.' . $ext;
$upload_dir = 'image/';

if (!move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $upload_dir . $nama_file)) {
    header("Location: $redirect?klaim=gagal");
    exit();
}


$foto_bukti_sql = "'" . mysqli_real_escape_string($con, $nama_file) . "'";

mysqli_begin_transaction($con);

try {
    
    $insert = mysqli_query($con,
        "INSERT INTO klaim_penyerahan
            (id_barang_temuan, id_laporan, id_petugas, alasan_klaim, foto_bukti, status_klaim)
         VALUES
            ($id_barang, $id_laporan, $id_petugas, '$alasan', $foto_bukti_sql, 'Menunggu Verifikasi')"
    );

    if (!$insert) {
        throw new Exception("Insert klaim gagal: " . mysqli_error($con));
    }

    // Update status barang temuan jadi Sedang Diklaim
    $upd_barang = mysqli_query($con,
        "UPDATE barang_temuan SET status = 'Sedang Diklaim'
         WHERE id_barang_temuan = $id_barang"
    );

    if (!$upd_barang) {
        throw new Exception("Update barang gagal: " . mysqli_error($con));
    }

    // Update status laporan jadi Ditemukan
    $upd_laporan = mysqli_query($con,
        "UPDATE laporan_kehilangan SET status = 'Ditemukan'
         WHERE id_laporan = $id_laporan"
    );

    if (!$upd_laporan) {
        throw new Exception("Update laporan gagal: " . mysqli_error($con));
    }

    
    mysqli_commit($con);
    header("Location: $redirect?klaim=sukses");

} catch (Exception $e) {
    
    mysqli_rollback($con);

    
    if (file_exists($upload_dir . $nama_file)) {
        unlink($upload_dir . $nama_file);
    }

    header("Location: $redirect?klaim=gagal");
}

exit();