<?php 

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    $pageName = "accountant";
    $rootF = "../..";

    include($rootF . "/apay/pay.api.php");
    include($rootF . "/configuration.php");
    include("../flower_orders/lang/language_am.php");
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);


    $levelArray = explode(",", $level[0]["user_level"]);
    //ddd(auth::roleExist(16));
    //dde($levelArray);
    $operators_to_show = getwayConnect::getwayData("SELECT * FROM user where user_level like '%36%' order by user_active desc");
    $userData = auth::checkUserExistById($uid);
    $total_result = [];
    $total_result_pending_info = [];
    if(!empty($_GET['from_date']) || !empty($_GET['to_date']) || !empty($_GET['user_id'])){
        $from_date = $_GET['from_date'];
        $to_date = $_GET['to_date'];
        $user_id = $_GET['user_id'];
        $type = $_GET['type'];
        if($type == 'pending_info'){
            $sql = "SELECT * FROM `pending_info` ";
            if(!empty($from_date)){
                $sql .= " where created_date >= '" . $from_date . "'"; 
                if(!empty($to_date)){
                    $to_date_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                    $sql.= " and created_date <= '" . $to_date_sql . "'";
                    if($user_id != "all"){
                        $sql.= " and operator_id = '" . $user_id . "'";
                    }if($user_id != "all"){
                        $sql.= " and operator_id = '" . $user_id . "'";
                    }
                }
                else{
                    if($user_id != "all"){
                        $sql.= " and operator_id = '" . $user_id . "'";
                    }
                }
            }
            else if(!empty($to_date)){
                $to_date_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                $sql.=" where created_date <= '" . $to_date_sql . "'";
                if($user_id != "all"){
                    $sql.= " and operator_id = '" . $user_id . "'";
                }
            }
            else if($user_id != "all"){
                $sql.= " where operator_id = '" . $user_id . "'";
            }
            $sql.=' Group by order_id';
            $total_result_pending_info = getwayConnect::getwayData($sql);
        }
        else{
            $sql_45_50 = "SELECT * FROM `log_45_50` ";
            $sql_50_55 = "SELECT * FROM `log_50_55` ";
            $sql_55_60 = "SELECT * FROM `log_55_60` ";
            $sql_60_65 = "SELECT * FROM `log_60_65` ";
            if(!empty($from_date)){
                $sql_45_50 .= " where date >= '" . $from_date . "'"; 
                $sql_50_55 .= " where date >= '" . $from_date . "'"; 
                $sql_55_60 .= " where date >= '" . $from_date . "'"; 
                $sql_60_65 .= " where date >= '" . $from_date . "'"; 
                if(!empty($to_date)){
                    $to_date_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                    $sql_45_50.= " and date <= '" . $to_date_sql . "'";
                    $sql_50_55.= " and date <= '" . $to_date_sql . "'";
                    $sql_55_60.= " and date <= '" . $to_date_sql . "'";
                    $sql_60_65.= " and date <= '" . $to_date_sql . "'";
                    if($user_id != "all"){
                        $sql_45_50.= " and operator_id = '" . $user_id . "'";
                        $sql_50_55.= " and operator_id = '" . $user_id . "'";
                        $sql_55_60.= " and operator_id = '" . $user_id . "'";
                        $sql_60_65.= " and operator_id = '" . $user_id . "'";
                    }
                }
                else{
                    if($user_id != "all"){
                        $sql_45_50.= " and operator_id = '" . $user_id . "'";
                        $sql_50_55.= " and operator_id = '" . $user_id . "'";
                        $sql_55_60.= " and operator_id = '" . $user_id . "'";
                        $sql_60_65.= " and operator_id = '" . $user_id . "'";
                    }
                }
            }
            else if(!empty($to_date)){
                $to_date_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                $sql_45_50.=" where date <= '" . $to_date_sql . "'";
                $sql_50_55.=" where date <= '" . $to_date_sql . "'";
                $sql_55_60.=" where date <= '" . $to_date_sql . "'";
                $sql_60_65.=" where date <= '" . $to_date_sql . "'";
                if($user_id != "all"){
                    $sql_45_50.= " and operator_id = '" . $user_id . "'";
                    $sql_50_55.= " and operator_id = '" . $user_id . "'";
                    $sql_55_60.= " and operator_id = '" . $user_id . "'";
                    $sql_60_65.= " and operator_id = '" . $user_id . "'";
                }
            }
            else if($user_id != "all"){
                $sql_45_50.= " where operator_id = '" . $user_id . "'";
                $sql_50_55.= " where operator_id = '" . $user_id . "'";
                $sql_55_60.= " where operator_id = '" . $user_id . "'";
                $sql_60_65.= " where operator_id = '" . $user_id . "'";
            }
            if($type == 'opened'){
                $sql_45_50.= ' and opened = 1';
                $sql_50_55.= ' and opened = 1';
                $sql_55_60.= ' and opened = 1';
                $sql_60_65.= ' and opened = 1';
            }
            $sql_45_50.= ' group by order_id';
            $sql_50_55.= ' group by order_id';
            $sql_55_60.= ' group by order_id';
            $sql_60_65.= ' group by order_id';

            $total_result_45_50 = getwayConnect::getwayData($sql_45_50);
            $total_result_50_55 = getwayConnect::getwayData($sql_50_55);
            $total_result_55_60 = getwayConnect::getwayData($sql_55_60);
            $total_result_60_65 = getwayConnect::getwayData($sql_60_65);
            if(count($total_result_45_50) > 0){
                foreach($total_result_45_50 as $key=>$value){
                    $total_result[] = $value;
                }
            }
            if(count($total_result_50_55) > 0){
                foreach($total_result_50_55 as $key=>$value){
                    $total_result[] = $value;
                }
            }
            if(count($total_result_55_60) > 0){
                foreach($total_result_55_60 as $key=>$value){
                    $total_result[] = $value;
                }
            }
            if(count($total_result_60_65) > 0){
                foreach($total_result_60_65 as $key=>$value){
                    $total_result[] = $value;
                }
            }
        }
    }
    if(isset($_REQUEST['getorderlog']) && $_REQUEST['getorderlog']){
        $order_id = $_REQUEST['order_id'];
        $check_table_count = substr($_REQUEST['order_id'], 0, 2);
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
        $result = [];
        $order_log = getwayConnect::getwayData("SELECT * FROM log_" . $table_count . " LEFT JOIN delivery_status ON log_" . $table_count . ".current_status_id = delivery_status.id left join user on log_" . $table_count . ".operator_id = user.id where order_id='{$order_id}'");
        $result['order_log'] = $order_log;
        print json_encode($result);die;
    }
    if(isset($_REQUEST['getorderlogByUser']) && $_REQUEST['getorderlogByUser']){
        $order_id = $_REQUEST['order_id'];
        $user_id = $_REQUEST['user_id'];
        $check_table_count = substr($_REQUEST['order_id'], 0, 2);
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
        $result = [];
        $order_log = getwayConnect::getwayData("SELECT * FROM log_" . $table_count . " LEFT JOIN delivery_status ON log_" . $table_count . ".current_status_id = delivery_status.id left join user on log_" . $table_count . ".operator_id = user.id where order_id='{$order_id}' and operator_id='" . $user_id . "'");
        $result['order_log'] = $order_log;
        print json_encode($result);die;
    }
    function getDateFormate($date){
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $exploded_date = explode(' ',$date);
        $mycDate = explode('-',$exploded_date[0]);
        $newcDate = $mycDate[2] . "-" . $monthNames[$mycDate[1] - 1] . "-" . $mycDate[0];
        return $newcDate . " " . $exploded_date[1];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">

    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    <link rel="stylesheet" href="<?= $rootF ?>/template/DataTables/datatables.css"/>


    <title>Log History</title>
</head>
<style>
    .show_log_of_order:hover{
        cursor:pointer;
    }
    .show_log_of_order_by_user:hover{
        cursor:pointer;
    }
    .show_log_of_order_by_user{
        text-decoration: underline;
    }
</style>
<body>
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
            <a class="navbar-brand" href="#">RG-SYSTEM / <?= strtoupper($userData[0]["username"]); ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <li><a href="../flower_orders?cmd=control">Control</a></li>
                <li><a href="../flower_orders?cmd=exit">Ելք</a></li>
                <li><a href="../flower_orders?cmd=flower_orders">Պատվերներ</a></li>
                <li><a href="../flower_orders?cmd=orders_delivery">Առաքում</a></li>
                <li><a href="../flower_orders?cmd=travel_orders">Տուրիստական</a></li>
                <li><a href="/account/accountant">ACCOUNTING</a></li>
                <li><a href="/print.php">PRINT</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='col-md-12'>
    <form>
        <div class='col-md-3' style='margin-top:60px'>
            <label style='float:left'>Created Date</label>
            <input type='date' style='width:70%;float:right' value="<?=(!empty($from_date)? $from_date : date('Y-m-d', strtotime('-3 day', strtotime(date('Y-m-d')))))?>" name='from_date' class='from_date mt-1 form-control'>
        </div>
        <div class='col-md-2' style='margin-top:60px'>
            <input type='date' value="<?=(!empty($to_date)? $to_date : '')?>" name='to_date' class='to_date mb-1 form-control'>
        </div>
        <div class='col-md-2' style='margin-top:60px'>
            <select class='form-control operator_filter' name='user_id'>
                <option value='all'>All</option>
                <?php
                    foreach( $operators_to_show as $key => $value ) {
                        ?>
                            <option <?=(isset($_GET['user_id']) && $_GET['user_id'] == $value['id'])? "selected" : ""?> value="<?=$value['id']?>"><?= ($value['full_name_am'] != '')? $value['full_name_am'] : $value['username']?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <div class='col-md-2' style='margin-top:60px'>
            <select class='form-control' name='type'>
                <option <?=(isset($_GET['type']) && $_GET['type'] == 'edited')? "selected" : ""?> value="edited">Խմբագրածները</option>
                <option <?=(isset($_GET['type']) && $_GET['type'] == 'opened')? "selected" : ""?> value="opened">Բացածները</option>
                <option <?=(isset($_GET['type']) && $_GET['type'] == 'pending_info')? "selected" : ""?> value="pending_info">Անավարտ եղածները</option>
            </select>
        </div>
        <div class='col-md-2' style='margin-top:60px'>
            <button type='submit' class='btn btn-danger'>Filter</button>
        </div>
    </form>
</div>
<div style='margin-top:100px' class="col-xs-12 par_data_table margin-top">
    <?php
    if(!empty($_GET['from_date']) || !empty($_GET['to_date']) || !empty($_GET['user_id'])){
        if($type != 'pending_info'){            
        ?>
            <table id="data_table" class="table table-bordered table-hover">

                <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th>
                        Date
                    </th>
                    <th>
                        Order Id
                    </th>
                    <th>
                        Status
                    </th>
                    <th>
                        User
                    </th>
                    <th>
                        Notes
                    </th>
                </tr>
                </thead>
                <tbody>
                    <?php 
                        foreach( $total_result as $key => $value ){
                            $OperatorInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = '".$value['operator_id']."'");
                            $currentStatusInfo = getwayConnect::getwayData("SELECT * FROM delivery_status WHERE id = '".$value['current_status_id']."'");
                            $orderInfo = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '".$value['order_id']."'");
                            $user_sql = '';
                            $from_date_sql = '';
                            $to_date_sql = '';
                            if($user_id != 'all'){
                                $user_sql.= ' and operator_id = "' . $user_id . '"';
                            }
                            if(!empty($from_date)){
                                $from_date_sql.= " and created_date >= '" . $from_date . "'";
                            }
                            if(!empty($to_date)){
                                $to_date_new_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                                $to_date_sql.= " and created_date <= '" . $to_date_new_sql . "'";
                            }
                            $pending_info = getwayConnect::getwayData("SELECT * FROM `pending_info` LEFT JOIN user on pending_info.operator_id = user.id WHERE `order_id` = '".$value['order_id']."' " . $user_sql . $from_date_sql . $to_date_sql . " ");
                            ?>
                                <tr>
                                    <td>
                                        <?=$key+1?>
                                    </td>
                                    <td>
                                        <?php
                                            $date = getDateFormate($value['date']);
                                            echo $date;
                                        ?>
                                    </td>
                                    <td>
                                        <p class='show_log_of_order_by_user' data-order-user-id="<?php echo $OperatorInfo[0]['id']?>"  data-order-id="<?php echo $value['order_id'] ?>">
                                            <?=$value['order_id']?>
                                        </p>
                                    </td>
                                    <td>
                                        <p style='display: none'><?php echo $orderInfo[0]['delivery_status'] ?></p>
                                        <img class='statusImage show_log_of_order' data-order-id="<?php echo $value['order_id'] ?>" src="../../template/icons/status/<?=$orderInfo[0]['delivery_status']?>.png">
                                    </td>
                                    <td>
                                        <?= ($OperatorInfo[0]['full_name_am'] != '')? $OperatorInfo[0]['full_name_am'] : $OperatorInfo[0]['username']?>

                                    </td>
                                    <td>
                                        <?php
                                            if(count($pending_info) > 0){
                                                foreach( $pending_info as $key => $value ){
                                                    ?>
                                                        <p><?= $key + 1 ?>)<span class='color_red'> <?=$value['created_date']?> ՝ </span> <?=$value['full_name_am']?> ՝ <?=$value['description']?></p>
                                                    <?php
                                                }
                                            }
                                        ?>
                                        <hr>
                                        <?= $orderInfo[0]['notes'] ?>
                                    </td>
                                </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        <?php
        }
        else{
            ?>
                <table id="data_table" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            #
                        </th>

                        <th>
                            Date
                        </th>
                        <th>
                            Order Id
                        </th>
                        <th>
                            Status
                        </th>
                        <th>
                            Notes
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach( $total_result_pending_info as $key => $value ){
                                $OperatorInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = '".$value['operator_id']."'");
                                $orderInfo = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '".$value['order_id']."'");
                                $user_sql = '';
                                $from_date_sql = '';
                                $to_date_sql = '';
                                if($user_id != 'all'){
                                    $user_sql.= ' and operator_id = "' . $user_id . '"';
                                }
                                if(!empty($from_date)){
                                    $from_date_sql.= " and created_date >= '" . $from_date . "'";
                                }
                                if(!empty($to_date)){
                                    $to_date_new_sql =  date('Y-m-d', strtotime('+1 day', strtotime(date($to_date))));
                                    $to_date_sql.= " and created_date <= '" . $to_date_new_sql . "'";
                                }
                                $pending_info = getwayConnect::getwayData("SELECT * FROM `pending_info` LEFT JOIN user on pending_info.operator_id = user.id WHERE `order_id` = '".$value['order_id']."' " . $user_sql . $from_date_sql . $to_date_sql . " ");
                                ?>
                                    <tr>
                                        <td>
                                            <?=$value['id']?>
                                        </td>
                                        <td>
                                            <?php
                                                $date = getDateFormate($value['created_date']);
                                                echo $date;
                                            ?>
                                        </td>
                                        <td>
                                            <a target='_blank' href="../flower_orders/order.php?orderId=<?=$value['order_id']?>">
                                                <?=$value['order_id']?>
                                            </a>
                                        </td>
                                        <td>
                                            <p style='display: none'><?php echo $orderInfo[0]['delivery_status'] ?></p>
                                            <img class='statusImage show_log_of_order' data-order-id="<?php echo $value['order_id'] ?>" src="../../template/icons/status/<?=$orderInfo[0]['delivery_status']?>.png">
                                        </td>
                                        <td>
                                            <?php
                                                if(count($pending_info) > 0){
                                                    foreach( $pending_info as $key => $value ){
                                                        ?>
                                                            <p><?= $key + 1 ?>)<span class='color_red'> <?=$value['created_date']?> ՝ </span> <?=$value['full_name_am']?> ՝ <?=$value['description']?></p>
                                                        <?php
                                                    }
                                                }
                                            ?>
                                            <hr>
                                            <?= $orderInfo[0]['notes'] ?>
                                        </td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            <?php
        }
    }
    ?>
</div>
<div class="modal fade" id="change_log" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="log_data">Change Log <span class='for_order_number'></span></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Log</td>
                        <td>Date</td>
                        <td>Current Status</td>
                        <td>User Name</td>
                    </tr>
                    </thead>
                    <tbody class="log_table_body">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>

<script src="<?= $rootF ?>/template/DataTables/datatables.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/js/validator.js"></script>
<script>
    $(document).ready(function () {

        $('#data_table').DataTable({
            "order": [[ 0, "desc" ]]
        });
        $(document).on('click','.show_log_of_order',function(){
            $('#change_log').modal('show');
            $(".log_table_body").empty();
            var order_id = $(this).data('order-id');
            $(".for_order_number").html(order_id);
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getorderlog: true,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp.length > 5){
                        resp = JSON.parse(resp);
                        for(var i = 0 ; i < resp.order_log.length ; i++ ){
                            var html="<tr>";
                                    html+="<td>";
                                        html+= i+1
                                    html+="</td>";
                                    html+="<td>";
                                        html+=resp.order_log[i].description
                                    html+="</td>";
                                    html+="<td>";
                                        html+=resp.order_log[i].date
                                    html+="</td>";
                                    html+="<td>";
                                        if(resp.order_log[i].name_am == 'Վճարումը դեռ չկատարված'){
                                            html+='Անավարտ';
                                        }
                                        else{
                                            html+=resp.order_log[i].name_am
                                        }
                                    html+="</td>";
                                    html+="<td>";
                                        if(resp.order_log[i]['full_name_am'] != ''){
                                            html+=resp.order_log[i].full_name_am
                                        }
                                        else{
                                            html+=resp.order_log[i].username
                                        }
                                    html+="</td>";
                                html+="</tr>";
                            $(".log_table_body").append(html);
                        }
                    }
                }
            })
        })
        $(document).on('click','.show_log_of_order_by_user',function(){
            $('#change_log').modal('show');
            $(".log_table_body").empty();
            var order_id = $(this).data('order-id');
            var user_id = $(this).data('order-user-id');
            $(".for_order_number").html(order_id);
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getorderlogByUser: true,
                    user_id: user_id,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp.length > 5){
                        resp = JSON.parse(resp);
                        for(var i = 0 ; i < resp.order_log.length ; i++ ){
                            var html="<tr>";
                                    html+="<td>";
                                        html+= i+1
                                    html+="</td>";
                                    html+="<td>";
                                        html+=resp.order_log[i].description
                                    html+="</td>";
                                    html+="<td>";
                                        html+=resp.order_log[i].date
                                    html+="</td>";
                                    html+="<td>";
                                        if(resp.order_log[i].name_am == 'Վճարումը դեռ չկատարված'){
                                            html+='Անավարտ';
                                        }
                                        else{
                                            html+=resp.order_log[i].name_am
                                        }
                                    html+="</td>";
                                    html+="<td>";
                                        html+=resp.order_log[i].username
                                    html+="</td>";
                                html+="</tr>";
                            $(".log_table_body").append(html);
                        }
                    }
                }
            })
        })
    })
</script>