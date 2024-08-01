<?php
	require_once 'lz_string.php';
	//seting timenya ke UTC bray biar sama
	date_default_timezone_set('UTC');
	error_reporting(0);
	
	//consid, secretkey, userkey  didapatkan dari BPJS bray
	$consId = "20161";
	$secretKey = "5tE868529F";
	$user_key = "a93a64f60e8aa7a6d7476babe2986763";

	//format timestamp
	$tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
	$signature = hash_hmac('sha256', $consId."&".$tStamp, $secretKey, true);

	$encodedSignature = base64_encode($signature);
	
	/*echo "X-cons-id: " .$consId ." ";
	echo "X-timestamp:" .$tStamp ." ";
	echo "X-signature: " .$encodedSignature;*/
	
	$ch = curl_init();
    $headers = array(
        'x-cons-id: '.$consId .'',
        'x-timestamp: '.$tStamp.'' ,
        'x-signature: '.$encodedSignature.'',
		'user_key: '.$user_key.'',
        'Content-Type: Application/JSON',          
        'Accept: Application/JSON'
    );
	
	curl_setopt($ch, CURLOPT_URL, "https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/ref/poli");
	curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
   
    $data = json_decode($content,true);

	//jika ingin view hasil json aktifkan ini
	//echo "$content";
	
	$string= " ".$data['response']."";

	//keynya dari concatenate data bpjs
	$key="$consId$secretKey$tStamp";
	$ou=stringDecrypt($key,$string);
	
	$json  = "".$ou."";
	$array = json_decode($json, true);
	
	

	//json to array php brayy
	foreach ($array as $key => $value) {
		echo "" . $value["kdpoli"] . " - " . $value["nmpoli"] . " - ".$value["nmsubspesialis"] . " - ".$value["kdsubspesialis"] . " <br>";
	}
	
?>