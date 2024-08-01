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
		
		if (($header['x-token'] == $RSKauth )) {
			
			$getJadwalOperasi = $con->query("SELECT * from operasi where kodebooking='$decode[kodebooking]'");
			$data = $getJadwalOperasi->fetch_assoc();

			if ($getJadwalOperasi->num_rows > 0) {
				$insert="UPDATE operasi SET status_konfirmasi='$decode[status_konfirmasi]' WHERE kodebooking='$decode[kodebooking]'";	
			}
			
			if ($getJadwalOperasi->num_rows <= 0) {
				$insert="INSERT INTO operasi (kodebooking, norm, namapasien, tipepasien, nopeserta, tanggaloperasi, jenistindakan, kodepoli, terlaksana, waktu_ditambahkan, waktu_diperbaharui,kamar_operasi,status_konfirmasi,jenisoperasi,doctor) VALUES ('$decode[kodebooking]', '$decode[norm]', '$decode[namapasien]', '$decode[tipepasien]', '$decode[nopeserta]', '$decode[tanggaloperasi]', '$decode[jenistindakan]', '$decode[kodepoli]', '$decode[terlaksana]', '$decode[waktu_ditambahkan]', '$decode[waktu_diperbaharui]','$decode[kamar_operasi]','$decode[status_konfirmasi]','$decode[jenisoperasi]','$decode[doctor]')";
			}
			if ($con->query($insert) === TRUE) {
					$response = array(
						'metadata' => array(
						'message' => 'Sukses',
						'code' => 200
					)
				);
			}
			else {
					$response = array(
						'metadata' => array(
						'message' => 'Gagal Menambahkan Data !',
						'code' => 201
					)
				);
			}
		}
		if (($header['x-token'] != $RSKauth )) {
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
