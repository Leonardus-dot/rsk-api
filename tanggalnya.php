<?php 
date_default_timezone_set('Asia/Jakarta');

$tanggalperiksa='2022-09-02';
$number_transactionbpjs=date('ymd',$tanggalPeriksa);

$pecah_tgl=explode("-",$tanggalperiksa);
$thn=substr($pecah_tgl[0],2,2);
$bln=$pecah_tgl[1];
$tgl=$pecah_tgl[2];

$tanggalnya ="".$thn."".$bln."".$tgl."";
ECHO"$tanggalnya";

?>