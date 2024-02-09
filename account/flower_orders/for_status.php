<?php 
	session_start();
	$pageName = "flower";
	$rootF = "../..";
	include_once $_SERVER['DOCUMENT_ROOT']."/controls/FlowersForms.php";
	include($rootF . "/configuration.php");
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		if($_POST['action'] == 'GetOrderStatus' ){
			function get_first_name_translate($first_name){
				$sql = "SELECT * from translate_of_names where first_name_eng = '" . $first_name . "' or first_name_rus ='" . $first_name ."' or first_name_arm ='" . $first_name ."' ";
	    		$result_first_name = getwayConnect::getwayData($sql);
	    		return $result_first_name;
			}
			function get_last_name_translate($first_name){
				$sql = "SELECT * from translate_of_names where last_name_rus = '" . $first_name . "' or last_name_eng ='" . $first_name ."'  or last_name_arm ='" . $first_name ."' ";
	    		$result_last_name = getwayConnect::getwayData($sql);
	    		return $result_last_name;
			}
			$code = $_POST['code'];
			$info =  base64_decode($code);
			$orderid = explode(' ', $info)[0];
			$data = [];
			if($orderid > 60333){
				$order_hdm_printed = getwayConnect::getwayData("SELECT * FROM `order_hdm_printed` where order_id = '" . $orderid . "'");
				$select_order_info = getwayConnect::getwayData("SELECT * FROM `rg_orders` where id = '" . $orderid . "'");
				$delivery_time = getwayConnect::getwayData("SELECT * FROM delivery_time where id = '" . $select_order_info[0]['delivery_time'] . "'");

				if($delivery_time){
					$select_order_info[0]['delivery_time_clock'] = $delivery_time[0]['name'];
				}
			}
			// Get Delivery Addres - delivery_street
		    $sql = "SELECT * FROM delivery_street WHERE code = '" . $select_order_info[0]['receiver_street'] . "'";
		    $result_row = getwayConnect::getwayData($sql);
		    $result_delivery_address = [];
		    if (count($result_row) > 0) {
		        foreach($result_row as $key=>$row) {
		            $result_delivery_address[] = $row;
		        }
		    }
		    $product_live_images = getwayConnect::getwayData("SELECT * FROM `product_real_images` WHERE order_id = '" . $orderid . "'");
			// Get Delivery Subregion - delivery_subregion
		    $sql = "SELECT * FROM delivery_subregion WHERE code='" . $select_order_info[0]['receiver_subregion'] . "'";
		    $result_row = getwayConnect::getwayData($sql);
		    $result_delivery_subregion = [];
		    if (count($result_row) > 0) {
		        foreach($result_row as $key=>$row) {
		            $result_delivery_subregion[] = $row;
		        }
		    }
			$delyvery_subregion_by = ' ';
			if($result_delivery_subregion[0]['name'] != ''){
			    $delyvery_subregion_by = $result_delivery_subregion[0];
			}
			$receiver_address_by = ' ';
			$sender_full_name = explode(' ' , $select_order_info[0]['sender_name']);
			if($sender_full_name[0]){
				$result_first_name = get_first_name_translate($sender_full_name[0]);
				if($result_first_name){
					$select_order_info[0]['sender_name_translates'] = $result_first_name;
				}
			}
			if($sender_full_name[1]){
				$result_last_name = get_last_name_translate($sender_full_name[1]);
				if($result_last_name){
					$select_order_info[0]['sender_last_name_translates'] = $result_last_name;
				}
			}
			$receiver_full_name = explode(' ' , $select_order_info[0]['receiver_name']);
			if($receiver_full_name[0]){
				$result_first_name = get_first_name_translate($receiver_full_name[0]);
				if($result_first_name){
					$select_order_info[0]['receiver_name_translates'] = $result_first_name;
				}
			}
			if($receiver_full_name[1]){
				$result_last_name = get_last_name_translate($receiver_full_name[1]);
				if($result_last_name){
					$select_order_info[0]['receiver_last_name_translates'] = $result_last_name;
				}
			}
		    if($select_order_info[0]['receiver_address'] != ''){
		        $receiver_address_by = $select_order_info[0]['receiver_address'] . ',';
		    }
		    $floorExist = ' ';
		    if($select_order_info[0]['receiver_floor'] != ''){
		        $floorExist = $select_order_info[0]['receiver_floor'];
		    }
		    $tributeExist = ' ';
		    if($select_order_info[0]['receiver_tribute'] != ''){
		        $tributeExist = $select_order_info[0]['receiver_tribute'];
		    }
		    $receiverEntranceExist = ' ';
		    if($select_order_info[0]['receiver_entrance'] != ''){
		        $receiverEntranceExist = $select_order_info[0]['receiver_entrance'];
		    }
		    $receiverDoorCodeExist = ' ';
		    if($select_order_info[0]['receiver_door_code'] != ''){
		        $receiverDoorCodeExist = $select_order_info[0]['receiver_door_code'];
		    }
			// Get Driver delivery Subregion Id - delivery_subregion
		    $cityYerevanIds = ['1','2','3','4','5','6','7','8','9','10','11','12'];
		    $sql = "SELECT * FROM delivery_subregion where code = '" . $select_order_info[0]['receiver_subregion'] . "'";
		    $result_row = getwayConnect::getwayData($sql);
		    $delivery_subregion = [];
		    if ($result_row->num_rows > 0) {
		        while($row = $result_row->fetch_assoc()) {
		            $delivery_subregion[] = $row;
		        }
		    }
			$erevanExist = '';
		    if(in_array($delivery_subregion[0]['id'], $cityYerevanIds)){
		        $erevanExist = 'Երևան,';
		    }
		    $select_order_info[0]['order_hdm_printed'] = $order_hdm_printed;
		    if($select_order_info[0]['receiver_subregion'] != 'chchstvac_hasce'){
		    	$select_order_info[0]['erevanExist'] = $erevanExist;
		    	$select_order_info[0]['delyvery_subregion_by'] = $delyvery_subregion_by;
		    	$select_order_info[0]['result_delivery_address'] = $result_delivery_address[0];
		    	$select_order_info[0]['receiver_address_by'] = $receiver_address_by;
		    	$select_order_info[0]['floorExist'] = $floorExist;
		    	$select_order_info[0]['tributeExist'] = $tributeExist;
		    	$select_order_info[0]['receiverEntranceExist'] = $receiverEntranceExist;
		    	$select_order_info[0]['receiverDoorCodeExist'] = $receiverDoorCodeExist;
		    	$select_order_info[0]['product_live_images'] = $product_live_images;
		    }
			print json_encode($select_order_info);die;
		}
	}
?>