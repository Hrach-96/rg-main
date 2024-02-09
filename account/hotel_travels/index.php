<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$pageName = "travel";
$rootF = "../..";
include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/controls/GetDatabaseContent.php';

date_default_timezone_set("Asia/Yerevan");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
if (!$access) {
    header("location:../../login");
} else {
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"], $pageName);
    $levelArray = explode(",", $level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
}
page::cmd();
if(isset($_REQUEST['remove_room_extra']) && $_REQUEST['remove_room_extra']){
    if($_REQUEST['type'] == 'room'){
        getwayConnect::getwaySend("DELETE FROM new_hotel_room_reservations WHERE id = '" . $_REQUEST['id'] . "'");
    }
    else if($_REQUEST['type'] == 'extra'){
        getwayConnect::getwaySend("DELETE FROM new_hotel_room_reservations_extra WHERE id = '" . $_REQUEST['id'] . "'");
    }
    return true;
}
if(isset($_REQUEST['row_information']) && $_REQUEST['row_information']){
    $reservationInfo = getwayConnect::getwayData("SELECT * from new_hotel_reservation where id='" . $_REQUEST['row_id'] . "'");
    $result = Array();
    $result['main'] = $reservationInfo;
    $roomInfo = getwayConnect::getwayData("SELECT * from new_hotel_room_reservations where row_id='" . $_REQUEST['row_id'] . "'");
    $result['room'] = $roomInfo;
    $roomInfo = getwayConnect::getwayData("SELECT * from new_hotel_room_reservations_extra where row_id='" . $_REQUEST['row_id'] . "'");
    $result['roomextra'] = $roomInfo;
    $guestInfo = getwayConnect::getwayData("SELECT * from new_hotel_guest_info where row_id='" . $_REQUEST['row_id'] . "'");
    if($guestInfo){
       $result['guestinfo'] = $guestInfo[0];
    }
    else{
       $result['guestinfo'] = Array();
    }
    $actionsInfo = getwayConnect::getwayData("SELECT * from hotel_action_connect where row_id='" . $_REQUEST['row_id'] . "'");
    foreach($actionsInfo as $key=>$value){
        $actionsInfo[$key]['userInfo'] = getwayConnect::getwayData("SELECT * from user where id='" . $value['user_id'] . "'")[0];
    }
    $result['actions'] = $actionsInfo;
    print json_encode($result);die;
}
if(isset($_REQUEST['insert_room_reservation']) && $_REQUEST['insert_room_reservation']){
    $roomAddInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_room_reservations (row_id,room_number_id,room_type_id,check_in_sm,check_out_sm,price,user_id,data_inserted) VALUES ('" . $_REQUEST['row_id'] . "','" . $_REQUEST['room_number_id'] . "','" . $_REQUEST['room_type_id'] . "','" . $_REQUEST['check_in_sm'] . "','" . $_REQUEST['check_out_sm'] . "','" . $_REQUEST['price'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')");
    return true;
}
if(isset($_REQUEST['get_usernames']) && $_REQUEST['get_usernames']){
    $userInfos = getwayConnect::getwayData("SELECT * from user");
    $result = Array();
    foreach($userInfos as $user){
        $result[$user['id']] = $user;
    }
    print json_encode($result);die;
}
if(isset($_REQUEST['insert_room_extra_reservation']) && $_REQUEST['insert_room_extra_reservation']){
    $extraRoomAddInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_room_reservations_extra (row_id,extra_type_id,extra_count,extra_price,user_id,data_inserted) VALUES ('" . $_REQUEST['row_id'] . "','" . $_REQUEST['extra_type_id'] . "','" . $_REQUEST['extra_count'] . "','" . $_REQUEST['extra_price'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')");
    return true;
}
if(isset($_REQUEST['insert_action_reservation']) && $_REQUEST['insert_action_reservation']){
    $actionAddInfo = getwayConnect::getwaySend("INSERT INTO hotel_action_connect (row_id,action_id,information, status,done,done_user_id,done_datetime,user_id,data_inserted) VALUES ('" . $_REQUEST['row_id'] . "','" . $_REQUEST['action_id'] . "','" . $_REQUEST['action_information'] . "','" . $_REQUEST['status_action'] . "',0,0,0,'" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')",true);
    $actionsInfo = getwayConnect::getwayData("SELECT * from hotel_action_connect where id='" . $actionAddInfo . "'");
    $actionsInfo[0]['userInfo'] = getwayConnect::getwayData("SELECT * from user where id='" . $userData[0]['id'] . "'")[0];
    print json_encode($actionsInfo);die;
}
if(isset($_REQUEST['remove_sess_row']) && $_REQUEST['remove_sess_row']){
    $_SESSION['row_id'] = 0;
    return true;
}
if(isset($_REQUEST['update_done_action']) && $_REQUEST['update_done_action']){
    getwayConnect::getwayData("UPDATE  hotel_action_connect SET " . " done = '1', done_user_id='" . $userData[0]['id'] . "', done_datetime = '" . date('Y-m-d H:i:s') . "' " . " WHERE id = '" . $_REQUEST['row_id'] . "'");

}
if(isset($_REQUEST['insert_reservation']) && $_REQUEST['insert_reservation']){
    $addInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_reservation (partner_id,main_check_in,main_check_out,reservation_status,reservation_number,important_notes,sales_point_id,whatsap_viber,first_name,last_name,phone_2,email,total_price,total_price_currency_id,net_price,net_price_currency_id,payment_status_id,payment_type_id,paid_amount,paid_amount_currency_id,more_details,user_id,data_inserted,date_last_update) VALUES ('" . $_REQUEST['partner_id'] . "','" . $_REQUEST['main_check_in'] . "','" . $_REQUEST['main_check_out'] . "','" . $_REQUEST['reservation_status'] . "','" . $_REQUEST['reservation_number'] . "','" . checkaddslashes($_REQUEST['important_notes']) . "','" . $_REQUEST['sales_point_id'] . "','" . checkaddslashes($_REQUEST['whatsap_viber']) . "','" . checkaddslashes($_REQUEST['first_name']) . "','" . checkaddslashes($_REQUEST['last_name']) . "','" . checkaddslashes($_REQUEST['phone_2']) . "','" . checkaddslashes($_REQUEST['email']) . "','" . $_REQUEST['total_price'] . "','" . $_REQUEST['total_price_currency_id'] . "','" . $_REQUEST['net_price'] . "','" . $_REQUEST['net_price_currency_id'] . "','" . $_REQUEST['payment_status_id'] . "','" . $_REQUEST['payment_type_id'] . "','" . $_REQUEST['paid_amount'] . "','" . $_REQUEST['paid_amount_currency_id'] . "','" . $_REQUEST['more_details'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "') ",true);
    $_SESSION['row_id'] = $addInfo;
    if(isset($_REQUEST['action_id']) && $_REQUEST['action_id'] > 0){
        $actionAddInfo = getwayConnect::getwaySend("INSERT INTO hotel_action_connect (row_id,user_id,data_inserted,action_id,information,status,done,done_user_id,done_datetime) VALUES ('" . $addInfo . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "','" . $_REQUEST['action_id'] . "','" . $_REQUEST['action_information'] . "','" . $_REQUEST['status_action'] . "',0,0,0)");
    }
    if(isset($_REQUEST['room_number_id']) && $_REQUEST['room_number_id'] > 0){
        $roomAddInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_room_reservations (row_id,room_number_id,room_type_id,check_in_sm,check_out_sm,price,user_id,data_inserted) VALUES ('" . $addInfo . "','" . $_REQUEST['room_number_id'] . "','" . $_REQUEST['room_type_id'] . "','" . $_REQUEST['check_in_sm'] . "','" . $_REQUEST['check_out_sm'] . "','" . $_REQUEST['price_small'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')");
    }
    if(isset($_REQUEST['extra_type_id']) && $_REQUEST['extra_type_id'] > 0){
        $extraRoomAddInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_room_reservations_extra (row_id,extra_type_id,extra_count,extra_price,user_id,data_inserted) VALUES ('" . $addInfo . "','" . $_REQUEST['extra_type_id'] . "','" . $_REQUEST['extra_count'] . "','" . $_REQUEST['extra_price'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')");
    }
    if(isset($_REQUEST['guest_country']) && $_REQUEST['guest_country'] > 0){
        $guestAddInfo = getwayConnect::getwaySend("INSERT INTO new_hotel_guest_info (row_id,country_id,city_id,exact_city,address,nationality_id,language_id,birthday,passport,knows_armenian,user_id,data_inserted) VALUES ('" . $addInfo . "','" . $_REQUEST['guest_country'] . "','" . $_REQUEST['guest_city'] . "','" . $_REQUEST['guest_exact_city'] . "','" . $_REQUEST['guest_address'] . "','" . $_REQUEST['guest_nationality'] . "','" . $_REQUEST['guest_language'] . "','" . $_REQUEST['guest_birthday'] . "','" . $_REQUEST['guest_passport'] . "','" . $_REQUEST['knows_armenian'] . "','" . $userData[0]['id'] . "','" . date("Y-m-d H:i:s") . "')");
    } 
}
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
    } else{
        return $str;
    }
}
if(isset($_REQUEST['update_reservation']) && $_REQUEST['update_reservation']){
    getwayConnect::getwayData("UPDATE new_hotel_reservation SET partner_id='" . $_REQUEST['partner_id'] . "',main_check_in='" . $_REQUEST['main_check_in'] . "',main_check_out='" . $_REQUEST['main_check_out'] . "',reservation_status='" . $_REQUEST['reservation_status'] . "',reservation_number='" . checkaddslashes($_REQUEST['reservation_number']) . "',important_notes='" . $_REQUEST['important_notes'] . "',sales_point_id='" . $_REQUEST['sales_point_id'] . "',first_name='" . checkaddslashes($_REQUEST['first_name']) . "',last_name='" . checkaddslashes($_REQUEST['last_name']) . "',whatsap_viber ='" . checkaddslashes($_REQUEST['whatsap_viber']) . "',phone_2='" . checkaddslashes($_REQUEST['phone_2']) . "',email='" . checkaddslashes($_REQUEST['email']) . "',total_price='" . $_REQUEST['total_price'] . "',total_price_currency_id='" . $_REQUEST['total_price_currency_id'] . "',net_price='" . $_REQUEST['net_price'] . "',net_price_currency_id='" . $_REQUEST['net_price_currency_id'] . "',payment_status_id='" . $_REQUEST['payment_status_id'] . "',payment_type_id='" . $_REQUEST['payment_type_id'] . "',paid_amount='" . $_REQUEST['paid_amount'] . "',paid_amount_currency_id='" . $_REQUEST['paid_amount_currency_id'] . "',more_details='" . $_REQUEST['more_details'] . "',user_id='" . $userData[0]['id'] . "',date_last_update='" . date("Y-m-d H:i:s") . "' WHERE id='" . $_REQUEST['row_id'] . "'");
    if(isset($_REQUEST['guest_country']) && $_REQUEST['guest_country'] > 0){
        getwayConnect::getwayData("UPDATE new_hotel_guest_info SET country_id='" . $_REQUEST['guest_country'] . "',city_id='" . $_REQUEST['guest_city'] . "',exact_city='" . $_REQUEST['guest_exact_city'] . "',address='" . $_REQUEST['guest_address'] . "',nationality_id='" . $_REQUEST['guest_nationality'] . "',language_id='" . $_REQUEST['guest_language'] . "',birthday='" . $_REQUEST['guest_birthday'] . "',passport='" . $_REQUEST['guest_passport'] . "',knows_armenian='" . $_REQUEST['knows_armenian'] . "' WHERE row_id='" . $_REQUEST['row_id'] . "'");
    }
    return true;
}
$all_hotels = getwayConnect::getwayData("SELECT * FROM data_hotels");
$all_hotel_array = Array();
foreach($all_hotels as $hotel){
    $all_hotel_array[$hotel['id']] = $hotel;
}
$all_partners = getwayConnect::getwayData("SELECT * FROM travel_partner");
$all_partner_array = Array();
foreach($all_partners as $partner){
    $all_partner_array[$partner['id']] = $partner;
}
$all_users = getwayConnect::getwayData("SELECT * FROM user");
$payment_statuses = getwayConnect::getwayData("SELECT * FROM data_payment_status where active = 1");
$travel_partners = getwayConnect::getwayData("SELECT * FROM travel_partner where active = 1");
$all_users_array = Array();
foreach($all_users as $user){
    $all_users_array[$user['id']] = $user;
}
$res_sql = "SELECT * from new_hotel_reservation where";

$type_value = 'main_check_in';
if(isset($_GET['type'])){
    $type_value = $_GET['type'];
}
if(!isset($_GET['end_date']) && !isset($_GET['start_date'])){
    // by default today
    $res_sql.=" " . $type_value . " >= '" . date("Y-m-d") . "'";
    $res_sql.=" and " . $type_value . " <= '" . date("Y-m-d") . "'";
}
if(isset($_GET['start_date'])){
    $res_sql.=" " . $type_value . " >= '" . $_GET['start_date'] . "'";
}
if(isset($_GET['end_date'])){
    $res_sql.=" and " . $type_value . " <= '" . $_GET['end_date'] . "'";
}
if(isset($_GET['payment_status']) && $_GET['payment_status'] != 'all'){
    $res_sql.=" and payment_status_id = '" . $_GET['payment_status'] . "'";
}
if(isset($_GET['sales_point_id']) && $_GET['sales_point_id'] != 'all'){
    $res_sql.=" and sales_point_id = '" . $_GET['sales_point_id'] . "'";
}
if(isset($_GET['search']) && $_GET['search'] != ''){
    $res_sql.=" and (first_name like '%" . $_GET['search'] . "%' or last_name like '%" . $_GET['search'] . "%' or whatsap_viber like '%" . $_GET['search'] . "%' or phone_2 like '%" . $_GET['search'] . "%' or email like '%" . $_GET['search'] . "%')";
}
$reservations = getwayConnect::getwayData($res_sql);
$displayModalSess = 0;
if(isset($_SESSION['row_id'])){
    $displayModalSess = $_SESSION['row_id'];
}
$access_token_parameters = array();
$curl = curl_init("http://new.regard-group.ru/currency.php");
curl_setopt($curl,CURLOPT_POST,true);
curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
$currencyResult = curl_exec($curl);
curl_close($curl);
$exchange_rate = json_decode($currencyResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Apay gateway">
    <meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
    <meta name="author" content="Hrachya Avagyan, Ruben Mnatsakanyan">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.css" />
    <title>New Travel</title>
    <style>
        .bg-custom-light{
            background: #eeeeee;
            padding:5px;
        }
        .border-right{
            border-right:1px solid black;
        }
        .border-left{
            border-left:1px solid black;
        }
        .custom-mt-2{
            margin-top:2px;
        }
        .update-row:hover{
            cursor: pointer;
            text-decoration: underline;
        }
        .btn_remove_room_extra:hover{
            cursor: pointer;
        }
        .displayRoomReservationResult{
            margin-top:15px;
            padding:5px;
        }
        .roomReservResultSm{
            border:1px solid black;
            margin-top:5px;
            margin-bottom:5px;
        }
        .d-none{
            display:none;
        }
        .custom-margin-left-10{
            margin-left: 10px;
        }
        .custom-margin-right-10{
            margin-right: 10px;
        }
        .font-12{
            font-size:12px;
        }
    </style>
</head>
<body>
    <input type='hidden' value="<?php echo $displayModalSess ?>" class='displayModalSess'>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">RG-SYSTEM</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">

                        <?php

                        $fData = page::buildFilter($level[0]["user_level"], "travel");
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>

                    </ul>
                </li>
                <li>
                    <a href="#" data-toggle="modal" data-target=".add-modal" >ADD</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class='container' style='margin-top:81px;width: 80%'>
    <div class='row'>
        <form>
            <label for='check_in_radio' style='margin-left:10px'>C:In</label>
            <input name='type' type='radio' <?php echo ($type_value == 'main_check_in')? 'checked':'' ?> value='main_check_in' checked id="check_in_radio">
            <label for='check_out_radio'>C:Out</label>
            <input name='type' type='radio' <?php echo ($type_value == 'main_check_out')? 'checked':'' ?> value='main_check_out' id="check_out_radio">

            <label for='start_date' style='margin-left:10px'>From</label>
            <input name='start_date' type="date" class='btn btn-default' value="<?php echo (isset($_GET['start_date'])? $_GET['start_date'] : '') ?>"  id="start_date"/>
            <label for='end_date' style="margin-left: 10px">To</label>
            <input type="date" class='btn btn-default' value="<?php echo (isset($_GET['end_date'])? $_GET['end_date'] : '') ?>" name="end_date" style="margin-right: 20px;" id="end_date"/>

            <select name='payment_status' class='btn btn-default' style='text-align: left'>
                <option value="all">All</option>
                <?php
                    foreach($payment_statuses as $status){
                        ?>
                            <option <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == $status['id'] )? 'selected' : '' ?> value="<?php echo $status['id'] ?>"><?php echo $status['name'] ?></option>
                        <?php
                    }
                ?>
            </select>
             <select name='sales_point_id' class='btn btn-default' style='text-align: left'>
                <option value="all">All</option>
                <?php
                    foreach($travel_partners as $partner){
                        ?>
                            <option <?php echo (isset($_GET['sales_point_id']) && $_GET['sales_point_id'] == $partner['id'] )? 'selected' : '' ?> value="<?php echo $partner['id'] ?>"><?php echo $partner['name'] ?></option>
                        <?php
                    }
                ?>
            </select>
            <input type='text' name='search' value="<?php echo (isset($_GET['search']))? $_GET['search'] : '' ?>" class='btn btn-default' style='margin-left:10px;text-align: left' placeholder="Search">
            <button type='submit' class='btn btn-default'>Search</button>
        </form>
        <a href="?start_date=<?= date("Y-m-d")?>&end_date=<?=date("Y-m-d")?>" class='btn btn-default'>Այսօր</a>
        <a href="?start_date=<?= date("Y-m-d", strtotime("+1 day"))?>&end_date=<?=date("Y-m-d", strtotime("+1 day"))?>" class='btn btn-default'>Վաղը</a>
    </div>
    <div class='row'>
        <table id="mainTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hotels</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Price</th>
                    <th>Contacts</th>
                    <th>Last Updated Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($reservations){
                        foreach($reservations as $data){
                            ?>
                                <tr>
                                    <td><span data-row-id="<?php echo $data['id'] ?>" class='update-row text-primary'> NUM- <?= $data['id']?></span><br><?= $data['first_name'] . " " . $data['last_name']?></td>
                                    <td style='width:20%'><?= $all_hotel_array[$data['partner_id']]['name']?><br><?= $all_partner_array[$data['sales_point_id']]['name']?></td>
                                    <td><img src="../../template/images/status/<?=$data['reservation_status']?>.png"></td>
                                    <td><?= date("d-M-Y",strtotime($data['main_check_in']))?></td>
                                    <td><?= date("d-M-Y",strtotime($data['main_check_out']))?></td>
                                    <td><?= $data['total_price']?>/<span style='color:red'><?= $data['total_price'] - $data['paid_amount'] ?></span> </td>
                                    <td><?= $data['whatsap_viber']?><br><?= $data['email']?></td>
                                    <td><?= date("d-M-Y H:i:s",strtotime($data['date_last_update']))?></td>
                                </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade update-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-center">ID: <span class='rowIdUpdate'></span></h5>
        </div>
        <div class="modal-body">
            <div class='row bg-custom-light'>
                <div class='col-md-1 col-sm-3 col-xs-3'>
                    <label for='partner_id_up'>Hotel:<span class='text-danger'>*</span></label>
                    <label for='main_check_in_up'>C In:</label>
                    <label for='main_check_out_up'>C Out:</label>
                </div>
                <div class='col-md-3 border-right col-xs-9 col-sm-9'>
                    <select name="partner_id_up" style='width:100%' id="partner_id_up">
                        <option value="0">Hotel:</option>
                        <?= page::buildOptions("data_hotels", false, false, false, true, 'ORDER BY `name`') ?>
                    </select><br>
                    <input type='date' style='width:100%' class='custom-mt-2' id="main_check_in_up" placeholder="Check in"><br>
                    <input type='date' style='width:100%' class='custom-mt-2' id="main_check_out_up" placeholder="Check out">
                </div>
                <div class='col-md-4 text-center border-right'>
                    <label for='sales_point_id_up'>Sales Point:<span class='text-danger'>*</span></label>
                    <select name="sales_point_id_up" id="sales_point_id_up">
                        <option value="0">Registered by</option>
                        <?= page::buildOptions("travel_partner", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select style='width:100%' name="reservation_status" id="reservation_status_up">
                        <option value=''>Reservation Status:<span class='text-danger'>*</span></option>
                         <?= page::buildOptions("data_status", false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select> 
                    <input placeholder="Booking number" id='reservation_number_up' class='custom-mt-2' style='width:100%'>
                </div>              
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-2'>
                    <input type='text' class='form-control' id='first_name_up' placeholder='First Name'>
                </div>
                <div class='col-md-3'>
                    <input type='text' class='form-control' id='last_name_up' placeholder='Last Name'>
                </div>
                <div class='col-md-2'>
                    <input type='text' placeholder="Phone/Whatsapp/Viber" class='form-control' id='whatsap_viber_up'>
                </div>
                <div class='col-md-2'>
                    <input type='text' placeholder="Phone 2" id='phone_2_up' class='form-control'>
                </div>
                <div class='col-md-3'>
                    <input placeholder="E-mail" id='email_up' class='form-control'>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-12' style='padding:0'>
                    <select name="room_number_id_up" id="room_number_id_up" style="max-width:60px;">
                        <option value="">N:</option>
                        <?= page::buildOptions("data_hotel_room_numbers", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select name="room_type_id" id="room_type_id_up" style='width:65px'>
                        <option value="">Room Type</option>
                        <?= page::buildOptions("data_hotel_room_type", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <label for='check_in_sm_up'>C In:</label>
                    <input type='datetime-local' name='check_in_sm_up' id='check_in_sm_up' style='width:150px' placeholder="Check in">
                    <label for='check_out_sm_up'>C Out:</label>
                    <input type='datetime-local' id='check_out_sm_up' style='width:150px' placeholder="Check out" name='check_out_sm_up'>
                    <input placeholder="Price" name='price_small_up' id='price_small_up' style='width:67px'>
                    <span class='btn btn-success addRoomReservation'>ADD</span>
                    <select name="extra_type_id_up" id="extra_type_id_up" style="max-width:60px;">
                        <option value="">Extra</option>
                        <?= page::buildOptions("hotel_order_extra", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select name="extra_count_up" id="extra_count_up">
                        <?php
                        foreach (range(1, 20) as $number) {
                            echo "<option value=\"{$number}\">{$number}</option>";
                        }
                        ?>
                    </select>
                    <input placeholder="Price" id='extra_price_up' style='width:65px'>
                    <span class='btn btn-success addRoomExtraReservation'>ADD</span>
                    <div class='displayRoomReservationResult'></div>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px;margin-bottom:20px'>
                <table border='1' class='actionTableDisplay d-none' style='width:100%'>
                    <thead>
                        <tr>
                            <th>Action Details</th>
                            <th>Edited by</th>
                            <th>On</th>
                            <th>Note</th>
                            <th>Done</th>
                            <th>Done By</th>
                            <th>Done Date</th>
                        </tr>
                    </thead>
                    <tbody class='displayActionsPart'>
                    </tbody>
                </table>
                <div class='col-md-3' style='margin-top:10px'>
                    Action
                    <select id='status_action_up'>
                        <option value="">Action Type</option>
                        <option value="1">What Happened?</option>
                        <option value="0">Required to do</option>
                    </select>
                </div>
                <div class='col-md-4' style='margin-top:10px'>
                     <select id="action_id_up" class='d-none' style="max-width:200px;">
                        <option value=''>Action Details</option>
                         <?= page::buildOptions("hotel_actions", false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                </div>
                <div class='col-md-5' style='margin-top:10px'>
                    <div class='actionMain_up'></div>
                </div>
            </div>
            <span class='currencyTotalResult'></span>
            <div class='row bg-custom-light'>
                <div class='col-md-3'>
                    <label for='total_price_up'>Price:</label>
                    <input placeholder="Total Price" class='benefitCalculate' data-variable="_up" id='total_price_up' style='width:35%'>
                    <select name='total_price_currency_id_up' id='total_price_currency_id_up' style='width:35%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <label for='net_price_up'>Net: </label>
                    <input placeholder="Net" id='net_price_up' class='benefitCalculate' data-variable="_up" style='width:35%;margin-left:12px'>
                    <select name='net_price_currency_id_up' id='net_price_currency_id_up' style='width:35%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <input placeholder="Total Benefit" disabled id='total_benefit_up' class='custom-mt-2 text-danger' style='width:100%'>
                </div>
                <div class='col-md-4'>
                    <select id='payment_status_id_up' style='width:40%'>
                        <?= page::buildOptions("data_payment_status",false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                    <input placeholder="Paid Amount" id='paid_amount_up' style='width:30%'>
                    <select id='paid_amount_currency_id_up' style='width:19%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <select id='payment_type_id_up' style='width:40%'>
                        <?= page::buildOptions("data_payment",false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                    <input placeholder="More details" id='more_details_up' style='width:30%'>
                </div>
                <div class='col-md-4'>
                    <textarea rows='3' style='width:100%;height:100%' id='important_notes_up' placeholder="Important notes"></textarea>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <select name="guest_country_up" style='width:180px' class='form-control' id="guest_country_up">
                        <option value=''>Country of residence</option>
                        <?= page::buildOptions("regions") ?>
                    </select>
                </div>
                <div class='col-md-3'>
                    <select name="guest_city_up" class='form-control' style='width:140px' id="guest_city_up" disabled="true">
                        <option value="">----</option>
                    </select>
                </div>
                <div class='col-md-3'>
                    <input type='text' class='form-control' id='guest_exact_city_up' placeholder="City">
                </div>
                <div class='col-md-3'>
                    <textarea class="form-control" style="height:48px;" name="guest_address_up" id="guest_address_up" placeholder="Address/Street"></textarea>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <select name="guest_nationality_up" id='guest_nationality_up' class='form-control'>
                        <option>Nationality</option>
                        <?= page::buildOptions("regions") ?>
                    </select>
                </div>
                <div class='col-md-2'>
                    <input type='date' id='guest_birthday_up' name='guest_birthday_up' class='form-control' >
                </div>
                <div class='col-md-2'>
                    <input type='text' class='form-control' style='width:110px' name='guest_passport_up' id='guest_passport_up' placeholder="Passport Info">
                </div>
                <div class='col-md-3'>
                    <select name="guest_language_up" id='guest_language_up' class='form-control'>
                        <option>Language</option>
                        <?= page::buildOptions("delivery_language") ?>
                    </select>
                </div>
                <div class='col-md-2'>
                    <input type='checkbox' id='guest_know_armenian_up' name='guest_know_armenian_up' >
                    <label for='guest_know_armenian_up' class='font-12' >Knows Armenian</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary updateReservationBtn">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>
<div class="modal fade add-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-center">New!</h5>
        </div>
        <div class="modal-body">
            <div class='row bg-custom-light'>
                <div class='col-md-1 col-sm-3 col-xs-3'>
                    <label for='partner_id'>Hotel:<span class='text-danger'>*</span></label>
                    <label for='main_check_in'>C In:</label>
                    <label for='main_check_out'>C Out:</label>
                </div>
                <div class='col-md-3 border-right col-xs-9 col-sm-9'>
                    <select name="partner_id" style='width:100%' id="partner_id">
                        <option value="0">Hotel:</option>
                        <?= page::buildOptions("data_hotels", false, false, false, true, 'ORDER BY `name`') ?>
                    </select><br>
                    <input type='date' style='width:100%' class='custom-mt-2' id="main_check_in" placeholder="Check in"><br>
                    <input type='date' style='width:100%' class='custom-mt-2' id="main_check_out" placeholder="Check out">
                </div>
                <div class='col-md-4 text-center border-right'>
                    <label for='sales_point_id'>Sales Point:<span class='text-danger'>*</span></label>
                    <select name="sales_point_id" id="sales_point_id">
                        <option value="0">Registered by<span class='text-danger'>*</span></option>
                        <?= page::buildOptions("travel_partner", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select style='width:100%' name="reservation_status" id="reservation_status">
                        <option value=''>Reservation Status:<span class='text-danger'>*</span></option>
                         <?= page::buildOptions("data_status", false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select> 
                    <input placeholder="Booking number" id='reservation_number' class='custom-mt-2' style='width:100%'>
                </div>              
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-2'>
                    <input type='text' class='form-control' id='first_name' placeholder='First Name'>
                </div>
                <div class='col-md-3'>
                    <input type='text' class='form-control' id='last_name' placeholder='Last Name'>
                </div>
                <div class='col-md-2'>
                    <input placeholder="Phone/Whatsapp/Viber" class='form-control' id='whatsap_viber'>
                </div>
                <div class='col-md-2'>
                    <input placeholder="Phone 2" id='phone_2' class='form-control'>
                </div>
                <div class='col-md-3'>
                    <input placeholder="E-mail" id='email' class='form-control'>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-12' style='padding:0'>
                    <select name="room_number_id" id="room_number_id" style="max-width:60px;">
                        <option value="">N:</option>
                        <?= page::buildOptions("data_hotel_room_numbers", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select name="room_type_id" id="room_type_id" style='width:65px'>
                        <option value="">Room Type</option>
                        <?= page::buildOptions("data_hotel_room_type", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <label for='check_in_sm'>C In:</label>
                    <input type='datetime-local' name='check_in_sm' style='width:150px' id='check_in_sm' placeholder="Check in">
                    <label for='check_out_sm'>C Out:</label>
                    <input type='datetime-local' id='check_out_sm' style='width:150px' placeholder="Check out" name='check_out_sm'>
                    <input placeholder="Price" name='price_small' id='price_small' style='width:70px'>
                    <select name="extra_type_id" id="extra_type_id" style="max-width:60px;margin-left:45px;">
                        <option value="">Extra</option>
                        <?= page::buildOptions("hotel_order_extra", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
                    <select name="extra_count" id="extra_count">
                        <?php
                        foreach (range(1, 20) as $number) {
                            echo "<option value=\"{$number}\">{$number}</option>";
                        }
                        ?>
                    </select>
                    <input placeholder="Price" id='extra_price' style='width:70px'>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-12 text-center'>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <div >
                        Action
                        <select id='status_action'>
                            <option value="">Action Type</option>
                            <option value="1">What Happened?</option>
                            <option value="0">Required to do</option>
                        </select>
                    </div>
               </div>
                <div class='col-md-5'>
                     <select id="action_id" class='d-none' style="max-width:200px;">
                        <option value=''>Action Details</option>
                         <?= page::buildOptions("hotel_actions", false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                </div>
                <div class='col-md-4'>
                    <div class='actionMain'></div>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <label for='total_price'>Price:</label>
                    <input placeholder="Total Price" class='benefitCalculate' data-variable='' id='total_price' style='width:35%'>
                    <select name='total_price_currency_id' id='total_price_currency_id' style='width:35%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <label for='net_price'>Net: </label>
                    <input placeholder="Net" id='net_price' class='benefitCalculate' data-variable='' style='width:35%;margin-left:12px'>
                    <select name='net_price_currency_id' id='net_price_currency_id' style='width:35%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <input placeholder="Total Benefit" disabled id='total_benefit' class='custom-mt-2 text-danger' style='width:100%'>
                </div>
                <div class='col-md-4'>
                    <select id='payment_status_id' style='width:40%'>
                        <?= page::buildOptions("data_payment_status",false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                    <input placeholder="Paid Amount" id='paid_amount' style='width:30%'>
                    <select id='paid_amount_currency_id' style='width:19%'>
                        <?= page::buildOptions("currency") ?>
                    </select>
                    <select id='payment_type_id' style='width:40%'>
                        <?= page::buildOptions("data_payment",false, false, false, true, 'ORDER BY `ordering` ') ?>
                    </select>
                    <input placeholder="More details" id='more_details' style='width:30%'>
                </div>
                <div class='col-md-4'>
                    <textarea rows='3' style='width:100%;height:100%' id='important_notes' placeholder="Important notes"></textarea>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <select name="guest_country" style='width:180px' class='form-control' id="guest_country">
                        <option value=''>Country of residence</option>
                        <?= page::buildOptions("regions") ?>
                    </select>
                </div>
                <div class='col-md-3'>
                    <select name="guest_city" class='form-control' style='width:140px' id="guest_city" disabled="true">
                        <option value="">----</option>
                    </select>
                </div>
                <div class='col-md-3'>
                    <input type='text' class='form-control' id='guest_exact_city' placeholder="City">
                </div>
                <div class='col-md-3'>
                    <textarea class="form-control" style="height:48px;" name="guest_address" id="guest_address" placeholder="Address/Street"></textarea>
                </div>
            </div>
            <div class='row bg-custom-light' style='margin-top:20px'>
                <div class='col-md-3'>
                    <select name="guest_nationality" id='guest_nationality' class='form-control'>
                        <option>Nationality</option>
                        <?= page::buildOptions("regions") ?>
                    </select>
                </div>
                <div class='col-md-2'>
                    <input type='date' id='guest_birthday' name='guest_birthday' class='form-control' >
                </div>
                <div class='col-md-2'>
                    <input type='text' class='form-control' style='width:110px' name='guest_passport' id='guest_passport' placeholder="Passport Info">
                </div>
                <div class='col-md-2'>
                    <select name="guest_language" id='guest_language' class='form-control'>
                        <option>Language</option>
                        <?= page::buildOptions("delivery_language") ?>
                    </select>
                </div>
                <div class='col-md-3'>
                    <input type='checkbox' id='guest_know_armenian' name='guest_know_armenian' >
                    <label for='guest_know_armenian' class='font-12'>Knows Armenian</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary addReservationBtn">Add</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.js"></script>
<script>
    $(document).ready(function(){
        var usd = <?= $exchange_rate->USD ?>;
        var rub = <?= $exchange_rate->RUB ?>;
        var gbp = <?= $exchange_rate->GBP ?>;
        var eur = <?= $exchange_rate->EUR ?>;
        $('#mainTable').DataTable({order:[[0,"desc"]]});
        var selectedRowId= '';
        var usernames = Array();
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                get_usernames: true,
            },
            success: function(resp){
                usernames = JSON.parse(resp);
            }
        })
        setTimeout(function(){
            var displayModalSess = $(".displayModalSess").val();
            if(displayModalSess > 0){
                $('.update-row[data-row-id="' + displayModalSess + '"]').click();
            }
        },500)
        
        $('.update-modal').on('hide.bs.modal', function () {
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    remove_sess_row: true,
                },
                success: function(resp){
                    console.log(resp)
                }
            })
        })
        function addRoomReservationResult(array){
            var html = '';
            for(var i = 0;i< array.length;i++){
                var check_in_date = array[i]['check_in_sm'].split(' ');
                var check_out_date = array[i]['check_out_sm'].split(' ');
                var night_count = differenceBetweenTwoDates(array[i]['check_in_sm'],array[i]['check_out_sm']);
                var text_night_day = night_count + ' Nights';
                var count_price_calc = 1;
                if(night_count == 0){
                    text_night_day = '1 Day';
                }
                else if(night_count > 0){
                    count_price_calc = night_count;
                }
                var array_price = array[i]['price'];
                var room_number = $('#room_number_id_up option[value="' + array[i]['room_number_id'] + '"]').html();
                var room_type = $('#room_type_id_up option[value="' + array[i]['room_type_id'] + '"]').html();
                html+= "<div title='Room' class='col-md-12 roomReservResultSm text-left'>"
                    html+= "<span class='text-danger btn_remove_room_extra' data-type='room' data-id='" + array[i]['id'] + "' style='font-weight:bolder;float:right'>X</span>"
                    html+= '<b>Hotel</b> - ' + $('#partner_id_up').find(":selected").html() + ' <span class="custom-margin-left-10 custom-margin-right-10">|</span>';
                    html+= '<b>CHECK IN:</b> ' + replaceDatetimeFormat(check_in_date[0]) + " " + check_in_date[1] + '; <span class="custom-margin-left-10 custom-margin-right-10">|</span> <b>CHECK OUT:</b> ' + replaceDatetimeFormat(check_out_date[0]) + " " + check_out_date[1] + " <span class='custom-margin-left-10 custom-margin-right-10'>|</span> (" + array_price.toFixed(2) + ") " + text_night_day + " <br>";
                    html+= "<b>Room Type:</b> " + room_type + ' <span class="custom-margin-left-10 custom-margin-right-10">|</span> <b>Room:</b> ' + room_number + '<span class="custom-margin-left-10 custom-margin-right-10">|</span><b>Price: <span class="priceSpan">' + (count_price_calc * array_price ).toFixed(2) + "</span>";
                html+= "</div>"
            }
            $(".displayRoomReservationResult").append(html);
        }
        
        $(document).on('click',".btn_remove_room_extra",function(){
            var choosedRow = $(this);
            if (confirm('Are you sure?')) {
                var type = $(this).attr('data-type');
                var id = $(this).attr('data-id');
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        remove_room_extra: true,
                        type:type,
                        id:id,
                    },
                    success: function(resp){
                        $(choosedRow).parent().remove();
                    }
                })
            }
        })
        function getSubRegion(itemId,variable) {
            if (itemId) {
                $.get("<?=$rootF;?>/data.php?cmd=getSubRegion&itemId=" + itemId, function (data) {
                    $("#guest_city" + variable).removeAttr("disabled");
                    $("#guest_city" + variable).html(data);
                });
            } else {
                $("#guest_city_up").attr("disabled", "true");
                $("#guest_city_up").html("<option value=\"\">----</option>");
            }

        }
        function addRoomExtraReservationResult(array){
            var html = '';
            for(var i = 0;i< array.length;i++){
                var extra_type = $('#extra_type_id_up option[value="' + array[i]['extra_type_id'] + '"]').html();
                var extra_count = $('#extra_count_up option[value="' + array[i]['extra_count'] + '"]').html();
                html+= "<div title='Extra' style='border:2px solid blue;padding:10px' class='col-md-12 border border-primary roomReservResultSm text-left'>"
                    html+= "<span class='text-danger btn_remove_room_extra' data-type='extra' data-id='" + array[i]['id'] + "' style='font-weight:bolder;float:right'>X</span>"
                    html+= '<b>EXTRAS</b> - ' + extra_type + ' <span class="custom-margin-left-10 custom-margin-right-10">|</span> ';
                    html+= '<b>Count</b> - ' + extra_count + ' <span class="custom-margin-left-10 custom-margin-right-10">|</span> <b> Price: <span class="priceSpan">' + array[i]['extra_price'].toFixed(2) + '</span><br>';
                html+= "</div>"
            }
            $(".displayRoomReservationResult").append(html);
        }
        $(document).on('keyup','.benefitCalculate',function(){
            var variable = $(this).attr('data-variable');
            totalBenefitPriceCalculate(variable);
        })
        function totalBenefitPriceCalculate(variable){
            var price = $('#total_price' + variable).val();
            var net_price = $('#net_price' + variable).val();
            var diff = price - net_price;
            var res = (diff * 100)/price;
            $("#total_benefit"+variable).val(diff.toFixed(2) + " " + $('#total_price_currency_id'+variable).find(":selected").html() + "  " + res.toFixed(2) + " %")
        }
        function differenceBetweenTwoDates(date1,date2){
            date1 = new Date(date1);
            date2 = new Date(date2);
            var milli_secs = date1.getTime() - date2.getTime();
            var days = milli_secs / (1000 * 3600 * 24);
            return Math.round(Math.abs(days));
        }
        function addActionReservationResult(array){
            var html = '';
            for(var i = 0;i< array.length;i++){
                var statusText = 'What Hapened?'
                var colorText = 'black';
                var check_out_date = array[i]['data_inserted'].split(' ');
                var done_date = array[i]['done_datetime'].split(' ');
                if(array[i]['status'] == 0){
                    colorText = 'red';
                    statusText = 'Required to do';
                }
                html+= '<tr>';
                    html+= '<td>';
                        html+= "<b>" + statusText + "</b>: " + $('#action_id_up option[value="' + array[i]['action_id'] + '"]').html();
                    html+= '</td>';
                    html+= '<td>';
                        html+= array[i]['userInfo']['full_name_en'];
                    html+= '</td>';
                    html+= '<td class="font-12" style="width:127px">';
                        html+= replaceDatetimeFormat(check_out_date[0]) + " " + check_out_date[1];
                    html+= '</td>';
                    html+= '<td style="color:' + colorText + '">';
                        html+= array[i]['information'];
                    html+= '</td>';
                    html+= '<td>';
                        if(array[i]['status'] == 0){
                            if(array[i]['done'] == 1){
                                html+= "<b class='text-primary'>Done!</b>";
                            }
                            else{
                                html+="<input data-row-id='" + array[i]['id'] + "' class='doneCheckbox' type='checkbox'> Done!"
                            }
                        }
                    html+= '</td>';
                    html+= '<td>';
                        if(array[i]['done_user_id'] > 0){
                            html+= usernames[array[i]['done_user_id']]['full_name_en'];
                        }
                    html+= '</td>';
                    html+= '<td class="font-12" style="width:127px">';
                        if(array[i]['done_datetime'] != '0000-00-00 00:00:00'){
                            html+= replaceDatetimeFormat(done_date[0]) + " " + done_date[1];
                        }
                    html+= '</td>';
                html+= '</tr>';
            }
            $(".displayActionsPart").append(html);
        }
        $(document).on('click',".addActionToRow",function(){
            var array = [];
            array['status_action'] = $("#status_action_up").val();
            array['action_id'] = $("#action_id_up").val();
            array['action_information'] = $("#action_information_up").val();
            $(".actionTableDisplay").removeClass('d-none');
            var result = Array(array);
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    insert_action_reservation: true,
                    status_action:array['status_action'],
                    action_id:array['action_id'],
                    action_information:array['action_information'],
                    row_id:selectedRowId,
                },
                success: function(resp){
                    addActionReservationResult(JSON.parse(resp));
                    $("#action_information_up").val('');
                    $("#action_id_up").prop("selectedIndex", 0);
                    $("#action_information_up").remove();
                    $("#action_id_up").addClass('d-none');
                    $("#status_action_up").prop("selectedIndex", 0);
                }
            })
        })
        $(document).on('click',".addRoomExtraReservation",function(){
            var array = [];
            array['extra_type_id'] = $("#extra_type_id_up").val();
            array['extra_count'] = $("#extra_count_up").val();
            array['extra_price'] = $("#extra_price_up").val();
            var result = Array(array);
            addRoomExtraReservationResult(result);
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    insert_room_extra_reservation: true,
                    extra_type_id:array['extra_type_id'],
                    extra_count:array['extra_count'],
                    extra_price:array['extra_price'],
                    row_id:selectedRowId,
                },
                success: function(resp){
                    console.log(resp);
                }
            })
        })
        $(document).on('click',".addRoomReservation",function(){
            var array = [];
            array['room_number_id'] = $("#room_number_id_up").val();
            array['room_type_id'] = $("#room_type_id_up").val();
            array['check_in_sm'] = $("#check_in_sm_up").val();
            console.log($("#check_in_sm_up").val(),8888);
            array['check_out_sm'] = $("#check_out_sm_up").val();
            array['price'] = $("#price_small_up").val();
            var result = Array(array);
            addRoomReservationResult(result);
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    insert_room_reservation: true,
                    room_number_id:array['room_number_id'],
                    room_type_id:array['room_type_id'],
                    check_in_sm:array['check_in_sm'],
                    check_out_sm:array['check_out_sm'],
                    price:array['price'],
                    row_id:selectedRowId,
                },
                success: function(resp){
                    console.log(resp);
                }
            })
        })
        $(document).on("change",".doneCheckbox",function(){
            $(this).attr('disabled',true);
            var row_id = $(this).attr('data-row-id');
            $.ajax({
                type : "POST",
                data: {
                    update_done_action: true,
                    row_id:row_id,
                },
                success:function(res){
                    console.log(res)
                }
            })
        })
        $(document).on('click',".addReservationBtn",function(){
            var partner_id = $("#partner_id").val();
            var main_check_in = $("#main_check_in").val();
            var main_check_out = $("#main_check_out").val();
            var reservation_status = $("#reservation_status").val();
            var reservation_number = $("#reservation_number").val();
            var important_notes = $("#important_notes").val();
            var sales_point_id = $("#sales_point_id").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var whatsap_viber = $("#whatsap_viber").val();
            var phone_2 = $("#phone_2").val();
            var email = $("#email").val();
            var total_price = $("#total_price").val();
            var total_price_currency_id = $("#total_price_currency_id").val();
            var net_price = $("#net_price").val();
            var net_price_currency_id = $("#net_price_currency_id").val();
            var paid_amount = $("#paid_amount").val();
            var paid_amount_currency_id = $("#paid_amount_currency_id").val();
            var more_details = $("#more_details").val();
            var payment_type_id = $("#payment_type_id").val();
            var payment_status_id = $("#payment_status_id").val();
            if(partner_id < 1){
                alert('Hotel is Required');
                return false;
            }
            if(sales_point_id < 1){
                alert('Sale Point is Required');
                return false;
            }
            if(reservation_status < 1){
                alert('Reservation is Required');
                return false;
            }
            if(main_check_in > main_check_out){
                alert("Please check C:in and C:out dates!")
                return false;
            }

            var status_action = $("#status_action").val();
            var action_id = $("#action_id").val();
            var action_information = $("#action_information").val();

            var room_number_id = $("#room_number_id").val();
            var room_type_id = $("#room_type_id").val();
            var check_in_sm = $("#check_in_sm").val();
            var check_out_sm = $("#check_out_sm").val();
            var price_small = $("#price_small").val();

            var extra_type_id = $("#extra_type_id").val();
            var extra_count = $("#extra_count").val();
            var extra_price = $("#extra_price").val();

            var guest_country = $("#guest_country").val();
            var guest_city = $("#guest_city").val();
            var guest_exact_city = $("#guest_exact_city").val();
            var guest_address = $("#guest_address").val();
            var guest_nationality = $("#guest_nationality").val();
            var guest_language = $("#guest_language").val();
            var guest_birthday = $("#guest_birthday").val();
            var guest_passport = $("#guest_passport").val();
            var knows_armenian = 0;
            if( $('#guest_know_armenian').is(':checked') ){
                knows_armenian = 1;
            }
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    insert_reservation: true,
                    partner_id: partner_id,
                    main_check_in: main_check_in,
                    main_check_out: main_check_out,
                    reservation_status: reservation_status,
                    reservation_number: reservation_number,
                    important_notes: important_notes,
                    sales_point_id: sales_point_id,
                    first_name: first_name,
                    last_name: last_name,
                    whatsap_viber: whatsap_viber,
                    phone_2: phone_2,
                    email: email,
                    total_price: total_price,
                    total_price_currency_id: total_price_currency_id,
                    net_price: net_price,
                    net_price_currency_id: net_price_currency_id,
                    paid_amount: paid_amount,
                    paid_amount_currency_id: paid_amount_currency_id,
                    more_details: more_details,
                    payment_status_id: payment_status_id,
                    payment_type_id: payment_type_id,
                    status_action: status_action,
                    action_id: action_id,
                    action_information: action_information,
                    room_number_id: room_number_id,
                    room_type_id: room_type_id,
                    check_in_sm: check_in_sm,
                    check_out_sm: check_out_sm,
                    price_small: price_small,
                    extra_type_id: extra_type_id,
                    extra_count: extra_count,
                    extra_price: extra_price,
                    guest_country: guest_country,
                    guest_exact_city: guest_exact_city,
                    guest_city: guest_city,
                    guest_address: guest_address,
                    guest_nationality: guest_nationality,
                    guest_language: guest_language,
                    guest_birthday: guest_birthday,
                    guest_passport: guest_passport,
                    knows_armenian: knows_armenian,
                },
                success: function(resp){
                    location.reload(true);
                }
            })
        })
        $(document).on('click',".updateReservationBtn",function(){
            if (confirm('Are you sure?')) {
                var partner_id = $("#partner_id_up").val();
                var main_check_in = $("#main_check_in_up").val();
                var main_check_out = $("#main_check_out_up").val();
                var reservation_status = $("#reservation_status_up").val();
                var reservation_number = $("#reservation_number_up").val();
                var important_notes = $("#important_notes_up").val();
                var sales_point_id = $("#sales_point_id_up").val();
                var first_name = $("#first_name_up").val();
                var last_name = $("#last_name_up").val();
                var whatsap_viber = $("#whatsap_viber_up").val();
                var phone_2 = $("#phone_2_up").val();
                var email = $("#email_up").val();

                var total_price = $("#total_price_up").val();
                var total_price_currency_id = $("#total_price_currency_id_up").val();
                var net_price = $("#net_price_up").val();
                var net_price_currency_id = $("#net_price_currency_id_up").val();
                var paid_amount = $("#paid_amount_up").val();
                var paid_amount_currency_id = $("#paid_amount_currency_id_up").val();
                var more_details = $("#more_details_up").val();
                var payment_type_id = $("#payment_type_id_up").val();
                var payment_status_id = $("#payment_status_id_up").val();
                var guest_country = $("#guest_country_up").val();
                var guest_city = $("#guest_city_up").val();
                var guest_exact_city = $("#guest_exact_city_up").val();
                var guest_address = $("#guest_address_up").val();
                var guest_nationality = $("#guest_nationality_up").val();
                var guest_language = $("#guest_language_up").val();
                var guest_birthday = $("#guest_birthday_up").val();
                var guest_passport = $("#guest_passport_up").val();
                var knows_armenian = 0;
                if(partner_id < 1){
                    alert('Hotel is Required');
                    return false;
                }
                if(sales_point_id < 1){
                    alert('Sale Point is Required');
                    return false;
                }
                if(reservation_status < 1){
                    alert('Reservation is Required');
                    return false;
                }
                if(main_check_in > main_check_out){
                    alert("Please check C:in and C:out dates!")
                    return false;
                }
                if( $('#guest_know_armenian_up').is(':checked') ){
                    knows_armenian = 1;
                }
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        update_reservation: true,
                        row_id: selectedRowId,
                        partner_id: partner_id,
                        main_check_in: main_check_in,
                        main_check_out: main_check_out,
                        reservation_status: reservation_status,
                        reservation_number: reservation_number,
                        important_notes: important_notes,
                        sales_point_id: sales_point_id,
                        last_name: last_name,
                        first_name: first_name,
                        whatsap_viber: whatsap_viber,
                        whatsap_viber_up: whatsap_viber,
                        phone_2: phone_2,
                        email: email,
                        total_price: total_price,
                        total_price_currency_id: total_price_currency_id,
                        net_price: net_price,
                        net_price_currency_id: net_price_currency_id,
                        paid_amount: paid_amount,
                        paid_amount_currency_id: paid_amount_currency_id,
                        more_details: more_details,
                        payment_status_id: payment_status_id,
                        payment_type_id: payment_type_id,
                        guest_country: guest_country,
                        guest_exact_city: guest_exact_city,
                        guest_city: guest_city,
                        guest_address: guest_address,
                        guest_nationality: guest_nationality,
                        guest_language: guest_language,
                        guest_birthday: guest_birthday,
                        guest_passport: guest_passport,
                        knows_armenian: knows_armenian,
                    },
                    success: function(resp){
                        // location.reload(true);
                    }
                })
            }
        })
        $(document).on('click',".update-row",function(){
            selectedRowId = $(this).attr('data-row-id');
            $(".displayRoomReservationResult").html('');
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    row_information: true,
                    row_id: selectedRowId,
                },
                success: function(resp){
                    if(resp.length > 0){
                        resp = JSON.parse(resp);
                        var resp_main = resp.main[0];
                        var resp_room = resp.room;
                        var resp_room_extra = resp.roomextra;
                        var resp_guest_info = resp.guestinfo;
                        var resp_actions = resp.actions;
                        $(".rowIdUpdate").html(resp_main['id']);
                        $("#main_check_in_up").val(resp_main['main_check_in']);
                        $("#main_check_out_up").val(resp_main['main_check_out']);
                        $('#partner_id_up option[value="' + resp_main['partner_id'] + '"]').prop('selected', true);
                        $('#reservation_status_up option[value="' + resp_main['reservation_status'] + '"]').prop('selected', true);
                        $("#reservation_number_up").val(resp_main['reservation_number']);
                        $('#sales_point_id_up option[value="' + resp_main['sales_point_id'] + '"]').prop('selected', true);
                        $("#important_notes_up").val(resp_main['important_notes']);
                        $("#first_name_up").val(resp_main['first_name']);
                        $("#last_name_up").val(resp_main['last_name']);
                        $("#whatsap_viber_up").val(resp_main['whatsap_viber']);
                        $("#phone_2_up").val(resp_main['phone_2']);
                        $("#email_up").val(resp_main['email']);
                        $("#total_price_up").val(resp_main['total_price']);
                        $("#net_price_up").val(resp_main['net_price']);
                        $('#total_price_currency_id_up option[value="' + resp_main['total_price_currency_id'] + '"]').prop('selected', true);
                        $('#net_price_currency_id_up option[value="' + resp_main['net_price_currency_id'] + '"]').prop('selected', true);
                        $("#paid_amount_up").val(resp_main['paid_amount']);
                        $('#paid_amount_currency_id_up option[value="' + resp_main['paid_amount_currency_id'] + '"]').prop('selected', true);
                        $("#more_details_up").val(resp_main['more_details']);
                        $('#payment_type_id_up option[value="' + resp_main['payment_type_id'] + '"]').prop('selected', true);
                        $('#payment_status_id_up option[value="' + resp_main['payment_status_id'] + '"]').prop('selected', true);

                        if(resp_room.length > 0){
                            addRoomReservationResult(resp_room);
                        }
                        if(resp_room_extra.length > 0){
                            addRoomExtraReservationResult(resp_room_extra);
                        }
                        if(resp_actions.length > 0){
                            $(".actionTableDisplay").removeClass('d-none');
                            addActionReservationResult(resp_actions);
                        }
                        $('#guest_country_up option[value="' + resp_guest_info['country_id'] + '"]').prop('selected', true);
                        $("#guest_exact_city_up").val(resp_guest_info['exact_city']);
                        getSubRegion($('#guest_country_up').val(),'_up')
                        setTimeout(function(){
                            if(resp_guest_info['city_id'] > 0){
                                $('#guest_city_up option[value="' + resp_guest_info['city_id'] + '"]').prop('selected', true);
                                $("#guest_city_up").removeAttr('disabled');
                            }

                        },500)
                        $("#guest_address_up").val(resp_guest_info['address']);
                        $('#guest_nationality_up option[value="' + resp_guest_info['nationality_id'] + '"]').prop('selected', true);
                        $('#guest_language_up option[value="' + resp_guest_info['language_id'] + '"]').prop('selected', true);
                        $("#guest_birthday_up").val( resp_guest_info['birthday']);
                        $("#guest_passport_up").val(resp_guest_info['passport']);
                        if(resp_guest_info['knows_armenian'] == 1){
                            $("#guest_know_armenian_up").attr('checked',true);
                        }
                        totalBenefitPriceCalculate('_up');
                    }
                }
            })
            $('.update-modal').modal('show');
            calculateExchangeTotal();
        })
        function calculateExchangeTotal(){
            setTimeout(function(){
                var totalRes = 0;
                var priceSpan = $(".priceSpan");
                for(var i = 0;i < priceSpan.length;i++){
                    totalRes = totalRes + parseInt($(priceSpan[i]).html());
                }
                var result_html = "<b>$ " + (totalRes / usd).toFixed(2) + " | € " + (totalRes / eur).toFixed(2) + " | R " + (totalRes / rub).toFixed(2) + " | G " + (totalRes / gbp).toFixed(2) + "</b>";
                $(".currencyTotalResult").html(result_html)
            },1000)
        }
        var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
        function replaceDatetimeFormat(datetime){
            if(datetime != null){
                var arr = datetime.split(" ");
                var mycDate = arr[0].split("-");
                return mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0];
            }
            return datetime;
        }
        $(document).on('change','#guest_country_up',function(){
            var val = $(this).val();
            getSubRegion(val,'_up');
        })
        $(document).on('change','#guest_country',function(){
            var val = $(this).val();
            getSubRegion(val,'');
        })
        $(document).on('change','#action_id',function(){
            $(".actionMain").html('');
            $(".actionMain").append('<textarea id="action_information" rows="1" style="width:100%;height:100%" placeholder="Note"></textarea>');
        })
        $(document).on('change','#action_id_up',function(){
            $(".actionMain_up").html('');
            $(".actionMain_up").append('<textarea id="action_information_up" rows="1" style="width:80%;height:100%" placeholder="Note"></textarea><span class="btn btn-success addActionToRow" style="float:right">Add</span>');
        })
        $(document).on('change','#status_action_up',function(){
            $("#action_id_up").removeClass('d-none')
        })
        $(document).on('change','#status_action',function(){
            $("#action_id").removeClass('d-none')
        })
    })
</script>
</body>
</html>