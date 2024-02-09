<?php
header('Content-Type: application/json');
session_start();
date_default_timezone_set("Asia/Yerevan");

$pageName = "delivery";
$rootF = "../..";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
$access = auth::checkUserAccess($secureKey);
$server_key = "A454745T$854@65Y!34%";

function hashServerRequest($server_key,$data){
		return md5($server_key.md5($server_key.$data));
}
function GetOrderTableCount($check_table_count){
	if($check_table_count >= 45 && $check_table_count < 50){
		$table_count = '45_50';
	}
	if($check_table_count >= 50 && $check_table_count < 55){
		$table_count = '50_55';
	}
	if($check_table_count >= 55 && $check_table_count < 60){
		$table_count = '55_60';
	}
	if($check_table_count >= 60 && $check_table_count < 65){
		$table_count = '60_65';
	}
	if($check_table_count >= 65 && $check_table_count <= 70){
		$table_count = '65_70';
	}
	if($check_table_count >= 70 && $check_table_count <= 75){
		$table_count = '70_75';
	}
	if($check_table_count >= 75 && $check_table_count <= 80){
		$table_count = '75_80';
	}
	if($check_table_count >= 80 && $check_table_count <= 85){
		$table_count = '85_85';
	}
	return $table_count;
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
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$operator = "";
$orderId = null;
$created = "";
$orderData = null;
$cc = "am";
$actioned = false;
$titleHelp = "NEW";
$postId = "";
$root = true;
$postArray = array();
$userData = "";
if(!$access){
	header("location:../../login");
}else{
	if($access === true){
		$uid = $_COOKIE["suid"];
		$level = auth::getUserLevel($uid);
	  $operator = page::getOperator($uid);
		page::accessByLevel($level[0]["user_level"],$pageName);
		$userData = auth::checkUserExistById($uid);
		$cc = $userData[0]["lang"];
		
		$postArray = array("delivery_price","who_received","changed_status_by_driver_date","bonus_type","delivery_date","delivery_time","delivery_time_manual","delivery_region","receiver_name","product","price","currency","receiver_subregion","receiver_street","receiver_address","receiver_phone","greetings_card","delivery_type","ontime","delivery_status","order_source","order_source_optional","payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone","sender_email","notes","notes_for_florist","sell_point","keyword","receiver_mood","next_action","next_action_id");
		
		if(isset($_REQUEST["orderId"]))
		{
			$orderId = htmlentities($_REQUEST["orderId"]);
			if($orderData = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '{$orderId}'"))
			{
				$created = $orderData[0]["created_date"].",".$orderData[0]["operator"];
			}else{
				$orderId = null;
			}
			$titleHelp = "EDIT";
		}
	}else{
		$uid = "-1";
		$postArray = array("delivery_price","who_received","changed_status_by_driver_date","bonus_type","delivery_date","delivery_time","delivery_time_manual","delivery_region","receiver_name","product","price","currency","receiver_subregion","receiver_street","receiver_address","receiver_phone","greetings_card","delivery_type","ontime","delivery_status","order_source","order_source_optional","payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone","sender_email","notes","notes_for_florist","sell_point","keyword","receiver_mood","next_action","next_action_id");
		$operator = "Robot";
		$cc = "am";
	}
}
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
	} else{
        return $str;
	}
}
if($access == true){
	page::cmd();
}
if(is_file("lang/language_{$cc}.php"))
{
	include("lang/language_{$cc}.php");	
}else{
	include("lang/language_am.php");
}
$stream_opts = [
    "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
];
$telegram_id = array('araqich1'=>'-1001094348964');
$deliverers = array("araqich1" => "Հրաչ", "araqich2" => "Կարեն");
$bot_id = '366104506:AAGTt0Kp0igoxxMYi4x2MmcPnOaqZla1lw0';
function file_get_contents_curl($url) {
	$ch = curl_init();
	$proxy = '167.99.33.139:8080';
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_PROXY, $proxy);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36" );
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
if(isset($_REQUEST["update_order"]))
{
		$actionQuery = "";
		$_REQUEST["operator"] = $operator;
		foreach($_REQUEST as $key => $value)
		{
			//$value = htmlentities($value);
			/*$value = str_replace('"',"",$value);
			$value = str_replace("'","",$value);
			$value = str_replace("%22","",$value);
			$value = str_replace("&quot;","",$value);
			$value = str_replace("&amp;","",$value);
			$value = str_replace("quot;","",$value);*/
			if(in_array($key,$postArray))
			{
				$value = checkaddslashes($value);
				$actionQuery .= " {$key} = '{$value}', ";
			} 
		}
		
		$value = "<br>Driver Edit: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		// $actionQuery .= "log = CONCAT(log,'{$value}') ";
		$actionQuery = rtrim($actionQuery,", ");
		//print_r($postArray);
		//echo $actionQuery;
		if(isset($_REQUEST["id"]))
		{
			$id =  htmlentities($_REQUEST["id"]);

			$delivered_at = getwayConnect::getwayData("SELECT delivered_at from rg_orders where id='{$id}'",PDO::FETCH_ASSOC);
			if(!isset($delivered_at[0]['delivered_at']) && isset($_REQUEST['delivery_status']) && $_REQUEST['delivery_status'] == 3){
				$date = gmdate('Y-m-d H:i:s', time() + 4 * 3600);
				$actionQuery .= ", `delivered_at`='{$date}' ";
			}
			//exit($userData[0]["username"]);
			// Created By Hrach
			$orderOldInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$_REQUEST["id"]}'");
			$Newdelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'");
			$Olddelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$orderOldInfo[0]['delivery_status']}'");
			$operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$_REQUEST["operator"]}'");
			$html_log_for_update_order= "Delivery Status Is Changed from " . $Olddelivery_status[0]['name_am'] . " to " . $Newdelivery_status[0]['name_am'];
			$check_table_count = substr($_REQUEST['id'], 0, 2);
			$table_count;
			if($check_table_count >= 45 && $check_table_count < 50){
				$table_count = '45_50';
			}
			if($check_table_count >= 50 && $check_table_count < 55){
				$table_count = '50_55';
			}
			if($check_table_count >= 55 && $check_table_count < 60){
				$table_count = '55_60';
			}
			if($check_table_count >= 60 && $check_table_count < 65){
				$table_count = '60_65';
			}
			if($check_table_count >= 65 && $check_table_count <= 70){
				$table_count = '65_70';
			}
			if($check_table_count >= 70 && $check_table_count <= 75){
				$table_count = '70_75';
			}
			if($check_table_count >= 75 && $check_table_count <= 80){
				$table_count = '75_80';
			}
			if($check_table_count >= 80 && $check_table_count <= 85){
				$table_count = '80_85';
			}
			if( $Olddelivery_status[0]['name_am'] != $Newdelivery_status[0]['name_am'] ){
				getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$_REQUEST["id"]}','{$html_log_for_update_order}','{$operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."','{$orderOldInfo[0]['delivery_status']}')");
			}
			//
			if(isset($userData[0]["username"])){
				$telegram_message = true;
				$_REQUEST['who_received'] = (isset($_REQUEST['who_received'])) ? $_REQUEST['who_received'] : '';
				if(isset($_REQUEST['delivery_status'])){
					$dlv = $deliverers[$userData[0]["username"]] ? $deliverers[$userData[0]["username"]] : $userData[0]["username"];
					if($_REQUEST['delivery_status'] == 3 && $_REQUEST['who_received'] != 'undefined'){
						$telegram_message = urlencode($dlv.' <a href="https://new.regard-group.ru/account/flower_orders/order.php?orderId='.$_REQUEST['id'].'">N-'.$_REQUEST['id'].'</a> Առաքված է, ստացող '.$_REQUEST['who_received']);
					}else if($_REQUEST['delivery_status'] == 6){
						$telegram_message = urlencode($dlv.' <a href="https://new.regard-group.ru/account/flower_orders/order.php?orderId='.$_REQUEST['id'].'">N-'.$_REQUEST['id'].'</a> ճանապարհին');
					}
				}
				
				$telegram_link = 'http://api.telegram.org/bot'.$bot_id.'/sendMessage?chat_id=-1001114000413&text='.$telegram_message.'&parse_mode=html&disable_web_page_preview=true';
				
				if($telegram_message){
					// file_get_contents_curl($telegram_link);
					$resp =	file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id."&chat_id=-1001114000413"."&telegram_message=".$telegram_message,false, stream_context_create($stream_opts));
				}

				//die('fghfghfg');
			}
			
			
			/*echo "UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'";*/
			if(getwayConnect::getwaySend("UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'"))
			{
				exit(json_encode(array("status"=>"ok")));
			}else{
				exit(json_encode(array("status"=>"fail")));
			}
		}else{
			exit(json_encode(array("status"=>"fail","msg"=>"oid")));
		}
}elseif(isset($_REQUEST['right_address'])){
	getwayConnect::getwaySend("UPDATE rg_orders SET right_address='{$_REQUEST['right_address']}' WHERE id='{$_REQUEST['order_id']}'");
	exit(json_encode(array('success' => 'Right Address is set!')));
}elseif(isset($_REQUEST['right_address_false'])){
	getwayConnect::getwaySend("UPDATE rg_orders SET right_address='{$_REQUEST['value']}' WHERE id='{$_REQUEST['order_id']}'");
	exit(json_encode(array('success' => 'Right Address is set!')));
}elseif(isset($_REQUEST['add_options_to_log'])){
	$who_received = $_POST['who_received'];
	$drive_price = $_POST['drive_price'];
	$mood = $_POST['mood'];
	$next_action = $_POST['next_action'];
	$order_id = $_POST['order_id'];
	$orderInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$order_id}'");
	$html_for_log = '';
	if($orderInfo[0]['receiver_mood'] != $mood){
		if($orderInfo[0]['receiver_mood'] == 0){
			$html_for_log.= ' Receiver Mood: <span style="color:red">=></span> <img src="http://new.regard-group.ru/account/orders_delivery/ico/mood_' . $mood . '.png" style="max-width: 30px;max-height: 30px;"><br>';
		}
		else{
			$html_for_log.= ' Receiver Mood: ' . '<img style="max-width: 30px;max-height: 30px;" src="http://new.regard-group.ru/account/orders_delivery/ico/mood_' . $orderInfo[0]['receiver_mood'] . '.png"> <span style="color:red">=></span> <img src="http://new.regard-group.ru/account/orders_delivery/ico/mood_' . $mood . '.png" style="max-width: 30px;max-height: 30px;"><br>';
		}
	}
	if($orderInfo[0]['next_action'] != $next_action){
		if($orderInfo[0]['next_action'] == 0){
			$html_for_log.= ' Next Action: <span style="color:red">=></span> <img src="http://new.regard-group.ru/template/icons/' . $next_action . '.png" style="width:43px"><br>';
		}
		else{
			$html_for_log.= ' Next Action: ' . '<img style="max-width: 30px;max-height: 30px;" src="http://new.regard-group.ru/template/icons/' . $orderInfo[0]['next_action'] . '.png"> <span style="color:red">=></span> <img src="http://new.regard-group.ru/template/icons/' . $next_action . '.png" style="width:43px"><br>';
		}
	}
	$next_action_text = '';
	if($next_action == 1){
		$next_action_text = ' oֆֆիս';
	}
	elseif($next_action == 2){
		$next_action_text = ' ավարտ';
	}
	elseif($next_action == 3){
		$next_action_text = ' հաջորդ պատվեր';
	}

	$telegram_message = true;
	$telegram_message = urlencode($userData[0]['full_name_am'] . ' հաջորդ։ ' . $next_action_text);
	
	if($telegram_message){
		$resp =	file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id."&chat_id=-1001114000413"."&telegram_message=".$telegram_message,false, stream_context_create($stream_opts));
	}


	if($who_received){
		if($orderInfo[0]['who_received'] != $who_received){
			$html_for_log.= ' Who Received: <span style="color:blue">' . $orderInfo[0]['who_received'] . '</span><span style="color:blue">=></span><span style="color:#4F6228"><b>' . $who_received .'</b></span><br>' ;
		}
	}
	if($drive_price){
		if($orderInfo[0]['delivery_price'] != $drive_price){
			$orderOldPrice = getwayConnect::getwayData("SELECT * FROM `drive_prices` WHERE `id` = '{$orderInfo[0]['delivery_price']}'");
			$orderNewPrice = getwayConnect::getwayData("SELECT * FROM `drive_prices` WHERE `id` = '{$drive_price}'");
			if(!$orderOldPrice){
				$html_for_log.= ' Delivery Price: <span style="color:blue">=></span><span style="color:#4F6228"><b>' . $orderNewPrice[0]['name'] .'</b></span><br>' ;
			}
			else{
				$html_for_log.= ' Delivery Price: <span style="color:blue">' . $orderOldPrice[0]['name'] . '</span><span style="color:blue">=></span><span style="color:#4F6228"><b>' . $orderNewPrice[0]['name'] . '</b></span><br>' ;
			}
		}
	}
	$table_count = GetOrderTableCount(substr($order_id, 0, 2));
	if($html_for_log != ''){
		$dd = getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$order_id}','{$html_for_log}','{$userData[0]['id']}','" . date("Y-m-d H:i:s") ."',{$orderInfo[0]['delivery_status']})");
	}
	return true;

}elseif(isset($_REQUEST["update_total_earns"])){
	$op_price = ucfirst(strtolower($userData[0]["username"]));
	$today_is = date('Y-m-d');
	$count_price = getwayConnect::getwayData("SELECT SUM( dp.name ) as total_earn 
FROM  `rg_orders` AS ro,  `drive_prices` AS dp,  `delivery_drivers` AS dd, delivery_deliverer as del
WHERE ro.delivery_price = dp.id
AND del.name = '{$userData[0]["username"]}'
AND ro.deliverer =  del.id
AND ro.delivery_type = dd.id AND MONTH(ro.delivery_date) = MONTH('{$today_is}') AND YEAR(ro.delivery_date) = YEAR('{$today_is}') ");
	$count_price = ($count_price[0]['total_earn']) ? $count_price[0]['total_earn'] : '0.00';
	exit(json_encode(array("status"=>"ok","msg"=>$count_price)));
}else{
	exit(json_encode(array("status"=>"fail","msg"=>"update")));
}
?>