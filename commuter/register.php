<?php
include "connection.php";

if(isset($_POST['register'])){

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = MD5($_POST['password']);
$telp = $_POST['nomor_telepon'];


$nama_file = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

$folder = "image/".$nama_file;

move_uploaded_file($tmp,$folder);

$query = mysqli_query($con,"INSERT INTO pelapor
(nama_pelapor,email,password,nomor_telepon,image)
VALUES
('$nama','$email','$password','$telp','$nama_file')");

if($query){
echo "<script>alert('Registrasi berhasil'); window.location='login.php';</script>";
}else{
echo "Registrasi gagal";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f2f3f7;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: url('https://i.pinimg.com/736x/9a/96/e1/9a96e1786ad3b6884d150a151c43e906.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
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

.card{
    width:350px;
    background:white;
    padding:35px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    text-align:center;
}

.input{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ddd;
    border-radius:6px;
    box-sizing: border-box;
}

.register-btn{
    width:100%;
    padding:12px;
    background:#1f6feb;
    color:white;
    border:none;
    border-radius:6px;
    margin-top:15px;
    cursor:pointer;
    transition: all 0.3s ease;
}

.register-btn:hover{
    background:#1555c0;
    box-shadow: 0 0 10px #1f6feb, 
                0 0 20px #1f6feb, 
                0 0 30px #1f6feb;
}

.login-link{
    margin-top:15px;
    font-size:14px;
}

.login-link a{
    color:#1f6feb;
    text-decoration:none;
    transition: all 0.3s ease;
}

.login-link a:hover{
    color:#1555c0;
    text-shadow: 0 0 5px rgba(31,111,235,0.6);
}
</style>

</head>

<body>

<div class="card">

<h2>Register</h2>
<p>Create your account</p>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nama" placeholder="Nama" class="input" required>

<input type="email" name="email" placeholder="Email" class="input" required>

<input type="password" name="password" placeholder="Password" class="input" required>

<input type="text" name="nomor_telepon" placeholder="Nomor Telepon" class="input" required>

<input type="file" name="image" class="input" required>

<button type="submit" name="register" class="register-btn">Register</button>

</form>

<div class="login-link">
Sudah punya akun? <a href="login.php">Login</a>
</div>

</div>

</body>
</html>