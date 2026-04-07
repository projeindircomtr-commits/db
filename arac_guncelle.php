<?php
include "db.php";
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

if(!isset($_GET['id'])){ header("Location: araclar.php"); exit; }

$id = intval($_GET['id']);
$arac = $baglanti->query("SELECT * FROM araclar WHERE id=$id")->fetch_assoc();
if(!$arac){ die("Arac bulunamadi!"); }

$mesaj = '';
$mesaj_tip = '';

if(isset($_POST['guncelle'])){
    $marka   = $baglanti->real_escape_string($_POST['marka']);
    $model   = $baglanti->real_escape_string($_POST['model']);
    $plaka   = $baglanti->real_escape_string($_POST['plaka']);
    $sahip   = $baglanti->real_escape_string($_POST['sahip']);
    $telefon = $baglanti->real_escape_string($_POST['telefon']);

    $sql = "UPDATE araclar SET marka='$marka', model='$model', plaka='$plaka', sahip='$sahip', telefon='$telefon' WHERE id=$id";
    if($baglanti->query($sql)){
        $mesaj = "Arac basariyla guncellendi!";
        $mesaj_tip = "success";
        $arac = $baglanti->query("SELECT * FROM araclar WHERE id=$id")->fetch_assoc();
    } else {
        $mesaj = "Hata: ".$baglanti->error;
        $mesaj_tip = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#667eea">
<title>Arac Guncelle - Santiye</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f8f9fa; min-height:100vh; padding-bottom:40px; }
.container { max-width:600px; padding:20px; }
.card { border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1); border:none; overflow:hidden; margin-top:20px; }
.card-header { background:linear-gradient(135deg,#51cf66 0%,#40c057 100%); color:white; padding:25px; font-size:1.3rem; font-weight:700; border:none; }
.card-body { padding:30px; }
.form-label { font-weight:600; color:#333; margin-bottom:8px; font-size:0.95rem; }
.form-control { border-radius:8px; border:2px solid #e0e0e0; padding:12px 15px; font-size:1rem; transition:all 0.3s; }
.form-control:focus { border-color:#51cf66; box-shadow:0 0 0 0.2rem rgba(81,207,102,0.25); }
.form-group { margin-bottom:20px; }
.btn-guncelle { background:linear-gradient(135deg,#51cf66,#40c057); color:white; border:none; padding:12px 40px; border-radius:8px; font-weight:600; font-size:1rem; width:100%; transition:all 0.3s; cursor:pointer; margin-bottom:10px; }
.btn-guncelle:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(81,207,102,0.3); color:white; }
.btn-iptal { background:#e0e0e0; color:#333; border:none; padding:12px 40px; border-radius:8px; font-weight:600; font-size:1rem; width:100%; transition:all 0.3s; text-decoration:none; display:block; text-align:center; }
.btn-iptal:hover { background:#d0d0d0; color:#333; }
.alert { border-radius:8px; border:none; margin-bottom:20px; font-weight:500; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-danger { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-truck"></i> Arac Guncelle
        </div>
        <div class="card-body">

            <?php if($mesaj != ''): ?>
                <div class="alert alert-<?= $mesaj_tip ?>"><?= $mesaj ?></div>
            <?php endif; ?>

            <div id="offlineMesaj" class="alert alert-warning" style="display:none;">
                Cevrimdisisiniz! Guncelleme kaydedildi, internet baglantisi kurulunca gonderilecek.
            </div>

            <form id="aracGuncelleForm">
                <input type="hidden" id="arac_id" value="<?= $id ?>">

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-truck"></i> Marka
                    </label>
                    <input type="text" id="marka" class="form-control"
                        value="<?= htmlspecialchars($arac['marka']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-card-text"></i> Model
                    </label>
                    <input type="text" id="model" class="form-control"
                        value="<?= htmlspecialchars($arac['model']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-signpost"></i> Plaka
                    </label>
                    <input type="text" id="plaka" class="form-control"
                        value="<?= htmlspecialchars($arac['plaka']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-person-fill"></i> Sahip
                    </label>
                    <input type="text" id="sahip" class="form-control"
                        value="<?= htmlspecialchars($arac['sahip']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-telephone-fill"></i> Telefon
                    </label>
                    <input type="tel" id="telefon" class="form-control"
                        value="<?= htmlspecialchars($arac['telefon']) ?>" required>
                </div>

                <button type="submit" class="btn-guncelle">
                    <i class="bi bi-check-circle"></i> Guncelle
                </button>
                <a href="araclar.php" class="btn-iptal">
                    <i class="bi bi-x-circle"></i> Iptal
                </a>
            </form>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('aracGuncelleForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const data = {
        id:      document.getElementById('arac_id').value,
        marka:   document.getElementById('marka').value,
        model:   document.getElementById('model').value,
        plaka:   document.getElementById('plaka').value,
        sahip:   document.getElementById('sahip').value,
        telefon: document.getElementById('telefon').value
    };

    const sonuc = await veriKaydet('api.php?action=arac_update', data);

    if (sonuc.offline) {
        document.getElementById('offlineMesaj').style.display = 'block';
    } else if (sonuc.ok) {
        window.location.href = 'araclar.php';
    }
});
</script>

</body>
</html>