<?php 
include "db.php"; 
$id=$_GET['id'];

if($_POST){
    $miktar=$_POST['miktar']; 
    $tip=$_POST['tip']; 
    $kisi=$_POST['kisi'] ?? '';

    if($tip=="giris"){ 
        $baglanti->query("UPDATE malzemeler SET adet=adet+$miktar WHERE id=$id"); 
    }
    else{ 
        $baglanti->query("UPDATE malzemeler SET adet=adet-$miktar WHERE id=$id"); 
        $baglanti->query("INSERT INTO zimmet (malzeme_id,alan_kisi,miktar) VALUES ($id,'$kisi',$miktar)");
    }

    $baglanti->query("INSERT INTO stok_hareket (malzeme_id,islem,miktar) VALUES ($id,'$tip',$miktar)");
    header("Location:index.php");
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
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
<div class="card">
<h3 class="mb-4">📊 Stok İşlem</h3>

<form method="post">
  <div class="mb-3">
    <label>Miktar</label>
    <input type="number" name="miktar" class="form-control" placeholder="Miktar" required>
  </div>
  <div class="mb-3">
    <label>İşlem Tipi</label>
    <select name="tip" class="form-control" required>
      <option value="giris">Giriş</option>
      <option value="cikis">Çıkış / Zimmet</option>
    </select>
  </div>
  <div class="mb-3">
    <label>Kime Verildi (Çıkış durumunda)</label>
    <input type="text" name="kisi" class="form-control" placeholder="Kişinin Adı">
  </div>
  <button type="submit" class="btn btn-success">Kaydet</button>
</form>
</div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
</body>
</html>