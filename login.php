<?php include "db.php"; ?>
<?php
// Giriş yapılmışsa direkt index.php'ye yönlendir
if(isset($_SESSION['login']) && $_SESSION['login']===true){
    header("Location:index.php");
    exit;
}
$hata = '';
if($_POST){
    $kullanici = $_POST['kullanici'];
    $sifre = $_POST['sifre'];
    $query = $baglanti->query("SELECT * FROM kullanicilar WHERE kullanici_adi='$kullanici' AND sifre='$sifre'");
    if($query->num_rows>0){
        $u = $query->fetch_assoc();
        $_SESSION['login'] = true;
        $_SESSION['kullanici'] = $kullanici;
        $_SESSION['rol'] = $u['rol'];
        header("Location:index.php");
        exit;
    } else {
        $hata = "Kullanıcı adı veya şifre yanlış!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#667eea">
<meta name="description" content="Stok Yönetimi Sistemi">
<link rel="manifest" href="manifest.json">
<title>Salman Şantiye Takip Sistemi - Giriş</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #1a1a1a, #0d6efd);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', sans-serif;
}
.login-card {
    background-color: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 15px;
    width: 100%;
    max-width: 400px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}
.login-card h3 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 1.6rem;
}
.form-control {
    background-color: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
}
.form-control:focus {
    background-color: rgba(255,255,255,0.3);
    color: #fff;
    box-shadow: none;
}
.btn-login {
    background-color: #0d6efd;
    border: none;
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s;
}
.btn-login:hover {
    background-color: #0b5ed7;
}
.hata {
    color: #ff4d4d;
    text-align: center;
    margin-bottom: 10px;
    font-weight: bold;
}
.login-logo {
    text-align: center;
    margin-bottom: 20px;
}
.login-logo i {
    font-size: 2.2rem;
    color: #00a8ff;
    margin-bottom: 5px;
}
.login-logo h1 {
    font-size: 1.5rem;
    margin: 0;
}
</style>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<div class="login-card">
    <div class="login-logo">
        <i class="fas fa-hard-hat"></i>
        <h1>Salman Şantiye Takip Sistemi</h1>
    </div>
    <?php if($hata != ''): ?>
        <div class="hata"><?= $hata ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <input type="text" name="kullanici" class="form-control" placeholder="Kullanıcı Adı" required>
        </div>
        <div class="mb-3">
            <input type="password" name="sifre" class="form-control" placeholder="Şifre" required>
        </div>
        <button type="submit" class="btn btn-login">Giriş Yap</button>
    </form>
</div>

<!-- PWA Offline Desteği -->
<script src="app.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker-v2.js')
                .then(registration => {
                    console.log('✅ Service Worker kaydedildi');
                })
                .catch(error => {
                    console.error('Service Worker kaydı başarısız:', error);
                });
        });
    }
</script>

</body>
</html>