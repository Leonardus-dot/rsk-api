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
			$status= $con->query("SELECT department.DeptName as namapoli, doctor.DoctorName as namadokter, (SELECT COUNT(*) FROM quebpjs WHERE kodeDokter=doctor.DoctorCode AND tanggalperiksa='$decode[tanggalperiksa]') as totalantrean, (SELECT number_live FROM counter_live where live_status='1') as antreanpanggil, quota.quotaJknNow +  quota.quotaNonJknNow  as sisaantrean,quota.quota, quota.quotaJkn as kuotajkn, quota.quotaJknNow as sisakuotajkn, quota.quotaNonJkn as kuotanonjkn , quota.quotaNonJknNow as sisakuotanonjkn, 'Harap datang 15 menit lebih awal' as keterangan,bpjsID,bpjsCode FROM quota INNER JOIN doctor ON quota.DoctorCode = doctor.DoctorCode INNER JOIN department ON quota.DeptCode = department.DeptCode WHERE quotaDate='$decode[tanggalperiksa]' and bpjsID='$decode[kodepoli]' and bpjsCode='$decode[kodedokter]'");
			
			$rowstatus = $status->fetch_array();
			
			$poli= $con->query("SELECT count(*) as jml from department where bpjsID='$decode[kodepoli]'");
			$rowpoli = $poli->fetch_array();
			
			$doctor= $con->query("SELECT count(*) as jml from doctor where bpjsCode='$decode[kodedokter]'");
			$rowdoctor = $doctor->fetch_array();
			
			if($rowpoli['jml'] <=0 ) {
				 $response = array(
						'metadata' => array(
						'message' => 'Maaf,poli tidak ditemukan!',
						'code' => 201
					)
				);	
			}
			else if($rowdoctor['jml'] <=0 ) {
				 $response = array(
						'metadata' => array(
						'message' => 'Maaf,dokter tidak ditemukan!',
						'code' => 201
					)
				);	
			}
			
			else if($decode['tanggalperiksa'] < $today) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf , tanggal tidak boleh kurang dari hari ini!',
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
			else if(empty($decode['kodedokter'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode dokter tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['tanggalperiksa'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,tanggal periksa tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,Tanggal periksa tidak valid!',
						'code' => 201
					)
				);	
			}
			else if ($status->num_rows <= 0){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf, Tidak ada data !',
						'code' => 201
					)
				);	
			}
			
			else if ($status->num_rows > 0){
				
				$lastupdate = strtotime($time) * 1000;
				$response = array(
						'response' => array(
						
							'namapoli'=> $rowstatus['namapoli'],
							'namadokter'=> $rowstatus['namadokter'],
							'totalantrean'=> $rowstatus['totalantrean'],
							'sisaantrean'=> $rowstatus['sisaantrean'],
							'antreanpanggil'=>$rowstatus['antreanpanggil'],
							'sisakuotajkn'=>$rowstatus['sisakuotajkn'],
							'kuotajkn'=> $rowstatus['kuotajkn'],
							'sisakuotanonjkn'=>$rowstatus['sisakuotanonjkn'],
							'kuotanonjkn'=> $rowstatus['kuotanonjkn'],
							'keterangan'=> $rowstatus['keterangan'],				
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
