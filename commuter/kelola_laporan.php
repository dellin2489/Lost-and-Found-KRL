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

// Search & filter
$search   = mysqli_real_escape_string($con, trim($_GET['search']   ?? ''));
$kategori = mysqli_real_escape_string($con, trim($_GET['kategori'] ?? ''));
$status   = mysqli_real_escape_string($con, trim($_GET['status']   ?? ''));
$sumber   = mysqli_real_escape_string($con, trim($_GET['sumber']   ?? '')); 

$conditions = ["1=1"];

if ($search   !== '') $conditions[] = "l.nama_barang LIKE '%$search%'";
if ($kategori !== '') $conditions[] = "l.kategori = '$kategori'";
if ($status   !== '') $conditions[] = "l.status = '$status'";


if ($sumber === 'Pelapor') {
    $conditions[] = "l.id_pelapor IS NOT NULL";
} elseif ($sumber === 'Petugas') {
    $conditions[] = "l.id_petugas IS NOT NULL AND l.id_pelapor IS NULL";
}

$where = implode(' AND ', $conditions);


$q_laporan = mysqli_query($con,
    "SELECT l.*, p.nama_pelapor, pt.nama_petugas AS nama_petugas_pelapor,
            kp.bukti_penyerahan, kp.alasan_klaim, b.nama_barang AS nama_barang_diserahkan
     FROM laporan_kehilangan l
     LEFT JOIN pelapor p ON p.id_pelapor = l.id_pelapor
     LEFT JOIN petugas pt ON pt.id_petugas = l.id_petugas
     LEFT JOIN klaim_penyerahan kp ON kp.id_laporan = l.id_laporan
     LEFT JOIN barang_temuan b ON kp.id_barang_temuan = b.id_barang_temuan
     WHERE $where
     ORDER BY l.id_laporan DESC"
);


$q_barang_tersedia = mysqli_query($con, "SELECT id_barang_temuan, nama_barang, lokasi_ditemukan FROM barang_temuan WHERE status = 'Tersedia'");
$barang_tersedia = [];
while ($b = mysqli_fetch_assoc($q_barang_tersedia)) {
    $barang_tersedia[] = $b;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Laporan - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body{
            margin:0;
            font-family:Arial;
            background:#f2f3f7;
            height:100vh;
            display:flex;
            justify-content:center;
            background: url('https://i.pinimg.com/736x/9a/96/e1/9a96e1786ad3b6884d150a151c43e906.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding-top:70px;
        }

        body::before{
            content:"";
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: url('https://i.pinimg.com/736x/9a/96/e1/9a96e1786ad3b6884d150a151c43e906.jpg') no-repeat center;
            background-size:cover;
            filter: blur(8px);
            transform: scale(1.05);
            z-index:-2;
        }

        body::after{
            content:"";
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(0,0,0,0.3);
            z-index:-1;
        }

        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }

        #modalDetailFoto {
            max-height: 250px;
            object-fit: cover;
            width: 100%;
            border-radius: 8px;
        }
        
        input[list] { cursor: pointer; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="petugas.php">🚆 CommuterLink Nusantara</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-pills">
                    <li class="nav-item"><a class="nav-link" href="petugas.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_barang.php">Kelola Barang</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kelola_laporan.php">Kelola Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_klaim.php">Kelola Klaim</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>" alt="Profile" class="profile-img rounded-circle" onerror="this.src='icon/user.ico'">
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-white">Kelola Laporan</h5>
                <small class="text-white-50">Daftar laporan kehilangan barang di stasiun KRL</small>
            </div>
            <div>
                <button class="btn btn-success fw-semibold me-2" data-bs-toggle="modal" data-bs-target="#modalTambahLaporan">
                    + Tambah Laporan
                </button>
                <a href="petugas.php" class="btn btn-warning fw-semibold">← Kembali</a>
            </div>
        </div>

        <?php if (isset($_GET['update'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Berhasil memperbarui data laporan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['hapus'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Laporan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['tambah']) && $_GET['tambah'] == 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Laporan berhasil ditambahkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="GET" action="kelola_laporan.php" class="row g-2 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari nama barang..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Tas" <?= $kategori == 'Tas' ? 'selected' : '' ?>>Tas</option>
                    <option value="Elektronik" <?= $kategori == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                    <option value="Dompet" <?= $kategori == 'Dompet' ? 'selected' : '' ?>>Dompet</option>
                    <option value="Pakaian" <?= $kategori == 'Pakaian' ? 'selected' : '' ?>>Pakaian</option>
                    <option value="Dokumen" <?= $kategori == 'Dokumen' ? 'selected' : '' ?>>Dokumen</option>
                    <option value="Aksesoris" <?= $kategori == 'Aksesoris' ? 'selected' : '' ?>>Aksesoris</option>
                    <option value="Lainnya" <?= $kategori == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Mencari" <?= $status == 'Mencari' ? 'selected' : '' ?>>Mencari</option>
                    <option value="Ditemukan" <?= $status == 'Ditemukan' ? 'selected' : '' ?>>Ditemukan</option>
                    <option value="Selesai" <?= $status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sumber" class="form-select">
                    <option value="">Semua Sumber</option>
                    <option value="Pelapor" <?= $sumber == 'Pelapor' ? 'selected' : '' ?>>Dari Pelapor</option>
                    <option value="Petugas" <?= $sumber == 'Petugas' ? 'selected' : '' ?>>Oleh Petugas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold w-100">Cari</button>
                <a href="kelola_laporan.php" class="btn btn-dark text-warning w-100">Reset</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (mysqli_num_rows($q_laporan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th>Pelapor / Sumber</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($laporan = mysqli_fetch_assoc($q_laporan)):
                            if ($laporan['status'] == 'Mencari') $badgeClass = 'bg-primary';
                            elseif ($laporan['status'] == 'Ditemukan') $badgeClass = 'bg-warning text-dark';
                            elseif ($laporan['status'] == 'Selesai') $badgeClass = 'bg-success';
                            else $badgeClass = 'bg-secondary';

                            $nama_pelapor_tampil = $laporan['nama_pelapor'];
                            if (empty($nama_pelapor_tampil)) {
                                $nama_pelapor_tampil = $laporan['nama_petugas_pelapor'];
                                $sumber_badge = "<span class='badge bg-info text-dark ms-1' style='font-size:0.7em;'>Petugas</span>";
                            } else {
                                $sumber_badge = "<span class='badge bg-secondary ms-1' style='font-size:0.7em;'>Pelapor</span>";
                            }
                        ?>
                            <tr>
                                <td class="text-muted"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($laporan['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($nama_pelapor_tampil) ?><br><?= $sumber_badge ?></td>
                                <td><?= htmlspecialchars($laporan['kategori']) ?></td>
                                <td><?= date('d M Y', strtotime($laporan['tanggal_hilang'])) ?></td>
                                <td><span class="badge <?= $badgeClass ?> rounded-pill"><?= htmlspecialchars($laporan['status']) ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($laporan['status'] !== 'Selesai'): ?>
                                        <button class="btn btn-sm btn-outline-success" title="Penyerahan Langsung" data-bs-toggle="modal" data-bs-target="#modalPenyerahan" data-id="<?= $laporan['id_laporan'] ?>" data-nama="<?= htmlspecialchars($laporan['nama_barang']) ?>">
                                            🤝
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-outline-secondary" title="Detail"
                                                data-bs-toggle="modal" data-bs-target="#modalDetail"
                                                data-nama="<?= htmlspecialchars($laporan['nama_barang']) ?>"
                                                data-pelapor="<?= htmlspecialchars($nama_pelapor_tampil) ?>"
                                                data-kategori="<?= htmlspecialchars($laporan['kategori']) ?>"
                                                data-deskripsi="<?= htmlspecialchars($laporan['deskripsi_ciri_ciri']) ?>"
                                                data-tanggal="<?= date('d M Y', strtotime($laporan['tanggal_hilang'])) ?>"
                                                data-waktu="<?= htmlspecialchars($laporan['waktu_hilang']) ?>"
                                                data-lokasi="<?= htmlspecialchars($laporan['lokasi_terakhir']) ?>"
                                                data-foto="<?= htmlspecialchars($laporan['foto_referensi']) ?>"
                                                data-status="<?= htmlspecialchars($laporan['status']) ?>"
                                                data-badge="<?= $badgeClass ?>"
                                                data-buktipenyerahan="<?= htmlspecialchars($laporan['bukti_penyerahan'] ?? '') ?>"
                                                data-alasan="<?= htmlspecialchars($laporan['alasan_klaim'] ?? '') ?>"
                                                data-namabarangdiserahkan="<?= htmlspecialchars($laporan['nama_barang_diserahkan'] ?? '') ?>">
                                            <img src="icon/detail.ico" style="width:16px; height:16px;">
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" title="Edit Status"
                                                data-bs-toggle="modal" data-bs-target="#modalEditStatus"
                                                data-id="<?= $laporan['id_laporan'] ?>"
                                                data-nama="<?= htmlspecialchars($laporan['nama_barang']) ?>"
                                                data-status="<?= htmlspecialchars($laporan['status']) ?>">
                                            <img src="icon/edit.ico" style="width:16px; height:16px;">
                                        </button>
                                        <a href="hapus_laporan.php?id=<?= $laporan['id_laporan'] ?>" title="Hapus"
                                           class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus laporan ini?')">
                                            <img src="icon/delete.ico" style="width:16px; height:16px;">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <p class="fs-5">Tidak ada laporan yang cocok dengan filter.</p>
                        <a href="kelola_laporan.php" class="btn btn-outline-warning">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><div class="modal fade" id="modalPenyerahan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Penyerahan Langsung</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_penyerahan_langsung.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_laporan" id="penyerahan_id_laporan">
                        <div class="alert alert-warning py-2 mb-3">Laporan: <strong id="penyerahan_nama_laporan"></strong></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cari & Pilih Barang Temuan</label>
                            <input type="text" id="input_cari_barang" class="form-control" list="list_barang" placeholder="-- Ketik nama barang --" autocomplete="off" required>
                            <input type="hidden" name="id_barang_temuan" id="hidden_id_barang" required>
                            <datalist id="list_barang">
                                <?php foreach ($barang_tersedia as $brg): ?>
                                    <option data-id="<?= $brg['id_barang_temuan'] ?>" value="<?= htmlspecialchars($brg['nama_barang']) ?> (Lokasi: <?= htmlspecialchars($brg['lokasi_ditemukan']) ?>)"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan / Alasan Verifikasi</label>
                            <textarea name="alasan_klaim" class="form-control" rows="2" required placeholder="Contoh: KTP asli cocok..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Bukti Penyerahan</label>
                            <input type="file" name="bukti_penyerahan" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success fw-semibold">Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahLaporan" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Tambah Laporan Kehilangan (Walk-in)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="tambah_laporan_aksi.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Tas">Tas</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Dompet">Dompet</option>
                                    <option value="Pakaian">Pakaian</option>
                                    <option value="Dokumen">Dokumen</option>
                                    <option value="Aksesoris">Aksesoris</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Hilang</label>
                                <input type="date" name="tanggal_hilang" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Waktu Hilang</label>
                                <input type="time" name="waktu_hilang" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lokasi Terakhir</label>
                                <input type="text" name="lokasi_terakhir" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Foto (Opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success fw-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Detail Laporan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <img id="modalDetailFoto" src="" alt="Foto" onerror="this.src='image/default.jpg'" class="img-fluid rounded">
                            <small class="text-muted d-block mt-1">Foto Referensi</small>
                        </div>
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-3" id="detail_nama"></h5>
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted" style="width:140px">Pelapor</td><td class="fw-semibold" id="detail_pelapor"></td></tr>
                                <tr><td class="text-muted">Kategori</td><td id="detail_kategori"></td></tr>
                                <tr><td class="text-muted">Ciri-ciri</td><td id="detail_deskripsi"></td></tr>
                                <tr><td class="text-muted">Waktu Hilang</td><td><span id="detail_tanggal"></span> - <span id="detail_waktu"></span></td></tr>
                                <tr><td class="text-muted">Lokasi Terakhir</td><td id="detail_lokasi"></td></tr>
                                <tr><td class="text-muted">Status</td><td><span id="detail_status_badge" class="badge rounded-pill"></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <div id="detail_section_penyerahan" style="display: none;" class="mt-4 pt-4 border-top">
                        <h6 class="fw-bold text-success mb-3"><img src="icon/check.ico" style="width:20px;"> Bukti Penyerahan Langsung</h6>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img id="detail_bukti_penyerahan" src="" alt="Bukti Penyerahan" class="img-fluid rounded border shadow-sm" style="max-height: 180px; object-fit: cover;">
                                <small class="text-muted d-block mt-1">Foto Bukti Penyerahan</small>
                            </div>
                            <div class="col-md-8">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width:140px">Barang Diberikan</td>
                                            <td class="fw-bold text-dark" id="detail_barang_diserahkan"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Keterangan / Alasan</td>
                                            <td class="fst-italic text-secondary" id="detail_alasan_klaim"></td>
                                        </tr>
                                    </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditStatus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Edit Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_status_laporan.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_laporan" id="input_id_laporan">
                        <div class="mb-3">
                            <label class="form-label">Status Laporan</label>
                            <select name="status" id="select_status" class="form-select" required>
                                <option value="Mencari">Mencari</option>
                                <option value="Ditemukan">Ditemukan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning fw-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datalist Handler
        const inputCariBarang = document.getElementById('input_cari_barang');
        const hiddenIdBarang = document.getElementById('hidden_id_barang');
        if (inputCariBarang) {
            inputCariBarang.addEventListener('input', function(e) {
                let inputVal = e.target.value;
                let options = document.getElementById('list_barang').options;
                hiddenIdBarang.value = ''; 
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === inputVal) {
                        hiddenIdBarang.value = options[i].getAttribute('data-id');
                        break;
                    }
                }
            });
        }

        // Modal Penyerahan Handler
        const modalPenyerahan = document.getElementById('modalPenyerahan');
        if(modalPenyerahan) {
            modalPenyerahan.addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                document.getElementById('penyerahan_id_laporan').value = btn.dataset.id;
                document.getElementById('penyerahan_nama_laporan').textContent = btn.dataset.nama;
            });
            modalPenyerahan.addEventListener('hidden.bs.modal', function() {
                if (inputCariBarang) inputCariBarang.value = '';
                if (hiddenIdBarang) hiddenIdBarang.value = '';
            });
        }

        // Modal Detail Handler (TERMASUK MUNCULIN BUKTI)
        const modalDetail = document.getElementById('modalDetail');
        if(modalDetail) {
            modalDetail.addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                
                // Info Laporan
                document.getElementById('detail_nama').textContent      = btn.dataset.nama;
                document.getElementById('detail_pelapor').textContent  = btn.dataset.pelapor;
                document.getElementById('detail_kategori').textContent = btn.dataset.kategori;
                document.getElementById('detail_deskripsi').textContent = btn.dataset.deskripsi;
                document.getElementById('detail_tanggal').textContent  = btn.dataset.tanggal;
                document.getElementById('detail_waktu').textContent    = btn.dataset.waktu;
                document.getElementById('detail_lokasi').textContent   = btn.dataset.lokasi;

                const badge = document.getElementById('detail_status_badge');
                badge.textContent = btn.dataset.status;
                badge.className   = 'badge rounded-pill ' + btn.dataset.badge;

                const foto = btn.dataset.foto;
                document.getElementById('modalDetailFoto').src = foto ? 'image/' + foto : 'image/default.jpg';

                // ==== LOGIKA MUNCULIN BUKTI PENYERAHAN ====
                const sectionPenyerahan = document.getElementById('detail_section_penyerahan');
                const fotoBukti = btn.dataset.buktipenyerahan;

                if (fotoBukti && fotoBukti.trim() !== '') {
                    // Tampilkan section kalau ada buktinya
                    sectionPenyerahan.style.display = 'block';
                    document.getElementById('detail_bukti_penyerahan').src = 'image/' + fotoBukti;
                    document.getElementById('detail_barang_diserahkan').textContent = btn.dataset.namabarangdiserahkan || '-';
                    document.getElementById('detail_alasan_klaim').textContent = btn.dataset.alasan || '-';
                } else {
                    // Sembunyikan kalau kosong/belum ada penyerahan
                    sectionPenyerahan.style.display = 'none';
                }
            });
        }

        // Modal Edit Status
        const modalEdit = document.getElementById('modalEditStatus');
        if(modalEdit) {
            modalEdit.addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                document.getElementById('input_id_laporan').value = btn.dataset.id;
                document.getElementById('select_status').value = btn.dataset.status;
            });
        }
    </script>

</body>
</html>