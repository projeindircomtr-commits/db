const CACHE_NAME = 'santiye-v2';
const API_CACHE  = 'santiye-api-v2';

const OFFLINE_URLS = [
  './',
  './index.php',
  './malzemeler.php',
  './ekle.php',
  './araclar.php',
  './arac_ekle.php',
  './dashboard.php',
  './kategoriler.php',
  './lokasyon.php',
  './app.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// API URL'leri - bunlar cache'lenecek
const API_URLS = [
  './api.php?action=malzemeler',
  './api.php?action=araclar',
  './api.php?action=kategoriler',
  './api.php?action=lokasyonlar'
];

// Kurulum
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(OFFLINE_URLS))
  );
  self.skipWaiting();
});

// Aktivasyon - eski cache temizle
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => k !== CACHE_NAME && k !== API_CACHE)
            .map(k => caches.delete(k))
      )
    )
  );
  self.clients.claim();
});

// Fetch
self.addEventListener('fetch', e => {
  const url = e.request.url;

  // POST isteklerini atlat
  if (e.request.method !== 'GET') return;

  // API istekleri - Network first, cache fallback
  if (url.includes('api.php')) {
    e.respondWith(
      fetch(e.request)
        .then(response => {
          const copy = response.clone();
          caches.open(API_CACHE).then(cache => cache.put(e.request, copy));
          return response;
        })
        .catch(() => caches.match(e.request))
    );
    return;
  }

  // Diger istekler - Cache first, network fallback
  e.respondWith(
    caches.match(e.request).then(cached => {
      return cached || fetch(e.request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(e.request, copy));
        return response;
      });
    }).catch(() => caches.match('./index.php'))
  );
});

// Background Sync
self.addEventListener('sync', e => {
  if (e.tag === 'offline-sync') {
    e.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
  const db = await openDB();
  const tx = db.transaction('queue', 'readwrite');
  const store = tx.objectStore('queue');
  const items = await getAllItems(store);

  for (const item of items) {
    try {
      const res = await fetch(item.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item.data)
      });
      if (res.ok) {
        store.delete(item.id);
      }
    } catch (err) {
      console.log('Sync bekliyor:', err);
    }
  }
}

function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open('santiye_offline', 1);
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
    req.onupgradeneeded = e => {
      e.target.result.createObjectStore('queue', { keyPath: 'id', autoIncrement: true });
    };
  });
}

function getAllItems(store) {
  return new Promise((resolve, reject) => {
    const req = store.getAll();
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}