<?php
include "db.php"; 
if(!isset($_SESSION['login'])){
    exit("Hata: Giriş yapılmamış.");
}

if(!isset($_GET['id'])){
    exit("Hata: ID yok.");
}

$id = intval($_GET['id']);

if($baglanti->query("DELETE FROM kategoriler WHERE id=$id")){
    echo "ok";
}else{
    echo $baglanti->error;
}
?>