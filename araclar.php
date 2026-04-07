<?php
require_once "db.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Kategorileri çek
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");

// Arama ve filtre
$ara         = isset($_GET['ara'])         ? $baglanti->real_escape_string($_GET['ara']) : '';
$kategori_id = $_GET['kategori_id'] ?? '';

// Toplam sayılar
$toplam_arac    = $baglanti->query("SELECT COUNT(*) as s FROM araclar")->fetch_assoc()['s'];
$kamera_var     = $baglanti->query("SELECT COUNT(*) as s FROM araclar WHERE camera='Var'")->fetch_assoc()['s'];
$gps_var        = $baglanti->query("SELECT COUNT(*) as s FROM araclar WHERE gps='Var'")->fetch_assoc()['s'];

// Araçları çek
$sql = "SELECT a.*, k.ad as kategori FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id WHERE 1";
if($kategori_id) $sql .= " AND a.kategori_id=" . (int)$kategori_id;
if($ara != '')   $sql .= " AND (a.marka LIKE '%$ara%' OR a.model LIKE '%$ara%' OR a.plaka LIKE '%$ara%' OR a.sahip LIKE '%$ara%')";
$sql .= " ORDER BY a.id DESC";
$araclar   = $baglanti->query($sql);
$arac_sayi = $araclar->num_rows;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Araçlar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    background: #f0f2f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
}

/* ── HERO ── */
.page-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 60%, #f093fb 100%);
    padding: 35px 30px 55px;
    position: relative;
    overflow: hidden;
}

.page-hero::before {
    content:''; position:absolute;
    top:-60px; right:-60px;
    width:250px; height:250px;
    background:rgba(255,255,255,0.07); border-radius:50%;
}

.page-hero::after {
    content:''; position:absolute;
    bottom:-80px; left:-40px;
    width:200px; height:200px;
    background:rgba(255,255,255,0.05); border-radius:50%;
}

.hero-circles span { position:absolute; border-radius:50%; background:rgba(255,255,255,0.06); }
.hero-circles span:nth-child(1){ width:150px; height:150px; top:10px; right:180px; }
.hero-circles span:nth-child(2){ width:80px;  height:80px;  bottom:15px; right:80px; }
.hero-circles span:nth-child(3){ width:110px; height:110px; top:-20px; left:40%; }

.hero-content { position:relative; z-index:2; }

.hero-label {
    font-size:0.78rem; color:rgba(255,255,255,0.7);
    font-weight:700; letter-spacing:2px;
    text-transform:uppercase; margin-bottom:6px;
    display:flex; align-items:center; gap:6px;
}

.hero-title {
    font-size:1.9rem; font-weight:900;
    color:white; margin-bottom:6px; line-height:1.2;
}

.hero-title span {
    background:linear-gradient(90deg,#fff,#ffd6ff);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}

.hero-sub { color:rgba(255,255,255,0.65); font-size:0.88rem; }

/* ── MİNİ STAT KARTLARI ── */
.mini-stats {
    margin-top: -30px;
    position: relative;
    z-index: 10;
    padding: 0 20px;
}

.mini-stat-card {
    background: white;
    border-radius: 16px;
    padding: 18px 15px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    display: flex; align-items: center; gap: 13px;
    transition: all 0.3s ease; height: 100%;
}

.mini-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.13);
}

.mini-stat-icon {
    width: 46px; height: 46px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: white; flex-shrink: 0;
}

.mini-stat-num { font-size:1.5rem; font-weight:900; color:#2c3e50; line-height:1; }
.mini-stat-lbl { font-size:0.72rem; color:#bbb; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }

/* ── ANA ALAN ── */
.main-wrap { padding: 25px 20px 40px; }

/* ── FİLTRE KARTI ── */
.filter-card {
    background: white;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    padding: 22px 25px;
    margin-bottom: 20px;
}

.filter-title {
    font-size:0.78rem; font-weight:700; color:#667eea;
    text-transform:uppercase; letter-spacing:1.5px;
    margin-bottom:15px; display:flex; align-items:center; gap:7px;
}

.form-control, .form-select {
    border-radius: 11px;
    border: 2px solid #e8e8e8;
    padding: 11px 15px;
    font-size: 0.88rem;
    transition: all 0.3s ease;
    background: #fafafa;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
    outline: none; background: white;
}

.btn-filtrele {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none; border-radius: 11px;
    padding: 11px 25px; font-weight: 700; color: white;
    transition: all 0.3s; width: 100%;
}

.btn-filtrele:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(102,126,234,0.4); color: white;
}

.btn-temizle {
    background: #f8f9ff;
    border: 2px solid #e8e8e8;
    border-radius: 11px;
    padding: 10px 18px; font-weight: 700; color: #999;
    transition: all 0.3s; width: 100%;
    text-decoration: none; display: block; text-align: center;
    font-size: 0.88rem;
}

.btn-temizle:hover { border-color: #667eea; color: #667eea; }

/* ── EXPORT ── */
.export-row {
    display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap;
}

.btn-export {
    flex: 1; min-width: 130px;
    border-radius: 11px; font-weight: 700;
    border: none; color: white;
    padding: 12px 18px; font-size: 0.85rem;
    cursor: pointer; transition: all 0.3s;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    text-decoration: none;
}

.btn-export:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); color: white; }
.btn-exp-add    { background: linear-gradient(135deg, #667eea, #764ba2); }
.btn-exp-excel  { background: linear-gradient(135deg, #51cf66, #40c057); }
.btn-exp-pdf    { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); }
.btn-exp-print  { background: linear-gradient(135deg, #868e96, #748089); }

/* ── SONUÇ BAŞLIĞI ── */
.result-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px; flex-wrap: wrap; gap: 8px;
}

.result-title {
    font-size: 0.95rem; font-weight: 800; color: #2c3e50;
    display: flex; align-items: center; gap: 8px;
}

.result-count {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white; padding: 3px 12px; border-radius: 20px;
    font-size: 0.78rem; font-weight: 700;
}

/* ── TABLO ── */
.table-card {
    background: white;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    overflow: hidden;
}

.scroll-hint {
    display: none; text-align: center;
    color: #bbb; font-size: 0.78rem;
    margin-bottom: 8px;
    animation: fadeInOut 2s ease-in-out infinite;
}

@keyframes fadeInOut { 0%,100%{opacity:0.4;} 50%{opacity:1;} }

.table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table { margin-bottom:0; min-width:900px; }

.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; font-weight: 700; border: none;
    padding: 16px 13px; text-align: center;
    font-size: 0.83rem; letter-spacing: 0.5px;
    white-space: nowrap;
}

.table tbody td {
    padding: 13px 13px; text-align: center;
    vertical-align: middle; border-bottom: 1px solid #f5f5f5;
    font-size: 0.86rem;
}

.table tbody tr { transition: all 0.25s ease; }
.table tbody tr:hover {
    background: linear-gradient(90deg, #f8f9ff, #f0f2f5);
}

/* Resim */
.img-container { display:flex; justify-content:center; align-items:center; }

.img-thumb {
    width:70px; height:70px; object-fit:cover;
    border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.12);
    border:3px solid #f0f0f0; cursor:pointer; transition:all 0.3s;
}

.img-thumb:hover {
    transform:scale(1.15) rotate(2deg);
    box-shadow:0 6px 20px rgba(102,126,234,0.3);
    border-color:#667eea;
}

.img-placeholder {
    width:70px; height:70px;
    background:linear-gradient(135deg,#f0f0f0,#e8e8e8);
    border-radius:12px; display:flex;
    align-items:center; justify-content:center;
    color:#ccc; font-size:1.8rem; border:3px dashed #ddd;
}

/* Badgeler */
.marka-ad { font-weight:700; color:#2c3e50; font-size:0.88rem; }

.plaka-badge {
    display:inline-block;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:white; padding:5px 13px; border-radius:8px;
    font-weight:800; font-size:0.82rem; letter-spacing:1.5px;
    box-shadow:0 2px 8px rgba(102,126,234,0.3);
}

.kategori-badge {
    background:linear-gradient(135deg,#51cf66,#40c057);
    color:white; padding:5px 11px; border-radius:8px;
    font-size:0.76rem; font-weight:600;
}

.sahip-badge {
    background:linear-gradient(135deg,#4dabf7,#339af0);
    color:white; padding:5px 11px; border-radius:8px;
    font-size:0.76rem; font-weight:600;
    display:inline-flex; align-items:center; gap:4px;
}

.tel-link {
    color:#667eea; font-weight:700; font-size:0.82rem;
    text-decoration:none; display:inline-flex; align-items:center; gap:4px;
}

.tel-link:hover { color:#764ba2; text-decoration:underline; }

.feature-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:20px;
    font-size:0.76rem; font-weight:700;
}

.feature-yes {
    background:linear-gradient(135deg,#51cf66,#40c057);
    color:white; box-shadow:0 2px 6px rgba(64,192,87,0.2);
}

.feature-no {
    background:#f0f0f0; color:#bbb;
}

/* İşlem butonları */
.btn-guncelle {
    background:#f8f9ff; color:#667eea;
    border:2px solid #667eea; border-radius:9px;
    padding:6px 12px; font-size:0.78rem;
    font-weight:700; transition:all 0.3s; margin:0 2px;
    display:inline-flex; align-items:center; gap:4px;
    text-decoration:none;
}

.btn-guncelle:hover {
    background:#667eea; color:white;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(102,126,234,0.4);
}

.btn-sil {
    background:#fff5f5; color:#ff6b6b;
    border:2px solid #ff6b6b; border-radius:9px;
    padding:6px 12px; font-size:0.78rem;
    font-weight:700; transition:all 0.3s; margin:0 2px;
    cursor:pointer;
    display:inline-flex; align-items:center; gap:4px;
}

.btn-sil:hover {
    background:#ff6b6b; color:white;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(255,107,107,0.4);
}

/* Aktif filtreler */
.active-filters { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }

.filter-tag {
    display:inline-flex; align-items:center; gap:6px;
    background:linear-gradient(135deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));
    border:1px solid rgba(102,126,234,0.25);
    color:#667eea; padding:5px 12px; border-radius:20px;
    font-size:0.78rem; font-weight:700;
}

/* Boş durum */
.no-data { text-align:center; padding:70px 20px; color:#ccc; }
.no-data i { font-size:4.5rem; display:block; margin-bottom:18px; }
.no-data h5 { color:#999; font-weight:700; margin-bottom:8px; font-size:1.1rem; }
.no-data p  { font-size:0.88rem; color:#bbb; }

/* ── RESPONSİVE ── */
@media(max-width:768px){
    .page-hero   { padding:28px 15px 48px; }
    .hero-title  { font-size:1.5rem; }
    .mini-stats  { padding:0 10px; }
    .main-wrap   { padding:20px 10px 30px; }
    .scroll-hint { display:block; }
    .export-row  { flex-direction:column; }
    .btn-export  { min-width:auto; width:100%; }
}

@media(max-width:576px){
    .hero-title  { font-size:1.3rem; }
    .table thead th, .table tbody td { padding:9px 7px; font-size:0.78rem; }
    .img-thumb   { width:50px; height:50px; }
    .img-placeholder { width:50px; height:50px; font-size:1.3rem; }
    .plaka-badge { padding:4px 8px; font-size:0.72rem; }
}

@media print {
    .page-hero, .mini-stats, .filter-card,
    .export-row, .scroll-hint,
    .btn-guncelle, .btn-sil { display:none !important; }
    .table-card { box-shadow:none; border-radius:0; }
    body { background:white; }
}
</style>
</head>
<body>
<script src="app.js"></script>
<?php include "header.php"; ?>

<!-- ══════════ HERO ══════════ -->
<div class="page-hero">
    <div class="hero-circles"><span></span><span></span><span></span></div>
    <div class="hero-content">
        <div class="hero-label"><i class="bi bi-truck"></i> Filo Yönetimi</div>
        <h1 class="hero-title">Araç <span>Listesi</span></h1>
        <p class="hero-sub">Tüm araçları görüntüleyin, filtreleyin ve yönetin.</p>
    </div>
</div>

<!-- ══════════ MİNİ STAT KARTLARI ══════════ -->
<div class="mini-stats">
    <div class="row g-3">
        <div class="col-md-4 col-4">
            <div class="mini-stat-card">
                <div class="mini-stat-icon" style="background:linear-gradient(135deg,#51cf66,#40c057);">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="mini-stat-num"><?= $toplam_arac ?></div>
                    <div class="mini-stat-lbl">Toplam Araç</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-4">
            <div class="mini-stat-card">
                <div class="mini-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div>
                    <div class="mini-stat-num"><?= $kamera_var ?></div>
                    <div class="mini-stat-lbl">Kameralı</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-4">
            <div class="mini-stat-card">
                <div class="mini-stat-icon" style="background:linear-gradient(135deg,#4dabf7,#339af0);">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <div class="mini-stat-num"><?= $gps_var ?></div>
                    <div class="mini-stat-lbl">GPS'li</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ ANA İÇERİK ══════════ -->
<div class="main-wrap">

    <!-- Filtre Kartı -->
    <div class="filter-card">
        <div class="filter-title"><i class="bi bi-funnel"></i> Filtrele & Ara</div>
        <form method="GET" class="row g-3">
            <div class="col-lg-5 col-md-6 col-12">
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#bbb;pointer-events:none;"></i>
                    <input type="text" name="ara" value="<?= htmlspecialchars($ara) ?>"
                           class="form-control" placeholder="Marka, Model, Plaka, Sahip ara..."
                           style="padding-left:38px;">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <select name="kategori_id" class="form-select">
                    <option value="">🏷️ Tüm Kategoriler</option>
                    <?php $kategoriler->data_seek(0); while($k=$kategoriler->fetch_assoc()): ?>
                        <option value="<?= $k['id'] ?>" <?= $kategori_id==$k['id']?'selected':'' ?>>
                            <?= htmlspecialchars($k['ad']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <button type="submit" class="btn-filtrele btn">
                    <i class="bi bi-search"></i> Ara
                </button>
            </div>
            <div class="col-lg-1 col-md-6 col-6">
                <a href="araclar.php" class="btn-temizle">
                    <i class="bi bi-x-circle"></i> Temizle
                </a>
            </div>
        </form>

        <!-- Aktif filtreler -->
        <?php if($ara != '' || $kategori_id != ''): ?>
        <div class="active-filters">
            <?php if($ara != ''): ?>
            <span class="filter-tag"><i class="bi bi-search"></i> "<?= htmlspecialchars($ara) ?>"</span>
            <?php endif; ?>
            <?php if($kategori_id != ''):
                $kn = $baglanti->query("SELECT ad FROM kategoriler WHERE id=".(int)$kategori_id)->fetch_assoc()['ad'] ?? ''; ?>
            <span class="filter-tag"><i class="bi bi-tag"></i> <?= htmlspecialchars($kn) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Export + Ekle Butonları -->
    <div class="export-row">
        <a href="arac_ekle.php" class="btn-export btn-exp-add">
            <i class="bi bi-plus-circle"></i> Yeni Araç
        </a>
        <button class="btn-export btn-exp-excel" onclick="exportTableToExcel('aracTable','araclar')">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </button>
        <button class="btn-export btn-exp-pdf" onclick="exportTableToPDF()">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </button>
        <button class="btn-export btn-exp-print" onclick="printTable()">
            <i class="bi bi-printer"></i> Yazdır
        </button>
    </div>

    <!-- Sonuç Başlığı -->
    <div class="result-head">
        <div class="result-title">
            <i class="bi bi-list-ul" style="color:#667eea;"></i>
            Araç Listesi
            <span class="result-count"><?= $arac_sayi ?> sonuç</span>
        </div>
    </div>

    <!-- Kaydırma İpucu -->
    <div class="scroll-hint">
        <i class="bi bi-arrow-left-right"></i> Tabloyu sağa/sola kaydırabilirsiniz
    </div>

    <!-- Tablo -->
    <div class="table-card">
        <?php if($arac_sayi > 0): ?>
        <div class="table-wrap">
        <table class="table table-hover" id="aracTable">
            <thead>
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-image"></i> Resim</th>
                    <th><i class="bi bi-truck"></i> Marka</th>
                    <th><i class="bi bi-card-text"></i> Model</th>
                    <th><i class="bi bi-signpost"></i> Plaka</th>
                    <th><i class="bi bi-camera-video"></i> Kamera</th>
                    <th><i class="bi bi-geo-alt"></i> GPS</th>
                    <th><i class="bi bi-person"></i> Sahip</th>
                    <th><i class="bi bi-telephone"></i> Telefon</th>
                    <th><i class="bi bi-tag"></i> Kategori</th>
                    <th><i class="bi bi-wrench"></i> İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while($a = $araclar->fetch_assoc()): ?>
                <tr id="arac-<?= $a['id'] ?>">
                    <td><strong style="color:#667eea; font-size:0.8rem;">#<?= $a['id'] ?></strong></td>
                    <td>
                        <div class="img-container">
                            <?php if(!empty($a['resim']) && file_exists("uploads/".$a['resim'])): ?>
                                <img src="uploads/<?= htmlspecialchars($a['resim']) ?>"
                                     class="img-thumb"
                                     alt="<?= htmlspecialchars($a['marka']) ?>"
                                     onclick="showImageModal('uploads/<?= htmlspecialchars($a['resim']) ?>','<?= htmlspecialchars($a['marka'].' '.$a['model']) ?>')">
                            <?php else: ?>
                                <div class="img-placeholder"><i class="bi bi-truck"></i></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="marka-ad"><?= htmlspecialchars($a['marka']) ?></td>
                    <td style="color:#555;"><?= htmlspecialchars($a['model']) ?></td>
                    <td><span class="plaka-badge"><?= htmlspecialchars($a['plaka']) ?></span></td>
                    <td>
                        <span class="feature-badge <?= (!empty($a['camera']) && $a['camera']=='Var') ? 'feature-yes' : 'feature-no' ?>">
                            <i class="bi bi-camera-video"></i>
                            <?= htmlspecialchars($a['camera'] ?: '-') ?>
                        </span>
                    </td>
                    <td>
                        <span class="feature-badge <?= (!empty($a['gps']) && $a['gps']=='Var') ? 'feature-yes' : 'feature-no' ?>">
                            <i class="bi bi-geo-alt"></i>
                            <?= htmlspecialchars($a['gps'] ?: '-') ?>
                        </span>
                    </td>
                    <td>
                        <span class="sahip-badge">
                            <i class="bi bi-person-fill"></i>
                            <?= htmlspecialchars($a['sahip'] ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <a href="tel:<?= htmlspecialchars($a['telefon'] ?? '') ?>" class="tel-link">
                            <i class="bi bi-telephone-fill"></i>
                            <?= htmlspecialchars($a['telefon'] ?? '-') ?>
                        </a>
                    </td>
                    <td><span class="kategori-badge"><?= htmlspecialchars($a['kategori'] ?? 'Kategorisiz') ?></span></td>
                    <td style="white-space:nowrap;">
                        <a href="arac_guncelle.php?id=<?= $a['id'] ?>" class="btn-guncelle">
                            <i class="bi bi-pencil"></i> Güncelle
                        </a>
                        <button class="btn-sil" onclick="silArac(<?= $a['id'] ?>)">
                            <i class="bi bi-trash"></i> Sil
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="no-data">
            <i class="bi bi-truck"></i>
            <h5>Araç Bulunamadı</h5>
            <p>Henüz araç eklenmemiş veya arama kriterlerinize uygun sonuç yok.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include "footer.php"; ?>

<!-- Resim Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden; border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2); border:none;">
                <h5 class="modal-title text-white fw-bold" id="imageModalTitle">
                    <i class="bi bi-truck me-2"></i>Araç Resmi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt=""
                     style="max-width:100%; max-height:75vh; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showImageModal(src, title){
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModalTitle').innerHTML = '<i class="bi bi-truck me-2"></i>' + title;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function silArac(id){
    if(confirm("Bu aracı silmek istediğine emin misin?")){
        fetch('arac_sil_ajax.php?id='+id)
        .then(r=>r.text())
        .then(d=>{
            if(d==="ok"){
                const row = document.getElementById('arac-'+id);
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(()=>row.remove(), 400);
            } else {
                alert("Hata: "+d);
            }
        })
        .catch(e=>alert("Hata: "+e));
    }
}

function exportTableToExcel(tableID, filename=''){
    let clone = document.getElementById(tableID).cloneNode(true);
    clone.querySelectorAll('tr').forEach(row=>{
        let cells = row.querySelectorAll('th,td');
        if(cells.length > 0) cells[cells.length-1].remove();
    });
    let wb = XLSX.utils.table_to_book(clone, {sheet:"Araçlar"});
    XLSX.writeFile(wb, filename+".xlsx");
}

function exportTableToPDF(){
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF('l','mm','a4');
    let clone = document.getElementById('aracTable').cloneNode(true);
    clone.querySelectorAll('tr').forEach(row=>{
        let cells = row.querySelectorAll('th,td');
        if(cells.length > 0) cells[cells.length-1].remove();
    });
    doc.setFontSize(16); doc.setFont(undefined,'bold');
    doc.text('Araçlar Listesi', 14, 12);
    doc.autoTable({
        html: clone, startY: 20,
        headStyles:{ fillColor:[102,126,234], textColor:255, fontStyle:'bold' },
        alternateRowStyles:{ fillColor:[245,245,250] }
    });
    doc.save('araclar.pdf');
}

function printTable(){
    let tbl = document.getElementById("aracTable");
    let w = window.open("");
    w.document.write(`<html><head><title>Araçlar</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body{font-family:'Segoe UI',sans-serif;margin:20px;}
        table{width:100%;border-collapse:collapse;}
        th,td{border:1px solid #ccc!important;padding:6px;text-align:center;vertical-align:middle;}
        img{width:45px;height:45px;object-fit:cover;border-radius:5px;}
        @media print{button,input{display:none;}}</style>
        </head><body><h3 style="margin-bottom:15px;">Araçlar Listesi</h3>`);
    w.document.write(tbl.outerHTML);
    w.document.write("</body></html>");
    w.document.close(); w.focus(); w.print();
}

// Scroll hint
const sc = document.querySelector('.scroll-hint');
const tw = document.querySelector('.table-wrap');
if(sc && tw){
    tw.addEventListener('scroll',()=>sc.style.display='none',{once:true});
    setTimeout(()=>{ if(sc){ sc.style.opacity='0'; setTimeout(()=>sc.style.display='none',500); } },4000);
}
</script>
</body>
</html>