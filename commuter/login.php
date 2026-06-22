<?php
session_start();
include "connection.php";

if(isset($_POST['login'])){
    $email    = $_POST['email'];
    $password = MD5($_POST['password']);

    // Cek pelapor
    $query1 = mysqli_query($con, "SELECT * FROM pelapor 
        WHERE email='$email' AND password='$password'");

    if(mysqli_num_rows($query1) > 0){
    $data = mysqli_fetch_assoc($query1);
    $_SESSION['id_pelapor']    = $data['id_pelapor'];
    $_SESSION['nama_pelapor']  = $data['nama_pelapor'];
    $_SESSION['image_pelapor'] = $data['image'];
    $_SESSION['role']          = "pelapor";
    header("Location: pelapor.php");
    exit();
}

// Cek petugas
$query2 = mysqli_query($con, "SELECT * FROM petugas 
    WHERE email='$email' AND password='$password'");

if(mysqli_num_rows($query2) > 0){
    $data = mysqli_fetch_assoc($query2);
    $_SESSION['id_petugas']    = $data['id_petugas'];
    $_SESSION['nama_petugas']  = $data['nama_petugas'];
    $_SESSION['image_petugas'] = $data['image'];
    $_SESSION['role']          = "petugas";
    header("Location: petugas.php");
    exit();
}

    $error = "Email atau Password salah!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>

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
    transform: scale(1.05); /* cegah pinggir kosong akibat blur */

    z-index:-2;
}

/* overlay gelap biar kontras */
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

h2{
    margin-bottom:5px;
}

p{
    color:gray;
    font-size:14px;
}

.input-box{
    width:100%;
    display:flex;
    margin-top:10px;
    border:1px solid #ddd;
    border-radius:6px;
    overflow:hidden;
}

.input-box input{
    flex:1;
    padding:12px;
    border:none;
    outline:none;
}

.show-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0 12px;
    background: white;
    cursor:pointer;
}   

.login-btn{
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

.login-btn:hover{
    background:#1555c0;
    box-shadow: 0 0 10px #1f6feb, 
                0 0 20px #1f6feb, 
                0 0 30px #1f6feb;
}

.register{
    margin-top:15px;
    font-size:14px;
}

.register a{
    color:#1f6feb;
    text-decoration:none;
    transition: all 0.3s ease;
}

.register a:hover{
    color:#1555c0;
    text-shadow: 0 0 5px rgba(31,111,235,0.6);
}

.error{
    color:red;
    font-size:13px;
}

</style>

</head>

<body>
<div class="card">

<h2>Login</h2>
<p>Access your account</p>

<?php if(isset($error)){ ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<div class="input-box">
    <input type="email" name="email" placeholder="Email" required>
</div>

<div class="input-box">
<input type="password" name="password" placeholder="Password" id="password" required>
<span class="show-btn" onclick="showPassword()">
    <img src="https://media.istockphoto.com/id/845329690/vector/eye-icon-vector-illustration.jpg?s=612x612&w=0&k=20&c=1SnGiyGCXd83V7m2hX0EsghFSqtmApJ6Qyy2b8Y3L1k=" id="eyeIcon" width="20">
</span>
</div>

<button type="submit" name="login" class="login-btn">Login</button>

</form>

<div class="register">
Don't have an account? <a href="register.php">Register</a>
</div>
<script>

function showPassword(){
    var x = document.getElementById("password");
    var icon = document.getElementById("eyeIcon");

    if(x.type === "password"){
        x.type = "text";
        icon.src = "https://static.thenounproject.com/png/22249-200.png"; // Icon tutup mata
    }else{
        x.type = "password";
        icon.src = "https://media.istockphoto.com/id/845329690/vector/eye-icon-vector-illustration.jpg?s=612x612&w=0&k=20&c=1SnGiyGCXd83V7m2hX0EsghFSqtmApJ6Qyy2b8Y3L1k="; // Icon buka
    }
}

</script>

</body>
</html>