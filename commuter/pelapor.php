<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}

$id   = (int)($_SESSION['id_pelapor'] ?? 0); // FIX #3: cast to int to prevent SQL injection
$nama = $_SESSION['nama_pelapor'] ?? 'Pelapor';
$image = $_SESSION['image_pelapor'] ?? '';

// Hitung laporan
$q_total     = mysqli_query($con, "SELECT COUNT(*) as total FROM laporan_kehilangan WHERE id_pelapor = $id");
$total       = mysqli_fetch_assoc($q_total)['total'];

$q_mencari   = mysqli_query($con, "SELECT COUNT(*) as total FROM laporan_kehilangan WHERE id_pelapor = $id AND status = 'Mencari'");
$mencari     = mysqli_fetch_assoc($q_mencari)['total'];

$q_ditemukan = mysqli_query($con, "SELECT COUNT(*) as total FROM laporan_kehilangan WHERE id_pelapor = $id AND status = 'Ditemukan'");
$ditemukan   = mysqli_fetch_assoc($q_ditemukan)['total'];

$q_selesai   = mysqli_query($con, "SELECT COUNT(*) as total FROM laporan_kehilangan WHERE id_pelapor = $id AND status = 'Selesai'");
$selesai     = mysqli_fetch_assoc($q_selesai)['total'];

// Laporan terbaru (5 terakhir)
$q_laporan = mysqli_query($con, "SELECT * FROM laporan_kehilangan WHERE id_pelapor = $id ORDER BY id_laporan DESC LIMIT 5");

// Barang temuan terbaru (6 terakhir)
$q_temuan = mysqli_query($con, "SELECT * FROM barang_temuan ORDER BY id_barang_temuan DESC LIMIT 6");

// Laporan aktif pelapor untuk dropdown klaim
$q_laporan_klaim = mysqli_query($con,
    "SELECT id_laporan, nama_barang, lokasi_terakhir
     FROM laporan_kehilangan
     WHERE id_pelapor = $id
     AND status IN ('Mencari','Ditemukan')
     ORDER BY id_laporan DESC"
);

// FIX #1: Store the count once so we can use it multiple times without exhausting the pointer
$jumlah_laporan_klaim = mysqli_num_rows($q_laporan_klaim);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Lost & Found KRL</title>
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
        .card-temuan .card-img-top {
            transition: opacity 0.2s ease;
        }
        .card-temuan.clickable .card-img-top {
            cursor: pointer;
        }
        .card-temuan.clickable .card-img-top:hover {
            opacity: 0.85;
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

        /* FIX #4: Give the barang temuan section a visible background */
        .temuan-section {
            background: rgba(30, 30, 30, 0.75);
            border-radius: 12px;
        }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#navbarNav" data-bs-offset="80" tabindex="0">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="pelapor.php">
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
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat_laporan.php">Buat Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan_saya.php">Laporan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="barang_temuan.php">Barang Temuan</a>
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
                                <a class="dropdown-item" href="profil_pelapor.php">Lihat Profil</a>
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
        <div class="container mt-4"> <!-- FIX #2: removed text-center here, apply per element -->

            <!-- Klaim Alert -->
            <?php if (isset($_GET['klaim'])): ?>
                <?php if ($_GET['klaim'] === 'sukses'): ?>
                    <div class="alert alert-success alert-dismissible fade show text-start mb-4">
                        Klaim berhasil dikirim! Tunggu verifikasi dari petugas.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger alert-dismissible fade show text-start mb-4">
                        Klaim gagal. Silakan coba lagi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- HERO -->
            <div class="hero-section text-center text-white mb-4">
                <h3 class="fw-bold mb-3">
                    Selamat Datang, <?= htmlspecialchars($nama) ?>!
                </h3>

                <div class="row justify-content-center">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-lg">
                            <h6>Sedang Mencari</h6>
                            <h2><?= $mencari ?></h2>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-sm">
                            <h6>Barang Ditemukan</h6>
                            <h2><?= $ditemukan ?></h2>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card shadow-lg">
                            <h6>Selesai</h6>
                            <h2><?= $selesai ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END HERO -->

            <!-- LAPORAN TERBARU -->
            <div id="laporan" class="bg-white rounded-3 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Laporan Terbaru Saya</h6>
                    <a href="laporan_saya.php" class="btn btn-sm btn-outline-warning fw-semibold">
                        Lihat Semua
                    </a>
                </div>

                <?php if (mysqli_num_rows($q_laporan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($laporan = mysqli_fetch_assoc($q_laporan)):
                            if ($laporan['status'] == 'Mencari') {
                                $badgeClass = 'bg-primary';
                            } elseif ($laporan['status'] == 'Ditemukan') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($laporan['status'] == 'Selesai') {
                                $badgeClass = 'bg-success';
                            } else {
                                $badgeClass = 'bg-secondary';
                            }
                        ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($laporan['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($laporan['kategori']) ?></td>
                                <td><?= htmlspecialchars($laporan['lokasi_terakhir']) ?></td>
                                <td><?= date('d M Y', strtotime($laporan['tanggal_hilang'])) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> rounded-pill">
                                        <?= $laporan['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        Belum ada laporan terbaru.
                    </div>
                <?php endif; ?>
            </div>
            <!-- END LAPORAN TERBARU -->

            <!-- BARANG TEMUAN TERBARU -->
            <!-- FIX #4: added temuan-section class for visible dark background -->
            <div class="temuan-section shadow-lg p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-white">Barang Temuan Terbaru</h6>
                    <a href="barang_temuan.php" class="btn btn-sm btn-outline-warning fw-semibold">
                        Lihat Semua
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
                        $is_tersedia = $bt['status'] === 'Tersedia';
                ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card h-100 shadow-sm card-temuan <?= $is_tersedia ? 'clickable' : '' ?>">
                            <img src="image/<?= $bt['image'] ?>"
                                 alt="<?= htmlspecialchars($bt['nama_barang']) ?>"
                                 class="card-img-top"
                                 style="height:150px; object-fit:cover;"
                                 onerror="this.src='image/default.jpg'"
                                 <?php if ($is_tersedia): ?>
                                     data-bs-toggle="modal"
                                     data-bs-target="#modalKlaim"
                                     data-id="<?= $bt['id_barang_temuan'] ?>"
                                     data-nama="<?= htmlspecialchars($bt['nama_barang']) ?>"
                                 <?php endif; ?>>
                            <div class="card-body d-flex flex-column">
                                <!-- Judul + Kategori + Status -->
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
                                <!-- Info -->
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
                                <!-- Tombol selalu di bawah -->
                                <?php if ($is_tersedia): ?>
                                    <button class="btn btn-warning btn-sm w-100 mt-auto fw-semibold"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalKlaim"
                                            data-id="<?= $bt['id_barang_temuan'] ?>"
                                            data-nama="<?= htmlspecialchars($bt['nama_barang']) ?>">
                                        Klaim Barang Ini
                                    </button>
                                <?php elseif ($bt['status'] === 'Sedang Diklaim'): ?>
                                    <button class="btn btn-secondary btn-sm w-100 mt-auto" disabled>
                                        Sedang Diklaim
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm w-100 mt-auto" disabled>
                                        Sudah Dikembalikan
                                    </button>
                                <?php endif; ?>
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
            <!-- END BARANG TEMUAN TERBARU -->

        </div> <!-- FIX #2: closes .container that was left unclosed -->
    </section>

    <!-- Modal Klaim -->
    <div class="modal fade" id="modalKlaim" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Klaim Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="proses_klaim.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">

                        <input type="hidden" name="id_barang_temuan" id="input_id_barang">
                        <input type="hidden" name="from" value="pelapor">

                        <div class="alert alert-info py-2">
                            Anda mengklaim: <strong id="label_nama_barang"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Pilih Laporan Kehilangan Anda <span class="text-danger">*</span>
                            </label>
                            <!-- FIX #1: use $jumlah_laporan_klaim instead of calling mysqli_num_rows again -->
                            <?php if ($jumlah_laporan_klaim > 0): ?>
                                <select name="id_laporan" class="form-select" required>
                                    <option value="">-- Pilih laporan --</option>
                                    <?php while ($lk = mysqli_fetch_assoc($q_laporan_klaim)): ?>
                                        <option value="<?= $lk['id_laporan'] ?>">
                                            <?= htmlspecialchars($lk['nama_barang']) ?> — <?= htmlspecialchars($lk['lokasi_terakhir']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Hanya laporan aktif yang ditampilkan.</div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    Anda belum memiliki laporan kehilangan aktif.
                                    <a href="#laporan" data-bs-dismiss="modal">Buat laporan dulu</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Alasan Klaim <span class="text-danger">*</span>
                            </label>
                            <textarea name="alasan_klaim" class="form-control" rows="3"
                                      placeholder="Jelaskan mengapa barang ini milik Anda..."
                                      required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Foto Bukti <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="foto_bukti" class="form-control"
                                   accept="image/*" required>
                            <div class="form-text">
                                Upload foto bukti kepemilikan (struk, foto lama, dll).
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Batal</button>
                        <!-- FIX #1: use stored count — pointer is no longer exhausted here -->
                        <?php if ($jumlah_laporan_klaim > 0): ?>
                            <button type="submit" class="btn btn-warning fw-semibold">
                                Kirim Klaim
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalKlaim = document.getElementById('modalKlaim');
        modalKlaim.addEventListener('show.bs.modal', function (e) {
            const btn        = e.relatedTarget;
            const idBarang   = btn.getAttribute('data-id');
            const namaBarang = btn.getAttribute('data-nama');

            document.getElementById('input_id_barang').value         = idBarang;
            document.getElementById('label_nama_barang').textContent = namaBarang;
        });
    </script>

</body>
</html>