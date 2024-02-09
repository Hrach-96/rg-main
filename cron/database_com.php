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
	
	private $servername = "82.202.170.110";
	private $username = "admin_fa";
	private $password = "RGfacom21$";
	private $dbname = "admin_flowers_armenia";
	
	private $cone;
	public function __construct (){
		$this->cone = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
		$this->cone->set_charset("utf8");;
	}
	public function removeSessionTableContent(){
		$sql = "TRUNCATE TABLE `jos_session`";
		//$sql = "DELETE FROM `jos_session`";
		$row_result = $this->cone->query($sql);
		return true;
	}
	public function runRepairSessionTable(){
		$sql = "REPAIR TABLE `jos_session`";
		$row_result = $this->cone->query($sql);
		return true;
	}
	public function geetSessionTable(){
		$sql = "SELECT * from jos_session";
		$row_result = $this->cone->query($sql);
		$result = [];
		if ($row_result->num_rows > 0) {
		    while($row = $row_result->fetch_assoc()) {
		        $result[] = $row;
		    }
		}
		else{
			runRepairSessionTable();
			removeSessionTableContent();
			return -1;
		}
		return $result;

	}	
}