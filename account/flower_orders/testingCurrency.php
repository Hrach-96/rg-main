<?php
	define("URL", "http://new.regard-group.ru/currency.php"); 
	$access_token_parameters = array(
    );
	$curl = curl_init(URL);
    curl_setopt($curl,CURLOPT_POST,true);
    curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result);
    print "<pre>";
    var_dump($result);die;
?>