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
$orderIddd = 0;
$orderIdPlus = $orderIddd + 500;
$orders = getwayConnect::getwayData("SELECT * FROM `rg_orders` where id >= " . $orderIddd ." and id <= " . $orderIdPlus);
$greetings_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
$notes_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes'");
$notes_for_florist_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes_for_florist'");
// print "<pre>";
$sql = '';

foreach($orders as $key=>$value){
	$order_greeting_card = $value['greetings_card'];
	$order_notes = $value['notes'];
	$order_notes_for_florist = $value['notes_for_florist'];
	$order_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE order_id = '{$value["id"]}'");
	if(count($order_row) == 0){
		$sql.= ' INSERT into `order_notes` (order_id,type_id, value) VALUE ("' . $value["id"] . '" ,"' . $greetings_card_type[0]["id"] . '","' . str_replace('"',"'",$order_greeting_card) . '"); ';
		$sql.= ' INSERT into `order_notes` (order_id,type_id, value) VALUE ("' . $value["id"] . '" ,"'.$notes_type[0]["id"].'", "'.str_replace('"',"'",$order_notes).'");';
		$sql.= ' INSERT into `order_notes` (order_id,type_id, value) VALUE ("'.$value["id"].'","'.$notes_for_florist_type[0]["id"].'", "'.str_replace('"',"'",$order_notes_for_florist).'");';
		$sql.= " UPDATE rg_orders SET notes='" . mb_strlen($order_notes) . "',notes_for_florist='" . mb_strlen($order_notes_for_florist) . "',greetings_card='" . mb_strlen($order_greeting_card) . "' WHERE id='{$value["id"]}'; ";
	}
}
print $sql;
// var_dump($orders);die;