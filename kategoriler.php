<?php 
// kategoriler.php
include "db.php"; 
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

$hata = '';
$basarili = '';

// Yeni kategori ekle
if($_POST && isset($_POST['kategori'])){
    $ad = $baglanti->real_escape_string($_POST['kategori']);
    if($baglanti->query("INSERT INTO kategoriler (ad) VALUES ('$ad')")){
        $basarili = "✅ Kategori başarıyla eklendi!";
    } else {
        $hata = "❌ Kategori eklenemedi! Hata: ".$baglanti->error;
    }
}

// Kategorileri çek
$kategoriler = $baglanti->query("SELECT * FROM kategoriler ORDER BY ad ASC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategoriler - PRO MAX</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #1a1a1a, #0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.container { max-width:600px; margin:50px auto; }
.card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:20px; box-shadow:0 8px 20px rgba(0,0,0,0.3); }
.form-control, .btn { border-radius:8px; }
.alert { border-radius:10px; }
.list-group-item { background: rgba(255,255,255,0.05); border:0; color:#fff; }
.list-group-item:hover { background: rgba(255,255,255,0.1); }
</style>
</head>
<body>

<?php include "header.php"; ?> <!-- Navbar include -->

<div class="container">
    <div class="card">
        <h3 class="text-center mb-4">📂 Kategoriler</h3>
        
        <?php if($hata != ''): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php elseif($basarili != ''): ?>
            <div class="alert alert-success"><?= $basarili ?></div>
        <?php endif; ?>
        
        <form method="post" class="d-flex mb-3">
            <input type="text" name="kategori" class="form-control" placeholder="Yeni Kategori" required>
            <button type="submit" class="btn btn-success ms-2">Ekle</button>
        </form>

        <ul class="list-group" id="kategori-listesi">
            <?php while($k=$kategoriler->fetch_assoc()): ?>
                <li class="list-group-item" id="kategori-<?= $k['id'] ?>">
                    <?= htmlspecialchars($k['ad']) ?>
                    <button class="btn btn-danger btn-sm" onclick="silKategori(<?= $k['id'] ?>)">Sil</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<?php include "footer.php"; ?> <!-- Footer include -->

<script>
// AJAX ile kategori silme
function silKategori(id){
    if(confirm("Silmek istediğine emin misin?")){
        fetch('kategori_sil_ajax.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            if(data === "ok"){
                // HTML listesinden kaldır
                document.getElementById('kategori-' + id).remove();
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