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
					
					$getkodebooking="SELECT CASE WHEN MAX(RIGHT(kodebooking,3)) + 1 IS NULL THEN  1 ELSE MAX(RIGHT(kodebooking,3)) + 1 END as kodebooking from que_antrean where tanggalperiksa=DATE(NOW())";
					$resultkodebooking= $con->query($getkodebooking);
					$rowkodebooking = $resultkodebooking->fetch_array();

					$nobooking = (int) substr($rowkodebooking['kodebooking'], 3, 3);
					$nobooking++;
					$charbooking = "RSK";
					$kodebookingrsk = "".$charbooking."".$number_transaction."" . sprintf("%03s", $rowkodebooking['kodebooking']);
				
					$getRM="SELECT CASE WHEN MAX(RIGHT(id_adm,3)) + 1 IS NULL THEN 1 ELSE MAX(RIGHT(id_adm,3)) + 1 END as noRM from admission_jkn";
					$resultRM= $con->query($getRM);
					$rowRM = $resultRM->fetch_array();

					$noRM = (int) substr($rowRM['noRM'], 2, 7);
					$noRM++;
					$charRM = "RM";
					$kodeRM = "".$charbooking."".$number_transaction."" . sprintf("%03s", $rowRM['noRM']);
					
					$insertpatient="INSERT INTO admission_jkn (id_adm, tanggal_daftar, nomorkartu, nik, nomorkk, nama, jeniskelamin, tanggallahir, nohp, alamat, kodeprop, namaprop, kodedati2, namadati2, kodekec, namakec, kodekel, namakel, rw, rt, waktu_tambah, status_didaftarkan,kodebooking) VALUES ('$kodeRM', DATE(NOW()), '$decode[nomorkartu]', '$decode[nik]', '$decode[nomorkk]', '$decode[nama]', '$decode[jeniskelamin]', '$decode[tanggallahir]', '$decode[nohp]', '$decode[alamat]', '$decode[kodeprop]', '$decode[namaprop]', '$decode[kodedati2]', '$decode[namadati2]', '$decode[kodekec]', '$decode[namakec]', '$decode[kodekel]', '$decode[namakel]', '$decode[rw]', '$decode[rt]', NOW(), 'MENUNGGU','$kodebookingrsk')";
					
					if ($con->query($insertpatient) === TRUE) {
						
			
					$gettime="SELECT MAX(nomorantrean) as kodeantrean,max(jamdilayani) as jam from que_antrean where tanggalperiksa=DATE(NOW())";
					$resulttime= $con->query($gettime);
					$rowtime = $resulttime->fetch_array();
					
					$getkodeantrean="SELECT MAX(nomorantrean) as kodeantrean from que_antrean where tanggalperiksa=DATE(NOW())";
					$resultkodeantrean= $con->query($getkodeantrean);
					$rowkodeantrean = $resultkodeantrean->fetch_array();
					
					$noantrean = (int) substr($rowkodeantrean['kodeantrean'], 1, 3);
					$noantrean++;
					$charantrean = "A";
					$kodeantreanrsk = $charantrean . sprintf("%03s", $noantrean);
					
					$getcount="SELECT count(nomorantrean) as kodeantrean from que_antrean where tanggalperiksa=DATE(NOW())";
					$resultcount= $con->query($getcount);
					$rowcount = $resultcount->fetch_array();
					
					if($rowcount['kodeantrean'] <= 0){
						$jadwalmulai= '09:00:00';
						$estimate=strtotime('09:00:00') * 1000;
					}
					
					if($rowcount['kodeantrean'] >= 1){
						
						$time=$rowtime['jam'];
						$date = date_create($time);
						date_add($date, date_interval_create_from_date_string('10 minutes'));
						$jadwalmulai= date_format($date, 'H:i:s');
						$estimate=strtotime($jadwalmulai) * 1000;
					}
					
					$sqlins = "INSERT INTO quebpjs (kodebooking, nomorantrean, nomorkartu, nik, nomorrm, notelp, tanggalPeriksa, kodepoli, nomorreferensi, jenisreferensi, jenisrequest, polieksekutif, estimasidilayani,jamdilayani, dokter, statusterlayani, waktu_ditambahkan, waktu_diperbaharui,jenisAntrean,kodeDokter,email) VALUES ('$kodebookingrsk', '$kodeantreanrsk', '$decode[nomorkartu]','$decode[nik]', '0', '0', DATE(NOW()), '-', '0', '1', '1', '0', '$estimate','$jadwalmulai', '','N',CURTIME(),CURTIME(),'BPJS','','-')";
					
					if ($con->query($sqlins) === TRUE) {
						$sqlCREATE ="INSERT INTO que_antrean (kodebooking, nomorantrean, tanggalperiksa, waktu, estimasidilayani, jamdilayani,jenisDaftar,jenisPasien,klinik) VALUES ('$kodebookingrsk', '$kodeantreanrsk', DATE(NOW()), NOW(), '$estimate', '$jadwalmulai','RAJAL','BPJS','-')";
						
						$con->query($sqlCREATE);
					
					}
					
					
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
