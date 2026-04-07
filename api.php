<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

// JSON request body al
$input = json_decode(file_get_contents('php://input'), true);

// ======================== MALZEMELERİ AKTARabla ========================
if ($action == 'malzemeler' && $method == 'GET') {
    $query = "SELECT * FROM malzemeler ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    $malzemeler = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $malzemeler[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $malzemeler]);
}

// MALZEMELERİ EKLE
else if ($action == 'malzeme_add' && $method == 'POST') {
    $ad = isset($input['ad']) ? $input['ad'] : '';
    $kategori_id = isset($input['kategori_id']) ? $input['kategori_id'] : 0;
    $adet = isset($input['adet']) ? $input['adet'] : 0;
    $lokasyon = isset($input['lokasyon']) ? $input['lokasyon'] : '';

    $query = "INSERT INTO malzemeler (ad, kategori_id, adet, lokasyon) 
              VALUES ('$ad', $kategori_id, $adet, '$lokasyon')";
    
    if (mysqli_query($conn, $query)) {
        $id = mysqli_insert_id($conn);
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Malzeme eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . mysqli_error($conn)]);
    }
}

// MALZEMELERİ GÜNCELLE
else if ($action == 'malzeme_update' && $method == 'POST') {
    $id = isset($input['id']) ? $input['id'] : 0;
    $ad = isset($input['ad']) ? $input['ad'] : '';
    $adet = isset($input['adet']) ? $input['adet'] : 0;

    $query = "UPDATE malzemeler SET ad='$ad', adet=$adet WHERE id=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Malzeme güncellendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . mysqli_error($conn)]);
    }
}

// MALZEMELERİ SİL
else if ($action == 'malzeme_delete' && $method == 'POST') {
    $id = isset($input['id']) ? $input['id'] : 0;
    
    $query = "DELETE FROM malzemeler WHERE id=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Malzeme silindi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . mysqli_error($conn)]);
    }
}

// ======================== ARAÇLAR ========================
else if ($action == 'araclar' && $method == 'GET') {
    $query = "SELECT * FROM araclar ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    $araclar = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $araclar[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $araclar]);
}

else if ($action == 'arac_add' && $method == 'POST') {
    $marka = $input['marka'] ?? '';
    $model = $input['model'] ?? '';
    $plaka = $input['plaka'] ?? '';
    $sahip = $input['sahip'] ?? '';
    $telefon = $input['telefon'] ?? '';

    $query = "INSERT INTO araclar (marka, model, plaka, sahip, telefon) 
              VALUES ('$marka', '$model', '$plaka', '$sahip', '$telefon')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Araç eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

// ======================== KATEGORİLER ========================
else if ($action == 'kategoriler' && $method == 'GET') {
    $query = "SELECT * FROM kategoriler ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    $kategoriler = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $kategoriler[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $kategoriler]);
}

else if ($action == 'kategori_add' && $method == 'POST') {
    $ad = $input['ad'] ?? '';
    
    $query = "INSERT INTO kategoriler (ad) VALUES ('$ad')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Kategori eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

// ======================== LOKASYONLAR ========================
else if ($action == 'lokasyonlar' && $method == 'GET') {
    $query = "SELECT * FROM lokasyonlar ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    $lokasyonlar = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $lokasyonlar[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $lokasyonlar]);
}

else if ($action == 'lokasyon_add' && $method == 'POST') {
    $ad = $input['ad'] ?? '';
    
    $query = "INSERT INTO lokasyonlar (ad) VALUES ('$ad')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Lokasyon eklendi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

// Bilinmeyen istek
else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek']);
}

mysqli_close($conn);
?>