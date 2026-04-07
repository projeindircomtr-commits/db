<?php 
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

$hata = '';
$basarili = '';

// Yeni lokasyon ekle
if($_POST && isset($_POST['lokasyon'])){
    $ad = $baglanti->real_escape_string($_POST['lokasyon']);
    if($baglanti->query("INSERT INTO lokasyonlar (ad) VALUES ('$ad')")){
        $basarili = "Lokasyon başarıyla eklendi!";
    } else {
        $hata = "Lokasyon eklenemedi! Hata: ".$baglanti->error;
    }
}

// Lokasyonları çek
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar ORDER BY ad ASC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lokasyonlar - PRO MAX</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #1a1a1a, #0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.container { max-width:600px; margin:50px auto; }
.card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:20px; box-shadow:0 8px 20px rgba(0,0,0,0.3); }
.form-control, .btn { border-radius:8px; }
.alert { border-radius:10px; }
.list-group-item { display:flex; justify-content:space-between; align-items:center; }
</style>
</head>
<body>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
<?php include "header.php"; ?> <!-- Navbar include -->

<div class="container">
    <div class="card">
        <h3 class="text-center mb-4">Lokasyonlar</h3>
        
        <?php if($hata != ''): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php elseif($basarili != ''): ?>
            <div class="alert alert-success"><?= $basarili ?></div>
        <?php endif; ?>
        
        <form method="post" class="d-flex mb-3">
            <input type="text" name="lokasyon" class="form-control" placeholder="Yeni Lokasyon" required>
            <button type="submit" class="btn btn-success ms-2">Ekle</button>
        </form>

        <ul class="list-group" id="lokasyon-listesi">
            <?php while($l=$lokasyonlar->fetch_assoc()): ?>
                <li class="list-group-item" id="lokasyon-<?= $l['id'] ?>">
                    <?= $l['ad'] ?>
                    <button class="btn btn-danger btn-sm" onclick="sil(<?= $l['id'] ?>)">Sil</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<script>
// AJAX ile lokasyon silme
function sil(id){
    if(confirm("Silmek istediğine emin misin?")){
        fetch('lokasyon_sil_ajax.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            if(data === "ok"){
                // HTML listesinden kaldır
                document.getElementById('lokasyon-' + id).remove();
            } else {
                alert("Hata oluştu: " + data);
            }
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>