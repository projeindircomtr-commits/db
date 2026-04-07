const DB_NAME    = 'santiye_offline';
const DB_VERSION = 2;
const STORE_NAME = 'queue';
const CACHE_STORE = 'api_cache';

function dbAc() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = e => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
      }
      if (!db.objectStoreNames.contains(CACHE_STORE)) {
        db.createObjectStore(CACHE_STORE, { keyPath: 'key' });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

async function kuyrugaEkle(url, data) {
  const db = await dbAc();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  tx.objectStore(STORE_NAME).add({ url, data, zaman: Date.now() });
  console.log('Offline kuyruga eklendi:', data);
}

async function apiCacheKaydet(key, data) {
  try {
    const db = await dbAc();
    const tx = db.transaction(CACHE_STORE, 'readwrite');
    tx.objectStore(CACHE_STORE).put({ key, data, zaman: Date.now() });
  } catch(e) {}
}

async function apiCacheOku(key) {
  try {
    const db = await dbAc();
    const tx = db.transaction(CACHE_STORE, 'readonly');
    return await new Promise((res, rej) => {
      const r = tx.objectStore(CACHE_STORE).get(key);
      r.onsuccess = () => res(r.result ? r.result.data : null);
      r.onerror = () => rej(null);
    });
  } catch(e) { return null; }
}

async function veriCek(url) {
  if (navigator.onLine) {
    try {
      const res = await fetch(url);
      const json = await res.json();
      if (json.status === 'success') {
        await apiCacheKaydet(url, json.data);
        return json.data;
      }
    } catch(e) {}
  }
  const cached = await apiCacheOku(url);
  if (cached) {
    gosterBildirim('Cevrimdisi mod - Son kaydedilen veriler gosteriliyor', 'warning');
    return cached;
  }
  return [];
}

async function syncYap() {
  if (!navigator.onLine) return;
  const db = await dbAc();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  const store = tx.objectStore(STORE_NAME);
  const items = await new Promise((res, rej) => {
    const r = store.getAll();
    r.onsuccess = () => res(r.result);
    r.onerror = () => rej(r.error);
  });

  for (const item of items) {
    try {
      const response = await fetch(item.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item.data)
      });
      if (response.ok) {
        const delTx = db.transaction(STORE_NAME, 'readwrite');
        delTx.objectStore(STORE_NAME).delete(item.id);
        console.log('Sync tamam, silindi:', item.id);
      }
    } catch (e) {
      console.log('Sync bekliyor...');
    }
  }
  bekleyenSayisiGoster();
}

async function bekleyenSayisiGoster() {
  try {
    const db = await dbAc();
    const tx = db.transaction(STORE_NAME, 'readonly');
    const store = tx.objectStore(STORE_NAME);
    const count = await new Promise((res, rej) => {
      const r = store.count();
      r.onsuccess = () => res(r.result);
      r.onerror = () => rej(r.error);
    });
    const badge = document.getElementById('offlineBadge');
    if (badge) {
      badge.textContent = count > 0 ? count + ' bekleyen kayit' : '';
      badge.style.display = count > 0 ? 'block' : 'none';
    }
  } catch(e) {}
}

async function veriKaydet(url, data) {
  if (navigator.onLine) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.status === 'success') {
        console.log('Online kaydedildi');
        return { ok: true, offline: false };
      }
    } catch (e) {}
  }
  await kuyrugaEkle(url, data);
  bekleyenSayisiGoster();
  return { ok: true, offline: true };
}

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('./service-worker.js')
      .then(reg => {
        console.log('Service Worker kayitli');
        if ('sync' in reg) {
          window.addEventListener('online', () => {
            reg.sync.register('offline-sync');
            syncYap();
          });
        }
      })
      .catch(e => console.error('SW hata:', e));
  });
}

window.addEventListener('online', () => {
  gosterBildirim('Internet baglantisi kuruldu! Veriler senkronize ediliyor...', 'success');
  syncYap();
});

window.addEventListener('offline', () => {
  gosterBildirim('Cevrimdisi mod! Veriler kaydedilip sonra senkronize edilecek.', 'warning');
});

function gosterBildirim(mesaj, tip) {
  let div = document.getElementById('pwaBildirim');
  if (!div) {
    div = document.createElement('div');
    div.id = 'pwaBildirim';
    div.style.cssText = 'position:fixed;top:70px;right:15px;z-index:9999;padding:12px 20px;border-radius:12px;font-size:0.85rem;font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,0.2);transition:all 0.3s;';
    document.body.appendChild(div);
  }
  div.textContent = mesaj;
  div.style.background = tip === 'success' ? '#51cf66' : '#f59f00';
  div.style.color = 'white';
  div.style.display = 'block';
  setTimeout(() => { div.style.display = 'none'; }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
  bekleyenSayisiGoster();
  if (navigator.onLine) syncYap();

  if (navigator.onLine) {
    veriCek('./api.php?action=malzemeler');
    veriCek('./api.php?action=araclar');
    veriCek('./api.php?action=kategoriler');
    veriCek('./api.php?action=lokasyonlar');
  }
});