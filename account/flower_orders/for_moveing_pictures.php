<?php
session_start();
$pageName = "flower";
$rootF = "../..";

//by Haykaz
include_once $_SERVER['DOCUMENT_ROOT']."/controls/FlowersForms.php";
////H

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$user_country = '0';
if (!$access) {
    header("location:../../login");
} else {
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"], $pageName);
    $levelArray = explode(",", $level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
    $user_country = $userData[0]["country_short"];
    if (is_file("lang/language_{$cc}.php")) {
        include("lang/language_{$cc}.php");
    } else {
        include("lang/language_am.php");
    }
}
$orders = getwayConnect::getwayData("SELECT * FROM delivery_images left join rg_orders on delivery_images.rg_order_id = rg_orders.id limit 0,1000 ");
print "<pre>";
foreach( $orders as $key => $value ){
	if(isset($value['id'])){
		$created_date = explode('-',$value['created_date']);
		$path =  $created_date[1] . '-' . substr($created_date[0], 2, 2) ;
		if (!is_dir('product_images/' . $path)) {
		    // mkdir('product_images/' . $path, 0777, true);
		}
		if (file_exists('product_images/' . $value['image_source'])) {
			// var_dump(rename('product_images/' . $value['image_source'], 'product_images/' . $path . '/' . $value['image_source']));
		}
	}	
}
die;
?>