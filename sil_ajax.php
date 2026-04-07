<?php
include "db.php"; 
if(!isset($_SESSION['login'])){
    exit("Giriş yapılmamış.");
}

if(!isset($_GET['id'])){
    exit("ID yok.");
}

$id = intval($_GET['id']);

// Silmeden önce resim varsa dosyayı da sil
$resim = $baglanti->query("SELECT resim FROM malzemeler WHERE id=$id")->fetch_assoc();
if($resim && $resim['resim'] && file_exists("uploads/".$resim['resim'])){
    unlink("uploads/".$resim['resim']);
}

// Malzemeyi sil
if($baglanti->query("DELETE FROM malzemeler WHERE id=$id")){
    echo "ok";
}else{
    echo $baglanti->error;
}
?>