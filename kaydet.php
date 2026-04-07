<?php include "db.php";

$ad=$_POST['ad']; $kat=$_POST['kategori']; $adet=$_POST['adet']; $lok=$_POST['lokasyon'];

$resim="";
if($_FILES['resim']['name']!=""){
    $resim=time()."_".$_FILES['resim']['name'];
    move_uploaded_file($_FILES['resim']['tmp_name'],"uploads/".$resim);
}

$baglanti->query("INSERT INTO malzemeler (ad,kategori_id,adet,lokasyon,resim) VALUES ('$ad',$kat,$adet,'$lok','$resim')");
$id=$baglanti->insert_id;

$alanlar=$baglanti->query("SELECT * FROM alanlar");
while($a=$alanlar->fetch_assoc()){
    $deger=$_POST['alan_'.$a['id']] ?? '';
    $baglanti->query("INSERT INTO malzeme_alan (malzeme_id,alan_id,deger) VALUES ($id,".$a['id'].",'$deger')");
}

header("Location:index.php");