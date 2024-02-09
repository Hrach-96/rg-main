<?php
if($root){
	$operator = page::getOperator($uid);
	function getEditor($orderId)
	{
		global $operator;
		return getwayConnect::getwayData("SELECT * FROM delivery_editor WHERE pid = '{$orderId}'");
	}
	function setEditor($orderId)
	{
		global $operator;
		$date = getway::utc();
		return getwayConnect::getwaySend("INSERT INTO delivery_editor SET operator = '{$operator}', pid = '{$orderId}',execution = '{$date}'");
	}
	function updateEditor($orderId,$type = 0)
	{
		global $operator;
		$query = "";
		if($type == 1)
		{
			$query = ",SET editing = 1";
		}
		$date = getway::utc();
		return getwayConnect::getwaySend("UPDATE delivery_editor SET operator = '{$operator}', execution = '{$date}' {$query} WHERE pid = '{$orderId}'");
	}
	function checkStatus($orderId){
		global $operator;
		if($data = getEditor($orderId)){
			$data = $data[0];
			$lastEdit = strtotime($data["execution"]);
			$differance = strtotime(getway::utc()) - $lastEdit;
			$minTime = 10;
			//var_dump($differance);
			if($data["operator"] == $operator)
			{
				updateEditor($orderId);
				return array("o" => $data["operator"]);
			}else if($data["operator"] != $operator && $differance <= $minTime){
				return array("o" => $data["operator"]);
			}else if($data["operator"] != $operator && $differance > $minTime){
				updateEditor($orderId);
				return array("o" => $operator);
			}
		}else{
			setEditor($orderId);
			return array("o" => $operator);
		}
	}
	function showEditors()
	{
		$now = strtotime(getway::utc())-10;
		$now = date("Y-m-d H:i:s",$now);
		return getwayConnect::getwayData("SELECT pid,operator FROM delivery_editor WHERE execution > '{$now}' ORDER BY execution DESC",PDO::FETCH_ASSOC);
	}
}
?>