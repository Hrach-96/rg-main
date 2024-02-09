<?php

session_start();
$pageName = "accountant";
$rootF = "../..";

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include("../flower_orders/lang/language_am.php");
include("AccountingAssets.php");

page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
if (!$access) {
    header("location:../../login");
}

$uid = $_COOKIE["suid"];
$level = auth::getUserLevel($uid);
$levelArray = explode(",", $level[0]["user_level"]);
$userData = auth::checkUserExistById($uid);
$cc = $userData[0]["lang"];
$user_country = $userData[0]["country_short"];


$strict_country = ($user_country > 0) ? 'AND `delivery_region` = 4 ' : '';
$root = true;

$get_lvl = explode(',', $level[0]["user_level"]);

$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");

function getConstant($value)
{
    if (defined($value)) {
        return constant($value);
    } else {
        return $value;
    }
}

$userData = $userData[0];
$rgUsersAsHotel = Array('58943049c73a2','5ffb77b3c6f96','5ffb77e89efb5','5ffb781e8d103','5ffb782e41712','579f84cb41a15','58660ca8d68af','5ffb78720674a','5ffb78860fc09','54cfae683b926ww');
$hotelUserId = 'a2705f8d4b958f942d7c';
if ((int)$userData["id"] > 0) {

    getwayConnect::getwayConnectIt();


    if (isset($_POST["posting"]) && $_POST["posting"] == "insert") {

        $uid = $_COOKIE["suid"];
        $level = auth::getUserLevel($uid);
        $userIdInsert = $userData["id"];
        if(in_array($uid, $rgUsersAsHotel)){
            $userIdInsert = auth::checkUserExistById($hotelUserId)[0]['id'];
        }

        $levelArray = explode(",", $level[0]["user_level"]);


        $actiontype = (int)$_POST["actiontype"];


        $target = $_POST["target"];
        $quantity = (int)$_POST["quantity"];
        $price = (int)$_POST["price"];
        $balance = (int)$_POST['balance'];
        if ($actiontype == 2) {
            $price = $price * -1;
        }


        if (in_array(17, $levelArray)) {
            $currency = 'AMD';
        } else {
            $currency = $_POST["selectedcurrency"];
        }


        $query = "INSERT INTO accounting (price, purpose, quantity, currency, actiontype, userid, balance)"
            . " VALUES"
            . " ('$price' , :target , '$quantity' , :currency , '$actiontype',  " . $userIdInsert . ", ". $balance .")";

        try {
            $pdo = getwayConnect::$db;
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':target', $target);
            $stmt->bindParam(':currency', $currency);
            $stmt->execute();
            $id = $pdo->lastInsertId();

            if ($id > 0) {
                print_r($id);
            } else {
                print_r("error");
            }

        } catch (PDOExecption $e) {
            $dbh->rollback();
            print "Error!: " . $e->getMessage() . "</br>";
        }

    }

    if (isset($_POST["posting"]) && $_POST["posting"] == "update" && auth::roleExist(19)) {

        if (isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $action_type = (int)$_POST["action_type"];
            $target = $_POST["target"];
            $quantity = (int)$_POST["quantity"];
            $price = (int)$_POST["price"];
            $currency = $_POST["selected_currency"];

            if ($action_type == 2 && $price >0) {
                $price = $price * -1;
            }

            $query = "UPDATE `accounting` SET `price`=" . $price . ",`purpose`='" . $target . "',`quantity`=" . $quantity . ",`currency`='" . $currency . "',`actiontype`=" . $action_type . ",`edit_date`='" . date('Y-m-d G:i:s') . "' WHERE `id`= " . $id;

            try {
                $stmt = getwayConnect::$db->query($query);
//                getwayConnect::getwaySend($query);
                if ($stmt) {
                    echo json_encode(['message' => 'ok']);
                } else {
                    echo json_encode(['message' => 'error']);
                }

            } catch (PDOExecption $e) {
                $dbh->rollback();
                print "Error!: " . $e->getMessage() . "</br>";
            }
        }

    }


    if (isset($_POST['action']) && $_POST["action"] == "archiving" && auth::roleExist(19)) {

        if (isset($_POST['id'])) {
            $id = (int)$_POST['id'];

//            $query = "INSERT INTO accounting (price, purpose, quantity, currency, actiontype, userid)"
//                . " VALUES"
//                . " ('$price' , :target , '$quantity' , :currency , '$actiontype',  " . $userData["id"] . ")";
//
            $archive_status = getwayConnect::getwayData('SELECT * FROM `accounting` where `id`='. $id);
            if($archive_status[0]['status_archive'] == 0){
                $status = 1;
            } else {
                $status = 0;
            }
            $query = "UPDATE `accounting` SET `status_archive`={$status},`edit_date`='" . date('Y-m-d G:i:s') . "' WHERE `id`= " . $id;

            try {

                $stmt = getwayConnect::$db->query($query);

                if ($stmt) {
                    echo json_encode(['message' => 'ok']);
                } else {
                    echo json_encode(['message' => 'error']);
                }

            } catch (PDOExecption $e) {
                $dbh->rollback();
                print "Error!: " . $e->getMessage() . "</br>";
            }

        }

    }

}