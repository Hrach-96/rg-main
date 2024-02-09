<?php
session_start();
include("../apay/pay.api.php");
include("../configuration.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
if(!$access){
	header("location:../login");
} else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
        page::accessByLevel($level[0]["user_level"],"all");
	//auth::destroySession();
}
?>