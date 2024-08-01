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
			
			if(empty($decode['nomorkartu'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor kartu tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if (mb_strlen($decode['nomorkartu'], 'UTF-8') < 13){
        	    $response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor kartu harus 13 digit!',
						'code' => 201
					)
				);	
            }
			else if(mb_strlen($decode['nik'], 'UTF-8') < 16){
				$response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor kartu harus 16 digit !',
						'code' => 201
					)
				);	
			}
			else if(empty($decode['nik'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nik tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['nama'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nama tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['jeniskelamin'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,jenis kelamin tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['nohp'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor hp tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['alamat'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,alamat tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['kodeprop'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode propinsi tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['namaprop'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,Nama propinsi tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['kodedati2'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode dati tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['namadati2'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nama dati tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['kodekec'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kecamatan tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['namakec'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nama keceamatan tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['kodekel'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,kode kelurahan tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['namakel'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nama kelurahan tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['rw'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor RW tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else if(empty($decode['rt'])) {
                $response = array(
						'metadata' => array(
						'message' => 'Maaf,nomor RT tidak boleh kosong!',
						'code' => 201
					)
				);	
            }
			else {
				
				$getNIK="SELECT count(*) as sumkartu from admission_jkn where nik='$decode[nik]'";
				$resultNIK= $con->query($getNIK);
				$rowNIK = $resultNIK->fetch_array();
				
				$getBPJS="SELECT count(*) as nomorkartu from admission_jkn where nomorkartu='$decode[nomorkartu]'";
				$resultBPJS= $con->query($getBPJS);
				$rowBPJS = $resultBPJS->fetch_array();
				
				
				if($rowNIK['sumkartu'] >=1){
					 $response = array(
							'metadata' => array(
							'message' => 'Gagal mendaftarkan , NIK sudah terdaftar !',
							'code' => 201
						)
					);
				}
				if($rowBPJS['nomorkartu'] >=1){
					 $response = array(
							'metadata' => array(
							'message' => 'Gagal mendaftarkan , Nomor Kartu BPJS sudah terdaftar !',
							'code' => 201
						)
					);
				}
				else if(($rowNIK['sumkartu'] <=0) && ($rowBPJS['nomorkartu'] <=0)) {
				
					$getRM="SELECT MAX(id_adm) as noRM from admission_jkn";
					$resultRM= $con->query($getRM);
					$rowRM = $resultRM->fetch_array();

					$noRM = (int) substr($rowRM['noRM'], 2, 7);
					$noRM++;
					$charRM = "RM";
					$kodeRM = $charRM . sprintf("%07s", $noRM);
					
					$insertpatient="INSERT INTO admission_jkn (id_adm, tanggal_daftar, nomorkartu, nik, nomorkk, nama, jeniskelamin, tanggallahir, nohp, alamat, kodeprop, namaprop, kodedati2, namadati2, kodekec, namakec, kodekel, namakel, rw, rt, waktu_tambah, status_didaftarkan) VALUES ('$kodeRM', DATE(NOW()), '$decode[nomorkartu]', '$decode[nik]', '$decode[nomorkk]', '$decode[nama]', '$decode[jeniskelamin]', '$decode[tanggallahir]', '$decode[nohp]', '$decode[alamat]', '$decode[kodeprop]', '$decode[namaprop]', '$decode[kodedati2]', '$decode[namadati2]', '$decode[kodekec]', '$decode[namakec]', '$decode[kodekel]', '$decode[namakel]', '$decode[rw]', '$decode[rt]', NOW(), 'MENUNGGU')";
					
					if ($con->query($insertpatient) === TRUE) {
					
					$response = array(
							'response' => array(
								'norm' => $kodeRM
							),
							'metadata' => array(
								'message' => 'Harap datang ke admisi/ pendaftaran untuk melengkapi data rekam medis',
								'code' => 200
							)
						);
					}
					else {
							 $response = array(
								'metadata' => array(
								'message' => 'Gagal mendapatkan nomor rekam medis pasien !',
								'code' => 201
							)
						);
					}
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
