<?php
// ekle.php - Malzeme Ekle
require_once "db.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Kategoriler ve lokasyonlar
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

$mesaj = '';
$mesaj_tip = '';

if(isset($_POST['kaydet'])){
    $ad = $baglanti->real_escape_string(trim($_POST['ad']));
    $adet = intval($_POST['adet']);
    $kategori_id = intval($_POST['kategori_id']);
    $lokasyon = $baglanti->real_escape_string(trim($_POST['lokasyon']));

    $resimAdi = null;

    // Resim yükleme kontrolü
    if(isset($_FILES['resim']) && $_FILES['resim']['error'] == 0){
        $hedefKlasor = 'uploads/';
        
        // Klasör kontrolü ve oluşturma
        if(!is_dir($hedefKlasor)){
            mkdir($hedefKlasor, 0777, true);
            chmod($hedefKlasor, 0777);
        }
        
        // Klasör yazılabilir mi kontrolü
        if(!is_writable($hedefKlasor)){
            $mesaj = "❌ Malzeme eklenmedi! uploads klasörü yazılabilir değil.";
            $mesaj_tip = "danger";
        } else {
            $dosya_uzantisi = strtolower(pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION));
            $izin_verilen = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            
            if(!in_array($dosya_uzantisi, $izin_verilen)){
                $mesaj = "❌ Malzeme eklenmedi! Sadece JPG, PNG, GIF ve WEBP dosyaları yüklenebilir.";
                $mesaj_tip = "danger";
            } else if($_FILES['resim']['size'] > 5242880){
                $mesaj = "❌ Malzeme eklenmedi! Dosya boyutu 5MB'dan büyük olamaz.";
                $mesaj_tip = "danger";
            } else {
                $resimAdi = 'malzeme_' . time() . '_' . rand(1000, 9999) . '.' . $dosya_uzantisi;
                $hedef = $hedefKlasor . $resimAdi;

                if(!move_uploaded_file($_FILES['resim']['tmp_name'], $hedef)){
                    $mesaj = "❌ Malzeme eklenmedi! Resim yüklenemedi.";
                    $mesaj_tip = "danger";
                    $resimAdi = null;
                } else {
                    chmod($hedef, 0644);
                }
            }
        }
    } else if(isset($_FILES['resim']) && $_FILES['resim']['error'] != 0){
        // Dosya seçilmedi ise devam et (opsiyonel)
        $mesaj_tip = "";
    }

    // Veritabanına kaydet (hata yoksa)
    if($mesaj_tip != 'danger'){
        $resim_kayit = $resimAdi ? "'".$baglanti->real_escape_string($resimAdi)."'" : "NULL";
        $sql = "INSERT INTO malzemeler (ad, adet, kategori_id, lokasyon, resim)
                VALUES ('$ad', '$adet', '$kategori_id', '$lokasyon', $resim_kayit)";
        
        if($baglanti->query($sql)){
            $mesaj = "✅ Malzeme başarıyla eklendi!";
            $mesaj_tip = "success";
            $_POST = array();
        } else {
            $mesaj = "❌ Malzeme eklenmedi! Veritabanı hatası.";
            $mesaj_tip = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#667eea">
    <meta name="description" content="Malzeme Ekle">
    <link rel="manifest" href="manifest.json">
    <title>Malzeme Ekle - Şantiye Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .container {
            max-width: 600px;
            padding: 20px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
            margin-top: 20px;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            font-size: 1.3rem;
            font-weight: 700;
            border: none;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control[type="file"] {
            cursor: pointer;
            padding: 15px;
        }

        .form-control[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
        }

        .form-control[type="file"]::file-selector-button:hover {
            opacity: 0.9;
        }

        .small-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            display: block;
        }

        .btn-kaydet {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .btn-kaydet:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-iptal {
            background: #e0e0e0;
            color: #333;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-iptal:hover {
            background: #d0d0d0;
            color: #333;
            text-decoration: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .preview-img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                max-width: 100%;
            }

            .card-body {
                padding: 20px;
            }

            .card-header {
                padding: 20px;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }

            .card-body {
                padding: 15px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control, .form-select {
                font-size: 0.95rem;
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-plus-circle"></i> Malzeme Ekle
        </div>
        <div class="card-body">
            
            <?php if($mesaj != ''): ?>
                <div class="alert alert-<?= $mesaj_tip ?>">
                    <?= $mesaj ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                
                <!-- Malzeme Adı -->
                <div class="form-group">
                    <label class="form-label" for="ad">
                        <i class="bi bi-box-seam"></i> Malzeme Adı <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ad"
                        name="ad" 
                        class="form-control" 
                        placeholder="Örn: Çimento, Tuğla, Demir..." 
                        required
                        value="<?= isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : '' ?>"
                    >
                </div>

                <!-- Adet -->
                <div class="form-group">
                    <label class="form-label" for="adet">
                        <i class="bi bi-stack"></i> Adet <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="adet"
                        name="adet" 
                        class="form-control" 
                        placeholder="0" 
                        min="0"
                        required
                        value="<?= isset($_POST['adet']) ? htmlspecialchars($_POST['adet']) : '' ?>"
                    >
                </div>

                <!-- Kategori -->
                <div class="form-group">
                    <label class="form-label" for="kategori_id">
                        <i class="bi bi-tag"></i> Kategori <span style="color: red;">*</span>
                    </label>
                    <select id="kategori_id" name="kategori_id" class="form-select" required>
                        <option value="">-- Kategori Seçiniz --</option>
                        <?php
                        $kategoriler->data_seek(0);
                        while($k=$kategoriler->fetch_assoc()): 
                        ?>
                            <option value="<?= $k['id'] ?>" <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['ad']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Lokasyon -->
                <div class="form-group">
                    <label class="form-label" for="lokasyon">
                        <i class="bi bi-geo-alt"></i> Lokasyon <span style="color: red;">*</span>
                    </label>
                    <select id="lokasyon" name="lokasyon" class="form-select" required>
                        <option value="">-- Lokasyon Seçiniz --</option>
                        <?php
                        $lokasyonlar->data_seek(0);
                        while($l=$lokasyonlar->fetch_assoc()): 
                        ?>
                            <option value="<?= htmlspecialchars($l['ad']) ?>" <?= (isset($_POST['lokasyon']) && $_POST['lokasyon'] == $l['ad']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($l['ad']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Resim Yükleme -->
                <div class="form-group">
                    <label class="form-label" for="resim">
                        <i class="bi bi-image"></i> Resim Yükle / Kamera ile Çek
                    </label>
                    <input 
                        type="file" 
                        id="resim"
                        name="resim" 
                        class="form-control" 
                        accept="image/*" 
                        capture="environment"
                        onchange="previewImage(this)"
                    >
                    <small class="small-text">
                        ✅ Mobil cihazlarda direkt kamera açılacak<br>
                        📸 Desteklenen: JPG, PNG, GIF, WEBP (Max 5MB)<br>
                        📌 Resim zorunlu değil
                    </small>
                    <img id="preview" class="preview-img" alt="Resim Önizlemesi">
                </div>

                <!-- Butonlar -->
                <button type="submit" name="kaydet" class="btn-kaydet">
                    <i class="bi bi-check-circle"></i> Kaydet
                </button>
                <a href="malzemeler.php" class="btn-iptal">
                    <i class="bi bi-x-circle"></i> İptal
                </a>

            </form>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>

<script>
// Resim önizlemesi
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>