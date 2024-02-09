<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/database/DatabaseConnection.php';

FlowersForm::connectToDatabase ();

class FlowersForm  {
    
    public static function connectToDatabase () {
        getwayConnect::getwayConnectIt();
    }
    
    public static function getDriversList() {
        $result = array();
        $query  = "SELECT * FROM `delivery_deliverer` where active = 1";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row;
        }
        return $result ;    
    }
    public static function getDeliveryReasonList() {
        $result = array();
        $query  = "SELECT * FROM `delivery_reason` ";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row;
        }
        return $result ;
    }
    public static function getLanguagePrimary() {
        $result = array();
        $query  = "SELECT * FROM `delivery_language` ";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row;
        }
        return $result ;
    }
    public static function getWhoReceveid() {
        $result = array();
        $query  = "SELECT * FROM `delivery_receiver` ";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row;
        }
        return $result ;
    }
    
     public static function getUserList() {
        $result = array();
        $query  = "SELECT * FROM `user` ";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row["username"];
        }
        return $result ;    
    }
    
    
    
    
    
}

