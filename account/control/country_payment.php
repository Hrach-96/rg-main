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
    $regions = getwayConnect::getwayData("SELECT * from countries where active = 1 ORDER BY `ordering` ASC, name_am");
    $payments = getwayConnect::getwayData("SELECT * from mail_payments");
    $AllConnections = getwayConnect::getwayData("SELECT region_connect_payment.id,mail_payments.icon,countries.name_am from region_connect_payment LEFT JOIN mail_payments on region_connect_payment.payment_id = mail_payments.id LEFT JOIN countries on region_connect_payment.region_id = countries.id");
    if(isset($_REQUEST['GetPaymentsOfCountry'])){
        $payments = getwayConnect::getwayData("SELECT * from region_connect_payment where region_id = '" . $_POST['country_id'] . "'");
        print json_encode($payments);die;
    }
    if(isset($_REQUEST['CountryConnectWithPayment'])){
        $region_id = $_POST['InputRegion'];
        $InputPayment = $_POST['InputPayment'];
        getwayConnect::getwaySend("DELETE FROM region_connect_payment where region_id='{$region_id}'");
        foreach( $InputPayment as $key => $value ){
            getwayConnect::getwaySend("INSERT INTO region_connect_payment (region_id,payment_id) VALUES('" . $region_id . "','" . $value . "' )");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/DataTables/datatables.css"/>
    <title>Country Payment</title>
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
        <div class='form-group col-md-2'>
            <select name='regions' class='form-control InputRegion'  >
                <option>Ընտրել</option>
                <?php
                    foreach( $regions as $key => $value ) {
                        ?>
                            <option value="<?=$value['id']?>"><?=$value['name_am']?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <div class='form-group col-md-2'>
            <select style='height:370px' name='payments' class='form-control InputPayment' multiple>
                <option>Ընտրել</option>
                <?php
                    foreach( $payments as $key => $value ) {
                        ?>
                            <option value="<?=$value['id']?>"><?=$value['name']?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <button type='submit' class='btn btn-danger btn_for_click_add_payment'>Connect</button>
    </div>
     <table id="data_table" class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>
                    #
                </th>
                <th>
                    Country
                </th>
                <th>
                    Payment
                </th>
            </tr>
            </thead>
            <tbody>
                <?php
                    foreach( $AllConnections as $key => $value ){
                        ?>
                            <tr>
                                <td>
                                    <?=$value['id']?>
                                </td>
                                <td>
                                    <?=$value['name_am']?>
                                </td>
                                <td>
                                    <img src="<?=$value['icon']?>" >
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
<script>
    $(document).ready(function () {
        $('#data_table').DataTable({
            "order": [[ 0, "desc" ]]
        });
        $(document).on("click",".btn_for_click_add_payment",function(){
            var InputRegion = $(".InputRegion").val();
            var InputPayment = $(".InputPayment").val();
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    CountryConnectWithPayment: true,
                    InputRegion:InputRegion,
                    InputPayment:InputPayment
                },
                success: function(resp){
                    location.reload();
                }
            })
        })
        $(document).on("change",".InputRegion",function(){
            var country_id = $(this).val();
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    GetPaymentsOfCountry: true,
                    country_id:country_id
                },
                success: function(resp){
                    resp = JSON.parse(resp);
                    $(".InputPayment option").removeAttr('selected');
                    for(var i = 0 ; i < resp.length ; i++){
                        $(".InputPayment option[value=" + resp[i]['payment_id'] + "]").attr('selected', 'selected'); 
                    }
                }
            })
        })
    })
</script>