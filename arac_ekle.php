<?php
require_once "db.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

$mesaj = '';
$mesaj_tip = '';

if(isset($_POST['kaydet'])){
    $marka       = $baglanti->real_escape_string($_POST['marka']);
    $model       = $baglanti->real_escape_string($_POST['model']);
    $plaka       = $baglanti->real_escape_string($_POST['plaka']);
    $sahip       = $baglanti->real_escape_string($_POST['sahip']);
    $telefon     = $baglanti->real_escape_string($_POST['telefon']);
    $camera      = $baglanti->real_escape_string($_POST['camera'] ?? 'Yok');
    $gps         = $baglanti->real_escape_string($_POST['gps'] ?? 'Yok');
    $kategori_id = intval($_POST['kategori_id'] ?? 0);

    $resim = '';

    // Kameradan çekilen base64 resim
    if(!empty($_POST['kamera_resim'])){
        $base64 = $_POST['kamera_resim'];
        $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $data   = base64_decode($base64);
        $resim  = uniqid('arac_') . '.jpg';
        file_put_contents("uploads/" . $resim, $data);
    }
    // Galeriden yüklenen resim
    elseif(isset($_FILES['resim']) && $_FILES['resim']['error'] == 0){
        $izin = ['image/jpeg','image/png','image/gif','image/webp'];
        if(in_array($_FILES['resim']['type'], $izin)){
            $uzanti = pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION);
            $resim  = uniqid('arac_') . '.' . $uzanti;
            move_uploaded_file($_FILES['resim']['tmp_name'], "uploads/" . $resim);
        } else {
            $mesaj     = "Sadece JPG, PNG, GIF, WEBP formatları desteklenir!";
            $mesaj_tip = "danger";
        }
    }

    if($mesaj == ''){
        $sql = "INSERT INTO araclar (marka, model, plaka, sahip, telefon, camera, gps, kategori_id, resim)
                VALUES ('$marka','$model','$plaka','$sahip','$telefon','$camera','$gps','$kategori_id','$resim')";
        if($baglanti->query($sql)){
            $mesaj     = "Araç başarıyla kaydedildi!";
            $mesaj_tip = "success";
        } else {
            $mesaj     = "Hata: " . $baglanti->error;
            $mesaj_tip = "danger";
        }
    }
}

$kategoriler = $baglanti->query("SELECT * FROM kategoriler ORDER BY ad ASC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#667eea">
<title>Araç Ekle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .container { padding: 30px 20px; max-width: 750px; }

    .page-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #2c3e50;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2rem;
    }

    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
        margin-bottom: 7px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e8e8e8;
        padding: 12px 16px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #fafafa;
        color: #2c3e50;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
        background: white;
    }

    .form-control::placeholder { color: #bbb; font-size: 0.88rem; }

    .input-icon-wrapper { position: relative; }
    .input-icon-wrapper i.field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 1rem;
        pointer-events: none;
    }
    .input-icon-wrapper .form-control,
    .input-icon-wrapper .form-select { padding-left: 42px; }

    /* Kamera Alanı */
    .camera-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
    }

    .camera-tab {
        flex: 1;
        padding: 12px;
        border-radius: 12px;
        border: 2px solid #e8e8e8;
        background: #fafafa;
        color: #999;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .camera-tab.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .camera-tab:hover:not(.active) {
        border-color: #667eea;
        color: #667eea;
    }

    /* Video / Önizleme */
    #videoContainer {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #000;
        display: none;
        margin-bottom: 15px;
    }

    #videoStream {
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        display: block;
        border-radius: 16px;
    }

    .camera-overlay {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn-cek {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        border: 5px solid white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 1.6rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }

    .btn-cek:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-cek:active { transform: scale(0.95); }

    .btn-kamera-cevir {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 3px solid white;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .btn-kamera-cevir:hover {
        background: rgba(255,255,255,0.4);
        transform: rotate(180deg);
    }

    /* Flash animasyonu */
    .flash-effect {
        position: absolute;
        inset: 0;
        background: white;
        border-radius: 16px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.1s ease;
    }

    .flash-effect.active { opacity: 1; }

    /* Galeri yükleme */
    .upload-area {
        border: 3px dashed #d0d0e8;
        border-radius: 16px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9ff;
        position: relative;
        overflow: hidden;
        display: none;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: #f0f2ff;
        transform: translateY(-2px);
    }

    .upload-area input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .upload-icon {
        font-size: 3rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: block;
        margin-bottom: 10px;
    }

    /* Önizleme */
    #previewContainer {
        display: none;
        text-align: center;
        animation: fadeIn 0.4s ease;
        margin-top: 15px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }

    #previewImage {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 16px;
        border: 4px solid #667eea;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    }

    .preview-label {
        display: inline-block;
        margin-top: 10px;
        background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        color: white;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .btn-yeniden {
        display: block;
        margin: 8px auto 0;
        background: none;
        border: none;
        color: #667eea;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-yeniden:hover { text-decoration: underline; }

    /* Toggle Switch */
    .toggle-group { display: flex; gap: 15px; flex-wrap: wrap; }
    .toggle-option { flex: 1; min-width: 120px; }
    .toggle-option input[type="radio"] { display: none; }
    .toggle-option label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 10px;
        border: 2px solid #e8e8e8;
        background: #fafafa;
        color: #999;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.3s ease;
        user-select: none;
    }
    .toggle-option input[type="radio"]:checked + label {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .toggle-option label:hover { border-color: #667eea; color: #667eea; }

    /* Alert */
    .alert-custom {
        border-radius: 12px;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 0.92rem;
        border: none;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
        animation: fadeIn 0.4s ease;
    }
    .alert-custom.success { background: linear-gradient(135deg, #d4edda, #c3e6cb); color: #155724; }
    .alert-custom.danger  { background: linear-gradient(135deg, #f8d7da, #f5c6cb); color: #721c24; }

    /* Butonlar */
    .btn-kaydet {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        padding: 14px 40px;
        font-weight: 700;
        color: white;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }
    .btn-kaydet:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-geri {
        background: #f8f9ff;
        border: 2px solid #667eea;
        border-radius: 12px;
        padding: 13px 30px;
        font-weight: 700;
        color: #667eea;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .btn-geri:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    canvas { display: none; }

    @media (max-width: 576px) {
        .form-card { padding: 20px 15px; }
        .container { padding: 15px 10px; }
        .btn-cek { width: 58px; height: 58px; font-size: 1.3rem; }
    }
</style>
</head>
<body>

<script src="app.js"></script>
<?php include "header.php"; ?>

<div class="container">

    <div class="page-title">
        <i class="bi bi-truck"></i>
        Yeni Araç Ekle
    </div>

    <?php if($mesaj != ''): ?>
    <div class="alert-custom <?= $mesaj_tip ?>">
        <i class="bi bi-<?= $mesaj_tip == 'success' ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
        <?= $mesaj ?>
    </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" id="aracForm">
        <input type="hidden" name="kamera_resim" id="kameraResimData">

        <!-- ARAÇ BİLGİLERİ -->
        <div class="form-card">
            <div class="section-title"><i class="bi bi-truck"></i> Araç Bilgileri</div>
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-12">
                    <label class="form-label">Marka <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="bi bi-truck field-icon"></i>
                        <input type="text" name="marka" class="form-control" placeholder="örn: Ford, Renault..." required>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="form-label">Model <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="bi bi-card-text field-icon"></i>
                        <input type="text" name="model" class="form-control" placeholder="örn: Transit, Kangoo..." required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Plaka <span class="text-danger">*</span></label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-signpost field-icon"></i>
                    <input type="text" name="plaka" class="form-control" placeholder="örn: 34 ABC 123" required>
                </div>
            </div>
            <div class="mb-1">
                <label class="form-label">Kategori</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-tag field-icon"></i>
                    <select name="kategori_id" class="form-select">
                        <option value="0">Kategori Seçin...</option>
                        <?php if($kategoriler && $kategoriler->num_rows > 0):
                            while($k = $kategoriler->fetch_assoc()): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['ad']) ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- SAHİP BİLGİLERİ -->
        <div class="form-card">
            <div class="section-title"><i class="bi bi-person"></i> Sahip Bilgileri</div>
            <div class="row g-3">
                <div class="col-md-6 col-12">
                    <label class="form-label">Sahip <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="bi bi-person-fill field-icon"></i>
                        <input type="text" name="sahip" class="form-control" placeholder="Araç sahibinin adı" required>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="form-label">Telefon <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="bi bi-telephone-fill field-icon"></i>
                        <input type="tel" name="telefon" class="form-control" placeholder="05XX XXX XX XX" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- EKİPMAN -->
        <div class="form-card">
            <div class="section-title"><i class="bi bi-cpu"></i> Ekipman Bilgileri</div>
            <div class="mb-4">
                <label class="form-label"><i class="bi bi-camera-video" style="color:#667eea;"></i> Kamera</label>
                <div class="toggle-group">
                    <div class="toggle-option">
                        <input type="radio" name="camera" id="camera_var" value="Var">
                        <label for="camera_var"><i class="bi bi-check-circle"></i> Var</label>
                    </div>
                    <div class="toggle-option">
                        <input type="radio" name="camera" id="camera_yok" value="Yok" checked>
                        <label for="camera_yok"><i class="bi bi-x-circle"></i> Yok</label>
                    </div>
                </div>
            </div>
            <div>
                <label class="form-label"><i class="bi bi-geo-alt" style="color:#667eea;"></i> GPS</label>
                <div class="toggle-group">
                    <div class="toggle-option">
                        <input type="radio" name="gps" id="gps_var" value="Var">
                        <label for="gps_var"><i class="bi bi-check-circle"></i> Var</label>
                    </div>
                    <div class="toggle-option">
                        <input type="radio" name="gps" id="gps_yok" value="Yok" checked>
                        <label for="gps_yok"><i class="bi bi-x-circle"></i> Yok</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESİM - KAMERA / GALERİ -->
        <div class="form-card">
            <div class="section-title"><i class="bi bi-camera"></i> Araç Fotoğrafı</div>

            <!-- Seçim Tabları -->
            <div class="camera-tabs">
                <div class="camera-tab active" id="tabKamera" onclick="tabSec('kamera')">
                    <i class="bi bi-camera-fill"></i> Kameradan Çek
                </div>
                <div class="camera-tab" id="tabGaleri" onclick="tabSec('galeri')">
                    <i class="bi bi-image"></i> Galeriden Seç
                </div>
            </div>

            <!-- Kamera Alanı -->
            <div id="kameraAlani">
                <div id="videoContainer">
                    <video id="videoStream" autoplay playsinline muted></video>
                    <div class="flash-effect" id="flashEffect"></div>
                    <div class="camera-overlay">
                        <button type="button" class="btn-kamera-cevir" onclick="kamerayCevir()" title="Kamerayı Çevir">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button type="button" class="btn-cek" onclick="fotoCek()" title="Fotoğraf Çek">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100" id="btnKameraAc" onclick="kameraAc()"
                    style="border-radius:12px; background:linear-gradient(135deg,#667eea,#764ba2); border:none; padding:14px; font-weight:700;">
                    <i class="bi bi-camera-fill me-2"></i> Kamerayı Aç
                </button>
            </div>

            <!-- Galeri Alanı -->
            <div class="upload-area" id="galeriAlani">
                <input type="file" name="resim" id="galeriInput" accept="image/*">
                <i class="bi bi-cloud-arrow-up upload-icon"></i>
                <div style="font-weight:700; color:#2c3e50; margin-bottom:5px;">Resim seçmek için tıklayın</div>
                <div style="color:#999; font-size:0.82rem;">JPG, PNG, WEBP • Maks. 5MB</div>
            </div>

            <!-- Önizleme -->
            <div id="previewContainer">
                <img id="previewImage" src="" alt="Önizleme">
                <span class="preview-label"><i class="bi bi-check-circle"></i> Fotoğraf Hazır</span>
                <button type="button" class="btn-yeniden" onclick="yenidenCek()">
                    <i class="bi bi-arrow-counterclockwise"></i> Yeniden Çek / Değiştir
                </button>
            </div>

            <canvas id="canvas"></canvas>
        </div>

        <!-- BUTONLAR -->
        <div class="d-flex gap-3 flex-wrap">
            <a href="araclar.php" class="btn-geri">
                <i class="bi bi-arrow-left"></i> Geri Dön
            </a>
            <button type="submit" name="kaydet" class="btn btn-kaydet">
                <i class="bi bi-check-circle me-2"></i> Aracı Kaydet
            </button>
        </div>

    </form>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let stream = null;
let aktifKamera = 'environment'; // arka kamera varsayılan
let aktifTab = 'kamera';

// Tab seçimi
function tabSec(tab) {
    aktifTab = tab;
    document.getElementById('tabKamera').classList.toggle('active', tab === 'kamera');
    document.getElementById('tabGaleri').classList.toggle('active', tab === 'galeri');
    document.getElementById('kameraAlani').style.display = tab === 'kamera' ? 'block' : 'none';
    document.getElementById('galeriAlani').style.display = tab === 'galeri' ? 'block' : 'none';
    document.getElementById('previewContainer').style.display = 'none';
    if(tab === 'kamera' && stream) kameraKapat();
}

// Kamerayı aç
async function kameraAc() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: aktifKamera, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false
        });
        const video = document.getElementById('videoStream');
        video.srcObject = stream;
        document.getElementById('videoContainer').style.display = 'block';
        document.getElementById('btnKameraAc').style.display = 'none';
        document.getElementById('previewContainer').style.display = 'none';
    } catch(err) {
        alert("Kamera açılamadı! Lütfen kamera iznini kontrol edin.\n\nHata: " + err.message);
    }
}

// Kamerayı çevir (ön/arka)
async function kamerayCevir() {
    aktifKamera = aktifKamera === 'environment' ? 'user' : 'environment';
    if(stream){
        stream.getTracks().forEach(t => t.stop());
    }
    await kameraAc();
}

// Fotoğraf çek
function fotoCek() {
    const video   = document.getElementById('videoStream');
    const canvas  = document.getElementById('canvas');
    const flash   = document.getElementById('flashEffect');

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');

    // Ön kameraysa ayna görüntüsünü düzelt
    if(aktifKamera === 'user'){
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
    }
    ctx.drawImage(video, 0, 0);

    // Flash animasyonu
    flash.classList.add('active');
    setTimeout(() => flash.classList.remove('active'), 150);

    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('kameraResimData').value = dataUrl;
    document.getElementById('previewImage').src = dataUrl;
    document.getElementById('previewContainer').style.display = 'block';

    // Kamerayı kapat
    kameraKapat();
}

// Kamerayı kapat
function kameraKapat() {
    if(stream){
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    document.getElementById('videoContainer').style.display = 'none';
    document.getElementById('btnKameraAc').style.display = 'block';
}

// Yeniden çek
function yenidenCek() {
    document.getElementById('previewContainer').style.display = 'none';
    document.getElementById('kameraResimData').value = '';
    document.getElementById('galeriInput').value = '';
    if(aktifTab === 'kamera'){
        kameraAc();
    }
}

// Galeriden seçince önizleme
document.getElementById('galeriInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(ev){
            document.getElementById('previewImage').src = ev.target.result;
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('kameraResimData').value = '';
        };
        reader.readAsDataURL(file);
    }
});

// Sayfa kapatılınca kamerayı durdur
window.addEventListener('beforeunload', () => {
    if(stream) stream.getTracks().forEach(t => t.stop());
});
</script>
</body>
</html>