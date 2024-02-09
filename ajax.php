<?php
 
include_once 'ajax/ajaxfunctions.php';
include_once 'controls/GetDatabaseContent.php';

if (isset($_GET["hotelid"]) && (int)$_GET["hotelid"] > 0) {
    $hotelid =(int)$_GET["hotelid"];
     GataDatabaseContent::connectToDatabase();
     
     
     print_r($hotelid);
    
}


if (isset($_POST["profit"])) {
    
    
    
    
    $hotelid =(int)$_GET["hotelid"];
     GataDatabaseContent::connectToDatabase();
      
     
     print_r($hotelid);
    
}




 