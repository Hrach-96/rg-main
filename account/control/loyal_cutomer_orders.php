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
        $sql = "SELECT rg_orders.id,rg_orders.delivery_date,rg_orders.sell_point,rg_orders.sender_email,rg_orders.sender_phone,delivery_sellpoint.name as sell_point_name FROM `rg_orders` LEFT JOIN delivery_sellpoint on rg_orders.sell_point = delivery_sellpoint.id";
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
        if($_REQUEST['group_by'] == 'sender_email'){
            $orderFilterBy = "Sender Email";
            $sql.=" and sender_email like '%" . trim($_REQUEST['by']) . "%'" ;
        }
        else if($_REQUEST['group_by'] == 'sender_phone'){
            $orderFilterBy = "Sender Phone";
            $sql.=" and sender_phone like '%" . trim($_REQUEST['by']) . "%'" ;
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
    <?php
        if(!empty($total_result)){
            ?>
                  <table id="data_table" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            Orders ID
                        </th>
                        <th>
                            Date of Delivery
                        </th>
                        <th>
                            Sales Point
                        </th>
                        <th>
                            Email
                        </th>
                        <th>
                            Phone
                        </th>
                        <th>
                            View Orders
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach( $total_result as $key => $value ){
                                ?>
                                    <tr>
                                        <td>
                                            <?=$value['id']?>
                                        </td>
                                        <td>
                                            <?=$value['delivery_date']?>
                                        </td>
                                        <td>
                                            <?=$value['sell_point_name']?>
                                        </td>
                                        <td>
                                            <?=$value['sender_email']?>
                                        </td>
                                        <td>
                                            <?=$value['sender_phone']?>
                                        </td>
                                        <td>
                                            <a href="/account/flower_orders/order.php?orderId=<?=$value['id']?>" target='_blank' class='btn_for_edit_time btn btn-primary'>View Order</a>
                                        </td>
                                    </tr>
                                <?php
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