<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/database/DatabaseConnection.php';

/**
 * This is new functional which allows to get data from database 
 * 
 */

class GataDatabaseContent {
    
    
    public static function connectToDatabase () {
        getwayConnect::getwayConnectIt();
    }


    
    public static function getDefaultCurrencyValues () {
        $result = array();
        $query  = "SELECT * FROM `data_exchange_rate` ORDER BY ID DESC LIMIT 1 ";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result = $row;
        }
        return $result ;    
    }
    
    public static function getAllAirlines () {
        $result = "";
        $query  = "SELECT * FROM `data_travel_airline`";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
           $result .= "<option value = \"" . $row["id"]. "\" >" . $row["name"]. "</option>";
        }
        return $result ;
    }
    
    
    
    
    
    
    
}



