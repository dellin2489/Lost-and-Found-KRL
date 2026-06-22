<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_pelapor'])) {
    header("Location: login.php");
    exit();
}

$id   = $_SESSION['id_pelapor'] ?? 0;
$nama = $_SESSION['nama_pelapor'] ?? 'Pelapor';
$image = $_SESSION['image_pelapor'] ?? '';

$q_profil = mysqli_query($con, "SELECT * FROM pelapor WHERE id_pelapor = $id");
$profil   = mysqli_fetch_assoc($q_profil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Lost & Found KRL</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        body { padding-top: 70px; background-color: #f8f9fa; }
        .nav-pills .nav-link.active { background-color: #fd7e14 !important; }
        .nav-link:hover {
            background-color: #fd7e1460 !important;
            border-radius: 20px;
            color: white !important;
        }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }
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
                        <a class="nav-link" href="barang_temuan.php">Barang Temuan</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 active"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                            alt="Profile"
                            class="profile-img rounded-circle"
                            onerror="this.src='icon/user.ico'">
                            <?= $nama ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php">Lihat Profil</a></li>
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
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        <img src="image/<?= $profil['image'] ?>"
                             onerror="this.src='image/default.jpg'"
                             class="rounded-circle mb-3"
                             style="width:100px; height:100px; object-fit:cover;">
                        <h5 class="fw-bold"><?= $profil['nama_pelapor'] ?></h5>
                        <p class="text-muted mb-0"><?= $profil['email'] ?></p>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Nomor Telepon</strong>
                            <span class="float-end"><?= $profil['nomor_telepon'] ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Email</strong>
                            <span class="float-end"><?= $profil['email'] ?></span>
                        </li>
                    </ul>

                    <div class="card-footer text-center">
                        <a href="pelapor.php" class="btn btn-secondary btn-sm">Kembali</a>
                        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>