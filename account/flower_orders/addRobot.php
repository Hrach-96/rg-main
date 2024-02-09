<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
date_default_timezone_set("Asia/Yerevan");

$pageName = "flower";
$rootF = "../..";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
$access = auth::checkUserAccess($secureKey);
$server_key = "A454745T$854@65Y!34%";

function hashServerRequest($server_key,$data){
		return md5($server_key.md5($server_key.$data));
}
if(isset($_REQUEST["server_insert"])){
	$srv_data = $_REQUEST;
	unset($srv_data["hash"]);
	$hash_from_server = $_REQUEST["hash"];
	$hash_here = hashServerRequest($server_key,implode(",",$srv_data));
	if($hash_from_server == $hash_here){
		$access = "robot";
	}
}elseif(!$access){
   header("location:../../login");
}
$postArray = array("important","bonus_type","delivery_date",
				   "delivery_time","delivery_time_manual","delivery_region",
				   "receiver_name","product","price","currency","receiver_subregion",
				   "receiver_street","receiver_address","receiver_phone","greetings_card",
				   "delivery_type","ontime","delivery_status","order_source","order_source_optional",
				   "payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone",
				   "sender_email","notes","notes_for_florist","sell_point","keyword","travel_time_end","delivery_reason","delivery_reason","delivery_language_primary","delivery_language_secondary","deliverer","srsinfo");
$actionQuery = "";
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
	} else{
        return $str;
	}
}
$gretings_card = '';
$notes = '';
$srsinfo = '';
$insertSrsinfo = false;
foreach($_REQUEST as $key => $value)
{
	if(in_array($key,$postArray))
	{
		if($key == "sell_point" && ($value == "rtp" || $value == "flp" || $value == "ows")){
			$value = checkaddslashes($_REQUEST["sell_point_partner"]);
		}
		$value = checkaddslashes($value);
		if($key == 'greetings_card'){
			if(mb_strlen($value) > 0){
				$gretings_card = $value;
			}
			$actionQuery .= "{$key} = '" . mb_strlen($value) . "', ";
		}
		else if($key == 'notes'){
			if(mb_strlen($value) > 0){
				$notes = $value;
			}
			$actionQuery .= "{$key} = '" . mb_strlen($value) . "', ";
		}
		else if($key == 'srsinfo'){
			if(mb_strlen($value) > 0){
				$insertSrsinfo = true;
				$srsinfo = $value;
			}
		}
		else{
			$actionQuery .= "{$key} = '{$value}', ";
		}
	} 
}
$cDate = date("Y-m-d");
$cTime = date("H:i:s");
$newTime = strtotime('-30 seconds');
if(!empty($_REQUEST)){
    $last_minute = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE created_date = '" . date('Y-m-d', $newTime) . "' and created_time > '" . date('H:i:s', $newTime) . "' and sender_email = '" . $_REQUEST['sender_email'] . "'");
    if(count($last_minute) == 0){

		$actionQuery .= "operator='{$_REQUEST["operator"]}' , created_date = '{$cDate}', created_time = '{$cTime}'";
		$actionQuery = rtrim($actionQuery,", ");
		$postId = getwayConnect::getwaySend("INSERT INTO rg_orders SET {$actionQuery}",true);
		getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$postId}','1', '" . checkaddslashes($gretings_card) . "')");
		getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$postId}','2', '" . checkaddslashes($notes) . "')");
		if($insertSrsinfo){
			getwayConnect::getwayData("INSERT into `rg_orders_srs_info` (value,order_id, created_date) VALUE ('" . checkaddslashes($srsinfo) . "','{$postId}','" . date("Y-m-d H:i:s") . "')");
		}
		print true;die;
    }
}	
		print true;die;