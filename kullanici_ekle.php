<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ 
    header("Location: login.php"); 
    exit; 
}

// Şu an giriş yapan kullanıcının rolünü al
$giren_id = $_SESSION['login'];
$giren = $baglanti->query("SELECT * FROM kullanicilar WHERE id=$giren_id")->fetch_assoc();
$admin_mi = $giren['rol']==1 ? true : false;

// Yeni kullanıcı ekleme sadece admin görebilsin
$msg = '';
if($admin_mi && isset($_POST['kaydet'])){
    $ad = $_POST['ad'];
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre']; // Düz metin
    $rol = $_POST['rol'] ?? 0;

    $stmt = $baglanti->prepare("INSERT INTO kullanicilar (ad, kullanici_adi, sifre, rol) VALUES (?,?,?,?)");
    $stmt->bind_param("sssi", $ad, $kullanici_adi, $sifre, $rol);
    if($stmt->execute()){
        $msg = "✅ Kullanıcı başarıyla eklendi.";
    }else{
        $msg = "❌ Hata: Kullanıcı eklenemedi.";
    }
}

// Kullanıcıları çek (sadece admin görür)
if($admin_mi){
    $kullanicilar = $baglanti->query("SELECT * FROM kullanicilar ORDER BY id DESC");
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
<title>Kullanıcı Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#1a1a1a,#0d6efd); color:#fff; font-family:'Segoe UI', sans-serif; min-height:100vh; }
.card { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:20px; margin-bottom:20px; box-shadow:0 8px 20px rgba(0,0,0,0.3); }
.table th, .table td { vertical-align: middle; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-4">
  <h3 class="mb-4">👤 Kullanıcı Yönetimi</h3>

  <?php if($msg != ''): ?>
    <div class="alert alert-light text-dark"><?= $msg ?></div>
  <?php endif; ?>

  <?php if($admin_mi){ ?>
  <div class="card mb-4">
    <h5>Yeni Kullanıcı Ekle</h5>
    <form method="post">
      <div class="mb-3">
        <label>Ad Soyad</label>
        <input type="text" name="ad" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Kullanıcı Adı</label>
        <input type="text" name="kullanici_adi" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Şifre (Düz Metin)</label>
        <input type="text" name="sifre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Rol</label>
        <select name="rol" class="form-select">
          <option value="0">Normal Kullanıcı</option>
          <option value="1">Admin</option>
        </select>
      </div>
      <button class="btn btn-success" name="kaydet">Kaydet</button>
    </form>
  </div>

  <div class="card">
    <h5>Mevcut Kullanıcılar</h5>
    <div class="table-responsive">
      <table class="table table-hover text-white">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Ad</th>
            <th>Kullanıcı Adı</th>
            <th>Şifre</th>
            <th>Rol</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php while($k = $kullanicilar->fetch_assoc()){ ?>
          <tr>
            <td><?= $k['id'] ?></td>
            <td><?= $k['ad'] ?></td>
            <td><?= $k['kullanici_adi'] ?></td>
            <td><?= $k['sifre'] ?></td>
            <td><?= $k['rol']==1?'Admin':'Normal' ?></td>
            <td>
              <a href="kullanici_guncelle.php?id=<?= $k['id'] ?>" class="btn btn-warning btn-sm">Güncelle</a>
              <a href="kullanici_sil.php?id=<?= $k['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
            </td>
          </tr>
          <?php } ?>
          <?php if($kullanicilar->num_rows==0){ ?>
          <tr><td colspan="6" class="text-center">Kayıt bulunamadı.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } else { ?>
    <div class="alert alert-warning">Kullanıcı listesi sadece admin tarafından görüntülenebilir.</div>
  <?php } ?>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
</body>
</html>