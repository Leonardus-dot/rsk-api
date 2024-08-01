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
			
			$tgl = "$decode[tanggalakhir]";
			$pecah = explode("-", $tgl);
			
			if (empty($decode['tanggalawal'])){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal awal harus di isi!',
						'code' => 201
					)
				);	
			}
			else if (empty($decode['tanggalakhir'])){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal akhir harus di isi!',
						'code' => 201
					)
				);	
			}
			else if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalawal'])) {
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal awal tidak valid!',
						'code' => 201
					)
				);	
			}
			else if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalakhir'])) {
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal akhir tidak valid!',
						'code' => 201
					)
				);	
			}
			else if(!$decode['tanggalawal']){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,format tanggal awal & akhir tidak valid !',
						'code' => 201
					)
				);	
			}
			else if(empty($decode['tanggalakhir'])){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,format akhir tidak valid  !',
						'code' => 201
					)
				);	
			}
			else if ($decode['tanggalawal'] > $decode['tanggalakhir']){ 
					$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal awal tidak boleh lebih dari tanggal akhir !',
					'code' => 201
					)
				);	
			}
			else if($decode['tanggalakhir'] > $today && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalawal']) ){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal format tanggal yang dikirimkan salah !',
						'code' => 201
					)
				);	
			}
			
			else {
				$getJadwalOperasi = $con->query("SELECT * FROM operasi INNER JOIN department ON operasi.kodepoli = department.DeptCode WHERE tanggaloperasi >='$decode[tanggalawal]' and tanggaloperasi <='$decode[tanggalakhir]' AND tipepasien='BPJS'");
				
				if ($getJadwalOperasi->num_rows > 0) {
			
					while($data = $getJadwalOperasi->fetch_assoc()) {
						$data_array[] = array(
							'kodebooking' => $data['kodebooking'],
							'tanggaloperasi' => $data['tanggaloperasi'],
							'jenistindakan' => $data['jenistindakan'],
							'kodepoli' => $data['bpjsID'],
							'namapoli' => $data['DeptName'],
							'terlaksana' => $data['terlaksana'],
							'nopeserta' => $data['nopeserta'],
							'lastupdate' => strtotime(date('H:i:s')) * 1000
						);
					}
					 $response = array(
						'response' => array(
							'list' => (
								$data_array
							)
						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
				}
				else {
					$response = array(
						'response' => array(
								'message' => 'Tidak ada data !',
								'code' => 200
						)
					);
				}
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
