<?php
header('Content-Type: application/json');
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
function getCurrencyValues(){
	$access_token_parameters = array();
	$curl = curl_init("http://new.regard-group.ru/currency.php");
	curl_setopt($curl,CURLOPT_POST,true);
	curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	$currencyResult = curl_exec($curl);
	curl_close($curl);
	$exchange_rate = json_decode($currencyResult);
	return $exchange_rate;
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
		
		$postArray = array("delivery_price","who_received","bonus_type","delivery_date","delivery_time","delivery_time_manual","delivery_region","receiver_name","product","price","currency","receiver_subregion","receiver_street","receiver_address","receiver_phone","greetings_card","delivery_type","ontime","delivery_status","order_source","order_source_optional","payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone","sender_email","notes","notes_for_florist","sell_point","keyword");
		
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
		$postArray = array("delivery_price","who_received","bonus_type","delivery_date","delivery_time","delivery_time_manual","delivery_region","receiver_name","product","price","currency","receiver_subregion","receiver_street","receiver_address","receiver_phone","greetings_card","delivery_type","ontime","delivery_status","order_source","order_source_optional","payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone","sender_email","notes","notes_for_florist","sell_point","keyword");
		$operator = "Robot";
		$cc = "am";
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
	if($check_table_count >= 70 && $check_table_count <= 75){
		$table_count = '70_75';
	}
	if($check_table_count >= 75 && $check_table_count <= 80){
		$table_count = '75_80';
	}
	if($check_table_count >= 80 && $check_table_count <= 85){
		$table_count = '80_85';
	}
	return $table_count;
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

function checkProdReady($order_id){
	$count_related = getwayConnect::getwayData("SELECT COUNT(*) as related_count from order_related_product_description where order_id='{$order_id}' and ready=0")[0];
	$count_delivery = getwayConnect::getwayData("SELECT COUNT(*) as delivery_count from delivery_images where rg_order_id='{$order_id}' and ready=0")[0];
	$orderInfo = getwayConnect::getwayData("SELECT * from rg_orders where id='{$order_id}'")[0];
	$Olddelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$orderInfo['delivery_status']}'");

	$finished = $count_delivery['delivery_count'] + $count_related['related_count'];

	$table_count = GetOrderTableCount(substr($order_id, 0, 2));
	if($finished > 0){
		$Newdelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '1'");
		$html_log_for_update_order.= '<br> Ստատուս: <span style="color:blue">' . $Olddelivery_status[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_status[0]['name_am']. '</b></span>';
		getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$order_id}','{$html_log_for_update_order}','13','" . date("Y-m-d H:i:s") ."','12')");
		getwayConnect::getwaySend("UPDATE rg_orders set delivery_status=1 where id='{$order_id}'");
	} else {
		$Newdelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '12'");
		$html_log_for_update_order.= '<br> Ստատուս: <span style="color:blue">' . $Olddelivery_status[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_status[0]['name_am']. '</b></span>';
		getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$order_id}','{$html_log_for_update_order}','13','" . date("Y-m-d H:i:s") ."','1')");
		getwayConnect::getwaySend("UPDATE rg_orders set delivery_status=12 where id='{$order_id}'");
	}
	return $finished;
}
if(isset($_POST['data']['changeFlourist'])){
	$order_id = $_POST['data']['order_id'];
	$flourist = $_POST['data']['flourist'];
	// Added By Hrach
	$orderOldInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$order_id}'");
	$Newflourist_id = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$flourist}'");
	$Oldflourist_id = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$orderOldInfo[0]['flourist_id']}'");
	if( $Newflourist_id[0]['username'] != $Oldflourist_id[0]['username'] ){
		$html_log_for_update_order.= "Flourist Name Is Changed from " . $Oldflourist_id[0]['username'] . " to " . $Newflourist_id[0]['username'];
		$check_table_count = substr($order_id, 0, 2);
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
			$table_count = '85_85';
		}
		getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$order_id}','{$html_log_for_update_order}','{$userData[0]['id']}','" . date("Y-m-d H:i:s") ."','{$orderOldInfo[0]['delivery_status']}')");
	}
	$query_florist = "UPDATE `rg_orders` SET `flourist_id` = '{$flourist}' WHERE `id` = '{$order_id}'";
	getwayConnect::getwaySend($query_florist);
	//
	exit(json_encode(array('success')));
}
if(isset($_REQUEST['change_product_related'])){
	getwayConnect::getwaySend("UPDATE order_related_product_description set ready='{$_REQUEST['val']}' where order_id='{$_REQUEST['order_id']}' AND related_id='{$_REQUEST['related_id']}'");
	$finished = false;
	$finished = checkProdReady($_REQUEST['order_id']);
	exit(json_encode(array('success', 'finished' => $finished)));
}
if(isset($_REQUEST['change_product_delivery_related'])){
	getwayConnect::getwaySend("UPDATE delivery_images set ready='{$_REQUEST['val']}' where id='{$_REQUEST['delivery_id']}'");
	$finished = false;
	$finished = checkProdReady($_REQUEST['order_id']);
	exit(json_encode(array('success', 'finished' => $finished)));
}
if(isset($_REQUEST['change_for_purchase'])){
	$new_value = '';
	if($_REQUEST['type'] == "1"){
		$old_checkbox = getwayConnect::getwayData("SELECT * from order_related_product_description where order_id = '{$_REQUEST['order_id']}' and id='{$_REQUEST['id']}'")[0];
		if($_REQUEST['user_id'] == '27'){
			if($_REQUEST['forPurchase'] == "0"){
				$new_value = 3;
				getwayConnect::getwaySend("UPDATE order_related_product_description set `for_purchase`='3', who_requested='{$_REQUEST['user_id']}' where id='{$_REQUEST['id']}'");
			}
			else{
				$new_value = $_REQUEST['forPurchase'];
				getwayConnect::getwaySend("UPDATE order_related_product_description set `for_purchase`='{$_REQUEST['forPurchase']}', who_requested='{$_REQUEST['user']}' where id='{$_REQUEST['id']}'");
			}
		}
		else{
			$new_value = $_REQUEST['forPurchase'];
			getwayConnect::getwaySend("UPDATE order_related_product_description set `for_purchase`='{$_REQUEST['forPurchase']}', who_requested='{$_REQUEST['user']}' where id='{$_REQUEST['id']}'");
		}
	} elseif($_REQUEST['type'] == "2") {
		getwayConnect::getwaySend("UPDATE delivery_images set `for_purchase`='{$_REQUEST['forPurchase']}', who_requested='{$_REQUEST['user']}' where id='{$_REQUEST['id']}'");
	}
	if($_REQUEST['user_id'] == '27'){
		if($_REQUEST['update_to_null_for_purchase'] == 'true'){
			getwayConnect::getwaySend("UPDATE rg_orders set `for_purchase`='0' where id='{$_REQUEST['order_id']}'");
		}
		else{
			getwayConnect::getwaySend("UPDATE rg_orders set `for_purchase`='1' where id='{$_REQUEST['order_id']}'");
		}
	}
	else{
		getwayConnect::getwaySend("UPDATE rg_orders set `for_purchase`='1' where id='{$_REQUEST['order_id']}'");
	}
	if($_REQUEST['type'] == "1"){
		$order_info = getwayConnect::getwayData("SELECT * from rg_orders where id = '{$_REQUEST['order_id']}'")[0];
		if($old_checkbox['for_purchase'] != $new_value){
			$html_log_for_update_order_log = '<br> Գնման ենթակա: <span style="color:blue">' . $old_checkbox['for_purchase'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b>' . $new_value . '</b></span>' ;
			$operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$_REQUEST['user_id']}'");
			$table_count = GetOrderTableCount(substr($_REQUEST['order_id'], 0, 2));
			getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$_REQUEST["order_id"]}','{$html_log_for_update_order_log}','{$operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."','" . $order_info["delivery_status"] . "')");
		}
	}
	$user = getwayConnect::getwayData("SELECT * from user where id = '{$_REQUEST['user']}'")[0];
	if(isset($user)){
		exit(json_encode(array('user' => $user['username'])));
	} else {
		exit(json_encode(array('user' => '')));		
	}
}
if(isset($_REQUEST['update_need_translate'])){
	$product_id = $_REQUEST['product_id'];
	$need_translate = $_REQUEST['checked'];
	getwayConnect::getwaySend("UPDATE orders_products set `need_translate`='" . $need_translate . "' where id ='{$product_id}'");
}
if(isset($_REQUEST['get_jos_vm_product'])){
	$product_id = $_REQUEST['product_id'];
	$data = getwayConnect::getwayData("SELECT * from jos_vm_product_stock_href LEFT JOIN jos_vm_product on jos_vm_product_stock_href.product_id = jos_vm_product.product_id LEFT JOIN jos_vm_product_price ON jos_vm_product_stock_href.product_id = jos_vm_product_price.product_id where stock_product_id='{$product_id}' order by product_publish desc,product_price");
    print json_encode($data);die;
}
if(isset($_REQUEST['isset_out_prod'])){
	$order_id = $_REQUEST['id'];
	$query_accounted = "SELECT orders_products_accounting.order_id,
    orders_products_accounting.product,
    orders_products_accounting.count,
    orders_products_accounting.accounting_type,
    orders_products_accounting.order_product_id,
    orders_products_accounting.order_product_type,
    orders_products_data.product_name,
    orders_products_data.product_description,
    orders_products_data.product_image,
    orders_products_accounting.editor,
    orders_products_accounting.date_utc,
    orders_products.id,
    orders_products.pcount as pcount,
    orders_products.pprice as pprice,
    orders_products_accounting.pNetcost
    FROM orders_products_accounting
    JOIN orders_category_product ON orders_category_product.products_id = orders_products_accounting.product
    JOIN orders_products ON orders_products_accounting.product = orders_products.product_data_id
    JOIN orders_products_data ON orders_products_data.id = orders_products.product_data_id
    WHERE orders_products_accounting.order_id = {$order_id}";
	$order_products = getwayConnect::getwayData("SELECT * FROM `order_related_products` where order_id = '{$order_id}';");
	if($order_products[0]['jos_vm_product_id']){
		$getCurrencyValues = getCurrencyValues();
		$order_products_array = explode(',',$order_products[0]['jos_vm_product_id']);
		$is_out_stock_products = getwayConnect::getwayData($query_accounted);
		$dataReturn = [];
		foreach($order_products_array as $order_product){
			$orderProductPrice=0;
			$orderProductPriceInfo = getwayConnect::getwayData('SELECT * FROM order_tax_info where rg_order_id="' . $order_id . '" and product_id ="' . $order_product . '"');
			if(isset($orderProductPriceInfo[0]['price_amd']) && $orderProductPriceInfo[0]['price_amd'] > 100){
				$orderProductPrice = $orderProductPriceInfo[0]['price_amd'];
			}
			else{
				$orderProductPriceInfoUsd = getwayConnect::getwayData('SELECT jos_vm_product.product_id, jos_vm_product_price.product_price,jos_vm_product_price.product_currency,order_related_product_description.price AS related_prod_price FROM jos_vm_product RIGHT JOIN jos_vm_product_price ON jos_vm_product.product_id = jos_vm_product_price.product_id LEFT JOIN order_related_product_description ON order_id = "' . $order_id . '" AND related_id = "' . $order_product . '" WHERE	jos_vm_product.product_id = "' . $order_product . '"');
				$orderProductPrice = $orderProductPriceInfoUsd[0]['related_prod_price']*$getCurrencyValues->USD;
			}
			// $dataReturnSmall['order_product_id'] = $order_product;
			$dataReturnSmall['order_product_id'] = $order_product;
			$dataReturnSmall['order_product_sold_price'] = $orderProductPrice;
			$dataReturnSmall['total_inqnarjeq'] = 0;
			foreach($is_out_stock_products as $key=>$stock_product){
				if($order_product == $stock_product['order_product_id']){
					$count_product = $stock_product['count'];
                    if (strpos($count_product, '-') !== false) {
                        $count_product = str_replace("-","",$count_product);
                    }
					$dataReturnSmall['total_inqnarjeq']+= $stock_product['pNetcost']*$count_product;
				}
			}
			$dataReturn[] = $dataReturnSmall;
		}
		exit(json_encode($dataReturn));
	}
	else{
		return false;
	}
}
if(isset($_REQUEST["update_order"]))
{
		$actionQuery = "";
		$_REQUEST["operator"] = $operator;
		$old_orderData = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '{$_REQUEST['id']}'");
		$Newdelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$_REQUEST["delivery_status"]}'");
		$Olddelivery_status = getwayConnect::getwayData("SELECT * FROM `delivery_status` WHERE `id` = '{$old_orderData[0]['delivery_status']}'");
		if($_REQUEST['delivery_status'] != $old_orderData[0]['delivery_status']){
			$operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$_REQUEST["operator"]}'");
			$html_log_for_update_order_log= '<br> Ստատուս: <span style="color:blue">' . $Olddelivery_status[0]['name_am'] . ' </span> <span style="color:red">-></span> <span style="color:#4F6228"><b> ' . $Newdelivery_status[0]['name_am']. '</b></span>';
			$table_count = GetOrderTableCount(substr($_REQUEST['id'], 0, 2));
			getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date,current_status_id) VALUES ('{$_REQUEST["id"]}','{$html_log_for_update_order_log}','{$operator_info[0]['id']}','" . date("Y-m-d H:i:s") ."','" . $_REQUEST["delivery_status"] . "')");
		}
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
		// $value = "<br>Driver Edit: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		// $actionQuery .= "log = CONCAT(log,'{$value}') ";
		$actionQuery = rtrim($actionQuery,", ");
		//print_r($postArray);
		//echo $actionQuery;
		if(isset($_REQUEST["id"]))
		{
			
			$id =  htmlentities($_REQUEST["id"]);
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
		
}else{
	exit(json_encode(array("status"=>"fail","msg"=>"update")));
}
?>