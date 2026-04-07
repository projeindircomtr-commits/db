<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

// --- Genel Sayılar ---
$malzeme_sayi   = $baglanti->query("SELECT COUNT(*) as toplam FROM malzemeler")->fetch_assoc()['toplam'];
$arac_sayi      = $baglanti->query("SELECT COUNT(*) as toplam FROM araclar")->fetch_assoc()['toplam'];
$kategori_sayi  = $baglanti->query("SELECT COUNT(*) as toplam FROM kategoriler")->fetch_assoc()['toplam'];
$lokasyon_sayi  = $baglanti->query("SELECT COUNT(*) as toplam FROM lokasyonlar")->fetch_assoc()['toplam'];
$toplam_stok    = $baglanti->query("SELECT SUM(adet) as toplam FROM malzemeler")->fetch_assoc()['toplam'] ?? 0;
$az_stok        = $baglanti->query("SELECT COUNT(*) as toplam FROM malzemeler WHERE adet <= 1")->fetch_assoc()['toplam'];

// --- Son eklenenler ---
$son_malzemeler = $baglanti->query("SELECT m.*, k.ad as kategori FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id ORDER BY m.id DESC LIMIT 5");
$son_araclar    = $baglanti->query("SELECT a.*, k.ad as kategori FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id ORDER BY a.id DESC LIMIT 5");

// --- Malzeme istatistikleri (kategori bazlı) ---
$malzeme_label = []; $malzeme_data = []; $malzeme_colors = [];
$kat_query = $baglanti->query("SELECT k.ad, SUM(m.adet) as toplam_adet FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id GROUP BY k.id ORDER BY toplam_adet DESC");
while($row = $kat_query->fetch_assoc()){
    $malzeme_label[] = $row['ad'] ?? 'Kategorisiz';
    $malzeme_data[]  = (int)$row['toplam_adet'];
}

// --- Araç istatistikleri (kategori bazlı) ---
$arac_label = []; $arac_data = [];
$arac_query = $baglanti->query("SELECT k.ad, COUNT(a.id) as toplam_arac FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id GROUP BY k.id ORDER BY toplam_arac DESC");
while($row = $arac_query->fetch_assoc()){
    $arac_label[] = $row['ad'] ?? 'Kategorisiz';
    $arac_data[]  = (int)$row['toplam_arac'];
}

// --- Lokasyon bazlı malzeme dağılımı ---
$lokasyon_label = []; $lokasyon_data = [];
$lok_query = $baglanti->query("SELECT lokasyon, COUNT(*) as sayi FROM malzemeler GROUP BY lokasyon ORDER BY sayi DESC");
while($row = $lok_query->fetch_assoc()){
    $lokasyon_label[] = $row['lokasyon'] ?: 'Belirtilmemiş';
    $lokasyon_data[]  = (int)$row['sayi'];
}

// --- Kategorilere göre malzeme çeşit sayısı ---
$cesit_label = []; $cesit_data = [];
$cesit_query = $baglanti->query("SELECT k.ad, COUNT(m.id) as cesit FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id GROUP BY k.id ORDER BY cesit DESC");
while($row = $cesit_query->fetch_assoc()){
    $cesit_label[] = $row['ad'] ?? 'Kategorisiz';
    $cesit_data[]  = (int)$row['cesit'];
}

// --- Araç kamera/GPS istatistikleri ---
$kamera_var = $baglanti->query("SELECT COUNT(*) as s FROM araclar WHERE camera='Var'")->fetch_assoc()['s'];
$kamera_yok = $arac_sayi - $kamera_var;
$gps_var    = $baglanti->query("SELECT COUNT(*) as s FROM araclar WHERE gps='Var'")->fetch_assoc()['s'];
$gps_yok    = $arac_sayi - $gps_var;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Şantiye Stok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .dashboard-container { padding: 30px 20px; }

    .page-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2c3e50;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title span {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* STAT KARTLARI */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--card-color);
        border-radius: 20px 20px 0 0;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
        background: var(--card-color);
        color: white;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-badge {
        position: absolute;
        top: 18px;
        right: 18px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: var(--card-color);
        color: white;
        opacity: 0.15;
        font-size: 2.5rem;
    }

    .stat-bg-icon {
        position: absolute;
        bottom: -10px;
        right: -5px;
        font-size: 5rem;
        opacity: 0.05;
        color: #2c3e50;
    }

    /* CHART KARTLARI */
    .chart-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }

    .chart-header {
        padding: 20px 25px 15px;
        border-bottom: 2px solid #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chart-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-title i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.1rem;
    }

    .chart-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .chart-body { padding: 20px 25px 25px; }

    /* SON EKLENENLER */
    .list-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .list-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .list-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .list-title i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .list-item {
        display: flex;
        align-items: center;
        padding: 14px 25px;
        border-bottom: 1px solid #f8f8f8;
        transition: all 0.2s ease;
        gap: 14px;
    }

    .list-item:last-child { border-bottom: none; }

    .list-item:hover {
        background: linear-gradient(90deg, #f8f9ff 0%, #f0f2f5 100%);
    }

    .list-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: white;
        flex-shrink: 0;
    }

    .list-info { flex: 1; min-width: 0; }

    .list-name {
        font-weight: 700;
        color: #2c3e50;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .list-sub {
        font-size: 0.78rem;
        color: #999;
        margin-top: 2px;
    }

    .list-right { text-align: right; flex-shrink: 0; }

    .adet-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.78rem;
    }

    .adet-badge.low {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    }

    .plaka-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.78rem;
        letter-spacing: 1px;
    }

    /* EKİPMAN DURUM KARTLARI */
    .durum-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        padding: 25px;
        margin-bottom: 25px;
    }

    .durum-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .durum-row:last-child { margin-bottom: 0; }

    .durum-label {
        font-weight: 700;
        color: #2c3e50;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .durum-label i { color: #667eea; }

    .progress {
        height: 10px;
        border-radius: 10px;
        background: #f0f0f0;
        flex: 1;
        margin: 0 15px;
        overflow: visible;
    }

    .progress-bar {
        border-radius: 10px;
        position: relative;
        transition: width 1.5s ease;
    }

    .durum-sayilar {
        font-size: 0.82rem;
        font-weight: 700;
        color: #999;
        white-space: nowrap;
    }

    /* AZ STOK UYARI */
    .uyari-card {
        background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
        border-radius: 20px;
        border: 2px solid #ffcdd2;
        padding: 20px 25px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(255,107,107,0.1); }
        50% { box-shadow: 0 4px 30px rgba(255,107,107,0.3); }
    }

    .uyari-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .uyari-text h6 {
        font-weight: 800;
        color: #c0392b;
        margin-bottom: 3px;
    }

    .uyari-text p {
        font-size: 0.85rem;
        color: #e74c3c;
        margin: 0;
    }

    .uyari-sayi {
        margin-left: auto;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* SECTION BAŞLIK */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 10px 0 20px;
    }

    .section-divider h5 {
        font-size: 1rem;
        font-weight: 800;
        color: #2c3e50;
        white-space: nowrap;
        margin: 0;
    }

    .section-divider::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, #e8e8e8, transparent);
        border-radius: 2px;
    }

    @media (max-width: 768px) {
        .dashboard-container { padding: 15px 10px; }
        .stat-number { font-size: 1.8rem; }
        .page-title { font-size: 1.3rem; }
        .chart-body { padding: 15px; }
    }
</style>
</head>
<body>
<script src="app.js"></script>
<?php include "header.php"; ?>

<div class="container-fluid dashboard-container">

    <div class="page-title">
        <i class="bi bi-bar-chart-line" style="background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i>
        <span>Şantiye Stok</span> Dashboard
    </div>

    <?php if($az_stok > 0): ?>
    <div class="uyari-card">
        <div class="uyari-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="uyari-text">
            <h6>⚠️ Az Stok Uyarısı!</h6>
            <p>Stok miktarı 1 veya altında olan malzeme bulunuyor. Lütfen kontrol edin.</p>
        </div>
        <div class="uyari-sayi"><?= $az_stok ?></div>
    </div>
    <?php endif; ?>

    <!-- STAT KARTLARI -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#667eea,#764ba2);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-number"><?= $malzeme_sayi ?></div>
                <div class="stat-label">Toplam Malzeme</div>
                <i class="bi bi-box-seam stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#51cf66,#40c057);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#51cf66,#40c057);">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-number"><?= $arac_sayi ?></div>
                <div class="stat-label">Toplam Araç</div>
                <i class="bi bi-truck stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#4dabf7,#339af0);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#4dabf7,#339af0);">
                    <i class="bi bi-tag"></i>
                </div>
                <div class="stat-number"><?= $kategori_sayi ?></div>
                <div class="stat-label">Kategori</div>
                <i class="bi bi-tag stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#f59f00,#f08c00);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#f59f00,#f08c00);">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-number"><?= $lokasyon_sayi ?></div>
                <div class="stat-label">Lokasyon</div>
                <i class="bi bi-geo-alt stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#20c997,#12b886);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#20c997,#12b886);">
                    <i class="bi bi-stack"></i>
                </div>
                <div class="stat-number"><?= number_format($toplam_stok) ?></div>
                <div class="stat-label">Toplam Stok</div>
                <i class="bi bi-stack stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--card-color: linear-gradient(135deg,#ff6b6b,#ee5a6f);">
                <div class="stat-icon" style="background: linear-gradient(135deg,#ff6b6b,#ee5a6f);">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?= $az_stok ?></div>
                <div class="stat-label">Az Stok</div>
                <i class="bi bi-exclamation-triangle stat-bg-icon"></i>
            </div>
        </div>
    </div>

    <!-- MALZEME GRAFİKLERİ -->
    <div class="section-divider">
        <h5><i class="bi bi-box-seam" style="color:#667eea;"></i> Malzeme İstatistikleri</h5>
    </div>
    <div class="row g-3 mb-2">
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-bar-chart"></i> Kategoriye Göre Stok Miktarı</div>
                    <span class="chart-badge">Bar</span>
                </div>
                <div class="chart-body">
                    <canvas id="malzemeBarChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-pie-chart"></i> Kategoriye Göre Dağılım</div>
                    <span class="chart-badge">Pasta</span>
                </div>
                <div class="chart-body">
                    <canvas id="malzemePieChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-geo-alt"></i> Lokasyona Göre Dağılım</div>
                    <span class="chart-badge">Doughnut</span>
                </div>
                <div class="chart-body">
                    <canvas id="lokasyonChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-layers"></i> Kategoriye Göre Malzeme Çeşidi</div>
                    <span class="chart-badge">Bar</span>
                </div>
                <div class="chart-body">
                    <canvas id="cesitBarChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Son Eklenen Malzemeler -->
        <div class="col-lg-8 col-md-12">
            <div class="list-card" style="margin-bottom:0;">
                <div class="list-header">
                    <div class="list-title"><i class="bi bi-clock-history"></i> Son Eklenen Malzemeler</div>
                    <a href="malzemeler.php" style="font-size:0.82rem; color:#667eea; font-weight:700; text-decoration:none;">
                        Tümünü Gör <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php if($son_malzemeler && $son_malzemeler->num_rows > 0):
                    while($m = $son_malzemeler->fetch_assoc()): ?>
                <div class="list-item">
                    <div class="list-avatar" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="list-info">
                        <div class="list-name"><?= htmlspecialchars($m['ad']) ?></div>
                        <div class="list-sub">
                            <i class="bi bi-tag" style="color:#667eea;"></i> <?= htmlspecialchars($m['kategori'] ?? 'Kategorisiz') ?>
                            &nbsp;|&nbsp;
                            <i class="bi bi-geo-alt" style="color:#4dabf7;"></i> <?= htmlspecialchars($m['lokasyon'] ?? '-') ?>
                        </div>
                    </div>
                    <div class="list-right">
                        <span class="adet-badge <?= $m['adet'] <= 1 ? 'low' : '' ?>"><?= $m['adet'] ?> adet</span>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:30px; text-align:center; color:#999;">Henüz malzeme yok.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ARAÇ GRAFİKLERİ -->
    <div class="section-divider" style="margin-top:15px;">
        <h5><i class="bi bi-truck" style="color:#51cf66;"></i> Araç İstatistikleri</h5>
    </div>
    <div class="row g-3 mb-2">
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-bar-chart"></i> Kategoriye Göre Araç Sayısı</div>
                    <span class="chart-badge" style="background:linear-gradient(135deg,#51cf66,#40c057);">Bar</span>
                </div>
                <div class="chart-body">
                    <canvas id="aracBarChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title"><i class="bi bi-pie-chart"></i> Araç Kategori Dağılımı</div>
                    <span class="chart-badge" style="background:linear-gradient(135deg,#51cf66,#40c057);">Pasta</span>
                </div>
                <div class="chart-body">
                    <canvas id="aracPieChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Kamera & GPS Durumu -->
        <div class="col-lg-4 col-md-6">
            <div class="durum-card" style="margin-bottom:0;">
                <div class="chart-title" style="margin-bottom:20px; font-size:0.95rem;">
                    <i class="bi bi-cpu" style="background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-size:1.1rem;"></i>
                    Araç Ekipman Durumu
                </div>

                <div class="durum-row">
                    <div class="durum-label"><i class="bi bi-camera-video"></i> Kamera</div>
                    <div class="progress">
                        <div class="progress-bar" style="background:linear-gradient(135deg,#667eea,#764ba2); width:<?= $arac_sayi > 0 ? round($kamera_var/$arac_sayi*100) : 0 ?>%"></div>
                    </div>
                    <div class="durum-sayilar"><?= $kamera_var ?> / <?= $arac_sayi ?></div>
                </div>

                <div class="durum-row">
                    <div class="durum-label"><i class="bi bi-geo-alt"></i> GPS</div>
                    <div class="progress">
                        <div class="progress-bar" style="background:linear-gradient(135deg,#51cf66,#40c057); width:<?= $arac_sayi > 0 ? round($gps_var/$arac_sayi*100) : 0 ?>%"></div>
                    </div>
                    <div class="durum-sayilar"><?= $gps_var ?> / <?= $arac_sayi ?></div>
                </div>

                <div style="margin-top:20px;">
                    <canvas id="kameraGpsChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <!-- Son Eklenen Araçlar -->
        <div class="col-12">
            <div class="list-card">
                <div class="list-header">
                    <div class="list-title"><i class="bi bi-clock-history"></i> Son Eklenen Araçlar</div>
                    <a href="araclar.php" style="font-size:0.82rem; color:#667eea; font-weight:700; text-decoration:none;">
                        Tümünü Gör <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="row g-0">
                <?php if($son_araclar && $son_araclar->num_rows > 0):
                    while($a = $son_araclar->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="list-item">
                        <div class="list-avatar" style="background:linear-gradient(135deg,#51cf66,#40c057);">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="list-info">
                            <div class="list-name"><?= htmlspecialchars($a['marka']) ?> <?= htmlspecialchars($a['model']) ?></div>
                            <div class="list-sub">
                                <i class="bi bi-person" style="color:#667eea;"></i> <?= htmlspecialchars($a['sahip'] ?? '-') ?>
                                &nbsp;|&nbsp;
                                <i class="bi bi-tag" style="color:#51cf66;"></i> <?= htmlspecialchars($a['kategori'] ?? 'Kategorisiz') ?>
                            </div>
                        </div>
                        <div class="list-right">
                            <span class="plaka-badge"><?= htmlspecialchars($a['plaka']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:30px; text-align:center; color:#999;">Henüz araç yok.</div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const COLORS = ['#667eea','#764ba2','#51cf66','#4dabf7','#f59f00','#ff6b6b','#20c997','#f06595','#74c0fc','#a9e34b'];

const chartDefaults = {
    responsive: true,
    animation: { duration: 1500, easing: 'easeOutQuart' },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(44,62,80,0.95)',
            titleColor: '#fff',
            bodyColor: '#ddd',
            padding: 12,
            cornerRadius: 10,
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1
        }
    }
};

// Malzeme Bar
new Chart(document.getElementById('malzemeBarChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($malzeme_label) ?>,
        datasets: [{
            label: 'Stok Miktarı',
            data: <?= json_encode($malzeme_data) ?>,
            backgroundColor: COLORS.map(c => c + 'cc'),
            borderColor: COLORS,
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { color: '#999' } },
            x: { grid: { display: false }, ticks: { color: '#666' } }
        }
    }
});

// Malzeme Pie
new Chart(document.getElementById('malzemePieChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($malzeme_label) ?>,
        datasets: [{
            data: <?= json_encode($malzeme_data) ?>,
            backgroundColor: COLORS,
            hoverOffset: 12,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            ...chartDefaults.plugins,
            legend: { display: true, position: 'bottom', labels: { color: '#666', padding: 12, font: { size: 11, weight: '600' } } }
        },
        cutout: '60%'
    }
});

// Lokasyon Chart
new Chart(document.getElementById('lokasyonChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($lokasyon_label) ?>,
        datasets: [{
            data: <?= json_encode($lokasyon_data) ?>,
            backgroundColor: ['#4dabf7','#f59f00','#20c997','#ff6b6b','#764ba2','#51cf66','#f06595','#74c0fc'],
            hoverOffset: 12,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            ...chartDefaults.plugins,
            legend: { display: true, position: 'bottom', labels: { color: '#666', padding: 12, font: { size: 11, weight: '600' } } }
        },
        cutout: '60%'
    }
});

// Malzeme Çeşit Bar
new Chart(document.getElementById('cesitBarChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($cesit_label) ?>,
        datasets: [{
            label: 'Çeşit Sayısı',
            data: <?= json_encode($cesit_data) ?>,
            backgroundColor: ['#4dabf7cc','#f59f00cc','#20c997cc','#ff6b6bcc','#764ba2cc'],
            borderColor: ['#4dabf7','#f59f00','#20c997','#ff6b6b','#764ba2'],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { color: '#999' } },
            x: { grid: { display: false }, ticks: { color: '#666' } }
        }
    }
});

// Araç Bar
new Chart(document.getElementById('aracBarChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($arac_label) ?>,
        datasets: [{
            label: 'Araç Sayısı',
            data: <?= json_encode($arac_data) ?>,
            backgroundColor: ['#51cf66cc','#20c997cc','#4dabf7cc','#f59f00cc','#667eeacc'],
            borderColor: ['#51cf66','#20c997','#4dabf7','#f59f00','#667eea'],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { color: '#999' } },
            x: { grid: { display: false }, ticks: { color: '#666' } }
        }
    }
});

// Araç Pie
new Chart(document.getElementById('aracPieChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($arac_label) ?>,
        datasets: [{
            data: <?= json_encode($arac_data) ?>,
            backgroundColor: ['#51cf66','#20c997','#4dabf7','#f59f00','#667eea','#ff6b6b'],
            hoverOffset: 12,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            ...chartDefaults.plugins,
            legend: { display: true, position: 'bottom', labels: { color: '#666', padding: 12, font: { size: 11, weight: '600' } } }
        },
        cutout: '60%'
    }
});

// Kamera & GPS Bar Chart
new Chart(document.getElementById('kameraGpsChart'), {
    type: 'bar',
    data: {
        labels: ['Kamera Var', 'Kamera Yok', 'GPS Var', 'GPS Yok'],
        datasets: [{
            data: [<?= $kamera_var ?>, <?= $kamera_yok ?>, <?= $gps_var ?>, <?= $gps_yok ?>],
            backgroundColor: ['#667eeacc','#ff6b6bcc','#51cf66cc','#f59f00cc'],
            borderColor: ['#667eea','#ff6b6b','#51cf66','#f59f00'],
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { color: '#999', stepSize: 1 } },
            x: { grid: { display: false }, ticks: { color: '#666', font: { size: 10 } } }
        }
    }
});
</script>
</body>
</html>