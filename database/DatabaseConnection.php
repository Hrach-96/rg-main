<?php

class getwayConnect
{
    public static $db = null;

    public static function getwayConnectIt(
        $connectData = null
 //       $connectData = array(
 //           "driver" => "mysql",
 //           "host" => "localhost",
 //           "port" => 3306,
 //          "database" => "admin_rgsystem",
//            "user" => "admin_rgsystem",
//            "pass" => "uniflora_rg_sysRG123$")
    )
    {

        if ($connectData === null)
            $connectData = include($_SERVER['DOCUMENT_ROOT'].'/config.php');

        if (self::$db == null) {
            try {
                self::$db = new PDO($connectData["driver"] .
                    ':host=' . $connectData["host"] .
                    ';port=' . $connectData["port"] .
                    ';dbname=' . $connectData["database"],
                    $connectData["user"], $connectData["pass"], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
                self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            } catch (PDOException $e) {
                self::$db = $e;
            }
        }
    }

    public static function getwayData($query, $type = false)
    { 	//echo "Q1";
        self::getwayConnectIt();
        try {
			
            $prepare = (is_object(self::$db) && method_exists(self::$db, 'prepare')) ? self::$db->prepare($query) : false;
            if (is_object($prepare)) {
                $execute = $prepare->execute();
                if ($execute) {
                    if ($type != false) {
                        return $prepare->fetchAll($type);
                    } else {
                        return $prepare->fetchAll();
                    }
                } else {
                    self::$db = "error";
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            self::$db = $e;
        }
    }


    public static function getwaySend($query, $showLastId = false)
    {
        self::getwayConnectIt();
        try {
			
            $prepare = (is_object(self::$db) && method_exists(self::$db, 'prepare')) ? self::$db->prepare($query) : false;
            if (is_object($prepare)) {
                $execute = $prepare->execute();
                if ($execute) {
                    if ($showLastId) {
                        return self::$db->lastInsertId();
                    } else {
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            self::$db = $e;
        }
    }

    public static function getwayCount($query)
    {
        $query = str_replace("SELECT *", "SELECT count(*)", $query);
        self::getwayConnectIt();
        try {
			//echo "C1";
            $prepare = (is_object(self::$db) && method_exists(self::$db, 'query')) ? self::$db->query($query) : false;
            if (is_object($prepare)) {

                return $prepare->fetchColumn();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            self::$db = $e;
        }
    }
}
        
