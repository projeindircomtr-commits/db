<?php
// guncelle.php
include "db.php";
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// ID kontrolü
if(!isset($_GET['id'])){
    header("Location: malzemeler_pro.php");
    exit;
}

$id = intval($_GET['id']);
$malzeme = $baglanti->query("SELECT * FROM malzemeler WHERE id=$id")->fetch_assoc();
if(!$malzeme){
    die("Malzeme bulunamadı!");
}

// Kategoriler ve lokasyonlar
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

$mesaj = '';

if(isset($_POST['guncelle'])){
    $ad = $baglanti->real_escape_string($_POST['ad']);
    $adet = intval($_POST['adet']);
    $kategori_id = intval($_POST['kategori_id']);
    $lokasyon = $baglanti->real_escape_string($_POST['lokasyon']);

    $resimAdi = $malzeme['resim'];

    // Resim yükleme
    if(isset($_FILES['resim']) && $_FILES['resim']['error'] == 0){
        $hedefKlasor = 'uploads/';
        if(!is_dir($hedefKlasor)){
            mkdir($hedefKlasor, 0777, true);
        }
        $resimAdi = time().'_'.preg_replace('/[^a-zA-Z0-9_.]/','_',$_FILES['resim']['name']);
        $hedef = $hedefKlasor.$resimAdi;

        if(!move_uploaded_file($_FILES['resim']['tmp_name'], $hedef)){
            $mesaj = "❌ Resim yüklenemedi! uploads klasör izinlerini kontrol et veya dosya boyutu çok büyük.";
        }
    }

    $sql = "UPDATE malzemeler SET 
                ad='$ad',
                adet='$adet',
                kategori_id='$kategori_id',
                lokasyon='$lokasyon',
                resim='$resimAdi'
            WHERE id=$id";

    if($baglanti->query($sql)){
        $mesaj = "✅ Malzeme başarıyla güncellendi!";
        $malzeme = $baglanti->query("SELECT * FROM malzemeler WHERE id=$id")->fetch_assoc();
    } else {
        $mesaj = "❌ Hata: ".$baglanti->error;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#667eea">
<meta name="description" content="Stok Yönetimi Sistemi">
<link rel="manifest" href="manifest.json">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#1a1a1a,#0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.card { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:30px; box-shadow:0 8px 20px rgba(0,0,0,0.3); margin-top:30px; }
.img-preview { width:100px; height:100px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
<div class="card">
<h3 class="mb-4">✏️ Malzeme Güncelle</h3>

<?php if($mesaj != ''): ?>
<div class="alert alert-light text-dark"><?= $mesaj ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label>Malzeme Adı</label>
    <input type="text" name="ad" value="<?= htmlspecialchars($malzeme['ad']) ?>" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Adet</label>
    <input type="number" name="adet" value="<?= $malzeme['adet'] ?>" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Kategori</label>
    <select name="kategori_id" class="form-control" required>
      <?php
      $kategoriler->data_seek(0);
      while($k=$kategoriler->fetch_assoc()): ?>
        <option value="<?= $k['id'] ?>" <?= $malzeme['kategori_id']==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ad']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Lokasyon</label>
    <select name="lokasyon" class="form-control" required>
      <?php
      $lokasyonlar->data_seek(0);
      while($l=$lokasyonlar->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($l['ad']) ?>" <?= $malzeme['lokasyon']==$l['ad']?'selected':'' ?>><?= htmlspecialchars($l['ad']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Resim Yükle / Kamera ile Çek</label>
    <?php if($malzeme['resim'] && file_exists("uploads/".$malzeme['resim'])): ?>
      <img src="uploads/<?= $malzeme['resim'] ?>" class="img-preview"><br>
    <?php endif; ?>
    <input type="file" name="resim" class="form-control" accept="image/*" capture="environment">
  </div>
  <button type="submit" name="guncelle" class="btn btn-success">Güncelle</button>
</form>
</div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
</body>
</html>