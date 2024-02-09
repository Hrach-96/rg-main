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

    $userData = auth::checkUserExistById($uid);
    $total_result = getwayConnect::getwayData("SELECT * FROM `pending_info`");
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


    <title>Notes History</title>
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
    <table id="data_table" class="table table-bordered table-hover">

        <thead>
        <tr>
            <th>
                #
            </th>

            <th>
                ժամանակ
            </th>
            <th>
                Օպերատոր
            </th>
            <th>
                Order Id
            </th>
            <th>
                Ընթացիկ ստատուս
            </th>
            <th>
                Նկարագրություն
            </th>
        </tr>
        </thead>
        <tbody>
            <?php 
                foreach( $total_result as $key => $value ){
                    $OperatorInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = '".$value['operator_id']."'");
                    $orderCurrentStatus    = getwayConnect::getwayData("SELECT name_am FROM `rg_orders` LEFT JOIN delivery_status ON rg_orders.delivery_status = delivery_status.id WHERE rg_orders.id = " . $value['order_id']);
                    ?>
                        <tr>
                            <td>
                                <?=$value['id']?>
                            </td>
                            <td>
                                <?=$value['created_date']?>
                            </td>
                            <td>
                                <?=$OperatorInfo[0]['username']?>
                            </td>
                            <td>
                                <a target='_blank' href="../flower_orders/order.php?orderId=<?=$value['order_id']?>">
                                    <?=$value['order_id']?>
                                </a>
                            </td>
                            <td>
                                <?=$orderCurrentStatus[0]['name_am']?>
                            </td>
                            <td>
                                <?=$value['description']?>
                            </td>
                        </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
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
    })
</script>