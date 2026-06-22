<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}

$id_pelapor = (int) $_SESSION['id_pelapor'];
$nama       = $_SESSION['nama_pelapor'] ?? 'Pelapor';
$image      = $_SESSION['image_pelapor'] ?? '';
$pesan      = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_laporan'])) {
    $nama_barang = mysqli_real_escape_string($con, $_POST['nama_barang']);
    $kategori    = mysqli_real_escape_string($con, $_POST['kategori']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $tanggal     = mysqli_real_escape_string($con, $_POST['tanggal_hilang']);
    $waktu       = mysqli_real_escape_string($con, $_POST['waktu_hilang']);
    $lokasi      = mysqli_real_escape_string($con, $_POST['lokasi_terakhir']);

    
    $foto_referensi_sql = 'NULL';

    if (isset($_FILES['foto_referensi']) && $_FILES['foto_referensi']['error'] === 0) {
        $ext     = strtolower(pathinfo($_FILES['foto_referensi']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $nama_file  = 'laporan_' . time() . '_' . $id_pelapor . '.' . $ext;
            $upload_dir = 'image/';
            if (move_uploaded_file($_FILES['foto_referensi']['tmp_name'], $upload_dir . $nama_file)) {
                $foto_referensi_sql = "'" . mysqli_real_escape_string($con, $nama_file) . "'";
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal mengupload foto. Silakan coba lagi.</div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Format foto harus JPG, JPEG, PNG, atau WEBP.</div>";
        }
    }

    if (empty($pesan)) {
        $query = "INSERT INTO laporan_kehilangan
                    (id_pelapor, nama_barang, kategori, deskripsi_ciri_ciri, tanggal_hilang, waktu_hilang, lokasi_terakhir, foto_referensi, status)
                  VALUES
                    ($id_pelapor, '$nama_barang', '$kategori', '$deskripsi', '$tanggal', '$waktu', '$lokasi', $foto_referensi_sql, 'Mencari')";

        if (mysqli_query($con, $query)) {
            header("Location: laporan_saya.php?laporan=sukses");
            exit();
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal membuat laporan: " . mysqli_error($con) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        
        body {
            margin: 0;
            font-family: Arial;
            min-height: 100vh;
            padding-top: 70px;
        }

        
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

        
        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover { background-color: #fd7e1460 !important; border-radius: 20px; color: white !important; }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="pelapor.php">🚆 CommuterLink Nusantara</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-pills">
                    <li class="nav-item"><a class="nav-link" href="pelapor.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="buat_laporan.php">Buat Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan_saya.php">Laporan Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="barang_temuan.php">Barang Temuan</a></li>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        Form Laporan Kehilangan Barang
                    </div>
                    <div class="card-body">
                        <?= $pesan ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" class="form-control"
                                       placeholder="Contoh: Tas Ransel Hitam Eiger" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori" class="form-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        <option value="Tas">Tas</option>
                                        <option value="Elektronik">Elektronik</option>
                                        <option value="Dompet">Dompet</option>
                                        <option value="Pakaian">Pakaian</option>
                                        <option value="Dokumen">Dokumen</option>
                                        <option value="Aksesoris">Aksesoris</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Lokasi Terakhir Terlihat <span class="text-danger">*</span></label>
                                    <input type="text" name="lokasi_terakhir" class="form-control"
                                           placeholder="Contoh: Gerbong 3, Stasiun Manggarai" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Tanggal Hilang <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_hilang" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Waktu Hilang <span class="text-danger">*</span></label>
                                    <input type="time" name="waktu_hilang" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi / Ciri-ciri Lengkap <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" class="form-control" rows="4"
                                          placeholder="Jelaskan ciri-ciri spesifik (warna, merk, isi di dalamnya)..."
                                          required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Foto Referensi (Opsional)</label>
                                <input type="file" name="foto_referensi" class="form-control" accept="image/*">
                                <small class="text-muted">Upload foto barang jika ada untuk memudahkan pencarian petugas.</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="pelapor.php" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" name="submit_laporan" class="btn btn-warning fw-bold">
                                    Kirim Laporan
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