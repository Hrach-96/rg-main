<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("apay/pay.api.php");
include("apay/travel.api.php");
include("configuration.php");

require 'excelphp/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

date_default_timezone_set("Asia/Yerevan");
header('Content-type: application/json');
$data = array("error" => "unothorized access");
$now = strtotime(getway::utc());
$access = auth::checkUserAccess($secureKey);
$count = 0;
$other_data = Array();
$paginator = "0,50";
$arrayCheckInputs = array("vServices", "vstatus", "vprice", "vcurrency", "vpayment", "vpaymenttype", "vpaymentNote", "vcustomerName", "vcustomerPhone", "vcustomerEmail", "vcustomerType", "vcountry", "vcity", "vcustomerAddress", "vsource", "vsourceNote", "vsellpoint", "vsellNote", "vuid", "vpartneID", "vguests", "vpartial_pay", "vpartial_pay_note", "vrevenue", "valert_date", "vnationality", "vpassport", "vbirthday", "varmenian","vroomkey","vwifiinfo","vlocalareainfo","vhotelpolicypapers", "vcheckedpayment", "vroomkeytaken", "vinvoiceprovided", "vlanguage", "vcityex");
$arrayHotelInputs = array('id', 'hotel_id', 'guests', 'adult_count', 'adult_price', 'child_count', 'child_price', 'single_count', 'single_price', 'double_count', 'double_price', 'triple_count', 'triple_price', 'family_count', 'family_price', 'check_in', 'check_out', 'breakfast', 'sim_card', 'transfer', 'transfer_price', 'city_tour', 'city_tour_price');
if ($access) {
    $uid = htmlentities($_COOKIE["suid"]);
    $userLevel = auth::checkUserExistById($uid);
    if (isset($_REQUEST["cmd"])) {
        $cmd = htmlentities($_REQUEST["cmd"]);

        if ($cmd == "getactions") {
            $result = getwayConnect::getwayData("SELECT * FROM `hotel_action_connect` WHERE row_id = '" . $_REQUEST['row_id'] . "'");
            foreach($result as $key=>$res){
                $userInfo = getwayConnect::getwayData("SELECT * FROM `user` WHERE id = '" . $res['user_id'] . "'");
                $actionInfo = getwayConnect::getwayData("SELECT * FROM `hotel_actions` WHERE id = '" . $res['action_id'] . "'");
                if($res['done_user_id'] > 0){
                    $doneUserInfo = getwayConnect::getwayData("SELECT * FROM `user` WHERE id = '" . $res['done_user_id'] . "'");
                    $result[$key]['done_user_info'] = $doneUserInfo[0];
                }
                $result[$key]['user_info'] = $userInfo[0];
                $result[$key]['action_info'] = $actionInfo[0];
            }
            print json_encode($result);die;
        }
        if ($cmd == "insertDone") {
            $query = "UPDATE  hotel_action_connect SET " . " done = '1', done_user_id='" . $_REQUEST['done_user_id'] . "', done_datetime = '" . date('Y-m-d H:i:s') . "' " . " WHERE id = '" . $_REQUEST['row_id'] . "'";
            getwayConnect::$db->query($query);
            return true;
        }
        if ($cmd == "updatedeliveryreason") {

            $query = "UPDATE  rg_orders SET"
                . " delivery_reason = '" . (int)$_GET["delivery_reason_id"] . "'"
                . " WHERE id = '" . (int)$_GET["id"] . "'";
            getwayConnect::$db->query($query);
            print_r("ok");

            exit(0);
        }
        if ($cmd == "updateprimarylanguage") {

            $query = "UPDATE  rg_orders SET"
                . " delivery_language_primary = '" . (int)$_GET["delivery_primary_language_id"] . "'"
                . " WHERE id = '" . (int)$_GET["id"] . "'";
            getwayConnect::$db->query($query);
            print_r("ok");

            exit(0);
        }
        if ($cmd == "updatewhoreceived") {

            $query = "UPDATE  rg_orders SET"
                . " who_received = '" . $_GET["who_received"] . "'"
                . " WHERE id = '" . (int)$_GET["id"] . "'";
            getwayConnect::$db->query($query);
            print_r("ok");

            exit(0);
        }
        if ($cmd == "updatestatus") {

            $query = "UPDATE  rg_orders SET"
                . " delivery_status = '" . (int)$_GET["delivery_status_id"] . "'"
                . " WHERE id = '" . (int)$_GET["id"] . "'";
            getwayConnect::$db->query($query);
            print_r("ok");

            exit(0);
        }
        if ($cmd == "updatedrive") {


            $result = getwayConnect::getwayData("SELECT * FROM `user` WHERE id = " . (int)$_GET["userid"]);

            $username = "<br>STAGE : " . $result[0]["username"] . "<br>" . date('Y-m-d H:i:s');


            $query = "UPDATE  rg_orders SET"
                . " quantity = " . (int)$_GET["quantity"] . ", "
                . " stage = " . (int)$_GET["stage"] . ", "
                . " step  = " . (int)$_GET["step"] . ", "
                . " deliverer = " . (int)$_GET["driverId"] . ","
                . " userid =  " . (int)$_GET["userid"] . ",  "
                . " log = CONCAT('" . $username . "', log)"
                . " WHERE id = " . (int)$_GET["id"];
            // Added By Hrach
                $orderOldInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$_GET["id"]}'");
                $html_log_for_update_order = '';
                $log = false;
                if ( $_GET["quantity"] != $orderOldInfo[0]['quantity']){
                    $log = true;
                    $html_log_for_update_order.= "Quantity Is Changed from " . $orderOldInfo[0]['quantity'] . " to " . $_GET["quantity"] . "<br>";
                }
                if ( $_GET["stage"] != $orderOldInfo[0]['stage']){
                    $log = true;
                    $html_log_for_update_order.= "Stage Is Changed from " . $orderOldInfo[0]['stage'] . " to " . $_GET["stage"] . "<br>";
                }
                if ( $_GET["step"] != $orderOldInfo[0]['step']){
                    $log = true;
                    $html_log_for_update_order.= "Step Is Changed from " . $orderOldInfo[0]['step'] . " to " . $_GET["step"] . "<br>";
                }
                if ( !empty($_GET['driverId']) && $_GET['driverId'] != $orderOldInfo[0]['deliverer']){
                    $constants_deliver = get_defined_constants();
                    $Newdeliverer = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = '{$_GET["driverId"]}'");
                    $Olddeliverer = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = '{$orderOldInfo[0]['deliverer']}'");
                    $log = true;
                    if(!empty($constants_deliver[$Olddeliverer[0]['name']])){
                        $oldDeliverer_name = $constants_deliver[$Olddeliverer[0]['name']];
                    }
                    else{
                        $oldDeliverer_name = $Olddeliverer[0]['name'];
                    }
                    if(!empty($constants_deliver[$Newdeliverer[0]['name']])){
                        $newDeliverer_name = $constants_deliver[$Newdeliverer[0]['name']];
                    }
                    else{
                        $newDeliverer_name = $Newdeliverer[0]['name'];
                    }
                    $html_log_for_update_order.= " Deliverer Is Changed from " . $oldDeliverer_name  . " to " .$newDeliverer_name . "<br>";
                }
                if($log){
                    $check_table_count = substr((int)$_GET['id'], 0, 2);
                    $table_count;
                    if($check_table_count >= 45 && $check_table_count < 50){
                        $table_count = '45_50';
                    }
                    if($check_table_count >= 50 && $check_table_count < 55){
                        $table_count = '50_55';
                    }
                    if($check_table_count >= 55 && $check_table_count < 60){
                        $table_count = '55_60';
                    }
                    if($check_table_count >= 60 && $check_table_count < 65){
                        $table_count = '60_65';
                    }
                    if($check_table_count >= 65 && $check_table_count <= 70){
                        $table_count = '65_70';
                    }
                    if($check_table_count >= 65 && $check_table_count <= 70){
                        $table_count = '65_70';
                    }
                    if($check_table_count >= 70 && $check_table_count <= 75){
                        $table_count = '70_75';
                    }
                    if($check_table_count >= 75 && $check_table_count <= 80){
                        $table_count = '75_80';
                    }
                    if($check_table_count >= 80 && $check_table_count <= 85){
                        $table_count = '80_85';
                    }
                    getwayConnect::getwaySend("INSERT INTO log_".$table_count  . " (order_id,description,operator_id,date) VALUES ('{$_GET['id']}','{$html_log_for_update_order}','{$_GET['userid']}','" . date("Y-m-d H:i:s") ."')");
                }
            getwayConnect::$db->query($query);


            print_r("ok");

            exit(0);

        } else if ($cmd == "data") {
            if (isset($_REQUEST["page"])) {
                $pageName = htmlentities($_REQUEST["page"]);
                $dataPl = getwayConnect::getwayData("SELECT * FROM `page_level` WHERE `pg_name` = '{$pageName}'");
                $pageLevel = $dataPl[0]["pg_level"];

                $time_now = date("Y-m-d");

                $sbtime = strtotime($time_now);
                $sbtimeh = $sbtime - (60 * 60);
                //one hour
                $beforeOneHour = date("Y-m-d", $sbtimeh);

                $sbtimed = $sbtime - (60 * 60 * 24);
                //before one day
                $beforeOneDay = date("Y-m-d", $sbtimed);

                $sbtimedp = $sbtime + (60 * 60 * 24);
                //after one day
                $afterOneDay = date("Y-m-d", $sbtimedp);

                $sbtime2dp = $sbtime + (60 * (60 * (24 * 2)));
                //after 2 day
                $after2Day = date("Y-m-d", $sbtime2dp);

                if ($pageName == "travel") {

                    $n_travel_data = getwayConnect::getwayData(
                        "SELECT DT.`id` AS `travel_id` FROM `data_travel` AS DT  
                                  RIGHT JOIN `travel_hotel_relation` AS THR ON THR.`travel_id` = DT.`id`
                                  RIGHT JOIN `data_hotel_booking` AS DHB ON THR.`hotel_booking_id` = DHB.`id`
                                  RIGHT JOIN `data_status` AS DS ON DS.`id` = DT.`travel_status`
                                  WHERE 
                                  DS.`name` = 'PENDING'
                                  AND DATE(DHB.`check_in`) <= '{$after2Day}'
                                  AND DATE(DHB.`check_in`) >= '{$time_now}'
                                  ;
                                  ", PDO::FETCH_ASSOC);


                    if (is_array($n_travel_data)) {
                        $new_array = array();
                        foreach ($n_travel_data as $value) {
                            if (isset($value['travel_id'])) {
                                $new_array[$value['travel_id']] = $value['travel_id'];
                            }
                        }
                        $other_data["alert"] = $new_array;
                    }


                } else {
                    $other_data["page"] = $pageName;
                }


                if (isset($_REQUEST["encodedData"])) {

                    $encodedData = htmlentities($_REQUEST["encodedData"]);
                    $checkEData = json_decode(base64_decode($encodedData), true);

                    $fields = array("error" => "no data");

                    if (isset($_REQUEST["paginator"])) {
                        $paginator = htmlentities($_REQUEST["paginator"]);
                        $checkNums = explode(":", $paginator);
                        if (!empty($checkNums) && isset($checkNums[0]) && isset($checkNums[1])) {
                            if (is_numeric($checkNums[0]) && is_numeric($checkNums[1])) {
                                $paginator = str_replace(":", ",", $paginator);
                            } else {
                                $paginator = "false";
                            }
                        }
                    }

                    if (!empty($checkEData)) {
                        $fields = page::getDataByfilter($userLevel[0]["user_level"], $encodedData, $pageLevel, $paginator);
                        
                        if (empty($fields)) {
                            $fields = array("error" => "no data1");
                        }
                    } else {
                        if($pageName == 'flower'){
                            if (page::compareUserLevelByPage($userLevel[0]["user_level"], $pageLevel)) {
                                $table = page::pageTable($pageName);
                                $pageName = $table["table_name"];
                                if ($table) {
                                    $query = "SELECT `rg_orders`.*, `user`.`username` as `flourist`, `delivery_time`.`name` as `delivery_time_range`, `organisations`.`name_am` as `organisation_name` FROM `rg_orders` 
                                    LEFT JOIN `user` ON `rg_orders`.`flourist_id` = `user`.`id` 
                                    LEFT JOIN `delivery_time` on `rg_orders`.`delivery_time` = `delivery_time`.`id`
                                    LEFT JOIN `organisations` on `rg_orders`.`organisation` = `organisations`.`id` WHERE `rg_orders`.sell_point != 22 ";
                                    
                                    
                                    if ($paginator != "false") {
                                        $query .= "  {$table["ordering_by"]}  LIMIT {$paginator}";
                                    } else {
                                        $query .= " {$table["ordering_by"]} ";
                                    }

                                    $data = getwayConnect::getwayData($query, PDO::FETCH_ASSOC);
                                    $query_count = "SELECT count(*) as `count` FROM `rg_orders` LEFT JOIN `user` ON `rg_orders`.`flourist_id` = `user`.`id`";
                                    $qCount = getwayConnect::getwayData($query_count)[0]['count'];
                                    if (!empty($data)) {
                                        $fields = array($qCount, $data);
                                    } else {
                                        $fields = false;
                                    }
                                } else {
                                    $fields = false;
                                }
                            } else {
                                $fields = false;
                            }
                        } else {
                            $fields = page::getDataByPage($userLevel[0]["user_level"], $pageLevel, $pageName, $paginator);
                        }

                        if (empty($fields)) {
                            $fields = array("error" => "no data2");
                        }
                    }

                    if (count($fields) > 1) {
                        $data = $fields[1];
                        $count = $fields[0];
                    } else {
                        $data = $fields;
                    }

                } else {
                    $data = page::getDataByPage($userLevel[0]["user_level"], $pageLevel, $pageName, $paginator);

                    if (empty($data)) {
                        $data = array("error" => "data not set");
                    } else {

                        $data = $data[1];
                        $count = $data[0];
                    }

                }
            } else {
                $data = array("error" => "source not set");
            }
        } else if ($cmd == "addData") {
            $data = Data_Action::getAddData($arrayCheckInputs);
            $actionArray = json_decode($_REQUEST['actionArray']);
            if(isset($actionArray->information)){
                $query = "INSERT INTO `hotel_action_connect` SET `row_id` = '" . $data["itemId"] . "',`user_id` = '" . $actionArray->user_id . "',`action_id` = '" . $actionArray->action_id . "',`information` = '" . $actionArray->information . "',`status` = '" . $actionArray->status . "',data_inserted='" . date('Y-m-d H:i:s') . "' ";
                getwayConnect::getwaySend($query);
            }
        } else if ($cmd == "viewData") {
            if (isset($_REQUEST["itemId"])) {
                $id = htmlentities($_REQUEST["itemId"]);
                if (is_numeric($id)) {
                    $data = travelData::getBaseDataById($id);
                    if ($data) {
                        if (empty($data)) {
                            $data = array("error" => "no such data");
                        } else {
                            $data[0]["latestUpdate"] = travelData::getLastLog($data[0]["travel_uniq"]);
                            $data[0]["addedBy"] = travelData::getAddedLog($data[0]["travel_uniq"]);
                        }

                    } else {
                        $data = array("error" => "no such data");
                    }
                } else {
                    $data = array("error" => "id must be a number");
                }
            } else {
                $data = array("error" => "id no set");
            }

        } else if ($cmd == "get_archive") {
            $pageName = htmlentities($_REQUEST["page"]);

            if ($pageName == "travel") {
                $encodedData = htmlentities($_REQUEST["encodedData"]);
                $checkEData = json_decode(base64_decode($encodedData), true);

                if (isset($checkEData["itemId"])) {
                    $travelId = htmlentities($checkEData["itemId"]);


                    $data = travelData::getAllLog($travelId);
                    if ($data) {
                        if (empty($data)) {
                            $data = array("error" => "no such data");
                        }

                    } else {
                        $data = array("error" => "no such data");
                    }

                } else {
                    $data = array("error" => "id no set");
                }
            }
        } else if ($cmd == "editData") {
            $data = Data_Action::getEditData($arrayCheckInputs);
            $actionArray = json_decode($_REQUEST['actionArray']);
            $query = "INSERT INTO `hotel_action_connect` SET `row_id` = '" . $_REQUEST["itemId"] . "',`user_id` = '" . $actionArray->user_id . "',`action_id` = '" . $actionArray->action_id . "',`information` = '" . $actionArray->information . "',`status` = '" . $actionArray->status . "',data_inserted='" . date('Y-m-d H:i:s') . "' ";
            getwayConnect::getwaySend($query);
        } else if ($cmd == "getSubRegion") {
            if (isset($_REQUEST["itemId"])) {
                header('Content-Type: text/html; charset=utf-8');
                $itemId = htmlentities($_REQUEST["itemId"]);
                echo travelData::getSubRegionById($itemId);
                exit;
            }
        } else if ($cmd == "getHotelAndExtra") {
            if (isset($_REQUEST["itemId"])) {
                $id = htmlentities($_REQUEST["itemId"]);
                if (is_numeric($id)) {
                    $booking_id = getwayConnect::getwayData("SELECT THR.`hotel_booking_id`,DHB.`hotel_id`,
                                                                    DHB.`check_in`,
                                                                    DHB.`check_out`,
                                                                    DHB.`adult_count`,
                                                                    DHB.`adult_price`,
                                                                    DHB.`child_count`,
                                                                    DHB.`child_price`,
                                                                    THR.`travel_id`,
                                                                    DHB.`hotel_confirmed`
                                                                    FROM 
                                                                    `travel_hotel_relation`  AS THR 
                                                                    LEFT JOIN `data_hotel_booking` AS DHB  ON DHB.`id` = THR.`hotel_booking_id` 
                                                                    WHERE THR.`travel_id` ='{$id}'", PDO::FETCH_ASSOC);

                    if (is_array($booking_id) && count($booking_id) > 0) {

                        $data = array();
                        foreach ($booking_id as $key => $value) {
                            $data[$value['hotel_booking_id']] = array(
                                "rooms" => travelData::getHotelRooms($value['hotel_booking_id']),
                                "extra" => travelData::getRoomExtra($value['hotel_booking_id']),
                                "travelInfo" => travelData::getTravel($value['travel_id']),
                                "global" => array(
                                    "hotel_id" => $value['hotel_id'],
                                    "check_in" => $value['check_in'],
                                    "check_out" => $value['check_out'],
                                    "adult_count" => $value['adult_count'],
                                    "adult_price" => $value['adult_price'],
                                    "child_count" => $value['child_count'],
                                    "child_price" => $value['child_price'],
                                    "hotel_confirmed" => $value['hotel_confirmed']
                                )
                            );
                        }

                    } else {
                        $data = array("error" => "booking_not_set_yet");
                    }
                } else {
                    $data = array("error" => "item_id_not_numeric");
                }
            } else {
                $data = array("error" => "item_id");
            }
        } else if ($cmd == "getTravelPartnerData") {
            if (isset($_REQUEST["pid"])) {
                $id = htmlentities($_REQUEST["pid"]);
                if (is_numeric($id)) {
                    $data_pid = travelData::getPartnerData($id);
                    $data_pid = isset($data_pid[0]) ? $data_pid[0] : $data_pid;
                    $data = array("pd" => $data_pid);
                } else {
                    $data = array("error" => "pid_id_not_numeric");
                }
            } else {
                $data = array("error" => "pid");
            }
        } else if ($cmd == "setTransfer") {
            if (isset($_REQUEST["itemId"])) {
                $id = htmlentities($_REQUEST["itemId"]);
                if (is_numeric($id)) {
                    $travel_data = travelData::getBaseDataById($id);
                    if ($travel_data) {
                        $travel_data = $travel_data[0];
                    }
                    $hotel_booking_data = getwayConnect::getwayData("SELECT DHB.*, DH.`name` AS `hotel_name`, DH.`address` AS `hotel_address` FROM
                                                                                        `travel_hotel_relation`  
                                                                    AS THR LEFT JOIN `data_hotel_booking` AS DHB  
                                                                    ON DHB.`id` = THR.`hotel_booking_id` 
                                                                    LEFT JOIN `data_hotels` AS DH  
                                                                    ON DH.`id` = DHB.`hotel_id` 
                                                                    WHERE THR.`travel_id` = '{$_REQUEST["itemId"]}' 
                                                                    ORDER BY DHB.`check_in` ASC", PDO::FETCH_ASSOC);


                    if ($travel_data) {
                        $hotel_name = getwayConnect::getwayData("SELECT * FROM `data_hotels` WHERE `id` = '{$hotel_booking_data[0]['hotel_id']}'", PDO::FETCH_ASSOC);
                        $date_value = "";
                        $action_where_to_be = '';
                        $action_where_to_go = '';
                        $travel_partner = (isset($travel_data['travel_partneID']) && $travel_data['travel_partneID'] > 0) ? $travel_data['travel_partneID'] : false;

                        if ($travel_partner && isset($travel_data['travel_customerName']) && strlen($travel_data['travel_customerName']) > 3) {
                            $partner_name = getwayConnect::getwayData("SELECT * FROM `travel_partner` WHERE `id` = '{$travel_partner}'", PDO::FETCH_ASSOC);
                            if (isset($partner_name[0])) {
                                $travel_partner = $partner_name[0]['name'];
                            }
                        } else {
                            $travel_partner = $travel_data['travel_customerName'];
                        }
                        $travel_guests = (isset($travel_data['travel_guests'])) ? $travel_data['travel_guests'] : '';
                        $transit_arm = array(
                            'sh' => 'շ',
                            'dz' => 'ծ',
                            'tz' => 'ձ',
                            'gh' => 'ղ',
                            'zh' => 'ժ',
                            'ch' => 'չ',
                            'ev' => 'և',
                            'th' => 'թ',
                            'ph' => 'փ',
                            'ye' => 'ե',
                            'j' => 'ջ',
                            'a' => 'ա',
                            'i' => 'ի',
                            'h' => 'հ',
                            'd' => 'դ',
                            'l' => 'լ',
                            'n' => 'ն',
                            'r\'' => 'ռ',
                            'r' => 'ր',
                            'k' => 'կ',
                            'x' => 'խ',
                            'c' => 'ց',
                            't' => 'տ',
                            'o' => 'օ',
                            'w' => 'ո',
                            'e' => 'է',
                            'g' => 'գ',
                            'z' => 'զ',
                            'p' => 'պ',
                            'q' => 'ք',
                            'y' => 'յ',
                            '@' => 'ը',
                            'f' => 'ֆ',
                            'jh' => 'ճ',
                            's' => 'ս',
                            'u' => 'ու',
                            'm' => 'մ',
                            'v' => 'վ'
                        );


                        $travel_guests = mb_convert_case($travel_guests, MB_CASE_TITLE, "UTF-8");
                        if (isset($_REQUEST['transfer_type'])) {
                            $_REQUEST['transfer_type'] = intval($_REQUEST['transfer_type']);
                            switch ($_REQUEST['transfer_type']) {
                                case 1:
                                    $date_value = (isset($hotel_booking_data[0]['check_in'])) ? $hotel_booking_data[0]['check_in'] : '';
                                    $action_where_to_be = 'Օդանավակայան / դիմավորում';
                                    $action_where_to_go = (isset($hotel_booking_data[0]) && isset($hotel_booking_data[0]['hotel_name'])) ? $hotel_booking_data[0]['hotel_name'] : '';
                                    $action_where_to_go = preg_replace("/\B[o]+/", 'w', $action_where_to_go);
                                    $action_where_to_go = preg_replace("/\B[e]+/", "ye", $action_where_to_go);
                                    $action_where_to_go = str_replace(array_keys($transit_arm), $transit_arm, strtolower($action_where_to_go));
                                    $action_where_to_go = mb_convert_case($action_where_to_go, MB_CASE_TITLE, "UTF-8");
                                    break;
                                case 2:
                                    $date_value = (isset($hotel_booking_data[0]['check_out'])) ? $hotel_booking_data[0]['check_out'] : '';
                                    $action_where_to_be = (isset($hotel_booking_data[0]) && isset($hotel_booking_data[0]['hotel_name'])) ? $hotel_booking_data[0]['hotel_name'] : '';
                                    $action_where_to_be = preg_replace("/\B[o]+/", 'w', $action_where_to_be);
                                    $action_where_to_be = preg_replace("/\B[e]+/", "ye", $action_where_to_be);
                                    $action_where_to_be = str_replace(array_keys($transit_arm), $transit_arm, strtolower($action_where_to_be));
                                    $action_where_to_be = mb_convert_case($action_where_to_be, MB_CASE_TITLE, "UTF-8");
                                    $action_where_to_be .= ' / ճանապարհում';
                                    $action_where_to_go = 'Օդանավակայան / ճանապարհում';
                                    break;
                                case 3:

                                    break;
                            }
                        }
                        if (strtotime($date_value)) {
                            $date_value = strtotime($date_value);
                            $date_value = date('Y-m-d H:i:s', $date_value);

                        }
                        $date_value = "`delivery_date` = '{$date_value}',";


                        $cDate = date("Y-m-d");
                        $action_transfer = getwayConnect::getwaySend("INSERT INTO `rg_orders` SET {$date_value} `receiver_name` = '{$action_where_to_be}',
						 `product` = '{$travel_guests}',
						 `receiver_address` = '{$action_where_to_go}',
						 `sender_name` = '{$travel_partner}',
						 `delivery_status` = 10,
						 `created_date` = '{$cDate}'
						 ", true);
                        //var_dump(getwayConnect::$db->errorInfo());
                        if ($action_transfer) {


                            $action_done = getwayConnect::getwaySend("UPDATE `data_travel` SET `travel_transfered` = {$_REQUEST['transfer_type']} WHERE `id` = '{$id}'");
                            $data = array("transfer" => $action_transfer);
                            $count = 0;
                        } else {
                            $data = array("error" => "transfer_error");
                        }
                    } else {
                        $data = array("error" => "data_not_obtained");
                    }
                } else {
                    $data = array("error" => "item_id_not_numeric");
                }
            } else {
                $data = array("error" => "item_id");
            }
        } else if ($cmd == "order_images") {
            if (isset($_REQUEST["itemId"])) {
                $id = htmlentities($_REQUEST["itemId"]);
                if (is_numeric($id)) {
                    $images_data = getwayConnect::getwayData("SELECT * FROM `delivery_images` WHERE `rg_order_id` = '{$id}'", PDO::FETCH_ASSOC);
                    $data = array("images" => $images_data);
                    $count = 0;
                }
            }
        } else if ($cmd == "related_images") {
            if (isset($_REQUEST["itemId"])) {
                $id = htmlentities($_REQUEST["itemId"]);
                if (is_numeric($id)) {
                    $relateds = getwayConnect::getwayData("SELECT * FROM order_related_products where order_id='{$id}'");                    
                    $images_data = [];
                    $out_images_data = [];
                    if(isset($relateds) && !empty($relateds) && isset($relateds[0]) && isset($relateds[0]['jos_vm_product_id'])){
                        $relateds = explode(',', $relateds[0]['jos_vm_product_id']);
                        foreach($relateds as $key => $value){
							$images = getwayConnect::getwayData("SELECT `product_thumb_image` as `image_source`,`product_full_image` as `image_full_source`, product_width, product_height,
                            `product_name` as `name`, `product_s_desc` as `image_note`, product_sku as sku, order_related_product_description.`description` as short_desc, 
                            order_related_product_description.`name` as changed_name, jos_vm_product_price.product_price as `price`, 
                            order_related_product_description.ready as related_ready, order_related_product_description.for_purchase as for_purchase, 
                            order_related_product_description.related_id as related_id,
                            order_related_product_description.id as order_related_id,
                            user.username as who_requested
                            FROM `jos_vm_product` RIGHT JOIN order_related_product_description on order_related_product_description.order_id='{$id}' AND order_related_product_description.related_id='{$value}'
                            RIGHT JOIN jos_vm_product_price on jos_vm_product_price.product_id = '{$value}'
                            LEFT JOIN user on user.id = order_related_product_description.who_requested
                            WHERE jos_vm_product.`product_id` = '{$value}'", PDO::FETCH_ASSOC);
							if(isset($images[0])){
								$images_data[] = $images[0];
								$images_data[$key]['id'] = (int)$id;
							}
                        }
                    }
                    $out_images = getwayConnect::getwayData("SELECT * from order_out_images where order_id='{$id}'");
                    if(isset($out_images) && !empty($out_images)){
                        foreach($out_images as $key => $out_image){
                            $out_images_data[] = $out_image;
                        }
                    }
                    $product_images_data['images'] = getwayConnect::getwayData("SELECT `delivery_images`.*, user.username from `delivery_images`
                        LEFT JOIN user on delivery_images.who_requested = user.id WHERE `delivery_images`.`rg_order_id` = '{$id}'", PDO::FETCH_ASSOC);
                    $product_images_data['orderInfo'] = getwayConnect::getwayData("SELECT * FROM rg_orders where id='{$id}'");
                    $data = array("images" => $product_images_data, "related_images" => $images_data, 'out_images' => $out_images_data,'order_created_date' => $product_images_data['orderInfo'][0]['created_date']);
                    $count = 0;
                } else {
                    $data = array("error" => "np command provided");
                }
            } else {
                $data = array("error" => "np command provided");
            }
        } else if($cmd == "receiver_mood"){
            if(getwayConnect::getwaySend("UPDATE rg_orders set receiver_mood='{$_REQUEST['mood']}' where id='{$_REQUEST['order_id']}'")){
                $data = array('success' => 'Mood changed!');
            } else {
                $data = array('error' => 'Something went wrong!');
            }
        }else if($cmd == 'mail_log'){
            $mail_log = getwayConnect::getwayData("SELECT content_type, `count` FROM mail_log where order_id={$_REQUEST['order_id']}");
            $mail_logs = [];
            foreach($mail_log as $mail_count){
                $mail_logs[$mail_count['content_type']] = $mail_count['count'];
            }
            $data = $mail_logs;
        }else {
            $data = array("error" => "np command provided");
        }
    }
}
if (isset($_GET["excel"]) && $_GET["excel"] == 1) {

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $excelEncodedData = htmlentities($_REQUEST["excelColEncodedData"]);
    $excelColData = json_decode(base64_decode($excelEncodedData), true);
    $regions = page::getArrayData("regions");
//    dd(in_array('check_in_date',$excelColData));
//    dde($excelColData);

    $row = 0;
    $coll = 0;
//dde($data);
    $headersArray = $data[0];


    foreach ($headersArray as $key => $value) {

//        '.p_id',
//            '.p_deal_date',
//            '.p_status',
//            '.p_airline',
//            // '.p_name',
//            '.p_contacts',
//            '.p_hotels',
//            '.p_comments',
//            '.p_sell_point',
//            '.p_last_updated_date',
//            // '.p_arrival_date',
//            // '.p_departure_date',
//            '.p_price',
//            '.p_net',
//            // '.p_we_paid',
//            '.p_partial_paid',
//            '.p_guests',
//            '.p_partner_order_id',
//            '.p_check_in_date',
//            '.p_check_out_date',


        $finalValue = FALSE;

        if ($key == "id" && in_array('id', $excelColData)) {
            $finalValue = "ID";
        }

        if ($key == "travel_prt_order_id" && in_array('partner_order_id', $excelColData)) {
            $finalValue = "Partner Order ID";
        }


        if ($key == "travel_status" && in_array('status', $excelColData)) {
            $finalValue = "Status";
//            dd(travel_status);
        }

        if ($key == "travel_price" && in_array('price', $excelColData)) {
            $finalValue = "Price";
        }

        if ($key == "travel_currency" && in_array('price', $excelColData)) {
            $finalValue = "Currency";
        }

        if ($key == "travel_country" && in_array('guests', $excelColData)) {
            $finalValue = "Country";
        }

        if ($key == "travel_guests" && in_array('guests', $excelColData)) {
            $finalValue = "Guests";
        }

        if ($key == "travel_date" && in_array('deal_date', $excelColData)) {
            $finalValue = "Deal Date";
        }

        if ($key == "travel_partial_pay" && in_array('partial_paid', $excelColData)) {
            $finalValue = "Partial Pay";
        }

        if ($key == "travel_CurrencyPartialPaied" && in_array('partial_paid', $excelColData)) {
            $finalValue = "Partial Pay Currenct";
        }

        if ($key == "travel_revenue" && in_array('net', $excelColData)) {
            $finalValue = "Net";
        }

        if ($key == "travel_CurrencyOfPrice" && in_array('net', $excelColData)) {
            $finalValue = "Net Currency";
        }

        if ($key == "travel_airline_name" && in_array('airline', $excelColData)) {
            $finalValue = "Airline";
        }

        if ($key == "travel_arraival_date" && in_array('airline', $excelColData)) {
            $finalValue = "Arrival";
        }

        if ($key == "travel_departure_date" && in_array('airline', $excelColData)) {
            $finalValue = "Departure";
        }


        if ($key == "date_last_update" && in_array('last_updated_date', $excelColData)) {
            $finalValue = "Last Updated Date";
        }

        if ($key == "travel_sellNote" && in_array('comments', $excelColData)) {
            $finalValue = "Comments";
        }


        if ($key == "travel_hotel_name") {

            if (in_array('hotels', $excelColData)) {
                $coll++;
                $sheet->setCellValueByColumnAndRow($coll, 1, "Hotels");
            }
            if (in_array('check_in_date', $excelColData)) {
                $coll++;
                $sheet->setCellValueByColumnAndRow($coll, 1, "Check IN");
            }
            if (in_array('check_out_date', $excelColData)) {
                $coll++;
                $sheet->setCellValueByColumnAndRow($coll, 1, "Check OUT");
            }
        } else if ($finalValue !== FALSE) {
            $coll++;
//            dd($coll);
//            dd($finalValue);
            $sheet->setCellValueByColumnAndRow($coll, 1, $finalValue);
        }

//dd($key);
    }

    foreach (range('A', $spreadsheet->getActiveSheet()->getHighestDataColumn()) as $col) {
        $spreadsheet->getActiveSheet()
            ->getColumnDimension($col)
            ->setAutoSize(true);
    }
//dde('kkk');
    $row = 1;
    //--------------
    foreach ($data as $valuesRows) {

        $row++;
        $coll = 0;

        foreach ($valuesRows as $key => $valuesCells) {
            $finalValue = FALSE;

            if ($key == "id" && in_array('id', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_prt_order_id" && in_array('partner_order_id', $excelColData)) {
                $finalValue = (string)$valuesCells;
            }

            if ($key == "travel_status" && in_array('status', $excelColData)) {
                $finalValue = Data_Action::getStatusValue($valuesCells);
            }

            if ($key == "travel_price" && in_array('price', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_currency" && in_array('price', $excelColData)) {
                $finalValue = Data_Action::getCurrencyValue($valuesCells);
            }

            if ($key == "travel_country" && in_array('guests', $excelColData)) {

                $finalValue = isset($regions[$valuesCells]) ? $regions[$valuesCells] : '';
            }

            if ($key == "travel_guests" && in_array('guests', $excelColData)) {
                $finalValue = str_replace("\n", ", ", $valuesCells);
            }
            if ($key == "travel_date" && in_array('deal_date', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_partial_pay" && in_array('partial_paid', $excelColData)) {
                $finalValue = $valuesCells ? (int)$valuesCells : 0;
            }

            if ($key == "travel_CurrencyPartialPaied" && in_array('partial_paid', $excelColData)) {
                $finalValue = Data_Action::getCurrencyValue($valuesCells);
            }

            if ($key == "travel_revenue" && in_array('net', $excelColData)) {
                $finalValue = $valuesCells ? (int)$valuesCells : 0;
            }

            if ($key == "travel_CurrencyOfPrice" && in_array('net', $excelColData)) {
                $finalValue = Data_Action::getCurrencyValue($valuesCells);
            }

            if ($key == "travel_airline_name" && in_array('airline', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_arraival_date" && in_array('airline', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_departure_date" && in_array('airline', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "date_last_update" && in_array('last_updated_date', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_sellNote" && in_array('comments', $excelColData)) {
                $finalValue = $valuesCells;
            }

            if ($key == "travel_hotel_name" && in_array('hotels', $excelColData)) {

                $htelData = explode("<hr>", $valuesCells);
                $hotelNames = "";
                $checkInHotel = "";
                $checkOutHotel = "";

                foreach ($htelData as $valueHotel) {
                    $cleanAll = strip_tags($valueHotel);
                    $exp1 = explode("CIN - ", $cleanAll);
                    if (count($exp1) > 1) {
                        $hotelNames .= $exp1[0] . ",";
                        $exp2 = explode("COUT -", $exp1[1]);
                        $checkInHotel .= Data_Action::getCorrectDateFormatForExport(trim($exp2[0])) . ",";


                        $checkOutHotel .= Data_Action::getCorrectDateFormatForExport(trim($exp2[1])) . ",";
                    }
                }

                $hotelNames = rtrim($hotelNames, ",");
                $checkInHotel = rtrim($checkInHotel, ",");
                $checkOutHotel = rtrim($checkOutHotel, ",");
                if (in_array('hotels', $excelColData)) {
                    $coll++;
                    $sheet->setCellValueByColumnAndRow($coll, $row, $hotelNames);
                }

                if (in_array('check_in_date', $excelColData)) {
                    $coll++;
                    $sheet->setCellValueByColumnAndRow($coll, $row, $checkInHotel);
                }

                if (in_array('check_out_date', $excelColData)) {
                    $coll++;
                    $sheet->setCellValueByColumnAndRow($coll, $row, $checkOutHotel);
                }


                $finalValue = $valuesCells;
            } else if ($finalValue !== FALSE) {
                $coll++;
                $sheet->setCellValueByColumnAndRow($coll, $row, $finalValue);
            }

        }
    }

    $filename = "file" . time() . ".xlsx";
    $filedir = "download/" . $filename;

    $writer = new Xlsx($spreadsheet);
    $writer->save($filedir);

    if (!file_exists($filename)) {
        header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
        header("Content-Length: " . filesize($filedir));
        header("Content-Type: application/octet-stream;");
        readfile($filedir);
        unlink($filedir);

    }

    exit;
} else {
    echo json_encode(array("count" => $count, "data" => $data, "other" => $other_data));
}
class Data_Action
{


    public static function getAddData($arrayCheckInputs)
    {

        $data = array("ok" => "added");
        $valueCount = true;
        $prefix = "v";
        $uniq = getway::genUniq();
        $query = "INSERT INTO `data_travel` SET `travel_uniq` = '{$uniq}', ";

        $query .= " date_insert = '" . date('Y-m-d H:i:s') . "',";


        foreach ($_REQUEST as $key => $value) {
            $key = htmlentities($key);
            $value = htmlentities($value);

            if (in_array($key, $arrayCheckInputs) && $value != '') {
                $mKey = "";
                if (substr($key, 0, strlen($prefix)) == $prefix) {
                    $mKey = substr($key, strlen($prefix));
                }
                $query .= "travel_" . $mKey . "='" . $value . "',";
            }

        }


        if (isset($_GET['vprt_order_id'])) {
            $query .= " travel_prt_order_id = '" . $_GET['vprt_order_id'] . "',";
        }

        if (isset($_GET['vtotal_income'])) {
            $query .= " travel_total_income = '" . $_GET['vtotal_income'] . "',";
        }

        if (isset($_GET['vWePaid'])) {
            $WePaid = $_GET['vWePaid'] == 'on' ? 1 : '';
            $query .= " travel_WePaid = '" . $WePaid . "',";
        }

        if (isset($_GET['vCurrencyOfPrice'])) {
            $query .= " travel_CurrencyOfPrice = '" . $_GET['vCurrencyOfPrice'] . "',";
        }

        if (isset($_GET['vCurrencyPartialPaied'])) {
            $query .= " travel_CurrencyPartialPaied = '" . $_GET['vCurrencyPartialPaied'] . "',";
        }

        if (isset($_GET['vexchange_USA'])) {
            $query .= " travel_exchange_USA = '" . $_GET['vexchange_USA'] . "',";
        }

        if (isset($_GET['vexchange_EUR'])) {
            $query .= " travel_exchange_EUR = '" . $_GET['vexchange_EUR'] . "',";
        }

        if (isset($_GET['vexchange_IRR'])) {
            $query .= " travel_exchange_IRR = '" . $_GET['vexchange_IRR'] . "',";
        }

        if (isset($_GET['vexchange_GEL'])) {
            $query .= " travel_exchange_GEL = '" . $_GET['vexchange_GEL'] . "',";
        }

        if (isset($_GET['vexchange_RUR'])) {
            $query .= " travel_exchange_RUR = '" . $_GET['vexchange_RUR'] . "',";
        }

        if (isset($_GET['vexchange_GBP'])) {
            $query .= " travel_exchange_GBP = '" . $_GET['vexchange_GBP'] . "',";
        }

        if (isset($_GET['vairline_id'])) {
            $custom_query = "SELECT * FROM `data_travel_airline` WHERE id = " . (int)$_GET['vairline_id'];
            $data_custom_hotel_names = getwayConnect::getwayData($custom_query);
            if (count($data_custom_hotel_names) > 0) {
                $query .= " travel_airline_id   = '" . $_GET['vairline_id'] . "',"
                    . " travel_airline_name = '" . $data_custom_hotel_names[0]["name"] . "',";
            }
        }

        if (isset($_GET['varraival_date'])) {
            $query .= " travel_arraival_date = '" . $_GET['varraival_date'] . "',";
        }

        if (isset($_GET['vdeparture_date'])) {
            $query .= " travel_departure_date = '" . $_GET['vdeparture_date'] . "',";
        }


        if ($valueCount) {
            $date = getway::utc();
            $query .= " travel_date = '{$date}',";
            $query = rtrim($query, ",");

//dd($query);
            $query = getwayConnect::getwaySend($query, true);
            $data['itemId'] = $query;
            if (!$query) {
                $data = array("error" => "sql syntax");
            } else {
                $_REQUEST['hotel_data'] = (isset($_REQUEST['hotel_data'])) ? $_REQUEST['hotel_data'] : false;
                travelData::insertHotelList($_REQUEST['hotel_data'], $query);
                travelData::addLog($_COOKIE["suid"], $uniq);

                $custom_ids = "";

                if (isset($arrayCheckVals) && count($arrayCheckVals) > 0) {

                    foreach ($arrayCheckVals as $key => $value) {
                        extra_data_hotel_booking::update_hotel_confirmed((int)$key, (int)$value);
                        $custom_ids .= "$value ";
                    }
                    extra_data_hotel_booking::update_filters_confirmed_hotels($query, $custom_ids);
                }

            }
        }


        return $data;
    }


    public static function getEditData($arrayCheckInputs)
    {
        $message = 'Changed: ';
        $for_log_arr = ['price', 'currency', 'revenue', 'status', 'partial_pay', 'hotel_name'];
        $data = array("ok" => "updated");
        $valueCount = true;
        $prefix = "v";


        $query = "UPDATE `data_travel` SET ";
        $query_hotels = "UPDATE `data_hotel_booking` SET ";


        if ($valueCount) {


            $query = rtrim($query, ",");
            //$query_hotels = rtrim($query_hotels,',');
            if (isset($_REQUEST["itemId"])) {

                $itemId = htmlentities($_REQUEST["itemId"]);
                $tData = travelData::getBaseDataById($itemId);


                $data_travel = getwayConnect::getwayData("SELECT * FROM `data_travel` WHERE id = '{$itemId}'", PDO::FETCH_ASSOC);
                if (isset($data_travel[0])) {
                    $data_travel = $data_travel[0];
                }


                foreach ($_GET as $key => $value) {
                    $key = htmlentities($key);
                    $value = htmlentities($value);

                    if (in_array($key, $arrayCheckInputs) && $value != '') {
                        $mKey = "";
                        if (substr($key, 0, strlen($prefix)) == $prefix) {
                            $mKey = substr($key, strlen($prefix));
                        }


                        if (in_array($mKey, $for_log_arr) && $data_travel["travel_" . $mKey] != $value) {
                            $message .= $mKey . ': ' . $data_travel["travel_" . $mKey] . ' to ' . $value . '<br>';
                        }
                        $query .= "travel_" . $mKey . "='" . $value . "',";
                        /// >>>>>>>>>>>>>>>>>>


                    }
                }
                $query = substr_replace($query, "", -1);

                $query .= " WHERE id = '{$itemId}'";

                if (substr($query, 0, 6) === 'UPDATE') {
                    $quer_new = explode("WHERE", $query);
                    $query_new_q = $quer_new[0];
                    $query_new_condition = $quer_new[1];


                    if (isset($_GET['vprt_order_id'])) {

                        if ($data_travel['travel_prt_order_id'] != $_GET['vprt_order_id']) {
                            $message .= ' order id: ' . $data_travel['travel_prt_order_id'] . ' to ' . $_GET['vprt_order_id'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " , travel_prt_order_id = '" . $_GET['vprt_order_id'] . "'";
                    }

                    if (isset($_GET['vtotal_income'])) {
                        //for change update 1

                        if ($data_travel['travel_total_income'] != $_GET['vtotal_income']) {
                            $message .= ' total income: ' . $data_travel['travel_total_income'] . ' to ' . $_GET['vtotal_income'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " , travel_total_income = '" . $_GET['vtotal_income'] . "'";
                    }

                    if (isset($_GET['vWePaid'])) {
                        //for change update 2

                        if ($data_travel['travel_WePaid'] != $_GET['vWePaid']) {
                            $message .= ' WePaid: ' . $data_travel['travel_WePaid'] . ' to ' . $_GET['vWePaid'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " , travel_WePaid = '" . $_GET['vWePaid'] . "'";
                    }

                    if (isset($_GET['vCurrencyOfPrice'])) {

                        //for change update 1
                        if ($data_travel['travel_CurrencyOfPrice'] != $_GET['vCurrencyOfPrice']) {
                            $message .= ' Currency Of Price: ' . $data_travel['travel_CurrencyOfPrice'] . ' to ' . $_GET['vCurrencyOfPrice'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " ,travel_CurrencyOfPrice = '" . $_GET['vCurrencyOfPrice'] . "'";
                    }

                    if (isset($_GET['vCurrencyPartialPaied'])) {
                        if ($data_travel['travel_CurrencyPartialPaied'] != $_GET['vCurrencyPartialPaied']) {
                            $message .= ' Currency Partial Paied: ' . $data_travel['travel_CurrencyPartialPaied'] . ' to ' . $_GET['vCurrencyPartialPaied'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " ,travel_CurrencyPartialPaied = '" . $_GET['vCurrencyPartialPaied'] . "'";
                    }

                    if (isset($_GET['vexchange_USA'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_USA = '" . $_GET['vexchange_USA'] . "'";
                    }

                    if (isset($_GET['vexchange_EUR'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_EUR = '" . $_GET['vexchange_EUR'] . "'";
                    }

                    if (isset($_GET['vexchange_IRR'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_IRR = '" . $_GET['vexchange_IRR'] . "'";
                    }

                    if (isset($_GET['vexchange_GEL'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_GEL = '" . $_GET['vexchange_GEL'] . "'";
                    }

                    if (isset($_GET['vexchange_RUR'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_RUR = '" . $_GET['vexchange_RUR'] . "'";
                    }

                    if (isset($_GET['vexchange_GBP'])) {
                        $query_new_q = $query_new_q . " , travel_exchange_GBP = '" . $_GET['vexchange_GBP'] . "'";
                    }

                    if (isset($_GET['vairline_id'])) {

                        $custom_query = "SELECT * FROM `data_travel_airline` WHERE id = " . (int)$_GET['vairline_id'];
                        $data_custom_hotel_names = getwayConnect::getwayData($custom_query);

                        if (count($data_custom_hotel_names) > 0) {
                            $query_new_q = $query_new_q
                                . " , travel_airline_id   = '" . $_GET['vairline_id'] . "' "
                                . " , travel_airline_name = '" . $data_custom_hotel_names[0]["name"] . "' ";


                            if ($data_travel['travel_airline_name'] != $data_custom_hotel_names[0]["name"]) {
                                $message .= ' Airline Name: ' . $data_travel['travel_airline_name'] . ' to ' . $data_custom_hotel_names[0]["name"] . '<br>';
                            }
                        }
                    }

                    if (isset($_GET['varraival_date'])) {

                        if ($data_travel['travel_arraival_date'] != $_GET['varraival_date']) {
                            $message .= ' Arrival Date: ' . $data_travel['travel_arraival_date'] . ' to ' . $_GET['varraival_date'] . '<br>';
                        }

                        $query_new_q = $query_new_q . " , travel_arraival_date = '" . $_GET['varraival_date'] . "'";
                    }

                    if (isset($_GET['vdeparture_date'])) {

                        if ($data_travel['travel_arraival_date'] != $_GET['vdeparture_date']) {
                            $message .= ' Departure Date: ' . $data_travel['travel_arraival_date'] . ' to ' . $_GET['vdeparture_date'] . '<br>';
                        }


                        $query_new_q = $query_new_q . " , travel_departure_date = '" . $_GET['vdeparture_date'] . "'";
                    }


                    $query_new_q = $query_new_q . " , date_last_update = '" . date('Y-m-d H:i:s') . "'";

                    $query = $query_new_q . " WHERE " . $quer_new[1];

                }

                $data = array("ok" => "updated", "sql" => $query);

//dd($query);
                $query = getwayConnect::getwaySend($query);


                $_REQUEST['hotel_data'] = (isset($_REQUEST['hotel_data'])) ? $_REQUEST['hotel_data'] : false;
                $message_hotel = travelData::insertHotelList($_REQUEST['hotel_data'], $itemId, $data_travel);

                $message = $message . ' ' . $message_hotel;
                if (!$query) {
                    $data = array("error" => "sql syntax");
                } else {
                    travelData::addLog($_COOKIE["suid"], $tData[0]["travel_uniq"], "edited", $message);
                }
            } else {
                $data = array("error" => "no item id set");
            }
        } else {
            $data = array("ok" => "no_values");
        }


        if (isset($_POST['vConfirmHotel'])) {
            $arrayCheckVals = $_POST['vConfirmHotel'];
            $custom_ids = "";
            foreach ($arrayCheckVals as $key => $value) {
                extra_data_hotel_booking::update_hotel_confirmed((int)$key, (int)$value);
                $custom_ids .= "$value ";
            }
            extra_data_hotel_booking::update_filters_confirmed_hotels((int)$_GET["itemId"], $custom_ids);
            extra_data_hotel_booking::update_filters_travel_hotel_confirmations_json((int)$_GET["itemId"], json_encode($arrayCheckVals));

        }

        return $data;


    }


    public static function getCurrencyValue($curency_id)
    {
        $query = "SELECT * FROM currency WHERE id = $curency_id";
        $result = getwayConnect::getwayData($query);
        if (isset($result[0]["name"])) {
            return $result[0]["name"];
        } else {
            return "";
        }
    }


    public static function getServiceValue($service_id)
    {
        $query = "SELECT * FROM `data_service` WHERE id = $service_id";
        $result = getwayConnect::getwayData($query);
        if (isset($result[0]["name"])) {
            return $result[0]["name"];
        } else {
            return "";
        }
    }


    public static function getPaymantBankValue($id)
    {
        $query = "SELECT * FROM `data_payment` WHERE id = $id";
        $result = getwayConnect::getwayData($query);
        if (isset($result[0]["name"])) {
            return $result[0]["name"];
        } else {
            return "";
        }
    }


    public static function getStatusValue($id)
    {
        $query = "SELECT * FROM `data_status` WHERE id = $id";
        $result = getwayConnect::getwayData($query);
        if (isset($result[0]["name"])) {
            return $result[0]["name"];
        } else {
            return "";
        }
    }


    public static function getCountryValue($id)
    {
        $query = "SELECT * FROM `regions`  WHERE id = $id";
        $result = getwayConnect::getwayData($query);
        if (isset($result[0]["name"])) {
            return $result[0]["name"];
        } else {
            return "";
        }
    }


    public static function getCorrectDateFormatForExport($dateIncoming)
    {
        $result = "";

        if (isset($dateIncoming) && $dateIncoming != "0000-00-00 00:00" && strlen($dateIncoming) > 8) {

            $dateExplode1 = explode(" ", $dateIncoming);


            $cleandate = $dateExplode1[0];
            $dateDays = explode("-", $cleandate);

            if (count($dateDays) > 2) {
                $year = $dateDays[0];
                $mounth = $dateDays[1];
                $day = $dateDays[2];


                switch ($mounth) {
                    case "01":
                        $mounth = "Jan";
                        break;
                    case "02":
                        $mounth = "Feb";
                        break;
                    case "03":
                        $mounth = "Mar";
                        break;
                    case "04":
                        $mounth = "Apr";
                        break;
                    case "05":
                        $mounth = "May";
                        break;
                    case "06":
                        $mounth = "Jun";
                        break;
                    case "07":
                        $mounth = "Jul";
                        break;
                    case "08":
                        $mounth = "Aug";
                        break;
                    case "09":
                        $mounth = "Sep";
                        break;
                    case "10":
                        $mounth = "Oct";
                        break;
                    case "11":
                        $mounth = "Nov";
                        break;
                    case "12":
                        $mounth = "Dec";
                        break;

                }
                $result = $day . "-" . $mounth . "-" . $year;
            }
        }
        return $result;

    }


}


?>