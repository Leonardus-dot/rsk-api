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

    switch ((isset($action) ? $action : "")) {

        case"token":
		
			$header = apache_request_headers();
		    $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            $response = array();
		
			if (($decode['username'] == $username) && ($decode['password'] == $password)){
					$response = array(
						'response' => array(
							'token' => $RSKauth
						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
			} 
			else if (($decode['username'] == null) && ($decode['password'] == null)){
					$response = array(
                    'status' => array(
                        'message' => 'Access denied',
                        'code' => 201
                    )
                );
			} 
			else if (!($decode['username']) && (!$decode['password'])){
					$response = array(
                    'status' => array(
                        'message' => 'Access denied',
                        'code' => 201
                    )
                );
			} 
			else {
                $response = array(
                    'status' => array(
                        'message' => 'Access denied',
                        'code' => 201
                    )
                );
            }
            echo json_encode($response);

	break;
	
	case"get-antrean":
	
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
		
		
		
		$sql = "SELECT Count(quota.quotaJknNow) AS count, quota.quotaJknNow as quota , quota.quotaJkn, quota.quotaJknNow, quota.quotaNonJkn, quota.quotaNonJknNow,DoctorCode,(select DoctorName from doctor WHERE DoctorCode=quota.DoctorCode) as doctor_name,(SELECT DeptName FROM department WHERE DeptCode=quota.DeptCode) as department_name,(SELECT bpjsID FROM department WHERE DeptCode=quota.DeptCode) as bpjsID FROM quota WHERE quotaDate='$decode[tanggalperiksa]' having bpjsID ='$kodepoli'";
		
		$result = $con->query($sql);
		$rowID = $result->fetch_array();
			
		if (($header['x-token'] == $RSKauth )) {
				
				$sqlPoli = "SELECT Count(*) AS countPoli from department where bpjsID ='$kodepoli'";
				$resultPoli = $con->query($sqlPoli);
				$rowIDPoli = $resultPoli->fetch_array();
			
				$getkodebooking="SELECT MAX(kodebooking) as kodebooking from quebpjs";
				$resultkodebooking= $con->query($getkodebooking);
				$rowkodebooking = $resultkodebooking->fetch_array();

				$nobooking = (int) substr($rowkodebooking['kodebooking'], 3, 3);
				$nobooking++;
				$charbooking = "RSK";
				$kodebookingrsk = $charbooking . sprintf("%03s", $nobooking);
				
				$getkodeantrean="SELECT MAX(nomorantrean) as kodeantrean from quebpjs where tanggalperiksa='$tanggalperiksa'";
				$resultkodeantrean= $con->query($getkodeantrean);
				$rowkodeantrean = $resultkodeantrean->fetch_array();
				
				$getnomorkartu="SELECT count(*) as sumkartu from quebpjs where tanggalperiksa='$tanggalperiksa' and nomorkartu='$nomorkartu'";
				$resultnomorkartu= $con->query($getnomorkartu);
				$rownomorkartu = $resultnomorkartu->fetch_array();

				$noantrean = (int) substr($rowkodeantrean['kodeantrean'], 1, 2);
				$noantrean++;
				$charantrean = "A";
				$kodeantreanrsk = $charantrean . sprintf("%02s", $noantrean);
				
				/* Estimasi pelayanan di pendaftaran */
				
				$gettime="SELECT MAX(nomorantrean) as kodeantrean,max(jamdilayani) as jam from quebpjs where tanggalperiksa='$tanggalperiksa'";
				$resulttime= $con->query($gettime);
				$rowtime = $resulttime->fetch_array();
				
				$getcount="SELECT count(nomorantrean) as kodeantrean from quebpjs where tanggalperiksa='$tanggalperiksa'";
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
				$tgl = "$decode[tanggalperiksa]";
				$pecah = explode("-", $tgl);
				$jumlah_karakter = strlen($nomorkartu);
				
				if (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') < 13){
        	         $errors[] = 'Nomor kartu harus 13 digit';
                }
                if(empty($decode['nik'])) {
                  $errors[] = 'Nomor KTP tidak boleh kosong';
                }
                if(!empty($decode['nik']) && mb_strlen($decode['nik'], 'UTF-8') < 16){
        	         $errors[] = 'Format nomor KTP tidak sesuai';
                }
				if (!checkdate($pecah[1], $pecah[2], $pecah[0])){ 
					$errors[] = 'Format tanggal periksa tidak sesuai';
				}
                if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
                   $errors[] = 'Format tanggal periksa tidak sesuai';
                }
                if(empty($decode['tanggalperiksa'])) {
                   $errors[] = 'Tanggal periksa tidak boleh kosong';
                }
                if($decode['tanggalperiksa'] == $date && strtotime($decode['tanggalperiksa']) < strtotime('+7 days')) {
                   $errors[] = 'Tanggal periksa harus H+1 sampai H+7';
                }
                if(empty($decode['kodepoli'])) {
                   $errors[] = 'Kode poli tidak boleh kosong';
                }
                if(!empty($decode['kodepoli']) && $rowIDPoli['countPoli'] =='0') {
                   $errors[] = 'Kode poli tidak ditemukan';
                }
				if($rowID['quota'] <=0){
				   $errors[] = 'Quota antrean tidak tersedia';
				}
				if($rownomorkartu['sumkartu'] >=1){
					$errors[] = 'Nomor kartu tersebut sudah mendapatkan nomor antrean sebelumnya pada hari dan tgl yang sama';
				}
				if ($rowID['quota'] ==0){
					$errors[] = 'Kouta antrean tidak tersedia';
				}
				if(!$decode['nomorkartu']) {
        	         $errors[] = 'format yang dikirim tidak valid 1';
                }
				if(!$decode['nik']) {
        	         $errors[] = 'format yang dikirim tidak valid 2';
                }
				if(!$decode['norm']) {
        	         $errors[] = 'format yang dikirim tidak valid 3';
                }
				if(!$decode['tanggalperiksa']) {
        	         $errors[] = 'format yang dikirim tidak valid';
                }
				if(!$decode['kodepoli']) {
        	         $errors[] = 'format yang dikirim tidak valid';
                }
				if(!$decode['nomorreferensi']) {
        	         $errors[] = 'format yang dikirim tidak valid';
                }
				if(!$decode['jeniskunjungan']) {
        	         $errors[] = 'format yang dikirim tidak valid';
                }
				if(!$decode['kodedokter']) {
        	         $errors[] = 'format yang dikirim tidak valid';
                }
                if(!empty($errors)) {
          	        foreach($errors as $error) {
                        $response = array(
                            'metadata' => array(
                                'message' => validation_errors($error),
                                'code' => 201
                            )
                        );
          	        }
                } 
				else if(empty($errors)) {
					
				$sqlins = "INSERT INTO quebpjs (kodebooking, nomorantrean, nomorkartu, nik, nomorrm, notelp, tanggalperiksa, kodepoli, nomorreferensi, jenisreferensi, jenisrequest, polieksekutif, estimasidilayani,jamdilayani, dokter, statusterlayani, waktu_ditambahkan, waktu_diperbaharui) VALUES ('$kodebookingrsk', '$kodeantreanrsk', '$nomorkartu','$nik', '$nomorrm', '$nohp', '$tanggalperiksa', '$kodepoli', '$nomorreferensi', '$jenisreferensi', '$jenisrequest', '$polieksekutif', '$estimate','$jadwalmulai', '$rowID[doctor_name]','N',CURTIME(),CURTIME())";

				if ($con->query($sqlins) === TRUE) {
					$getDoctor="SELECT DoctorName from doctor where bpjsCode='$decode[kodedokter]'";
					$resultDoctor= $con->query($getDoctor);
					$rowDoctor = $resultDoctor->fetch_array();
					
						$response = array(
							'response' => array(
								'nomorantrean' => $kodeantreanrsk,
								'angkaantrean' => $kodeantreanrsk,
								'kodebooking' => $kodebookingrsk,
								'pasiembaru' => '0',
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
						$sqlUpdateQuota = "UPDATE quota set quotaJknNow=quotaJknNow - 1 where quotaDate='$decode[tanggalperiksa]' and DoctorCode='$rowID[DoctorCode]'";
						$con->query($sqlUpdateQuota);
					}
				else {
						$response = array(
							'metadata' => array(
							'message' => 'Gagal mendapatkan nomor antrean,  Hubungi IT RS Sekar Kamulyan !',
							'code' => 201
							)
						);
					}
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
		break;  
		
		
		
		case"rekap-antrean":
		
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
		break;  
		
		case"sisa-antrean":
		
		$header = apache_request_headers();
		$konten = trim(file_get_contents("php://input"));
		$decode = json_decode($konten, true);
		$response = array();
		
		if (($header['x-token'] == $RSKauth )) {
			$statusbooking= $con->query("SELECT quebpjs.kodebooking, quebpjs.nomorantrean, quebpjs.nomorkartu, quebpjs.nik, quebpjs.nomorrm, quebpjs.notelp, quebpjs.tanggalperiksa, quebpjs.kodepoli, quebpjs.nomorreferensi, quebpjs.jenisreferensi, quebpjs.jenisrequest, quebpjs.polieksekutif, quebpjs.estimasidilayani, quebpjs.jamdilayani, quebpjs.dokter, quebpjs.statusterlayani, quebpjs.waktu_ditambahkan, quebpjs.waktu_diperbaharui, department.DeptName,(SELECT quotaJknNow from quota WHERE quotaDate=quebpjs.tanggalperiksa) as sisaantrean FROM quebpjs INNER JOIN department ON quebpjs.kodepoli = department.bpjsID WHERE kodebooking='$decode[kodebooking]' AND statusterlayani!='B'");
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
							'antreanpanggil' => $rowstatus['nomorantrean'],
							'waktutunggu' => 9000,
							'keterangan' => 'Mohon datang tepat waktu',

						),
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
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
		break; 
		
		case"batal-antrean":
		
		$header = apache_request_headers();
		$konten = trim(file_get_contents("php://input"));
		$decode = json_decode($konten, true);
		$response = array();
		
		if (($header['x-token'] == $RSKauth )) {
			$statusbooking= $con->query("SELECT quebpjs.kodebooking, quebpjs.nomorantrean, quebpjs.nomorkartu, quebpjs.nik, quebpjs.nomorrm, quebpjs.notelp, quebpjs.tanggalperiksa, quebpjs.kodepoli, quebpjs.nomorreferensi, quebpjs.jenisreferensi, quebpjs.jenisrequest, quebpjs.polieksekutif, quebpjs.estimasidilayani, quebpjs.jamdilayani, quebpjs.dokter, quebpjs.statusterlayani, quebpjs.waktu_ditambahkan, quebpjs.waktu_diperbaharui, department.DeptName,(SELECT quotaJknNow from quota WHERE quotaDate=quebpjs.tanggalperiksa) as sisaantrean FROM quebpjs INNER JOIN department ON quebpjs.kodepoli = department.bpjsID WHERE kodebooking='$decode[kodebooking]'");
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
				
				$sqlUpdate = "UPDATE quebpjs set statusterlayani='B',keteranganBatal='$decode[keterangan]' where kodebooking='$decode[kodebooking]'";
				$con->query($sqlUpdate);
				$response = array(
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
			}
			else {
				$response = array(
						'metadata' => array(
							'message' => 'Gagal',
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
		break; 
		
		case"pasien-baru":
		
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
		break; 
		
		case"cekin-antrean":
		
		$header = apache_request_headers();
		$konten = trim(file_get_contents("php://input"));
		$decode = json_decode($konten, true);
		$response = array();
		
		if (($header['x-token'] == $RSKauth )) {
			$statusbooking= $con->query("SELECT quebpjs.kodebooking, quebpjs.nomorantrean, quebpjs.nomorkartu, quebpjs.nik, quebpjs.nomorrm, quebpjs.notelp, quebpjs.tanggalperiksa, quebpjs.kodepoli, quebpjs.nomorreferensi, quebpjs.jenisreferensi, quebpjs.jenisrequest, quebpjs.polieksekutif, quebpjs.estimasidilayani, quebpjs.jamdilayani, quebpjs.dokter, quebpjs.statusterlayani, quebpjs.waktu_ditambahkan, quebpjs.waktu_diperbaharui, department.DeptName,(SELECT quotaJknNow from quota WHERE quotaDate=quebpjs.tanggalperiksa) as sisaantrean FROM quebpjs INNER JOIN department ON quebpjs.kodepoli = department.bpjsID WHERE kodebooking='$decode[kodebooking]'");
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
				
				$sqlUpdate = "UPDATE quebpjs set statusterlayani='Y',waktucekin='$decode[waktu]' where kodebooking='$decode[kodebooking]'";
				$con->query($sqlUpdate);
				$response = array(
						'metadata' => array(
							'message' => 'Ok',
							'code' => 200
						)
					);
			}
			else {
				$response = array(
						'metadata' => array(
							'message' => 'Gagal',
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
		break; 
		
		case"tambah-operasi":
		
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
		break;  
		
		case"jadwal-operasi":
		
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
		break; 
			
		case"get-operasi":
		
		$header = apache_request_headers();
		$konten = trim(file_get_contents("php://input"));
		$decode = json_decode($konten, true);
		$response = array();
		
		if (($header['x-token'] == $RSKauth )) {
			
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
		if (($header['x-token'] != $RSKauth )) {
			$response = array(
						'metadata' => array(
						'message' => 'Token tidak valid !',
						'code' => 201
					)
				);
		}
		
		echo json_encode($response);
		break; 
		
		case"close-operasi":
		
		$header = apache_request_headers();
		$konten = trim(file_get_contents("php://input"));
		$decode = json_decode($konten, true);
		$response = array();
		
		if (($header['x-token'] == $RSKauth )) {
			$updateJadwalOperasi = $con->query("update operasi set terlaksana='$decode[terlaksana]',waktu_diperbaharui='$decode[waktu_diperbaharui]' where kodebooking='$decode[kodebooking]'");
			if ($con->query($updateJadwalOperasi) === TRUE) {
				$response = array(
					'metadata' => array(
						'message' => 'Ok',
						'code' => 200
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
		break; 
		
	}
}
else {
	echo "<!DOCTYPE html>\n"; 
	echo "<html lang=\"en\">\n"; 
	echo "  <head>\n"; 
	echo "    <meta charset=\"utf-8\">\n"; 
	echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n"; 
	echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n"; 
	echo "\n"; 
	echo "    <title>Web Service RS. Sekar Kamulyan</title>\n"; 
	echo "\n"; 
	echo "    <meta name=\"description\" content=\"Webservices RS. Sekar Kamulyan http://www.rssekarkamulyan.com\">\n"; 
	echo "    <meta name=\"author\" content=\"SISFO - RSK\">\n"; 
	echo "\n"; 
	echo "  <link rel=\"icon\" type=\"image/png\" href=\"hhttps://app.rssekarkamulyan.com:8081/api-rsk/img/logo-rsk.png\">\n";
	echo "    <link href=\"https://app.rssekarkamulyan.com:8081/api-rsk/css/bootstrap.min.css\" rel=\"stylesheet\">\n"; 
	echo "    <link href=\"https://app.rssekarkamulyan.com:8081/api-rsk/css/style.css\" rel=\"stylesheet\">\n"; 
	echo "\n"; 
	echo "  </head>\n"; 
	echo "  <body background=\"\">\n"; 
	echo "\n"; 
	echo "    <div class=\"container-fluid\">\n"; 
	echo "	<div class=\"row\">\n"; 
	echo "		<div class=\"col-md-12\">\n"; 
	echo "			<blockquote class=\"blockquote\">\n"; 
	echo "				<p class=\"mb-0\">\n"; 
	echo "					Selamat datang di WEB Service RS. SEKAR KAMULYAN - Mobile JKN \n"; 
	echo "				</p>\n"; 
	echo "				<footer class=\"blockquote-footer\">\n"; 
	echo "					<i>Bridging antrean online & jadwal operasi RS - BPJS <cite>(Mobile JKN)</cite></i>\n"; 
	echo "				</footer>\n"; 
	echo "			</blockquote>\n"; 
	echo "			<div class=\"alert alert-success alert-dismissable\">\n"; 
	echo "				 \n"; 
	echo "				<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">\n"; 
	echo "					×\n"; 
	echo "				</button>\n"; 
	echo "				<h4>\n"; 
	echo "					Pemberitahuan !\n"; 
	echo "				</h4>Informasi username & Password, Silahkan email ke sisfo@rssekarkamulyan.com !<i> <a href=\"https://rssekarkamulyan.com\" class=\"alert-link\">Kunjungi Web</a></i>\n"; 
	echo "			</div>\n"; 
	echo "			Listing (Web Services RS. Sekar Kamulyan). \n"; 
	echo "			</br>\n"; 
	echo "				</br>\n"; 
	echo "			<table class=\"table\" >\n"; 
	echo "				<thead>\n"; 
	echo "					<tr>\n"; 
	echo "						<th>\n"; 
	echo "							#\n"; 
	echo "						</th>\n"; 
	echo "						<th>\n"; 
	echo "							Url\n"; 
	echo "						</th>\n"; 
	echo "						<th>\n"; 
	echo "							Method\n"; 
	echo "						</th>\n"; 
	echo "						<th>\n"; 
	echo "							Token\n"; 
	echo "						</th>\n"; 
	echo "						<th>\n"; 
	echo "							Header\n"; 
	echo "						</th>\n"; 
	echo "						<th>\n"; 
	echo "							Keterangan\n"; 
	echo "						</th>\n"; 
	echo "					</tr>\n"; 
	echo "				</thead>\n"; 
	echo "				<tbody>\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							1.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/token\">https://app.rssekarkamulyan.com:8081/api-rsk/token</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Generate token dengan JWT , token dari hasil generate username & password , didapatkan dari Tim IT RS Sekar Kamulyan.\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							2.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/get-antrean\">https://app.rssekarkamulyan.com:8081/api-rsk/get-antrean</a>\n"; 
	echo "						</td>\n";
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 	
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							GET nomor antrian. \n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							3.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/rekap-antrean\">https://app.rssekarkamulyan.com:8081/api-rsk/rekap-antrean</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Rekap antrean. \n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							4.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/jadwal-operasi\">https://app.rssekarkamulyan.com:8081/api-rsk/jadwal-operasi</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Menampilkan seluruh jadwal operasi \n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							5.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/get-operasi\">https://app.rssekarkamulyan.com:8081/api-rsk/get-operasi</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Menampilkan seluruh jadwal operasi sesuai nomor BPJS\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							6.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/batal-antrean\">https://app.rssekarkamulyan.com:8081/api-rsk/batal-antrean</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Membatalkan antrean\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							7.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/cekin-antrean\">http://app.rssekarkamulyan.com/:8081/api-rsk/cekin-antrean</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Cekin pasien\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							8.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/sisa-antrean\">https://app.rssekarkamulyan.com:8081/api-rsk/sisa-antrean</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Sisa Antrean\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							9.\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/status-antrean\">https://app.rssekarkamulyan.com:8081/api-rsk/status-antrean</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Status Antrean\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							10.\n"; 
	echo "							\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/pasien-baru\">https://app.rssekarkamulyan.com:8081/api-rsk/pasien-baru</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Pendaftaran pasien baru\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							11.\n"; 
	echo "							\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/get-antrean-farmasi\">https://app.rssekarkamulyan.com:8081/api-rsk/get-antrean-farmasi</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Ambil antrean farmasi\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "					<tr>\n"; 
	echo "						<td>\n"; 
	echo "							12.\n"; 
	echo "							\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							<a href=\"https://app.rssekarkamulyan.com:8081/api-rsk/get-status-antrean-farmasi\">https://app.rssekarkamulyan.com:8081/api-rsk/get-status-antrean-farmasi</a>\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							POST\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							JWT (Generate)\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							x-token:{token}\n"; 
	echo "						</td>\n"; 
	echo "						<td>\n"; 
	echo "							Ambil status antrean farmasi\n"; 
	echo "						</td>\n"; 
	echo "					</tr>\n"; 
	echo "					\n"; 
	echo "				</tbody>\n"; 
	echo "			</table>\n"; 
	echo "		</div>\n"; 
	echo "	</div>\n"; 
	echo "</div>\n"; 
	echo "\n"; 
	echo "    <script src=\"js/jquery.min.js\"></script>\n"; 
	echo "    <script src=\"js/bootstrap.min.js\"></script>\n"; 
	echo "    <script src=\"js/scripts.js\"></script>\n"; 
	echo "  </body>\n"; 
	echo "</html>\n";
} 
?>
