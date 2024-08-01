<?php
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
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: POST, GET");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
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
		
		if (($header['x-token'] == $RSKauth ) && ($header['x-username']==$username)) {
			$statusbooking= $con->query("SELECT quebpjs.kodebooking, quebpjs.nomorantrean, quebpjs.nomorkartu, quebpjs.nik, quebpjs.nomorrm, quebpjs.notelp, quebpjs.tanggalperiksa, quebpjs.kodepoli, quebpjs.nomorreferensi, quebpjs.jenisreferensi, quebpjs.jenisrequest, quebpjs.polieksekutif, quebpjs.estimasidilayani, quebpjs.jamdilayani, quebpjs.dokter, quebpjs.statusterlayani, quebpjs.waktu_ditambahkan, quebpjs.waktu_diperbaharui, department.DeptName,(SELECT quotaJknNow from quota WHERE quotaDate=quebpjs.tanggalperiksa and DoctorCode=quebpjs.kodeDokter) as sisaantrean,(SELECT number_live FROM counter_live where live_status='1') as panggil FROM quebpjs INNER JOIN department ON quebpjs.kodepoli = department.bpjsID WHERE kodebooking='$decode[kodebooking]' AND statusterlayani!='B'");
			$rowstatus = $statusbooking->fetch_array();
			if(empty($decode['kodebooking'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode booking tidak boleh kosong!',
						'code' => 201
					)
				);	
            }

			else if ($statusbooking->num_rows <= 0) {
				$response = array(
					'metadata' => array(
					'message' => 'Kode booking tidak ditemukan !',
					'code' => 201
					)
				);
			}
			else if ($statusbooking->num_rows > 0){
				
				$lastupdate = strtotime($time) * 1000;
				$response = array(
						'response' => array(
							'nomorantrean' => $rowstatus['nomorantrean'],
							'namapoli' => $rowstatus['DeptName'],
							'dokter' => $rowstatus['dokter'],
							'sisaantrean' => $rowstatus['sisaantrean'],
							'antreanpanggil' => $rowstatus['panggil'],
							'waktutunggu' => 9000,
							'keterangan' => 'Mohon datang tepat waktu, Terima Kasih',
							
							
						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
			}
		}
		if (($header['x-token'] != $RSKauth ) OR ($header['x-username'] !=$username)) {
			$response = array(
						'metadata' => array(
						'message' => 'Token tidak valid !',
						'code' => 201
					)
				);
		}
		if ((!$header['x-token']) OR (!$header['x-username'])) {
			$response = array(
						'metadata' => array(
						'message' => 'Token tidak valid !',
						'code' => 201
					)
				);
		}
		
		
		echo json_encode($response);
} 
else { 
		$response = array(
				'metadata' => array(
				'message' => 'you are not authorized to access this application !',
				'code' => 201
			)
		);
		echo json_encode($response);

}
?>
