<?php
session_start();
$pageName = "flower";
$rootF = "../..";
$root = true;
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
header('Content-type: application/json');
page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";

if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
	page::accessByLevel($level[0]["user_level"],$pageName);
	$levelArray = explode(",",$level[0]["user_level"]);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
	if(is_file("lang/language_{$cc}.php"))
	{
		include("lang/language_{$cc}.php");	
	}else{
		include("lang/language_am.php");
	}
}
$regionData = page::getRegionFromCC($cc);
date_default_timezone_set ("Asia/Yerevan");
include("functions.orders.php");
if(isset($_REQUEST["pid"])){
	$pid = htmlentities($_REQUEST["pid"]);
	if(is_numeric($pid))
	{
		$data = checkStatus($pid);
		echo json_encode($data);
	}
}elseif(isset($_REQUEST["editors"])){
	echo json_encode(showEditors());
}
?>