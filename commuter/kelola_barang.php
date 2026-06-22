<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id_petugas = $_SESSION['id_petugas'];
$nama = $_SESSION['nama_petugas'] ?? 'Petugas';
$image = $_SESSION['image_petugas'] ?? '';
$pesan = '';

if (isset($_POST['tambah_barang'])) {
    $nama_barang = mysqli_real_escape_string($con, $_POST['nama_barang']);
    $kategori    = mysqli_real_escape_string($con, $_POST['kategori']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $tanggal     = mysqli_real_escape_string($con, $_POST['tanggal_ditemukan']);
    $waktu       = mysqli_real_escape_string($con, $_POST['waktu_ditemukan']);
    $lokasi      = mysqli_real_escape_string($con, $_POST['lokasi_ditemukan']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $nama_file = 'temuan_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $nama_file)) {
                $image = $nama_file;
            }
        }
    }

    $query = "INSERT INTO barang_temuan (id_petugas, nama_barang, kategori, deskripsi, tanggal_ditemukan, waktu_ditemukan, lokasi_ditemukan, image, status) 
              VALUES ($id_petugas, '$nama_barang', '$kategori', '$deskripsi', '$tanggal', '$waktu', '$lokasi', '$image', '$status')";
    
    if (mysqli_query($con, $query)) {
        $pesan = "<div class='alert alert-success'>Berhasil mencatat barang temuan baru.</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal mencatat: " . mysqli_error($con) . "</div>";
    }
}

if (isset($_POST['edit_barang'])) {
    $id_barang   = (int)$_POST['id_barang_temuan'];
    $nama_barang = mysqli_real_escape_string($con, $_POST['nama_barang']);
    $kategori    = mysqli_real_escape_string($con, $_POST['kategori']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $tanggal     = mysqli_real_escape_string($con, $_POST['tanggal_ditemukan']);
    $waktu       = mysqli_real_escape_string($con, $_POST['waktu_ditemukan']);
    $lokasi      = mysqli_real_escape_string($con, $_POST['lokasi_ditemukan']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    $update_foto = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $nama_file = 'temuan_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $nama_file)) {
                $update_foto = ", image='$nama_file'";
            }
        }
    }

    $query = "UPDATE barang_temuan SET 
              nama_barang='$nama_barang', kategori='$kategori', deskripsi='$deskripsi', 
              tanggal_ditemukan='$tanggal', waktu_ditemukan='$waktu', lokasi_ditemukan='$lokasi', status='$status' $update_foto
              WHERE id_barang_temuan=$id_barang";

    if (mysqli_query($con, $query)) {
        $pesan = "<div class='alert alert-success'>Data barang temuan berhasil diupdate.</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal update: " . mysqli_error($con) . "</div>";
    }
}

$q_barang = mysqli_query($con, "SELECT bt.*, p.nama_petugas FROM barang_temuan bt JOIN petugas p ON bt.id_petugas = p.id_petugas ORDER BY bt.id_barang_temuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang Temuan - Petugas</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
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
        .nav-link:hover { background-color: #fd7e1460 !important; border-radius: 20px; color: white !important; }
        .profile-img { width: 30px; height: 30px; object-fit: cover; }
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
                    <li class="nav-item"><a class="nav-link active" href="kelola_barang.php">Kelola Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_laporan.php">Kelola Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_klaim.php">Kelola Klaim</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $image ? 'image/' . $image : 'icon/user.ico' ?>"
                            alt="Profile"
                            class="profile-img rounded-circle"
                            onerror="this.src='icon/user.ico'"> <?= $nama ?>
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
                <h5 class="fw-bold mb-0 text-white">Kelola Barang Temuan</h5>
                <small class="text-white-50">Catat dan kelola barang yang ditemukan di area KRL</small>
            </div>
            <button class="btn btn-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Barang</button>
        </div>

        <?= $pesan ?>
        <?php if (isset($_GET['hapus']) && $_GET['hapus'] == 'sukses') echo "<div class='alert alert-success'>Barang berhasil dihapus.</div>"; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Foto</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi Ditemukan</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($q_barang)): ?>
                            <tr>
                                <td><img src="image/<?= $row['image'] ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;" onerror="this.src='image/default.jpg'"></td>
                                <td class="fw-semibold"><?= $row['nama_barang'] ?></td>
                                <td><?= $row['kategori'] ?></td>
                                <td><?= $row['lokasi_ditemukan'] ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_ditemukan'])) ?> <br> <small><?= $row['waktu_ditemukan'] ?></small></td>
                                <td>
                                    <?php
                                        $bg = $row['status'] == 'Tersedia' ? 'bg-success' : ($row['status'] == 'Sedang Diklaim' ? 'bg-warning text-dark' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= $bg ?> rounded-pill"><?= $row['status'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit" 
                                        data-id="<?= $row['id_barang_temuan'] ?>" data-nama="<?= $row['nama_barang'] ?>" data-kategori="<?= $row['kategori'] ?>"
                                        data-deskripsi="<?= $row['deskripsi'] ?>" data-tanggal="<?= $row['tanggal_ditemukan'] ?>" data-waktu="<?= $row['waktu_ditemukan'] ?>"
                                        data-lokasi="<?= $row['lokasi_ditemukan'] ?>" data-status="<?= $row['status'] ?>">
                                        Edit
                                    </button>
                                    <a href="hapus_barang.php?id=<?= $row['id_barang_temuan'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus barang ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Tambah Barang Temuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="Tas">Tas</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Dompet">Dompet</option>
                                    <option value="Pakaian">Pakaian</option>
                                    <option value="Dokumen">Dokumen</option>
                                    <option value="Aksesoris">Aksesoris</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Ditemukan</label>
                                <input type="date" name="tanggal_ditemukan" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu Ditemukan</label>
                                <input type="time" name="waktu_ditemukan" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lokasi Ditemukan</label>
                                <input type="text" name="lokasi_ditemukan" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Foto Barang</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Awal</label>
                                <select name="status" class="form-select">
                                    <option value="Tersedia">Tersedia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_barang" class="btn btn-warning fw-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Barang Temuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_barang_temuan" id="edit_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" id="edit_nama" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
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
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Ditemukan</label>
                                <input type="date" name="tanggal_ditemukan" id="edit_tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu Ditemukan</label>
                                <input type="time" name="waktu_ditemukan" id="edit_waktu" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lokasi Ditemukan</label>
                                <input type="text" name="lokasi_ditemukan" id="edit_lokasi" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Update Foto (Opsional)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Tersedia">Tersedia</option>
                                    <option value="Sedang Diklaim">Sedang Diklaim</option>
                                    <option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_barang" class="btn btn-primary fw-bold">Update Data</button>
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
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_nama').value = btn.dataset.nama;
            document.getElementById('edit_kategori').value = btn.dataset.kategori;
            document.getElementById('edit_deskripsi').value = btn.dataset.deskripsi;
            document.getElementById('edit_tanggal').value = btn.dataset.tanggal;
            document.getElementById('edit_waktu').value = btn.dataset.waktu;
            document.getElementById('edit_lokasi').value = btn.dataset.lokasi;
            document.getElementById('edit_status').value = btn.dataset.status;
        });
    </script>
</body>
</html>