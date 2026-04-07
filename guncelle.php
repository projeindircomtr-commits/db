<?php
include "db.php";
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

if(!isset($_GET['id'])){ header("Location: malzemeler.php"); exit; }

$id = intval($_GET['id']);
$malzeme = $baglanti->query("SELECT * FROM malzemeler WHERE id=$id")->fetch_assoc();
if(!$malzeme){ die("Malzeme bulunamadi!"); }

$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

$mesaj = '';
$mesaj_tip = '';

if(isset($_POST['guncelle'])){
    $ad = $baglanti->real_escape_string($_POST['ad']);
    $adet = intval($_POST['adet']);
    $kategori_id = intval($_POST['kategori_id']);
    $lokasyon = $baglanti->real_escape_string($_POST['lokasyon']);
    $resimAdi = $malzeme['resim'];

    if(isset($_FILES['resim']) && $_FILES['resim']['error'] == 0){
        $hedefKlasor = 'uploads/';
        if(!is_dir($hedefKlasor)) mkdir($hedefKlasor, 0777, true);
        $resimAdi = time().'_'.preg_replace('/[^a-zA-Z0-9_.]/','_',$_FILES['resim']['name']);
        if(!move_uploaded_file($_FILES['resim']['tmp_name'], $hedefKlasor.$resimAdi)){
            $mesaj = "Resim yuklenemedi!";
            $mesaj_tip = "danger";
        }
    }

    $sql = "UPDATE malzemeler SET ad='$ad', adet='$adet', kategori_id='$kategori_id', lokasyon='$lokasyon', resim='$resimAdi' WHERE id=$id";
    if($baglanti->query($sql)){
        $mesaj = "Malzeme basariyla guncellendi!";
        $mesaj_tip = "success";
        $malzeme = $baglanti->query("SELECT * FROM malzemeler WHERE id=$id")->fetch_assoc();
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
<title>Malzeme Guncelle - Santiye</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f8f9fa; min-height:100vh; padding-bottom:40px; }
.container { max-width:600px; padding:20px; }
.card { border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1); border:none; overflow:hidden; margin-top:20px; }
.card-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:25px; font-size:1.3rem; font-weight:700; border:none; }
.card-body { padding:30px; }
.form-label { font-weight:600; color:#333; margin-bottom:8px; font-size:0.95rem; }
.form-control,.form-select { border-radius:8px; border:2px solid #e0e0e0; padding:12px 15px; font-size:1rem; transition:all 0.3s; }
.form-control:focus,.form-select:focus { border-color:#667eea; box-shadow:0 0 0 0.2rem rgba(102,126,234,0.25); }
.form-group { margin-bottom:20px; }
.btn-guncelle { background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; padding:12px 40px; border-radius:8px; font-weight:600; font-size:1rem; width:100%; transition:all 0.3s; cursor:pointer; margin-bottom:10px; }
.btn-guncelle:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(102,126,234,0.3); color:white; }
.btn-iptal { background:#e0e0e0; color:#333; border:none; padding:12px 40px; border-radius:8px; font-weight:600; font-size:1rem; width:100%; transition:all 0.3s; text-decoration:none; display:block; text-align:center; }
.btn-iptal:hover { background:#d0d0d0; color:#333; }
.alert { border-radius:8px; border:none; margin-bottom:20px; font-weight:500; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-danger { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
.img-preview { width:100px; height:100px; object-fit:cover; border-radius:10px; margin-bottom:10px; border:3px solid #667eea; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-pencil-square"></i> Malzeme Guncelle
        </div>
        <div class="card-body">

            <?php if($mesaj != ''): ?>
                <div class="alert alert-<?= $mesaj_tip ?>"><?= $mesaj ?></div>
            <?php endif; ?>

            <div id="offlineMesaj" class="alert alert-warning" style="display:none;">
                Cevrimdisisiniz! Guncelleme kaydedildi, internet baglantisi kurulunca gonderilecek.
            </div>

            <form id="guncelleForm" enctype="multipart/form-data">
                <input type="hidden" id="malzeme_id" value="<?= $id ?>">

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-box-seam"></i> Malzeme Adi
                    </label>
                    <input type="text" id="ad" class="form-control"
                        value="<?= htmlspecialchars($malzeme['ad']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-stack"></i> Adet
                    </label>
                    <input type="number" id="adet" class="form-control"
                        value="<?= $malzeme['adet'] ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-tag"></i> Kategori
                    </label>
                    <select id="kategori_id" class="form-select" required>
                        <?php $kategoriler->data_seek(0); while($k=$kategoriler->fetch_assoc()): ?>
                        <option value="<?= $k['id'] ?>"
                            <?= $malzeme['kategori_id']==$k['id']?'selected':'' ?>>
                            <?= htmlspecialchars($k['ad']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-geo-alt"></i> Lokasyon
                    </label>
                    <select id="lokasyon" class="form-select" required>
                        <?php $lokasyonlar->data_seek(0); while($l=$lokasyonlar->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($l['ad']) ?>"
                            <?= $malzeme['lokasyon']==$l['ad']?'selected':'' ?>>
                            <?= htmlspecialchars($l['ad']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-image"></i> Resim
                    </label>
                    <?php if($malzeme['resim'] && file_exists("uploads/".$malzeme['resim'])): ?>
                    <br><img src="uploads/<?= $malzeme['resim'] ?>" class="img-preview"><br>
                    <?php endif; ?>
                    <input type="file" name="resim" class="form-control mt-2"
                        accept="image/*" capture="environment">
                </div>

                <button type="submit" class="btn-guncelle">
                    <i class="bi bi-check-circle"></i> Guncelle
                </button>
                <a href="malzemeler.php" class="btn-iptal">
                    <i class="bi bi-x-circle"></i> Iptal
                </a>
            </form>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('guncelleForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const data = {
        id:          document.getElementById('malzeme_id').value,
        ad:          document.getElementById('ad').value,
        adet:        document.getElementById('adet').value,
        kategori_id: document.getElementById('kategori_id').value,
        lokasyon:    document.getElementById('lokasyon').value
    };

    const sonuc = await veriKaydet('api.php?action=malzeme_update', data);

    if (sonuc.offline) {
        document.getElementById('offlineMesaj').style.display = 'block';
    } else if (sonuc.ok) {
        window.location.href = 'malzemeler.php';
    }
});
</script>

</body>
</html>