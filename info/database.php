<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
	public function getReferral($order_id){
		$sql = "SELECT * FROM rg_orders_srs_info where order_id = '" . $order_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getRegions(){
		$sql = "SELECT * from countries where active = 1 ORDER BY `ordering` ASC, name_am";
		$row_result = $this->cone->query($sql);
		$result = [];
		if($row_result){
			if ($row_result->num_rows > 0) {
			    while($row = $row_result->fetch_assoc()) {
			        $result[] = $row;
			    }
			}
		}
		return $result;
	}
	public function getOrders($start_date,$end_date,$website,$sender_country){
		$sql = "SELECT * FROM rg_orders";
		$from_date = $start_date;
        $to_date = $end_date;
        if(!empty($from_date)){
            $sql .= " where created_date >= '" . $from_date . "'"; 
            if(!empty($to_date)){
                $sql.= " and created_date <= '" . $to_date . "'";
            }
        }
        else if(!empty($to_date)){
            $sql.=" where created_date <= '" . $to_date . "'";
        }
        if($website == 'all'){
        	$sql.=" and (sell_point = '2' or sell_point = '18')";

        }
        else{
        	$sql.=" and sell_point = '" . $website . "'";
        }
        if($sender_country != 'all'){
        	$sql.=" and sender_country = ". $sender_country;
        }
        $sql.=" and delivery_status in (1,3,6,7,11,12,13,14)";
		$row_result = $this->cone->query($sql);
		$result = [];
		if($row_result){
			if ($row_result->num_rows > 0) {
			    while($row = $row_result->fetch_assoc()) {
			        $result[] = $row;
			    }
			}
		}
		return $result;
	}
	public function getRowByColumnEqual($table,$column,$value){
		$sql = "SELECT * FROM " . $table . " where " . $column . " = '" . $value . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getAllRowsOrderByModifyDate($table,$userPosition){
		$userPositionSql = '(';
		foreach($userPosition as $key => $position){
			if($key == 0){
				$userPositionSql.= $position . " = 1 ";
			}
			else{
				$userPositionSql.= ' or ' . $position . " = 1 ";
			}
		}
		$userPositionSql.= ")" ;
		$sql = "SELECT * FROM " . $table . " where " . $userPositionSql . " and deleted_date = '0000-00-00 00:00:00' order by modify_date desc";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function checkIfView($user_id,$post_id){
		$sql = "SELECT * FROM info_post_view where user_id = '" . $user_id ."' and post_id = '" . $post_id . "'" ;
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        return $row;
		    }
		}
		return false;
	}
	public function removePost($post){
		$new_sql = "UPDATE info_posts SET deleted_date = '" . date('Y-m-d H:i:s',strtotime('240 minute')) . "' WHERE id = " . $post['post_id'];
		$this->cone->query($new_sql);
	}
	public function removeFromTable($column,$table,$value){
		$new_sql = "DELETE FROM " . $table . " WHERE " . $column . " = '" . $value . "'";
		$this->cone->query($new_sql);
	}
	public function removeComment($post){
		$new_sql = "DELETE FROM info_post_comments WHERE id = " . $post['comment_id'];
		$this->cone->query($new_sql);
	}
	public function updatePost($post){
		$postDrivers = 0;
		$postFlourists = 0;
		$postOperators = 0;
		$postHotel = 0;
		if(isset($post['drivers'])){
			$postDrivers = 1;
		}
		if(isset($post['operators'])){
			$postOperators = 1;
		}
		if(isset($post['flourists'])){
			$postFlourists = 1;
		}
		if(isset($post['hotel'])){
			$postHotel = 1;
		}
		$new_sql = "UPDATE info_posts SET title = '" . $this->cone->real_escape_string($post['title']) . "',operators = '" . $postOperators . "',driver = '" . $postDrivers . "',flourist = '" . $postFlourists . "',hotel = '" . $postHotel . "',content = '" . $this->cone->real_escape_string($post['content']) . "' WHERE id = " . $post['post_id'];
		$this->cone->query($new_sql);
		return true;
	}
	public function getUserPosition($userInfo){
		$pos = Array();
		if(strpos($userInfo['user_level'], '36') !== false){
			$pos[] = 'operators';
		}
		if(strpos($userInfo['user_level'], '99') !== false){
			$pos[] = 'operators';
			$pos[] = 'flourist';
			$pos[] = 'driver';
			$pos[] = 'hotel';
		}
		if(strpos($userInfo['user_level'], '30') !== false){
			$pos[] = 'flourist';
		}
		if(strpos($userInfo['user_level'], '40') !== false){
			$pos[] = 'driver';
		}
		if(strpos($userInfo['user_level'], '18') !== false){
			$pos[] = 'hotel';
		}
		return $pos;
	}
	public function getAllRowsOrderByModifyDateValue($table,$column,$value){
		$sql = "SELECT * FROM " . $table . " where " . $column . " = '" . $value . "' order by modify_date desc";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function getPostFiles($post_id){
		$sql = "SELECT * FROM info_post_files where post_id = '" . $post_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function viewHistory($post_id){
		$sql = "SELECT * FROM info_post_view where post_id = '" . $post_id . "'";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		    	$userInfo = $this->getRowByColumnEqual('user','id',$row['user_id']);
		    	$row['username'] = $userInfo[0]['full_name_am'];
		    	if($row['username'] == ''){
		    		$row['username'] = $userInfo[0]['username'];
		    	}
		        $result[] = $row;
		    }
		}
		return $result;
	}
	public function addPost($post,$user_id){
		$postDrivers = 0;
		$postFlourists = 0;
		$postOperators = 0;
		$postHotel = 0;
		if(isset($post['drivers'])){
			$postDrivers = 1;
		}
		if(isset($post['operators'])){
			$postOperators = 1;
		}
		if(isset($post['flourists'])){
			$postFlourists = 1;
		}
		if(isset($post['hotel'])){
			$postHotel = 1;
		}
		$new_sql = 'INSERT INTO info_posts (user_id,content,title,operators,driver,flourist,hotel,created_date,modify_date) VALUES ( "' . $user_id .'","' . $this->cone->real_escape_string($post['description']) .'","' . $this->cone->real_escape_string($post['addPostTitle']) .'","' . $postOperators .'","' . $postDrivers .'","' . $postFlourists .'","' . $postHotel .'","' . date('Y-m-d H:i:s',strtotime('240 minute')) .'","' . date('Y-m-d H:i:s',strtotime('240 minute')) . '")';
		$this->cone->query($new_sql);
		return $this->cone->insert_id;
	}
	public function addComment($post,$user_id){
		$new_sql = "INSERT INTO info_post_comments (user_id,content,post_id,created_date,modify_date) VALUES ( '" . $user_id ."','" . $post['commentText'] ."','" . $post['post_id'] ."','" . date('Y-m-d H:i:s',strtotime('240 minute')) ."','" . date('Y-m-d H:i:s',strtotime('240 minute')) . "')";
		$this->cone->query($new_sql);
	}
	public function addFileToPost($post_id,$fileName){
		$new_sql = "INSERT INTO info_post_files (post_id,file_name) VALUES ( '" . $post_id ."','" . $fileName ."')";
		$this->cone->query($new_sql);
	}
	public function addView($post,$user_id){
		$new_sql = "INSERT INTO info_post_view (user_id,post_id,date) VALUES ( '" . $user_id ."','" . $post['post_id'] ."','" . date('Y-m-d H:i:s',strtotime('240 minute')) . "')";
		$this->cone->query($new_sql);
	}
	
}