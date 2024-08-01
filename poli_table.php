<?php 
 
		
		require_once "function_decrypt.php";
		
		//consid, secretkey, userkey  didapatkan dari BPJS bray
		$consId = "20161";
		$secretKey = "5tE868529F";
		$user_key = "a93a64f60e8aa7a6d7476babe2986763";

		//format timestamp
		$tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
		$signature = hash_hmac('sha256', $consId."&".$tStamp, $secretKey, true);

		$encodedSignature = base64_encode($signature);
		
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
		
		$string= " ".$data['response']."";

		//keynya dari concatenate data bpjs
		$key="$consId$secretKey$tStamp";
		$ou=stringDecrypt($key,$string);
		
		$json  = decompress("".$ou."");
		$array = json_decode($json, true);
		
		echo "<b>Keynya adalah :</b>";
		echo "</br>";
		echo "</br>";
		
		echo "$key";
		echo "</br>";
		echo "</br>";
		
		echo "<b>Status Data :</b>";
		
		
		echo "</br>";
		echo "</br>";
		
		echo "".$data['metadata']['message']."";
		echo "</br>";
		echo "</br>";
		
		//json to array php brayy
		echo "<b>Ini adalah data yang di compress dan encrypt :</b>";
		echo "</br>";
		echo "</br>";
		
		echo "".$content."";
		
		
		echo "</br>";
		echo "</br>";
		
		echo "<b>Ini adalah data json yang di decrypt :</b>";
		echo "</br>";
		echo "</br>";
		
		echo "$json";
		echo "</br>";
		echo "</br>";
		
		echo "<b>Ini adalah data json to php :</b>";	
		echo "</br>";
		echo "</br>";
		$no=0;
		echo "<table class=table table-sm table-bordered>
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							Kode Poli
						</th>
						<th>
							Nama Poli
						</th>
						<th>
							Kode Sub Spesialis
						</th>
						<th>
							Nama Sub Spesialis
						</th>
					</tr>
				</thead>
				<tbody>";
				foreach ($array as $key => $value) {
				$no++;
				echo "<tr>";
						echo "<td>$no</td>";
						echo "<td>$value[kdpoli]</td>";
						echo "<td>$value[nmpoli]</td>";
						echo "<td>$value[kdsubspesialis]</td>";
						echo "<td>$value[nmsubspesialis]</td>";
				echo"</tr>";
		}
		echo"</tbody>";
		echo"</table>";
?>
 