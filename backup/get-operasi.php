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
			
			$jumlah_karakter = strlen($decode['nopeserta']);
			if ($jumlah_karakter < 13){
				$response = array(
						'metadata' => array(
						'message' => 'nomor peserta kurang dari 13 digit !',
						'code' => 201
					)
				);
			}
			if (!$decode['nopeserta']){
				$response = array(
						'metadata' => array(
						'message' => 'Format nomor peserta tidak valid !',
						'code' => 201
					)
				);
			}
			else if ($jumlah_karakter >= 13){
				$getJadwalOperasi = $con->query("SELECT * FROM operasi INNER JOIN department ON operasi.kodepoli = department.DeptCode WHERE nopeserta='$decode[nopeserta]' AND tipepasien='BPJS'");
				if ($getJadwalOperasi->num_rows > 0) {
					
					while($data = $getJadwalOperasi->fetch_assoc()) {
						$data_array[] = array(
							'kodebooking' => $data['kodebooking'],
							'tanggaloperasi' => $data['tanggaloperasi'],
							'jenistindakan' => $data['jenistindakan'],
							'kodepoli' => $data['bpjsID'],
							'namapoli' => $data['DeptName'],
							'terlaksana' => $data['terlaksana'],
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
				else if ($getJadwalOperasi->num_rows <= 0){
					$response = array(
						'response' => array(
								'message' => 'Tidak ada data !',
								'code' => 201
						)
					);
				  }	
			
				else {
					$response = array(
						'response' => array(
								'message' => 'Hubungi administrator sistem !',
								'code' => 201
						)
					);
				}
			}	
			else {
				$response = array(
						'response' => array(
								'message' => 'Hubungi administrator sistem !',
								'code' => 201
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
