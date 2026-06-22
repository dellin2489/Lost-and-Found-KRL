<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_klaim.php");
    exit();
}

$id_klaim  = (int) $_POST['id_klaim'];
$id_barang = (int) $_POST['id_barang_temuan'];
$id_laporan = (int) $_POST['id_laporan'];
$aksi      = $_POST['aksi'];

if ($aksi === 'setujui') {

    // Upload bukti penyerahan (opsional)
    $bukti = null;
    if (isset($_FILES['bukti_penyerahan']) && $_FILES['bukti_penyerahan']['error'] === 0) {
        $ext       = pathinfo($_FILES['bukti_penyerahan']['name'], PATHINFO_EXTENSION);
        $nama_file = 'serah_' . time() . '_' . $id_klaim . '.' . $ext;
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($ext), $allowed)) {
            move_uploaded_file($_FILES['bukti_penyerahan']['tmp_name'], 'image/' . $nama_file);
            $bukti = $nama_file;
        }
    }

    $bukti_val = $bukti ? "'$bukti'" : "NULL";

    // Update klaim
    mysqli_query($con,
        "UPDATE klaim_penyerahan
         SET status_klaim = 'Disetujui & Diserahkan',
             tanggal_penyerahan = NOW(),
             bukti_penyerahan = $bukti_val
         WHERE id_klaim = $id_klaim"
    );

    // Update barang temuan
    mysqli_query($con,
        "UPDATE barang_temuan SET status = 'Sudah Dikembalikan'
         WHERE id_barang_temuan = $id_barang"
    );

    // Update laporan kehilangan
    mysqli_query($con,
        "UPDATE laporan_kehilangan SET status = 'Selesai'
         WHERE id_laporan = $id_laporan"
    );

    header("Location: kelola_klaim.php?verifikasi=disetujui");

} elseif ($aksi === 'tolak') {

    // Update klaim
    mysqli_query($con,
        "UPDATE klaim_penyerahan SET status_klaim = 'Ditolak'
         WHERE id_klaim = $id_klaim"
    );

    // Kembalikan barang temuan ke Tersedia
    mysqli_query($con,
        "UPDATE barang_temuan SET status = 'Tersedia'
         WHERE id_barang_temuan = $id_barang"
    );

    // Kembalikan laporan ke Mencari
    mysqli_query($con,
        "UPDATE laporan_kehilangan SET status = 'Mencari'
         WHERE id_laporan = $id_laporan"
    );

    header("Location: kelola_klaim.php?verifikasi=ditolak");

} else {
    header("Location: kelola_klaim.php");
}

exit();