<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id   = $_SESSION['id_petugas'] ?? 0;
$nama = $_SESSION['nama_petugas'] ?? 'Petugas';
$image = $_SESSION['image_petugas'] ?? '';

// Stat cards
$q_total         = mysqli_query($con, "SELECT COUNT(*) as total FROM barang_temuan");
$total           = mysqli_fetch_assoc($q_total)['total'];

$q_belum_diklaim = mysqli_query($con, "SELECT COUNT(*) as total FROM barang_temuan WHERE status = 'Tersedia'");
$belum_diklaim   = mysqli_fetch_assoc($q_belum_diklaim)['total'];

$q_verifikasi    = mysqli_query($con, "SELECT COUNT(*) as total FROM klaim_penyerahan WHERE status_klaim = 'Menunggu Verifikasi'");
$verifikasi      = mysqli_fetch_assoc($q_verifikasi)['total'];

$q_disetujui     = mysqli_query($con, "SELECT COUNT(*) as total FROM klaim_penyerahan WHERE status_klaim = 'Disetujui & Diserahkan'");
$disetujui       = mysqli_fetch_assoc($q_disetujui)['total'];

$q_selesai       = mysqli_query($con, "SELECT COUNT(*) as total FROM laporan_kehilangan WHERE status = 'Selesai'");
$selesai         = mysqli_fetch_assoc($q_selesai)['total'];

// Klaim menunggu verifikasi (5 terbaru)
$q_menunggu = mysqli_query($con,
    "SELECT k.*,
            bt.nama_barang,
            p.nama_pelapor,
            l.lokasi_terakhir
     FROM klaim_penyerahan k
     JOIN barang_temuan bt ON bt.id_barang_temuan = k.id_barang_temuan
     JOIN laporan_kehilangan l ON l.id_laporan = k.id_laporan
     JOIN pelapor p ON p.id_pelapor = l.id_pelapor
     WHERE k.status_klaim = 'Menunggu Verifikasi'
     ORDER BY k.id_klaim DESC
     LIMIT 5"
);

// Barang temuan terbaru (6 terakhir)
$q_temuan = mysqli_query($con, "SELECT * FROM barang_temuan ORDER BY id_barang_temuan DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body {
            padding-top: 70px;
            margin: 0;
            font-family: Arial;
            background: #f2f3f7;
        }

        /* background image + blur */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://i.pinimg.com/736x/9a/96/e1/9a96e1786ad3b6884d150a151c43e906.jpg') no-repeat center;
            background-size: cover;
            filter: blur(8px);
            transform: scale(1.05);
            z-index: -2;
        }

        /* overlay gelap tipis */
        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.25);
            z-index: -1;
        }

        section { scroll-margin-top: 80px; }

        .nav-pills .nav-link.active {
            background-color: #fd7e14 !important;
        }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img {
            width: 30px;
            height: 30px;
            object-fit: cover;
        }

        .card-temuan {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-temuan:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
            min-height: 120px;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
        }

        .hero-section {
            padding: 40px 20px;
            border-radius: 12px;
            background: linear-gradient(
                rgba(0, 0, 0, 0.7),
                rgba(0, 0, 0, 0.4)
            );
            position: relative;
            overflow: hidden;
        }

        /* Give the barang temuan section a visible background */
        .temuan-section {
            background: rgba(30, 30, 30, 0.75);
            border-radius: 12px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="petugas.php">
                🚆 CommuterLink Nusantara
            </a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="petugas.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_barang.php">Kelola Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_laporan.php">Kelola Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_klaim.php">Kelola Klaim</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                                 alt="Profile"
                                 class="profile-img rounded-circle"
                                 onerror="this.src='icon/user.ico'">
                            <?= htmlspecialchars($nama) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="profil_petugas.php">Lihat Profil</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HOME -->
    <section id="home" class="py-5">
        <div class="container mt-4">

            <!-- HERO -->
            <div class="hero-section text-center text-white mb-4">
                <h3 class="fw-bold mb-3">
                    Selamat Datang, <?= htmlspecialchars($nama) ?>!
                </h3>

                <!-- Stat Cards -->
                <div class="row justify-content-center">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-lg">
                            <h6>Total Temuan</h6>
                            <h2><?= $total ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-sm">
                            <h6>Belum Diklaim</h6>
                            <h2><?= $belum_diklaim ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-lg">
                            <h6>Menunggu Verifikasi</h6>
                            <h2><?= $verifikasi ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-sm">
                            <h6>Disetujui & Diserahkan</h6>
                            <h2><?= $disetujui ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-lg">
                            <h6>Laporan Selesai</h6>
                            <h2><?= $selesai ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END HERO -->

            <!-- AKSI CEPAT -->
            <div class="d-flex gap-2 flex-wrap mb-4">
                <a href="kelola_barang.php" class="btn btn-warning fw-semibold">
                    + Tambah Barang Temuan
                </a>
                <a href="kelola_laporan.php" class="btn btn-outline-secondary fw-semibold">
                    Kelola Laporan
                </a>
                <a href="kelola_klaim.php" class="btn btn-outline-secondary fw-semibold">
                    Semua Klaim
                </a>
            </div>

            <!-- KLAIM MASUK -->
            <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Klaim Masuk - Menunggu Verifikasi</h6>
                    <a href="kelola_klaim.php" class="btn btn-sm btn-outline-warning fw-semibold">
                        Lihat Semua →
                    </a>
                </div>

                <?php if (mysqli_num_rows($q_menunggu) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Barang</th>
                                <th>Pelapor</th>
                                <th>Tanggal Klaim</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($klaim_row = mysqli_fetch_assoc($q_menunggu)): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($klaim_row['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($klaim_row['nama_pelapor']) ?></td>
                                <td><?= date('d M Y', strtotime($klaim_row['tanggal_klaim'])) ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark rounded-pill">
                                        Menunggu Verifikasi
                                    </span>
                                </td>
                                <td>
                                    <a href="verifikasi_klaim.php?id=<?= $klaim_row['id_klaim'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        Tidak ada klaim yang menunggu verifikasi.
                    </div>
                <?php endif; ?>
            </div>

            <!-- BARANG TEMUAN TERBARU -->
            <div class="temuan-section shadow-lg p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-white">Barang Temuan Terbaru</h6>
                    <a href="kelola_barang.php" class="btn btn-sm btn-outline-warning fw-semibold">
                        Lihat Semua →
                    </a>
                </div>

                <div class="row g-3">
                <?php if (mysqli_num_rows($q_temuan) > 0):
                      while ($bt = mysqli_fetch_assoc($q_temuan)):

                        if ($bt['status'] == 'Tersedia') {
                            $badge_t = 'bg-success';
                        } elseif ($bt['status'] == 'Sedang Diklaim') {
                            $badge_t = 'bg-warning text-dark';
                        } else {
                            $badge_t = 'bg-secondary';
                        }
                ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card h-100 shadow-sm card-temuan">
                            <img src="image/<?= $bt['image'] ?>"
                                 alt="<?= htmlspecialchars($bt['nama_barang']) ?>"
                                 class="card-img-top"
                                 style="height:150px; object-fit:cover;"
                                 onerror="this.src='image/default.jpg'">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title fw-bold mb-1">
                                        <?= htmlspecialchars($bt['nama_barang']) ?>
                                    </h6>
                                    <div class="d-flex gap-1 flex-wrap justify-content-end">
                                        <span class="badge bg-info text-dark rounded-pill">
                                            <?= htmlspecialchars($bt['kategori']) ?>
                                        </span>
                                        <span class="badge <?= $badge_t ?> rounded-pill">
                                            <?= $bt['status'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="small text-muted text-start mb-2">
                                    <div class="mb-1">
                                        📍 <strong>Lokasi:</strong>
                                        <div><?= htmlspecialchars($bt['lokasi_ditemukan']) ?></div>
                                    </div>
                                    <div class="mb-1">
                                        📅 <strong>Tanggal:</strong>
                                        <div><?= date('d M Y', strtotime($bt['tanggal_ditemukan'])) ?></div>
                                    </div>
                                    <div>
                                        📝 <strong>Detail:</strong>
                                        <div><?= htmlspecialchars($bt['deskripsi']) ?></div>
                                    </div>
                                </div>
                                <a href="kelola_barang.php?edit=<?= $bt['id_barang_temuan'] ?>"
                                   class="btn btn-outline-warning btn-sm w-100 mt-auto">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                else: ?>
                    <div class="col-12 text-center py-4 text-white">
                        <p>Belum ada barang temuan.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>