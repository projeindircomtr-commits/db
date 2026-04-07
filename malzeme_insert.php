<?php
include "db.php";
$data = json_decode(file_get_contents('php://input'), true);
if($data){
    $stmt = $baglanti->prepare("INSERT INTO malzemeler(ad, adet, kategori_id, lokasyon) VALUES(?,?,?,?)");
    $stmt->bind_param("siis", $data['ad'], $data['adet'], $data['kategori'], $data['lokasyon']);
    if($stmt->execute()){
        echo "OK";
    } else echo "HATA";
}
?>