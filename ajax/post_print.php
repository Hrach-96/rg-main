<?php

session_start();

include("../apay/pay.api.php");
include("../apay/travel.api.php");
include("../configuration.php");

include_once $_SERVER['DOCUMENT_ROOT'].'/controls/FlowersForms.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/account/flower_orders/lang/language_am.php';

date_default_timezone_set("Asia/Yerevan");

$access = auth::checkUserAccess($secureKey);

if ($access) {
    
    
    $driveid = $_POST["driveid"];
    $STAGE   = $_POST["stage"];
    $name    = $_POST["name"];
    
    $query_check = "SELECT * FROM page_print WHERE DRIVERID = $driveid AND STAGE = $STAGE  AND CURRENT_DATE() = DATE(CDATE) ";
    
    $result =  getwayConnect::$db->query($query_check);
    
    $record_id = 0;
    foreach ($result as $row) {
       $record_id =  $row["id"];
    }
    
    if ($record_id > 0) {
        $query_update = "UPDATE page_print SET NAME = '" . $name  . "' WHERE id = " . $record_id;
         getwayConnect::$db->query($query_update );
    } else {
        $query_insert = "INSERT INTO page_print (DRIVERID, STAGE , NAME ) " 
                . " VALUES ($driveid , $STAGE, '$name')";
        getwayConnect::$db->query($query_insert);
    }
    
    print_r("ok");
    
}