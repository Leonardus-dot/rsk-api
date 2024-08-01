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
			
			$tgl = "$decode[tanggalperiksa]";
			$pecah = explode("-", $tgl);
			
			$getdepartment="select count(*) as jumlah from department where bpjsID='$decode[kodepoli]'";
			$resultdepartment= $con->query($getdepartment);
			$rowdepartment = $resultdepartment->fetch_array();
			
			$tgl = "$decode[tanggalperiksa]";
			$pecah = explode("-", $tgl);
			$jumlah_karakter = strlen($decode['nomorkartu']);
			
			$getrekap="SELECT Count(quebpjs.nomorantrean) AS totalantrean, department.DeptName FROM quebpjs INNER JOIN department ON quebpjs.kodepoli = department.bpjsID where tanggalperiksa='$decode[tanggalperiksa]' and kodepoli='$decode[kodepoli]'";
			$resultrekap= $con->query($getrekap);
			$rowrekap = $resultrekap->fetch_array();
			
			$getrekapterlayani="SELECT Count(*) AS totalterlayani FROM quebpjs where statusterlayani='Y' and tanggalperiksa='$decode[tanggalperiksa]' and kodepoli='$decode[kodepoli]'";
			$resultrekapterlayani= $con->query($getrekapterlayani);
			$rowrekapterlayani = $resultrekapterlayani->fetch_array();
			
			if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal periksa tidak valid!',
						'code' => 201
					)
				);	
			}
			else if(empty($decode['kodepoli'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode poli tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if (!checkdate($pecah[1], $pecah[2], $pecah[0])){ 
					$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal tidak valid !',
					'code' => 201
					)
				);	
			}
			else if ($rowdepartment['jumlah'] <= 0){
				$response = array(
					'metadata' => array(
					'message' => 'Kode poli tidak ditemukan !',
					'code' => 201
					)
				);
			}
			else if($rowrekap['totalantrean'] <=0){
					$response = array(
					'metadata' => array(
					'message' => 'Status antrean 0 !',
					'code' => 200
					)
				);
			}
			else {
				
				$lastupdate = strtotime($time) * 1000;
				$response = array(
						'response' => array(
							'namapoli' => $rowrekap['DeptName'],
							'totalantrean' => $rowrekap['totalantrean'],
							'jumlahterlayani' => $rowrekapterlayani['totalterlayani'],
							'lastupdate' => $lastupdate,
							'lastupdatetanggal' =>$time ,
							
						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
			}
			
		}
		else if (($header['x-token'] != $RSKauth )) {
				$response = array(
					'metadata' => array(
					'message' => 'Token Gagal',
					'code' => 201
				)
			);
		}
		else {
				$response = array(
					'metadata' => array(
					'message' => 'Hubungi Administrator RS Sekar Kamulyan !',
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
