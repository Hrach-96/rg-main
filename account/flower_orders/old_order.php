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
	/*if(isset($_REQUEST['price'])){
		preg_match('/^.*?([\d]+(?:\.[\d]+)?).*?$/', $_REQUEST['price'], $p_matches);
		if(isset($p_matches[1])){
			$_REQUEST['price'] = $p_matches[1];
		}
	}*/


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
$bot_id = '352258205:AAEdtQCbXYJRx7fW8ddcMHi9hIKxFW73dSc';
$telegram_message = false;
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
$lastOperator ='';
$delivery_drivers = '';
$travel_operators = array(1, 2, 78);

$regions = getwayConnect::getwayData("SELECT * from countries where active = 1 ORDER BY `ordering` ASC, name_am");

if(isset($_REQUEST['get_street_info']) && $_REQUEST['get_street_info'] != ''){
	$street = getwayConnect::getwayData("SELECT * from delivery_street where code = '{$_REQUEST['get_street_info']}'");
	$zone = null;
	if(isset($street[0]['zone']) && $street[0]['zone'] != ''){
		$zone = getwayConnect::getwayData("SELECT * from delivery_zone where id='{$street[0]['zone']}'");
	}
	if(isset($street) && !empty($street)){
		echo json_encode(array('street' => $street[0], 'zone' => $zone[0]));
	}
	exit;
}
// Added by Hrach
if(isset($_REQUEST['get_stock_prods']) && $_REQUEST['get_stock_prods'] != ''){
	$street = getwayConnect::getwayData("SELECT * from jos_vm_product_stock_href LEFT JOIN orders_products_data ON jos_vm_product_stock_href.stock_product_id = orders_products_data.id where product_id = '{$_REQUEST['get_stock_prods']}'");
	print json_encode($street);
	exit;
}
//

function addEditImage($images_data,$order_id, $productdesc, $productprice, $producttaxid, $productquantity, $productamdprice){
	//image_id
	//exit(print_r($_REQUEST,true));
	if($images_data && $order_id){
		if(count($images_data) > 0){
			foreach($images_data as $imkey => $im){
				$note_key = str_replace(".", "_", $imkey);
				$note = isset($_REQUEST[$note_key]) ? $_REQUEST[$note_key] : '';
				$desc = isset($productdesc[$imkey])? $productdesc[$imkey] : '';
				// Added By Dev for xml asop52f41v78x8z5
				$taxid = isset($producttaxid[$imkey])? $producttaxid[$imkey] : '';
				$quantity = isset($productquantity[$imkey])? $productquantity[$imkey] : '';
				$amdPrice = isset($productamdprice[$imkey])? $productamdprice[$imkey] : '';
				// end asop52f41v78x8z5
				$price = isset($productprice[$imkey])? $productprice[$imkey] : 0;
				/**/if($get_im = getwayConnect::getwayData("SELECT * FROM `delivery_images` WHERE `image_source` = '{$im}' AND `rg_order_id` = '{$order_id}'",PDO::FETCH_ASSOC)){
						//print_r($get_im);
						// if($note != $get_im[0]['image_note']){
							//exit('6a5s4d6a5s4');
							getwayConnect::getwaySend("UPDATE `delivery_images` SET `image_note` = '" . checkaddslashes($note) . "',`tax_account_id` = '{$taxid}',`tax_quantity` = '{$quantity}' , `tax_price_amd` = '{$amdPrice}', `product_desc` = '" . checkaddslashes($desc) . "', `price` = '{$price}' WHERE `rg_order_id` = '{$order_id}' AND `image_source` = '{$im}'");
						// }
				}else{
					getwayConnect::getwaySend("INSERT INTO `delivery_images` SET `image_source` = '{$im}',`tax_account_id` = '{$taxid}',`tax_quantity` = '{$quantity}' , `tax_price_amd` = '{$amdPrice}' , `image_note` = '" . checkaddslashes($note) ."', `rg_order_id` = '{$order_id}', `product_desc` = '" . checkaddslashes($desc) ."', `price` = '{$price}'");
				}
			}
		}
	}
	$check_im = getwayConnect::getwayData("SELECT * FROM `delivery_images` WHERE `rg_order_id` = '{$order_id}'",PDO::FETCH_ASSOC);
	if($check_im){
		getwayConnect::getwaySend("UPDATE `rg_orders` SET `image_exist` = '1' WHERE `id` = '{$order_id}'");
	}else{
		getwayConnect::getwaySend("UPDATE `rg_orders` SET `image_exist` = '0' WHERE `id` = '{$order_id}'");
	}
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
	return $table_count;
}
function sendTelegramMessageToFlorist(){
	$telegram_message = urlencode('Փոփոխություն է տեղի ունեցել <a href="http://new.regard-group.ru/account/orders_delivery/">N- ' . $_REQUEST['orderId'] . ' </a> պատվերի վերաբերյալ');
	// for Florists Telegram message
	$bot_id_florist = '1301694216:AAEoQysaIHcWUXmj1JzcaQxWb6H0mbWHZDI';
	$chat_id_florist = '-1001233712256';
	// for test
	// $bot_id_florist = '1132061644:AAE7ZtxgxjGWE24DPxTDpYqSGufK8wUYNrU';
	// $chat_id_florist = '-1001315951791';
	$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id_florist."&chat_id=" . $chat_id_florist . "&telegram_message=".$telegram_message);
}
function GetDriverInfoForTelegram($deliverer){
	$result = [];
	// Hovik
	if($deliverer == 1){
		$result['driver_name'] = 'Հովիկ';
		$result['bot_id_driver'] = "1024861344:AAFt4TlAsj4sHMDHFIK8Sf6G5gLq_NEIT6U";
		$result['chat_id_driver'] = "-1001150171268";
		$result['max_clock'] = "23:00:00";
		$result['min_clock'] = "08:00:00";
	}
	// Hrach Driver
	else if($deliverer == 8){
		$result['driver_name'] = 'Հրաչ';
		$result['bot_id_driver'] = "1049865235:AAGnoe5a0ZgyH1bEEVcPeX_1AYF2X_zh-68";
		$result['chat_id_driver'] = "-1001232712138";
		$result['max_clock'] = "23:00:00";
		$result['min_clock'] = "08:00:00";
	}
	// Goqor Driver
	else if($deliverer == 9){
		$result['driver_name'] = 'Կարեն';
		$result['bot_id_driver'] = "1290180420:AAF9-9J9NzF-QutfwfGJjV0oMdsrxKPB1MU";
		$result['chat_id_driver'] = "-1001280842610";
		$result['max_clock'] = "23:59:00";
		$result['min_clock'] = "08:00:00";
	}
	// Norik Driver
	else if($deliverer == 2){
		$result['driver_name'] = 'Գագիկ';
		$result['bot_id_driver'] = "748291883:AAEK4lYJQmZvCH12FlAyDA_Es-RorjhVHdQ";
		$result['chat_id_driver'] = "-1001350603987";
		$result['max_clock'] = "23:00:00";
		$result['min_clock'] = "08:00:00";
	}
	return $result;
}
function changeImageStatus($order_id){
	getwayConnect::getwaySend("UPDATE `rg_orders` SET `image_exist` = '0' WHERE `id` = '{$order_id}'");
}
function addDeliverer($driver,$order_id){
	/*if($order_id){
		if($data = getwayConnect::getwayData("SELECT * FROM `order_delivery_deliverer` WHERE  `rg_order_id` = '{$order_id}'",PDO::FETCH_ASSOC)){
			getwayConnect::getwaySend("UPDATE `order_delivery_deliverer` SET
							  `deliverer_id`= '{$driver}' WHERE  `rg_order_id` = '{$order_id}'");
		}else{
			getwayConnect::getwaySend("INSERT INTO `order_delivery_deliverer` SET
							  `deliverer_id`= '{$driver}',
							  `rg_order_id` = '{$order_id}'");
		}
	}*/
}
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
		
		$postArray = array("important","bonus_type","delivery_date","delivery_time",
				   "delivery_time_manual","delivery_region","receiver_name",
				   "product","price","currency","receiver_subregion","receiver_street",
				   "receiver_address","receiver_entrance","receiver_floor","receiver_tribute","receiver_door_code","receiver_phone","delivery_type",
				   "ontime","delivery_status","order_source","order_source_optional","payment_type","first_connect","second_connect",
				   "payment_optional","sender_name","sender_country","sender_region","sender_address","sender_phone",
				   "sender_email","sell_point","keyword","travel_time_end","delivery_reason","delivery_language_primary","delivery_language_secondary","deliverer", "flourist_id", "operator_name", "confirmed_by", "confirmed", "bonus_info", "organisation","total_price_amd");
		
		if(isset($_REQUEST["orderId"]))
		{
			$orderId = htmlentities($_REQUEST["orderId"]);
			if($orderData = getwayConnect::getwayData("SELECT rg_orders.*, user.username as confirmed_by_user FROM rg_orders LEFT JOIN user on rg_orders.confirmed_by = user.id WHERE rg_orders.id = '{$orderId}'"))
			{
				$delivery_deliverer = getwayConnect::getwayData("SELECT * FROM `order_delivery_deliverer` WHERE `rg_order_id` = '{$orderId}'",PDO::FETCH_ASSOC);
				$delivery_deliverer = isset($delivery_deliverer[0]) ? $delivery_deliverer[0] : '';
				// Added By Hrach
				$operatorInfo = getwayConnect::getwayData("SELECT * FROM user where username = '" . $orderData[0]["operator"] . "'");
				$orgDate = $orderData[0]["created_date"];  
    			$newDate = date("d-m-Y", strtotime($orgDate));  
				if($operatorInfo){
					$created = $operatorInfo[0]['full_name_am'] . "ի կողմից " . $newDate . ', ' .$orderData[0]["created_time"];
				}
				$lastOperator = $orderData[0]["operator"];
			}else{
				$orderId = null;
			}
			$titleHelp = "EDIT";
		}
	}else{
		$uid = "-1";
		$postArray = array("important","bonus_type","delivery_date",
				   "delivery_time","delivery_time_manual","delivery_region",
				   "receiver_name","product","price","currency","receiver_subregion",
				   "receiver_street","receiver_address","receiver_entrance","receiver_floor","receiver_tribute","receiver_door_code","receiver_phone",
				   "delivery_type","ontime","delivery_status","order_source","order_source_optional",
				   "payment_type","first_connect","second_connect","payment_optional","sender_name","sender_country","sender_region","sender_address","sender_phone",
				   "sender_email","sell_point","keyword","travel_time_end","delivery_reason","delivery_reason","delivery_language_primary","delivery_language_secondary","deliverer", "flourist_id", "operator_name", "confirmed_by", "confirmed", "bonus_info", "organisation","total_price_amd");
		$operator = "Robot";
		$cc = "am";
	}
}
$organisation = null;
if(isset($orderData[0]) && isset($orderData[0]['organisation'])){
	$organisation = getwayConnect::getwayData("SELECT * from organisations where id = '{$orderData[0]['organisation']}'");
	if(isset($organisation) && !empty($organisation)){
		$organisation = $organisation[0];
	}
}
$arrayFlorstDeliveryCases = [1,7,11];
$query_parent_categories = "SELECT `category_id`, `category_name` from `jos_vm_category` where category_id In (SELECT category_parent_id FROM jos_vm_category_xref where 1 GROUP BY category_parent_id)";
$parent_categories = getwayConnect::getwayData($query_parent_categories);
$other_cat['category_id'] = 0;
$other_cat['category_name'] = "Other";
array_push($parent_categories, $other_cat);

$orderRelatedProducts = [];
if(isset($_REQUEST['orderId'])){
	$orders_related =  getwayConnect::getwayData("SELECT * FROM order_related_products where order_id='{$_REQUEST['orderId']}'");
	if(isset($orders_related) && !empty($orders_related)){
		$orders_related = explode(",", $orders_related[0]['jos_vm_product_id']);
		foreach($orders_related as $order_related){
			$orderRelatedProducts[] = getwayConnect::getwayData("SELECT jos_vm_product.*, jos_vm_product_price.product_price, jos_vm_product_price.product_currency, order_related_product_description.description as short_desc, order_related_product_description.name as related_name, order_related_product_description.price as related_prod_price
				from jos_vm_product RIGHT JOIN jos_vm_product_price on jos_vm_product.product_id=jos_vm_product_price.product_id LEFT JOIN order_related_product_description
				on order_id='{$_REQUEST['orderId']}' AND related_id='{$order_related}'  WHERE jos_vm_product.product_id='{$order_related}'");
		}
	}
}
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
	} else{
        return $str;
	}
}
// Added By Dev for xml asop52f41v78x8z5
function getTaxInfo($order_id,$product_id){
	$taxInfo = getwayConnect::getwayData("SELECT * FROM order_tax_info where rg_order_id='{$order_id}' and product_id ='{$product_id}'");
    return $taxInfo;
}
//  end asop52f41v78x8z5
$flourists = getwayConnect::getwayData("SELECT * FROM user where (id = 27 OR user_level=30) AND user_active=1 order by user_try DESC",PDO::FETCH_ASSOC);

$operators = getwayConnect::getwayData("SELECT * FROM user where user_level BETWEEN 36 AND 39 AND user_active=1",PDO::FETCH_ASSOC);

$exchange_rate = getwayConnect::getwayData("SELECT * FROM data_exchange_rate order by ID desc limit 1")[0];
if($access == true){
	page::cmd();
}
if(is_file("./lang/language_{$cc}.php"))
	{
		include("./lang/language_{$cc}.php");	
	}else{
		include("./lang/language_am.php");
	}

if(isset($_REQUEST["insert_order"]))
	{
		//print_r($_REQUEST);
		$actionQuery = "";
		if(isset($_REQUEST['relatedProduct'])){
			$relatedProducts = $_REQUEST['relatedProduct'];
			unset($_REQUEST['relatedProduct']);
		}
		if(isset($_REQUEST['short_desc'])){
			$related_short_descs = $_REQUEST['short_desc'];
			$related_names = $_REQUEST['related_name'];
			$related_prod_price = $_REQUEST['productNewPrice'];
			// Added By Dev for xml asop52f41v78x8z5
			$related_prod_amd_price = $_REQUEST['productAmdPrice'];
			$related_prod_id_con = $_REQUEST['productIdCon'];
			$related_prod_quantity = $_REQUEST['productQuantity'];
			$related_prod_tax_account = $_REQUEST['productTaxAccount'];
			// end asop52f41v78x8z5
			unset($_REQUEST['short_desc']);
			unset($_REQUEST['related_name']);
			unset($_REQUEST['productNewPrice']);
			unset($_REQUEST['productAmdPrice']);
			unset($_REQUEST['productIdCon']);
			unset($_REQUEST['productQuantity']);
			unset($_REQUEST['productTaxAccount']);
		}
		if(!isset($_REQUEST['flourist_id']) || $_REQUEST['flourist_id'] == ''){
			$_REQUEST['flourist_id'] = 0;
		}
		if(!isset($_REQUEST['sell_point']) || $_REQUEST['sell_point'] == ''){
			$_REQUEST['sell_point'] = 0;
		}
		foreach($_REQUEST as $key => $value)
		{
			if(in_array($key,$postArray))
			{
				if($key == "sell_point" && ($value == "rtp" || $value == "flp" || $value == "ows")){
					$value = checkaddslashes($_REQUEST["sell_point_partner"]);
				}
				$value = checkaddslashes($value);
				if($key == 'bonus_info'){
					$value = trim($value);
				}
				$actionQuery .= "{$key} = '{$value}', ";
			} 
		}
		$value = "<br>Added: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		$cDate = date("Y-m-d");
		$cTime = date("H:i:s");
		//die($cDate);
		$actionQuery .= " operator='{$_REQUEST["operator"]}' , created_date = '{$cDate}', created_time = '{$cTime}'";
		$actionQuery = rtrim($actionQuery,", ");
		// if($_REQUEST['delivery_status'] == 3){
		// 	$date = gmdate('Y-m-d H:i:s', time() + 4 * 3600);
		// 	$actionQuery .= ", `delivered_at`='{$date}' ";
		// }
		$postId = getwayConnect::getwaySend("INSERT INTO rg_orders SET {$actionQuery}",true);
		getwayConnect::getwayData("UPDATE rg_orders SET notes='" . mb_strlen($_REQUEST["notes"]) . "',notes_for_florist='" . mb_strlen($_REQUEST["notes_for_florist"]) . "',greetings_card='" . mb_strlen($_REQUEST["greetings_card"]) . "' WHERE id='{$postId}'");
		$anonym = 0;
		if(isset($_REQUEST["anonym"])){
			$anonym = 1;
		}
		getwayConnect::getwayData("UPDATE rg_orders SET anonym='" . $anonym . "' WHERE id='{$postId}'");
		$greetings_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
		$notes_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes'");
		$notes_for_florist_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes_for_florist'");
		getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$postId}','{$greetings_card_type[0]['id']}', '{$_REQUEST["greetings_card"]}')");
		getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$postId}','{$notes_type[0]['id']}', '{$_REQUEST["notes"]}')");
		getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$postId}','{$notes_for_florist_type[0]['id']}', '{$_REQUEST["notes_for_florist"]}')");
		// Added By Dev for xml asop52f41v78x8z5
		getwayConnect::getwaySend("INSERT INTO tax_numbers_of_check (hdm_tax,hvhh_tax, order_id,postcard_amd_price,delivery_static_price,delivery_other_price) VALUES ('{$_REQUEST['hdm_tax']}','{$_REQUEST['hvhh_tax']}' ,'{$postId}','{$_REQUEST['postcard_amd_price']}' ,'{$_REQUEST['delivery_static_price']}' ,'{$_REQUEST['delivery_other_price']}' )");
			// end asop52f41v78x8z5
		if($access == true){
			if($postId)
			{
				$_REQUEST["deliverer"] = (isset($_REQUEST["deliverer"]) && is_numeric($_REQUEST["deliverer"])) ?  $_REQUEST["deliverer"] : 0;
				if(isset($_REQUEST['image_id'])){
					addEditImage($_REQUEST['image_id'],$postId, $_REQUEST['productdesc'], $_REQUEST['productprice'],$_REQUEST['producttaxid'],$_REQUEST['productquantity'],$_REQUEST['productamdprice']);
				}
				if(isset($relatedProducts) && !empty($relatedProducts)){
					$relatedProductString = implode(",", $relatedProducts);
					getwayConnect::getwaySend("INSERT into order_related_products (`order_id`, `jos_vm_product_id`) VALUES ('{$postId}', '{$relatedProductString}')");
					foreach($related_short_descs as $key => $related_short_desc){
						getwayConnect::getwayData("INSERT into `order_related_product_description` (order_id, related_id, `description`, `name`, `price`) VALUE ('{$postId}', '{$key}', '{$related_short_desc}', '{$related_names[$key]}', '{$related_prod_price[$key]}')");
						// Added By Dev for xml asop52f41v78x8z5
						getwayConnect::getwayData("INSERT into `order_tax_info` (rg_order_id,price_amd, quantity, `tax_account_id`,`product_id`) VALUE ('{$postId}','{$related_prod_amd_price[$key]}', '{$related_prod_quantity[$key]}', '{$related_prod_tax_account[$key]}', '{$related_prod_id_con[$key]}')");
						// end asop52f41v78x8z5
					}
				}
				if($_REQUEST["deliverer"] > 0){
					
					$dlv = getwayConnect::getwayData("SELECT name FROM `delivery_deliverer` WHERE `id` = '{$_REQUEST["deliverer"]}'",PDO::FETCH_ASSOC);
					$dlv = (isset($dlv[0]["name"])) ? $dlv[0]["name"] : null;
					
					
					if(in_array($_REQUEST["delivery_status"], array(1, 4, 5, 6, 7, 11, 12, 13))){
						
						$dlv = getwayConnect::getwayData("SELECT name FROM `delivery_deliverer` WHERE `id` = '{$_REQUEST["deliverer"]}'",PDO::FETCH_ASSOC);
					    $dlv = (isset($dlv[0]["name"])) ? $dlv[0]["name"] : null;
						$dlv = defined($dlv) ? @constant($dlv) : $dlv;
						
						$sts = getwayConnect::getwayData("SELECT name FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'",PDO::FETCH_ASSOC);
					    $sts = (isset($sts[0]["name"])) ? $sts[0]["name"] : null;
						$sts = defined($sts) ? @constant($sts) : $sts;
						
						$telegram_message = urlencode("Նոր Պատվեր՝ {$dlv}-ի համար` «{$sts}» / <a href='https://new.regard-group.ru/account/orders_delivery/'N-{$postId}</a>");
					}	
					
					addDeliverer($_REQUEST["deliverer"],$postId);
					
				}
				
				$actioned = 1;
				//echo "<script>window.location.replace(\"../\");</script>";
			}else{
				$actioned = 4;
			}
		}
		if(!empty($_REQUEST['complain_type']) || !empty($_REQUEST['complain_reason'])){
			getwayConnect::getwaySend("INSERT INTO complain_of_orders ( type_id,reason,order_id) VALUES ('" . $_REQUEST['complain_type'] . "', '" . $_REQUEST['complain_reason'] . "','{$postId}')");
			getwayConnect::getwaySend("UPDATE rg_orders SET complain='1' WHERE id='{$postId}'");
		}
		$drivers_array = array(1,8,9,2);
		if($_REQUEST['delivery_status'] == 1 && $_REQUEST['delivery_date'] == date('Y-m-d') && in_array($_REQUEST['deliverer'], $drivers_array)){
			$driverInfo = GetDriverInfoForTelegram($_REQUEST['deliverer']);
			$driver_name = $driverInfo['driver_name'];
			$bot_id_driver = $driverInfo['bot_id_driver'];
			$chat_id_driver = $driverInfo['chat_id_driver'];
			$max_clock = $driverInfo['max_clock'];
			$min_clock = $driverInfo['min_clock'];
			$telegram_message = urlencode('Հարգելի ' . $driver_name . ' <a href="http://new.regard-group.ru/account/orders_delivery/"> N- ' . $postId . ' </a> պատվերը կցվեց Ձեզ` առաքման նպատակով:');
			$current_time = date("H:i:s");
		    if($current_time < $max_clock && $current_time > $min_clock){
				$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id_driver."&chat_id=" . $chat_id_driver . "&telegram_message=".$telegram_message);
		    }
		}
		 if($access === "robot"){
			$telegram_message = urlencode("Նոր Պատվեր՝ Robot-ից / <a href='https://new.regard-group.ru/account/orders_delivery/'N-{$postId}</a>");
			$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id."&chat_id=-1001108550129"."&telegram_message=".$telegram_message);
		 	exit("ok");	
		}
		if($_REQUEST['operator'] == "robot"){
			$telegram_message = urlencode("Nor Patver");
			// $telegram_message = urlencode("Nor Patver` Robot-ic / <a href='https://new.regard-group.ru/account/orders_delivery/'N-1231</a>");
			$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id."&chat_id=-1001108550129"."&telegram_message=".$telegram_message);
		}
	}
	else if(isset($_REQUEST["update_order"]))
	{
		if(isset($_REQUEST['relatedProduct'])){
			$relatedProductString = implode(',', $_REQUEST['relatedProduct']);
			unset($_REQUEST['relatedProduct']);
		} else {
			getwayConnect::getwaySend("DELETE FROM order_related_products where order_id='{$_REQUEST['orderId']}'");
		}
		$getRelatedProductString = getwayConnect::getwayData("SELECT * from `order_related_products` where order_id = '{$_REQUEST['orderId']}'");
		if(isset($_REQUEST['related_name'])){
			$related_name = $_REQUEST['related_name'];
			unset($_REQUEST['related_name']);
		}
		if(isset($_REQUEST['productNewPrice'])){
			$productNewPrice = $_REQUEST['productNewPrice'];
			unset($_REQUEST['productNewPrice']);
		}
		// Added By Dev for xml asop52f41v78x8z5
		if(isset($_REQUEST['productAmdPrice'])){
			$productAmdPrice = $_REQUEST['productAmdPrice'];
			unset($_REQUEST['productAmdPrice']);
		}
		if(isset($_REQUEST['productQuantity'])){
			$productQuantity = $_REQUEST['productQuantity'];
			unset($_REQUEST['productQuantity']);
		}
		if(isset($_REQUEST['productTaxAccount'])){
			$productTaxAccount = $_REQUEST['productTaxAccount'];
			unset($_REQUEST['productTaxAccount']);
		}
		if(isset($_REQUEST['productIdCon'])){
			$productIdCon = $_REQUEST['productIdCon'];
			unset($_REQUEST['productIdCon']);
		}
		// end asop52f41v78x8z5
		if(isset($_REQUEST['short_desc'])){
			$related_short_descs = $_REQUEST['short_desc'];
			unset($_REQUEST['short_desc']);
		} else {	
			getwayConnect::getwaySend("DELETE FROM order_related_product_description where order_id='{$_REQUEST['orderId']}'");
		}
		getwayConnect::getwaySend("DELETE from order_related_product_description where order_id = '{$_REQUEST['orderId']}'");
		// Added By Dev for xml asop52f41v78x8z5
		getwayConnect::getwaySend("DELETE from order_tax_info where rg_order_id = '{$_REQUEST['orderId']}'");
		// end asop52f41v78x8z5
		if(isset($related_short_descs)){
			foreach($related_short_descs as $key => $related_short_desc){
				$related_prod_name = '';
				$related_prod_price = 0;
				$related_prod_price_amd = 0;
				$related_prod_quantity = 0;
				$related_prod_tax_account = 0;
				$related_prod_id_con = 0;
				if(isset($related_name)){
					$related_prod_name = $related_name[$key]; 
				}
				if(isset($productNewPrice)){
					$related_prod_price = $productNewPrice[$key]; 
				}
				// Added By Dev for xml asop52f41v78x8z5
				if(isset($productAmdPrice)){
					$related_prod_price_amd = $productAmdPrice[$key]; 
				}
				if(isset($productQuantity)){
					$related_prod_quantity = $productQuantity[$key]; 
				}
				if(isset($productTaxAccount)){
					$related_prod_tax_account = $productTaxAccount[$key]; 
				}
				if(isset($productIdCon)){
					$related_prod_id_con = $productIdCon[$key]; 
				}
				// end asop52f41v78x8z5
				$related_prod_name = str_replace("'", "\'", $related_prod_name);
				$related_short_desc = str_replace("'", "\'", $related_short_desc);
				getwayConnect::getwayData("INSERT into `order_related_product_description` (order_id, related_id, `description`, `name`, `price`) VALUE ('{$_REQUEST['orderId']}', '{$key}', '{$related_short_desc}', '{$related_prod_name}', '{$related_prod_price}')");
				// Added By Dev for xml asop52f41v78x8z5
				getwayConnect::getwayData("INSERT into `order_tax_info` (rg_order_id,price_amd, quantity, `tax_account_id`,`product_id`) VALUE ('{$_REQUEST['orderId']}','{$related_prod_price_amd}', '{$related_prod_quantity}', '{$related_prod_tax_account}', '{$related_prod_id_con}')");
				// end asop52f41v78x8z5
			}
		}
		$actionQuery = "";
		$_REQUEST['important'] = (isset($_REQUEST['important'])) ? $_REQUEST['important'] : 0;
		if(!isset($_REQUEST['confirmed'])){
			$_REQUEST['confirmed_by'] = 0;
		}
		$_REQUEST['receiver_name'] = trim(preg_replace('/\s+/', ' ', $_REQUEST['receiver_name']));
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
				if($key == "sell_point" && ($value == "rtp" || $value == "flp" || $value == "ows")){
					$value = checkaddslashes($_REQUEST["sell_point_partner"]);
				}
				if($key == 'bonus_info'){
					$value = trim($value);
				}
				$actionQuery .= " {$key} = '{$value}', ";
			} 
		}
		$value = "<br>Edit: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		// $actionQuery .= "log = CONCAT(log,'{$value}') ";
		if($lastOperator == 'Robot'){
			$actionQuery .= " operator = '{$operator}' ";
			// $actionQuery .= ",operator = '{$operator}' ";
		} else if($lastOperator == ''){
			$actionQuery .= " operator = '{$_REQUEST['operator']}' ";
			// $actionQuery .= ",operator = '{$_REQUEST['operator']}' ";
		}
		$actionQuery = rtrim($actionQuery,", ");
		//print_r($postArray);
		//echo $actionQuery;
		if(isset($_REQUEST["id"]))
		{
			// Created By Hrach
			$log = false;
			$greetings_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
			$greetings_card_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$greetings_card_type[0]['id']}' and order_id = '{$_REQUEST["id"]}'");
			$notes_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes'");
			$notes_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$notes_type[0]['id']}' and order_id = '{$_REQUEST["id"]}'");
			$notes_for_florist_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes_for_florist'");
			$notes_for_florist_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$notes_for_florist_type[0]['id']}' and order_id = '{$_REQUEST["id"]}'");
			if(isset($greetings_card_row[0])){
				getwayConnect::getwayData("UPDATE order_notes SET value='{$_REQUEST["greetings_card"]}' WHERE order_id='{$_REQUEST["id"]}' and type_id = '{$greetings_card_type[0]['id']}'");
			}
			else{
				getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$_REQUEST["id"]}','{$greetings_card_type[0]['id']}', '{$_REQUEST["greetings_card"]}')");
			}
			if(isset($notes_row[0])){
				getwayConnect::getwayData("UPDATE order_notes SET value='{$_REQUEST["notes"]}' WHERE order_id='{$_REQUEST["id"]}' and type_id = '{$notes_type[0]['id']}'");
			}
			else{
				getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$_REQUEST["id"]}','{$notes_type[0]['id']}', '{$_REQUEST["notes"]}')");
			}
			if(isset($notes_for_florist_row[0])){
				getwayConnect::getwayData("UPDATE order_notes SET value='{$_REQUEST["notes_for_florist"]}' WHERE order_id='{$_REQUEST["id"]}' and type_id = '{$notes_for_florist_type[0]['id']}'");
			}
			else{
				getwayConnect::getwayData("INSERT into `order_notes` (order_id,type_id, value) VALUE ('{$_REQUEST["id"]}','{$notes_for_florist_type[0]['id']}', '{$_REQUEST["notes_for_florist"]}')");
			}
			getwayConnect::getwayData("UPDATE rg_orders SET notes='" . mb_strlen($_REQUEST["notes"]) . "',notes_for_florist='" . mb_strlen($_REQUEST["notes_for_florist"]) . "',greetings_card='" . mb_strlen($_REQUEST["greetings_card"]) . "' WHERE id='{$_REQUEST["id"]}'");
			$anonym = 0;
			if(isset($_REQUEST["anonym"])){
				$anonym = 1;
			}
			getwayConnect::getwayData("UPDATE rg_orders SET anonym='" . $anonym . "' WHERE id='{$_REQUEST["id"]}'");
			// var_dump($_REQUEST["anonym"]);die;
			$orderOldInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$_REQUEST["orderId"]}'");
			$operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$_REQUEST["operator"]}'");
			$html_log_for_update_order = '';
			if(!empty($orderOldInfo)){
				if ( isset( $_REQUEST['bonus_type']) && $_REQUEST['bonus_type'] != $orderOldInfo[0]['bonus_type']){
					$log = true;
					$bonus_type[1] = "Բոնուս";
					$bonus_type[2] = "Մալուս";
					$bonus_type[3] = "Ոչինչ";
					$html_log_for_update_order .= 'Bonus Type: <span style="color:blue"> ' . $bonus_type[$orderOldInfo[0]['bonus_type']] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $bonus_type[$_REQUEST['bonus_type']] . '</b></span>';
				}
				if ( isset( $_REQUEST['bonus_info']) && $_REQUEST['bonus_info'] != $orderOldInfo[0]['bonus_info']){
					$log = true;
					$html_log_for_update_order.= '<br> Bonus Info: <span style="color:blue"> ' . $orderOldInfo[0]['bonus_info'] . '  </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['bonus_info'] . '</b></span>';
				}
				if ( $_REQUEST['delivery_date'] != $orderOldInfo[0]['delivery_date']){
					$log = true;
					$html_log_for_update_order.= '<br> Մեկնման օրը:<span style="color:blue"> ' . $orderOldInfo[0]['delivery_date'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['delivery_date']. '</b></span>';
				}
				if ( $_REQUEST['delivery_time'] != $orderOldInfo[0]['delivery_time']){
					$NewDeliveryTime = getwayConnect::getwayData("SELECT * FROM `delivery_time` WHERE `id` = '{$_REQUEST["delivery_time"]}'");
					$OldDeliveryTime = getwayConnect::getwayData("SELECT * FROM `delivery_time` WHERE `id` = '{$orderOldInfo[0]['delivery_time']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Առաքման ժամ: <span style="color:blue"> ' . $OldDeliveryTime[0]['name'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $NewDeliveryTime[0]['name'] . '</b></span>';
				}
				if ( $_REQUEST['delivery_time_manual'] != $orderOldInfo[0]['delivery_time_manual']){
					$log = true;
					$html_log_for_update_order.= '<br> Առաքման ժամ ոչ ավտմատացված: <span style="color:blue"> ' . $orderOldInfo[0]['delivery_time_manual'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['delivery_time_manual']. '</b></span>';
				}
				if ( $_REQUEST['travel_time_end'] != $orderOldInfo[0]['travel_time_end']){
					$log = true;
					$html_log_for_update_order.= '<br> Մեկնման ժամի ավարտ: <span style="color:blue"> ' . $orderOldInfo[0]['travel_time_end'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['travel_time_end'] . '</b></span>';
				}
				if ( $_REQUEST['delivery_region'] != $orderOldInfo[0]['delivery_region']){
					$NewDeliveryRegion = getwayConnect::getwayData("SELECT * FROM `delivery_region` WHERE `id` = '{$_REQUEST["delivery_region"]}'");
					$OldDeliveryRegion = getwayConnect::getwayData("SELECT * FROM `delivery_region` WHERE `id` = '{$orderOldInfo[0]['delivery_region']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Երկիր: <span style="color:blue"> ' . $OldDeliveryRegion[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $NewDeliveryRegion[0]['name_am']. '</b></span>';
				}
				if ( $_REQUEST['receiver_name'] != $orderOldInfo[0]['receiver_name']){
					$log = true;
					$html_log_for_update_order.= '<br> Receiver Name: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_name'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_name']. '</b></span>';
				}
				if ( $_REQUEST['product'] != $orderOldInfo[0]['product']){
					$log = true;
					$html_log_for_update_order.= '<br> Ստացող: <span style="color:blue"> ' . $orderOldInfo[0]['product'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['product']. '</b></span>';
				}
				if ( $_REQUEST['price'] != $orderOldInfo[0]['price']){
					$log = true;
					$html_log_for_update_order.= '<br> Գին: <span style="color:blue"> ' . $orderOldInfo[0]['price'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['price']. '</b></span>';
				}
				if ( $_REQUEST['currency'] != $orderOldInfo[0]['currency']){
					$Newcurrency = getwayConnect::getwayData("SELECT * FROM `currency` WHERE `id` = '{$_REQUEST["currency"]}'");
					$Oldcurrency = getwayConnect::getwayData("SELECT * FROM `currency` WHERE `id` = '{$orderOldInfo[0]['currency']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Արժույթ: <span style="color:blue"> ' . $Oldcurrency[0]['name'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newcurrency[0]['name']. '</b></span>';
				}
				if ( !empty($_REQUEST['receiver_subregion']) && $_REQUEST['receiver_subregion'] != $orderOldInfo[0]['receiver_subregion']){
					$log = true;
					$html_log_for_update_order.= '<br> Receiver Subregion: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_subregion'] .  ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_subregion']. '</b></span>';
				}
				if ( $_REQUEST['organisation'] != $orderOldInfo[0]['organisation']){
					$NewOrganisation = getwayConnect::getwayData("SELECT * FROM `organisations` WHERE `id` = '{$_REQUEST["organisation"]}'");
					$OldOrganisation = getwayConnect::getwayData("SELECT * FROM `organisations` WHERE `id` = '{$orderOldInfo[0]['organisation']}'");
					$log = true;
					if(!empty($OldOrganisation)){
						$html_log_for_update_order.= '<br> Կազմակերպություններ: <span style="color:blue"> ' . $OldOrganisation[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $NewOrganisation[0]['name_am']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Կազմակերպություններ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $NewOrganisation[0]['name_am']. '</b></span>';
					}
				}
				if ( $_REQUEST['receiver_address'] != $orderOldInfo[0]['receiver_address']){
					$log = true;
					$html_log_for_update_order.= '<br> Ստացողի հասցե: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_address'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_address'] . '</b></span>';
				}
				if ( $_REQUEST['receiver_floor'] != $orderOldInfo[0]['receiver_floor']){
					$log = true;
					if(empty($orderOldInfo[0]['receiver_floor'])){
						$html_log_for_update_order.= '<br> Ստացողի բնակարան: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_floor']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ստացողի բնակարան: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_floor'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_floor']. '</b></span>';
					}
				}
				if ( $_REQUEST['receiver_tribute'] != $orderOldInfo[0]['receiver_tribute']){
					$log = true;
					if(empty($orderOldInfo[0]['receiver_tribute'])){
						$html_log_for_update_order.= '<br> Ստացողի հարկ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_tribute']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ստացողի հարկ: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_tribute'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_tribute']. '</b></span>';
					}
				}
				if ( $_REQUEST['receiver_entrance'] != $orderOldInfo[0]['receiver_entrance']){
					$log = true;
					if(empty($orderOldInfo[0]['receiver_entrance'])){
						$html_log_for_update_order.= '<br> Ստացողի մուտքը: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_entrance']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ստացողի մուտքը:<span style="color:blue"> ' . $orderOldInfo[0]['receiver_entrance'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_entrance'] . '</b></span>';
					}
				}
				if ( $_REQUEST['receiver_door_code'] != $orderOldInfo[0]['receiver_door_code']){
					$log = true;
					if(empty($orderOldInfo[0]['receiver_door_code'])){
						$html_log_for_update_order.= '<br> Ստացողի դռան ծածկագիր: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['receiver_door_code']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ստացողի դռան ծածկագիր:<span style="color:blue"> ' . $orderOldInfo[0]['receiver_door_code'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_door_code']. '</b></span>';
					}
				}
				if ( $_REQUEST['receiver_phone'] != $orderOldInfo[0]['receiver_phone']){
					$log = true;
					if(empty($orderOldInfo[0]['receiver_phone'])){
						$html_log_for_update_order.= '<br> Ստացողի հեռախոս: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_phone']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ստացողի հեռախոս: <span style="color:blue"> ' . $orderOldInfo[0]['receiver_phone'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['receiver_phone']. '</b></span>';
					}
				}
				if ( $_REQUEST['deliverer'] != $orderOldInfo[0]['deliverer']){
					$constants_deliver = get_defined_constants();
					$Newdeliverer = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = '{$_REQUEST["deliverer"]}'");
					$Olddeliverer = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = '{$orderOldInfo[0]['deliverer']}'");
					$log = true;
					if(!empty($constants_deliver[$Olddeliverer[0]['name']])){
						$oldDeliverer_name = $constants_deliver[$Olddeliverer[0]['name']];
					}
					else{
						$oldDeliverer_name = $Olddeliverer[0]['name'];
					}
					if(!empty($constants_deliver[$Newdeliverer[0]['name']])){
						$newDeliverer_name = $constants_deliver[$Newdeliverer[0]['name']];
					}
					else{
						$newDeliverer_name = $Newdeliverer[0]['name'];
					}
					$html_log_for_update_order.= '<br> Առաքիչ: <span style="color:blue">' . $oldDeliverer_name  . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $newDeliverer_name. '</b></span>';
				}
				if ( isset($_REQUEST['delivery_type']) && $_REQUEST['delivery_type'] != $orderOldInfo[0]['delivery_type']){
					$Newdelivery_type = getwayConnect::getwayData("SELECT * FROM `delivery_drivers` WHERE `id` = '{$_REQUEST["delivery_type"]}'");
					$Olddelivery_type = getwayConnect::getwayData("SELECT * FROM `delivery_drivers` WHERE `id` = '{$orderOldInfo[0]['delivery_type']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Առաքման Տեսակ: <span style="color:blue">' . $Olddelivery_type[0]['name'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_type[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['delivery_status'] != $orderOldInfo[0]['delivery_status']){
					$Newdelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'");
					$Olddelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$orderOldInfo[0]['delivery_status']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Ստատուս: <span style="color:blue">' . $Olddelivery_status[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_status[0]['name_am']. '</b></span>';
				}
				if ( $_REQUEST['order_source'] != $orderOldInfo[0]['order_source']){
					$Neworder_source = getwayConnect::getwayData("SELECT * FROM `delivery_source` WHERE `id` = '{$_REQUEST["order_source"]}'");
					$Oldorder_source = getwayConnect::getwayData("SELECT * FROM `delivery_source` WHERE `id` = '{$orderOldInfo[0]['order_source']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Աղբյուր: <span style="color:blue"> ' . $Oldorder_source[0]['name'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Neworder_source[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['order_source_optional'] != $orderOldInfo[0]['order_source_optional']){
					$log = true;
					if(empty($orderOldInfo[0]['order_source_optional'])){
						$html_log_for_update_order.= '<br> Աղբյուրի տվյալներ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['order_source_optional']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Աղբյուրի տվյալներ:<span style="color:blue"> ' . $orderOldInfo[0]['order_source_optional'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['order_source_optional']. '</b></span>';
					}
				}
				if ( $_REQUEST['payment_type'] != $orderOldInfo[0]['payment_type']){
					$Newpayment_type = getwayConnect::getwayData("SELECT * FROM `delivery_payment` WHERE `id` = '{$_REQUEST["payment_type"]}'");
					$Oldpayment_type = getwayConnect::getwayData("SELECT * FROM `delivery_payment` WHERE `id` = '{$orderOldInfo[0]['payment_type']}'");
					$log = true;
					if(!empty($Oldpayment_type)){
						$html_log_for_update_order.= '<br> Վճարման Ձև: <span style="color:blue">' . $Oldpayment_type[0]['name_am'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newpayment_type[0]['name_am']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Վճարման Ձև: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newpayment_type[0]['name_am']. '</b></span>';
					}
				}
				if ( $_REQUEST['payment_optional'] != $orderOldInfo[0]['payment_optional']){
					$log = true;
					if(empty($orderOldInfo[0]['payment_optional'])){
						$html_log_for_update_order.= '<br> Վճարման տվյալներ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['payment_optional']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Վճարման տվյալներ: <span style="color:blue">' . $orderOldInfo[0]['payment_optional'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['payment_optional']. '</b></span>';
					}
				}
				if ( $_REQUEST['sender_name'] != $orderOldInfo[0]['sender_name']){
					$log = true;
					if(empty($orderOldInfo[0]['sender_name'])){
						$html_log_for_update_order.= '<br> Ուղարկողի Անուն: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_name']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ուղարկողի Անուն: <span style="color:blue"> ' . $orderOldInfo[0]['sender_name'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_name']. '</b></span>';
					}
				}
				if ( $_REQUEST['sender_country'] != $orderOldInfo[0]['sender_country']){
					$Newsender_country = getwayConnect::getwayData("SELECT * FROM `countries` WHERE `id` = '{$_REQUEST["sender_country"]}'");
					$Oldsender_country = getwayConnect::getwayData("SELECT * FROM `countries` WHERE `id` = '{$orderOldInfo[0]['sender_country']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Ուղարկողի Երկիր: <span style="color:blue"> ' . $Oldsender_country[0]['name_am'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newsender_country[0]['name_am']. '</b></span>';
				}
				if ( $_REQUEST['sender_region'] != $orderOldInfo[0]['sender_region']){
					$log = true;
					if(empty($orderOldInfo[0]['sender_region'])){
						$html_log_for_update_order.= '<br> Ուղարկողի Քաղաք: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['sender_region']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ուղարկողի Քաղաք: <span style="color:blue"> ' . $orderOldInfo[0]['sender_region'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_region']. '</b></span>';
					}
				}
				if ( $_REQUEST['sender_address'] != $orderOldInfo[0]['sender_address']){
					$log = true;
					if(empty($orderOldInfo[0]['sender_address'])){
						$html_log_for_update_order.= '<br> Ուղարկողի Հասցե: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_address']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ուղարկողի Հասցե: <span style="color:blue">' . $orderOldInfo[0]['sender_address'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_address']. '</b></span>';
					}
				}
				if ( $_REQUEST['sender_phone'] != $orderOldInfo[0]['sender_phone']){
					$log = true;
					if(empty($orderOldInfo[0]['sender_phone'])){
						$html_log_for_update_order.= '<br> Ուղարկողի Հեռ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_phone']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ուղարկողի Հեռ: <span style="color:blue"> ' . $orderOldInfo[0]['sender_phone'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_phone']. '</b></span>';
					}
				}
				if ( $_REQUEST['sender_email'] != $orderOldInfo[0]['sender_email']){
					$log = true;
					if(empty($orderOldInfo[0]['sender_email'])){
						$html_log_for_update_order.= '<br> Ուղարկողի էլ.փոստ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_email']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ուղարկողի էլ.փոստ: <span style="color:blue"> ' . $orderOldInfo[0]['sender_email'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['sender_email']. '</b></span>';
					}
				}
				if ( $_REQUEST['delivery_reason'] != $orderOldInfo[0]['delivery_reason']){
					$Newdelivery_reason = getwayConnect::getwayData("SELECT * FROM `delivery_reason` WHERE `id` = '{$_REQUEST["delivery_reason"]}'");
					$Olddelivery_reason = getwayConnect::getwayData("SELECT * FROM `delivery_reason` WHERE `id` = '{$orderOldInfo[0]['delivery_reason']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Պատճառ/առիթ: <span style="color:blue"> ' . $Olddelivery_reason[0]['name'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_reason[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['greetings_card'] != $orderOldInfo[0]['greetings_card']){
					$log = true;
					if(empty($orderOldInfo[0]['greetings_card'])){
						$html_log_for_update_order.= '<br> Բացիկի տեքստ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['greetings_card']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Բացիկի տեքստ: <span style="color:blue"> ' . $orderOldInfo[0]['greetings_card'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['greetings_card']. '</b></span>';
					}
				}
				if ( $_REQUEST['delivery_language_primary'] != $orderOldInfo[0]['delivery_language_primary']){
					$Newdelivery_language_primary = getwayConnect::getwayData("SELECT * FROM `delivery_language` WHERE `id` = '{$_REQUEST["delivery_language_primary"]}'");
					$Olddelivery_language_primary = getwayConnect::getwayData("SELECT * FROM `delivery_language` WHERE `id` = '{$orderOldInfo[0]['delivery_language_primary']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Պատվիրատուի առաջնային լեզու:: <span style="color:blue">' . $Olddelivery_language_primary[0]['name'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_language_primary[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['delivery_language_secondary'] != $orderOldInfo[0]['delivery_language_secondary']){
					$Newdelivery_language_secondary = getwayConnect::getwayData("SELECT * FROM `delivery_language` WHERE `id` = '{$_REQUEST["delivery_language_secondary"]}'");
					$Olddelivery_language_secondary = getwayConnect::getwayData("SELECT * FROM `delivery_language` WHERE `id` = '{$orderOldInfo[0]['delivery_language_secondary']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Պատվիրատուի երկրորդական լեզու:: <span style="color:blue"> ' . $Olddelivery_language_secondary[0]['name'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_language_secondary[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['notes'] != $orderOldInfo[0]['notes']){
					$log = true;
					if(empty($orderOldInfo[0]['notes'])){
						$html_log_for_update_order.= '<br> Օպերատորի Նշումներ: <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['notes']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Օպերատորի Նշումներ: <span style="color:blue">' . $orderOldInfo[0]['notes'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['notes']. '</b></span>';
					}
				}
				if ( $_REQUEST['notes_for_florist'] != $orderOldInfo[0]['notes_for_florist']){
					$log = true;
					if(empty($orderOldInfo[0]['notes_for_florist'])){
						$html_log_for_update_order.= '<br> Ցուցում Վարորդին: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['notes_for_florist']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Ցուցում Վարորդին:  <span style="color:blue">' . $orderOldInfo[0]['notes_for_florist'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['notes_for_florist']. '</b></span>';
					}
				}
				if ( $_REQUEST['sell_point_partner'] != '' && $_REQUEST['sell_point_partner'] != $orderOldInfo[0]['sell_point']){
					$Newdelivery_sellpoint = getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint` WHERE `id` = '{$_REQUEST["sell_point_partner"]}'");
					$Olddelivery_sellpoint = getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint` WHERE `id` = '{$orderOldInfo[0]['sell_point']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Sell Point:  <span style="color:blue">' . $Olddelivery_sellpoint[0]['name'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_sellpoint[0]['name']. '</b></span>';
				}
				if ( $_REQUEST['flourist_id'] != $orderOldInfo[0]['flourist_id']){
					$Newflourist_id = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$_REQUEST["flourist_id"]}'");
					$Oldflourist_id = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$orderOldInfo[0]['flourist_id']}'");
					$log = true;
					$html_log_for_update_order.= '<br> Մատակարար: <span style="color:blue">' . $Oldflourist_id[0]['username'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newflourist_id[0]['username']. '</b></span>';
				}
				if ( $_REQUEST['operator_name'] != $orderOldInfo[0]['operator_name']){
					$log = true;
					if(empty($orderOldInfo[0]['operator_name'])){
						$html_log_for_update_order.= '<br> Պատասխանատու: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['operator_name']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Պատասխանատու:<span style="color:blue">' . $orderOldInfo[0]['operator_name'] .  '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['operator_name']. '</b></span>';
					}
				}
				if ( $_REQUEST['keyword'] != $orderOldInfo[0]['keyword']){
					$log = true;
					if(empty($orderOldInfo[0]['keyword'])){
						$html_log_for_update_order.= '<br> Keyword: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['keyword']. '</b></span>';
					}
					else{
						$html_log_for_update_order.= '<br> Keyword: <span style="color:blue">' . $orderOldInfo[0]['keyword'] . '</span><span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $_REQUEST['keyword']. '</b></span>';
					}
				}
				if($log){
					$table_count = GetOrderTableCount(substr($_REQUEST['orderId'], 0, 2));
					getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$_REQUEST["orderId"]}','{$html_log_for_update_order}','{$operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."',{$orderOldInfo[0]['delivery_status']})");
					if(($_REQUEST['delivery_date'] == date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')))) || $_REQUEST['delivery_date'] == date('Y-m-d')) && in_array($_REQUEST["delivery_status"], $arrayFlorstDeliveryCases)){
						sendTelegramMessageToFlorist();
					}
				}
			}
			//

			$id =  htmlentities($_REQUEST["id"]);

			if(empty($_REQUEST['complain_type']) && empty($_REQUEST['complain_reason'])){
				getwayConnect::getwaySend("DELETE FROM complain_of_orders where order_id = '{$id}'");
				getwayConnect::getwaySend("UPDATE rg_orders SET complain='0' WHERE id='{$id}'");
			}
			$complain_for_order_data = getwayConnect::getwayData("SELECT * from complain_of_orders where order_id='{$id}'");
			if(empty($complain_for_order_data)){
				if(!empty($_REQUEST['complain_type']) || !empty($_REQUEST['complain_reason'])){
					getwayConnect::getwaySend("INSERT INTO complain_of_orders ( type_id,reason,order_id) VALUES ('" . $_REQUEST['complain_type'] . "', '" . $_REQUEST['complain_reason'] . "','{$id}')");
					getwayConnect::getwaySend("UPDATE rg_orders SET complain='1' WHERE id='{$id}'");
				}
			}
			else{
				getwayConnect::getwaySend("UPDATE complain_of_orders SET type_id='" . $_REQUEST['complain_type'] . "' ,reason='" . $_REQUEST['complain_reason'] . "' WHERE order_id='{$id}'");
			}
			//echo "UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'";
			$delivered_at = getwayConnect::getwayData("SELECT delivered_at from rg_orders where id='{$id}'",PDO::FETCH_ASSOC);
			if(!isset($delivered_at[0]['delivered_at']) && $_REQUEST['delivery_status'] == 3){
				$date = gmdate('Y-m-d H:i:s', time() + 4 * 3600);
				$actionQuery .= ", `delivered_at`='{$date}' ";
			}
			if(isset( $relatedProductString ) && $relatedProductString == ''){

				getwayConnect::getwaySend("DELETE FROM order_related_products where order_id='{$id}'");
			}
			if(isset($getRelatedProductString) && !empty($getRelatedProductString)){
				getwayConnect::getwaySend("UPDATE order_related_products SET jos_vm_product_id='{$relatedProductString}' WHERE order_id='{$id}'");
			} else {
				if(isset($relatedProductString) && $relatedProductString != ''){
					getwayConnect::getwaySend("INSERT INTO order_related_products (order_id, jos_vm_product_id) VALUES ('{$id}', '{$relatedProductString}')");
				}
			}
			// Added By Dev for xml asop52f41v78x8z5
			$tax_number_of_check_info = getwayConnect::getwayData("SELECT * FROM `tax_numbers_of_check` WHERE `order_id` = '{$id}'");
			$delivery_other_price = '';
			if(isset($_REQUEST['delivery_other_price'])){
				$delivery_other_price = $_REQUEST['delivery_other_price'];
			}
			$delivery_static_price = '0';
			if(isset($_REQUEST['delivery_static_price'])){
				$delivery_static_price = $_REQUEST['delivery_static_price'];
			}
			if($tax_number_of_check_info){
				getwayConnect::getwaySend("UPDATE tax_numbers_of_check SET hdm_tax='{$_REQUEST['hdm_tax']}',hvhh_tax='{$_REQUEST['hvhh_tax']}' ,postcard_amd_price='{$_REQUEST['postcard_amd_price']}' ,delivery_static_price='{$delivery_static_price}' ,delivery_other_price='{$delivery_other_price}' WHERE order_id='{$id}'");
			}
			else{
				getwayConnect::getwaySend("INSERT INTO tax_numbers_of_check (hdm_tax,hvhh_tax, order_id,postcard_amd_price,delivery_static_price,delivery_other_price) VALUES ('{$_REQUEST['hdm_tax']}','{$_REQUEST['hvhh_tax']}' ,'{$id}','{$_REQUEST['postcard_amd_price']}','{$delivery_static_price}','{$delivery_other_price}')");
			}
			// end asop52f41v78x8z5
			if(getwayConnect::getwaySend("UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'"))
			{
				// Added By Hrach08/12/19
				$operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$_REQUEST["operator"]}'");
				$new_status_of_order_info = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'");
				if(isset($_REQUEST['notes_for_pending']) && !empty($_REQUEST['notes_for_pending'])){
					getwayConnect::getwaySend("INSERT INTO pending_info (order_id, description,operator_id,created_date,status) VALUES ('{$_REQUEST['orderId']}' , '{$_REQUEST['notes_for_pending']}','{$operator_info[0]['id']}','" . date('Y-m-d H:i:s') . "','1')");
					$table_count = GetOrderTableCount(substr($_REQUEST['orderId'], 0, 2));
					$html_for_notes_for_pending_log = '<br> Անավարտի նշում: <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $_REQUEST['notes_for_pending'] . '</b></span>';
					getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date) VALUES ('{$_REQUEST["orderId"]}','{$html_for_notes_for_pending_log}','{$operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."')");
					if(($_REQUEST['delivery_date'] == date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')))) || $_REQUEST['delivery_date'] == date('Y-m-d')) && in_array($_REQUEST["delivery_status"], $arrayFlorstDeliveryCases) ){
						sendTelegramMessageToFlorist();
					}
				}
				if($_REQUEST['old_status_of_order'] == 2 && $_REQUEST['delivery_status'] != 2){
					getwayConnect::getwaySend("INSERT INTO pending_info (order_id, description,operator_id,created_date,status) VALUES ('{$_REQUEST['orderId']}' , 'Status Changed by {$_REQUEST["operator"]} ,from Pending to {$new_status_of_order_info[0]['name_en']}','{$operator_info[0]['id']}','" . date('Y-m-d H:i:s') . "','0')");
				}
				//
				if(isset($_REQUEST['image_id'])){
					addEditImage($_REQUEST['image_id'],$id, $_REQUEST['productdesc'], $_REQUEST['productprice'],$_REQUEST['producttaxid'],$_REQUEST['productquantity'],$_REQUEST['productamdprice']);
				} else {
					changeImageStatus($id);
				}
				$_REQUEST["deliverer"] = (isset($_REQUEST["deliverer"]) && is_numeric($_REQUEST["deliverer"])) ?  $_REQUEST["deliverer"] : 0;
				
				$_REQUEST["delivery_status"] = (isset($_REQUEST["delivery_status"]) && is_numeric($_REQUEST["delivery_status"])) ?  $_REQUEST["delivery_status"] : 0;
				
				if($_REQUEST["deliverer"] > 0){
								
					if(in_array($_REQUEST["delivery_status"], array(1, 3, 4, 5, 6, 7, 11, 12, 13))){
						
						$dlv = getwayConnect::getwayData("SELECT name FROM `delivery_deliverer` WHERE `id` = '{$_REQUEST["deliverer"]}'",PDO::FETCH_ASSOC);
					    $dlv = (isset($dlv[0]["name"])) ? $dlv[0]["name"] : null;
						$dlv = defined($dlv) ? @constant($dlv) : $dlv;
						
						$sts = getwayConnect::getwayData("SELECT name FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'",PDO::FETCH_ASSOC);
					    $sts = (isset($sts[0]["name"])) ? $sts[0]["name"] : null;
						$sts = defined($sts) ? @constant($sts) : $sts;
						
						$telegram_message = urlencode("N-{$id} Պատվերի Փոփոխում՝ {$dlv}-ի համար` «{$sts}» / <a href='https://new.regard-group.ru/account/orders_delivery/'></a>");
					}					

					addDeliverer($_REQUEST["deliverer"],$id);
				}
				if($cc == "fr")
				{
					$xe = getwayConnect::getwayData("SELECT `name` FROM `currency` WHERE `id` = '{$_REQUEST["currency"]}'");
					$xe = (isset($xe[0]["name"])) ? $xe[0]["name"] : null;
					$confirm_link = '<a href="https://new.regard-group.ru/flower_orders">https://new.regard-group.ru/flower_orders</a>';
					$to = 'takciparis@gmail.com';//"dxjan@ya.ru";
					$subject = 'L"ordre a été changé '.$id;
					$message = 'Livirasion date,time: '.$_REQUEST["delivery_date"].','.$_REQUEST["delivery_time"].' '.$_REQUEST["delivery_time_manual"].'<br> Prix: '.$_REQUEST["price"].' '.$xe.'<br> Address:  '.$_REQUEST["receiver_subregion"].' '.$_REQUEST["receiver_address"].'<br> Sil vous plaît confirmer! '.$confirm_link.'<br> / '.$_REQUEST["operator"];
					$from = 'sales@flowers-armenia.com';
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
					$headers .= "From: <".$from.">" . "\r\n";
					$headers .= "Cc: <".$from.">" . "\r\n";
					
					mail($to, $subject, $message, $headers);
				}
				$actioned = 2;
				
				//header("location:../");
			}else{
				$actioned = 4;
			}
			//var_dump($isUpdated);
			//die();
			$drivers_array = array(1,8,9,2);
			if($orderOldInfo[0]['delivery_status'] != 1 && $_REQUEST['delivery_status'] == 1 && $_REQUEST['delivery_date'] == date('Y-m-d') && in_array($_REQUEST['deliverer'], $drivers_array)){
				$driverInfo = GetDriverInfoForTelegram($_REQUEST['deliverer']);
				$driver_name = $driverInfo['driver_name'];
				$bot_id_driver = $driverInfo['bot_id_driver'];
				$chat_id_driver = $driverInfo['chat_id_driver'];
				$max_clock = $driverInfo['max_clock'];
				$min_clock = $driverInfo['min_clock'];
				$telegram_message = urlencode('Հարգելի ' . $driver_name . ' <a href="http://new.regard-group.ru/account/orders_delivery/"> N- ' . $_REQUEST['id'] . ' </a> պատվերը կցվեց Ձեզ` առաքման նպատակով:');
				$current_time = date("H:i:s");
			    if($current_time < $max_clock && $current_time > $min_clock){
					$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id_driver."&chat_id=" . $chat_id_driver . "&telegram_message=".$telegram_message);
			    }
			}
			if($orderOldInfo[0]['delivery_status'] == 1 && $_REQUEST['delivery_status'] == 1){
				if($orderOldInfo[0]['deliverer'] != $_REQUEST['deliverer'] && in_array($_REQUEST['deliverer'], $drivers_array) && $_REQUEST['delivery_date'] == date('Y-m-d')){
					$driverInfo = GetDriverInfoForTelegram($_REQUEST['deliverer']);
					$driver_name = $driverInfo['driver_name'];
					$bot_id_driver = $driverInfo['bot_id_driver'];
					$chat_id_driver = $driverInfo['chat_id_driver'];
					$max_clock = $driverInfo['max_clock'];
					$min_clock = $driverInfo['min_clock'];
					$telegram_message = urlencode('Հարգելի ' . $driver_name . ' <a href="http://new.regard-group.ru/account/orders_delivery/"> N- ' . $_REQUEST['id'] . ' </a> պատվերը կցվեց Ձեզ` առաքման նպատակով:');
					$current_time = date("H:i:s");
			    	if($current_time < $max_clock && $current_time > $min_clock){
						$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id_driver."&chat_id=" . $chat_id_driver . "&telegram_message=".$telegram_message);
					}
				}
			}
		}
	}
	
	if($telegram_message){
	//  commented by Ruben on 5 April 2017 as of Error in the evening. 
		// @file_get_contents("https://api.telegram.org/bot{$bot_id}/sendMessage?chat_id=-1001108550129&text={$telegram_message}&parse_mode=html&disable_web_page_preview=true");
		$resp =	@file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id."&chat_id=-1001108550129"."&telegram_message=".$telegram_message);

	}
	
	if(isset($_POST['get']) && $_POST['get'] == 'getOrganisations'){
		$organisations = getwayConnect::getwayData("SELECT `id`, `name_am`, `street`, `address`, `floor`, `entrance`, `door_code` FROM organisations where `type`='{$_POST['type']}' && `region`='{$_POST['region']}' && `active`=1");
		echo json_encode($organisations);
		exit;
	}


if(isset($_REQUEST['other_orders'])){
	$receiver_phone = $_REQUEST['receiver_phone'];
	$sender_phone = $_REQUEST['sender_phone'];
	$sender_email = $_REQUEST['sender_email'];
	$keyword = $_REQUEST['keyword'];
	$query = "SELECT rg_orders.*, countries.name_am as country_name, delivery_street.name as receiver_street_name, delivery_sellpoint.name as sell_point_name, complain_of_orders.type_id FROM rg_orders
			LEFT JOIN countries on rg_orders.sender_country = countries.id
			LEFT JOIN delivery_street on rg_orders.receiver_street = delivery_street.code
			LEFT JOIN complain_of_orders ON rg_orders.id = complain_of_orders.order_id
			LEFT JOIN delivery_sellpoint on rg_orders.sell_point = delivery_sellpoint.id where rg_orders.id != '{$_REQUEST['orderId']}' AND ( ";

	if(isset($receiver_phone) && $receiver_phone != ''){
		$query .= " receiver_phone LIKE '%{$receiver_phone}' OR";
	}
	if(isset($sender_phone) && $sender_phone != ''){
		$query .= " sender_phone LIKE '%{$sender_phone}' OR";
	}
	if(isset($sender_phone) && $sender_phone != ''){
		$query .= " order_source_optional LIKE '%{$sender_phone}' OR";
	}
	if(isset($sender_email) && $sender_email != ''){
		$query .= " sender_email = '{$sender_email}' OR";
	}
	if(isset($sender_email) && $sender_email != ''){
		$query .= " order_source_optional LIKE '%{$sender_email}' OR";
	}
	if(isset($keyword) && $keyword != ''){
		$query .= " keyword = '{$keyword}' OR";
	}
	$query = rtrim($query, 'OR');
	$query .= " )";
	$query .= " GROUP BY rg_orders.id ORDER BY rg_orders.id DESC";
	$results = getwayConnect::getwayData($query);
	foreach($results as $key => $value){
		$relateds = getwayConnect::getwayData("SELECT * FROM order_related_products where order_id='{$value['id']}'");
		$results[$key]['products'] = [];
		if(isset($relateds) && !empty($relateds) && isset($relateds[0]) && isset($relateds[0]['jos_vm_product_id'])){
			$relateds = explode(",", $relateds[0]['jos_vm_product_id']);
			foreach($relateds as $ket => $related){
				$images = getwayConnect::getwayData("SELECT `product_thumb_image` as `image_source`, product_width, product_height,
					`product_name` as `name`, `product_s_desc` as `image_note`, product_sku as sku, order_related_product_description.`description` as short_desc, 
					order_related_product_description.`name` as changed_name, jos_vm_product_price.product_price as `price`, 
					order_related_product_description.ready as related_ready, order_related_product_description.for_purchase as for_purchase, 
					order_related_product_description.related_id as related_id,
					order_related_product_description.id as order_related_id,
					user.username as who_requested
					FROM `jos_vm_product`
					RIGHT JOIN order_related_product_description on order_related_product_description.order_id='{$value['id']}' AND order_related_product_description.related_id='{$related}'
					RIGHT JOIN jos_vm_product_price on jos_vm_product_price.product_id = '{$related}'
					LEFT JOIN user on user.id = order_related_product_description.who_requested
					WHERE jos_vm_product.`product_id` = '{$related}'", PDO::FETCH_ASSOC);
				if(isset($images[0])){
					array_push($results[$key]['products'], $images[0]);
				}
			}
		}
		//start created by Hrach 11 06 19
		$new_query = "SELECT * FROM `delivery_images` WHERE rg_order_id = '" . $value['id'] . "' ";
		$new_result = getwayConnect::getwayData($new_query);
		if(isset($new_result)){
			$results[$key]['products_delivery'] = $new_result;
		}
		else{
			$results[$key]['products_delivery'] = [];
		}
		// end 11 06 19
	}
	echo json_encode($results);
	exit;
}
if(isset($_REQUEST['other_deliveries']) && $_REQUEST['other_deliveries'] && isset($_REQUEST['orderId'])){
	$query = "SELECT rg_orders.*, delivery_street.name as receiver_street_name, delivery_subregion.name as region_name FROM rg_orders 
			LEFT JOIN delivery_street on rg_orders.receiver_street = delivery_street.code
			LEFT JOIN delivery_subregion on rg_orders.receiver_subregion = delivery_subregion.code
			where deliverer='{$_REQUEST['deliverer']}' AND delivery_status IN (1,3,6,7,11,12,13,14) ";
	if(isset($_REQUEST['delivery_time']) && $_REQUEST['delivery_time'] != ''){
		$query .= " AND rg_orders.delivery_time='{$_REQUEST['delivery_time']}'";
	}
	if(isset($_REQUEST['delivery_date']) && $_REQUEST['delivery_date'] != ''){
		$query .= " AND rg_orders.delivery_date='{$_REQUEST['delivery_date']}' ";
	} else {
		$query .= " AND rg_orders.delivery_date=CURDATE() ";
	}
	$query .= " AND rg_orders.id != '{$_REQUEST['orderId']}'";
	$results = getwayConnect::getwayData($query);
	foreach($results as $key => $value){
		$relateds = getwayConnect::getwayData("SELECT * FROM order_related_products where order_id='{$value['id']}'");
		$results[$key]['products'] = [];
		if(isset($relateds) && !empty($relateds) && isset($relateds[0]) && isset($relateds[0]['jos_vm_product_id'])){
			$relateds = explode(",", $relateds[0]['jos_vm_product_id']);
			foreach($relateds as $ket => $related){
				$images = getwayConnect::getwayData("SELECT `product_thumb_image` as `image_source`, product_width, product_height,
					`product_name` as `name`, `product_s_desc` as `image_note`, product_sku as sku, order_related_product_description.`description` as short_desc, 
					order_related_product_description.`name` as changed_name, jos_vm_product_price.product_price as `price`, 
					order_related_product_description.ready as related_ready, order_related_product_description.for_purchase as for_purchase, 
					order_related_product_description.related_id as related_id,
					order_related_product_description.id as order_related_id,
					user.username as who_requested
					FROM `jos_vm_product`
					RIGHT JOIN order_related_product_description on order_related_product_description.order_id='{$value['id']}' AND order_related_product_description.related_id='{$related}'
					RIGHT JOIN jos_vm_product_price on jos_vm_product_price.product_id = '{$related}'
					LEFT JOIN user on user.id = order_related_product_description.who_requested
					WHERE jos_vm_product.`product_id` = '{$related}'", PDO::FETCH_ASSOC);
				if(isset($images[0])){
					array_push($results[$key]['products'], $images[0]);
				}
			}
		}
	}
	echo json_encode($results);
	exit;
}
// Added By Hrach
	if( !empty($orderData[0]["receiver_name"]) ){
		$full_name =  explode( ' ' , $orderData[0]["receiver_name"] );
		if(isset($full_name[0])){
			$receiver_first_name = $full_name[0];
			$first_names = getwayConnect::getwayData("SELECT * FROM translate_of_names where first_name_arm ='{$receiver_first_name}'");
		}
		if(isset($full_name[1])){
			$receiver_last_name = $full_name[1];
			$last_names = getwayConnect::getwayData("SELECT * FROM translate_of_names where last_name_arm ='{$receiver_last_name}'");
		}
	}
	if( !empty($orderData[0]["sender_name"]) ){
		$full_name_sender =  explode( ' ' , $orderData[0]["sender_name"] );
		if(isset($full_name_sender[0])){
			$sender_first_name = $full_name_sender[0];
			$first_names_sender = getwayConnect::getwayData("SELECT * FROM translate_of_names where first_name_arm ='{$sender_first_name}'");
		}
		if(isset($full_name_sender[1])){
			$sender_last_name = $full_name_sender[1];
			$last_names_sender = getwayConnect::getwayData("SELECT * FROM translate_of_names where last_name_arm ='{$sender_last_name}'");
		}
	}
	if(isset($_REQUEST['for_translate_first_names']) || isset($_REQUEST['for_translate_last_names'])){
		$check_first_name = getwayConnect::getwayData("SELECT * FROM translate_of_names where first_name_arm ='{$_REQUEST['first_name_arm']}'");
		$check_last_name = getwayConnect::getwayData("SELECT * FROM translate_of_names where last_name_arm ='{$_REQUEST['last_name_arm']}'");
		if(!empty($check_first_name)){
			getwayConnect::getwaySend("UPDATE translate_of_names  SET first_name_arm='{$_REQUEST['first_name_arm']}',first_name_rus='{$_REQUEST['first_name_rus']}',first_name_eng='{$_REQUEST['first_name_eng']}',operator_id = '{$userData[0]['id']}', updated_date = '" . date("Y-m-d H:i:s") . "' WHERE id='{$_REQUEST['for_translate_first_names']}'");
		}
		else{
			if(!empty($_REQUEST['first_name_arm'])){
				getwayConnect::getwaySend("INSERT INTO translate_of_names (first_name_arm, first_name_rus,first_name_eng,operator_id,updated_date) VALUES ('{$_REQUEST['first_name_arm']}' , '{$_REQUEST['first_name_rus']}' ,'{$_REQUEST['first_name_eng']}','{$userData[0]['id']}','" . date('Y-m-d H:i:s') . "')");
			}
		}
		if(!empty($check_last_name)){
			getwayConnect::getwaySend("UPDATE translate_of_names  SET last_name_arm='{$_REQUEST['last_name_arm']}',last_name_rus='{$_REQUEST['last_name_rus']}',last_name_eng='{$_REQUEST['last_name_eng']}',operator_id = '{$userData[0]['id']}', updated_date = '" . date("Y-m-d H:i:s") . "' WHERE id='{$_REQUEST['for_translate_last_names']}'");
		}
		else{
			if(!empty($_REQUEST['last_name_arm'])){
				getwayConnect::getwaySend("INSERT INTO translate_of_names (last_name_arm, last_name_rus,last_name_eng,operator_id,updated_date) VALUES ('{$_REQUEST['last_name_arm']}' , '{$_REQUEST['last_name_rus']}' ,'{$_REQUEST['last_name_eng']}','{$userData[0]['id']}','" . date('Y-m-d H:i:s') . "')");
			}
		}
	    echo "<script>window.location.replace(\"order.php?orderId={$orderData[0]['id']}\");</script>";
	    exit;
	}
	// Hrach added
	if(isset($_REQUEST['GetPaymentForOrder']) && $_REQUEST['GetPaymentForOrder']){
	    $country_id = $_REQUEST['country_id'];
	    $result = getwayConnect::getwayData("SELECT region_connect_payment.region_id,region_connect_payment.id,mail_payments.icon,countries.name_am from region_connect_payment LEFT JOIN mail_payments on region_connect_payment.payment_id = mail_payments.id LEFT JOIN countries on region_connect_payment.region_id = countries.id where region_connect_payment.region_id = '" . $country_id . "'");
	    print json_encode($result);die;
	}
	// Hrach added
	if(isset($_REQUEST['GetProductDataById']) && $_REQUEST['GetProductDataById']){
	    $id = $_REQUEST['id'];
	    $result = getwayConnect::getwayData("SELECT * from jos_vm_product LEFT join jos_vm_product_price on jos_vm_product.product_id = jos_vm_product_price.product_id where jos_vm_product.product_id = '" . $id . "'");
	    print json_encode($result);die;
	}
	// Hrach added
	if(isset($_REQUEST['getFirstNameTranslate']) && $_REQUEST['getFirstNameTranslate']){
	    $first_name = $_REQUEST['first_name'];
	    $result_first_name = getwayConnect::getwayData("SELECT * from translate_of_names where first_name_eng = '" . $first_name . "' or first_name_rus ='" . $first_name ."' ");
	    if(count($result_first_name) > 0){
	    	$result_first_name = $result_first_name[0];
	    	$result_first_name['exist'] = true;
	    	print json_encode($result_first_name);die;
	    }
	    return false;
	}
	if(isset($_REQUEST['getLastNameTranslate']) && $_REQUEST['getLastNameTranslate']){
	    $last_name = $_REQUEST['last_name'];
	    $result_last_name = getwayConnect::getwayData("SELECT * from translate_of_names where last_name_rus = '" . $last_name . "' or last_name_eng ='" . $last_name ."' ");
	    if(count($result_last_name) > 0){
	    	$result_last_name = $result_last_name[0];
	    	$result_last_name['exist'] = true;
	    	print json_encode($result_last_name);die;
	    }
	    return false;
	}
	// Hrach added
	if(isset($_REQUEST['GetProductsWithSKUCode']) && $_REQUEST['GetProductsWithSKUCode']){
	    $val = $_REQUEST['val'];
	    $result = getwayConnect::getwayData("SELECT * from jos_vm_product LEFT join jos_vm_product_price on jos_vm_product.product_id = jos_vm_product_price.product_id where product_sku like '%" . $val . "%'");
	    print json_encode($result);die;
	}
	// Hrach added
	if(isset($_REQUEST['getorderlog']) && $_REQUEST['getorderlog']){
	    $order_id = $_REQUEST['order_id'];
	    $table_count = GetOrderTableCount(substr($_REQUEST['order_id'], 0, 2));
	    $result = [];
	    $order_log = getwayConnect::getwayData("SELECT * FROM log_" . $table_count . " LEFT JOIN delivery_status ON log_" . $table_count . ".current_status_id = delivery_status.id left join user on log_" . $table_count . ".operator_id = user.id where order_id='{$order_id}'");
	    $result['order_log'] = $order_log;
	    print json_encode($result);die;
	}
//
$constants = get_defined_constants();
include("functions.orders.php");
$get_lvl = explode(',',$level[0]["user_level"]);
$complain_types = getwayConnect::getwayData("SELECT * FROM complain_types");
if(isset($_REQUEST['orderId'])){
	$complain_for_order = getwayConnect::getwayData("SELECT * FROM complain_of_orders where order_id = '" . $_REQUEST['orderId'] ."'" );
}
	// Added By Dev for xml asop52f41v78x8z5
	$tax_number_hdm_text = '';
	$tax_number_hvhh_text = '';
	$tax_number_postcard_amd_price = '';
	$tax_number_delivery_static_price = '';
	$tax_number_delivery_other_price = '';
	if(isset($_REQUEST['orderId'])){
		$tax_number_of_check_info_show = getwayConnect::getwayData("SELECT * FROM `tax_numbers_of_check` WHERE `order_id` = '" . $_REQUEST['orderId'] ."'");
		if($tax_number_of_check_info_show){
			$tax_number_hdm_text = $tax_number_of_check_info_show[0]['hdm_tax'];
			$tax_number_hvhh_text = $tax_number_of_check_info_show[0]['hvhh_tax'];
			$tax_number_postcard_amd_price = $tax_number_of_check_info_show[0]['postcard_amd_price'];
			$tax_number_delivery_static_price = $tax_number_of_check_info_show[0]['delivery_static_price'];
			$tax_number_delivery_other_price = $tax_number_of_check_info_show[0]['delivery_other_price'];
		}
		$xml_downloaded_history = getwayConnect::getwayData("SELECT * FROM `order_xml_download` WHERE `order_id` = '" . $_REQUEST['orderId'] ."'");
	}
    $desc_tax_accounts = [];
    if ($file = fopen("../../desc_tax_account_2020.txt", "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            $info = explode('|', $line);
            if(count($info) > 1){
                $desc_tax_accounts[] = $info[0];
            }
        }
        fclose($file);
    }
    $delivery_prices = [];
    if ($file = fopen("../../delivery_prices_2020.txt", "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            $info = explode('|', $line);
            if(count($info) > 1){
                $delivery_prices[] = $info[0];
            }
        }
        fclose($file);
    }
    // end asop52f41v78x8z5
	$loged_operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$operator}'");
	if(isset($_GET['orderId'])){
	    if(!isset($_COOKIE['openOperatorOrderId_'.$_REQUEST['orderId']])){
			$orderInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$_REQUEST['orderId']}'");
			$date1 = new DateTime();
			$date2 = new DateTime();
			$diff = $date1->diff($date2);

			$date1 = date("Y-m-d");
			$date2 = $orderInfo[0]['delivery_date'];
			$days = (strtotime($date1) - strtotime($date2)) / (60 * 60 * 24);
			if($days > 0 && $orderInfo[0]['delivery_status'] == 3){
				$table_count = GetOrderTableCount(substr($_REQUEST['orderId'], 0, 2));
				$text = 'Opened';
				getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id,opened) VALUES ('{$_REQUEST["orderId"]}','{$text}','{$loged_operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."',{$orderData[0]["delivery_status"]},'1')");
				$cookie_name = "openOperatorOrderId_" . $_REQUEST['orderId'];
				$cookie_value = $_REQUEST['orderId'];
				setcookie($cookie_name, $cookie_value, time() + (1800), "/");
			}
		}
	}
	if(isset($_REQUEST['orderId'])){
		$greetings_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
		$greetings_card_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$greetings_card_type[0]['id']}' and order_id = '{$_REQUEST["orderId"]}'");
		$notes_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes'");
		$notes_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$notes_type[0]['id']}' and order_id = '{$_REQUEST["orderId"]}'");
		$notes_for_florist_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes_for_florist'");
		$notes_for_florist_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$notes_for_florist_type[0]['id']}' and order_id = '{$_REQUEST["orderId"]}'");
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Apay gateway">
		<meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
		<meta name="author" content="Davit Gabrielyan, Ruben Mnatsakanyan">
		<meta http-equiv="cache-control" content="max-age=0" />
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
		<!-- Bootstrap minified CSS -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
		<!-- Bootstrap optional theme -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
		<link href="<?=$rootF?>/template/slider/jquery.magnify.css" rel="stylesheet">
		<style>
			.hoverLinkEffect:hover{
				cursor:pointer;
				text-decoration: underline;
			}
			.skuCodeSearch:hover{
				cursor:pointer;
			}
			.skuCodeSearch:hover{
				cursor:pointer;
			}
			.ModalForCountryPayments:hover{
				cursor:pointer;
				text-decoration: underline;
			}
			.payment_info:hover{
				cursor:pointer;
				text-decoration: underline;
			}
			.show_log_of_order_action:hover{
				cursor:pointer;
			}
			.show_log_of_order:hover{
				cursor:pointer;
			}
			.show_log_of_order_sender:hover{
				cursor:pointer;
			}
			.img_for_stock_prods:hover{
				cursor:pointer;
			}
			#my-awesome-dropzone{
				padding: 5px;
			    border: 1px dashed #999;
			    border-radius: 5px;
			    margin: 5px;
			    margin-left: 0;
			    width: 100%;
			    font-weight: bold;
			    color:#CCC;
			    text-align: center;
			    cursor: pointer;
			}
			#my-awesome-dropzone:hover{
				color:#AAA;
				border-color:#333;
			}
			.article .text {
				font-size: 13px;
				line-height: 17px;
				/*font-family: arial;*/
			}
			.article .text.short {
				height: 0px;
				overflow: hidden;
			}

			.date-picker-wrapper{
				z-index:99999999;
			}
			.datepicker{
				z-index:99999999;
			}
			hr{
				line-height: 0;
				margin-top:2px;
				margin-bottom:2px;
				border-color:#666;
			}
			#loading{
				position: fixed;
				z-index:9999999999;
				top: -6px;
				left: -8px;
				display: block;
			}
			.time-div{
				background-color: white;
				display: inline-flex;
				border: 1px solid #ccc;
				color: #555;
				margin-left: 2px;
				margin-top: 5px;
			}
			.time-input {
				border: none;
				color: #555;
				text-align: center;
				width: 40px;
			}
			.deleteRelated{
				position: absolute;
				left: 0;
			}
			.dz-image-preview {
				display: inline-block;
			}
			.dz-processing.dz-success  {
				margin-top: 10px !important;
				margin-bottom: 5px !important;
			}
			.dz-processing.dz-success span:not(.preview){
				/* top: -100px !important; */
			}
			.new_price, .new_price_currency {
				color: #AAA;
				position: relative;
				left: 15px;
				top: 5px;
			}
			.multicurrency_values {
				color: #000000;
				position: relative;
				top: 5px;
				font-weight:bolder;
			}
			.total_price_amd{
				font-size:15px;
				font-weight: bolder;
				color:red;
			}
			.total_price_usd{
				font-size:15px;
				font-weight: bolder;
				color:red;
			}
			.related_product_price {
				position: absolute;
				left: 0;
				top: 155px;
				font-weight: bold;
			}
			#receiver_address{
				display: inline-block;
				width: 85%;
			}
			#receiver_entrance, #receiver_floor, #receiver_door_code, #receiver_tribute  {
				display: inline-block;
				width: 14%;
			}
			.receiver_info_icons {
				max-width: 32px;
				max-height: 32px;
				margin-right: 2px;
			}
			.original_price {
				text-decoration: line-through; 
			}
			.discounted_price {
				margin-left: 2px;
				font-size: 12px;
				color: grey;
			}
			.w-size {
				padding-left: 0;
				background: url('./ico/w.png') 1px 20px no-repeat;
				width: 32px;
				background-position-y: 20px;
				background-position-x: 1px;
				right: 75px;
				top: 115px;
			}
			.h-size {
				padding-top: 7px;
				padding-left: 7px;
				background: url('./ico/h.png') no-repeat;
				margin-left: 5px;
				margin-right: 5px;
				top: 110px;
				right: 75px;
			}
			.h-size, .w-size {
				position: relative;
				height: 32px;
				color: #909090;
				font-size: 11px;
				display: inline-block;
			}
			.productNewPrice {
				width: 25%;
				-moz-appearance: textfield;
				float: right;
			}
			.productNewPrice::-webkit-outer-spin-button, .productNewPrice::-webkit-inner-spin-button {
				-webkit-appearance: none;
				margin: 0;
			}
			.productAmdPriceField::-webkit-outer-spin-button, .productAmdPriceField::-webkit-inner-spin-button {
				-webkit-appearance: none;
			}
			.withoutArrowInputs::-webkit-outer-spin-button, .withoutArrowInputs::-webkit-inner-spin-button {
				-webkit-appearance: none;
			}
			.total_amd_field_price::-webkit-outer-spin-button, .total_amd_field_price::-webkit-inner-spin-button {
				-webkit-appearance: none;
			}
			.relatedProduct {
				padding-right: 0px !important;
			}
			.relatedProduct textarea{
				float: right;
			}
			#bonus_info {
				margin-top: 15px;
			}
			.hide_bonus_info {
				display: none;
			}
			.productNewAlternatePrice {
				font-size: 12px;
				float: right;
			}
			.dollar-sign{
				float: right;
			}
			.ip-link{
				color: black;
			}
			.ip-link label {
				text-decoration: underline;
			}
			.hidden-organisation {
				/* display: none; */
			}
			#otherOrders {

			}
			#otherDeliveries {
				max-width: 45%;
				margin: 20px auto;
			}
			#imagelightbox {
				z-index: 500;
			}
			.deliveryCar{
				width: 67px;
				height: 57px;
			}
			/*Added By Hrach*/
			.partnerInfomockupdiv{
				width: 1000px;
	            height: 800px;
	            display: none;
	            overflow-x: scroll;
	            border: 1px solid gray;
	            padding-top: 2%;
	            z-index: 1000;
	            position: absolute;
	            top: 300px;
	            left: 250px;
	            padding:10px;
			}
			.td_for_notification_type{
				text-align: center;
				width:30%;
				padding:10px;
				border:1px dotted black;
			}
			.td_for_notification_value{
				text-align: center;
				width:30%;
				padding:10px;
				width:100px;
				border:1px dotted black;
			}
			.display-none{
				display:none;
			}
			.skuSearchResultMain{
				z-index:9;
				border:3px solid green;
				right:10px;
				width:300px;
				top:0;
				bottom:0;
				height:100%;
				border-radius:7px;
				padding:20px;
				padding-bottom:0; 
				position:fixed;
				overflow:scroll;
			}
			.color_red{
				color:red;
			}
			.color_red:hover{
				color:red;
			}
			.productAmdPriceField{
				float:right;
				width:24%;
				border:2px solid red;
				border-radius: 2px;
			}
			.productUsdPriceField{
				float:left;
				width:15%;
			}
			.usdIconPricePart{
				float:left;
				margin-left:20px;
			}
			.amdIconPricePart{
				float:right;
			}
			.other_product_price{
				width:60px;
				float: left;
			}
			.productAddedTaxAccount{
				width:70%;
				float:right;
			}
			.customFormControl{
				padding:3px 6px!important;
				height:28px!important;
			}
			.oneRowFormControl{
				width:120px!important;
			}
			.showEachPrice{
				font-weight:bolder;
			}
		</style>
		<title><?=$titleHelp;?> <?=(defined('RG_ORDER_SYSTEM')) ?  RG_ORDER_SYSTEM : 'RG_ORDER_SYSTEM';?></title>
		<?php

		if($actioned == 1)
		{
			echo "<script>alert(\"".((defined('SAVED')) ?  SAVED : 'SAVED')."\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$postId}\");</script>";
			exit;
			
		}else if($actioned == 2){
			echo "<script>alert(\"".((defined('SAVED')) ?  SAVED : 'SAVED')."\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$_REQUEST["orderId"]}\");</script>";
			exit;
		}else if($actioned == 4){
			echo "<script>alert(\"FAIL TO SAVE\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$_REQUEST["orderId"]}\");</script>";
			exit;
		}
		?>

		<!-- Boostrsap searchable select -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">

	</head>
	<body>
		<div class='display-none skuSearchResultMain'>
			<div class='hideSkuSearchResultPart'><i title='close' class="glyphicon glyphicon-remove closeSkuSearchPart"></i></div>
			<div class='skuCodeSearchResult'></div>
		</div>
<form method="post" action="" id="new_order_form" class="form-horizontal" role="form" onsubmit="return checkAll(this);">
    <div align="center" style="margin:5px;">
		<p><span id="my_timer" style="color: #f00; font-size: 150%; font-weight: bold;">02:00:00</span></p>
    </div>
    <div class='row' style='max-width: 1000px;margin:auto'>
		<div class='col-md-6'>
			<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
					<p><?php if($orderId != null){ ?><strong><?=(defined('ORDER_NUMBER')) ?  ORDER_NUMBER : 'ORDER_NUMBER';?>: <?=$orderId?></strong><?php }?>
			        <img src="/template/images/log_icon.png" style='width:25px;height:25px' data-order-id="<?=$orderId?>" class='show_log_of_order_action' ></p>
			        <!-- <img src="/template/images/delivered.png" style='width:88px;height:66px;cursor:pointer' title="Առաքված" onclick="window.open('mail/?mails=<?=$orderData[0]['id']?>&content_id=5','popUpWindow','height=600,width=970,resizable=yes,scrollbars=yes,toolbar=yes');"> -->
			        <?php if($created != ''){?><p><div style="margin:5px;"><strong><?=(defined('REGISTERED')) ?  ucfirst(REGISTERED) : 'REGISTERED';?> <?=$created?></strong></div></p><?php }?>
			<?php }?>
		</div>
		<div class='col-md-6' style='padding-left:40px'>
			<p>
				<div style="margin:5px;">
					<strong>
						<?=(defined('EDITOR_OF_THIS_ORDER')) ?  ucfirst(EDITOR_OF_THIS_ORDER) : 'EDITOR_OF_THIS_ORDER';?> 
						<?php
				            $types = ['jpeg', 'png', 'JPEG', 'jpg'];
				            foreach($types as $type){
				                if(file_exists('../user_images/' . $userData[0]['uid']. ".". $type)){
				                ?>
				                    <div class="btn-group" role="group" aria-label="...">
				                        <img style='margin-left:5px;margin-right:5px;border-radius:50%' src="<?= '../user_images/' . $userData[0]['uid']. '.'. $type ?>" alt="" width="40" height="40" class="pull-right">
				                    </div>
				                <?php
				                }
				            }
		        		?>
		        		<?=$userData[0]['full_name_am']?>
					</strong>
				</div>
			</p>
			<p><div style="margin:5px;"><strong><span id="foundCount"></span></strong></div></p>
		</div>
	</div>
    <?php if($orderId != null){
		$edOperator = checkStatus($orderId);
		$edOperator["o"] = (isset($edOperator["o"])) ? $edOperator["o"] : "";
	?>
	<?php if($operator != $edOperator["o"]){
		$edOperatorInfo = getwayConnect::getwayData("SELECT full_name_am FROM user where username = '" . $edOperator["o"] . "'");
		?>
			<div align="center" style="margin:5px;font-size:20px">
				<img src="<?=$rootF?>/template/icons/editing.png"/>
				<?=(defined('EDITOR_AT_THE_MOMENT')) ?  EDITOR_AT_THE_MOMENT : 'EDITOR_AT_THE_MOMENT';?>՝ 
				<strong style="color:red;display:none" id="editorOperator"> <?=ucfirst($edOperator["o"]);?>
				</strong>
				<strong style="color:red;"> <?=ucfirst($edOperatorInfo[0]['full_name_am']);?>
				</strong>
			</div>
	<?php 
		}
	}?>
<div align="center" class='blur_effect_div' style="margin:5px;">
<table style="border:0px none; max-width: 1000px;">
    <tbody>
        <tr>
            <td>
            <!--tablestart block 1-->
            <div style="border: 3px solid #F0AD4E; width:auto; height:auto; border-radius:7px; padding:20px; padding-bottom:0; ">
            <table border="0">
                <tbody>
                    <tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(',',$level[0]["user_level"])))) ? ((defined('DELIVERY_DAY')) ?  DELIVERY_DAY : 'DELIVERY_DAY') : ((defined('DRIVEING_DAY')) ?  DRIVEING_DAY : 'DRIVEING_DAY');?>: *</label></td>
                        <td><input value="<?=(isset($orderData[0]["delivery_date"])) ? $orderData[0]["delivery_date"] : ""?>" type="text" name="delivery_date" class="required form-control datepicker hasDatepicker" id="delivery_date" addon="date" required></td>
                    </tr>
                    <tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(',',$level[0]["user_level"])))) ? ((defined('DELIVERY_TIME')) ?  DELIVERY_TIME : 'DELIVERY_TIME') : ((defined('DRIVEING_TIME')) ?  DRIVEING_TIME : 'DRIVEING_TIME');?>: </label></td>
                        <td>
                        <?php 
                        	if(empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))){
                        ?>
                        <select onclick="" name="delivery_time" class="form-control" id="delivery_time" style="width: 100px; float: left;">
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
                                <?php
								$active = (isset($orderData[0]["delivery_time"])) ? $orderData[0]["delivery_time"] : false;
								echo page::buildOptions("delivery_time",$active);
								?>
                            </select>
                            <?php
                            
                            }else{
                            	echo "<label for=\"time_manual\" style=\"width: 45px;display: inline-block;\">".((defined('START')) ?  START : 'START').":</label>";
                            }
                            ?>

                            <input value="<?=(isset($orderData[0]["delivery_time_manual"])) ? $orderData[0]["delivery_time_manual"] : ""?>" type="hidden" name="delivery_time_manual"  id="time_manual" class="form-control" style="width: 110px;display: inline-block;">
							<div class="time-div">
								<input type="number" min="0" max="23" class="time-input delivery_time_manual delivery_time_manual_hour" data-target="time_manual" data-sibling=".delivery_time_manual_mins" value="<?= (isset($orderData[0]["delivery_time_manual"]) && $orderData[0]["delivery_time_manual"] != '') ? explode(':', $orderData[0]["delivery_time_manual"])[0] : "" ?>">:
								<input type="number" min="0" max="59" class="time-input delivery_time_manual delivery_time_manual_mins" data-target="time_manual" data-sibling=".delivery_time_manual_hour" value="<?=(isset($orderData[0]["delivery_time_manual"]) && $orderData[0]["delivery_time_manual"] != '') ? explode(':', $orderData[0]["delivery_time_manual"])[1] : "" ?>">
							</div>
                            <?php 
                        	// if(!empty(array_intersect(array(89),explode(',',$level[0]["user_level"])))){
                        		?>
                        		<label for="travel_time_end">- <input value="<?=(isset($orderData[0]["travel_time_end"])) ? $orderData[0]["travel_time_end"] : ""?>" type="hidden" name="travel_time_end"  id="travel_time_end" class="form-control" style="width: 110px; display: inline-block;"></label>
								<div class="time-div">
								<input type="number" min="0" max="23" class="time-input travel_time_end travel_time_end_hour" data-target="travel_time_end" data-sibling=".travel_time_end_mins" value="<?= (isset($orderData[0]["travel_time_end"]) && $orderData[0]["travel_time_end"] != '') ? explode(':', $orderData[0]["travel_time_end"])[0] : "" ?>">:
								<input type="number" min="0" max="59" class="time-input travel_time_end travel_time_end_mins" data-target="travel_time_end" data-sibling=".travel_time_end_hour" value="<?=(isset($orderData[0]["travel_time_end"]) && $orderData[0]["travel_time_end"] != '') ? explode(':', $orderData[0]["travel_time_end"])[1] : "" ?>">
							</div>
                        	<?php
                        	// }
                        	?>
                        </td>
                    </tr>
					
					
					
					
					
					
					
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                    <tr>
                        <td><label><?=(defined('COUNTRY')) ?  COUNTRY : 'COUNTRY';?>: *</label></td>
                        <td><select name="delivery_region" id="b_region" class="form-control required delivery_region" onchange="buildRegions(1,this,<?=(isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : "null"?>)" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									$active = (isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : false;
									echo page::buildOptions("delivery_region",$active);
								?>
                            </select>
                        </td>
                    </tr>
					<?php }else {?>
					<input type="hidden" name="delivery_region" id="b_region" value="1">
					<?php } ?>
					
					
					
					
					
					
                    <tr>
                        <td><label class='translateNameToArmenianReceiverName hoverLinkEffect'><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ?  ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('ORDER_TO_DRIVE')) ?  ORDER_TO_DRIVE : 'ORDER_TO_DRIVE');?>: </label></td>
                        <td>
							<input value="<?=(isset($orderData[0]["receiver_name"])) ? $orderData[0]["receiver_name"] : ""?>" type="text" class="form-control" name="receiver_name" id="receiver_name">
							<?php
								if(isset($full_name)){
									?>
										<table class="table-bordered">
											<tr>
												<td style='padding:5px'><span class='show_log_of_order'><?=(isset($first_names[0]['first_name_rus'])?  $first_names[0]['first_name_rus'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order'><?=(isset($first_names[0]['first_name_eng'])?  $first_names[0]['first_name_eng'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order'><?=(isset($last_names[0]['last_name_rus'])?  $last_names[0]['last_name_rus'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order'><?=(isset($last_names[0]['last_name_eng'])?  $last_names[0]['last_name_eng'] : '')?></span></td>
											</tr>
											<tr>
												<td style='padding:5px;color:red'><?=(!isset($first_names[0]['first_name_rus']) || empty($first_names[0]['first_name_rus'])? "<span class='show_log_of_order'>переводить</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($first_names[0]['first_name_eng']) || empty($first_names[0]['first_name_eng'])? "<span class='show_log_of_order'>translate</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($last_names[0]['last_name_rus']) || empty($last_names[0]['last_name_rus'])? "<span class='show_log_of_order'>переводить</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($last_names[0]['last_name_eng']) || empty($last_names[0]['last_name_eng'])? "<span class='show_log_of_order'>translate</span>" : '')?></td>
											</tr>
										</table>
									<?php
								}
							?>
						</td>
                    </tr>

					<tr>
						<td><label>Search By SKU code</label></td>
						<td>
							<input type='text' style='width:180px;float:left' class='form-control inputskuCodeSearch' placeholder='SKU Code'>
							<img src="/template/icons/search.png" class='skuCodeSearch' style='height:30px'>
						</td>
					</tr>
					<tr>
						<td><label for="parent_categories">Category</label></td>
						<td>
							<div class="col-sm-6" style="padding-left: 0">
								<select id="parent_categories" class="form-control">
									<option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
									<?php 
										foreach($parent_categories as $parent_category){
											echo "<option value='".$parent_category['category_id']."'>".$parent_category['category_name']."</option>";
										}
									?>
								</select>
							</div>
							<div class="col-sm-6" style="padding-left: 0; padding-right: 0;">
								<select id="related_category" class="form-control hidden">
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<div class="hidden col-sm-12" id="relatedProductsDiv" style="padding-left: 0">
								<select id="relatedProducts" class="selectpicker" style="margin: 10px;"  data-live-search="true" multiple>
									
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<div id="selectedProducts">
								<?php
								// Added By Dev for xml asop52f41v78x8z5
								$option_select_delivery_price = '';
								foreach($delivery_prices as $key=>$delivery_price){
									$key = $key+1;
									if($tax_number_delivery_static_price == $key){
										$option_select_delivery_price.='<option value="' . $key . '" selected>' . $delivery_price . '</option>';
									}
									else{
										$option_select_delivery_price.='<option value="' . $key . '">' . $delivery_price . '</option>';
									}
								}
								$option_select_upload = '';
								foreach($desc_tax_accounts as $key=>$desc_tax_account){
									$key = $key+1;
									$option_select_upload.='<option value="' . $key . '">' . $desc_tax_account . '</option>';
								}
								// end asop52f41v78x8z5
								if(isset($orderRelatedProducts) && !empty($orderRelatedProducts)){
									$related_name = '';
									$count_product = 0;
									foreach($orderRelatedProducts as $orderRelatedProduct){
										// Added Hrach Dev asop52f41v78x8z5
										$taxInfo = getTaxInfo($orderId,$orderRelatedProduct[0]['product_id']);
										if(!empty($taxInfo)){
											$taxInfo = $taxInfo[0];
										}
										else{
											$taxInfo['tax_account_id'] = '';
											$taxInfo['price_amd'] = '';
											$taxInfo['quantity'] = '';
										}
										$option_select = '';
										foreach($desc_tax_accounts as $key=>$desc_tax_account){
											$key = $key+1;
											if($taxInfo['tax_account_id'] == $key){
												$option_select.='<option value="' . $key . '" selected>' . $desc_tax_account . '</option>';
											}
											else{
												$option_select.='<option value="' . $key . '">' . $desc_tax_account . '</option>';
											}
										}
										$dprice = number_format($orderRelatedProduct[0]['product_price'] - $orderRelatedProduct[0]['product_price'] * ( $orderRelatedProduct[0]['product_discount_id'] /100 ), 2);
										if(isset($orderRelatedProduct) && !empty($orderRelatedProduct)){
											echo '<div class="col-sm-12 relatedProduct" id="relatedPrd'.$orderRelatedProduct[0]['product_id'].'" data-price="'.$dprice.'">';
											// echo '<span style="display: inline-block;">';
											echo '<a data-magnify="gallery" data-caption="'.$orderRelatedProduct[0]['product_desc'].'" href="./jos_product_images/'.$orderRelatedProduct[0]['product_thumb_image'].'"> <img src="./jos_product_images/'.$orderRelatedProduct[0]['product_thumb_image'].'" alt="" style="max-width: 115px; float: left;" title="'.$orderRelatedProduct[0]['product_desc'].'"></a>';
											echo '<button data-dz-remove="" class="btn btn-danger btn-xs deleteRelated" data-price="'.$dprice.'"><i class="glyphicon glyphicon-remove"></i></button>';
											// echo '</span>';
											if($orderRelatedProduct[0]['product_width'] > 0){
												echo '<div class="w-size">'.number_format($orderRelatedProduct[0]['product_width'], 2).'</div>';
											}
											if($orderRelatedProduct[0]['product_height'] > 0){
												echo '<div class="h-size">'.number_format($orderRelatedProduct[0]['product_height'], 2).'</div>';
											}
											echo '<input type="hidden" name="relatedProduct[]" value="'.$orderRelatedProduct[0]['product_id'].'">';
											if($orderRelatedProduct[0]['product_discount_id'] > 0){
												echo '<span class="related_product_price"><span class="original_price">$ '.number_format($orderRelatedProduct[0]['product_price'], 2).'</span> $<span class="discounted_price">'.$dprice.'</span>('.$orderRelatedProduct[0]['product_discount_id'].'%)</span>';
											} else {
												echo '<span class="related_product_price">$ '.number_format($orderRelatedProduct[0]['product_price'], 2).'</span>';
											}
											echo '<span><br>';
											echo '<span class="dollar-sign usdIconPricePart">$</span><input type="number" step="0.01" name="productNewPrice['.$orderRelatedProduct[0]['product_id'].']" data-product-id="' . $orderRelatedProduct[0]['product_id'] .'"  class="productNewPrice productUsdPriceField usdShowField" value="'.$orderRelatedProduct[0]['related_prod_price'].'">';
											// Added By Dev for xml asop52f41v78x8z5
											echo '<input type="number" step="0.01" name="productAmdPrice['.$orderRelatedProduct[0]['product_id'].']" class="productAmdPriceField showForXML" data-prod-id="'.$orderRelatedProduct[0]['product_id'].'" value="' . $taxInfo['price_amd'] . '"><span class="amdIconPricePart showForXML"><img src="/template/icons/currency/3.png" style="height:15px" ></span>';
											echo '<span class="convertedPriceCurrencys_'.$orderRelatedProduct[0]['product_id'].'"></span>';
											// end asop52f41v78x8z5
											echo '<br>';
											echo '<span class="productNewAlternatePrice"></span>';
											echo '<textarea rows="2" cols="18" name="related_name['.$orderRelatedProduct[0]['product_id'].']" title="'.$orderRelatedProduct[0]['product_name']. ' - '. $orderRelatedProduct[0]['product_desc'] .'">'.$orderRelatedProduct[0]['related_name'].'</textarea>';
											echo '<textarea rows="4" cols="19" name="short_desc['.$orderRelatedProduct[0]['product_id'].']">'.$orderRelatedProduct[0]['short_desc']. '</textarea>';
											echo '</span>';
											// Added By Dev for xml asop52f41v78x8z5
											echo '<div class="col-md-12" style="float:right"><input type="hidden" name="productIdCon['.$orderRelatedProduct[0]['product_id'].']" value="' . $orderRelatedProduct[0]['product_id'] . '"><span class="showEachPrice showForXML" data-prod-id="' . $orderRelatedProduct[0]['product_id'] . '"></span> <input type="number" class="productQuantityAdded productQuantityField showForXML withoutArrowInputs" data-prod-id="'.$orderRelatedProduct[0]['product_id'].'" data-prod-id="'.$orderRelatedProduct[0]['product_id'].'" value="' . $taxInfo['quantity'] . '" name="productQuantity['.$orderRelatedProduct[0]['product_id'].']" style="width:18%" placeholder="Հատ"><select class="productAddedTaxAccount showForXML form-control" style="padding: 3px 6px;width:150px;font-size: 12px;height:30px" name="productTaxAccount['.$orderRelatedProduct[0]['product_id'].']">' . $option_select . '</select></div>';
											// end asop52f41v78x8z5
											echo "<div class='col-md-12'><img data-prod-id='". $orderRelatedProduct[0]['product_id'] . "' class='img_for_stock_prods' style='height:30px' src='http://new.regard-group.ru/template/icons/baxadrutyun.jpg' ><div class='div_for_stock_prods" . $orderRelatedProduct[0]['product_id']  ." hidden col-md-12'></div></div>";
											echo '</div>';
											$related_name.= $orderRelatedProduct[0]['related_name'] . " , ";
											$count_product++;
										}
									}
								}
								
								?>
							</div>
							<div id="newlySelectedProducts">
							</div>
						</td>
					</tr>
                    <tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ?  ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('ORDERED_TOURISTS_TO_DRIVE')) ?  ORDERED_TOURISTS_TO_DRIVE : 'ORDERED_TOURISTS_TO_DRIVE');?></label></td>
                        <td><textarea class="form-control" name="product" id="product" cols="20" rows="4"><?=(isset($orderData[0]["product"])) ? $orderData[0]["product"] : ""?></textarea>
					</td>
					<tr>
                        <td><label><?=(defined('P_IMAGE')) ? P_IMAGE : 'P_IMAGE';?></label></td>
                        <td><div id="my-awesome-dropzone">UPLOAD HERE</div></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><div class="table table-striped" class="files" id="previews">

							  <div id="preview_template" class="file-row">
							    <!-- This is used as the file preview template -->
							    <div>
							        <span class="preview" style="position: relative;width: 90px;height: 100px;display: inline-block;vertical-align: text-bottom;">

							        <img data-dz-thumbnail style="position: absolute; top: ;left:0;z-index: -1;" width="100px" height="100px" />
										<div class="progress progress-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 5px; margin-bottom: 0px !important;" data-dz-uploadprogressBar>
											<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress>
											</div>
										</div>
										<button data-dz-remove class="btn btn-danger btn-xs delete">
								        	<i class="glyphicon glyphicon-remove"></i>
								      	</button>
    									<div style='margin-top:140px' class="showEachPrice text-right" data-dz-amdeachprice></div>
								      <div class="dz-filename" style="display: none;"><span data-dz-name></span></div>



							        </span>

							        <span style="position: relative; max-width: 205px; float: right;text-align: right;margin-top: 15px;">
										<span class="dollar-sign usdIconPricePart">$</span>
										<input type="number" class="other_product_price usdShowField usdUploadedPrice" placeholder="USD" data-dz-productprice>
										<input type="number" step="0.01" data-dz-amdprice class="productAmdPriceField showForXML" style='width:33%' ><span class="amdIconPricePart showForXML"><img src="/template/icons/currency/3.png" style="height:15px" ></span><br><br>
										<span class="productExchangePart" data-dz-productexchange></span>
										<input type="text" data-dz-filenote placeholder="Այլ ապրանքի անուն" />
										<textarea cols="22" rows="4" data-dz-productdesc placeholder="Այլ ապրանքի նկարագրություն"></textarea>
										<input type="hidden" data-dz-fileid/>
    									<!-- Added By Dev for xml asop52f41v78x8z5 -->
										<input type="number" class="uploadQUanitityClass productQuantityField showForXML withoutArrowInputs" data-dz-productquantity style="width:25%" placeholder="Հատ">
										<select class='addedProductTaxId uploadProductTaxId showForXML' style="padding: 3px 6px;width:54%;font-size: 12px;height:30px" data-dz-producttaxid><?php echo $option_select_upload ?></select>
										<!-- end asop52f41v78x8z5 -->
							        </span>
							    </div>
							    <div>
							        <strong class="error text-danger" data-dz-errormessage></strong>
							    </div>
							  </div>

							</div>
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=(defined('POSTCARD')) ? POSTCARD : 'POSTCARD';?>: </label></td>
                        <td>
							<input type="text" class="form-control postcard_amd_price" placeholder='Գին' value="<?php echo $tax_number_postcard_amd_price ?>" name="postcard_amd_price" style="width: 62px; float: left;">
							<select class='form-control delivery_static_price' name='delivery_static_price' style='width:153px;float:left;font-size: 13px;'>
								<option value="0">Առաքման արժեք</option>
								<?php echo $option_select_delivery_price ?>
							</select>
							<input type="text" class="form-control delivery_other_price" placeholder='Այլ Գին' value="<?php echo $tax_number_delivery_other_price ?>" name="delivery_other_price" style="width: 70px; float: left;">
                        </td>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                        <td><label><?=(defined('ORDER_PRICE')) ? ORDER_PRICE : 'ORDER_PRICE';?>: *</label></td>
                        <td><br><input value="<?=(isset($orderData[0]["price"])) ? $orderData[0]["price"] : ""?>" oninput="check(this,1)" type="text" name="price" id="price" class="required form-control customFormControl" style="width:65px; float:left;" required>
                            <select name="currency" id="currency" class="form-control required customFormControl" style="width:75px; float:left; display:inline-block" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									$active = (isset($orderData[0]["currency"])) ? $orderData[0]["currency"] : false;
									
									echo page::buildOptions("currency",$active);
								?>
                            </select>
                            <!-- <input type='text' class='form-control calculatedProcentOfPrice customFormControl' disabled placeholder='30%' style='width:65px;float:left'> -->
                            <img src="/template/images/PayNow.png" style='width:60px;float:left;height:40px;cursor:pointer' title="Վճարել" onclick="window.open('mail/?mails=<?=$orderData[0]['id']?>&content_id=1','popUpWindow','height=600,width=970,resizable=yes,scrollbars=yes,toolbar=yes');">
                            <span class='showForXML' style='float:left;font-size:20px'>Դ</span>
                            <input type='number' class='form-control showForXML customFormControl total_amd_field_price' value="<?php echo $orderData[0]["total_price_amd"] ?>" name='total_price_amd' style='width:75px;float:left;border:1px solid red'>
							<!-- <span class="new_price"></span> -->
							<!-- <span class="new_price_currency">USD</span> -->
							<br>
							<br>
							<br>
							<span class='total_price_usd'></span>
							<span class='multicurrency_values' ></span>
							<span class='total_price_amd'></span>
							<br>
							<br>
                        </td>
                    </tr>
					
					
					
					
					
					
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                    <tr>
                        <td><label><?=(defined('RECEIVER_STATE')) ? RECEIVER_STATE : 'RECEIVER_STATE';?>: </label></td>
                        <td><select <?=(isset($orderData[0]["receiver_subregion"])) ? "": "disabled=\"disabled\""?> name="receiver_subregion" id="b_subregion" class="form-control" onchange="buildRegions(2,this);">
                                <option value="0"><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									if(isset($orderData[0]["receiver_subregion"]))
									{
										$active = $orderData[0]["receiver_subregion"];
										echo page::buildOptions("delivery_subregion",$active,$orderData[0]["delivery_region"]);
									}
								?>
                            </select>
                        </td>
					</tr>
					<tr>
                        <td><label><?=(defined('RECEIVER_STREET')) ? RECEIVER_STREET : 'RECEIVER_STREET';?>: </label></td>
                        <td>
				<!--onchange="ChangeSelectedValue(this.selectedIndex.text);"-->
                            <select <?=(isset($orderData[0]["receiver_street"])) ? "": "disabled=\"disabled\"";?> name="receiver_street" id="b_street" class="selectpicker form-control" style="margin: 10px;"  data-live-search="true" >
                                <option value="E-1"></option>
								<?php
									if(isset($orderData[0]["receiver_street"]))
									{
										$active = $orderData[0]["receiver_street"] ;
										$active = (!page::isStreetCode($active)) ? page::getStreetCodeByName($active) : $active;
										echo page::buildOptions("delivery_street",$active,$orderData[0]["receiver_subregion"]);
									}
								?>
                            </select>
                        </td>
                    </tr>
					<tr>
						<td></td>
						<td><span class="street_info">
							<?php
								if(isset($orderData[0]["receiver_street"])){
									$street_info = getwayConnect::getwayData("SELECT * from delivery_street where code = '{$orderData[0]['receiver_street']}'");
									if(isset($street_info[0]['zone'])){
										$zone = getwayConnect::getwayData("SELECT * FROM delivery_zone where id='{$street_info[0]['zone']}'");
									}
									if(isset($street_info) && !empty($street_info[0])){
										echo 'Zone '. $street_info[0]['zone']. ', ';
										if(isset($zone) && !empty($zone[0])){
											echo "price ". $zone[0]['price'].', ';
										}
										echo 'KM: ' . $street_info[0]['distance'] . ', ';
										echo 'DT: ' . $street_info[0]['delivery_time'] . ', ';
										if(isset($street_info[0]['wiki_url']) && $street_info[0]['wiki_url'] != ''){
											echo ' <a target="blank" href="'.$street_info[0]['wiki_url'].'">Wiki </a>, ';
										}
										if(isset($street_info[0]['coordinates']) && $street_info[0]['coordinates'] != ''){
											echo ' <a target="blank" href="https://www.google.com/maps/dir/Yervand+Kochar+Street,+Yerevan,+Armenia/'.$street_info[0]['coordinates'].'/@40.2000815,44.4699913,12z/data=!3m1!4b1!4m12!4m11!1m5!1m1!1s0x406abcf595178223:0xf074b89337f1809!2m2!1d44.5177361!2d40.1713366!1m3!2m2!1d45.18!2d40.38!3e0">MAP </a> ';
										}
									}
								}
							?>
						</span>
						</td>
					</tr>
					<tr class="organisation-type-tr <?= isset($orderData[0]['organisation']) ? '': 'hidden-organisation' ?>">
						<td><label for="organisation_types"><?=(defined('ORGANISATION_TYPES')) ? ORGANISATION_TYPES : 'ORGANISATION_TYPES';?>:</label></td>
						<td>
							<select id="organisation_types" class="form-control">
								<option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php 

									$organisationTypes = getwayConnect::getwayData("SELECT * FROM organisation_types where active = 1");
									foreach($organisationTypes as $organisationType){
										echo "<option value='".$organisationType['id'] ."'";
										if(isset($organisation) && !empty($organisation) && $organisation['type'] == $organisationType['id']){
											echo "selected='selected'";
										}
										echo ">".$organisationType['name']."</option>";
									}
								?>
							</select>
						</td>
					</tr>

					<tr class="organisations-tr <?= isset($orderData[0]['organisation']) ? '': 'hidden-organisation' ?>">
						<td><label for="organisations"><?=(defined('ORGANISATIONS')) ? ORGANISATIONS : 'ORGANISATIONS';?>:</label></td>
						<td>
							<select id="organisations" name="organisation" class="form-control">
								<option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php 
									if(isset($organisation)){
										$allorganisations = getwayConnect::getwayData("SELECT * FROM organisations where `type`='{$organisation['type']}' && `region`='{$organisation['region']}' and active = 1");
										if(count($allorganisations) > 0){
											foreach($allorganisations as $allorganisation){
												echo "<option value='".$allorganisation['id']."'";
												echo "data-street='".$allorganisation['street']."'";
												echo "data-address='".$allorganisation['address']."'";
												echo "data-entrance='".$allorganisation['entrance']."'";
												echo "data-floor='".$allorganisation['floor']."'";
												echo "data-door_code='".$allorganisation['door_code']."'";
												if($allorganisation['id'] == $orderData[0]['organisation']){
													echo "selected='selected'";
												}
												echo ">".$allorganisation['name_am']."</option>";
											}
										}
									}
								
								?>
							</select>
						</td>
					</tr>
				<?php }else {?>
					<input type="hidden" name="receiver_subregion" id="b_subregion" value="0">
					<input type="hidden" name="receiver_street" id="b_street" value="0">
				<?php } ?>
					
                    <tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_HOME')) ? RECEIVER_HOME : 'RECEIVER_HOME') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label></td>
                        <td><img src="./ico/building.jpg" class='receiver_info_icons' alt="Address"><input value="<?=(isset($orderData[0]["receiver_address"])) ? str_replace("\"","'",$orderData[0]["receiver_address"]) : ""?>" type="text" class=" form-control" name="receiver_address" id="receiver_address"></td>
                    </tr>
					<?php if(!in_array($userData[0]['id'], $travel_operators)){ ?>
						<tr>
							<td>
								<label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_FLOOR')) ? RECEIVER_FLOOR : 'RECEIVER_FLOOR') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label>
								<label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_TRIBUTE')) ? RECEIVER_TRIBUTE : 'RECEIVER_TRIBUTE') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label>
								<label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_ENTRANCE')) ? RECEIVER_ENTRANCE : 'RECEIVER_ENTRANCE') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label>
								<label><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_DOOR_CODE')) ? RECEIVER_DOOR_CODE : 'RECEIVER_DOOR_CODE') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label>
							</td>
							<td>
								<img src="./ico/appartment.jpg" class='receiver_info_icons' title="Բնակարան" alt="Apartment"><input value="<?=(isset($orderData[0]["receiver_floor"])) ? str_replace("\"","'",$orderData[0]["receiver_floor"]) : ""?>" style='padding: 4px' type="text" class=" form-control" name="receiver_floor" placeholder="Բնակարան" id="receiver_floor">
								<img src="./ico/stairs.jpg" class='receiver_info_icons' title="Հարկ" alt="Floor"><input value="<?=(isset($orderData[0]["receiver_tribute"])) ? str_replace("\"","'",$orderData[0]["receiver_tribute"]) : ""?>" type="text" class=" form-control" style='padding: 4px' name="receiver_tribute" placeholder="Հարկ" id="receiver_tribute">
								<img src="./ico/entrance.jpg" class='receiver_info_icons' title="Մուտք" alt="Entrance"><input style='padding: 4px' value="<?=(isset($orderData[0]["receiver_entrance"])) ? str_replace("\"","'",$orderData[0]["receiver_entrance"]) : ""?>" type="text" class=" form-control" name="receiver_entrance" placeholder="Մուտք" id="receiver_entrance">
								<img src="./ico/door-lock.jpg" class='receiver_info_icons' title="Կոդ" alt="Door Code"><input style='padding: 4px' value="<?=(isset($orderData[0]["receiver_door_code"])) ? str_replace("\"","'",$orderData[0]["receiver_door_code"]) : ""?>" type="text" class=" form-control" name="receiver_door_code" placeholder="Կոդ" id="receiver_door_code">
							</td>
						</tr>
					<?php }?>
					<?php if(isset($orderData[0]['right_address']) && $orderData[0]['right_address'] != ''){ ?>
						<tr>
							<td>
								<label style="color: red;"><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('NOTES_BY_DRIVER')) ? NOTES_BY_DRIVER : 'NOTES_BY_DRIVER') : ((defined('WHER_TO_DRIVE')) ? WHER_TO_DRIVE : 'WHER_TO_DRIVE');?>:</label>
							</td>
							<td>
								<span><?= $orderData[0]['right_address'] ?></span>
							</td>
						</tr>
					<?php } ?>
                    <tr>
                        <td>
							<label>
								<?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('RECEIVER_PHONE')) ? RECEIVER_PHONE : 'RECEIVER_PHONE' ): ((defined('TOURIST_PHONE')) ? TOURIST_PHONE : 'TOURIST_PHONE');?>:
							</label>
                        </td>
                        <td>
							<input value="<?=(isset($orderData[0]["receiver_phone"])) ? $orderData[0]["receiver_phone"] : ""?>" type="text" pattern="(^\++[0-9]{6,})(,?\s?\+?[0-9]{6,})*" oninvalid="this.setCustomValidity('Only numeric, comma, space, + allowed, must be min 6 numbers')" oninput="this.setCustomValidity('')" class="form-control" name="receiver_phone" id="receiver_phone">
							<span style='font-size:11px' class='color_red receiverPhoneMsg'></span>
                        </td>
                    </tr>
					<tr>
						<td><label for="deliverer"><?=(defined('DELIVERER')) ? DELIVERER : 'DELIVERER';?>*: </label></td>
	                    <td>
	                    	<select name="deliverer" id="deliverer" class="form-control" onchange="select_delivery_type($(this))" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									$active = (isset($orderData[0]["deliverer"])) ? $orderData[0]["deliverer"] : false;
					
									if($drivers_data = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` ORDER BY `ordering` ASC",PDO::FETCH_ASSOC)){
										foreach ($drivers_data as $dkey => $dvalue) {
                                            $dvalue['name'] =  (isset($constants[$dvalue['name']])) ? $constants[$dvalue['name']] : $dvalue['name'] ;
											$selected = ($dvalue['id'] == $active) ? "selected" : '';
											echo "<option value=\"{$dvalue['id']}\" data-car=\"{$dvalue['delivery_type_id']}\" {$selected}>{$dvalue['name']}</option>";
										}
									}
								?>
                            </select>
	                    </td>
					</tr>
					
                    <tr>
                        <td><label><?=(defined('DELIVERY_TYPE'))? DELIVERY_TYPE :'DELIVERY_TYPE';?>: </label></td>
                        <td>
                            <?php
                            $active = (isset($orderData[0]["delivery_type"])) ?$orderData[0]["delivery_type"] : false;

                            if($drivers_data = getwayConnect::getwayData("SELECT * FROM `delivery_drivers` WHERE `active` = 1 ORDER BY `ordering` ASC",PDO::FETCH_ASSOC)){
                                foreach ($drivers_data as $dkey => $dvalue) {
                                    $selected = ($dvalue['id'] == $active) ? "checked" : '';
                                    $dvalue['name'] =  (isset($constants[$dvalue['name']])) ? $constants[$dvalue['name']]: $dvalue['name'] ;
                                    ?>
                                    <label id="<?=$dvalue['name'];?>" style="padding:2px;">
                                        <input type="radio" name="delivery_type" value="<?=$dvalue['id'];?>" id="<?=$dvalue['name'];?>" <?=$selected?>>
                                        <img src="<?=$rootF?>/template/icons/deliver/<?=$dvalue['id'];?>.png" width="67px" title="<?=(defined($dvalue['name']))? constant($dvalue['name']) : $dvalue['name'] . ' ' . $dvalue['car_number']; ?>" >
                                    </label>
                                    <?php
                                }
                            }
                            ?>

							
                        </td>
                    </tr>
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>

					<?php } else {?>
					  <tr>
                        <td>&nbsp;<input type="hidden" name="greetings_card" id="greetings_card" value=""> </td>
						 <td></td>
                    </tr>
					<?php } ?>
					<tr>
						<td><label><?=(defined('COMPLAIN_TYPE'))? COMPLAIN_TYPE :'COMPLAIN_TYPE';?>: </label></td>
						<td>
							<select name="complain_type" id="complain_type" class="form-control" >
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									foreach ($complain_types as $key_type => $value_type) {
										?>
											<option <?= (isset($complain_for_order) && !empty($complain_for_order) && $complain_for_order[0]['type_id'] == $value_type['id'])? 'selected' : '' ?> value="<?=$value_type['id']?>" ><?=$value_type['type']?></option>
										<?php
									}
								?>
                            </select>
						</td>
					</tr>
					<tr>
						<td><label><?=(defined('COMPLAIN_REASON'))? COMPLAIN_REASON :'COMPLAIN_REASON';?>: </label></td>
						<td>
							<textarea style='margin-top:10px;margin-bottom:10px' class="form-control" name="complain_reason" id="complain_reason" cols="20" rows="6"><?=(isset($complain_for_order) && !empty($complain_for_order) ? $complain_for_order[0]['reason'] : '')?></textarea>
						</td>
					</tr>

					</tbody>
            </table>
            </div>
            <!--tableend block 1-->
            </td>

            <td style="vertical-align: baseline">
            <!--tablestart block 2-->
            <div style=" min-width:454px; border:3px solid #D9534F; margin-left:10px;  height:100%; border-radius:7px; padding:20px; ">
            <table border="0">
                <tbody>
                    <tr>
                        <td><label><?=(defined('STATUS'))? STATUS :'STATUS';?>: *</label></td>
                        <td><select name="delivery_status" id="delivery_status" class="form-control required" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
                                <?php
					$active = (isset($orderData[0]["delivery_status"])) ? $orderData[0]["delivery_status"] : false;
					echo page::buildOptions("delivery_status",$active);
				?>
                            </select>
                            <input type='hidden' name='old_status_of_order' value="<?=$orderData[0]['delivery_status']?>">
                            <input type='hidden' class='delivery_at_driver' value="<?=$orderData[0]['delivered_at']?>">
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=(defined('ORDER_SOURCE'))? ORDER_SOURCE :'ORDER_SOURCE';?>:*</label></td>
                        <td><select name="order_source" id="order_source" class="form-control required" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
                                <?php
					$active = (isset($orderData[0]["order_source"])) ? $orderData[0]["order_source"] : false;
					echo page::buildOptionsByOrdering("delivery_source",$active);
				?>
                            </select>
                        </td>
                    </tr>
					
					
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                    <tr>
                        <td><label><?=(defined('ORDER_SOURCE_1'))? ORDER_SOURCE_1 :'ORDER_SOURCE_1';?>:</label></td>
                        <td>
							<input value="<?=(isset($orderData[0]["order_source_optional"])) ? $orderData[0]["order_source_optional"] : ""?>" type="text" id="order_source_optional" oninvalid="this.setCustomValidity('Only numeric, comma, space, + allowed, must be min 6 numbers')" oninput="this.setCustomValidity('')" name="order_source_optional" class="form-control">
							<input value="<?=(isset($orderData[0]["created_time"]) && isset($orderData[0]["created_date"]) ) ? $orderData[0]["created_date"] . ' ' .  $orderData[0]["created_time"] : ""?>" type='hidden' class='created_date_time_value'>
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=(defined('PAYMENT_TYPE'))? PAYMENT_TYPE :'PAYMENT_TYPE';?>:</label></td>
                        <td><select name="payment_type" id="payment_type" class="form-control required">
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
                                <?php
									$active = (isset($orderData[0]["payment_type"])) ? $orderData[0]["payment_type"] : false;
									echo page::buildOptionsByOrdering("delivery_payment",$active, false, false,true, '');
								?>
                            </select>
                        </td>
                    </tr>
                    <tr>
						<td><label class='payment_info'><?=(defined('PAYMENT_INFO'))? PAYMENT_INFO :'PAYMENT_INFO';?></label></td>
                        <td><input value="<?=(isset($orderData[0]["payment_optional"])) ? $orderData[0]["payment_optional"] : ""?>" type="text" class="form-control" name="payment_optional"></td>
                    </tr>
                     <tr>
                        <td><label>Վճարումից առաջ կապ հաստատվեց:</label></td>
                        <td>
							<select name="first_connect" id="first_connect" class="form-control" style="max-width: 116px; float:left; display:inline-block">
                                <option value="">Առաջին:</option>
                                <option <?php echo($orderData[0]["first_connect"] == '13')? 'selected' : '' ?> value="13">Viber</option>
                                <option <?php echo($orderData[0]["first_connect"] == '14')? 'selected' : '' ?> value="14">WhatsApp</option>
                                <option <?php echo($orderData[0]["first_connect"] == '11' )? 'selected' : ''?> value="11">Phone</option>
                                <option <?php echo($orderData[0]["first_connect"] == '2' )? 'selected' : ''?> value="2">Live Chat</option>
                                <option <?php echo($orderData[0]["first_connect"] == '3')? 'selected' : '' ?> value="3">Skype</option>
                                <option <?php echo($orderData[0]["first_connect"] == '10')? 'selected' : '' ?> value="10">Email</option>
                                <option <?php echo($orderData[0]["first_connect"] == '18')? 'selected' : '' ?> value="18">Telegram</option>
                            </select>
							<select name="second_connect" id="second_connect" class="form-control" style="max-width: 116px; float:left; display:inline-block">
                                <option value="">Երկրորդ:</option>
                                <option <?php echo($orderData[0]["second_connect"] == '13')? 'selected' : '' ?> value="13">Viber</option>
                                <option <?php echo($orderData[0]["second_connect"] == '14')? 'selected' : '' ?> value="14">WhatsApp</option>
                                <option <?php echo($orderData[0]["second_connect"] == '11' )? 'selected' : ''?> value="11">Phone</option>
                                <option <?php echo($orderData[0]["second_connect"] == '2' )? 'selected' : ''?> value="2">Live Chat</option>
                                <option <?php echo($orderData[0]["second_connect"] == '3')? 'selected' : '' ?> value="3">Skype</option>
                                <option <?php echo($orderData[0]["second_connect"] == '10')? 'selected' : '' ?> value="10">Email</option>
                                <option <?php echo($orderData[0]["second_connect"] == '18')? 'selected' : '' ?> value="18">Telegram</option>
                            </select>
                        </td>
                    </tr>
                     <tr>
                        <td>
							<label>Բոնուս / Մալուս։</label>
						</td>
                        <td>
							<div style='margin-bottom:10px'>
								<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
						        <label class="btn btn-primary" style='float:left;margin-right:2px'><input type="radio" name="bonus_type" value="1" id="option1" <?=(isset($orderData[0]["bonus_type"]) && $orderData[0]["bonus_type"] == 1) ? "checked" : ""?> onclick="addAttrb(); showBonusInfo()"><?=(defined('BONUS')) ?  BONUS : 'BONUS';?></label>
						        <label class="btn btn-danger"><input type="radio" name="bonus_type" value="2" id="option2" <?=(isset($orderData[0]["bonus_type"]) && $orderData[0]["bonus_type"] == 2) ? "checked" : ""?> onclick="removeAttrb(); showBonusInfo()"><?=(defined('ISMALUS')) ?  ISMALUS : 'ISMALUS';?></label>
						        <label class="btn btn-warning"><input type="radio" name="bonus_type" value="3" id="option3" <?=(isset($orderData[0]["bonus_type"]) && $orderData[0]["bonus_type"] == 3) ? "checked" : ""?> onclick="addAttrb(); hideBonusInfo()"><?=(defined('NON')) ?  NON : 'NON';?></label>
								<?php }else {?>
									<input type="hidden" name="bonus_type" id="option3" value="3">
								<?php } ?>
							</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        	<label class='hoverLinkEffect translateNameToArmenianSenderName'><?=(empty(array_intersect(array(89),explode(",",$get_lvl[0])))) ? ((defined('SENDER_NAME'))? SENDER_NAME :'SENDER_NAME') : ((defined('PARTNER_NAME'))? PARTNER_NAME :'PARTNER_NAME');?>:
                        	</label>
                        	<br>
	                        <input type='checkbox' name='anonym' value="<?=($orderData[0]["anonym"] == 1) ? 0 : 1?>" <?=($orderData[0]["anonym"] == 1) ? 'checked' : ''?> id='anonym_checkbox'> <label for='anonym_checkbox'><?=ANONYM?></label>
                        </td>
                        <td><input value="<?=(isset($orderData[0]["sender_name"])) ? $orderData[0]["sender_name"] : ""?>" type="text" name="sender_name" class="form-control" id="sender_name">
							<?php
								if(isset($full_name_sender)){
									?>
										<table class="table-bordered">
											<tr>
												<td style='padding:5px'><span class='show_log_of_order_sender'><?=(isset($first_names_sender[0]['first_name_rus'])?  $first_names_sender[0]['first_name_rus'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order_sender'><?=(isset($first_names_sender[0]['first_name_eng'])?  $first_names_sender[0]['first_name_eng'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order_sender'><?=(isset($last_names_sender[0]['last_name_rus'])?  $last_names_sender[0]['last_name_rus'] : '')?></span></td>
												<td style='padding:5px'><span class='show_log_of_order_sender'><?=(isset($last_names_sender[0]['last_name_eng'])?  $last_names_sender[0]['last_name_eng'] : '')?></span></td>
											</tr>
											<tr>
												<td style='padding:5px;color:red'><?=(!isset($first_names_sender[0]['first_name_rus']) || empty($first_names_sender[0]['first_name_rus'])? "<span class='show_log_of_order_sender'>переводить</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($first_names_sender[0]['first_name_eng']) || empty($first_names_sender[0]['first_name_eng'])? "<span class='show_log_of_order_sender'>translate</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($last_names_sender[0]['last_name_rus']) || empty($last_names_sender[0]['last_name_rus'])? "<span class='show_log_of_order_sender'>переводить</span>" : '')?></td>
												<td style='padding:5px;color:red'><?=(!isset($last_names_sender[0]['last_name_eng']) || empty($last_names_sender[0]['last_name_eng'])? "<span class='show_log_of_order_sender'>translate</span>" : '')?></td>
											</tr>
										</table>
									<?php
								}
							?>
						</td>
                    </tr>
                    <tr>
                        <td>
							<label>
								<a href="https://en.wikipedia.org/wiki/List_of_mobile_telephone_prefixes_by_country" style="color: black;" target="_blank"><?=(defined('SENDER_PHONE'))? SENDER_PHONE :'SENDER_PHONE';?>:</a>
							</label>
                        </td>
						<td>
							<input value="<?=(isset($orderData[0]["sender_phone"])) ? $orderData[0]["sender_phone"] : ""?>" pattern="(^\++[0-9]{6,})(,?\s?\+?[0-9]{6,})*" oninvalid="this.setCustomValidity('Only numeric, comma, space, + allowed, must be min 6 numbers')" oninput="this.setCustomValidity('')" type="text" class="form-control" name="sender_phone" id="sender_phone">
							<span style='font-size:11px' class='color_red senderPhoneMsg'></span>
						</td>
                    </tr>
                    <tr>
                        <td><label><?=(defined('SENDER_EMAIL'))? SENDER_EMAIL :'SENDER_EMAIL';?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_email"])) ? $orderData[0]["sender_email"] : ""?>" type="email" class="form-control" placeholder="Enter email" name="sender_email" id="sender_email"></td>
                    </tr>
                    <tr>
                        <td>
							<a target="_blank" class="ip-link" href="https://whatismyipaddress.com/ip/<?=(isset($orderData[0]["keyword"])) ? $orderData[0]["keyword"] : "00.00.000.000"?>">
								<label><?=(defined('REF_KEYWORDS'))? REF_KEYWORDS :'REF_KEYWORDS';?> : </label>
							</a>
							<span style='font-size:11px' class='color_red countryNamePart'><?=WILL_WRITE_COUNTRY_IF?></span>
						</td>
                        <td>
							<input value="<?=(isset($orderData[0]["keyword"])) ? $orderData[0]["keyword"] : ""?>" type="text" class="form-control required malus_required ip_field" name="keyword" id="keyword">
                        </td>
                    </tr>
                    <tr>
                        <td><label class='ModalForCountryPayments'><?=(defined('SENDER_COUNTRY'))? SENDER_COUNTRY :'SENDER_COUNTRY';?>*: </label></td>
                        <td>
							<select name="sender_country" id="sender_country" class="form-control required" required>
								<option value=""><?=(defined('SELECT_FROM_LIST'))? SELECT_FROM_LIST :'SELECT_FROM_LIST';?></option>
								<?php
									foreach($regions as $region){
										echo "<option data-phone-length='" . $region['phone_length'] . "' data-phone-operators-codes='" . $region['phone_operators_codes'] . "' data-phone-code='" . $region['country_phone']  ."' value='".$region['id']."'";
										if(isset($orderData[0]['sender_country']) && $orderData[0]['sender_country'] == $region['id']){
											echo "selected='selected'";
										}
										echo ">".$region['name_am']."</option>";
									}
								?>
							</select>
						</td>
                    </tr>
                    <tr>
                        <td><label><?=(defined('SENDER_CITY'))? SENDER_CITY :'SENDER_CITY';?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_region"])) ? $orderData[0]["sender_region"] : ""?>" type="text" class="form-control" name="sender_region" id="sender_region"></td>
                    </tr>
					<?php }else {?>
						<input type="hidden" name="order_source_optional" id="order_source_optional" value="">
						<input type="hidden" name="payment_type" id="payment_type" value="">
						<input type="hidden" name="payment_optional" id="payment_optional" value="">
			    	<?php } ?>
					<?php if(empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                    <tr>
                        <td><label><?=(defined('SENDER_ADDRESS'))? SENDER_ADDRESS :'SENDER_ADDRESS';?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_address"])) ? $orderData[0]["sender_address"] : ""?>" type="text" class="form-control" name="sender_address" id="sender_address"></td>
                    </tr>
                    <?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) : ?>
                    <tr>
                        <td><label><?=(defined('DELIVERY_REASON')) ? DELIVERY_REASON : 'DELIVERY_REASON';?>: *</label></td>
                        <td>
                            <select name="delivery_reason" id="delivery_reason" class="form-control required" style="float:left; display:inline-block" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									$active = (isset($orderData[0]["delivery_reason"])) ? $orderData[0]["delivery_reason"] : false;
									echo page::buildOptions("delivery_reason",$active);
								?>
                            </select>
                        </td>
                    </tr>
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                    <tr>
                        <td><label><?=(defined('GREETING_CARD_TEXT')) ? GREETING_CARD_TEXT : 'GREETING_CARD_TEXT';?>: </label></td>
                        <td><textarea class="form-control" name="greetings_card" id="greetings_card" cols="20" rows="5"><?=(isset($greetings_card_row[0])) ? $greetings_card_row[0]['value'] : ""?></textarea>
			            </td>
                    </tr>
					<?php } else {?>
					  <tr>
                        <td>&nbsp;<input type="hidden" name="greetings_card" id="greetings_card" value=""> </td>
						 <td></td>
                    </tr>
					<?php } ?>
                    <tr>
                        <td><label><?=(defined('CLIENT_LANG')) ? CLIENT_LANG : 'CLIENT_LANG';?>: *</label></td>
                        <td>
                            <select name="delivery_language_primary" id="delivery_language_primary" class="form-control required" style="max-width: 116px; float:left; display:inline-block" required>
                                <option value=""><?=(defined('PRIMA')) ? PRIMA : 'PRIMA';?>:</option>
				<?php
					$active = (isset($orderData[0]["delivery_language_primary"])) ? $orderData[0]["delivery_language_primary"] : false;
					
					echo page::buildOptions("delivery_language",$active);
				?>
                            </select>
                            <select name="delivery_language_secondary" id="delivery_language_secondary" class="form-control required" style="max-width: 116px; float:left; display:inline-block" required>
                                <option value=""><?=(defined('SECONDA')) ? SECONDA : 'SECONDA';?>:</option>
				<?php
					$active = (isset($orderData[0]["delivery_language_secondary"])) ? $orderData[0]["delivery_language_secondary"] : false;
					
					echo page::buildOptions("delivery_language",$active);
				?>
                            </select>
                        </td>
                    </tr>					
                            <?php
                            endif;
                             }else {?>
							 
					<tr>
                        <td><label><?=(defined('TRAVEL_REASON')) ? TRAVEL_REASON : 'TRAVEL_REASON';?>: *</label></td>
                        <td>
                            <select name="delivery_reason" id="delivery_reason" class="form-control required" style="float:left; display:inline-block" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
				<?php
					$active = (isset($orderData[0]["delivery_reason"])) ? $orderData[0]["delivery_reason"] : false;
					
					echo page::buildOptions("delivery_reason",$active);
				?>
                            </select>
                        </td>
                    </tr>
						<input type="hidden" name="sender_region" id="sender_region" value="">
						<input type="hidden" name="sender_address" id="sender_address" value="">
						<input type="hidden" name="sender_phone" id="sender_phone" value="">
						<input type="hidden" name="sender_email" id="sender_email" value="">
			    	<?php } ?>
					
                    <tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(',',$level[0]["user_level"])))) ? ((defined('NOTES'))? NOTES :'NOTES') : ((defined('TOUR_MANAGER_NOTES'))? TOUR_MANAGER_NOTES :'TOUR_MANAGER_NOTES');?>: </label></td>
                        <td><textarea class="form-control" name="notes" id="comment" cols="20" rows="6"><?=(isset($notes_row[0])) ? $notes_row[0]['value'] : ""?></textarea></td>
                    </tr>
                    <!-- Added By Hrach 08/12/19 -->
                    <?php
						if(isset($orderData[0]["delivery_status"]) && $orderData[0]["delivery_status"] == 2 ){
							$required = false;
							if( $orderData[0]['control_pending'] == 1 ) {
								$last_pending_info = getwayConnect::getwayData("SELECT * FROM `pending_info` WHERE `order_id` = '{$orderData[0]["id"]}' GROUP BY id desc limit 1");
								if(!empty($last_pending_info)){
									$overminutes =  strtotime(date('Y-m-d H:i:s')) - strtotime($last_pending_info[0]['created_date']);
									if( $overminutes > 1800 ) {
										$required = true;
									}
								}
							}
							?>
			                    <tr>
									<td>
										<label for='notes_for_pending'><?=NOTES_FOR_PENDING?></label>
									</td>
									<td>
										<textarea class="form-control" name="notes_for_pending" id="notes_for_pending" cols="20" rows="6" <?= ( $required )?'required' : '' ?> ></textarea>
									</td>
			                    </tr>
							<?php
						}
                    ?>
		            <?php
						$pending_info = getwayConnect::getwayData("SELECT * FROM `pending_info` LEFT JOIN user on pending_info.operator_id = user.id WHERE `order_id` = '{$orderData[0]["id"]}' and status = '1'");
						if(!empty($pending_info)){
							?>
								<tr><td>
										<b>
											<?=OLD_NOTES_FOR_PENDING?>
										</b>
									</td>
									<td>
										<div>
											<?php
												foreach( $pending_info as $key => $value ){
													?>
														<p><?= $key + 1 ?>)<span class='color_red'> <?=$value['created_date']?> ՝ </span> <?=$value['full_name_am']?> ՝ <?=$value['description']?></p>
													<?php
												}
											?>
										</div>
									</td>
								</tr>
							<?php
						}
		            ?>
					<tr>
                        <td><label><?=(empty(array_intersect(array(89),explode(',',$level[0]["user_level"])))) ? ((defined('NOTES_FOR_FLORIST'))? NOTES_FOR_FLORIST :'NOTES_FOR_FLORIST') : ((defined('NOTES_FOR_DRIVER'))? NOTES_FOR_DRIVER :'NOTES_FOR_DRIVER');?>: </label></td>
                        <td><textarea class="form-control" name="notes_for_florist" id="notes_for_florist" cols="20" rows="6"><?=(isset($notes_for_florist_row[0])) ? $notes_for_florist_row[0]['value'] : ""?></textarea></td>
                    </tr>
					
					<?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) {
						$active = (isset($orderData[0]["sell_point"])) ? $orderData[0]["sell_point"] : false;
									$relatedpartners = page::buildRelatedOptions('`data_partners`',
									'`delivery_sellpoint`',
									$active,
									"RIGHT JOIN",
									"ON `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id`",
									"WHERE `data_partners`.`depend_on` = (SELECT `depend_on` FROM `data_partners` WHERE `sell_point_id` = {$active} LIMIT 1) AND `data_partners`.`active` = 1"
									);
					?>
                    <tr>
                        <td><label><?=(defined('SALE_POINT'))? SALE_POINT :'SALE_POINT';?>:* </label></td>
                        <td>
							<select name="sell_point" id="sell_point" class="form-control" required>
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<option value="flp" <?=(rtrim($relatedpartners[1]) == 'flower') ? "selected" : ""?>><?=(isset($constants['FLOWERS_PARTNERS'])) ? $constants['FLOWERS_PARTNERS'] : 'FLOWERS_PARTNERS';?>...</option>
								<option value="rtp" <?=(rtrim($relatedpartners[1]) == 'travel') ? "selected" : ""?>><?=(isset($constants['TRAVEL_PARTNERS'])) ? $constants['TRAVEL_PARTNERS'] : 'TRAVEL_PARTNERS';?>...</option>
								<option value="ows" <?=(rtrim($relatedpartners[1]) == 'ows') ? "selected" : ""?>><?=(isset($constants['OTHER_WEBSITES'])) ? $constants['OTHER_WEBSITES'] : 'OTHER_WEBSITES';?>...</option>
                                <?php
									
						
									echo "<!--";
									echo $active."::";
									print_r($relatedpartners);
									echo "-->";
									echo page::buildOptions("delivery_sellpoint",$active,false,false,true);//set active to true
								?>
                            </select>
						</td>
                    </tr>
					<tr id="partnersFiled" style="display:<?=(!$relatedpartners[1]) ? "none" : ''?>;">
                        <td ><label id="partnerLable">
						<?php
						if(rtrim($relatedpartners[1]) == 'flower'){
							echo (isset($constants['FLOWERS_PARTNERS'])) ? $constants['FLOWERS_PARTNERS'] : 'FLOWERS_PARTNERS';
						}else if(rtrim($relatedpartners[1]) == 'travel'){
							echo (isset($constants['TRAVEL_PARTNERS'])) ? $constants['TRAVEL_PARTNERS'] : 'TRAVEL_PARTNERS';
						}else if(rtrim($relatedpartners[1]) == 'ows'){
							echo (isset($constants['OTHER_WEBSITES'])) ? $constants['OTHER_WEBSITES'] : 'OTHER_WEBSITES';
						}else{
							echo "";
						}
						?>
						:* </label><img class='img_for_info_div_mockup' src='http://new.regard-group.ru/template/icons/partner_icon.png'></td>
                        <td>
							<select name="sell_point_partner" id="sell_point_partner" class="form-control">
                                <option value=""><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>
								<?php
									$active = (isset($orderData[0]["sell_point"])) ? $orderData[0]["sell_point"] : 0;
									//buildRelatedOptions($table_name,$rel_table,$active = false,$join = 'RIGHT JOIN ',$on,$where ="",$orderby="")
									echo $relatedpartners[0];//set active to true
								?>
                            </select>
						</td>
                    </tr>
					<tr>
                        <td><label><?=(isset($constants['PROVIDER'])) ? $constants['PROVIDER'] : 'PROVIDER';?>: </label></td>
                        <td>
							<select class="form-control required" name="flourist_id" id="flourist">
								<option value=""><?=(isset($constants['SELECT_FROM_LIST'])) ? $constants['SELECT_FROM_LIST'] : 'SELECT_FROM_LIST';?></option>
								<?php foreach($flourists as $flourist){?>
									<option value="<?=$flourist['id']?>" <?=(isset($orderData[0]["id"]) && $orderData[0]['flourist_id'] == $flourist['id']) ? "selected='selected'" : "" ?>><?=ucfirst($flourist['username'])?></option>
								<?php } ?>
							</select>
						</td>
                    </tr>
					<tr>
                        <td><label><?=(isset($constants['RESPONSIBLE'])) ? $constants['RESPONSIBLE'] : 'RESPONSIBLE';?>: </label></td>
                        <td>
							<select class="form-control required" name="operator_name" id="operator_name">
								<option value=""><?=(isset($constants['SELECT_FROM_LIST'])) ? $constants['SELECT_FROM_LIST'] : 'SELECT_FROM_LIST';?></option>
								<?php foreach($operators as $operator_name){?>
									<option value="<?=$operator_name['username']?>" <?=(isset($orderData[0]["id"]) && $orderData[0]['operator_name'] == $operator_name['username']) ? "selected='selected'" : "" ?>><?=ucfirst($operator_name['username'])?></option>
								<?php } ?>
							</select>
						</td>
                    </tr>
					<?php }else {?>
						<input type="hidden" name="sell_point" id="sell_point" value="22">
						<input type="hidden" name="keyword" id="keyword" value="">
			    	<?php } ?>
                    <?php if (empty(array_intersect(array(89),explode(',',$level[0]["user_level"]))) || in_array(99,explode(',',$level[0]["user_level"]))) { ?>
                        <tr>
                            <td><label><?=(defined('HOW_WAS_DELIVERED'))? HOW_WAS_DELIVERED :'HOW_WAS_DELIVERED';?>: </label></td>
                            <td><label id="true_time" style="padding:5px; padding-right:20px">
                                    <input type="radio" name="ontime" value="1" id="true_time" <?=(isset($orderData[0]["ontime"]) && $orderData[0]["ontime"] == 1) ? "checked" : ""?>>
                                    <img height="40px" src="<?=$rootF?>/template/icons/ontime/1.png"></label>
                                <label id="bicycle-delivery" style="padding-right:20px">
                                    <input type="radio" name="ontime" value="2" id="false_time" <?=(isset($orderData[0]["ontime"]) && $orderData[0]["ontime"] == 2) ? "checked" : ""?>>
                                    <img height="40px" src="<?=$rootF?>/template/icons/ontime/2.png"></label>
                                <label style="padding-left: 5px;">
                                    <input type="checkbox" name="important" value="1" <?=(isset($orderData[0]["important"]) && $orderData[0]["important"] == 1) ? "checked" : ""?>>
                                    <img height="40px" src="<?=$rootF?>/template/icons/important/important.gif"></label>
                            </td>
                        </tr>
                    <?php }  ?>
					<?php if( isset($_GET['orderId']) && $_GET['orderId'] && $operator != $orderData[0]['operator'] ) {?>
						<tr>
							<td>
								<!-- <a href="<?=$rootF?>/confirmed.php" target="_blank" style="color:red"> -->
									<label><?=(isset($constants['CONFIRMED'])) ? $constants['CONFIRMED'] : 'CONFIRMED';?>: </label>
								<!-- </a> -->
							</td>
							<td>
								<input type="checkbox" name="confirmed" id="confirmed" value="1"
									<?php if( isset($orderData[0]["confirmed"]) && $orderData[0]["confirmed"] ) { 
										echo " checked ";
										echo " disabled ";
									} else { 
										echo " ";
									} 
									?>
								>
								<input type="hidden" name="confirmed_by" 
									<?php if( isset($orderData[0]["confirmed"]) && $orderData[0]["confirmed"] && isset($orderData[0]['confirmed_by']) ){
										echo " value='".$orderData[0]['confirmed_by']."' ";
									} else {
										echo " value='".$userData[0]['id']."' ";
									}
									?>
								>
								<label for="confirmed">
									<?php if( isset($orderData[0]["confirmed"]) && $orderData[0]["confirmed"] && isset($orderData[0]['confirmed_by']) ){
										echo ucfirst($orderData[0]['confirmed_by_user']);
									} else {
										echo ucfirst($operator);
									}
									?>
								</label>
							</td>
						</tr>
					<?php }?>
						<?php
							if(isset($_GET['orderId'])){
								$notifications_for_order = getwayConnect::getwayData("SELECT * FROM `notification_result` LEFT JOIN notification_type on notification_result.type_id = notification_type.id where order_id = '" . $orderData[0]['id'] . "'");
							}
						?>
						<?php
							if(isset($notifications_for_order) && count($notifications_for_order) > 0){
								foreach($notifications_for_order as $key=>$value){
									?>
										<tr>
											<td class='td_for_notification_type'><?=$value['type']?></td>
											<td class='td_for_notification_value'><?=$value['value']?> - <?=$value['datetime']?></td>
										</tr>
									<?php
								}
							}
						?>
						<tr class='taxCheckText'>
	                        <td colspan='2' class='taxCheckTextTd'>
	                        	<label  style='color:red;font-weight:bolder;font-size: 12px;padding-top: 5px;float:left'><?=(defined('HDM_CHECK_NUMBER'))? HDM_CHECK_NUMBER :'HDM_CHECK_NUMBER';?></label>
								<input style='border:1px solid red;height:25px;float:left;margin-left:4px' type="text" value="<?php echo $tax_number_hdm_text ?>" name="hdm_tax" class="form-control oneRowFormControl taxCheckPrintHdm">
								<label  style='color:red;font-weight:bolder;float:left;padding-top: 5px;font-size: 12px;margin-left: 15px'><?=(defined('HVHH_CHECK_NUMBER'))? HVHH_CHECK_NUMBER :'HVHH_CHECK_NUMBER';?></label>
								<input style='border:1px solid red;height:25px;float:left;margin-left: 5px' type="text" value="<?php echo $tax_number_hvhh_text ?>" name="hvhh_tax" class="form-control oneRowFormControl taxCheckPrintHvhh">
	                        </td>
	                    </tr>
						<tr>
							<td class='printXmlText' colspan='2' style='color:red;font-weight:bolder;font-size: 15px'>
								<?=(isset($constants['PRINT_TAX'])) ? $constants['PRINT_TAX'] : 'PRINT_TAX';?>
								<a class='color_red' style='margin:10px' href="/account/flower_orders/xmlInvoice.php?order_id=<?=$orderId?>" download target='_blank'>
									XML
								</a>
								<?=(isset($constants['EXPORTING'])) ? $constants['EXPORTING'] : 'EXPORTING';?>
							</td>
						</tr>
						<?php
							if(isset($xml_downloaded_history) && count($xml_downloaded_history) > 0){
								?>
									<tr>
										<td>
											<p style='font-weight:bolder;font-size:12px'><?=(isset($constants['PRINTED_HDM'])) ? $constants['PRINTED_HDM'] : 'PRINTED_HDM';?></p>
										</td>
										<td>
											<ul>
												<?php
													foreach($xml_downloaded_history as $key=>$value){
														?>
															<li>
																<?php echo $value['downloaded_datetime'] ?>
															</li>
														<?php
													}
												?>
											</ul>
										</td>
									</tr>
								<?php
							}
						?>
                </tbody>
            </table>
            </div>
            <!--tableend block 2-->
            </td>
        </tr>
    </tbody>
</table>
</div>
<div align="center">
    <button type="submit" name="<?=(isset($orderData[0]["id"]))? "update_order" : "insert_order"?>" class="btn btn-primary" id="btnSave"><?=(isset($orderData[0]["id"]))? ((defined('SAVE'))? SAVE :'SAVE') : ((defined('ADD'))? ADD :'ADD');?></button>
    &nbsp;<input type="button" class="btn btn-danger" value="<?=(defined('CLOSE'))? CLOSE :'CLOSE';?>" onclick="document.location.href='../flower_orders';">
    &nbsp;<input type="reset" class="btn btn-warning" value="<?=(defined('RESET'))? RESET :'RESET';?>" id="btnReset">
    <input type="hidden" value="<?=$operator?>" name="operator">
    <?php
	if(isset($orderData[0]["id"])){
    ?>
	<input type="hidden" value="<?=$orderData[0]["id"]?>" name="id">
    <?php	
	}
    ?>
</div>
</form>
<div id="otherDeliveries">
	<table class="table">
		<div class="partnerInfomockupdiv"></div>
		<tr class="thead">
			<th>Order ID</th>
			<th>Delivery Status</th>
			<th style="min-width: 120px;">Delivery Date</th>
			<th>Delivery Address</th>
			<th>Order Description</th>
		</tr>
		<tr class="tdata">
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="otherOrders">
	<table class="table">
		<tr class="thead">
			<th>Order ID</th>
			<th>Delivery Status</th>
			<th>Delivery Date</th>
			<th>Order Created Time</th>
			<th>Ordered Products</th>
			<th>Price</th>
			<th>Sender Name</th>
			<th>Sender Country</th>
			<th>Sender Address</th>
			<th>Receiver Name</th>
			<th>Receiver Address</th>
			<th>Notes</th>
			<th>Sell Point</th>
		</tr>
		<tr class="tdata">
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
<!-- Added By Hrach -->
<div class="modal fade" id="modal_for_country_payment" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Country</td>
                        <td>Payment</td>
                    </tr>
                    </thead>
                    <tbody class="country_payment_table_body">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Added By Hrach -->
<div class="modal fade" id="change_log_doing" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="log_data">Պատվերի հետ կատարված գործողությունների ընթացքը <span class='for_order_number'></span></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>#</td>
                        <td>Մանրամասներ</td>
                        <td>Ամսաթիվ</td>
                        <td>Ստատուս</td>
                        <td>Սպասարկող</td>
                    </tr>
                    </thead>
                    <tbody class="log_table_body">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Փակել</button>
            </div>
        </div>
    </div>
</div>
<!-- Added By Ruben -->
<div class="modal fade" id="payment_info_window" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="log_data">Աղբյուրի տվյալներ<span class='for_order_number'></span></h4>
            </div>
            <div class="modal-body">
				<p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:15px;font-family:"Calibri","sans-serif";text-align:justify;'><strong><span style='font-family:"Sylfaen","serif";'>Վճարման տվյալներ</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- &nbsp;&lt;&lt;Վճարման Ձև&gt;&gt;-ից կախված հարկավոր է լրացնել հետևալ կերպ՝</span></p>
				<ul class="decimal_type" style="list-style-type: circle;">
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Unistream</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; ստանալու կոդը &nbsp;/ գումարը &nbsp;/ ում անունով: Օրինակ. 254789245 / 89000 ռուբլի/ Հովիկ</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>MoneyGram</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- ստանալու կոդը &nbsp;/ գումարը &nbsp;/ ում անունով</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Ria</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- ստանալու կոդը &nbsp;/ գումարը &nbsp;/ ում անունով</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Золотая Корона</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- ստանալու կոդը &nbsp;/ գումարը &nbsp;/ ում անունով</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Բանկային փոխանցում</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- &nbsp;Transaction ID / գումար / ամսաթիվ : Օրինակ.858933 / 37000դր / 11,04</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>WebMoney</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; փոխանցված գումարը / ամսաթիվ: Օրինակ. 58 euro / 23.12</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Yandex.Money</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; փոխանցված գումարը / մանրամասն ամսաթիվը: &nbsp;11791 ռուբլի/ 03.03.2020 11:40</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Credit Cards by Stripe</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; փոխանցողի էլ փոստը՝ Stripe-ից ստացված հաստատման նամակից</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Qiwi&nbsp;</span></strong><span style='font-family:"Sylfaen","serif";'>- փոխանցված գումարը / ամսաթիվ: Օրինակ.&nbsp;</span><span style='font-family:"Sylfaen","serif";'>92</span><span style='font-family:"Sylfaen","serif";'>58&nbsp;</span><span style='font-family:"Sylfaen","serif";'>ռուբլի</span><span style=";">&nbsp;/ 23.12</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>PayPal</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցողի էլ փոստը՝&nbsp;</span><span style='font-family:"Sylfaen","serif";'>PayPal</span><span style=";">&nbsp;-ից ստացված հաստատման նամակից</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Կանխիկ Խանութում</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; ստացած&nbsp;</span><span style='font-family:"Sylfaen","serif";'>գումարը</span><span style='font-family:"Sylfaen","serif";'>&nbsp;/ ստացող ֆլորիստի անունը:&nbsp;</span><span style='font-family:"Sylfaen","serif";'>Օրինակ.&nbsp;</span><span style='font-family:"Sylfaen","serif";'>1500</span><span style=";">&nbsp;/&nbsp;</span><span style=";">Անի</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Կանխիկ Առաքիչին</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; ստացած&nbsp;</span><span style='font-family:"Sylfaen","serif";'>գումարը</span><span style='font-family:"Sylfaen","serif";'>&nbsp;/ ստացող առաքիչի անունը:&nbsp;</span><span style='font-family:"Sylfaen","serif";'>Օրինակ.&nbsp;</span><span style='font-family:"Sylfaen","serif";'>1500</span><span style=";">&nbsp;/&nbsp;</span><span style=";">Հրաչ</span></li>  
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Իդրամ 942636529</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցված գումարը / ամսաթիվ: Օրինակ.</span><span style='font-family:"Sylfaen","serif";'>&nbsp;</span><span style='font-family:"Sylfaen","serif";'>10700դր</span><span style='font-family:"Sylfaen","serif";'>&nbsp;</span><span style=";">/ 15,02</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";color:gray;'>Իդրամ Օնլայն</span></strong><span style='font-family:"Sylfaen","serif";color:gray;'>&nbsp;- փոխանցված գումարը / Transaction ID: Օրինակ. &nbsp;14500 դրամ/45797811</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Ameriabank Օնլայն</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- փոխանցված գումարը / Transaction ID: Օրինակ. 26600 դրամ/1587367818</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>TelCell Տերմինալ</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; համակարգը /</span><span style='font-family:"Sylfaen","serif";'>&nbsp;գումարը / ամսաթիվ: Օրինակ.&nbsp;</span><span style='font-family:"Sylfaen","serif";'>Ինեկո /5500դրամ</span><span style='font-family:"Sylfaen","serif";'>&nbsp;/ 23.12</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Cberbank</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցված գումարը / ամսաթիվ:&nbsp;</span><span style='font-family:"Sylfaen","serif";'>Օրինակ. 10121 ռուբլի/19,04</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Ապառիկ Տրված</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;&ndash; չլրացնել ոչինիչ եթե գործնկեր է՝ հետվճարային տարբերակով, հակառակ դեպքում լրացնել թե ինչպես պետք է ստանալ վճառումը: Օրինակ. կմոտենա խանութ կվճարի</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Փոխանցում ըստ Էլ. դուրս գրված հաշվի</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-</span><span style='font-family:"Sylfaen","serif";'>&nbsp;փոխանցված գումարը / մանրամասն ամսաթիվը&nbsp;</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>La Caxia Barcelona</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցված գումարը / մանրամասն ամսաթիվը</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>Stripe Barcelona</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցողի էլ փոստը՝  Stripe-ից ստացված հաստատման նամակից</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>PayPal Barcelona</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;-&nbsp;</span><span style='font-family:"Sylfaen","serif";'>փոխանցողի էլ փոստը՝&nbsp;</span><span style='font-family:"Sylfaen","serif";'>PayPal</span><span style=";">&nbsp;-ից ստացված հաստատման նամակից</span></li>
				    <li><strong><span style='font-family:"Sylfaen","serif";'>ACBA Օնլայն</span></strong><span style='font-family:"Sylfaen","serif";'>&nbsp;- փոխանցված գումարը / Transaction ID: Օրինակ. 26600 դրամ/1587367818</span></li>
				</ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">փակել</button>
            </div>
        </div>
    </div>
</div>
<!-- Added By Hrach -->
<div class="modal fade" id="change_log" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
			<form method='post'>
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
	                </button>
	                <h4 class="modal-title" id="log_data">Translate Name</h4>
	            </div>
	            <div class="modal-body">
					<input type='hidden' name='for_translate_first_names' value="<?=(isset($first_names[0]['id']))?$first_names[0]['id']:''?>">
					<input type='hidden' name='for_translate_last_names' value="<?=(isset($last_names[0]['id']))?$last_names[0]['id']:''?>">
	                <table class="table table-bordered">
	                    <thead>
	                    <tr>
	                        <th>Հայերեն Անուն</th>
	                        <th>Ռուսերեն Անուն</th>
	                        <th>Անգլերեն Անուն</th>
	                    </tr>
	                    <tr class='tr_for_names'>
							<td>
								<input type='text' name='first_name_arm' class='form-control' value="<?=(isset($receiver_first_name))? $receiver_first_name : ''?>">
							</td>
							<td>
								<input type='text' name='first_name_rus' class='form-control' value="<?=(isset($first_names[0]['first_name_rus']))? $first_names[0]['first_name_rus'] : ''?>">
							</td>
							<td>
								<input type='text' name='first_name_eng' class='form-control' value="<?=(isset($first_names[0]['first_name_eng']))? $first_names[0]['first_name_eng'] : ''?>">
							</td>
							</tr>
							<tr>
							<th>Հայերեն Ազգանուն</th>
	                        <th>Ռուսերեն Ազգանուն</th>
	                        <th>Անգլերեն Ազգանուն</th>
	                    </tr>
	                    <tr class='tr_for_surnames'>
							<td>
								<input type='text' name='last_name_arm' class='form-control' value="<?=(isset($receiver_last_name))? $receiver_last_name : ''?>">
							</td>
							<td>
								<input type='text' name='last_name_rus' class='form-control' value="<?=(isset($last_names[0]['last_name_rus']))? $last_names[0]['last_name_rus'] : ''?>">
							</td>
							<td>
								<input type='text' name='last_name_eng' class='form-control' value="<?=(isset($last_names[0]['last_name_eng']))? $last_names[0]['last_name_eng'] : ''?>">
							</td>
						</tr>
						</thead>
	                </table>
	            </div>
	            <div class="modal-footer">
	                <button type='submit' class="btn btn-success">Update</button>
	                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	            </div>
	        </form>
        </div>
    </div>
</div>
<div class="modal fade" id="change_log_sender" tabindex="-1" role="dialog" aria-labelledby="log_data_sender">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
			<form method='post'>
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
	                </button>
	                <h4 class="modal-title" id="log_data_sender">Translate Name</h4>
	            </div>
	            <div class="modal-body">
					<input type='hidden' name='for_translate_first_names' value="<?=(isset($first_names_sender[0]['id']))?$first_names_sender[0]['id']:''?>">
					<input type='hidden' name='for_translate_last_names' value="<?=(isset($last_names_sender[0]['id']))?$last_names_sender[0]['id']:''?>">
	                <table class="table table-bordered">
	                    <thead>
	                    <tr>
	                        <th>Հայերեն Անուն</th>
	                        <th>Ռուսերեն Անուն</th>
	                        <th>Անգլերեն Անուն</th>
	                    </tr>
	                    <tr class='tr_for_names'>
							<td>
								<input type='text' name='first_name_arm' class='form-control' value="<?=(isset($sender_first_name))? $sender_first_name : ''?>">
							</td>
							<td>
								<input type='text' name='first_name_rus' class='form-control' value="<?=(isset($first_names_sender[0]['first_name_rus']))? $first_names_sender[0]['first_name_rus'] : ''?>">
							</td>
							<td>
								<input type='text' name='first_name_eng' class='form-control' value="<?=(isset($first_names_sender[0]['first_name_eng']))? $first_names_sender[0]['first_name_eng'] : ''?>">
							</td>
							</tr>
							<tr>
							<th>Հայերեն Ազգանուն</th>
	                        <th>Ռուսերեն Ազգանուն</th>
	                        <th>Անգլերեն Ազգանուն</th>
	                    </tr>
	                    <tr class='tr_for_surnames'>
							<td>
								<input type='text' name='last_name_arm' class='form-control' value="<?=(isset($sender_last_name))? $sender_last_name : ''?>">
							</td>
							<td>
								<input type='text' name='last_name_rus' class='form-control' value="<?=(isset($last_names_sender[0]['last_name_rus']))? $last_names_sender[0]['last_name_rus'] : ''?>">
							</td>
							<td>
								<input type='text' name='last_name_eng' class='form-control' value="<?=(isset($last_names_sender[0]['last_name_eng']))? $last_names_sender[0]['last_name_eng'] : ''?>">
							</td>
						</tr>
						</thead>
	                </table>
	            </div>
	            <div class="modal-footer">
	                <button type='submit' class="btn btn-success">Update</button>
	                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	            </div>
	        </form>
        </div>
    </div>

</div>
<!--  -->
<audio style="display:none;" id="alertSound" src="<?=$rootF?>/template/sound/alert.mp3" loop></audio>
<link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
		<!-- Bootstrap minified CSS -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
		<!-- Bootstrap optional theme -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />

<!-- initialize library-->
		<!-- Latest jquery compiled and minified JavaScript -->
		<script src="https://code.jquery.com/jquery-latest.min.js"></script>
		<!-- Bootstrap minified JavaScript -->
		<script src="<?=$rootF?>/template/bootstrap/js/bootstrap.min.js"></script>
		<!--end initialize library-->
		<!-- Menu Toggle Script -->
		<!-- Bootstrap minified JavaScript -->
		<script src="<?=$rootF?>/template/js/accounting.min.js"></script>
		<script src="<?=$rootF?>/template/dropzone.js"></script>
		<script src="<?=$rootF?>/template/datepicker/js/bootstrap-datepicker.js"></script>
		<script src="<?=$rootF?>/template/js/phpjs.js"></script>
		<script src="<?=$rootF?>/template/rangedate/moment.min.js"></script>
		<script src="<?=$rootF?>/template/rangedate/jquery.daterangepicker.js"></script>
		<script src="<?= $rootF ?>/template/js/imagelightbox.min.js"></script>
		<script src="<?=$rootF?>/template/slider/jquery.magnify.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>
        
			<script type="text/javascript">
				$('[data-magnify]').magnify({
				  fixedContent: false
				});
				var subregionType = <?=page::getJsonData("delivery_subregion", "code");?>;
				var product_results = [];
				var newly_selected = [];
				setTimeout(function(){
					showPrintXml();
					showPrintXmlText();
					showUsdPriceDependsWebsite();
					calculateSomeProcent();
					calculateMulticurrencyPrice();
					calculateTotalAmdPrice();
					calculatingUsdPriceTotal();
					calculatEachPrice();
					taxCheckPrint();
					disableDeliveryField();
					addCarBorder();
					showAmdPricePaymentType();
					setRequiredIpInSomeCases();
					setUploadedExchangePrices();
					// requiredFirstConnectField();
					checkPaymentTypeRequired();
					checkBonusRadioDisabled();
				},500)
				var usd = 470;
				var rub = 7.1;
				var gbp = 550;
				var eur = 500;
				var amd = 1;
				$(document).on("change","#payment_type",function(){
					showPrintXml();
				})
				$(document).on("change",".delivery_region",function(){
					showPrintXml();
				})
				$(document).on("change","#currency",function(){
					calculateMulticurrencyPrice();
					calculateSomeProcent();
				})
				$(document).on("change","#price",function(){
					calculateSomeProcent();
					calculateMulticurrencyPrice();
				})
				$(document).on("change",".productAmdPriceField",function(){
					calculateTotalAmdPrice();
				})
				$(document).on("keyup",".taxCheckPrintHvhh",function(){
					taxCheckPrint();
				})
				$(document).on("keyup",".taxCheckPrintHdm",function(){
					taxCheckPrint();
				})
				$(document).on("change","#sell_point_partner",function(){
					showUsdPriceDependsWebsite();
					showPrintXmlText();
					showPrintXml();
				})
				$(document).on("change","#sell_point",function(){
					showUsdPriceDependsWebsite();
					showPrintXml();
				})
				$(document).on("change",".productUsdPriceField",function(){
					calculatingUsdPriceTotal();
				})
				$(document).on("change",".usdUploadedPrice",function(){
					calculatingUsdPriceTotal();
				})
				$(document).on("change",".delivery_static_price",function(){
					disableDeliveryField();
				})
				$(document).on("keyup",".delivery_other_price",function(){
					disableDeliveryField();
					calculateTotalAmdPrice();
				})
				$(document).on("keyup",".postcard_amd_price",function(){
					calculateTotalAmdPrice();
				})
				$(document).on("change",".delivery_static_price",function(){
					calculateTotalAmdPrice();
				})
				$(document).on("keyup",".productAmdPriceField",function(){
					var choosed_id = $(this).attr('data-prod-id');
					calculateEachProductPrice(choosed_id);
				})
				$(document).on("keyup",".productQuantityAdded",function(){
					var choosed_id = $(this).attr('data-prod-id');
					calculateEachProductPrice(choosed_id);
				})
				$(document).on("keyup",".uploadQUanitityClass",function(){
					var choosed_id = $(this).attr('data-prod-id');
					calculateEachProductPrice(choosed_id);
				})
				$(document).on("change","#payment_type",function(){
					setRequiredIpInSomeCases();
				})
				$(document).on("change","#order_source",function(){
					setRequiredIpInSomeCases();
				})
				$(document).on("click",".translateNameToArmenianReceiverName",function(){
					translateNameToArmenianReceiverName();
				})
				$(document).on("click",".translateNameToArmenianSenderName",function(){
					translateNameToArmenianSenderName();
				})
				$(document).on("change","#first_connect",function(){
					checkBonusRadioDisabled();
				})
				$(document).on("change","input[type=radio][name=bonus_type]",function(){
					checkBonusRadioDisabled();
				})
				function checkBonusRadioDisabled(){
					var firstConnectVal = $('#first_connect').val();
					if(firstConnectVal == ''){
						$('#option1').prop("checked", false);;
						$('#option1').attr('disabled','disabled');
					}
					else{
						$('#option1').removeAttr('disabled');
					}
				}
				function translateNameToArmenianSenderName(){
					var sender_full_name = $("#sender_name").val().split(" ");
					var first_name = sender_full_name[0];
					var last_name = sender_full_name[1];
					if(first_name && first_name.length > 1){
						setTimeout(function(){
							getFirstNameTranslate(first_name,'sender_name');
						},400)
					}
					if(last_name && last_name.length > 1){
						setTimeout(function(){
							getLastNameTranslate(last_name,'sender_name');
						},800)
					}
				}
				function translateNameToArmenianReceiverName(){
					var receiver_full_name = $("#receiver_name").val().split(" ");
					var first_name = receiver_full_name[0];
					var last_name = receiver_full_name[1];
					if(first_name && first_name.length > 1){
						setTimeout(function(){
							getFirstNameTranslate(first_name,'receiver_name');
						},400)
					}
					if(last_name && last_name.length > 1){
						setTimeout(function(){
							getLastNameTranslate(last_name,'receiver_name');
						},800)
					}
				}
				function getFirstNameTranslate(first_name,inputId){
					var inputVal = $("#" + inputId).val();
					var valOfInput = $("#" + inputId).val().split(" ");
					var valOfInputFirst = $("#" + inputId).val().split(" ")[0];
					var valOfInputSecond = $("#" + inputId).val().split(" ")[1];
					$.ajax({
			            url: location.href,
			            type: 'post',
			            data: {
			                getFirstNameTranslate: true,
			                first_name: first_name
			            },
			            success: function(resp){
			            	if(resp){
			            		resp = JSON.parse(resp);
								$("#" + inputId).val(inputVal.replace(valOfInputFirst, resp.first_name_arm));
			            	}
			            }
			        })
				}
				function getLastNameTranslate(last_name,inputId){
					var valOfInput = $("#" + inputId).val().split(" ");
					var valOfInputFirst = $("#" + inputId).val().split(" ")[0];
					var valOfInputSecond = $("#" + inputId).val().split(" ")[1];
					$.ajax({
			            url: location.href,
			            type: 'post',
			            data: {
			                getLastNameTranslate: true,
			                last_name: last_name
			            },
			            success: function(resp){
			            	if(resp){
			            		resp = JSON.parse(resp);
			            		$("#" + inputId).val(valOfInputFirst + " " + resp.last_name_arm)
			            	}
			            }
			        })
				}
				var requiredArrayForPaymentType = ['15','23']
				var requiredArrayOrderSource = ['2']
				function setRequiredIpInSomeCases(){
					var payment_type = $("#payment_type").val();
					var order_source = $("#order_source").val();
					if($.inArray(payment_type, requiredArrayForPaymentType) !== -1 || $.inArray(order_source, requiredArrayOrderSource) !== -1 ){
						$("#keyword").attr('required',true);
					}
					else{
						$("#keyword").attr('required',false);
					}
				}
				function addCarBorder(){
					var delivery_at_driver = $(".delivery_at_driver").val();
					var delivery_status = $("#delivery_status").val();
					var travel_time_end = $("#travel_time_end").val();
					var delivery_date = $("#delivery_date").val();
					var time_manual = $("#time_manual").val();
					var delivery_time_range = $("#delivery_time option:selected" ).text();
					var timeToDiff = '';
                    var car_color = 'none';
                    if(delivery_status == 3){
                        if(travel_time_end != ''){
                            timeToDiff = delivery_date + " " + travel_time_end;
                        } else if (time_manual != ''){
                            timeToDiff = delivery_date + " " + time_manual;
                        }
                        else if (delivery_time_range != null){
                            timeToDiff = delivery_date + " " + delivery_time_range.split('-')[1];
                        }
                        timeToDiff += ":00";
                        if(delivery_at_driver != null){
                            var timeDiff = (new Date(timeToDiff).getTime() - new Date(delivery_at_driver).getTime());
                            var minDiff =   Math.floor((timeDiff % 86400000) / 3600000) * 60 + Math.round(((timeDiff % 86400000) % 3600000) / 60000);
                            if(minDiff > 30) {
                                car_color = "green";
                            } else if (minDiff <= 30 && minDiff >= 0){
                                car_color = "yellow";
                            } else if(minDiff < 0) {
                                car_color = "red";
                            }
                        }
                        var title_of_car = $('input[name=delivery_type]:checked').parent().find('img').attr('title');
	                    $('input[name=delivery_type]:checked').parent().css({'border':'2px solid ' + car_color});
	                    $('input[name=delivery_type]:checked').parent().find('img').attr('title',title_of_car + " " + delivery_at_driver);
                        if(car_color != 'none'){
	                        var delivery_type_radio = $('input[name=delivery_type]');
	                        for(var i = 0 ; i < delivery_type_radio.length ; i++){
	                        	$(delivery_type_radio[i]).attr('disabled',true)
	                        }
                        }
                    }
				}
				function calculatEachPrice(){
					var productAmdPriceField = $(".productAmdPriceField");
					for(var i = 0 ; i < productAmdPriceField.length ; i++){
						var data_prod_id = $(productAmdPriceField[i]).attr('data-prod-id');
						var productPrice = $(productAmdPriceField[i]).val();
						var productQuantityField = $(".productQuantityField[data-prod-id='" + data_prod_id + "']").val();
						var price_for_prod = 0;
						if(productQuantityField != 0 || productPrice != 0){
							price_for_prod = (Math.round(productPrice/productQuantityField * 100) / 100).toFixed(2);
						}
						$(".showEachPrice[data-prod-id='" + data_prod_id + "']").html(price_for_prod);
					}
				}
				function calculateEachProductPrice(choosed_id){
					var productAmdPriceField = $(".productAmdPriceField[data-prod-id='" + choosed_id + "']").val();
					var productQuantityField = $(".productQuantityField[data-prod-id='" + choosed_id + "']").val();
					var price_for_prod = 0;
					if(productQuantityField != 0 && productQuantityField != ''){
						price_for_prod = (Math.round(productAmdPriceField/productQuantityField * 100) / 100).toFixed(2);
					}
					$(".showEachPrice[data-prod-id='" + choosed_id + "']").html(price_for_prod);
				}
				function disableDeliveryField(){
					var delivery_static_price = $(".delivery_static_price").val();
					var delivery_other_price = $(".delivery_other_price").val();
					if(delivery_static_price != 0){
						$('.delivery_other_price').attr('disabled',true);
					}
					else if(delivery_other_price != ''){
						$('.delivery_static_price').attr('disabled',true);
					}
					if(delivery_static_price == 0){
						$('.delivery_other_price').removeAttr('disabled');
					}
					if(delivery_other_price == ''){
						$('.delivery_static_price').removeAttr('disabled');
					}
				}
				function calculatingUsdPriceTotal(){
					var usdUploadedPrice = $(".usdUploadedPrice");
					var productUsdPriceField = $(".productUsdPriceField");
					var total_usd_price = 0;
					for(var i = 0 ; i < usdUploadedPrice.length ; i++){
						total_usd_price+= parseInt($(usdUploadedPrice[i]).val())
					}
					for(var i = 0 ; i < productUsdPriceField.length ; i++){
						total_usd_price+= parseInt($(productUsdPriceField[i]).val())
					}
					$(".total_price_usd").html("$" + total_usd_price)
				}
				var arm_websites_sell_point_partner_array = ['16','15','20','19','44'];
				var arm_websites_sell_point_array = ['18','13'];
				function showUsdPriceDependsWebsite(){
					var sell_point = $("#sell_point").val();
					if(sell_point == 'flp'){
						var sell_point_partner = $("#sell_point_partner").val();
						if($.inArray(sell_point_partner, arm_websites_sell_point_partner_array) !== -1){
							$(".usdShowField").slideUp(300);
							$(".productExchangePart").slideUp(300);
							$(".usdShowField").removeAttr('required','required');
							$(".usdShowField").removeAttr('min');
							$(".usdIconPricePart").slideUp(300);
							$(".productAmdPriceField").slideDown(200)
							$(".total_amd_field_price").slideDown(200)
							$(".amdIconPricePart").slideDown(200)
						}
						else{
							$(".usdShowField").slideDown(300);
							$(".productExchangePart").slideDown(300);
							$(".usdShowField").attr('required','required');
							$(".usdShowField").attr('min','1');
							$(".usdIconPricePart").slideDown(300);
							$(".productAmdPriceField").slideUp(200)
							$(".total_amd_field_price").slideUp(200)
							$(".amdIconPricePart").slideUp(200)
						}
					}
					else{
						if($.inArray(sell_point, arm_websites_sell_point_array) !== -1 ){
							$(".usdShowField").slideUp(300);
							$(".productExchangePart").slideUp(300);
							$(".usdShowField").removeAttr('required','required');
							$(".usdShowField").removeAttr('min');
							$(".usdIconPricePart").slideUp(300);
							$(".productAmdPriceField").slideDown(200)
							$(".total_amd_field_price").slideDown(200)
							$(".amdIconPricePart").slideDown(200)
						}
						else{
							$(".usdShowField").slideDown(300);
							$(".productExchangePart").slideDown(300);
							$(".usdShowField").attr('required','required');
							$(".usdShowField").attr('min','1');
							$(".usdIconPricePart").slideDown(300);
							$(".productAmdPriceField").slideUp(200)
							$(".total_amd_field_price").slideUp(200)
							$(".amdIconPricePart").slideUp(200)
						}
					}
				}
				function taxCheckPrint(){
					var taxCheckPrintHvhh = $(".taxCheckPrintHvhh").val();
					var taxCheckPrintHdm = $('.taxCheckPrintHdm').val();
					if(taxCheckPrintHvhh != ''){
						$('.taxCheckPrintHdm').attr('disabled',true);
					}
					else if(taxCheckPrintHdm != ''){
						$('.taxCheckPrintHvhh').attr('disabled',true);
					}
					if(taxCheckPrintHvhh == ''){
						$('.taxCheckPrintHdm').removeAttr('disabled');
					}
					if(taxCheckPrintHdm == ''){
						$('.taxCheckPrintHvhh').removeAttr('disabled');
					}
				}
				function calculateTotalAmdPrice(){
					var productAmdPriceField = $(".productAmdPriceField");
					var delivery_other_price = $(".delivery_other_price").val();
					var total_price_amd = 0;
					for(var i = 0 ; i < productAmdPriceField.length ; i++){
						if($(productAmdPriceField[i]).val() != ''){
							total_price_amd+= parseInt($(productAmdPriceField[i]).val());
						}
					}
					var postcard_amd_price = $(".postcard_amd_price").val();
					if(postcard_amd_price != ''){
						total_price_amd+= parseInt(postcard_amd_price);
					}
					var delivery_static_price_checked = $(".delivery_static_price option:selected" ).text();
					if(delivery_static_price_checked != 'Առաքման արժեք'){
						if(delivery_static_price_checked != 'Անվճար' && delivery_static_price_checked != 'Առաքման արժեք'){
							var delivery_static_price = delivery_static_price_checked.match(/\d+/);
							total_price_amd+= parseInt(delivery_static_price);
						}
					}
					else if(delivery_other_price != ''){
						total_price_amd+= parseInt(delivery_other_price)
					}
					$(".total_price_amd").html('Դ' + total_price_amd)
				}
				function calculateSomeProcent(){
					var val = $('#price').val();
					var currency = $("#currency option:selected" ).text();
					var res = (val*30)/100;
					if(currency == 'EUR'){
						res = res*eur;
					}
					if(currency == 'USD'){
						res = res*usd;
					}
					if(currency == 'RUB'){
						res = res*rub;
					}
					if(currency == 'GBP'){
						res = res*gbp;
					}
					$(".calculatedProcentOfPrice").val(res);
				}
				var payment_type_array = ['15','23','16','24','25','11','12','13','26','27','28','30','31','5','19'];
				var status_xml_approve_array = ['1','6','3','7','11','12','13'];
				function showPrintXmlText(){
					var status = $("#delivery_status").val();
					var valPaymentType = $("#payment_type").val();
					var sell_point_partner = $("#sell_point_partner").val();
					if($.inArray(status, status_xml_approve_array) !== -1 && $.inArray(valPaymentType, payment_type_array) !== -1 && $(".delivery_region").val() == 1 ){
						$(".printXmlText").slideDown(300);
					}else if( sell_point_partner == 44 ){
						$(".printXmlText").slideDown(200)
						$(".taxCheckTextTd").slideDown(200)
					}else if(sell_point_partner == 16 || sell_point_partner == 15 ){
						$(".printXmlText").slideUp(200)
						$(".taxCheckTextTd").slideUp(200)
					}
					else{
						$(".taxCheckTextTd").slideDown(200)
						$(".printXmlText").slideUp(300);
					}
				}

				function showAmdPricePaymentType(){
					var valPaymentType = $("#payment_type").val();
					if($.inArray(valPaymentType, payment_type_array) !== -1){
						$(".productAmdPriceField").slideDown(200)
						$(".amdIconPricePart").slideDown(200)
						$(".total_amd_field_price").slideDown(200)
					}
				}
				function showPrintXml(){
					var valPaymentType = $("#payment_type").val();
					var sell_point_partner = $("#sell_point_partner").val();
					if(($.inArray(valPaymentType, payment_type_array) !== -1 && $(".delivery_region").val() == 1) || (sell_point_partner == 16 || sell_point_partner == 15 || sell_point_partner == 44)){
						$(".showForXML").slideDown(300);
						$(".productAmdPriceField").slideDown(200)
						$(".amdIconPricePart").slideDown(200)
						addRequiredToAmdFields();
						$(".taxCheckText").slideDown(300);
					}
					else if(valPaymentType == 18){
						var sell_point_partner = $("#sell_point_partner").val();
						var sell_point = $("#sell_point").val();
						if($.inArray(sell_point_partner, arm_websites_sell_point_partner_array) !== -1 || $.inArray(sell_point, arm_websites_sell_point_array) !== -1 ){
							$(".printXmlText").slideUp(200)
							$(".productAmdPriceField").slideDown(200)
							$(".usdShowField").slideUp(200)
							$(".productExchangePart").slideUp(200)
							$(".usdShowField").removeAttr('required','required');
							$(".usdShowField").removeAttr('min');
							$(".amdIconPricePart").slideDown(200)
							addRequiredToAmdFields()
						}
						else{
							$(".usdShowField").slideDown(200)
							$(".productExchangePart").slideDown(200)
							$(".usdShowField").attr('required','required');
							$(".usdShowField").attr('min','1');
							$(".productAmdPriceField").slideUp(200)
							$(".amdIconPricePart").slideUp(200)
							removeRequiredFromAmdFields()
						}
					}
					else{
						removeRequiredFromAmdFields()
						$(".showForXML").slideUp(300);
						$(".taxCheckText").slideUp(300);
						$(".usdShowField").slideDown(200)
						$(".productExchangePart").slideDown(200)
						$(".usdShowField").attr('required','required');
						$(".usdShowField").attr('min','1');
					}
				}
				function addRequiredToAmdFields(){
					$(".productAmdPriceField").attr('required',true);
					$(".uploadQUanitityClass").attr('required',true);
					$(".uploadProductTaxId").attr('required',true);
					$(".productQuantityAdded").attr('required',true);
					$(".productAddedTaxAccount").attr('required',true);
					$(".total_amd_field_price").attr('required',true);
					$(".total_amd_field_price").attr('min','1');
					$(".productAmdPriceField").attr('min','1');
					$(".uploadQUanitityClass").attr('min','1');
					$(".uploadProductTaxId").attr('min','1');
					$(".productQuantityAdded").attr('min','1');
					$(".productAddedTaxAccount").attr('min','1');
				}
				function removeRequiredFromAmdFields(){
					$(".productAmdPriceField").attr('required',false);
					$(".uploadQUanitityClass").attr('required',false);
					$(".uploadProductTaxId").attr('required',false);
					$(".productQuantityAdded").attr('required',false);
					$(".productAddedTaxAccount").attr('required',false);
					$(".total_amd_field_price").attr('required',false);
					$(".total_amd_field_price").removeAttr('min');
					$(".productAmdPriceField").removeAttr('min');
					$(".uploadQUanitityClass").removeAttr('min');
					$(".uploadProductTaxId").removeAttr('min');
					$(".productQuantityAdded").removeAttr('min');
					$(".productAddedTaxAccount").removeAttr('min');
				}
				$('.time-input').on("keydown", function(e) {
				// prevent: "e", "=", ",", "-", "."
					if ([69, 187, 188, 189, 190].includes(e.keyCode)) {
						e.preventDefault();
					}
				})
				// Added By Dev for xml asop52f41v78x8z5
				var taxAccounts = getTaxAccountTexts();
				setTimeout(function(){
					chooseAddedProductSelectValue();
				},1000)
				function chooseAddedProductSelectValue(){
					var addedProductTaxId = $(".addedProductTaxId");
					for(var i = 0 ; i < addedProductTaxId.length ; i++){
						var val = $(addedProductTaxId[i]).attr('valuechoose');
						if(val != 'null'){
							$(addedProductTaxId[i]).val(val);
						}
					}
				}
				function getTaxAccountTexts(){
					$.ajax({
						url : "./products/ajax.php?getTaxAccountTexts=1",
						type: "GET",
						success:function(res){
							taxAccounts = JSON.parse(res)
						}
					})
				}
				// end asop52f41v78x8z5
				var ip_field;
				$("#sender_country").on("click", function() {
				    if($(".ip_field").val() != ip_field && $(".ip_field").val().length > 6 ){
						ip_field = $(".ip_field").val();
						addCountryNameunderIp(ip_field);
				    }
				});
				function addCountryNameunderIp(ip){
					$.getJSON("http://www.geoplugin.net/json.gp?ip="+ip, function (data) {
					    $(".countryNamePart").html(data.geoplugin_countryName);
					});
				}
				$(document).on("focusout",".ip_field",function(){
					checkValidIpFormatShowLabel();
				})
				function checkValidIpFormatShowLabel(){
					var val = $(".ip_field").val();
				    var message = '';
				    if(val.length > 0){
				    	if(val.match(/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/) != null){
				    		message+= "<?=WILL_WRITE_COUNTRY_IF?>";
				    	}
				    	else{
							message+= "Սխալ ֆորմատ";
				    	}
				    }
				    else{
			    		message+= "<?=WILL_WRITE_COUNTRY_IF?>";
				    }
				    if(message.length > 0){
						$(".countryNamePart").html(message)
				    }
				}
				function checkValidIpFormat(){
					var val = $(".ip_field").val();
					if(val.length > 0){
						if(val.match(/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/) != null){
							return true;
						}
						else{
							return false
						}
					}
					else{
						return true;
					}
				}
				checkPaymentTypeSelect();
				$(document).on("change","#payment_type",function(){
					checkPaymentTypeSelect();
					showPrintXmlText();
				})
				var old_country_phone_sender_country;
				var old_country_phone_receiver_country;
				$(document).on("change",".delivery_region",function(){
					var val = $(this).find('option:selected').html();
					if(val == 'Մոսկվա'){
						val = 'Ռուսաստան';
					}
					var country_phone_code = $( "#sender_country option:contains('" + val + "')" ).attr('data-phone-code');
					var country_operator_codes = $( "#sender_country option:contains('" + val + "')" ).attr('data-phone-operators-codes');
					var country_phone_length = $( "#sender_country option:contains('" + val + "')" ).attr('data-phone-length');
					var receiver_phone_val = $("#receiver_phone").val();
					if(receiver_phone_val == '' || old_country_phone_receiver_country == receiver_phone_val){
						$("#receiver_phone").val(country_phone_code);
					}
					console.log(old_country_phone_receiver_country);
					old_country_phone_receiver_country = country_phone_code;
					console.log(old_country_phone_receiver_country);
				})
				$(document).on("change","#sender_country",function(){
					var country_phone = $(this).find('option:selected').attr('data-phone-code');
					var sender_phone_val = $("#sender_phone").val();
					if(sender_phone_val == '' || old_country_phone_sender_country == sender_phone_val){
						$("#sender_phone").val(country_phone);
					}
					old_country_phone_sender_country = country_phone;
				})
				// Added By Hrach 08/12/19
				if($("#delivery_status").val() == 2){
					$("#operator_name").attr('required',true);
					checkIncomplitTime();
				}
				if($("#delivery_status").val() == 5){
					removeChexyalFromStatusList();
				}
				function getCurrenctFullDate(){
					var current_date = new Date($.now());
					return current_date.getFullYear() + "-" + (current_date.getMonth() + 1) + "-" + current_date.getDate() + " "+ current_date.getHours()+":"+current_date.getMinutes()+":"+current_date.getSeconds();
				}
				function checkIncomplitTime(){
					var current_date = getCurrenctFullDate();
					var created_date_time_value = $(".created_date_time_value").val();
					var difference = Math.ceil(( new Date(current_date) - new Date( created_date_time_value ))/1000/60);
					if(difference > 240){
						removeChexyalFromStatusList();
					}
				}
				function removeChexyalFromStatusList(){
					$("#delivery_status option[value='4']").remove();
				}
				function checkReceiverPhoneValidate(){
					var val = $("#receiver_phone").val();
					var country_name = $(".delivery_region").find('option:selected').html();
					if(country_name == 'Մոսկվա'){
						country_name = 'Ռուսաստան';
					}
					var country_phone = $("#sender_country option:contains('" + country_name + "')").attr('data-phone-code');
					var operator_codes = $("#sender_country option:contains('" + country_name + "')").attr('data-phone-operators-codes');
					var country_phone_length = $("#sender_country option:contains('" + country_name + "')").attr('data-phone-length');

					var message = '';
					if(country_phone && operator_codes && country_phone_length){
						if(val.length > 1){
							if(val.indexOf(',') != -1){
								return '';
							}
							if(val.indexOf('++') == -1){
								if (val.indexOf(country_phone) >= 0){
									var operator_codes_array = operator_codes.split(',');
									var max_length_operator_code = 0;
									for(var i = 0 ; i < operator_codes_array.length ; i++){
										if(operator_codes_array[i].length > max_length_operator_code){
											max_length_operator_code = operator_codes_array[i].length;
										}
									}
									var val_for_operator_codes = val.replace(country_phone,'');
									var operator_code = val_for_operator_codes.substr(0, max_length_operator_code);
									var operator_code_exist = '';
									for(var j = 0 ; j < operator_codes_array.length; j++){
										if(operator_code.indexOf(operator_codes_array[j]) >= 0){
											operator_code_exist = operator_codes_array[j];
										}
									}
									if(operator_code_exist != ''){
										var val_for_length_of_number = val_for_operator_codes.replace(operator_code_exist,'');
										if(val_for_length_of_number.length != country_phone_length){
											message = country_name + '-ի հեռախոսահամարի թվերի քանակը ՝ ' + country_phone_length;
										}
									}
									else{
										message = 'Օպերատրի կոդը սխալ է։';
									}
								}
								else{
									message = country_name + '-ի կոդը սկսվում է՝ ' + country_phone;
								}
							}
							return message;
						}
						else{
							return '';
						}
					}
					else{
						return '';
					}
				}
				function checkSenderPhoneValidate(){
					var val = $("#sender_phone").val();
					var country_name = $("#sender_country").find('option:selected').html();
					var country_phone = $("#sender_country").find('option:selected').attr('data-phone-code');
					var operator_codes = $("#sender_country").find('option:selected').attr('data-phone-operators-codes');
					var country_phone_length = $("#sender_country").find('option:selected').attr('data-phone-length');
					var message = '';
					if(country_phone && operator_codes && country_phone_length){
						if(val.length > 1){
							if(val.indexOf(',') != -1){
								return '';
							}
							if(val.indexOf('++') == -1){
								if (val.indexOf(country_phone) >= 0){
									var operator_codes_array = operator_codes.split(',');
									var max_length_operator_code = 0;
									for(var i = 0 ; i < operator_codes_array.length ; i++){
										if(operator_codes_array[i].length > max_length_operator_code){
											max_length_operator_code = operator_codes_array[i].length;
										}
									}
									var val_for_operator_codes = val.replace(country_phone,'');
									var operator_code = val_for_operator_codes.substr(0, max_length_operator_code);
									var operator_code_exist = '';
									for(var j = 0 ; j < operator_codes_array.length; j++){
										if(operator_code.indexOf(operator_codes_array[j]) >= 0){
											operator_code_exist = operator_codes_array[j];
										}
									}
									if(operator_code_exist != ''){
										var val_for_length_of_number = val_for_operator_codes.replace(operator_code_exist,'');
										if(val_for_length_of_number.length != country_phone_length){
											message = country_name + '-ի հեռախոսահամարի թվերի քանակը ՝ ' + country_phone_length;
										}
									}
									else{
										message = 'Օպերատրի կոդը սխալ է։';
									}
								}
								else{
									message = country_name + '-ի կոդը սկսվում է՝ ' + country_phone;
								}
							}
							return message;
						}
						else{
							return '';
						}
					}
					else{
						return '';
					}
				}
				function createProductList(product,searchPart){
					let dprice = (Number(product['product_price']) - Number(product['product_price']) * (Number(product['product_discount_id'])/100)).toFixed(2);
					var html = '<div class="col-sm-12 relatedProduct" style="margin-top:20px" data-price="'+dprice+'">';
					html += '<img src="./jos_product_images/'+product['product_thumb_image']+'" style="max-width: 115px; float: left;" alt="" title="'+product['product_desc']+'">';
					if(!searchPart){
						html += '<button data-dz-remove="" class="btn btn-danger btn-xs deleteRelated" data-price="'+dprice+'"><i class="glyphicon glyphicon-remove"></i></button>';
					}
					if(product['product_width'] > 0){
						html += '<div class="w-size">' + Number(product['product_width']).toFixed(2) +'</div>';
					}
					if(product['product_height'] > 0){
						html += '<div class="h-size">' + Number(product['product_height']).toFixed(2) +'</div>';
					}
					html += '<input type="hidden" name="relatedProduct[]" value="'+product['product_id']+'">';
					if(Number(product['product_discount_id']) > 0){
						html += '<span class="related_product_price">'+" <span class='original_price'>$ "+Number(product['product_price'])+"</span>   <span class='discounted_price'>$"+dprice+'</span>('+Number(product['product_discount_id'])+'%)</span>';
					} else {
						html += '<span class="related_product_price">$'+Number(product['product_price']).toFixed(2)+'</span>';
					}
					html += '<span><br>';
					html += '<input type="number" step="0.01" name="productNewPrice['+product['product_id']+']" class="productNewPrice" value="'+dprice+'"><span class="dollar-sign">$</span>';
					html += '<br>';
					html += '<span class="productNewAlternatePrice"></span>';
					let product_name = product['product_name'].replace("'", "\'");
					let product_s_desc = product['product_s_desc'].replace("'", "\'");
					let product_attr = product['attribute'].replace("'", "\'");
					html += '<textarea rows="2" cols="18" name="related_name['+product['product_id']+']" title="'+product_name+ ' - '+ product_s_desc +'">'+product['product_sku'] + ' - ' + product_name +'</textarea>';
					html += "<textarea rows='4' cols='19' name='short_desc["+product['product_id']+"]'>"+product_s_desc;
					if(product['product_s_desc'] != '' && product['attribute'] != ''){
						html += ', ';
					}
					if(product['attribute'] != ''){
						html += product_attr;
					}
					html +=  "</textarea>";
					html += '</span>';
					// Added By Hrach
					html += "<div class='col-md-12'><img data-prod-id='" + product['product_id'] + "' class='img_for_stock_prods' src='http://new.regard-group.ru/template/icons/zoom.png' ><div class='div_for_stock_prods" + product['product_id']  +" hidden col-md-12'></div></div>"
					html += '</div>';
					if(searchPart){
						html += "<button class='btn btnForAddProductToOrder' data-product-id='" + product['product_id'] + "' style='width:100%'>Ավելացնել <i class='glyphicon glyphicon-arrow-up'></i></button"
					}
					return html;
				}
				function createProductListTaxFormat(product,searchPart){
					// Added By Dev for xml asop52f41v78x8z5
			    	var optionTaxAccount = '';
					for(var i = 0 ; i < taxAccounts.length ; i++){
						optionTaxAccount+= "<option value='" + (i+1) + "'>" + taxAccounts[i] + "</option>";
					}
			    	// end asop52f41v78x8z5
					let dprice = (Number(product['product_price']) - Number(product['product_price']) * (Number(product['product_discount_id'])/100)).toFixed(2);
					var html = '<div class="col-sm-12 relatedProduct" style="margin-top:20px" id="relatedPrd'+product['product_id']+'" data-price="'+dprice+'">';
					html += '<img src="./jos_product_images/'+product['product_thumb_image']+'" style="max-width: 115px; float: left;" alt="" title="'+product['product_desc']+'">';
					if(!searchPart){
						html += '<button data-dz-remove="" class="btn btn-danger btn-xs deleteRelated" data-price="'+dprice+'"><i class="glyphicon glyphicon-remove"></i></button>';
					}
					if(product['product_width'] > 0){
						html += '<div class="w-size">' + Number(product['product_width']).toFixed(2) +'</div>';
					}
					if(product['product_height'] > 0){
						html += '<div class="h-size">' + Number(product['product_height']).toFixed(2) +'</div>';
					}
					html += '<input type="hidden" name="relatedProduct[]" value="'+product['product_id']+'">';
					if(Number(product['product_discount_id']) > 0){
						html += '<span class="related_product_price">'+" <span class='original_price'>$ "+Number(product['product_price'])+"</span>   <span class='discounted_price'>$"+dprice+'</span>('+Number(product['product_discount_id'])+'%)</span>';
					} else {
						html += '<span class="related_product_price">$'+Number(product['product_price']).toFixed(2)+'</span>';
					}
					html += '<span><br>';
					html += '<span class="dollar-sign usdIconPricePart">$</span><input type="number" step="0.01" name="productNewPrice['+product['product_id']+']" class="productNewPrice productUsdPriceField usdShowField" value="'+dprice+'">';
					html += '<input type="number" step="0.01" name="productAmdPrice[' + product['product_id'] + ']" class="productAmdPriceField showForXML" data-prod-id="' + product['product_id'] + '" ><span class="amdIconPricePart showForXML"><img src="/template/icons/currency/3.png" style="height:15px" ></span>';
					html += '<br>';
					html += '<span class="productNewAlternatePrice"></span>';
					let product_name = product['product_name'].replace("'", "\'");
					let product_s_desc = product['product_s_desc'].replace("'", "\'");
					let product_attr = product['attribute'].replace("'", "\'");
					html += '<textarea rows="2" cols="18" name="related_name['+product['product_id']+']" title="'+product_name+ ' - '+ product_s_desc +'">'+product['product_sku'] + ' - ' + product_name +'</textarea>';
					html += "<textarea rows='4' cols='19' name='short_desc["+product['product_id']+"]'>"+product_s_desc;
					if(product['product_s_desc'] != '' && product['attribute'] != ''){
						html += ', ';
					}
					if(product['attribute'] != ''){
						html += product_attr;
					}
					html +=  "</textarea>";
					html += '</span>';
					html += '<div class="col-md-12" style="float:right"><input type="hidden" name="productIdCon[' + product['product_id'] + ']" value="' + product['product_id'] + '"><span class="showEachPrice" data-prod-id="' + product['product_id'] + '"></span> <input type="number" class="productQuantityAdded productQuantityField showForXML withoutArrowInputs" data-prod-id="' + product['product_id'] + '" name="productQuantity[' + product['product_id'] + ']" style="width:18%" placeholder="Հատ"><select class="productAddedTaxAccount showForXML form-control" style="padding: 3px 6px;width:150px;font-size: 12px;height:30px" name="productTaxAccount[' + product['product_id'] + ']">' + optionTaxAccount + '</select></div>';
					// Added By Hrach
					html += "<div class='col-md-12'><img data-prod-id='" + product['product_id'] + "' class='img_for_stock_prods' src='http://new.regard-group.ru/template/icons/zoom.png' ><div class='div_for_stock_prods" + product['product_id']  +" hidden col-md-12'></div></div>"
					html += '</div>';
					if(searchPart){
						html += "<button class='btn btnForAddProductToOrder' data-product-id='" + product['product_id'] + "' style='width:100%'>Add <i class='glyphicon glyphicon-arrow-up'></i></button"
					}
					return html;
				}
				$(document).on('click',".btnForAddProductToOrder",function(){
					var id = $(this).attr('data-product-id');
					$.ajax({
			            url: location.href,
			            type: 'post',
			            data: {
			                GetProductDataById: true,
			                id: id,
			            },
			            success: function(resp){
			            	resp = JSON.parse(resp);
			            	var html = createProductListTaxFormat(resp[0],false);
			            	$("#newlySelectedProducts").append(html)
							setTimeout(function(){
								var val = $("#relatedPrd"+resp[0]['product_id']).find(".productNewPrice");
								calculateNewPrice(val);
								showPrintXml();
							},500)
			            }
			        })
				})
				$(document).on("keypress",".inputskuCodeSearch",function(e){
					if(e.which == 13) {
				        e.preventDefault();
				        $(".skuCodeSearch").click();
				    }
				})
				$(document).on('click',".closeSkuSearchPart",function(){
					$(".skuCodeSearchResult").empty();
					$(".skuSearchResultMain").addClass("display-none")
				})
				$(document).on('click',".skuCodeSearch",function(){
					var val = $(".inputskuCodeSearch").val();
					if(val.length > 0){
						$.ajax({
				            url: location.href,
				            type: 'post',
				            data: {
				                GetProductsWithSKUCode: true,
				                val: val,
				            },
				            success: function(resp){
				            	resp = JSON.parse(resp);
				            	$(".skuCodeSearchResult").empty();
				            	if(resp.length > 0){
					            	for(var i = 0 ; i <= resp.length-1;i++){
					            		var html = createProductList(resp[i],true);
					            		$(".skuCodeSearchResult").append(html)
					            		$(".skuSearchResultMain").removeClass("display-none")
					            	}
				            	}
				            	else{
				            		alert("No result");
				            	}
				            }
				        })
					}
				})
				$(document).on('change',"#delivery_status",function(){
					checkPaymentTypeRequired();
					showPrintXmlText();
					if($(this).val() == 2){
						$("#operator_name").attr('required',true);
					}
					else{
						$("#operator_name").removeAttr('required')
					}
				})
				//
				// Added By Hrach
					$(document).on('click','.show_log_of_order',function(){
						$('#change_log').modal('show');
					})
					$(document).on('click','.show_log_of_order_sender',function(){
						$('#change_log_sender').modal('show');
					})
					$(document).on('click','.payment_info',function(){
						$('#payment_info_window').modal('show');
					})
				    $(document).on('click','.ModalForCountryPayments',function(){
				        $('#modal_for_country_payment').modal('show');
				        $(".country_payment_table_body").empty();
				        var country_id = $("#sender_country").val();
				        $.ajax({
				            url: location.href,
				            type: 'post',
				            data: {
				                GetPaymentForOrder: true,
				                country_id: country_id,
				            },
				            success: function(resp){
				            	resp = JSON.parse(resp);
				            	var html = '';
				            	for(var i = 0 ; i < resp.length ; i ++){
				            		var html="<tr>";
			                            html+="<td>";
			                            html+=resp[i]['id']
			                            html+="</td>";
			                            html+="<td>";
			                            html+=resp[i]['name_am']
			                            html+="</td>";
			                            html+="<td>";
			                            html+="<img src='" + resp[i]['icon'] + "'> "
			                            html+="</td>";
		                            html+="</tr>";
		                        	$(".country_payment_table_body").append(html);
				            	}
				            }
				        })
				    })
				    $(document).on('click','.show_log_of_order_action',function(){
				        $('#change_log_doing').modal('show');
				        $(".log_table_body").empty();
				        var order_id = $(this).data('order-id');
				        $(".for_order_number").html(order_id);
				        $.ajax({
				            url: location.href,
				            type: 'post',
				            data: {
				                getorderlog: true,
				                order_id: order_id,
				            },
				            success: function(resp){
				                if(resp.length > 5){
				                    resp = JSON.parse(resp);
				                    for(var i = 0 ; i < resp.order_log.length ; i++ ){
				                        var html="<tr>";
				                                html+="<td>";
				                                    html+= i+1
				                                html+="</td>";
				                                html+="<td>";
				                                    html+=resp.order_log[i].description
				                                html+="</td>";
				                                html+="<td>";
				                                    html+=resp.order_log[i].date
				                                html+="</td>";
				                                html+="<td>";
				                                if(resp.order_log[i].name_am == 'Վճարումը դեռ չկատարված'){
				                                    html+='Անավարտ';
				                                }
				                                else{
				                                    html+=resp.order_log[i].name_am
				                                }
				                                html+="</td>";
				                                html+="<td>";
				                                    html+=resp.order_log[i].username
				                                html+="</td>";
				                            html+="</tr>";
				                        $(".log_table_body").append(html);
				                    }
				                }
				            }
				        })
				    })
				//
				$('body').on('blur', '.delivery_time_manual_hour, .travel_time_end_hour', function(){
					var $self = $(this);
					if($self.val() != ""){
						if($self.val().length >= 2){
							$self.val($self.val().slice(0,2));
						}
						if($self.val().length == 1){
							$self.val('0' + $self.val());
						}
						if(!$self.val() || $self.val() == ""){
							$self.val("00");
						}
						if($self.val() > 23){
							$self.val(23);
						}
						var val1 = $self.val() != '' ? $self.val() : '00';
						var val2 = $($self.attr('data-sibling')).val() != '' ? $($self.attr('data-sibling')).val() : '00';
						var time_manual = val1 + ":"+ val2;
						$('#' + $self.attr('data-target')).val(time_manual);
					} else {
						$('#' + $self.attr('data-target')).val('');
					} 
				});
				
				$('body').on('change', '#delivery_date, #delivery_time', function(){
					showOtherDeliveries();
				});

				$('body').on('blur', '.delivery_time_manual_mins, .travel_time_end_mins', function(){
					var $self = $(this);
					if($self.val() != ""){
						if($self.val().length >= 2){
							$self.val($self.val().slice(0,2));
						}
						if($self.val().length == 1){
							$self.val('0' + $self.val());
						}
						if(!$self.val() || $self.val() == ""){
							$self.val("00");
						}
						if($self.val() > 59){
							$self.val(59);
						}
						var val1 = $($self.attr('data-sibling')).val() != '' ? $($self.attr('data-sibling')).val() : '00';
						var val2 = $self.val() != '' ? $self.val() : '00';
						var time_manual = val1 + ":"+ val2;
						$('#' + $self.attr('data-target')).val(time_manual);
					}
					if($($self.attr('data-sibling')).val() == ''){
						$('#' + $self.attr('data-target')).val('');
					}
				});
				// Added By Hrach
				$(document).on('click',".img_for_stock_prods",function(){
					var prod_id = $(this).data('prod-id');
					if ( $(".div_for_stock_prods"+prod_id).hasClass('hidden') ){
						$.ajax({
							type: 'post',
							url: location.href,
							data: {
								get_stock_prods: prod_id
							},
							success: function(resp){
								resp = JSON.parse(resp);
								if ( resp.length > 0 ){
									$(".div_for_stock_prods"+prod_id).removeClass('hidden')
									var html = '';
									for( var i = 0 ; i < resp.length ; i++ ){
										html += resp[i]['product_name'] + " - " + resp[i]['count'] + " հատ <br>";
									}
									$(".div_for_stock_prods"+prod_id).empty();
									$(".div_for_stock_prods"+prod_id).append(html);
								}
							}
						})
					}
					else{
						$(".div_for_stock_prods"+prod_id).addClass('hidden')
					}
				})
				existStockProduct();
				function existStockProduct(){
					var img_for_stock_prods = $(".img_for_stock_prods");
					if(img_for_stock_prods.length > 0){
						for(var i = 0 ; i < img_for_stock_prods.length; i++){
							var prod_id = $(img_for_stock_prods[i]).data('prod-id');
							$.ajax({
								type: 'post',
								url: location.href,
								data: {
									get_stock_prods: prod_id
								},
								async:false,
								success: function(resp){
									resp = JSON.parse(resp);
									if ( resp.length == 0 ){
										$(".div_for_stock_prods"+prod_id).parent().remove();
									}
								}
							})
						}
					}
				}
				function removeAttrb(){
					$("#delivery_date").removeAttr("required");
					$("#b_region").removeAttr("required");
					$("#product").removeAttr("required");
					$("#price").removeAttr("required");
					$("#currency").removeAttr("required");
					$("#deliverer").removeAttr("required");
					$("#delivery_reason").removeAttr("required");
				}

				function showBonusInfo(){
					$('#bonus_info').removeClass('hide_bonus_info');
				}

				function hideBonusInfo(){
					$('#bonus_info').removeClass('hide_bonus_info').addClass('hide_bonus_info');
					// $('#bonus_info').val('');
				}
				<?php 
				if(isset($orderData[0]["bonus_type"]) && $orderData[0]["bonus_type"] == 2){
					echo "removeAttrb();";
				}
				?>
				// $(document).on("change","input[name=bonus_type]",function(){
				// 	requiredFirstConnectField()
				// })
				// function requiredFirstConnectField(val){
				// 	var val = $("input[name=bonus_type]:checked").val();
				// 	if(val == 1){
				// 		$('#first_connect').attr('required','required');
				// 	}
				// 	else{
				// 		$('#first_connect').removeAttr('required','required');
				// 	}
				// }
				function addAttrb(){
					$("#delivery_date").attr("required","required");
					$("#b_region").attr("required","required");
					$("#price").attr("required","required");
					$("#currency").attr("required","required");
					$("#deliverer").attr("required","required");
					$("#delivery_reason").attr("required","required");
				}
                    var allRegions = <?=page::buildAllRegions()?>;
					function buildPartners(type,name){
						var allFlPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'flower' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;
						var allRTPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'travel' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;
						var owsp = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'ows' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;

						var phtml = '';
						jQuery("#partnersFiled").css("display","table-row");
						if(type == "flp"){
							for(var i=0;i < allFlPartners.length;i++){
								phtml += "<option value=\""+allFlPartners[i].value+"\" >"+allFlPartners[i].name+"</option>";
							}
						}else if(type == "rtp"){
							for(var i=0;i < allRTPartners.length;i++){
								phtml += "<option value=\""+allRTPartners[i].value+"\" >"+allRTPartners[i].name+"</option>";
							}
						}else if(type == "ows"){
							for(var i=0;i < owsp.length;i++){
								phtml += "<option value=\""+owsp[i].value+"\" >"+owsp[i].name+"</option>";
							}
						}else{
							jQuery("#partnersFiled").css("display","none");
						}
						jQuery("#partnerLable").html(name);
						jQuery("#sell_point_partner").html(phtml);
						phtml = "";
						ChangeFieldsForPartners(document.getElementById('sell_point_partner').value,document.getElementById('sell_point_partner').options[document.getElementById('sell_point_partner').selectedIndex].text);
					}
					// Added By Hrach
					function ChangeFieldsForPartners(id,name){
						var choosePartner = <?=json_encode(getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint`"))?>;
						for(var i=0;i < choosePartner.length;i++){
							if( choosePartner[i].name == name ){
								if( choosePartner[i].ispartner == 1 ){
									document.getElementById("sender_email").value = choosePartner[i].email ;
								}
							}
						}
					}
					function CheckPartners(id,name){
						ChangeFieldsForPartners(id,name);
					}
					document.getElementById("sell_point_partner").onchange = function(){
						CheckPartners(this.value,this.options[this.selectedIndex].text);
					}
					$(document).on('click',".img_for_info_div_mockup",function(){
						var sell_point_partner = document.getElementById('sell_point_partner').options[document.getElementById('sell_point_partner').selectedIndex].text
						var choosePartner = <?=json_encode(getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint`"))?>;
						var partnerInformation;
						for(var i=0;i < choosePartner.length;i++){
							if( choosePartner[i].name == sell_point_partner ){
								if( choosePartner[i].ispartner == 1 ){
									partnerInformation = choosePartner[i];
								}
							}
						}
						if(partnerInformation){
					        $('.partnerInfomockupdiv').html('');
					        let html = "<div class='selectedPartner'>";
					        html += "<h1><i>"+partnerInformation.name+"</i></h1>";
					        html += "<h4>"+partnerInformation.working_terms+"</h4>";
					        html += "<h5>"+partnerInformation.email+"</h5>";
					        html += "<h6>"+partnerInformation.phone+"</h6>";
					        html += "</div>";
					        $('.partnerInfomockupdiv').append(html);
					        $('.partnerInfomockupdiv').css('display', 'inline-block');
					        $('.blur_effect_div').css('filter', 'blur(15px)');
						}
					})
					$(document).mouseup(function(e)
				    {
				        var container = $(".partnerInfomockupdiv");
				        // if the target of the click isn't the container nor a descendant of the container
				        if (!container.is(e.target) && container.has(e.target).length === 0)
				        {
				            container.css('display', 'none');
				            $('.blur_effect_div').css('filter', 'blur(0)');
				        }
				    });
					//
					document.getElementById("sell_point").onchange = function(){
						buildPartners(this.value,this.options[this.selectedIndex].text);
					}
                    function buildRegions(type,el,activeItem)
                    {
			var selectedItem = "";
                        if (type == null) {
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
                            for(var i=0;i < allRegions.length;i++)
                            {
                                next = allRegions[i].region.code;
				
                                if (current != next && allRegions[i].region.name != "") {
					if (allRegions[i].region.code == activeItem) {
						selectedItem = "selected";
						
					}else{
						selectedItem = "";
					}
					console.log(activeItem);
                                    dhtml += "<option value=\""+allRegions[i].region.code+"\" "+selectedItem+">"+allRegions[i].region.name+"</option>";
                                    current = next;
                                }
                                
                            }
                            $("#b_region").html(dhtml);
                        }
						$('.street_info').html('');
                        if (type == 1){
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
			    
                            $("#b_street").html("");
                            $("#b_street").attr("disabled","disabled");
                            if (!el.value) {
                                $("#b_subregion").attr("disabled","disabled");
                                $("#b_subregion").html(dhtml);
                                return false;
                            }
                            $("#b_subregion").removeAttr("disabled");
                            for(var i=0;i < allRegions.length;i++)
                            {
                                if (allRegions[i].region.code == el.value) {
                                    if (allRegions[i].region.sub_region) {
                                        next = allRegions[i].region.sub_region.name;
					
                                        if (current != next && allRegions[i].region.sub_region.name != "") {
						if (allRegions[i].region.sub_region.code == activeItem) {
							selectedItem = "selected";
						}else{
							selectedItem = "";
						}
                                            dhtml += "<option value=\""+allRegions[i].region.sub_region.code+"\""+selectedItem+">"+allRegions[i].region.sub_region.name+"</option>";
                                            current = next;
                                        }
                                    }
                                }
                            }
                            $("#b_subregion").html(dhtml);
                        }
                        if (type == 2){
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
                            let all_regions = ['kotayq', 'lori', 'tavush', 'syunik', 'vayoc_dzor', 'armavir', 'shirak', 'ararat', 'aragatsotn', 'gexarquniq'];
							let all_streets = ['ajapnyak', 'avan', 'arabkir', 'davtashen', 'erebuni', 'kentron', 'malatia-sebastia', 'nor-norq', 'norq-marash', 'nubarashen', 'shengavit', 'qanaqer-zeytun'];

                            if (!el.value) {
                                $("#b_street").attr("disabled","disabled");
                                $("#b_street").html(dhtml);
                                return false;
                            }
                            $("#b_street").removeAttr("disabled");
							if($('#b_subregion').val() == 'all_regions'){
								for(var i=0;i < allRegions.length;i++)
								{
									if (all_regions.indexOf(allRegions[i].region.sub_region.code) != -1){
										if (allRegions[i].region.sub_region.street) {
											next = allRegions[i].region.sub_region.street.name;
											if (current != next && allRegions[i].region.sub_region.street.name != "") {
												dhtml += "<option data-zone='"+allRegions[i].region.sub_region.street.zone+"' data-code='"+allRegions[i].region.sub_region.street.code+"' data-region='"+allRegions[i].region.sub_region.code+"' value=\""+allRegions[i].region.sub_region.street.code+"\">"+allRegions[i].region.sub_region.street.name;
												dhtml += " (" + allRegions[i].region.sub_region.name;
												if(allRegions[i].region.sub_region.street.zone != null && allRegions[i].region.sub_region.street.zone > 0){
													dhtml += ", Zone "+ allRegions[i].region.sub_region.street.zone;
												}
												dhtml += " ) ";
												dhtml += "</option>";
												current = next;
											}
										}
									}
								}
							} else if($('#b_subregion').val() == 'all_streets'){
								for(var i=0;i < allRegions.length;i++)
								{
									if (all_streets.indexOf(allRegions[i].region.sub_region.code) != -1){
										if (allRegions[i].region.sub_region.street) {
											next = allRegions[i].region.sub_region.street.name;
											if (current != next && allRegions[i].region.sub_region.street.name != "") {
												dhtml += "<option data-zone='"+allRegions[i].region.sub_region.street.zone+"' data-code='"+allRegions[i].region.sub_region.street.code+"' data-region='"+allRegions[i].region.sub_region.code+"' value=\""+allRegions[i].region.sub_region.street.code+"\">"+allRegions[i].region.sub_region.street.name;
												dhtml += " (" + allRegions[i].region.sub_region.name;
												if(allRegions[i].region.sub_region.street.zone != null && allRegions[i].region.sub_region.street.zone > 0){
													dhtml += ", Zone "+ allRegions[i].region.sub_region.street.zone;
												}
												dhtml += " ) ";
												dhtml += "</option>";
												current = next;
											}
										}
									}
								}
							} else {

								for(var i=0;i < allRegions.length;i++)
								{
									if (allRegions[i].region.sub_region.code == el.value) {
										if (allRegions[i].region.sub_region.street) {
											next = allRegions[i].region.sub_region.street.name;
											if (current != next && allRegions[i].region.sub_region.street.name != "") {
												dhtml += "<option data-zone='"+allRegions[i].region.sub_region.street.zone+"' data-code='"+allRegions[i].region.sub_region.street.code+"' value=\""+allRegions[i].region.sub_region.street.code+"\">"+allRegions[i].region.sub_region.street.name;
												if(allRegions[i].region.sub_region.street.old_name != null && allRegions[i].region.sub_region.street.old_name != ''){
													dhtml += " ("+ allRegions[i].region.sub_region.street.old_name +") ";
												}
												dhtml += "</option>";
												current = next;
											}
										}
									}
								}
							}
							$('#b_street').selectpicker('destroy');
							$('#b_street').html(dhtml);
							loadSelect(dhtml);
                            // $("#b_street").html(dhtml);
							loadOrganisationsTypes(true);
                        }
                        return false;
                    }
                    //buildRegions(null,null,<?=(isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : "null"?>);
					function loadSelect(dhtml){
						setTimeout( function(){
							$('#b_street').selectpicker('render');
						}, 500)
					}
					$('body').on('change', '#b_street', function(){
						let reg = $('#b_subregion').val();
						let sel_reg = $('#b_street').find('option:selected').attr('data-region');
						let zone = $('#b_street').find('option:selected').attr('data-zone');
						let code = $('#b_street').find('option:selected').attr('data-code');
						if(reg == 'all_regions' || reg == 'all_streets' || sel_reg != undefined){
							$('#b_subregion').val(sel_reg);	
						}
						if(zone > 0){
							$.ajax({
								type: 'post',
								url: location.href,
								data: {
									get_street_info: code
								},
								success: function(resp){
									let resp_data = JSON.parse(resp);
									let street_data = resp_data['street'];
									let street_html = '';
									// if(street_data != undefined && street_data.length > 0){
										street_html += 'Zone ' + street_data['zone'] + ', ';
										if(resp_data['zone'] != null){
											street_html += 'price ' + resp_data['zone']['price'] + ', ';
										}
										street_html += 'KM: ' + street_data['distance'] + ', ';
										street_html += 'DT: ' + street_data['delivery_time'];
										if(street_data['wiki_url'] != null && street_data['wiki_url'] != ''){
											street_html += ', <a target="blank" href="'+street_data['wiki_url']+'">Wiki </a>';
										}
										if(street_data['coordinates'] != null && street_data['coordinates'] != ''){
											street_html += ', <a target="blank" href="https://www.google.com/maps/dir/Yervand+Kochar+Street,+Yerevan,+Armenia/'+street_data['coordinates']+'/@40.2000815,44.4699913,12z/data=!3m1!4b1!4m12!4m11!1m5!1m1!1s0x406abcf595178223:0xf074b89337f1809!2m2!1d44.5177361!2d40.1713366!1m3!2m2!1d45.18!2d40.38!3e0">MAP </a> ';
										}
										$('.street_info').html(street_html);
									// }
									
								}
							})
						} else {
							$('.street_info').html('');
						}
					});
					function loadOrganisationsTypes(change = false){
						// let organisationsTypes = <?= json_encode(getwayConnect::getwayData('SELECT * FROM organisation_types where active = 1')); ?>;
						$('.organisation-type-tr').removeClass('hidden-organisation');
						if(change){
							$('#organisation_types').val('');
							$('#organisations').html('<option><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>');
						}
						// let ohtml = '<select id="organisations">';
						// ohtml += '<option>Select Organisation Type</option>';
						// organisationsTypes.forEach(organisationType => {
						// 	ohtml += '<option value="'+organisationType['id']+'">'+organisationType['name']+'</option>';
						// });
						// ohtml += '</select>'
						// $('#organisation_types').html(ohtml)
					}

					$('body').on('change', '#organisation_types', function(){
						let otype = $(this).val();
						let oregion = $('#b_subregion').val();
						$.ajax({
							type: 'post',
							url: location.href,
							data: {
								get: 'getOrganisations',
								type: otype,
								region: oregion
							},
							success: function(resp){
								let odatas = JSON.parse(resp);
								if(odatas.length > 0){
									$('.organisations-tr').removeClass('hidden-organisation');
									let ohtml = '<option><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>';
									odatas.forEach(odata => {
										console.log(odata)
										ohtml += '<option value="'+odata['id']+'" data-street="'+odata['street']+'" data-address="'+odata['address']+'" data-entrance="'+odata['entrance']+'" data-floor="'+odata['floor']+'" data-door_code="'+odata['door_code']+'">'+odata['name_am']+'</option>';
									});
									$('#organisations').html(ohtml);
								}
							}
						})
					})

					$('body').on('change', '#organisations', function(){
						let oselected = $('#organisations').find('option:selected');
						let address = oselected.attr('data-address');
						let entrance = oselected.attr('data-entrance');
						let floor = oselected.attr('data-floor');
						let door_code = oselected.attr('data-door_code');
						let oval = oselected.attr('data-street');
						// $('#b_street').val(oval);
						$('#b_street').selectpicker('val', oval);
						$('#receiver_address').val(address);
						$('#receiver_floor').val(floor);
						$('#receiver_entrance').val(entrance);
						$('#receiver_door_code').val(door_code);
					});

                    function check(el,type)
                    {
                        if (type == 1) {
                            if (el.value.search(/^[0-9.]+$/) == -1 && el.value != "") {
                               el.value = /[0-9]*/.exec(el.value);
                               alert("Only Numeric allowed!");
                                return false;
                            }
                        }
                        if (type == 2) {
                //             if (!el.value) {
                //                 $("#"+el.id).css("border-color","#FF0000");
                //                 return false;
                //             }else{
				// $("#"+el.id).css("border-color","#CCC");
			    // }
                        }
                        return true;
                    }
                    function checkAll(el)
                    {
                        var cont = true;
						let ord_src_val = $('#order_source').val();
						let ord_src_opt = $('#order_source_optional').val();
						let send_phone = $('#sender_phone').val();

						if (document.getElementById("price").value.search(/^[0-9.]+$/) == -1 && document.getElementById("price").value != "") {
						   alert("Only Numeric allowed!");
						    $("#price").css("border-color","#FF0000");
							cont = false;
						}

                        $('[required]').each(function(){
                                if (this.value == "") {
                                    alert("Important fields muust be filled!")
				    $("#"+this.id).css("border-color","#FF0000");
                                    cont = false;
                                    alert("REQUIRED FIELDS NOT SET");
                                    return false;
                                }
                            });

					<?php if(!in_array($userData[0]['id'], $travel_operators)){ ?>

						if(['13', '14', '18'].indexOf(ord_src_val) > -1){
							// if(send_phone == '' || ord_src_opt == '' || /(^\+[0-9]{6,})(,?\s?\+?[0-9]{6,})*/.exec(ord_src_opt) == null || /([a-zA-Z\/	])*/.exec(ord_src_opt) != null){
							if(send_phone == '' ){
								cont = false;
								alert('Please enter sender phone number!');
								return false;
							} else if(ord_src_opt == ''){
								cont = false;
								alert('Please enter order source optional!');
								return false;
							}
						}
					<?php } ?>
						var validSenderPhone = checkSenderPhoneValidate();
						if(validSenderPhone != ''){
							$(".senderPhoneMsg").html(validSenderPhone)
							alert('Please Enter Valid Number In Sender Phone')
							return false;
						}
						else{
							$(".senderPhoneMsg").html('')
						}
						var validIpFormat = checkValidIpFormat();
						if(validIpFormat == false){
							alert('Incorrect IP format')
							return false;
						}
						var validReceiverPhone = checkReceiverPhoneValidate();
						if(validReceiverPhone != ''){
							$(".receiverPhoneMsg").html(validReceiverPhone)
							alert('Please Enter Valid Number In Receiver Phone')
							return false;
						}
						else{
							$(".receiverPhoneMsg").html('')
						}
                        if (cont) {
                            return true;
                        }else{
                            return false;
                        }
                        
                    }
                    if ($('[addon="date"]')) {
			$('[addon="date"]').datepicker({format: 'yyyy-mm-dd'});
		    }
		    var orderId = "<?=$orderId;?>";
			function startTimer() {
					var my_timer = document.getElementById("my_timer");
					if(my_timer){
						var alert_sound = document.getElementById("alertSound");
						var time = my_timer.innerHTML;
						var arr = time.split(":");
						var h = arr[0];
						var m = arr[1];
						var s = arr[2];
						if (s == 0) {
						  if (m == 0) {
							if (h == 0) {
							  alert_sound.play();
							  return;
							}
							h--;
							m = 60;
							if (h < 10) h = "0" + h;
						  }
						  m--;
						  if (m < 10) m = "0" + m;
						  s = 59;
						}
						else s--;
						if (s < 10) s = "0" + s;
						document.getElementById("my_timer").innerHTML = h+":"+m+":"+s;
						setTimeout(startTimer, 1000);
					}
				}
				window.timerStarted = false;
				window.firstEditor = false;
				window.unlockEdit = false;
                function pull()
				{
					$.get("pull.php?pid="+orderId, function (get_data){
						//console.log(get_data);
						if($("#editorOperator").html())
						{
							$("input,select,textarea").prop("disabled", true);
							if(!firstEditor){
								firstEditor = get_data.o.charAt(0).toUpperCase()+get_data.o.slice(1);
							}else{
								if(firstEditor != (get_data.o.charAt(0).toUpperCase()+get_data.o.slice(1))){
								 location.reload();
									if(!unlockEdit){
										//$("#btnSave").css("display","inline-block");
										//$("#btnReset").css("display","inline-block");
										unlockEdit = true;
									}else{
										unlockEdit = false;
									}
								}
								
							}
							firstEditor = get_data.o.charAt(0).toUpperCase()+get_data.o.slice(1);
							$("#editorOperator").html(get_data.o.charAt(0).toUpperCase()+get_data.o.slice(1));
							if(!unlockEdit){
								$("#btnSave").css("display","none");
								$("#btnReset").css("display","none");
							}
						}else{
							if(!timerStarted){
								startTimer();
								timerStarted = true;
							}
						}
					});
				}
				function select_delivery_type(jObj){
					if(jObj.find('option:selected').attr('data-car')){
						$("input[name=delivery_type][value=" + jObj.find('option:selected').attr('data-car') + "]").prop('checked', true);
					}
					showOtherDeliveries();
				}
				var previewNode = document.querySelector("#preview_template");
				previewNode.id = "";
				var previewTemplate = previewNode.parentNode.innerHTML;
				previewNode.parentNode.removeChild(previewNode);
				var drop_picture = new Dropzone("div#my-awesome-dropzone",{ 
					<?php
					if(isset($_REQUEST["orderId"]))
					{
						?>
							url: "./upload_image.php?order_id=<?=$_REQUEST['orderId']?>",
						<?php
					}
					else{
						?>
							url: "./upload_image.php",
						<?php
					}
					?>
					thumbnailWidth:50,
					thumbnailHeight:50,
					uploadMultiple:true,
					parallelUploads: 1,
					previewTemplate: previewTemplate,
					previewsContainer: "#previews",
					<?php
					if(isset($_REQUEST["orderId"]))
					{
					?>
					init: function() {
				        thisDropzone = this;
				        // <!-- 4 -->
				        $.get('upload_image.php?order_id=<?=$_REQUEST["orderId"];?>', function(data) {
				 			var iki = 0;
				            // <!-- 5 -->
				            $.each(data, function(key,value){
				                var mockFile = { name: value.name, size: value.size , note: value.note, product_desc: value.product_desc, price: value.price,tax_quantity:value.tax_quantity,tax_price_amd:value.tax_price_amd,tax_account_id:value.tax_account_id};
				                thisDropzone.options.addedfile.call(thisDropzone, mockFile);
				                thisDropzone.options.thumbnail.call(thisDropzone, mockFile, "product_images/" + value.path + "/"+value.name);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-filenote]")[iki].setAttribute("name",mockFile.name);
    							// Added By Dev for xml asop52f41v78x8z5
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-amdprice]")[iki].setAttribute("name","productamdprice["+mockFile.name+"]");
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-productquantity]")[iki].setAttribute("name","productquantity["+mockFile.name+"]");
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-amdeachprice]")[iki].setAttribute("data-prod-id",mockFile.name);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-productquantity]")[iki].setAttribute("data-prod-id",mockFile.name);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-producttaxid]")[iki].setAttribute("name","producttaxid["+mockFile.name+"]");
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-amdprice]")[iki].setAttribute("value",mockFile.tax_price_amd);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-amdprice]")[iki].setAttribute("data-prod-id",mockFile.name);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-productquantity]")[iki].setAttribute("value",mockFile.tax_quantity);
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-producttaxid]")[iki].setAttribute("valuechoose",mockFile.tax_account_id);
				                // end asop52f41v78x8z5
				                thisDropzone.previewsContainer.querySelectorAll("[data-dz-filenote]")[iki].setAttribute("value",mockFile.note);
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-fileid]")[iki].setAttribute("name","image_id["+mockFile.name+"]");
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-fileid]")[iki].setAttribute("value",mockFile.name);
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-productdesc]")[iki].setAttribute("name","productdesc["+mockFile.name+"]");
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-productdesc]")[iki].value = mockFile.product_desc;
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-productprice]")[iki].setAttribute("name","productprice["+mockFile.name+"]");
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-productexchange]")[iki].setAttribute("name","productexchange["+mockFile.name+"]");
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-productprice]")[iki].setAttribute("value",mockFile.price);
								thisDropzone.previewsContainer.querySelectorAll("[data-dz-uploadprogressBar]")[iki].style.display='none';
								iki++;
				                 
				            });
				        });
				    }
				    <?php
					}
					?>
				});
				drop_picture.on('success',function(data){
					if(data.xhr.response){
						setTimeout(function(){
							showPrintXml();
						},500)
						var v = JSON.parse(data.xhr.response);
						data.previewElement.querySelector("[data-dz-filenote]").setAttribute("name",v.id);
						data.previewElement.querySelector("[data-dz-fileid]").setAttribute("name","image_id["+v.id+"]");
						data.previewElement.querySelector("[data-dz-fileid]").setAttribute("value",v.name);
    					// Added By Dev for xml asop52f41v78x8z5
    					data.previewElement.querySelector("[data-dz-amdprice]").setAttribute("name","productamdprice["+v.id+"]");
    					data.previewElement.querySelector("[data-dz-amdprice]").setAttribute("data-prod-id",v.id);
    					data.previewElement.querySelector("[data-dz-productquantity]").setAttribute("name","productquantity["+v.id+"]");
    					data.previewElement.querySelector("[data-dz-amdeachprice]").setAttribute("data-prod-id",v.id);
    					data.previewElement.querySelector("[data-dz-productquantity]").setAttribute("data-prod-id",v.id);
    					data.previewElement.querySelector("[data-dz-producttaxid]").setAttribute("name","producttaxid["+v.id+"]");
    					// end asop52f41v78x8z5
						data.previewElement.querySelector("[data-dz-productdesc]").setAttribute("name","productdesc["+v.id+"]");
						data.previewElement.querySelector("[data-dz-productprice]").setAttribute("name","productprice["+v.id+"]");
						data.previewElement.querySelector("[data-dz-productexchange]").setAttribute("name","productexchange["+v.id+"]");
					}
				});
				drop_picture.on('removedfile',function(data){
					//console.log(data.previewElement.querySelector("[data-dz-fileid]").value)
					jQuery.ajax( "upload_image.php?remove="+data.previewElement.querySelector("[data-dz-fileid]").value+"&order_id=<?=isset($_REQUEST["orderId"]) ? $_REQUEST["orderId"]: '';?>")
					 .done(function() {
					 	//alert('DONE');
					 })
				});
				
				pull();
				setInterval(pull,8000);

				$(document).ready(function(){
					if($('#b_region').val() == ''){
						$('#b_region').val(1);
						$('#b_region').trigger('change');
					}
					if($('#order_source').val() != ''){
						checkOrderSource();
					}
					if($('#delivery_status').val() != ''){
						checkNotes();
					}
					if($('#deliverer').val() == ''){
						$('#deliverer').val(7);
						$('[id="Other"]').prop('checked', true);
					}
					if($('#delivery_status').val() == ''){
						$('#delivery_status').val(2);
					}
					if($('[id="order_source "]').val() == ''){
						$('[id="order_source "]').val(11);
					}
					if($('#delivery_reason').val() == ''){
						// $('#delivery_reason').val(24);
					}
					if($('#option1').prop('checked') == false && $('#option2').prop('checked') == false){
						$('#option3').prop('checked', true);
					}
					if($('#flourist').val() == ''){
						$('#flourist').val(13);
					}
					$('#confirmed').on('change', function(e){
						e.preventDefault();

						var $self = $(this);
						var answer = confirm("<?=(isset($constants['CONFIRMED_TEXT'])) ? $constants['CONFIRMED_TEXT'] : 'CONFIRMED_TEXT';?>");
						if(answer){
							$('#confirmed').prop('checked', true );
							// $('#confirmed').prop('checked', true );
							// $("#confirmed").attr('value', 1);
							// console.log($('#confirmed').is(':checked'))
							// console.log($(this).is(':checked'));
						} else {
							$('#confirmed').prop('checked', false );
						}
					})
					if($('#b_subregion').val() != undefined && $('#b_subregion').val() != 0){
						loadOrganisationsTypes();
					}
					showOtherOrders();
					showOtherDeliveries();
				})
	$('body').on('change', '#order_source', function(){
		checkOrderSource();
	})
	function checkOrderSource(){
		if(['13', '14', '18'].indexOf($('#order_source').val()) > -1){
			$('#order_source_optional').attr('pattern', "(^\\+[0-9]{6,})(,?\\s?\\+?[0-9]{6,})*");
		} else {
			$('#order_source_optional').attr('pattern', "*");
		}
	}
	$('body').on('change', '#delivery_status', function(){
		checkNotes();
	})
	function checkNotes(){
		if(['4', '7', '8', '9'].indexOf($('#delivery_status').val()) > -1){
			$('#comment').attr('required', true);
		} else {
			$('#comment').removeAttr('required');
		}
	}
	$('#parent_categories').on('change', function(){
		var val = $(this).val();
		if(val != ""){
			$('#related_category').removeClass('hidden');
			$.ajax({
				url: "./products/ajax.php?getRelatedCategories=1&parent=" + val,
				type: "GET",
				success: function(response){
					$('#relatedProducts').selectpicker('destroy');
					newly_selected = [];
					$("#relatedProducts").html('');
					$('#relatedProductsDiv').addClass('hidden');
					var html = "<option value=''><?=(defined('SELECT_FROM_LIST')) ?  SELECT_FROM_LIST : 'SELECT_FROM_LIST';?>:</option>";
					var results = JSON.parse(response);
					results.forEach(category => {
						let name = category['category_name'].replace("'", "\'");
						html+= "<option value='"+category['category_id']+"'>"+name+"</option>";
					})
					$('#related_category').html(html);
				}
			})
		} else {
			$('#related_category').addClass('hidden');
		}
	});
	$('#related_category').on('change', function(){
		var val = $(this).val();
		if(val != ""){
			$.ajax({
				url: "./products/ajax.php?getRelatedProducts=1&parent=" + val,
				type: "GET",
				success: function(response){
					$('#relatedProducts').selectpicker('destroy');
					newly_selected = [];
					var result = JSON.parse(response);
					$("#relatedProducts").html('');
					if(result.length){
						product_results = [];
						result.forEach(element => {
							if(!$('#relatedPrd'+element.product_id).length){
								product_results[element.product_id] = element;
								let name = element.product_name.replace("'", "\'");
								var html = "<option value='"+element.product_id+"'>"+element.product_sku + " - "+ name;
								if(element.product_publish != 'Y'){
									html += "&nbsp;&nbsp  (Unpublished)";
								}
								html += "</option>";
								$('#relatedProducts').append(html);
							}
						})
						addSelectPicker();
						$('#relatedProducts').selectpicker('val', 'Test');
					} else {
						$('#relatedProducts').selectpicker('destroy');
						newly_selected = [];
						$('#relatedProductsDiv').addClass('hidden');			
					}
				}
			})

			
			// checkRelated();
		} else {
			$('#relatedProducts').html('');
			$('#relatedProductsDiv').addClass('hidden');
		}
	});
	function addSelectPicker(){
		setTimeout(() => {
			$('#relatedProductsDiv').removeClass('hidden');
			$('#relatedProducts').selectpicker('render').selectpicker('val', product_results);
		}, 500);
	}

	$('body').on('change', '#relatedProducts', function(){
    	// Added By Dev for xml asop52f41v78x8z5
    	var optionTaxAccount = '';
		for(var i = 0 ; i < taxAccounts.length ; i++){
			optionTaxAccount+= "<option value='" + (i+1) + "'>" + taxAccounts[i] + "</option>";
		}
    	// end asop52f41v78x8z5
		var selecteds = $(this).val();
		if(selecteds !== null){
			// $('#newlySelectedProducts').html('');
			selecteds.forEach(selected => {
				if(typeof product_results[selected] != undefined){
					if(!$('#relatedPrd'+selected).length){

						var prd = product_results[selected];
						let dprice = (Number(prd['product_price']) - Number(prd['product_price']) * (Number(prd['product_discount_id'])/100)).toFixed(2);
						var html = '<div class="col-sm-12 relatedProduct" id="relatedPrd'+prd['product_id']+'" data-price="'+dprice+'">';
						// html += '<span style="display: inline-block;">';
						html += '<a data-magnify="gallery" data-caption="'+prd['product_desc']+'" href="./jos_product_images/'+prd['product_thumb_image']+'">  <img src="./jos_product_images/'+prd['product_thumb_image']+'" style="max-width: 115px; float: left;" alt="" title="'+prd['product_desc']+'"></a>';
						html += '<button data-dz-remove="" class="btn btn-danger btn-xs deleteRelated" data-price="'+dprice+'"><i class="glyphicon glyphicon-remove"></i></button>';
						if(prd['product_width'] > 0){
							html += '<div class="w-size">' + Number(prd['product_width']).toFixed(2) +'</div>';
						}
						if(prd['product_height'] > 0){
							html += '<div class="h-size">' + Number(prd['product_height']).toFixed(2) +'</div>';
						}
						// html += '</span>';
						html += '<input type="hidden" name="relatedProduct[]" value="'+prd['product_id']+'">';
						if(Number(prd['product_discount_id']) > 0){
							html += '<span class="related_product_price">'+" <span class='original_price'>$ "+Number(prd['product_price'])+"</span>   <span class='discounted_price'>$"+dprice+'</span>('+Number(prd['product_discount_id'])+'%)</span>';
						} else {
							html += '<span class="related_product_price">$'+Number(prd['product_price']).toFixed(2)+'</span>';
						}
						html += '<span><br>';
						html += '<span class="dollar-sign usdIconPricePart">$</span><input type="number" step="0.01" name="productNewPrice['+prd['product_id']+']" class="productNewPrice productUsdPriceField usdShowField" value="'+dprice+'">';
    					// Added By Dev for xml asop52f41v78x8z5
						html += '<input type="number" step="0.01" name="productAmdPrice[' + prd['product_id'] + ']" class="productAmdPriceField showForXML" data-prod-id="' + prd['product_id'] + '" ><span class="amdIconPricePart showForXML"><img src="/template/icons/currency/3.png" style="height:15px" ></span>';
						// end asop52f41v78x8z5
						html += '<br>';
						html += '<span class="productNewAlternatePrice"></span>';
						let product_name = prd['product_name'].replace("'", "\'");
						let product_s_desc = prd['product_s_desc'].replace("'", "\'");
						let product_attr = prd['attribute'].replace("'", "\'");
						html += '<textarea rows="2" cols="18" name="related_name['+prd['product_id']+']" title="'+product_name+ ' - '+ product_s_desc +'">'+prd['product_sku'] + ' - ' + product_name +'</textarea>';
						html += "<textarea rows='4' cols='19' name='short_desc["+prd['product_id']+"]'>"+product_s_desc;
						if(prd['product_s_desc'] != '' && prd['attribute'] != ''){
							html += ', ';
						}
						if(prd['attribute'] != ''){
							html += product_attr;
						}
						html +=  "</textarea>";
						html += '</span>';
    					// Added By Dev for xml asop52f41v78x8z5
						html += '<div class="col-md-12" style="float:right"><input type="hidden" name="productIdCon[' + prd['product_id'] + ']" value="' + prd['product_id'] + '"><span class="showEachPrice" data-prod-id="' + prd['product_id'] + '"></span> <input type="number" class="productQuantityAdded productQuantityField showForXML withoutArrowInputs" data-prod-id="' + prd['product_id'] + '" name="productQuantity[' + prd['product_id'] + ']" data-prod-id="' + prd['product_id'] + '" style="width:18%" placeholder="Հատ"><select class="productAddedTaxAccount showForXML form-control" style="padding: 3px 6px;width:150px;font-size: 12px;height:30px" name="productTaxAccount[' + prd['product_id'] + ']">' + optionTaxAccount + '</select></div>';
						// end asop52f41v78x8z5
						// Added By Hrach
						html += "<div class='col-md-12'><img data-prod-id='" + prd['product_id'] + "' class='img_for_stock_prods' src='http://new.regard-group.ru/template/icons/baxadrutyun.jpg' style='height:30px' ><div class='div_for_stock_prods" + prd['product_id']  +" hidden col-md-12'></div></div>"
						html += '</div>';
						$('#newlySelectedProducts').append(html);
						updatePrice(dprice, 1);
						calculateNewPrice($("#relatedPrd"+prd['product_id']).find(".productNewPrice"));
					}
				}
				calculatePrice();
				
			});
		}
	});

	$('body').on('click', '.deleteRelated', function(e){
		e.preventDefault();
		var $self = $(this);
		var answer = confirm('Are you sure');
		updatePrice($self.closest('.relatedProduct').find('.productNewPrice').val(), 0);
		if(answer){
			$self.closest('.relatedProduct').remove();
		}
	})

	var currencies = {
		"1": 'USD',
		"2": 'RUB',
		"3": 'AMD',
		"4": 'EUR',
		"5": 'GBP',
		"6": 'IRR' 
	}
	var rate = {
		'USD': <?= $exchange_rate['USD'] ?>,
		'RUB': <?= $exchange_rate['RUB'] ?>,
		'EUR': <?= $exchange_rate['EUR'] ?>,
		'GBP': <?= $exchange_rate['GBP'] ?>,
		'IRR': <?= $exchange_rate['IRR'] ?>,
		'AMD': 1
	};
	function calculateMulticurrencyPrice(){
		var val = $('#price').val();
		var currency = $("#currency option:selected" ).text();
		var result = 0;
		if(currency == 'RUB'){
			result = '$ ' + Math.ceil(val * (rub/usd)) + ' | ' + ' G ' + Math.ceil(val * (rub/gbp)) + ' | ' + ' € ' + Math.ceil(val * (rub/eur)) + ' | ' + ' Դ ' + Math.ceil(val*(rub/amd));
		}
		if(currency == 'EUR'){
			result = '$ ' + Math.ceil(val * (eur/usd)) + ' | ' + ' G ' + Math.ceil(val * (eur/gbp)) + ' | ' + ' R ' + Math.ceil(val * (eur/rub)) + ' | ' + ' Դ ' + Math.ceil(val*(eur/amd));
		}
		if(currency == 'USD'){
			result = ' € ' + Math.ceil(val * (usd/eur)) + ' | ' + ' G ' + Math.ceil(val * (usd/gbp)) + ' | ' + ' R ' + Math.ceil(val * (usd/rub)) + ' | ' + ' Դ ' + Math.ceil(val*(usd/amd));
		}
		if(currency == 'GBP'){
			result = '$ ' + Math.ceil(val * (gbp/usd)) + ' | ' + ' € ' + Math.ceil(val * (gbp/eur)) + ' | ' + ' R ' + Math.ceil(val * (gbp/rub)) + ' | ' + ' Դ ' + Math.ceil(val*(gbp/amd));
		}
		if(currency == 'AMD'){
			result = '$ ' + Math.ceil(val * (amd/usd)) + ' | ' + ' € ' + Math.ceil(val * (amd/eur)) + ' | ' + ' R ' + Math.ceil(val * (amd/rub)) + ' | ' + ' G ' + Math.ceil(val*(amd/gbp));
		}
		$(".multicurrency_values").html(result);
	}
	function calculatePrice(){
		let total = 0;
		if($('.relatedProduct').length){
			$('.productNewPrice').each((index, element) => {
				let price = Number($(element).val());
				total += price;
			});	
			$('.new_price').css('display', 'inline');
			$('.new_price_currency').css('display', 'inline');
		} else {
			$('.new_price').css('display', 'none');
			$('.new_price_currency').css('display', 'none');
		}

		if($('.other_product_price').length){
			$('.other_product_price').each((ind, other_elem) => {
				let other_price = Number($(other_elem).val());
				total += other_price;
			})
			$('.new_price').css('display', 'inline');
			$('.new_price_currency').css('display', 'inline');
		}
		$('.new_price').css('display', 'inline');
		$('.new_price').html(total);

	}
	$(document).ready(function(){
		setTimeout(function(){
			calculatePrice();
			$('.productNewPrice').each((index, elem) => {
				calculateNewPrice($(elem));
			})
		}, 2000);
	})
	$('body').on('change', '.other_product_price', function(){
		calculatePrice();
	})
	$('body').on('change', '.productNewPrice', function(){
		calculateNewPrice($(this));
	})
	function calculateNewPrice(elem){
		let price = parseFloat($(elem).val());
		if(price > 0){
			let price_amd = Math.round((price * rate['USD']).toFixed(2));
			let price_eur = Math.round((price_amd / rate['EUR']).toFixed(2));
			let price_rub = Math.round((price_amd / rate['RUB']).toFixed(2));
			let price_gbp = Math.round((price_amd / rate['GBP']).toFixed(2));
			let html = '&#8364;'+ price_eur +', ';
			html += 'R'+ price_rub +', ';
			html += 'G'+ price_gbp +', ';
			html += 'Դ'+ price_amd;
			elem.siblings('.productNewAlternatePrice').html(html);
		} else {
			elem.siblings('.productNewAlternatePrice').html('');
		}
	}
	$(document).on("change",".other_product_price",function(){
		var nameFile = $(this).attr('name');
		var idFile = nameFile.split('[')[1].slice(0,-1);
		var price = $(this).val();
		calculateUploadedPriceExchange(price,idFile)
	})
	function setUploadedExchangePrices(){
		var other_product_price = $(".other_product_price");
		for(var i = 0 ; i < other_product_price.length ; i++){
			var nameFile = $(other_product_price[i]).attr('name');
			var idFile = nameFile.split('[')[1].slice(0,-1);
			var price = $(other_product_price[i]).val();
			calculateUploadedPriceExchange(price,idFile)
		}
	}
	function calculateUploadedPriceExchange(price,idFile){
		if(price > 0){
			let price_amd = Math.round((price * rate['USD']).toFixed(2));
			let price_eur = Math.round((price_amd / rate['EUR']).toFixed(2));
			let price_rub = Math.round((price_amd / rate['RUB']).toFixed(2));
			let price_gbp = Math.round((price_amd / rate['GBP']).toFixed(2));
			let html = '&#8364;'+ price_eur +', ';
			html += 'R'+ price_rub +', ';
			html += 'G'+ price_gbp +', ';
			html += 'Դ'+ price_amd;
			$(".productExchangePart[name='productexchange[" + idFile + "]']").html(html);
		}
	}
	function updatePrice(price, action){
		let current_price = parseFloat($('.new_price').first().html());
		price = parseFloat(price);
		let new_price = 0;
		if(action == 1){
			new_price =  current_price + price;
		} else {
			new_price = current_price - price;
		}
		$('.new_price').html(new_price.toFixed(2));
		$('.new_price').css('display', 'inline');
		$('.new_price_currency').css('display', 'inline');
	}

	let order_statuses = [
		'', 'Հաստատված', 'Անավարտ', 'Առաքված', 'Չեղյալ', 'Բաց թողնված', 'Ճանապարհին', 'Վերադարձրած', 'Կոմունիկացիա', 'Դուբլիկատ', 'Ավտոմատ', 'Հրաժարվել է առաքել', 'Պատրաստ', 'Հաստատված առաքիչի կողմից'
	]

	$('body').on('change', '#receiver_phone, #sender_phone, #sender_email', function(){
		showOtherOrders();
	});
	let delivery_times = [
		'8-10',
		'9-12',
		'12-15',
		'15-18',
		'18-21',
		'21-24',
		'00-00',
		'00.00-9.00',
		'08.00-15.00',
		'15.00-19.00',
		'19.00-00.00'
	]
	function showOtherDeliveries(){
		let deliverer = $('#deliverer').val();
		let delivery_date = $('#delivery_date').val();
		let delivery_time = $('#delivery_time').val();
		if(deliverer != undefined && deliverer != ''){
			$.ajax({
				url: location.href,
				type: 'POST',
				data: {
					other_deliveries: true,
					deliverer: deliverer,
					delivery_date: delivery_date,
					delivery_time: delivery_time
				},
				success: function(resp){
					let other_deliveries = JSON.parse(resp);
					$('#otherDeliveries').find('.tdata').remove();
					if(other_deliveries.length > 0){
						other_deliveries.forEach(other_delivery => {
							let ot_del_html = '<tr class="tdata">';
							ot_del_html += '<td><a target="_blank" href="?orderId='+other_delivery['id']+'">'+other_delivery['id']+'</a><br>';
							ot_del_html += '<img class="deliveryCar" src="../../template/icons/deliver/'+other_delivery['deliverer']+'.png">'
							ot_del_html += '</td>';
							// ot_del_html += '<td>'+order_statuses[other_delivery['delivery_status']]+'</td>';
							ot_del_html += '<td><img src="../../template/icons/status/'+other_delivery['delivery_status']+'.png"></td>';
							
							ot_del_html += '<td>';
							ot_del_html += other_delivery['delivery_date'] + '<br>';
							ot_del_html += delivery_times[other_delivery['delivery_time']];
							if(other_delivery['delivery_time_manual'] != '' || other_delivery['travel_time_end'] != ''){
								ot_del_html += '<br>';
								ot_del_html += "("
								if(other_delivery['delivery_time_manual'] != ''){
									ot_del_html += other_delivery['delivery_time_manual'];
								}
								if(other_delivery['delivery_time_manual'] != '' && other_delivery['travel_time_end'] != ''){
									ot_del_html += " - ";
								}
								if(other_delivery['travel_time_end'] != ''){
									ot_del_html += other_delivery['travel_time_end']; 
								}
								ot_del_html += ")";
							}
							ot_del_html += '</td>';

							ot_del_html += '<td>';
								if(other_delivery['receiver_street_name'] != null){
									ot_del_html += other_delivery['receiver_street_name'];
									
									if(other_delivery['receiver_address'] != '' && other_delivery['receiver_address'] != null){
										ot_del_html += ' '+ other_delivery['receiver_address']+ ' ';
									}
									if(other_delivery['receiver_entrance'] != '' && other_delivery['receiver_entrance'] != null){
										ot_del_html += ' Մուտք'+ other_delivery['receiver_entrance']+ ' ';
									}
									if(other_delivery['receiver_floor'] != '' && other_delivery['receiver_floor'] != null){
										ot_del_html += ' Բնակարան'+ other_delivery['receiver_floor']+ ' ';
									}
									if(other_delivery['receiver_tribute'] != '' && other_delivery['receiver_tribute'] != null){
										ot_del_html += ' Հարկ'+ other_delivery['receiver_tribute']+ ' ';
									}
									if(other_delivery['receiver_door_code'] != '' != null){
										ot_del_html += ' Կոդ'+ other_delivery['receiver_door_code']+ ' ';
									}
									if(other_delivery['region_name'] != null && other_delivery['region_name'] != ''){
										ot_del_html += "(" + other_delivery['region_name'] + ")";
									}
								} else {
									ot_del_html += '';
								}
							ot_del_html += '</td>';

							ot_del_html += '<td>';
							if(other_delivery['products'].length > 0){
								if (!$('a[data-imagelightbox="del_' + other_delivery.id + '"]').length) {
									let path = './jos_product_images/';
									other_delivery['products'].forEach(other_prod => {
										// ot_del_html += other_prod['changed_name'] + '<br>';
										$('body').append('<a href="'+path + other_prod.image_source + '" class="otherImages" data-imagelightbox="del_' + other_delivery.id + '" style="display:none"><img src="'+path+ other_prod.image_source + '" alt="' + other_prod.short_desc + '"></a>');
										ot_del_html += '<img src="'+path + other_prod.image_source +'" style="max-width: 50px; max-height: 50px;" onclick="zoom_del_img('+other_delivery.id+')" alt="Zoom" />';
									});
								}
							}
							ot_del_html += '</td>';
							
							
							ot_del_html += '</tr>';
							$('#otherDeliveries table').append(ot_del_html);
						});	
					}
				}
			})
		}
	}

	function showOtherOrders(){

		let receiver_phone = $('#receiver_phone').val();
		let sender_phone = $('#sender_phone').val();
		let sender_email = $('#sender_email').val();
		let keyword = $('#keyword').val();
		$('.otherImages').remove();
		if(receiver_phone != '' || sender_phone != '' || sender_email != '' || keyword != ''){
			$.ajax({
				url: location.href,
				type: 'POST',
				data: {
					other_orders: true,
					receiver_phone: receiver_phone,
					sender_phone: sender_phone,
					keyword: keyword,
					sender_email: sender_email
				},
				success: function(resp){
					let other_orders = JSON.parse(resp);
					if(other_orders.length > 0){
						$('#otherOrders').find('.tdata').remove();
						other_orders.forEach(other_order => {
							let ot_or_html = '<tr class="tdata">';
							ot_or_html += '<td><a target="_blank" href="?orderId='+other_order['id']+'">'+other_order['id']+'</a>';
							if(other_order['operator'] != null && other_order['operator'] != ''){
								ot_or_html += '<br> by '+ other_order['operator'].charAt(0).toUpperCase() + other_order['operator'].slice(1);
							}
							ot_or_html += '</td>';
							// ot_or_html += '<td>'+order_statuses[other_order['delivery_status']]+'</td>';
							ot_or_html += '<td><img src="../../template/icons/status/'+other_order['delivery_status']+'.png">';
							if(other_order.type_id != null){
								ot_or_html += '<img style="width:40px;height:45px" src="../../template/images/complain.png">';
							}
							ot_or_html += '</td>';
							ot_or_html += '<td>';
								if(other_order['delivered_at'] != null){
									ot_or_html += other_order['delivered_at'];
								} else {
									ot_or_html += '';
								}
							ot_or_html += '</td>';
							ot_or_html += '<td>'+other_order['created_date']+ ' ' + other_order['created_time'] +'</td>';
							ot_or_html += '<td>';
							// start Editing by Hrach 11 06 19
							if(other_order['products'].length > 0 || other_order['products_delivery'].length > 0){
								if (!$('a[data-imagelightbox="' + other_order.id + '"]').length) {
									if(other_order['products'].length){
										let path = './jos_product_images/';
										other_order['products'].forEach(other_prod => {
											ot_or_html += other_prod['changed_name'] + '<br>';
											$('body').append('<a href="'+path + other_prod.image_source + '" class="otherImages" data-imagelightbox="' + other_order.id + '" style="display:none"><img src="'+path+ other_prod.image_source + '" alt="' + other_prod.short_desc + '"></a>');
											ot_or_html += '<img src="'+path + other_prod.image_source +'" style="max-width: 50px; max-height: 50px;" onclick="zoom_img('+other_order.id+')" alt="Zoom" />';
										});
									}
									if(other_order['products_delivery'].length > 0){
										var created_date = other_order['created_date'].split('-');
										var year_short = created_date[0].substring(created_date[0].length-2)
										var img_url_path = created_date[1] + "-" + year_short ;
										let path = './product_images/' + img_url_path + '/' ;
									other_order['products_delivery'].forEach(other_prod => {
										ot_or_html += other_prod['image_note'] + '<br>';
										$('body').append('<a href="'+path + other_prod.image_source + '" class="otherImages" data-imagelightbox="' + other_order.id + '" style="display:none"><img src="'+path+ other_prod.image_source + '" alt="' + other_prod.product_desc + '"></a>');
										ot_or_html += '<img src="'+path + other_prod.image_source +'" style="max-width: 50px; max-height: 50px;" onclick="zoom_img('+other_order.id+')" alt="Zoom" />';
									});
									}
								}
							}
							//end 11 06 19
							if(other_order['product'] != null && other_order['product'] != ''){
								ot_or_html += '<br>' + other_order['product'];
							}
							ot_or_html += '</td>';
							ot_or_html += '<td>'+other_order['price']+ ' ' + currencies[other_order['currency']] +'</td>';
							ot_or_html += '<td>'+other_order['sender_name']+'<br>';
							if(other_order['sender_phone'] != '' && other_order['sender_phone'] != null){
								ot_or_html += other_order['sender_phone']+'<br>';
							}
							if(other_order['sender_email'] != '' && other_order['sender_email'] != null){
								ot_or_html += other_order['sender_email']+'<br>';
							}
							ot_or_html += '</td>';
							ot_or_html += '<td>';
								if(other_order['country_name'] != null){
									ot_or_html += other_order['country_name'];
								} else {
									ot_or_html += '';
								}
							ot_or_html += '</td>';
							ot_or_html += '<td>'+other_order['sender_address']+'<br>'+other_order['sender_region']+'</td>';
							ot_or_html += '<td>'+other_order['receiver_name']+'<br>'+other_order['receiver_phone']+'</td>';
							ot_or_html += '<td>';

								if(other_order['receiver_subregion'] != '0'){
									ot_or_html += subregionType[other_order['receiver_subregion']] + " ";
									ot_or_html += " <?=(defined('STATE')) ? STATE : 'STATE';?> /-/ ";
								}
								if(other_order['receiver_street_name'] != null){
									ot_or_html += other_order['receiver_street_name'] ;
								}
								if(other_order['receiver_address'] != '' && other_order['receiver_address'] != null){
									ot_or_html += ' '+ other_order['receiver_address']+ ', ';
								}
								if(other_order['receiver_entrance'] != '' && other_order['receiver_entrance'] != null){
									ot_or_html += ' Մուտք '+ other_order['receiver_entrance']+ ', ';
								}
								if(other_order['receiver_floor'] != '' && other_order['receiver_floor'] != null){
									ot_or_html += ' Բնակարան '+ other_order['receiver_floor']+ ', ';
								}
								if(other_order['receiver_tribute'] != '' && other_order['receiver_tribute'] != null){
									ot_or_html += ' Հարկ '+ other_order['receiver_tribute']+ ', ';
								}
								if(other_order['receiver_door_code'] != '' && other_order['receiver_door_code'] != null){
									ot_or_html += ' Կոդ '+ other_order['receiver_door_code']+ ' ';
								}
							ot_or_html += '</td>';
							ot_or_html += '<td>'+other_order['notes']+'</td>';
							ot_or_html += '<td>'+other_order['sell_point_name']+'</td>';
							ot_or_html += '</tr>';
							$('#otherOrders table').append(ot_or_html);
						});
						$('#foundCount').html('Այս հաճախորդի տվյալներով գտնվեց ևս '+ other_orders.length + ' պատվեր։');
					}
				}
			})
		}
	}

	function zoom_img(id){
		var selectorF = 'a[data-imagelightbox="' + id + '"]';
		var instanceF = $(selectorF).imageLightbox(
			{
				quitOnImgClick: false,
				onLoadStart: function () {
					captionOff();
					activityIndicatorOn();
				},
				onLoadEnd: function () {
					captionOn();
					activityIndicatorOff();
				},
				onEnd: function () {
					captionOff();
					activityIndicatorOff();
				}
			});
		instanceF.switchImageLightbox(0);
	}

	function zoom_del_img(id){
		var selectorF = 'a[data-imagelightbox="del_' + id + '"]';
		var instanceF = $(selectorF).imageLightbox(
			{
				quitOnImgClick: false,
				onLoadStart: function () {
					captionOff();
					activityIndicatorOn();
				},
				onLoadEnd: function () {
					captionOn();
					activityIndicatorOff();
				},
				onEnd: function () {
					captionOff();
					activityIndicatorOff();
				}
			});
		instanceF.switchImageLightbox(0);
	}

	var
        // ACTIVITY INDICATOR
        activityIndicatorOn = function () {
            $('<div id="imagelightbox-loading"><div></div></div>').appendTo('body');
        },
        activityIndicatorOff = function () {
            $('#imagelightbox-loading').remove();
        },

        // OVERLAY
        overlayOn = function () {
            $('<div id="imagelightbox-overlay"></div>').appendTo('body');
        },
        overlayOff = function () {
            $('#imagelightbox-overlay').remove();
        },

        // CLOSE BUTTON
        closeButtonOn = function (instance) {
            $('<button type="button" id="imagelightbox-close" title="Close"></button>').appendTo('body').on('click touchend', function () {
                $(this).remove();
                instance.quitImageLightbox();
                return false;
            });
        },
        closeButtonOff = function () {
            $('#imagelightbox-close').remove();
        },

        // CAPTION
        captionOn = function () {
            var description = $('a[href="' + $('#imagelightbox').attr('src') + '"] img').attr('alt');
            if (description !== undefined && description.length > 0)
                $('<div id="imagelightbox-caption">' + description + '</div>').appendTo('body');
        },
        captionOff = function () {
            $('#imagelightbox-caption').remove();
        },

        // NAVIGATION
        navigationOn = function (instance, selector) {
            var images = $(selector);
            if (images.length) {
                var nav = $('<div id="imagelightbox-nav"></div>');
                for (var i = 0; i < images.length; i++)
                    nav.append('<button type="button"></button>');

                nav.appendTo('body');
                nav.on('click touchend', function () {
                    return false;
                });

                var navItems = nav.find('button');
                navItems.on('click touchend', function () {
                    var $this = $(this);
                    if (images.eq($this.index()).attr('href') != $('#imagelightbox').attr('src'))
                        instance.switchImageLightbox($this.index());

                    navItems.removeClass('active');
                    navItems.eq($this.index()).addClass('active');

                    return false;
                })
                    .on('touchend', function () {
                        return false;
                    });
            }
        },
        navigationUpdate = function (selector) {
            var items = $('#imagelightbox-nav button');
            items.removeClass('active');
            items.eq($(selector).filter('[href="' + $('#imagelightbox').attr('src') + '"]').index(selector)).addClass('active');
        },
        navigationOff = function () {
            $('#imagelightbox-nav').remove();
        },

        // ARROWS
        arrowsOn = function (instance, selector) {
            var $arrows = $('<button type="button" class="imagelightbox-arrow imagelightbox-arrow-left"></button><button type="button" class="imagelightbox-arrow imagelightbox-arrow-right"></button>');

            $arrows.appendTo('body');

            $arrows.on('click touchend', function (e) {
                e.preventDefault();

                var $this = $(this),
                    $target = $(selector + '[href="' + $('#imagelightbox').attr('src') + '"]'),
                    index = $target.index(selector);

                if ($this.hasClass('imagelightbox-arrow-left')) {
                    index = index - 1;
                    if (!$(selector).eq(index).length)
                        index = $(selector).length;
                }
                else {
                    index = index + 1;
                    if (!$(selector).eq(index).length)
                        index = 0;
                }

                instance.switchImageLightbox(index);
                return false;
            });
        },
        arrowsOff = function () {
            $('.imagelightbox-arrow').remove();
        };
        // Added By Hrach 28 05 2020
		function checkPaymentTypeSelect(){
			var val = $("#payment_type").val();
			if(val == 21 || val == 22 || val == 10 || val == 8){
				$("#sender_email").attr('required',true)
			}
			else{
				$("#sender_email").removeAttr('required')
			}
		}
		function checkPaymentTypeRequired(){
			var val = $("#delivery_status").val();
			if(val == 1){
				$("#payment_type").attr('required',true)
			}
			else{
				$("#payment_type").removeAttr('required')
			}
		}

</script>

</body>
</html>