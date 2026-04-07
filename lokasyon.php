<?php 
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

$hata = '';
$basarili = '';

if($_POST && isset($_POST['lokasyon'])){
    $ad = $baglanti->real_escape_string($_POST['lokasyon']);
    if($baglanti->query("INSERT INTO lokasyonlar (ad) VALUES ('$ad')")){
        $basarili = "Lokasyon basariyla eklendi!";
    } else {
        $hata = "Lokasyon eklenemedi!";
    }
}

$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar ORDER BY ad ASC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lokasyonlar - Santiye</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f8f9fa; min-height:100vh; padding-bottom:40px; }
.container { max-width:600px; padding:20px; }
.card { border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1); border:none; overflow:hidden; margin-top:20px; }
.card-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:25px; font-size:1.3rem; font-weight:700; border:none; }
.card-body { padding:30px; }
.form-control { border-radius:8px; border:2px solid #e0e0e0; padding:12px 15px; font-size:1rem; transition:all 0.3s; }
.form-control:focus { border-color:#667eea; box-shadow:0 0 0 0.2rem rgba(102,126,234,0.25); }
.btn-ekle { background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; padding:12px 25px; border-radius:8px; font-weight:600; transition:all 0.3s; white-space:nowrap; cursor:pointer; }
.btn-ekle:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(102,126,234,0.3); color:white; }
.list-group-item { border:none; border-bottom:1px solid #f0f0f0; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; font-weight:600; color:#2c3e50; background:white; }
.list-group-item:hover { background:#f8f9ff; }
.list-group-item:last-child { border-bottom:none; }
.btn-sil { background:linear-gradient(135deg,#ff6b6b,#ee5a6f); color:white; border:none; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; transition:all 0.3s; cursor:pointer; }
.btn-sil:hover { transform:scale(1.05); box-shadow:0 4px 12px rgba(255,107,107,0.4); }
.alert { border-radius:8px; border:none; margin-bottom:20px; font-weight:500; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-danger { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
.bos-mesaj { text-align:center; color:#bbb; padding:30px; font-size:0.9rem; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-geo-alt"></i> Lokasyonlar
        </div>
        <div class="card-body">

            <?php if($hata != ''): ?>
                <div class="alert alert-danger"><?= $hata ?></div>
            <?php elseif($basarili != ''): ?>
                <div class="alert alert-success"><?= $basarili ?></div>
            <?php endif; ?>

            <div id="offlineMesaj" class="alert alert-warning" style="display:none;">
                Cevrimdisisiniz! Veri kaydedildi, internet baglantisi kurulunca gonderilecek.
            </div>

            <div class="d-flex gap-2 mb-4">
                <input type="text" id="lokasyonAd" class="form-control" placeholder="Yeni Lokasyon Adi" required>
                <button class="btn-ekle" onclick="lokasyonEkle()">
                    <i class="bi bi-plus-lg"></i> Ekle
                </button>
            </div>

            <ul class="list-group" id="lokasyon-listesi">
                <?php if($lokasyonlar && $lokasyonlar->num_rows > 0):
                    while($l=$lokasyonlar->fetch_assoc()): ?>
                <li class="list-group-item" id="lokasyon-<?= $l['id'] ?>">
                    <span>
                        <i class="bi bi-geo-alt-fill" style="color:#667eea;margin-right:8px;"></i>
                        <?= htmlspecialchars($l['ad']) ?>
                    </span>
                    <button class="btn-sil" onclick="silLokasyon(<?= $l['id'] ?>)">
                        <i class="bi bi-trash"></i> Sil
                    </button>
                </li>
                <?php endwhile;
                else: ?>
                <li class="bos-mesaj">Henuz lokasyon eklenmemis.</li>
                <?php endif; ?>
            </ul>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function lokasyonEkle() {
    const ad = document.getElementById('lokasyonAd').value.trim();
    if (!ad) { alert('Lokasyon adi bos olamaz!'); return; }

    const sonuc = await veriKaydet('api.php?action=lokasyon_add', { ad: ad });

    if (sonuc.offline) {
        document.getElementById('offlineMesaj').style.display = 'block';
        document.getElementById('lokasyonAd').value = '';
    } else if (sonuc.ok) {
        location.reload();
    }
}

function silLokasyon(id) {
    if (confirm("Silmek istedigine emin misin?")) {
        fetch('lokasyon_sil_ajax.php?id=' + id)
        .then(r => r.text())
        .then(data => {
            if (data === "ok") {
                document.getElementById('lokasyon-' + id).remove();
            } else {
                alert("Hata: " + data);
            }
        });
    }
}

document.getElementById('lokasyonAd').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') lokasyonEkle();
});
</script>

</body>
</html>