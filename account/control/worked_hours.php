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

    $userData = auth::checkUserExistById($uid);
    $sql = "SELECT * FROM `worked_hours`";
    if(!empty($_GET['from_date']) || !empty($_GET['to_date']) || !empty($_GET['user_id'])){
        $from_date = $_GET['from_date'];
        $to_date = $_GET['to_date'];
        $user_id = $_GET['user_id'];
        if(!empty($from_date)){
            $sql .= " where start_date >= '" . $from_date . "'"; 
            if(!empty($to_date)){
                $sql.= " and end_date <= '" . $to_date . "'";
                if($user_id != "all"){
                    $sql.= " and user_id = '" . $user_id . "'";
                }if($user_id != "all"){
                    $sql.= " and user_id = '" . $user_id . "'";
                }
            }
            else{
                if($user_id != "all"){
                    $sql.= " and user_id = '" . $user_id . "'";
                }
            }
        }
        else if(!empty($to_date)){
            $sql.=" where end_date <= '" . $to_date . "'";
            if($user_id != "all"){
                $sql.= " and user_id = '" . $user_id . "'";
            }
        }
        else if($user_id != "all"){
            $sql.= " where user_id = '" . $user_id . "'";
        }
    }
    $total_result = getwayConnect::getwayData($sql);
    $users = getwayConnect::getwayData("SELECT * FROM `worked_hours` GROUP BY user_id");
    $usersInfo = [];
    foreach($users as $key=>$value){
        $usersInfo[] = getwayConnect::getwayData("SELECT * FROM `user` where id = '" . $value['user_id'] ."'");
    }
    if(isset($_POST['action'])){
        if($_POST['action'] == "GetAllData"){
            $GetAllData = getwayConnect::getwayData("SELECT * FROM `worked_hours` where id = '" . $_POST['data_id'] . "'");
            print json_encode($GetAllData);die;
        }
        if($_POST['action'] == "UpdateClock"){
            $total_hours = strtotime($_POST['end_date'] . " " . $_POST['end']) - strtotime($_POST['start_date'] . " " . $_POST['start']);
            $GetAllData = getwayConnect::getwayData("UPDATE worked_hours set end_time='" . $_POST['end'] . "',start_time = '" . $_POST['start'] . "',start_date = '" . $_POST['start_date'] . "',end_date = '" . $_POST['end_date'] . "' , total_worked = '" . $total_hours . "' where  id = '" . $_POST['id'] . "'");
            return true;
        }
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
    <link rel="stylesheet" href="index_css.css">


    <title>Hours</title>
</head>
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
<div style='margin-top:100px' class="col-xs-12 par_data_table margin-top">
    <div class="col-md-10" style='margin-bottom:20px'>
        <form>
            <div class='form-group col-md-3'>
                <select name='user_id' class='form-control'  >
                    <option value="all">All</option>
                    <?php
                        foreach( $usersInfo as $key => $value ) {
                            ?>
                                <option <?=(isset($user_id) && $user_id == $value[0]['id'])? "selected" : ""?> value="<?=$value[0]['id']?>"><?= ($value[0]['full_name_am'])? $value[0]['full_name_am'] :$value[0]['username']?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class='form-group col-md-3'>
                <input type='date' value="<?=(!empty($from_date)? $from_date : '')?>" name='from_date' class='from_date mt-1 form-control'>
            </div>
            <div class='form-group col-md-3'>
                <input type='date' value="<?=(!empty($to_date)? $to_date : '')?>" name='to_date' class='to_date mb-1 form-control'>
            </div>
            <button type='submit' class='btn btn-danger btn_for_click_filter'>Filter</button>
        </form>
    </div>
    <div class="col-md-2">
        <form>
            <button type='submit' class='btn btn-danger btn_for_click_filter'>Show All</button>
        </form>
    </div>
    <table id="data_table" class="table table-bordered table-hover">

        <thead>
        <tr>
            <th>
                #
            </th>
            <th>
               Start Date
            </th>
            <th>
                Start Time
            </th>
            <th>
               End Date
            </th>
            <th>
                End Time
            </th>
            <th>
                Total Worked
            </th>
            <th>
                User
            </th>
            <th>
                Edit
            </th>
        </tr>
        </thead>
        <tbody>
            <?php 
                foreach( $total_result as $key => $value ){
                    $OperatorInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = '".$value['user_id']."'");
                    ?>
                        <tr>
                            <td>
                                <?=$value['id']?>
                            </td>
                            <td>
                                <?=$value['start_date']?>
                            </td>
                            <td>
                                <?=$value['start_time']?>
                            </td>
                            <td>
                                <?=$value['end_date']?>
                            </td>
                            <td>
                                <?=($value['end_time'] == '00:00:00')? "": $value['end_time']?>
                            </td>
                            <td>
                                <?= date('H:i', mktime(0,$value['total_worked']/60)); ?>
                            </td>
                            <td>
                                <?=($OperatorInfo[0]['full_name_am'])? $OperatorInfo[0]['full_name_am'] : $OperatorInfo[0]['username']?>
                            </td>
                            <td>
                                <button data-id="<?=$value['id']?>" class='btn_for_edit_time btn btn-danger'>Edit</button>
                            </td>
                        </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
</div>
<!-- Added By Hrach -->
<div class="modal fade" id="EditTimeModal" tabindex="-1" role="dialog" aria-labelledby="log_data">
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
                        <th>
                            Start Date
                        </th>
                        <th>
                            Start Time
                        </th>
                        <th>
                            End Date
                        </th>
                        <th>
                            End Time
                        </th>
                    </tr>
                    </thead>
                    <tbody class="editTime_table_body">
                        <tr>
                            <td>
                                <input type='date' class="input_for_startdateEdit form-control">
                            </td>
                            <td>
                                <input type="time" class="input_for_startEdit form-control">
                            </td>
                            <td>
                                <input type='date' class="input_for_enddateEdit form-control">
                            </td>
                            <td>
                                <input type="time" value="<?=date("H:i:s")?>" class="input_for_endEdit form-control">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn_for_save_edits" data-dismiss="modal">Save</button>
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
        var data_id;
        $(document).on("click",".btn_for_edit_time",function(){
            data_id = $(this).attr('data-id');
            $.ajax({
                type:'post',
                data:{action:'GetAllData',data_id:data_id},
                success:function(res){
                    res = JSON.parse(res);
                    $(".input_for_startdateEdit").val(res[0].start_date);
                    $(".input_for_enddateEdit").val(res[0].end_date);
                    $(".input_for_startEdit").val(res[0].start_time);
                    if(res[0].end_time != "00:00:00"){
                        $(".input_for_endEdit").val(res[0].end_time);
                    }
                }
            })
            $('#EditTimeModal').modal('show');
        })
        $(document).on("click",".btn_for_save_edits",function(){
            var input_for_startdateEdit = $(".input_for_startdateEdit").val();
            var input_for_startEdit = $(".input_for_startEdit").val();
            var input_for_endEdit = $(".input_for_endEdit").val();
            var input_for_enddateEdit = $(".input_for_enddateEdit").val();
            $.ajax({
                type:"post",
                data:{action:"UpdateClock",start_date:input_for_startdateEdit,end_date:input_for_enddateEdit,start:input_for_startEdit,end:input_for_endEdit,id:data_id},
                success:function(res){
                    location.reload();
                }
            })
        })
    })
</script>