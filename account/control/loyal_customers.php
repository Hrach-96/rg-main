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
    $orderFilterBy = '';
    $payed = [1,3,6,7,11,12,13,14];
    $not_payed = [2,4,5,8,9,10];
    if(!empty($_GET['from_date']) || !empty($_GET['to_date'])){
        $sql = "SELECT sender_phone,sender_email,count(*) FROM `rg_orders`";
        $from_date = $_GET['from_date'];
        $to_date = $_GET['to_date'];
        $group_by = $_GET['group_by'];
        if(!empty($from_date)){
            $sql .= " where created_date >= '" . $from_date . "'"; 
            if(!empty($to_date)){
                $sql.= " and created_date <= '" . $to_date . "'";
                
            }
        }
        else if(!empty($to_date)){
            $sql.=" where created_date <= '" . $to_date . "'";
        }
        if($_REQUEST['status'] == 'Payed'){
            $sql.= " AND";
            foreach($payed as $key=>$value){
                if($key == 0){
                    $sql.= "( delivery_status =" . $value;
                }
                else{
                    $sql.= " or delivery_status = " . $value;
                }
            }
            $sql.=")";
        }
        if($_REQUEST['status'] == 'Not_Payed'){
            $sql.= " AND";
            foreach($not_payed as $key=>$value){
                if($key == 0){
                    $sql.= "( delivery_status =" . $value;
                }
                else{
                    $sql.= " or delivery_status = " . $value;
                }
            }
            $sql.=")";
        }

        if($_REQUEST['group_by'] == 'sender_email'){
            $orderFilterBy = "Sender Email";
            $sql.=" GROUP BY sender_email desc";
        }
        else if($_REQUEST['group_by'] == 'sender_phone'){
            $orderFilterBy = "Sender Phone";
            $sql.=" GROUP BY sender_phone desc";
        }
        if($_REQUEST['count'] == 5){
            $sql.= " HAVING COUNT(*) >= " . $_REQUEST['count'];
        }
        else{
            $sql.= " HAVING COUNT(*) = " . $_REQUEST['count'];
        }
        $total_result = getwayConnect::getwayData($sql);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/DataTables/datatables.css"/>
    <title>Loyal Customers</title>
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
            <div class='form-group col-md-2'>
                <input type='date' value="<?=(!empty($from_date)? $from_date : '')?>" name='from_date' class='from_date mt-1 form-control'>
            </div>
            <div class='form-group col-md-2'>
                <input type='date' value="<?=(!empty($to_date)? $to_date : '')?>" name='to_date' class='to_date mb-1 form-control'>
            </div>
            <div class='form-group col-md-2'>
                <select name='group_by' class='form-control'  >
                    <option <?=(isset($_GET['group_by']) && $_GET['group_by'] == 'sender_email' ) ? 'selected' : '' ?> value="sender_email">Sender Email</option>
                    <option <?=(isset($_GET['group_by']) && $_GET['group_by'] == 'sender_phone' ) ? 'selected' : '' ?> value="sender_phone">Sender Phone</option>
                </select>
            </div>
            <div class='form-group col-md-2'>
                <select name='count' class='form-control'  >
                    <option <?=(isset($_GET['count']) && $_GET['count'] == '2' ) ? 'selected' : '' ?> value="2">2</option>
                    <option <?=(isset($_GET['count']) && $_GET['count'] == '3' ) ? 'selected' : '' ?> value="3">3</option>
                    <option <?=(isset($_GET['count']) && $_GET['count'] == '4' ) ? 'selected' : '' ?> value="4">4</option>
                    <option <?=(isset($_GET['count']) && $_GET['count'] == '5' ) ? 'selected' : '' ?> value="5">5+</option>
                </select>
            </div>
            <div class='form-group col-md-2'>
                <select name='status' class='form-control'  >
                    <option <?=(isset($_GET['status']) && $_GET['status'] == 'All' ) ? 'selected' : '' ?> value="All">All</option>
                    <option <?=(isset($_GET['status']) && $_GET['status'] == 'Payed' ) ? 'selected' : '' ?> value="Payed">Payed</option>
                    <option <?=(isset($_GET['status']) && $_GET['status'] == 'Not_Payed' ) ? 'selected' : '' ?> value="Not_Payed">Not Payed</option>
                </select>
            </div>
            <button type='submit' class='btn btn-danger btn_for_click_filter'>Filter</button>
        </form>
    </div>
    <?php
        if(!empty($total_result)){
            ?>
                  <table id="data_table" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            #
                        </th>
                        <th>
                            <?=$orderFilterBy ?>
                        </th>
                        <th>
                            Count
                        </th>
                        <th>
                            View Orders
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach( $total_result as $key => $value ){
                                if(!empty($value[$_GET['group_by']])){
                                    ?>
                                        <tr>
                                            <td>
                                                <?=$key?>
                                            </td>
                                            <td>
                                                <?=$value[$_GET['group_by']]?>
                                            </td>
                                            <td>
                                                <?=$value['count(*)']?>
                                            </td>
                                            <td>
                                                <a href="loyal_cutomer_orders.php?from_date=<?=$_GET['from_date']?>&to_date=<?=$_GET['to_date']?>&group_by=<?=$_GET['group_by']?>&by=<?=$value[$_GET['group_by']]?>&status=<?=$_GET['status']?>" target='_blank' class='btn_for_edit_time btn btn-primary'>View Orders</a>
                                            </td>
                                        </tr>
                                    <?php
                                }
                            }
                        ?>
                    </tbody>
                </table>
                <hr>
                <?=$sql?>
            <?php
        }
    ?>
  
</div>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<script src="<?= $rootF ?>/template/DataTables/datatables.js"></script>
<script>
    $(document).ready(function () {
        $('#data_table').DataTable({
            "order": [[ 0, "desc" ]]
        });
    })
</script>