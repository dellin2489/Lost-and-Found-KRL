<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}

$id    = (int) $_SESSION['id_pelapor']; 
$nama  = $_SESSION['nama_pelapor'] ?? 'Pelapor';
$image = $_SESSION['image_pelapor'] ?? '';
$pesan = '';

// EDIT LAPORAN
if (isset($_POST['edit_laporan'])) {
    $id_laporan  = (int) $_POST['id_laporan'];
    $nama_barang = mysqli_real_escape_string($con, $_POST['nama_barang']);
    $kategori    = mysqli_real_escape_string($con, $_POST['kategori']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi_ciri_ciri']);
    $tanggal     = mysqli_real_escape_string($con, $_POST['tanggal_hilang']);
    $waktu       = mysqli_real_escape_string($con, $_POST['waktu_hilang']);
    $lokasi      = mysqli_real_escape_string($con, $_POST['lokasi_terakhir']);

    
    $q_foto = mysqli_query($con,
        "SELECT foto_referensi FROM laporan_kehilangan
         WHERE id_laporan = $id_laporan AND id_pelapor = $id"
    );
    if (mysqli_num_rows($q_foto) === 0) {
        header("Location: laporan_saya.php");
        exit();
    }

    $update_foto = "";

    if (isset($_FILES['foto_referensi']) && $_FILES['foto_referensi']['error'] === 0) {
        $ext     = strtolower(pathinfo($_FILES['foto_referensi']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $nama_file = 'laporan_' . time() . '_' . $id . '.' . $ext;
            if (move_uploaded_file($_FILES['foto_referensi']['tmp_name'], 'image/' . $nama_file)) {
                $nama_file_escaped = mysqli_real_escape_string($con, $nama_file);
                $update_foto = ", foto_referensi='$nama_file_escaped'";
            }
        }
    }

    $query = "UPDATE laporan_kehilangan
              SET nama_barang='$nama_barang', kategori='$kategori',
                  deskripsi_ciri_ciri='$deskripsi', tanggal_hilang='$tanggal',
                  waktu_hilang='$waktu', lokasi_terakhir='$lokasi' $update_foto
              WHERE id_laporan=$id_laporan AND id_pelapor=$id";

    if (mysqli_query($con, $query)) {
        $pesan = "<div class='alert alert-success alert-dismissible fade show'>Laporan berhasil diupdate. <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal update: " . mysqli_error($con) . "</div>";
    }
}


if (isset($_POST['hapus_laporan'])) {
    $id_hapus = (int) $_POST['id_hapus'];
    mysqli_query($con,
        "DELETE FROM laporan_kehilangan
         WHERE id_laporan = $id_hapus AND id_pelapor = $id"
    );
    header("Location: laporan_saya.php?deleted=1");
    exit();
}


$q_laporan = mysqli_query($con,
    "SELECT l.*,
            k.status_klaim,
            k.tanggal_penyerahan,
            bt.nama_barang AS nama_barang_temuan,
            bt.lokasi_ditemukan
     FROM laporan_kehilangan l
     LEFT JOIN klaim_penyerahan k ON k.id_laporan = l.id_laporan
     LEFT JOIN barang_temuan bt ON bt.id_barang_temuan = k.id_barang_temuan
     WHERE l.id_pelapor = $id
     ORDER BY l.id_laporan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - Lost & Found KRL</title>
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
        .nav-link:hover { background-color: #fd7e1460 !important; border-radius: 20px; color: white !important; }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }
        section { scroll-margin-top: 80px; }
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
                        <a class="nav-link" href="pelapor.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat_laporan.php">Buat Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="laporan_saya.php">Laporan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="barang_temuan.php">Barang Temuan</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                                 alt="Profile" class="profile-img rounded-circle"
                                 onerror="this.src='icon/user.ico'">
                            <?= htmlspecialchars($nama) ?> <!-- FIX #2 -->
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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-white">Laporan Saya</h5>
                <small class="text-white-50">Semua laporan kehilangan yang pernah kamu buat</small>
            </div>
            <a href="pelapor.php" class="btn btn-warning fw-semibold">← Kembali</a>
        </div>

        <!-- Alerts -->
        <?= $pesan ?>

        
        <?php if (isset($_GET['laporan']) && $_GET['laporan'] === 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Laporan berhasil dibuat!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Laporan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel -->
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (mysqli_num_rows($q_laporan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi Hilang</th>
                                <th>Tanggal Hilang</th>
                                <th>Status Laporan</th>
                                <th>Status Klaim</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($laporan = mysqli_fetch_assoc($q_laporan)):

                            if ($laporan['status'] == 'Mencari') {
                                $badgeClass = 'bg-primary';
                            } elseif ($laporan['status'] == 'Ditemukan') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($laporan['status'] == 'Selesai') {
                                $badgeClass = 'bg-success';
                            } else {
                                $badgeClass = 'bg-secondary';
                            }

                            if ($laporan['status_klaim'] == 'Menunggu Verifikasi') {
                                $badgeKlaim = 'bg-info text-dark';
                            } elseif ($laporan['status_klaim'] == 'Disetujui & Diserahkan') {
                                $badgeKlaim = 'bg-success';
                            } elseif ($laporan['status_klaim'] == 'Ditolak') {
                                $badgeKlaim = 'bg-danger';
                            } else {
                                $badgeKlaim = '';
                            }
                        ?>
                            <tr>
                                <td class="text-muted"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($laporan['nama_barang']) ?></td> <!-- FIX #2 -->
                                <td><?= htmlspecialchars($laporan['kategori']) ?></td>
                                <td><?= htmlspecialchars($laporan['lokasi_terakhir']) ?></td>
                                <td><?= date('d M Y', strtotime($laporan['tanggal_hilang'])) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> rounded-pill">
                                        <?= $laporan['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($laporan['status_klaim'] != null): ?>
                                        <span class="badge <?= $badgeKlaim ?> rounded-pill">
                                            <?= htmlspecialchars($laporan['status_klaim']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEdit"
                                                data-id="<?= $laporan['id_laporan'] ?>"
                                                data-nama="<?= htmlspecialchars($laporan['nama_barang'], ENT_QUOTES) ?>"
                                                data-kategori="<?= htmlspecialchars($laporan['kategori'], ENT_QUOTES) ?>"
                                                data-deskripsi="<?= htmlspecialchars($laporan['deskripsi_ciri_ciri'], ENT_QUOTES) ?>"
                                                data-tanggal="<?= $laporan['tanggal_hilang'] ?>"
                                                data-waktu="<?= $laporan['waktu_hilang'] ?>"
                                                data-lokasi="<?= htmlspecialchars($laporan['lokasi_terakhir'], ENT_QUOTES) ?>">
                                            <img src="icon/edit.ico" style="width:16px; height:16px;">
                                        </button>

                                        
                                        <form method="POST" action="laporan_saya.php"
                                              onsubmit="return confirm('Yakin ingin menghapus laporan ini?')"
                                              class="d-inline">
                                            <input type="hidden" name="id_hapus" value="<?= $laporan['id_laporan'] ?>">
                                            <button type="submit" name="hapus_laporan"
                                                    class="btn btn-sm btn-outline-danger">
                                                <img src="icon/delete.ico" style="width:16px; height:16px;">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <p class="fs-5">Belum ada laporan kehilangan.</p>
                        <a href="buat_laporan.php" class="btn btn-warning fw-semibold">+ Buat Laporan Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- MODAL EDIT -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_laporan" id="edit_id_laporan">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" name="nama_barang" id="edit_nama"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="kategori" id="edit_kategori" class="form-select" required>
                                    <option value="Tas">Tas</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Dompet">Dompet</option>
                                    <option value="Pakaian">Pakaian</option>
                                    <option value="Dokumen">Dokumen</option>
                                    <option value="Aksesoris">Aksesoris</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Ciri-ciri Barang</label>
                                <textarea name="deskripsi_ciri_ciri" id="edit_deskripsi"
                                          class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Hilang</label>
                                <input type="date" name="tanggal_hilang" id="edit_tanggal"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Waktu Hilang</label>
                                <input type="time" name="waktu_hilang" id="edit_waktu"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Lokasi Terakhir</label>
                                <input type="text" name="lokasi_terakhir" id="edit_lokasi"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Foto Referensi
                                    <span class="text-muted fw-normal">(opsional, kosongkan jika tidak ingin mengubah)</span>
                                </label>
                                <input type="file" name="foto_referensi" class="form-control" accept="image/*">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_laporan" class="btn btn-warning fw-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('edit_id_laporan').value = btn.dataset.id;
            document.getElementById('edit_nama').value       = btn.dataset.nama;
            document.getElementById('edit_kategori').value   = btn.dataset.kategori;
            document.getElementById('edit_deskripsi').value  = btn.dataset.deskripsi;
            document.getElementById('edit_tanggal').value    = btn.dataset.tanggal;
            document.getElementById('edit_waktu').value      = btn.dataset.waktu;
            document.getElementById('edit_lokasi').value     = btn.dataset.lokasi;
        });
    </script>

</body>
</html>