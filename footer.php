<?php
// footer.php

date_default_timezone_set('Europe/Istanbul');

$sehir = isset($_GET['sehir']) ? $_GET['sehir'] : 'Istanbul';
$para  = isset($_GET['para'])  ? $_GET['para']  : 'USD';

// Hava durumu
$weather = @file_get_contents("https://wttr.in/".urlencode($sehir)."?format=3");
if(!$weather) $weather = "Hava durumu alınamadı";

// Kur verileri
$kur = ['USD'=>'0','EUR'=>'0','ALTIN'=>'0','GRAMALTIN'=>'0'];

$tcmb_data = @file_get_contents('https://www.tcmb.gov.tr/kurlar/today.xml');
if($tcmb_data){
    $xml = simplexml_load_string($tcmb_data);
    $kur['USD'] = (string)$xml->Currency[0]->ForexSelling;
    $kur['EUR'] = (string)$xml->Currency[3]->ForexSelling;
}

$altin_json = @file_get_contents('https://api.genelpara.com/embed/doviz.json');
if($altin_json){
    $altin_data = json_decode($altin_json, true);
    if(isset($altin_data['gram-altin']['satis'])) $kur['GRAMALTIN'] = $altin_data['gram-altin']['satis'];
    if(isset($altin_data['altin']['ons']))         $kur['ALTIN']     = $altin_data['altin']['ons'];
}

$sehirler = ['Istanbul','Ankara','Izmir','Antalya','Bursa','Adana','Trabzon'];
?>

<style>
/* ── FOOTER ── */
.site-footer {
    background: linear-gradient(135deg, #1a1d2e 0%, #16213e 50%, #0f3460 100%);
    color: #fff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
    overflow: hidden;
    margin-top: 40px;
}

/* Dekoratif arka plan daireler */
.site-footer::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: rgba(102,126,234,0.08);
    border-radius: 50%;
    pointer-events: none;
}

.site-footer::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 220px; height: 220px;
    background: rgba(240,147,251,0.06);
    border-radius: 50%;
    pointer-events: none;
}

/* Üst ince gradient çizgi */
.footer-topline {
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
}

@keyframes shimmer {
    0%  { background-position: 200% 0; }
    100%{ background-position: -200% 0; }
}

.footer-main {
    padding: 40px 30px 30px;
    position: relative;
    z-index: 2;
}

/* Grid */
.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-bottom: 35px;
}

/* Her bir blok */
.footer-block { position: relative; }

.footer-block-title {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.footer-block-title i { color: #667eea; font-size: 0.85rem; }

/* Saat */
.footer-clock {
    font-size: 1.6rem;
    font-weight: 800;
    color: white;
    letter-spacing: 2px;
    font-variant-numeric: tabular-nums;
}

.footer-date {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.5);
    margin-top: 4px;
}

/* Hava durumu */
.footer-weather-text {
    font-size: 1.05rem;
    font-weight: 700;
    color: white;
    margin-bottom: 10px;
}

.footer-select {
    background: rgba(255,255,255,0.08);
    border: 1.5px solid rgba(255,255,255,0.15);
    color: white;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 0.82rem;
    font-family: 'Segoe UI', sans-serif;
    cursor: pointer;
    transition: all 0.3s;
    outline: none;
    width: 100%;
    max-width: 180px;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23ffffff' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px;
}

.footer-select:hover, .footer-select:focus {
    border-color: #667eea;
    background-color: rgba(102,126,234,0.15);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
}

.footer-select option { background: #1a1d2e; color: white; }

/* Kur */
.kur-value {
    font-size: 1.5rem;
    font-weight: 900;
    color: white;
    display: flex;
    align-items: flex-end;
    gap: 5px;
    margin-bottom: 8px;
}

.kur-value span {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.4);
    font-weight: 600;
    margin-bottom: 4px;
}

.kur-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(102,126,234,0.2);
    border: 1px solid rgba(102,126,234,0.3);
    color: #a5b4fc;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
}

/* ── YAPIMCI ── */
.footer-author {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 8px;
    text-align: center;
}

.author-label {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
}

.author-name {
    font-size: 1.4rem;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea 0%, #f093fb 50%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 1px;
    position: relative;
    display: inline-block;
}

.author-name::after {
    content: '';
    position: absolute;
    bottom: -3px; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #f093fb, #764ba2);
    border-radius: 2px;
    animation: widthPulse 2s ease-in-out infinite;
}

@keyframes widthPulse {
    0%,100%{ transform: scaleX(0.6); opacity:0.7; }
    50%    { transform: scaleX(1);   opacity:1; }
}

.author-title {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.35);
    letter-spacing: 1px;
}

/* ── ALT ÇUBUK ── */
.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding: 16px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    position: relative;
    z-index: 2;
}

.footer-copy {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.3);
}

.footer-copy strong { color: rgba(255,255,255,0.5); }

.footer-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.f-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.45);
}

.f-badge i { font-size: 0.75rem; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .footer-grid    { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .footer-main    { padding: 30px 15px 20px; }
    .footer-bottom  { padding: 14px 15px; justify-content: center; text-align: center; }
    .author-name    { font-size: 1.2rem; }
    .footer-clock   { font-size: 1.3rem; }
}

@media (max-width: 480px) {
    .footer-grid { grid-template-columns: 1fr 1fr; }
    .footer-author { margin-top: 10px; }
}
</style>

<footer class="site-footer">
    <!-- Üst gradient çizgi -->
    <div class="footer-topline"></div>

    <div class="footer-main">
        <div class="footer-grid">

            <!-- SAAT & TARİH -->
            <div class="footer-block">
                <div class="footer-block-title"><i class="bi bi-clock"></i> Tarih & Saat</div>
                <div class="footer-clock" id="footerClock"><?= date('H:i:s') ?></div>
                <div class="footer-date"><?= date('d.m.Y') ?> — <?= ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'][date('w')] ?></div>
            </div>

            <!-- HAVA DURUMU -->
            <div class="footer-block">
                <div class="footer-block-title"><i class="bi bi-cloud-sun"></i> Hava Durumu</div>
                <div class="footer-weather-text"><?= htmlspecialchars($weather) ?></div>
                <form method="GET">
                    <?php if(isset($_GET['para'])): ?>
                    <input type="hidden" name="para" value="<?= htmlspecialchars($para) ?>">
                    <?php endif; ?>
                    <select name="sehir" class="footer-select" onchange="this.form.submit()">
                        <?php foreach($sehirler as $s): ?>
                            <option value="<?= $s ?>" <?= $s==$sehir?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- KUR / PİYASA -->
            <div class="footer-block">
                <div class="footer-block-title"><i class="bi bi-currency-exchange"></i> Piyasa / Kur</div>
                <div class="kur-value">
                    <?= number_format((float)($kur[$para] ?? 0), 2, ',', '.') ?>
                    <span><?= $para=='ALTIN' ? 'USD/Ons' : ($para=='GRAMALTIN' ? 'TL/gr' : 'TL') ?></span>
                </div>
                <div class="kur-badge">
                    <i class="bi bi-graph-up"></i>
                    <?= $para == 'USD' ? '🇺🇸 Dolar' : ($para=='EUR' ? '🇪🇺 Euro' : ($para=='ALTIN' ? '🥇 Ons Altın' : '🥇 Gram Altın')) ?>
                </div>
                <form method="GET" style="margin-top:10px;">
                    <?php if(isset($_GET['sehir'])): ?>
                    <input type="hidden" name="sehir" value="<?= htmlspecialchars($sehir) ?>">
                    <?php endif; ?>
                    <select name="para" class="footer-select" onchange="this.form.submit()">
                        <option value="USD"       <?= $para=='USD'?'selected':'' ?>>🇺🇸 USD / TL</option>
                        <option value="EUR"       <?= $para=='EUR'?'selected':'' ?>>🇪🇺 EUR / TL</option>
                        <option value="ALTIN"     <?= $para=='ALTIN'?'selected':'' ?>>🥇 Altın / Ons</option>
                        <option value="GRAMALTIN" <?= $para=='GRAMALTIN'?'selected':'' ?>>🥇 Gram Altın</option>
                    </select>
                </form>
            </div>

            <!-- YAPIMCI -->
            <div class="footer-block">
                <div class="footer-block-title"><i class="bi bi-code-slash"></i> Geliştirici</div>
                <div class="footer-author">
                    <div class="author-label">✦ Tasarım & Geliştirme ✦</div>
                    <div class="author-name">Muhammed Salman</div>
                    <div class="author-title">Full Stack Developer</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Alt çubuk -->
    <div class="footer-bottom">
        <div class="footer-copy">
            &copy; <?= date('Y') ?> <strong>Şantiye Stok Yönetim Sistemi</strong> — Tüm hakları saklıdır.
        </div>
        <div class="footer-badges">
            <div class="f-badge"><i class="bi bi-phone"></i> PWA Destekli</div>
            <div class="f-badge"><i class="bi bi-shield-check"></i> Güvenli</div>
            <div class="f-badge"><i class="bi bi-lightning-charge"></i> Hızlı</div>
        </div>
    </div>
</footer>

<script>
// Canlı saat
function saatGuncelle(){
    const now = new Date();
    const s = String(now.getHours()).padStart(2,'0') + ':' +
              String(now.getMinutes()).padStart(2,'0') + ':' +
              String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('footerClock');
    if(el) el.textContent = s;
}
setInterval(saatGuncelle, 1000);

// Service Worker
if('serviceWorker' in navigator){
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker-v2.js')
            .then(()  => console.log('✅ Service Worker kaydedildi'))
            .catch(e  => console.error('SW hata:', e));
    });
}
</script>