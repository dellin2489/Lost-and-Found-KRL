<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_laporan.php");
    exit();
}

$id_laporan = (int) ($_POST['id_laporan'] ?? 0);
$status_raw = trim($_POST['status'] ?? '');


$allowed_status = ['Mencari', 'Ditemukan', 'Selesai'];

if (!$id_laporan || !in_array($status_raw, $allowed_status)) {
    header("Location: kelola_laporan.php?update=gagal");
    exit();
}

$status = mysqli_real_escape_string($con, $status_raw);

$result = mysqli_query($con,
    "UPDATE laporan_kehilangan
     SET status = '$status'
     WHERE id_laporan = $id_laporan"
);

if ($result && mysqli_affected_rows($con) >= 0) {
    header("Location: kelola_laporan.php?update=sukses");
} else {
    header("Location: kelola_laporan.php?update=gagal");
}
exit();