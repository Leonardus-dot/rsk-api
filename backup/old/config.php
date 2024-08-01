<?php

	$servername = "localhost";
	$username = "root";
	$password = "";
	$database = "sekarapp";
	$con = new mysqli($servername, $username, $password ,$database);
	if ($con->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	function validation_errors($error) {
		$errors = $error;
		return $errors;
	}
	
	if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $return = array();
        foreach($_SERVER as $key=>$value) {
            if (substr($key,0,5)=="HTTP_") {
                $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                $return[$key]=$value;
            }else{
                $return[$key]=$value;
	        }
        }
        return $return;
    }
	}

?>