<?php

class Accounting
{

    public static function getUsersList()
    {

        $result = array();

//        $query = "SELECT * FROM `user` WHERE user_level LIKE '17' OR user_level LIKE '18' OR user_level LIKE '19' ORDER BY username ASC ";
        $query = "SELECT * FROM `user` WHERE (user_active = 1) and (user_level LIKE '%16%' OR user_level LIKE '%17%' OR user_level LIKE '%18%' OR user_level LIKE '%19%') ORDER BY username ASC ";

        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
            $result[$row["id"]] = $row["username"];
        }
        return $result;
    }


    public static function getCurrenciesList()
    {
        $result = array();
        $query = "SELECT * FROM `currency`";
        $query_result = getwayConnect::$db->query($query);
        foreach ($query_result as $row) {
            $result[$row["id"]] = $row["name"];
        }
        return $result;
    }


    public static function getAllObjects($date, $actiontype, $filterArray)
    {

        $result = array();
        $condition = " WHERE ";
        if (isset($filterArray['date_start']) && $filterArray['date_start']
            && isset($filterArray['date_end']) && $filterArray['date_end']) {

            $st_date = $filterArray["date_start"];
            $end_date = $filterArray['date_end'];

            $condition .= "  DATE(cdate) >=  '$st_date' AND  DATE(cdate) <=  '$end_date' ";

        } else if (isset($filterArray['date_start']) && $filterArray['date_start']) {

            $st_date = $filterArray["date_start"];
            $condition .= "  DATE(cdate) >=  '$st_date' ";
        } else if (isset($filterArray['date_end']) && $filterArray['date_end']) {

            $end_date = $filterArray['date_end'];
            $condition .= "  DATE(cdate) <=  '$end_date' ";

        } else if ((isset($filterArray["price"]) && $filterArray["price"] != '' ) || ( isset($filterArray["target"]) && $filterArray["target"] != '' ) ) {
            $condition .= "  DATE(cdate) >=  '".date('Y-m-d', strtotime('-1 week'))."' ";
        }
        else {
            $condition .= "  DATE(cdate) =  '".date('Y-m-d')."' ";
        }

//
//        if ($userid > 0) {
//            $condition .= " AND userid = $userid";
//        }

        if ($actiontype > 0) {
            $condition .= " AND actiontype = $actiontype ";
        }

        if (isset($filterArray["price"]) && $filterArray['price'] != '') {
            $condition .= " AND price = " . $filterArray["price"];
        }

        if (isset($filterArray["target"]) && strlen($filterArray["target"]) > 0) {
            $condition .= " AND purpose LIKE  '%" . $filterArray["target"] . "%'";
        }


        if (isset($filterArray["actiontype"]) && in_array($filterArray["actiontype"], [1, 2])) {
            $condition .= " AND actiontype = " . $filterArray["actiontype"];
        }


        if (isset($filterArray["actiontype"]) && $filterArray["actiontype"] == 3 && auth::roleExist(19)) {
            $condition .= " AND status_archive = 1";
        } else {
            $condition .= " AND status_archive = 0";
        }


        if (isset($filterArray["users"]) && $filterArray["users"] > 0) {
            $condition .= " AND userid = " . $filterArray["users"];
        }

        if (isset($filterArray["currencies"]) && strlen($filterArray["currencies"]) > 1) {
            $condition .= " AND currency = '" . $filterArray["currencies"] . "' ";
        }


        $query = "SELECT AC.* , US.username FROM `accounting` AS AC  "
            . " LEFT JOIN `user` AS US ON US.id  = AC.userid "
            . " " . $condition . " ORDER BY id ASC";


        $query_result = getwayConnect::$db->query($query);

        foreach ($query_result as $row) {
            $result[$row["id"]] = $row;
        }
        return $result;
    }
    public static function getAllObjectsAccountant($date, $actiontype, $filterArray,$userData)
    {

        $result = array();
        $condition = " WHERE ";
        if (isset($filterArray['date_start']) && $filterArray['date_start']
            && isset($filterArray['date_end']) && $filterArray['date_end']) {

            $st_date = $filterArray["date_start"];
            $end_date = $filterArray['date_end'];

            $condition .= "  DATE(cdate) >=  '$st_date' AND  DATE(cdate) <=  '$end_date' ";

        } else if (isset($filterArray['date_start']) && $filterArray['date_start']) {

            $st_date = $filterArray["date_start"];
            $condition .= "  DATE(cdate) >=  '$st_date' ";
        } else if (isset($filterArray['date_end']) && $filterArray['date_end']) {

            $end_date = $filterArray['date_end'];
            $condition .= "  DATE(cdate) <=  '$end_date' ";

        } else if ((isset($filterArray["price"]) && $filterArray["price"] != '' ) || ( isset($filterArray["target"]) && $filterArray["target"] != '' ) ) {
            $condition .= "  DATE(cdate) >=  '".date('Y-m-d', strtotime('-1 week'))."' ";
        }
        else {
            $condition .= "  DATE(cdate) =  '".date('Y-m-d')."' ";
        }
        if( $userData['id'] != 4 && $userData['id'] != 78 ){
            if (strpos($userData['user_level'], '89') !== false) {
                $condition .= " and `US`.user_level like '%89%' ";
            }
            if (strpos($userData['user_level'], '48') !== false || strpos($userData['user_level'], '30') !== false) {
                $condition .= " and (`US`.user_level like '%48%' or `US`.user_level like '%30%')";
            }
            if ($userData['username'] == 'sona') {
                $condition .= " and `US`.id = '" . $userData['id'] . "' ";
            }
        }

//
//        if ($userid > 0) {
//            $condition .= " AND userid = $userid";
//        }

        if ($actiontype > 0) {
            $condition .= " AND actiontype = $actiontype ";
        }

        if (isset($filterArray["price"]) && $filterArray['price'] != '') {
            $condition .= " AND price = " . $filterArray["price"];
        }

        if (isset($filterArray["target"]) && strlen($filterArray["target"]) > 0) {
            $condition .= " AND purpose LIKE  '%" . $filterArray["target"] . "%'";
        }


        if (isset($filterArray["actiontype"]) && in_array($filterArray["actiontype"], [1, 2])) {
            $condition .= " AND actiontype = " . $filterArray["actiontype"];
        }


        if (isset($filterArray["actiontype"]) && $filterArray["actiontype"] == 3 && auth::roleExist(19)) {
            $condition .= " AND status_archive = 1";
        } else {
            $condition .= " AND status_archive = 0";
        }


        if (isset($filterArray["users"]) && $filterArray["users"] > 0) {
            $condition .= " AND userid = " . $filterArray["users"];
        }

        if (isset($filterArray["currencies"]) && strlen($filterArray["currencies"]) > 1) {
            $condition .= " AND currency = '" . $filterArray["currencies"] . "' ";
        }


        $query = "SELECT AC.* , US.username FROM `accounting` AS AC  "
            . " LEFT JOIN `user` AS US ON US.id  = AC.userid "
            . " " . $condition . " ORDER BY id ASC";
        $query_result = getwayConnect::$db->query($query);

        foreach ($query_result as $row) {
            $result[$row["id"]] = $row;
        }
        return $result;
    }

    public static function getCorrectedDate($date)
    {
        $result = "";

        $arrayMonth = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
        $dateTimeExp = explode(" ", $date);

        $dateExplode = explode("-", $dateTimeExp[0]);


        $result = $dateExplode[2] . "-" . $arrayMonth[(int)$dateExplode[1] - 1] . "-" . $dateExplode[0] . " " . substr($dateTimeExp[1], 0, -3);

        return $result;

    }


}
