<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

// Genel sayılar
$malzeme_sayi  = $baglanti->query("SELECT COUNT(*) as s FROM malzemeler")->fetch_assoc()['s'];
$arac_sayi     = $baglanti->query("SELECT COUNT(*) as s FROM araclar")->fetch_assoc()['s'];
$kategori_sayi = $baglanti->query("SELECT COUNT(*) as s FROM kategoriler")->fetch_assoc()['s'];
$lokasyon_sayi = $baglanti->query("SELECT COUNT(*) as s FROM lokasyonlar")->fetch_assoc()['s'];
$toplam_stok   = $baglanti->query("SELECT SUM(adet) as s FROM malzemeler")->fetch_assoc()['s'] ?? 0;
$az_stok       = $baglanti->query("SELECT COUNT(*) as s FROM malzemeler WHERE adet <= 1")->fetch_assoc()['s'];

// Son eklenen malzemeler
$son_malzemeler = $baglanti->query("SELECT m.*, k.ad as kategori FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id ORDER BY m.id DESC LIMIT 5");

// Son eklenen araçlar (sahip bilgisiyle)
$son_araclar = $baglanti->query("SELECT a.*, k.ad as kategori FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id ORDER BY a.id DESC LIMIT 5");

// Tüm araçlar (sahip kartları için)
$tum_araclar = $baglanti->query("SELECT a.*, k.ad as kategori FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id ORDER BY a.id DESC LIMIT 8");

// Stok uyarıları
$stok_uyarilari = $baglanti->query("SELECT * FROM malzemeler WHERE adet <= 1 ORDER BY adet ASC LIMIT 5");

// Malzeme grafik
$malzeme_label = []; $malzeme_data = [];
$kat_q = $baglanti->query("SELECT k.ad, SUM(m.adet) as toplam FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id GROUP BY k.id ORDER BY toplam DESC");
while($r = $kat_q->fetch_assoc()){ $malzeme_label[] = $r['ad'] ?? 'Kategorisiz'; $malzeme_data[] = (int)$r['toplam']; }

// Araç grafik
$arac_label = []; $arac_data = [];
$arac_q = $baglanti->query("SELECT k.ad, COUNT(a.id) as toplam FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id GROUP BY k.id ORDER BY toplam DESC");
while($r = $arac_q->fetch_assoc()){ $arac_label[] = $r['ad'] ?? 'Kategorisiz'; $arac_data[] = (int)$r['toplam']; }

// Lokasyon dağılımı
$lok_label = []; $lok_data = [];
$lok_q = $baglanti->query("SELECT lokasyon, COUNT(*) as sayi FROM malzemeler GROUP BY lokasyon ORDER BY sayi DESC LIMIT 6");
while($r = $lok_q->fetch_assoc()){ $lok_label[] = $r['lokasyon'] ?: 'Belirtilmemiş'; $lok_data[] = (int)$r['sayi']; }

// Kamera & GPS
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
<meta name="theme-color" content="#667eea">
<title>Şantiye Stok - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{ margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    min-height: 100vh;
}

/* ── HERO ── */
.hero-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 60%, #f093fb 100%);
    padding: 45px 30px 60px;
    position: relative;
    overflow: hidden;
}

.hero-banner::before {
    content:''; position:absolute;
    top:-80px; right:-80px;
    width:320px; height:320px;
    background:rgba(255,255,255,0.07); border-radius:50%;
}

.hero-banner::after {
    content:''; position:absolute;
    bottom:-100px; left:-60px;
    width:280px; height:280px;
    background:rgba(255,255,255,0.05); border-radius:50%;
}

.hero-circles span { position:absolute; border-radius:50%; background:rgba(255,255,255,0.06); }
.hero-circles span:nth-child(1){ width:180px; height:180px; top:10px; right:200px; }
.hero-circles span:nth-child(2){ width:90px;  height:90px;  bottom:20px; right:100px; }
.hero-circles span:nth-child(3){ width:130px; height:130px; top:-30px; left:40%; }

.hero-content { position:relative; z-index:2; }

.hero-greeting {
    font-size:0.9rem; color:rgba(255,255,255,0.75);
    font-weight:600; letter-spacing:2px;
    text-transform:uppercase; margin-bottom:8px;
}

.hero-title {
    font-size:2.2rem; font-weight:900;
    color:white; margin-bottom:8px; line-height:1.2;
}

.hero-title span {
    background:linear-gradient(90deg,#fff,#ffd6ff);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}

.hero-actions {
    display:flex; gap:12px; flex-wrap:wrap; margin-top:25px;
}

.hero-btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:12px 22px; border-radius:50px;
    font-weight:700; font-size:0.88rem;
    text-decoration:none; transition:all 0.3s ease;
    border:none; cursor:pointer;
}

.hero-btn-primary {
    background:white; color:#667eea;
    box-shadow:0 4px 15px rgba(0,0,0,0.15);
}

.hero-btn-primary:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 25px rgba(0,0,0,0.2); color:#764ba2;
}

.hero-btn-outline {
    background:rgba(255,255,255,0.15); color:white;
    border:2px solid rgba(255,255,255,0.4);
    backdrop-filter:blur(5px);
}

.hero-btn-outline:hover {
    background:rgba(255,255,255,0.25);
    transform:translateY(-3px); color:white;
}

/* ── STAT KARTLARI ── */
.stats-row {
    margin-top:-35px; position:relative;
    z-index:10; padding:0 20px;
}

.stat-card {
    background:white; border-radius:20px;
    padding:22px 18px;
    box-shadow:0 8px 30px rgba(0,0,0,0.1);
    transition:all 0.3s ease; height:100%;
    position:relative; overflow:hidden;
    cursor:pointer; text-decoration:none; display:block;
}

.stat-card::after {
    content:''; position:absolute;
    bottom:0; left:0; right:0; height:4px;
    background:var(--clr);
    border-radius:0 0 20px 20px;
}

.stat-card:hover {
    transform:translateY(-8px);
    box-shadow:0 16px 45px rgba(0,0,0,0.15);
}

.stat-icon-wrap {
    width:52px; height:52px; border-radius:15px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; color:white;
    background:var(--clr); margin-bottom:14px;
    box-shadow:0 4px 15px rgba(0,0,0,0.15);
}

.stat-num { font-size:2rem; font-weight:900; color:#2c3e50; line-height:1; margin-bottom:4px; }
.stat-lbl { font-size:0.78rem; color:#aaa; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
.stat-bg  { position:absolute; right:-10px; bottom:-10px; font-size:5rem; opacity:0.04; color:#000; }

/* ── ARAÇ SAHİP KARTLARI ── */
.arac-sahip-section {
    padding: 0 15px;
    margin-top: 30px;
    margin-bottom: 5px;
}

.arac-scroll-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 10px;
}

.arac-scroll-wrap::-webkit-scrollbar { height: 5px; }
.arac-scroll-wrap::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 5px; }
.arac-scroll-wrap::-webkit-scrollbar-thumb { background: #667eea; border-radius: 5px; }

.arac-cards-row {
    display: flex;
    gap: 15px;
    width: max-content;
    padding: 5px 2px 5px;
}

.arac-sahip-card {
    width: 240px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.arac-sahip-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 20px 20px 0 0;
}

.arac-sahip-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 35px rgba(102,126,234,0.2);
}

.arac-card-plaka {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 14px;
    box-shadow: 0 3px 10px rgba(102,126,234,0.3);
}

.arac-card-marka {
    font-size: 1rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.arac-card-marka i { color: #667eea; }

.arac-card-divider {
    height: 1px;
    background: #f0f0f0;
    margin-bottom: 14px;
}

.arac-card-sahip {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.sahip-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #51cf66, #40c057);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1rem; flex-shrink: 0;
}

.sahip-info-name {
    font-weight: 700; color: #2c3e50; font-size: 0.88rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.sahip-info-label { font-size: 0.72rem; color: #bbb; }

.arac-card-tel {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9ff;
    border-radius: 10px;
    padding: 8px 12px;
}

.arac-card-tel i { color: #667eea; font-size: 0.9rem; }
.arac-card-tel a {
    color: #667eea; font-weight: 700; font-size: 0.85rem;
    text-decoration: none;
}
.arac-card-tel a:hover { text-decoration: underline; }

.arac-card-badges {
    display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap;
}

.mini-badge {
    padding: 3px 9px; border-radius: 20px;
    font-size: 0.7rem; font-weight: 700; color: white;
}

/* ── SEKSİYON BAŞLIK ── */
.sec-head {
    display:flex; align-items:center; justify-content:space-between;
    margin: 25px 0 15px;
}

.sec-title {
    font-size:1.05rem; font-weight:800; color:#2c3e50;
    display:flex; align-items:center; gap:9px;
}

.sec-title i {
    width:32px; height:32px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; color:white;
    background:linear-gradient(135deg,#667eea,#764ba2);
}

.sec-link {
    font-size:0.82rem; font-weight:700; color:#667eea;
    text-decoration:none; display:flex; align-items:center; gap:4px;
    transition:gap 0.2s;
}

.sec-link:hover { gap:8px; color:#764ba2; }

/* ── CHART KART ── */
.chart-card {
    background:white; border-radius:20px;
    box-shadow:0 4px 20px rgba(0,0,0,0.07);
    overflow:hidden; margin-bottom:20px;
    transition:all 0.3s;
}

.chart-card:hover { box-shadow:0 8px 30px rgba(0,0,0,0.12); }

.chart-card-head {
    padding:18px 22px 12px; border-bottom:2px solid #f5f5f5;
    display:flex; align-items:center; justify-content:space-between;
}

.chart-card-title {
    font-size:0.9rem; font-weight:700; color:#2c3e50;
    display:flex; align-items:center; gap:8px;
}

.chart-card-title i { color:#667eea; font-size:1rem; }
.chart-card-body { padding:18px 22px 22px; }

.chart-pill {
    font-size:0.72rem; font-weight:700;
    padding:4px 12px; border-radius:20px; color:white;
    background:linear-gradient(135deg,#667eea,#764ba2);
}

/* ── LİSTE KART ── */
.list-card {
    background:white; border-radius:20px;
    box-shadow:0 4px 20px rgba(0,0,0,0.07);
    overflow:hidden; margin-bottom:20px;
}

.list-card-head {
    padding:18px 22px; border-bottom:2px solid #f5f5f5;
    display:flex; align-items:center; justify-content:space-between;
}

.list-item {
    display:flex; align-items:center;
    padding:13px 22px; border-bottom:1px solid #f8f8f8;
    gap:13px; transition:background 0.2s;
}

.list-item:last-child { border-bottom:none; }
.list-item:hover { background:#f8f9ff; }

.l-avatar {
    width:40px; height:40px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; color:white; flex-shrink:0;
}

.l-info { flex:1; min-width:0; }
.l-name { font-weight:700; color:#2c3e50; font-size:0.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.l-sub  { font-size:0.76rem; color:#bbb; margin-top:2px; }

.pill { padding:4px 11px; border-radius:20px; font-size:0.75rem; font-weight:700; color:white; flex-shrink:0; }
.pill-purple { background:linear-gradient(135deg,#667eea,#764ba2); }
.pill-green  { background:linear-gradient(135deg,#51cf66,#40c057); }
.pill-red    { background:linear-gradient(135deg,#ff6b6b,#ee5a6f); }
.pill-blue   { background:linear-gradient(135deg,#4dabf7,#339af0); }

/* ── UYARI ── */
.uyari-banner {
    background:linear-gradient(135deg,#fff5f5,#ffe8e8);
    border:2px solid #ffcdd2; border-radius:18px;
    padding:18px 22px; margin-bottom:20px;
    display:flex; align-items:center; gap:15px;
    animation:glow 2.5s ease-in-out infinite;
}

@keyframes glow {
    0%,100%{ box-shadow:0 4px 20px rgba(255,107,107,0.1); }
    50%    { box-shadow:0 4px 30px rgba(255,107,107,0.35); }
}

.uyari-ic {
    width:48px; height:48px; border-radius:14px;
    background:linear-gradient(135deg,#ff6b6b,#ee5a6f);
    display:flex; align-items:center; justify-content:center;
    color:white; font-size:1.3rem; flex-shrink:0;
}

.uyari-text h6 { font-weight:800; color:#c0392b; margin-bottom:3px; font-size:0.95rem; }
.uyari-text p  { font-size:0.82rem; color:#e74c3c; margin:0; }

.uyari-num {
    margin-left:auto; width:42px; height:42px; border-radius:50%;
    background:linear-gradient(135deg,#ff6b6b,#ee5a6f);
    color:white; display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; font-weight:900; flex-shrink:0;
}

/* ── EKİPMAN ── */
.ekipman-card {
    background:white; border-radius:20px;
    box-shadow:0 4px 20px rgba(0,0,0,0.07);
    padding:22px; margin-bottom:20px;
}

.eq-row { display:flex; align-items:center; margin-bottom:18px; gap:12px; }
.eq-row:last-of-type { margin-bottom:0; }

.eq-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:white; flex-shrink:0; }
.eq-label { font-weight:700; color:#2c3e50; font-size:0.85rem; min-width:60px; }
.eq-bar   { flex:1; height:10px; background:#f0f0f0; border-radius:10px; overflow:hidden; }
.eq-fill  { height:100%; border-radius:10px; transition:width 1.5s ease; }
.eq-val   { font-size:0.78rem; font-weight:700; color:#999; white-space:nowrap; }

/* ── HIZLI ERİŞİM ── */
.quick-grid {
    display:grid; grid-template-columns:repeat(2,1fr);
    gap:12px; margin-bottom:20px;
}

.quick-btn {
    background:white; border-radius:16px; padding:20px 15px;
    text-align:center; text-decoration:none;
    box-shadow:0 4px 15px rgba(0,0,0,0.07);
    transition:all 0.3s; display:block;
}

.quick-btn:hover { transform:translateY(-5px); box-shadow:0 10px 30px rgba(0,0,0,0.12); }
.quick-btn i     { display:block; font-size:1.8rem; margin-bottom:8px; }
.quick-btn span  { font-size:0.8rem; font-weight:700; color:#2c3e50; }

.main-wrap { padding:0 15px 40px; }

.dash-footer { text-align:center; padding:20px; color:#bbb; font-size:0.8rem; }

/* ── SCROLL HİNT ── */
.scroll-hint {
    text-align:center; color:#bbb; font-size:0.78rem;
    margin-bottom:8px;
    animation:fadeInOut 2s ease-in-out infinite;
    display:none;
}

@keyframes fadeInOut { 0%,100%{opacity:0.4;} 50%{opacity:1;} }

@media(max-width:768px){
    .hero-title    { font-size:1.5rem; }
    .stats-row     { padding:0 10px; }
    .stat-num      { font-size:1.6rem; }
    .hero-banner   { padding:35px 20px 55px; }
    .quick-grid    { grid-template-columns:repeat(4,1fr); }
    .arac-sahip-section { padding:0 10px; }
    .scroll-hint   { display:block; }
}

@media(max-width:576px){
    .hero-title    { font-size:1.3rem; }
    .hero-actions  { gap:8px; }
    .hero-btn      { padding:10px 16px; font-size:0.82rem; }
    .quick-grid    { grid-template-columns:repeat(2,1fr); }
    .stat-num      { font-size:1.4rem; }
}
</style>
</head>
<body>
<?php include "header.php"; ?>

<!-- ══════════ HERO ══════════ -->
<div class="hero-banner">
    <div class="hero-circles"><span></span><span></span><span></span></div>
    <div class="hero-content">
        <div class="hero-greeting"><i class="bi bi-building"></i> &nbsp;Şantiye Stok Yönetimi</div>
        <h1 class="hero-title">Hoş Geldiniz! 👋</h1>
        <div class="hero-actions">
            <a href="malzemeler.php" class="hero-btn hero-btn-primary">
                <i class="bi bi-box-seam"></i> Malzemelere Git
            </a>
            <a href="araclar.php" class="hero-btn hero-btn-outline">
                <i class="bi bi-truck"></i> Araçlara Git
            </a>
            <a href="arac_ekle.php" class="hero-btn hero-btn-outline">
                <i class="bi bi-plus-circle"></i> Araç Ekle
            </a>
        </div>
    </div>
</div>

<!-- ══════════ STAT KARTLARI ══════════ -->
<div class="stats-row">
    <div class="row g-3">
        <div class="col-xl-2 col-md-4 col-6">
            <a href="malzemeler.php" class="stat-card" style="--clr:linear-gradient(135deg,#667eea,#764ba2);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#667eea,#764ba2);"><i class="bi bi-box-seam"></i></div>
                <div class="stat-num"><?= $malzeme_sayi ?></div>
                <div class="stat-lbl">Malzeme</div>
                <i class="bi bi-box-seam stat-bg"></i>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="araclar.php" class="stat-card" style="--clr:linear-gradient(135deg,#51cf66,#40c057);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#51cf66,#40c057);"><i class="bi bi-truck"></i></div>
                <div class="stat-num"><?= $arac_sayi ?></div>
                <div class="stat-lbl">Araç</div>
                <i class="bi bi-truck stat-bg"></i>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--clr:linear-gradient(135deg,#4dabf7,#339af0);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#4dabf7,#339af0);"><i class="bi bi-tag"></i></div>
                <div class="stat-num"><?= $kategori_sayi ?></div>
                <div class="stat-lbl">Kategori</div>
                <i class="bi bi-tag stat-bg"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--clr:linear-gradient(135deg,#f59f00,#f08c00);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#f59f00,#f08c00);"><i class="bi bi-geo-alt"></i></div>
                <div class="stat-num"><?= $lokasyon_sayi ?></div>
                <div class="stat-lbl">Lokasyon</div>
                <i class="bi bi-geo-alt stat-bg"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--clr:linear-gradient(135deg,#20c997,#12b886);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#20c997,#12b886);"><i class="bi bi-stack"></i></div>
                <div class="stat-num"><?= number_format($toplam_stok) ?></div>
                <div class="stat-lbl">Toplam Stok</div>
                <i class="bi bi-stack stat-bg"></i>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="--clr:linear-gradient(135deg,#ff6b6b,#ee5a6f);">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#ff6b6b,#ee5a6f);"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-num"><?= $az_stok ?></div>
                <div class="stat-lbl">Az Stok</div>
                <i class="bi bi-exclamation-triangle stat-bg"></i>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ ARAÇ SAHİP KARTLARI ══════════ -->
<div class="arac-sahip-section">
    <div class="sec-head">
        <div class="sec-title">
            <i class="bi bi-truck"></i> Araç Sahipleri
        </div>
        <a href="araclar.php" class="sec-link">Tümünü Gör <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="scroll-hint"><i class="bi bi-arrow-left-right"></i> Kaydırabilirsiniz</div>
    <div class="arac-scroll-wrap">
        <div class="arac-cards-row">
            <?php if($tum_araclar && $tum_araclar->num_rows > 0):
                while($a = $tum_araclar->fetch_assoc()): ?>
            <div class="arac-sahip-card">
                <!-- Plaka -->
                <div class="arac-card-plaka">
                    <?= htmlspecialchars($a['plaka']) ?>
                </div>
                <!-- Marka Model -->
                <div class="arac-card-marka">
                    <i class="bi bi-truck"></i>
                    <?= htmlspecialchars($a['marka'] . ' ' . $a['model']) ?>
                </div>
                <div class="arac-card-divider"></div>
                <!-- Sahip -->
                <div class="arac-card-sahip">
                    <div class="sahip-avatar"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <div class="sahip-info-name"><?= htmlspecialchars($a['sahip'] ?? 'Belirtilmemiş') ?></div>
                        <div class="sahip-info-label">Araç Sahibi</div>
                    </div>
                </div>
                <!-- Telefon -->
                <div class="arac-card-tel">
                    <i class="bi bi-telephone-fill"></i>
                    <a href="tel:<?= htmlspecialchars($a['telefon'] ?? '') ?>">
                        <?= htmlspecialchars($a['telefon'] ?? 'Belirtilmemiş') ?>
                    </a>
                </div>
                <!-- Badges -->
                <div class="arac-card-badges">
                    <?php if(!empty($a['kategori'])): ?>
                    <span class="mini-badge" style="background:linear-gradient(135deg,#4dabf7,#339af0);">
                        <i class="bi bi-tag"></i> <?= htmlspecialchars($a['kategori']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if(!empty($a['camera']) && $a['camera']=='Var'): ?>
                    <span class="mini-badge" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="bi bi-camera-video"></i> Kamera
                    </span>
                    <?php endif; ?>
                    <?php if(!empty($a['gps']) && $a['gps']=='Var'): ?>
                    <span class="mini-badge" style="background:linear-gradient(135deg,#51cf66,#40c057);">
                        <i class="bi bi-geo-alt"></i> GPS
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile;
            else: ?>
            <div style="padding:30px; color:#bbb; font-size:0.9rem;">Henüz araç eklenmemiş.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══════════ ANA İÇERİK ══════════ -->
<div class="main-wrap mt-3">

    <?php if($az_stok > 0): ?>
    <div class="uyari-banner">
        <div class="uyari-ic"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="uyari-text">
            <h6>⚠️ Az Stok Uyarısı!</h6>
            <p>Stok seviyesi kritik olan <?= $az_stok ?> malzeme var. Hemen kontrol edin.</p>
        </div>
        <div class="uyari-num"><?= $az_stok ?></div>
    </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- SOL KOLON -->
        <div class="col-xl-8 col-12">
            <div class="row g-3">
                <div class="col-md-6 col-12">
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <div class="chart-card-title"><i class="bi bi-bar-chart"></i> Malzeme Stokları</div>
                            <span class="chart-pill">Kategori</span>
                        </div>
                        <div class="chart-card-body"><canvas id="malzemeBar" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <div class="chart-card-title"><i class="bi bi-pie-chart"></i> Araç Dağılımı</div>
                            <span class="chart-pill" style="background:linear-gradient(135deg,#51cf66,#40c057);">Kategori</span>
                        </div>
                        <div class="chart-card-body"><canvas id="aracPie" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <div class="chart-card-title"><i class="bi bi-geo-alt"></i> Lokasyon Dağılımı</div>
                            <span class="chart-pill" style="background:linear-gradient(135deg,#4dabf7,#339af0);">Lokasyon</span>
                        </div>
                        <div class="chart-card-body"><canvas id="lokasyonChart" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="ekipman-card">
                        <div class="chart-card-title" style="margin-bottom:20px;">
                            <i class="bi bi-cpu" style="color:#667eea;"></i> Araç Ekipman Durumu
                        </div>
                        <div class="eq-row">
                            <div class="eq-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);"><i class="bi bi-camera-video"></i></div>
                            <div class="eq-label">Kamera</div>
                            <div class="eq-bar"><div class="eq-fill" style="width:<?= $arac_sayi>0?round($kamera_var/$arac_sayi*100):0 ?>%; background:linear-gradient(90deg,#667eea,#764ba2);"></div></div>
                            <div class="eq-val"><?= $kamera_var ?>/<?= $arac_sayi ?></div>
                        </div>
                        <div class="eq-row">
                            <div class="eq-icon" style="background:linear-gradient(135deg,#51cf66,#40c057);"><i class="bi bi-geo-alt"></i></div>
                            <div class="eq-label">GPS</div>
                            <div class="eq-bar"><div class="eq-fill" style="width:<?= $arac_sayi>0?round($gps_var/$arac_sayi*100):0 ?>%; background:linear-gradient(90deg,#51cf66,#40c057);"></div></div>
                            <div class="eq-val"><?= $gps_var ?>/<?= $arac_sayi ?></div>
                        </div>
                        <div style="margin-top:18px;"><canvas id="ekipmanChart" height="140"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- Son Malzemeler -->
            <div class="sec-head">
                <div class="sec-title"><i class="bi bi-clock-history"></i> Son Eklenen Malzemeler</div>
                <a href="malzemeler.php" class="sec-link">Tümü <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-card">
                <?php if($son_malzemeler && $son_malzemeler->num_rows > 0):
                    while($m = $son_malzemeler->fetch_assoc()): ?>
                <div class="list-item">
                    <div class="l-avatar" style="background:linear-gradient(135deg,#667eea,#764ba2);"><i class="bi bi-box-seam"></i></div>
                    <div class="l-info">
                        <div class="l-name"><?= htmlspecialchars($m['ad']) ?></div>
                        <div class="l-sub">
                            <i class="bi bi-tag" style="color:#667eea;"></i> <?= htmlspecialchars($m['kategori'] ?? 'Kategorisiz') ?>
                            &nbsp;·&nbsp;
                            <i class="bi bi-geo-alt" style="color:#4dabf7;"></i> <?= htmlspecialchars($m['lokasyon'] ?? '-') ?>
                        </div>
                    </div>
                    <span class="pill <?= $m['adet']<=1?'pill-red':'pill-purple' ?>"><?= $m['adet'] ?> adet</span>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:30px;text-align:center;color:#bbb;">Henüz malzeme eklenmemiş.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SAĞ KOLON -->
        <div class="col-xl-4 col-12">

            <!-- Hızlı Erişim -->
            <div class="sec-head" style="margin-top:0;">
                <div class="sec-title"><i class="bi bi-lightning-charge"></i> Hızlı Erişim</div>
            </div>
            <div class="quick-grid">
                <a href="malzemeler.php" class="quick-btn">
                    <i class="bi bi-box-seam" style="color:#667eea;"></i>
                    <span>Malzemeler</span>
                </a>
                <a href="araclar.php" class="quick-btn">
                    <i class="bi bi-truck" style="color:#51cf66;"></i>
                    <span>Araçlar</span>
                </a>
                <a href="ekle.php" class="quick-btn">
                    <i class="bi bi-plus-circle" style="color:#f59f00;"></i>
                    <span>Malzeme Ekle</span>
                </a>
                <a href="arac_ekle.php" class="quick-btn">
                    <i class="bi bi-plus-circle-dotted" style="color:#ff6b6b;"></i>
                    <span>Araç Ekle</span>
                </a>
            </div>

            <!-- Kritik Stoklar -->
            <?php if($stok_uyarilari && $stok_uyarilari->num_rows > 0): ?>
            <div class="sec-head">
                <div class="sec-title">
                    <i class="bi bi-exclamation-triangle" style="background:linear-gradient(135deg,#ff6b6b,#ee5a6f);"></i>
                    Kritik Stoklar
                </div>
                <a href="malzemeler.php" class="sec-link">Tümü <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-card">
                <?php $stok_uyarilari->data_seek(0); while($s = $stok_uyarilari->fetch_assoc()): ?>
                <div class="list-item">
                    <div class="l-avatar" style="background:linear-gradient(135deg,#ff6b6b,#ee5a6f);"><i class="bi bi-exclamation-lg"></i></div>
                    <div class="l-info">
                        <div class="l-name"><?= htmlspecialchars($s['ad']) ?></div>
                        <div class="l-sub"><i class="bi bi-geo-alt" style="color:#4dabf7;"></i> <?= htmlspecialchars($s['lokasyon'] ?? '-') ?></div>
                    </div>
                    <span class="pill pill-red"><?= $s['adet'] ?> adet</span>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

            <!-- Son Araçlar -->
            <div class="sec-head">
                <div class="sec-title">
                    <i class="bi bi-truck" style="background:linear-gradient(135deg,#51cf66,#40c057);"></i>
                    Son Eklenen Araçlar
                </div>
                <a href="araclar.php" class="sec-link">Tümü <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-card">
                <?php if($son_araclar && $son_araclar->num_rows > 0):
                    while($a = $son_araclar->fetch_assoc()): ?>
                <div class="list-item">
                    <div class="l-avatar" style="background:linear-gradient(135deg,#51cf66,#40c057);"><i class="bi bi-truck"></i></div>
                    <div class="l-info">
                        <div class="l-name"><?= htmlspecialchars($a['marka'].' '.$a['model']) ?></div>
                        <div class="l-sub">
                            <i class="bi bi-person" style="color:#667eea;"></i> <?= htmlspecialchars($a['sahip'] ?? '-') ?>
                            &nbsp;·&nbsp;
                            <i class="bi bi-telephone" style="color:#51cf66;"></i> <?= htmlspecialchars($a['telefon'] ?? '-') ?>
                        </div>
                    </div>
                    <span class="pill pill-green"><?= htmlspecialchars($a['plaka']) ?></span>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:30px;text-align:center;color:#bbb;">Henüz araç eklenmemiş.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<div class="dash-footer">
    <i class="bi bi-building"></i> Şantiye Stok Yönetim Sistemi &copy; <?= date('Y') ?>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const C=['#667eea','#51cf66','#4dabf7','#f59f00','#ff6b6b','#20c997','#764ba2','#f06595'];
const tip={backgroundColor:'rgba(44,62,80,0.95)',titleColor:'#fff',bodyColor:'#ddd',padding:12,cornerRadius:10,borderColor:'rgba(255,255,255,0.1)',borderWidth:1};

new Chart(document.getElementById('malzemeBar'),{
    type:'bar',
    data:{labels:<?= json_encode($malzeme_label) ?>,datasets:[{label:'Stok',data:<?= json_encode($malzeme_data) ?>,backgroundColor:C.map(c=>c+'bb'),borderColor:C,borderWidth:2,borderRadius:8,borderSkipped:false}]},
    options:{responsive:true,animation:{duration:1500,easing:'easeOutQuart'},plugins:{legend:{display:false},tooltip:{...tip}},scales:{y:{beginAtZero:true,grid:{color:'#f5f5f5'},ticks:{color:'#999'}},x:{grid:{display:false},ticks:{color:'#666'}}}}
});

new Chart(document.getElementById('aracPie'),{
    type:'doughnut',
    data:{labels:<?= json_encode($arac_label) ?>,datasets:[{data:<?= json_encode($arac_data) ?>,backgroundColor:C,hoverOffset:12,borderWidth:3,borderColor:'#fff'}]},
    options:{responsive:true,cutout:'62%',animation:{duration:1500},plugins:{legend:{display:true,position:'bottom',labels:{color:'#666',padding:10,font:{size:10,weight:'700'}}},tooltip:{...tip}}}
});

new Chart(document.getElementById('lokasyonChart'),{
    type:'doughnut',
    data:{labels:<?= json_encode($lok_label) ?>,datasets:[{data:<?= json_encode($lok_data) ?>,backgroundColor:['#4dabf7','#f59f00','#20c997','#ff6b6b','#764ba2','#51cf66'],hoverOffset:12,borderWidth:3,borderColor:'#fff'}]},
    options:{responsive:true,cutout:'62%',animation:{duration:1500},plugins:{legend:{display:true,position:'bottom',labels:{color:'#666',padding:10,font:{size:10,weight:'700'}}},tooltip:{...tip}}}
});

new Chart(document.getElementById('ekipmanChart'),{
    type:'bar',
    data:{labels:['Kamera ✓','Kamera ✗','GPS ✓','GPS ✗'],datasets:[{data:[<?= $kamera_var ?>,<?= $kamera_yok ?>,<?= $gps_var ?>,<?= $gps_yok ?>],backgroundColor:['#667eeabb','#ff6b6bbb','#51cf66bb','#f59f00bb'],borderColor:['#667eea','#ff6b6b','#51cf66','#f59f00'],borderWidth:2,borderRadius:6,borderSkipped:false}]},
    options:{responsive:true,animation:{duration:1500},plugins:{legend:{display:false},tooltip:{...tip}},scales:{y:{beginAtZero:true,grid:{color:'#f5f5f5'},ticks:{color:'#999',stepSize:1}},x:{grid:{display:false},ticks:{color:'#666',font:{size:10}}}}}
});

// Scroll hint gizle
const scrollWrap = document.querySelector('.arac-scroll-wrap');
const scrollHint = document.querySelector('.scroll-hint');
if(scrollWrap && scrollHint){
    scrollWrap.addEventListener('scroll',()=>{ scrollHint.style.display='none'; },{once:true});
    setTimeout(()=>{ if(scrollHint){ scrollHint.style.opacity='0'; setTimeout(()=>scrollHint.style.display='none',500); } },4000);
}
</script>
</body>
</html>