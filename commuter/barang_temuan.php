<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}

$id    = (int) ($_SESSION['id_pelapor'] ?? 0);
$nama  = $_SESSION['nama_pelapor'] ?? 'Pelapor';
$image = $_SESSION['image_pelapor'] ?? '';


$search   = mysqli_real_escape_string($con, trim($_GET['search']   ?? ''));
$kategori = mysqli_real_escape_string($con, trim($_GET['kategori'] ?? ''));
$status   = mysqli_real_escape_string($con, trim($_GET['status']   ?? ''));


$search_raw   = trim($_GET['search']   ?? '');
$kategori_raw = trim($_GET['kategori'] ?? '');
$status_raw   = trim($_GET['status']   ?? '');

$where = "1=1";
if ($search   !== '') $where .= " AND nama_barang LIKE '%$search%'";
if ($kategori !== '') $where .= " AND kategori = '$kategori'";
if ($status   !== '') $where .= " AND status = '$status'";

$q_temuan = mysqli_query($con,
    "SELECT * FROM barang_temuan WHERE $where ORDER BY id_barang_temuan DESC"
);

// Laporan untuk dropdown klaim
$q_laporan_klaim = mysqli_query($con,
    "SELECT id_laporan, nama_barang, lokasi_terakhir
     FROM laporan_kehilangan
     WHERE id_pelapor = $id AND status IN ('Mencari','Ditemukan')
     ORDER BY id_laporan DESC"
);


$jumlah_laporan_klaim = mysqli_num_rows($q_laporan_klaim);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Temuan - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        html { scroll-behavior: smooth; }

        
        body {
            margin: 0;
            font-family: Arial;
            min-height: 100vh;
            padding-top: 70px;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('https://i.pinimg.com/736x/9a/96/e1/9a96e1786ad3b6884d150a151c43e906.jpg') no-repeat center;
            background-size: cover;
            filter: blur(8px);
            transform: scale(1.05);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }

        .card-temuan {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-temuan:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        }
        .card-temuan.clickable .card-img-top {
            cursor: pointer;
            transition: opacity 0.2s ease;
        }
        .card-temuan.clickable .card-img-top:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>

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
                        <a class="nav-link" href="pelapor.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat_laporan.php">Buat Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan_saya.php">Laporan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="barang_temuan.php">Barang Temuan</a>
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
                            <li><a class="dropdown-item" href="profil_pelapor.php">Lihat Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    
    <div class="container py-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-white">Barang Temuan</h5>
                <small class="text-white-50">Daftar barang yang ditemukan di stasiun KRL</small>
            </div>
            <a href="pelapor.php" class="btn btn-warning fw-semibold">← Kembali</a>
        </div>

        <div class="rounded-3 shadow-lg p-4 mb-4" style="background: rgba(30,30,30,0.75);">

            <!-- Search & Filter Form -->
            <form method="GET" action="barang_temuan.php" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Cari nama barang..."
                           value="<?= htmlspecialchars($search_raw) ?>">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        <option value="Tas"        <?= $kategori_raw == 'Tas'        ? 'selected' : '' ?>>Tas</option>
                        <option value="Elektronik" <?= $kategori_raw == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                        <option value="Dompet"     <?= $kategori_raw == 'Dompet'     ? 'selected' : '' ?>>Dompet</option>
                        <option value="Pakaian"    <?= $kategori_raw == 'Pakaian'    ? 'selected' : '' ?>>Pakaian</option>
                        <option value="Dokumen"    <?= $kategori_raw == 'Dokumen'    ? 'selected' : '' ?>>Dokumen</option>
                        <option value="Aksesoris"  <?= $kategori_raw == 'Aksesoris'  ? 'selected' : '' ?>>Aksesoris</option>
                        <option value="Lainnya"    <?= $kategori_raw == 'Lainnya'    ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="Tersedia"           <?= $status_raw == 'Tersedia'           ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Sedang Diklaim"     <?= $status_raw == 'Sedang Diklaim'     ? 'selected' : '' ?>>Sedang Diklaim</option>
                        <option value="Sudah Dikembalikan" <?= $status_raw == 'Sudah Dikembalikan' ? 'selected' : '' ?>>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-warning fw-semibold w-100">Cari</button>
                    <a href="barang_temuan.php" class="btn btn-dark text-warning w-100">Reset</a>
                </div>
            </form>

            <!-- Klaim Alert -->
            <?php if (isset($_GET['klaim'])): ?>
                <?php if ($_GET['klaim'] === 'sukses'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Klaim berhasil dikirim! Tunggu verifikasi dari petugas.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        Klaim gagal. Silakan coba lagi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Card Grid -->
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
                             style="height: 180px; object-fit: cover;"
                             onerror="this.src='image/default.jpg'"
                             <?php if ($is_tersedia): ?>
                                 data-bs-toggle="modal"
                                 data-bs-target="#modalKlaim"
                                 data-id="<?= $bt['id_barang_temuan'] ?>"
                                 data-nama="<?= htmlspecialchars($bt['nama_barang'], ENT_QUOTES) ?>"
                             <?php endif; ?>>
                        <div class="card-body d-flex flex-column">
                            <!-- Judul, Kategori, Status -->
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
                                        data-nama="<?= htmlspecialchars($bt['nama_barang'], ENT_QUOTES) ?>">
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
                <div class="col-12 text-center py-5 text-white">
                    <p>Tidak ada barang temuan yang cocok.</p>
                    <a href="barang_temuan.php" class="btn btn-outline-warning">Reset Pencarian</a>
                </div>
            <?php endif; ?>
            </div>
            

        </div>
        

    </div>
    

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
                        <input type="hidden" name="from" value="barang_temuan">

                        <div class="alert alert-info py-2">
                            Anda mengklaim: <strong id="label_nama_barang"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Pilih Laporan Kehilangan Anda <span class="text-danger">*</span>
                            </label>
                            
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
                                    <a href="buat_laporan.php" data-bs-dismiss="modal">Buat laporan dulu</a>
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
        modalKlaim.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('input_id_barang').value         = btn.getAttribute('data-id');
            document.getElementById('label_nama_barang').textContent = btn.getAttribute('data-nama');
        });
    </script>

</body>
</html>