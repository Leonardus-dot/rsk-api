<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, x-token, x-username");
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('UTC');

$today = date('Y-m-d');
$time = date('Y-m-d H:i:s');
require_once "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET["act"]) ? $_GET["act"] : null;

if ($method == 'POST') {
	header("Content-Type: application/json");
	header("Access-Control-Allow-Methods: POST, GET");

	$username = 'BPJSRSK';
	$password = 'RSKword2020';

	$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
	$payload = json_encode(['username' => $username, 'password' => $password, 'date' => strtotime(date('Y-m-d')) * 1000]);
	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	$RSKauth = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

	$header = apache_request_headers();
	$konten = trim(file_get_contents("php://input"));
	$decode = json_decode($konten, true);
	$response = array();

	$nomorkartu = mysqli_real_escape_string($con, $decode['nomorkartu']);
	$nik = mysqli_real_escape_string($con, $decode['nik']);
	$nohp = mysqli_real_escape_string($con, $decode['nohp']);
	$kodepoli = mysqli_real_escape_string($con, $decode['kodepoli']);
	$norm = mysqli_real_escape_string($con, $decode['norm']);
	$tanggalperiksa = mysqli_real_escape_string($con, $decode['tanggalperiksa']);
	$kodedokter = mysqli_real_escape_string($con, $decode['kodedokter']);
	$jampraktek = mysqli_real_escape_string($con, $decode['jampraktek']);
	$jeniskunjungan = mysqli_real_escape_string($con, $decode['jeniskunjungan']);
	$nomorreferensi = mysqli_real_escape_string($con, $decode['nomorreferensi']);

	$sql = "SELECT quota.DeptCode,quota.quotaJknNow as quota , quota.quotaJkn, quota.quotaJknNow, quota.quotaNonJkn, quota.quotaNonJknNow,DoctorCode,(select DoctorName from doctor WHERE DoctorCode=quota.DoctorCode) as doctor_name,(SELECT DeptName FROM department WHERE DeptCode=quota.DeptCode) as department_name,(SELECT bpjsID FROM department WHERE DeptCode=quota.DeptCode) as bpjsID,(select bpjsCode from doctor WHERE DoctorCode=quota.DoctorCode) as bpjsCode FROM quota WHERE quotaDate='$tanggalperiksa' having bpjsID ='$kodepoli' and bpjsCode='$kodedokter'";

	$result = $con->query($sql);
	$rowID = $result->fetch_array();

	if (($header['x-token'] == $RSKauth) && ($header['x-username'] == $username)) {

		$sqlPoli = "SELECT Count(*) AS countPoli from department where bpjsID ='$kodepoli'";
		$resultPoli = $con->query($sqlPoli);
		$rowIDPoli = $resultPoli->fetch_array();

		$getkodebooking = "SELECT CASE WHEN MAX(RIGHT(kodebooking,3)) + 1 IS NULL THEN  1 ELSE MAX(RIGHT(kodebooking,3)) + 1 END as kodebooking from que_antrean where tanggalperiksa='$tanggalperiksa'";
		$resultkodebooking = $con->query($getkodebooking);
		$rowkodebooking = $resultkodebooking->fetch_array();


		$pecah_tgl = explode("-", $tanggalperiksa);
		$thn = substr($pecah_tgl[0], 2, 2);
		$bln = $pecah_tgl[1];
		$tgl = $pecah_tgl[2];

		$tanggalnya = "" . $thn . "" . $bln . "" . $tgl . "";



		$nobooking = (int) substr($rowkodebooking['kodebooking'], 3, 3);
		$nobooking++;
		$charbooking = "RSK";
		$kodebookingrsk = "" . $charbooking . "" . $tanggalnya . "" . sprintf("%03s", $rowkodebooking['kodebooking']);

		/*$getkodeantrean="SELECT MAX(nomorantrean) as kodeantrean from quebpjs where tanggalperiksa='$tanggalperiksa'";
				$resultkodeantrean= $con->query($getkodeantrean);
				$rowkodeantrean = $resultkodeantrean->fetch_array();*/

		$getkodeantrean = "SELECT CASE WHEN MAX(nomorantrean)+1 IS NULL THEN 1 ELSE MAX(nomorantrean)+ 1 END as kodeantrean from que_antrean where tanggalperiksa='$tanggalperiksa'";

		$resultkodeantrean = $con->query($getkodeantrean);
		$rowkodeantrean = $resultkodeantrean->fetch_array();

		$getnomorkartu = "SELECT count(*) as sumkartu from quebpjs where tanggalperiksa='$tanggalperiksa' and nomorkartu='$nomorkartu' and statusterlayani !='B'";
		$resultnomorkartu = $con->query($getnomorkartu);
		$rownomorkartu = $resultnomorkartu->fetch_array();

		$noantrean = (int) substr($rowkodeantrean['kodeantrean'], 1, 3);
		$noantrean++;
		$charantrean = "A";
		//$kodeantreanrsk = $charantrean . sprintf("%03s", $noantrean);
		$kodeantreanrsk = $rowkodeantrean['kodeantrean'];
		$kodeantreanBPJS = $charantrean . $rowkodeantrean['kodeantrean'];

		/* Estimasi pelayanan di pendaftaran */

		/*$gettime="SELECT MAX(nomorantrean) as kodeantrean,max(jamdilayani) as jam from quebpjs where tanggalperiksa='$tanggalperiksa'";
				$resulttime= $con->query($gettime);
				$rowtime = $resulttime->fetch_array();*/

		$gettime = "SELECT MAX(nomorantrean) as kodeantrean,max(jamdilayani) as jam from que_antrean where tanggalperiksa='$tanggalperiksa' and klinik='$rowID[DeptCode]'";
		$resulttime = $con->query($gettime);
		$rowtime = $resulttime->fetch_array();


		/*$getcount="SELECT count(nomorantrean) as kodeantrean from quebpjs where tanggalperiksa='$tanggalperiksa'";
				$resultcount= $con->query($getcount);
				$rowcount = $resultcount->fetch_array();*/

		$getcount = "SELECT count(nomorantrean) as kodeantrean from que_antrean where tanggalperiksa='$tanggalperiksa' and klinik='$rowID[DeptCode]'";
		$resultcount = $con->query($getcount);
		$rowcount = $resultcount->fetch_array();

		if ($rowcount['kodeantrean'] <= 0) {
			$jadwalmulai = '07:00:00';
			//$estimate = strtotime('07:00:00') * 1000;
			
			$tStamp="$tanggalperiksa $jadwalmulai";
			$estimate = strtotime($tStamp) * 1000; 
		}

		if ($rowcount['kodeantrean'] >= 1) {

			$time = $rowtime['jam'];
			$date = date_create($time);
			date_add($date, date_interval_create_from_date_string('3 minutes'));
			$jadwalmulai = date_format($date, 'H:i:s');
			
			$tStamp="$tanggalperiksa $jadwalmulai";
			$estimate = strtotime($tStamp) * 1000; 
			//$estimate = strtotime($jadwalmulai) * 1000;
			
			
		}
		$tgl = "$decode[tanggalperiksa]";
		$pecah = explode("-", $tgl);
		$jumlah_karakter = strlen($nomorkartu);

		if (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') < 13) {
			$errors[] = 'Nomor kartu harus 13 digit';
		}
		if (empty($decode['nik'])) {
			$errors[] = 'Nomor KTP tidak boleh kosong';
		}
		if (!empty($decode['nik']) && mb_strlen($decode['nik'], 'UTF-8') < 16) {
			$errors[] = 'Format nomor KTP tidak sesuai';
		}
		if (!checkdate($pecah[1], $pecah[2], $pecah[0])) {
			$errors[] = 'Format tanggal periksa tidak sesuai';
		}
		if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $decode['tanggalperiksa'])) {
			$errors[] = 'Format tanggal periksa tidak sesuai';
		}
		if (empty($decode['tanggalperiksa'])) {
			$errors[] = 'Tanggal periksa tidak boleh kosong';
		}
		if ($decode['tanggalperiksa'] == $date && strtotime($decode['tanggalperiksa']) < strtotime('+7 days')) {
			$errors[] = 'Tanggal periksa harus H+1 sampai H+7';
		}
		if (empty($decode['kodepoli'])) {
			$errors[] = 'Kode poli tidak boleh kosong';
		}
		if (!empty($decode['kodepoli']) && $rowIDPoli['countPoli'] == '0') {
			$errors[] = 'Kode poli tidak ditemukan';
		}
		if ($rowID['quota'] <= 0) {
			$errors[] = 'Quota antrean tidak tersedia';
		}

		if ($rowID['quota'] == 0) {
			$errors[] = 'Quota antrean tidak tersedia';
		}
		if ($rownomorkartu['sumkartu'] >= 1) {
			$errors[] = 'Maaf ,Nomor kartu anda sudah mendapatkan nomor antrean sebelumnya pada hari, tanggal dan dokter yang sama ,Terima Kasih';
		}

		if (!$decode['nomorkartu']) {
			$errors[] = 'format nomorkartu yang dikirim tidak valid';
		}
		if (!$decode['nik']) {
			$errors[] = 'format nik yang dikirim tidak valid';
		}

		if (!$decode['tanggalperiksa']) {
			$errors[] = 'format tanggalperiksa yang dikirim tidak valid';
		}
		if (!$decode['kodepoli']) {
			$errors[] = 'format kodepoli yang dikirim tidak valid';
		}
		if (!$decode['nomorreferensi']) {
			$errors[] = 'format nomorreferensi yang dikirim tidak valid';
		}
		if (!$decode['jeniskunjungan']) {
			$errors[] = 'format jeniskunjungan yang dikirim tidak valid';
		}
		if (!$decode['kodedokter']) {
			$errors[] = 'format kodedokter yang dikirim tidak valid';
		}
		if (!empty($errors)) {
			$i=0;
			foreach ($errors as $error) {
				$response = array(
					'metadata' => array(
						'message' => validation_errors($error),
						'code' => 201,
						'data' => $konten
					)
				);
				$i++;
				
				
			}
			
			$err= validation_errors($error);
				$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'$err,tanggal Periksa $tanggalperiksa,kodepoli: $kodepoli, $kodebpjs - $nomorkartu - $kodebookingrsk')");
		} else if (empty($errors)) {
			$sqlGetBooking = "SELECT count(*) AS jmlKode from quebpjs where kodebooking='$kodebookingrsk'";
			$resultBooking = $con->query($sqlGetBooking);
			$rowBooking = $resultBooking->fetch_array();


			if ($rowBooking['jmlKode'] >= 1) {
				$response = array(
					'metadata' => array(
						'message' => 'Gagal mendapatkan kode booking , Silahkan ulangi kembali!',
						'code' => 201
					)
				);
				
				$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'Gagal mendapatkan kode booking , Silahkan ulangi kembali!$tanggalperiksa  $kodebpjs - $nomorkartu - $kodebookingrsk')");
			} else if ($rowBooking['jmlKode'] <= 0) {

				$sqlins = "INSERT INTO quebpjs (kodebooking, nomorantrean, nomorkartu, nik, nomorrm, notelp, tanggalperiksa, kodepoli, nomorreferensi, jenisreferensi, jenisrequest, polieksekutif, estimasidilayani,jamdilayani, dokter, statusterlayani, waktu_ditambahkan, waktu_diperbaharui,jenisAntrean,kodeDokter,email,app,kode_antrean,nama,noRM) VALUES ('$kodebookingrsk', '$kodeantreanrsk', '$nomorkartu','$nik', '$norm', '$nohp', '$tanggalperiksa', '$kodepoli', '$nomorreferensi', '1', '1', '0', '$estimate','$jadwalmulai', '$rowID[doctor_name]','N',CURTIME(),CURTIME(),'BPJS','$rowID[DoctorCode]','-','JKN','A','','$norm')";

				if ($con->query($sqlins) === TRUE) {
					$getDoctor = "SELECT DoctorName from doctor where bpjsCode='$decode[kodedokter]'";
					$resultDoctor = $con->query($getDoctor);
					$rowDoctor = $resultDoctor->fetch_array();

					$sqlcreate = "INSERT INTO que_antrean (kodebooking, nomorantrean, tanggalperiksa, waktu, estimasidilayani, jamdilayani,jenisDaftar,jenisPasien,klinik,app,waktu_tunggu,kode_antrean,status_panggil) VALUES ('$kodebookingrsk', '$kodeantreanrsk', '$tanggalperiksa', NOW(), '$estimate', '$jadwalmulai','RAJAL','BPJS','$rowID[DeptCode]','JKN','1','A','')";


					$con->query($sqlcreate);

					$sqlUpdateQuota = "UPDATE quota set quotaJknNow=quotaJknNow - 1 where quotaDate='$tanggalperiksa' and DoctorCode='$rowID[DoctorCode]'";
					$con->query($sqlUpdateQuota);

					$timeline = $con->query("INSERT INTO waktu_tunggu (kodebooking, waktu_tunggu, waktu_ditambahkan, user) VALUES ('$kodebookingrsk', '1', NOW(), 'JKN')");

					$kodebpjs = "A" . $kodeantreanrsk . "";
					$response = array(
						'response' => array(
							'nomorantrean' => $kodebpjs,
							'angkaantrean' => $kodeantreanrsk,
							'kodebooking' => $kodebookingrsk,
							'norm' => '0',
							'namapoli' => $rowID['department_name'],
							'namadokter' => $rowDoctor['DoctorName'],
							'estimasidilayani' => $estimate,
							'sisakuotajkn' => $rowID['quotaJknNow'],
							'kuotajkn' => $rowID['quotaJkn'],
							'sisakuotanonjkn' => $rowID['quotaNonJknNow'],
							'kuotanonjkn' => $rowID['quotaNonJkn'],
							'keterangan' => 'Peserta harap datang lebih awal 60 menit sebelum jam buka'
						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
					// $sqlUpdateQuota = "UPDATE quota set quotaJknNow=quotaJknNow - 1 where quotaDate='$decode[tanggalperiksa]' and DoctorCode='$rowID[DoctorCode]'";
					// $con->query($sqlUpdateQuota);
				}
				
				$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'$kodebpjs - $nomorkartu - $kodebookingrsk - $nomorreferensi -$tanggalperiksa')");
			} else {
				$response = array(
					'metadata' => array(
						'message' => 'Gagal mendapatkan nomor antrean,  Hubungi IT RS Sekar Kamulyan !',
						'code' => 201
					)
				);
				$err= validation_errors($error);
				$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'$kodebpjs - $nomorkartu , gagal mendapatkan antrean $err , $nomorreferensi')");
			}
		}
	}
	if (($header['x-token'] != $RSKauth) or ($header['x-username'] != $username)) {
		$response = array(
			'metadata' => array(
				'message' => 'Token tidak valid !',
				'code' => 201
			)
		);
		
		$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'Token tidak valid')");
	}
	if ((!$header['x-token']) or (!$header['x-username'])) {
		$response = array(
			'metadata' => array(
				'message' => 'Token tidak valid !',
				'code' => 201,
				'data' => $header
			)
		);
		
		$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'Token tidak valid')");
	}

	echo json_encode($response);
} else {
	$response = array(
		'metadata' => array(
			'message' => 'Sorry,You are not authorized to access this application !',
			'code' => 201
		),
		'author' => array(
			'name' => 'SISFO RS SEKAR KAMULYAN',
			'developer' => 'Team SISFO',
			'email' => 'sisfo@rssekarkamulyan.com'
		)

	);
	
	$insertAPI= $con->query("insert into api_hit value('Get Antrean',NOW(),0,'akses tidak diijinkan')");
	echo json_encode($response);
}
