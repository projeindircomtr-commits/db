<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);

// ======================== MALZEMELER ========================

// Malzemeleri Getir
if ($action == 'malzemeler' && $method == 'GET') {
    $query = "SELECT * FROM malzemeler ORDER BY id DESC";
    $result = mysqli_query($baglanti, $query);
    $malzemeler = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $malzemeler[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $malzemeler]);
}

// Malzeme Ekle
else if ($action == 'malzeme_add' && $method == 'POST') {
    $ad          = mysqli_real_escape_string($baglanti, $input['ad'] ?? '');
    $kategori_id = intval($input['kategori_id'] ?? 0);
    $adet        = intval($input['adet'] ?? 0);
    $lokasyon    = mysqli_real_escape_string($baglanti, $input['lokasyon'] ?? '');

    $query = "INSERT INTO malzemeler (ad, kategori_id, adet, lokasyon)
              VALUES ('$ad', $kategori_id, $adet, '$lokasyon')";

    if (mysqli_query($baglanti, $query)) {
        $id = mysqli_insert_id($baglanti);
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Malzeme eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// Malzeme Guncelle
else if ($action == 'malzeme_update' && $method == 'POST') {
    $id          = intval($input['id'] ?? 0);
    $ad          = mysqli_real_escape_string($baglanti, $input['ad'] ?? '');
    $adet        = intval($input['adet'] ?? 0);
    $kategori_id = intval($input['kategori_id'] ?? 0);
    $lokasyon    = mysqli_real_escape_string($baglanti, $input['lokasyon'] ?? '');

    $query = "UPDATE malzemeler SET
                ad='$ad',
                adet=$adet,
                kategori_id=$kategori_id,
                lokasyon='$lokasyon'
              WHERE id=$id";

    if (mysqli_query($baglanti, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Malzeme guncellendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// Malzeme Sil
else if ($action == 'malzeme_delete' && $method == 'POST') {
    $id = intval($input['id'] ?? 0);

    $query = "DELETE FROM malzemeler WHERE id=$id";

    if (mysqli_query($baglanti, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Malzeme silindi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// ======================== ARACLAR ========================

// Araclari Getir
else if ($action == 'araclar' && $method == 'GET') {
    $query = "SELECT * FROM araclar ORDER BY id DESC";
    $result = mysqli_query($baglanti, $query);
    $araclar = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $araclar[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $araclar]);
}

// Arac Ekle
else if ($action == 'arac_add' && $method == 'POST') {
    $marka       = mysqli_real_escape_string($baglanti, $input['marka'] ?? '');
    $model       = mysqli_real_escape_string($baglanti, $input['model'] ?? '');
    $plaka       = mysqli_real_escape_string($baglanti, $input['plaka'] ?? '');
    $sahip       = mysqli_real_escape_string($baglanti, $input['sahip'] ?? '');
    $telefon     = mysqli_real_escape_string($baglanti, $input['telefon'] ?? '');
    $camera      = mysqli_real_escape_string($baglanti, $input['camera'] ?? 'Yok');
    $gps         = mysqli_real_escape_string($baglanti, $input['gps'] ?? 'Yok');
    $kategori_id = intval($input['kategori_id'] ?? 0);

    $query = "INSERT INTO araclar (marka, model, plaka, sahip, telefon, camera, gps, kategori_id)
              VALUES ('$marka', '$model', '$plaka', '$sahip', '$telefon', '$camera', '$gps', $kategori_id)";

    if (mysqli_query($baglanti, $query)) {
        $id = mysqli_insert_id($baglanti);
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Arac eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// Arac Guncelle
else if ($action == 'arac_update' && $method == 'POST') {
    $id      = intval($input['id'] ?? 0);
    $marka   = mysqli_real_escape_string($baglanti, $input['marka'] ?? '');
    $model   = mysqli_real_escape_string($baglanti, $input['model'] ?? '');
    $plaka   = mysqli_real_escape_string($baglanti, $input['plaka'] ?? '');
    $sahip   = mysqli_real_escape_string($baglanti, $input['sahip'] ?? '');
    $telefon = mysqli_real_escape_string($baglanti, $input['telefon'] ?? '');

    $query = "UPDATE araclar SET
                marka='$marka',
                model='$model',
                plaka='$plaka',
                sahip='$sahip',
                telefon='$telefon'
              WHERE id=$id";

    if (mysqli_query($baglanti, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Arac guncellendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// Arac Sil
else if ($action == 'arac_delete' && $method == 'POST') {
    $id = intval($input['id'] ?? 0);

    $query = "DELETE FROM araclar WHERE id=$id";

    if (mysqli_query($baglanti, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Arac silindi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// ======================== KATEGORILER ========================

// Kategorileri Getir
else if ($action == 'kategoriler' && $method == 'GET') {
    $query = "SELECT * FROM kategoriler ORDER BY ad ASC";
    $result = mysqli_query($baglanti, $query);
    $kategoriler = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $kategoriler[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $kategoriler]);
}

// Kategori Ekle
else if ($action == 'kategori_add' && $method == 'POST') {
    $ad = mysqli_real_escape_string($baglanti, $input['ad'] ?? '');

    $query = "INSERT INTO kategoriler (ad) VALUES ('$ad')";

    if (mysqli_query($baglanti, $query)) {
        $id = mysqli_insert_id($baglanti);
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Kategori eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// ======================== LOKASYONLAR ========================

// Lokasyonlari Getir
else if ($action == 'lokasyonlar' && $method == 'GET') {
    $query = "SELECT * FROM lokasyonlar ORDER BY ad ASC";
    $result = mysqli_query($baglanti, $query);
    $lokasyonlar = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $lokasyonlar[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $lokasyonlar]);
}

// Lokasyon Ekle
else if ($action == 'lokasyon_add' && $method == 'POST') {
    $ad = mysqli_real_escape_string($baglanti, $input['ad'] ?? '');

    $query = "INSERT INTO lokasyonlar (ad) VALUES ('$ad')";

    if (mysqli_query($baglanti, $query)) {
        $id = mysqli_insert_id($baglanti);
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Lokasyon eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($baglanti)]);
    }
}

// Bilinmeyen istek
else {
    echo json_encode(['status' => 'error', 'message' => 'Gecersiz istek']);
}

mysqli_close($baglanti);
?>