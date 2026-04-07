<?php
session_start();
$baglanti = new mysqli("localhost","mar2e7groucomtr_stok","Ceza1Ceza","mar2e7groucomtr_stok");
if($baglanti->connect_error){
    die("DB Hatası: ".$baglanti->connect_error);
}
?>