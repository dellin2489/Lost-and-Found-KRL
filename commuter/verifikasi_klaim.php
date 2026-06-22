<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$nama = $_SESSION['nama_petugas'] ?? 'Petugas';

$id_klaim = (int) $_GET['id'];

// Ambil detail klaim
$q = mysqli_query($con,
    "SELECT k.*,
            bt.nama_barang, bt.lokasi_ditemukan, bt.deskripsi AS deskripsi_barang, bt.image AS image_barang,
            l.nama_barang AS nama_laporan, l.deskripsi_ciri_ciri, l.lokasi_terakhir,
            l.tanggal_hilang, l.waktu_hilang, l.foto_referensi,
            p.nama_pelapor, p.nomor_telepon, p.email
     FROM klaim_penyerahan k
     JOIN barang_temuan bt ON bt.id_barang_temuan = k.id_barang_temuan
     JOIN laporan_kehilangan l ON l.id_laporan = k.id_laporan
     JOIN pelapor p ON p.id_pelapor = l.id_pelapor
     WHERE k.id_klaim = $id_klaim"
);

if (mysqli_num_rows($q) === 0) {
    header("Location: kelola_klaim.php");
    exit();
}

$klaim = mysqli_fetch_assoc($q);

// Hanya bisa diverifikasi kalau masih menunggu
if ($klaim['status_klaim'] !== 'Menunggu Verifikasi') {
    header("Location: kelola_klaim.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Klaim - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { padding-top: 70px; background-color: #f8f9fa; }

        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }

        .foto-box {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="petugas.php">
                CommuterLink Nusantara
            </a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-pills">
                    <li class="nav-item">
                        <a class="nav-link" href="petugas.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_barang.php">Kelola Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_laporan.php">Kelola Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kelola_klaim.php">Kelola Klaim</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                                 alt="Profile"
                                 class="profile-img rounded-circle"
                                 onerror="this.src='icon/user.ico'">
                            <?= $nama ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil_petugas.php">Lihat Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0">Verifikasi Klaim</h5>
                <small class="text-muted">Review dan putuskan klaim barang ini</small>
            </div>
            <a href="kelola_klaim.php" class="btn btn-sm btn-outline-secondary">← Kembali</a>
        </div>

        <div class="row g-4">

            <!-- KOLOM KIRI: Info Barang & Klaim -->
            <div class="col-md-7">

                <!-- Info Barang Temuan -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-semibold">
                        Barang Temuan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <img src="image/<?= $klaim['image_barang'] ?>"
                                     class="foto-box"
                                     onerror="this.src='image/default.jpg'">
                            </div>
                            <div class="col-md-7">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px">Nama Barang</td>
                                        <td class="fw-semibold"><?= $klaim['nama_barang'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Deskripsi</td>
                                        <td><?= $klaim['deskripsi_barang'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Lokasi Temuan</td>
                                        <td><?= $klaim['lokasi_ditemukan'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Laporan Pelapor -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-semibold">
                        Laporan Kehilangan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <img src="image/<?= $klaim['foto_referensi'] ?>"
                                     class="foto-box"
                                     onerror="this.src='image/default.jpg'">
                                <small class="text-muted d-block mt-1 text-center">Foto Referensi Pelapor</small>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px">Nama Pelapor</td>
                                        <td class="fw-semibold"><?= $klaim['nama_pelapor'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">No. Telepon</td>
                                        <td><?= $klaim['nomor_telepon'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td><?= $klaim['email'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Ciri-ciri</td>
                                        <td><?= $klaim['deskripsi_ciri_ciri'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Lokasi Hilang</td>
                                        <td><?= $klaim['lokasi_terakhir'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Hilang</td>
                                        <td><?= date('d M Y', strtotime($klaim['tanggal_hilang'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Foto Bukti Klaim -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning fw-semibold">
                        Bukti Klaim dari Pelapor
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <img src="image/<?= $klaim['foto_bukti'] ?>"
                                     class="foto-box"
                                     onerror="this.src='image/default.jpg'">
                                <small class="text-muted d-block mt-1 text-center">Foto Bukti</small>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px">Alasan Klaim</td>
                                        <td><?= $klaim['alasan_klaim'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Klaim</td>
                                        <td><?= date('d M Y H:i', strtotime($klaim['tanggal_klaim'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: Form Keputusan -->
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-semibold">
                        Keputusan Verifikasi
                    </div>
                    <div class="card-body">

                        <div class="alert alert-info">
                            Periksa informasi di sebelah kiri sebelum membuat keputusan.
                        </div>

                        <form action="proses_verifikasi.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_klaim"        value="<?= $klaim['id_klaim'] ?>">
                            <input type="hidden" name="id_barang_temuan" value="<?= $klaim['id_barang_temuan'] ?>">
                            <input type="hidden" name="id_laporan"       value="<?= $klaim['id_laporan'] ?>">

                            <!-- Bukti Penyerahan -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Foto Bukti Penyerahan
                                    <span class="text-muted fw-normal">(opsional, hanya jika disetujui)</span>
                                </label>
                                <input type="file" name="bukti_penyerahan" class="form-control" accept="image/*">
                                <div class="form-text">Upload foto saat barang diserahkan ke pelapor.</div>
                            </div>

                            <!-- Tombol Keputusan -->
                            <div class="d-grid gap-2">
                                <button type="submit" name="aksi" value="setujui"
                                        class="btn btn-success fw-semibold"
                                        onclick="return confirm('Setujui klaim ini dan serahkan barang?')">
                                    Setujui & Serahkan
                                </button>
                                <button type="submit" name="aksi" value="tolak"
                                        class="btn btn-danger fw-semibold"
                                        onclick="return confirm('Tolak klaim ini?')">
                                    Tolak Klaim
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>