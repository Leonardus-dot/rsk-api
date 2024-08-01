<?php
	ini_set('display_errors', 0);
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	date_default_timezone_set('UTC');

	$today = date('Y-m-d');
	$time = date('Y-m-d H:i:s');
	require_once "config.php";

	$method = $_SERVER['REQUEST_METHOD'];
	$action = isset($_GET["act"]) ? $_GET["act"] : null;
	

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
		

			
			

	if (!$decode['nosurat']){
		$response = array(
				'metadata' => array(
				'message' => 'Nomor surat kontrol tidak valid !',
				'code' => 201
			)
		);
	}
	else if ($decode['nosurat']){
		$getKontrol = $con->query("SELECT
										surat_kontrol.nomor_surat,
										surat_kontrol.no_rm,
										surat_kontrol.nik,
										surat_kontrol.no_jkn,
										surat_kontrol.no_rujukan,
										surat_kontrol.nama_pasien,
										surat_kontrol.diagnosa,
										surat_kontrol.terapi,
										surat_kontrol.tanggal_rujukan,
										surat_kontrol.tanggal_kontrol,
										surat_kontrol.addDate,
										surat_kontrol.addTime,
										surat_kontrol.addUser,
										surat_kontrol.`status`,
										surat_kontrol.dokter,
										surat_kontrol.nosep,
										surat_kontrol.klinik,
										surat_kontrol.nosuratjkn,
										doctor.bpjsCode,
										doctor.DoctorName,
										department.DeptName
										FROM
										surat_kontrol
										INNER JOIN doctor ON surat_kontrol.dokter = doctor.DoctorCode
										INNER JOIN department ON surat_kontrol.klinik = department.bpjsID
										 WHERE nosuratjkn='$decode[nosurat]' AND status='AKTIF'");
		if ($getKontrol->num_rows > 0) {
			
			
			
			while($data = $getKontrol->fetch_assoc()) {
					$response = array(
					"metaData" => 200,
					"message" => 'OK',
					"List" => array('noSuratBPJS' => $data['nosuratjkn'],
						'noSuratRS' => $data['nomor_surat'],
						'noJkn' => $data['no_jkn'],
						'noRujukan' => $data['no_rujukan'],
						'diagnosa' => $data['diagnosa'],
						'namaPasien' => $data['nama_pasien'],
						'kodeDokter' => $data['bpjsCode'],
						'namaDokter' => $data['DoctorName'],
						'kodeKlinik' => $data['klinik'],
						'namaKlinik' => $data['DeptName'],
						'tglKontrol' => $data['tanggal_kontrol']
					)
				);
						
			}
		
		}
		else if ($getKontrol->num_rows <= 0){
			$response = array(
				"metaData" => 201,
					"message" => 'Tidak ada data',
		
			);
		}	
	
	}	

	
	echo json_encode($response);


?>
