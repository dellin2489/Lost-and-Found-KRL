<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id    = $_SESSION['id_petugas'];
$nama  = $_SESSION['nama_petugas'] ?? 'Petugas';
$image = $_SESSION['image_petugas'] ?? '';

// Active tab
$tab = $_GET['tab'] ?? 'semua';

// Filter status klaim
$status = mysqli_real_escape_string($con, trim($_GET['status'] ?? ''));

$where = "1=1";
if ($status !== '') $where .= " AND k.status_klaim = '$status'";

$q_klaim = mysqli_query($con,
    "SELECT k.*,
            bt.nama_barang, bt.lokasi_ditemukan, bt.image AS image_barang,
            l.nama_barang AS nama_barang_laporan,
            l.deskripsi_ciri_ciri, l.lokasi_terakhir, l.id_laporan,
            p.nama_pelapor, p.nomor_telepon
     FROM klaim_penyerahan k
     JOIN barang_temuan bt ON bt.id_barang_temuan = k.id_barang_temuan
     JOIN laporan_kehilangan l ON l.id_laporan = k.id_laporan
     JOIN pelapor p ON p.id_pelapor = l.id_pelapor
     WHERE $where
     ORDER BY k.id_klaim DESC"
);


$q_history = mysqli_query($con,
    "SELECT k.*,
            bt.nama_barang, bt.lokasi_ditemukan, bt.image AS image_barang,
            l.nama_barang AS nama_barang_laporan,
            l.deskripsi_ciri_ciri, l.lokasi_terakhir, l.id_laporan,
            p.nama_pelapor, p.nomor_telepon
     FROM klaim_penyerahan k
     JOIN barang_temuan bt ON bt.id_barang_temuan = k.id_barang_temuan
     JOIN laporan_kehilangan l ON l.id_laporan = k.id_laporan
     JOIN pelapor p ON p.id_pelapor = l.id_pelapor
     WHERE k.status_klaim IN ('Disetujui & Diserahkan', 'Ditolak')
     ORDER BY k.id_klaim DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Klaim - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Arial;
            background: #f2f3f7;
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
            background: rgba(0,0,0,0.3);
            z-index: -1;
        }
        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }

        
        .tab-nav {
            border-bottom: 2px solid rgba(255,255,255,0.15);
            margin-bottom: 1.5rem;
        }
        .tab-nav .nav-link {
            color: rgba(255,255,255,0.65) !important;
            border-radius: 0 !important;
            border-bottom: 3px solid transparent;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: -2px;
            background: none !important;
        }
        .tab-nav .nav-link:hover {
            color: #fff !important;
            background: none !important;
            border-radius: 0 !important;
        }
        .tab-nav .nav-link.active {
            color: #fd7e14 !important;
            border-bottom-color: #fd7e14 !important;
            background: none !important;
        }

        
        .history-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .history-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .history-card .proof-thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .history-card .no-photo {
            width: 100%;
            height: 160px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 0.8rem;
        }

        
        #detailModal .proof-img {
            width: 100%;
            max-height: 340px;
            object-fit: contain;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .info-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.45rem;
            font-size: 0.875rem;
        }
        .info-row .info-label {
            font-weight: 600;
            min-width: 130px;
            color: #555;
        }
        .info-row .info-value {
            color: #222;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="petugas.php">🚆 CommuterLink Nusantara</a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-pills">
                    <li class="nav-item"><a class="nav-link" href="petugas.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_barang.php">Kelola Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_laporan.php">Kelola Laporan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kelola_klaim.php">Kelola Klaim</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                                 alt="Profile" class="profile-img rounded-circle"
                                 onerror="this.src='icon/user.ico'">
                            <?= htmlspecialchars($nama) ?>
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
                <h5 class="fw-bold mb-0 text-white">Kelola Klaim</h5>
                <small class="text-white-50">Daftar semua klaim barang temuan</small>
            </div>
            <a href="petugas.php" class="btn btn-warning fw-semibold">← Kembali</a>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['verifikasi'])): ?>
            <?php if ($_GET['verifikasi'] === 'disetujui'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Klaim berhasil disetujui dan barang telah diserahkan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['verifikasi'] === 'ditolak'): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    Klaim telah ditolak. Barang kembali tersedia.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <ul class="nav tab-nav">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'semua' ? 'active' : '' ?>"
                   href="?tab=semua">📋 Semua Klaim</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'riwayat' ? 'active' : '' ?>"
                   href="?tab=riwayat">🗂️ Riwayat Verifikasi</a>
            </li>
        </ul>

        <!-- ==================== SEMUA KLAIM ==================== -->
        <?php if ($tab === 'semua'): ?>

        <!-- Filter -->
        <form method="GET" action="kelola_klaim.php" class="row g-2 mb-4">
            <input type="hidden" name="tab" value="semua">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Menunggu Verifikasi"    <?= $status == 'Menunggu Verifikasi'    ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="Disetujui & Diserahkan" <?= $status == 'Disetujui & Diserahkan' ? 'selected' : '' ?>>Disetujui & Diserahkan</option>
                    <option value="Ditolak"                <?= $status == 'Ditolak'                ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold w-100">Filter</button>
                <a href="kelola_klaim.php?tab=semua" class="btn btn-dark text-warning w-100">Reset</a>
            </div>
        </form>

        <!-- Tabel -->
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (mysqli_num_rows($q_klaim) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Barang Temuan</th>
                                <th>Pelapor</th>
                                <th>No. Telepon</th>
                                <th>Tanggal Klaim</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($klaim = mysqli_fetch_assoc($q_klaim)):
                            if ($klaim['status_klaim'] == 'Menunggu Verifikasi') {
                                $badgeKlaim = 'bg-warning text-dark';
                            } elseif ($klaim['status_klaim'] == 'Disetujui & Diserahkan') {
                                $badgeKlaim = 'bg-success';
                            } elseif ($klaim['status_klaim'] == 'Ditolak') {
                                $badgeKlaim = 'bg-danger';
                            } else {
                                $badgeKlaim = 'bg-secondary';
                            }

                            
                            $row_json = htmlspecialchars(json_encode($klaim), ENT_QUOTES);
                        ?>
                            <tr style="cursor:pointer;"
                                onclick="openDetail(<?= $row_json ?>)"
                                title="Klik untuk lihat detail">
                                <td class="text-muted"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($klaim['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($klaim['nama_pelapor']) ?></td>
                                <td><?= htmlspecialchars($klaim['nomor_telepon']) ?></td>
                                <td><?= date('d M Y', strtotime($klaim['tanggal_klaim'])) ?></td>
                                <td>
                                    <span class="badge <?= $badgeKlaim ?> rounded-pill">
                                        <?= htmlspecialchars($klaim['status_klaim']) ?>
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <?php if ($klaim['status_klaim'] == 'Menunggu Verifikasi'): ?>
                                        <a href="verifikasi_klaim.php?id=<?= $klaim['id_klaim'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <img src="icon/edit.ico" style="width:16px; height:16px;">
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary"
                                                onclick="openDetail(<?= $row_json ?>)">
                                            👁
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center py-5 text-black">
                        <p class="fs-5">Tidak ada klaim yang cocok.</p>
                        <a href="kelola_klaim.php" class="btn btn-outline-warning">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ==================== RIWAYAT VERIFIKASI ==================== -->
        <?php elseif ($tab === 'riwayat'): ?>

        <?php if (mysqli_num_rows($q_history) > 0): ?>
        <div class="row g-3">
        <?php while ($h = mysqli_fetch_assoc($q_history)):
            $isApproved = $h['status_klaim'] === 'Disetujui & Diserahkan';
            $badgeH     = $isApproved ? 'bg-success' : 'bg-danger';
            $row_json_h = htmlspecialchars(json_encode($h), ENT_QUOTES);
        ?>
            <div class="col-md-4 col-sm-6">
                <div class="history-card" onclick="openDetail(<?= $row_json_h ?>)">

                    <!-- Foto Bukti thumbnail -->
                    <?php if (!empty($h['foto_bukti'])): ?>
                        <img src="image/<?= htmlspecialchars($h['foto_bukti']) ?>"
                             class="proof-thumb"
                             alt="Bukti Klaim"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="no-photo" style="display:none;">📷 Foto tidak tersedia</div>
                    <?php else: ?>
                        <div class="no-photo">📷 Tidak ada foto bukti</div>
                    <?php endif; ?>

                    <div class="p-3">
                        <!-- Status -->
                        <div class="mb-2">
                            <span class="badge <?= $badgeH ?> rounded-pill">
                                <?= htmlspecialchars($h['status_klaim']) ?>
                            </span>
                        </div>
                        <!-- Nama Barang -->
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($h['nama_barang']) ?></h6>
                        <!-- Reporter -->
                        <p class="text-muted small mb-1">
                            👤 <?= htmlspecialchars($h['nama_pelapor']) ?>
                        </p>
                        <!-- Tanggal Klaim -->
                        <p class="text-muted small mb-0">
                            📅 <?= date('d M Y', strtotime($h['tanggal_klaim'])) ?>
                        </p>
                        <p class="text-muted small mb-0 text-truncate">
                            📝 <?= htmlspecialchars($h['alasan_klaim'] ?? '-') ?>
                        </p>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
        </div>
        <?php else: ?>
            <div class="text-center py-5 text-white">
                <p class="fs-5">Belum ada riwayat verifikasi.</p>
            </div>
        <?php endif; ?>

        <?php endif;  ?>
    </div>


    <!-- ==================== DETAIL ==================== -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Detail Klaim</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">

                        <!-- Left: proof photo -->
                        <div class="col-md-5">
                            <p class="fw-semibold text-muted small mb-2">FOTO BUKTI KEPEMILIKAN</p>
                            <img id="modal-proof-img" src="" alt="Bukti Klaim" class="proof-img mb-2"
                                 onerror="this.src='image/default.jpg'">
                            <a id="modal-proof-link" href="#" target="_blank"
                               class="btn btn-sm btn-outline-secondary w-100">
                                🔍 Buka Foto Penuh
                            </a>
                        </div>

                        <!-- Right: claim info -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <span id="modal-badge" class="badge rounded-pill fs-6"></span>
                            </div>

                            <p class="fw-semibold text-muted small mb-2">INFORMASI KLAIM</p>
                            <div class="info-row">
                                <span class="info-label">Barang Temuan</span>
                                <span class="info-value" id="modal-nama-barang">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Lokasi Ditemukan</span>
                                <span class="info-value" id="modal-lokasi-barang">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Pelapor</span>
                                <span class="info-value" id="modal-pelapor">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. Telepon</span>
                                <span class="info-value" id="modal-telepon">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanggal Klaim</span>
                                <span class="info-value" id="modal-tanggal">—</span>
                            </div>

                            <hr class="my-3">

                            <p class="fw-semibold text-muted small mb-2">LAPORAN KEHILANGAN</p>
                            <div class="info-row">
                                <span class="info-label">Nama Barang</span>
                                <span class="info-value" id="modal-nama-laporan">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Lokasi Terakhir</span>
                                <span class="info-value" id="modal-lokasi-laporan">—</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ciri-ciri</span>
                                <span class="info-value" id="modal-ciri">—</span>
                            </div>

                            <hr class="my-3">

                            <p class="fw-semibold text-muted small mb-2">ALASAN KLAIM</p>
                            <p id="modal-alasan" class="text-secondary small"
                               style="background:#f8f9fa; border-radius:8px; padding:0.75rem;">—</p>

                            <!-- Action button (only for pending) -->
                            <div id="modal-action" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function openDetail(data) {
            // Foto Bukti
            const foto = data.foto_bukti ?? '';
            const imgSrc = foto ? 'image/' + foto : 'image/default.jpg';
            document.getElementById('modal-proof-img').src  = imgSrc;
            document.getElementById('modal-proof-link').href = imgSrc;

            // Status 
            const badge = document.getElementById('modal-badge');
            const status = data.status_klaim ?? '';
            badge.textContent = status;
            badge.className = 'badge rounded-pill fs-6 ';
            if (status === 'Menunggu Verifikasi')    badge.className += 'bg-warning text-dark';
            else if (status === 'Disetujui & Diserahkan') badge.className += 'bg-success';
            else if (status === 'Ditolak')           badge.className += 'bg-danger';
            else                                     badge.className += 'bg-secondary';

            // Info Klaim
            document.getElementById('modal-nama-barang').textContent   = data.nama_barang      ?? '—';
            document.getElementById('modal-lokasi-barang').textContent = data.lokasi_ditemukan  ?? '—';
            document.getElementById('modal-pelapor').textContent       = data.nama_pelapor      ?? '—';
            document.getElementById('modal-telepon').textContent       = data.nomor_telepon     ?? '—';
            document.getElementById('modal-nama-laporan').textContent  = data.nama_barang_laporan ?? '—';
            document.getElementById('modal-lokasi-laporan').textContent= data.lokasi_terakhir   ?? '—';
            document.getElementById('modal-ciri').textContent          = data.deskripsi_ciri_ciri ?? '—';
            document.getElementById('modal-alasan').textContent        = data.alasan_klaim      ?? '—';

           
            const tgl = data.tanggal_klaim
                ? new Date(data.tanggal_klaim).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'})
                : '—';
            document.getElementById('modal-tanggal').textContent = tgl;

            // Action button
            const actionDiv = document.getElementById('modal-action');
            if (status === 'Menunggu Verifikasi') {
                actionDiv.innerHTML = `<a href="verifikasi_klaim.php?id=${data.id_klaim}"
                    class="btn btn-warning fw-semibold w-100">✔ Verifikasi Klaim Ini</a>`;
            } else {
                actionDiv.innerHTML = '';
            }

            // Show modal
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }
    </script>
</body>
</html>