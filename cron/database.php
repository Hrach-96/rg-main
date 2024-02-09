<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set("Asia/Yerevan");
Class Database {
	
	// private $servername = "localhost";
	// private $username = "root";
	// private $password = "";
	// private $dbname = "rg-hotel";
	
	private $servername = "localhost";
	private $username = "admin_rgsystem";
	private $password = "uniflora_rg_sysRG123$";
	private $dbname = "admin_rgsystem";
	
	private $cone;
	public function __construct (){
		$this->cone = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
		$this->cone->set_charset("utf8");;
	}
	public function insertTest($title){
		$sql = "INSERT INTO disadvantages_list (category_id, title, malus_unit, cost) VALUES (1, '" . $title . "', 4, 510)";
		$row_result = $this->cone->query($sql);
		$result = [];
		return $result;

	}
	public function getOnOff($variable){
		$sql = "SELECT * from off_on where variable = '" . $variable . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;

	}
	public function getAnavartRecordsToday($order_id,$operator_id){
		$sql = "SELECT * FROM pending_info where order_id = '{$order_id}' and operator_id = '{$operator_id}' and created_date > '" . date('Y-m-d') . " 00:00:00'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getAnavartOrdersOperatorName($operator){
		$sql = "SELECT * FROM rg_orders where operator_name='{$operator}' and delivery_status='2'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getPedingInfoByOrderId($order_id){
		$sql = "SELECT * FROM `pending_info` WHERE `order_id` = '{$order_id}' and status = '1'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function selectDeliveryTimeById($id){
		$sql = "SELECT * FROM delivery_time where id='{$id}'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function ordersByStatusNotConfirmed($status_id){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and confirmed = '0' and delivery_status = '" . $status_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function ordersByStatusTimeManually($status_id){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and (delivery_time_manual != '' or travel_time_end != '') and delivery_status = '" . $status_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function ordersByStatus($status_id){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_status = '" . $status_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function ordersByStatusDeliveryDate($status_id,$ids){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_time in(" . $ids . ") and delivery_status = '" . $status_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function statusInfo($status_id){
		$sql = "SELECT * FROM delivery_status where id = '" . $status_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function countryInfo($country_id){
		$sql = "SELECT * FROM countries where id = '" . $country_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function operatorInfo($operator){
		$sql = "SELECT * FROM user where username = '" . $operator . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function userInfoById($user_id){
		$sql = "SELECT * FROM user where id = '" . $user_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result[0];
	}
	public function notesInfo($note_type,$order_id){
		$sql = "SELECT * FROM `order_notes` WHERE `type_id` = '" . $note_type ."' and order_id = '" . $order_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function notCheckedOrders(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and confirmed = '0' group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryTime18_24(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_time in(5,6) group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryTime00_09(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_time in(7,8) group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryTimeManual(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and (delivery_time_manual != '' or travel_time_end != '') group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function haastatvacOrderStatuses(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_status in (1,3,6,7,11,12,13,14) group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function chhaastatvacOrderStatuses(){
		$sql = "SELECT * FROM rg_orders where created_date = '" . date('Y-m-d') . "' and id > 80000 and delivery_status in (2,4,5,8,9,10) group by delivery_status";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryExpireOrders(){
		$sql = "SELECT * FROM rg_orders where id > 80000 and delivery_status in (1,12,13) and delivery_date = '" . date("Y-m-d") . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getTodayOrdersRobots(){
		$sql = "SELECT * FROM rg_orders where id > 80000 and delivery_status = 10 and created_date = '" . date("Y-m-d") . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getOrderMailLogByStatus($order_id,$content_id){
		$sql = "SELECT * FROM mail_log where order_id = '" . $order_id . "' and content_type = '" . $content_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getOrderMailLog($order_id){
		$sql = "SELECT * FROM mail_log where order_id = '" . $order_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryHastatumOrdersToday(){
		$sql = "SELECT * FROM rg_orders where id > 80000 and created_date = '" . date("Y-m-d") . "' and delivery_status IN (1,6,7,11,12,13) ";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliveryExpireOrdersToday(){
		$sql = "SELECT * FROM rg_orders where id > 80000 and delivery_date = '" . date("Y-m-d") . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function deliverySellpointById($id){
		$sql = "SELECT * FROM delivery_sellpoint where inform_delivery=".$id;
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getOrdersFewDays($order_id){
		$sql = "SELECT id,delivery_status,sell_point,delivered_at FROM rg_orders where id='" . $order_id. "' and id > 80000 and delivery_date >= '" . date('Y-m-d', strtotime('-2 days')) . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getFromMailLog($order_id){
		$sql = "SELECT content_type,count FROM mail_log where order_id='" . $order_id. "' and content_type = 5";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
}