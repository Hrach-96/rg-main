<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 4/17/18
 * Time: 11:00 AM
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$pageName = "Sku Codes";
$rootF = "../..";

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include("../flower_orders/lang/language_am.php");

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

$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");

$userData = $userData[0];

if (!(isset($userData["id"]) && (int)$userData["id"] > 0)) {
    header("location:../../login");
}
// Get Sku code
$query_result = getwayConnect::$db->query("SELECT * FROM delivery_sellpoint");
$total_result = [];
foreach ($query_result as $row) {
    $total_result[] = $row;
}
if(isset($_POST['action'])){
    if($_POST['action'] == "change_column"){
        $id = $_POST['id'];
        $val = $_POST['val'];
        $column = $_POST['column'];
        getwayConnect::getwayData("UPDATE `delivery_sellpoint` SET `" . $column . "` = '" . $val . "' where id = '" . $id . "'");
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
    <link rel="stylesheet" href="../accountant/index_css.css">


    <title>Partners Info</title>
</head>
<body>
    <style type="text/css">
    .switch {
          position: relative;
          display: inline-block;
          width: 60px;
          height: 34px;
        }

        .switch input { 
          opacity: 0;
          width: 0;
          height: 0;
        }

        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }

        .slider:before {
          position: absolute;
          content: "";
          height: 26px;
          width: 26px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          -webkit-transition: .4s;
          transition: .4s;
        }

        input:checked + .slider {
          background-color: #2196F3;
        }

        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
          -webkit-transform: translateX(26px);
          -ms-transform: translateX(26px);
          transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
          border-radius: 34px;
        }

        .slider.round:before {
          border-radius: 50%;
        }
</style>
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
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false"><?= (defined('FILTER')) ? FILTER : 'FILTER'; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"], $pageName);
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>
                    </ul>
                    <?php if (auth::roleExist(33)): ?>
                    <!--                    --><?php //if (max(page::filterLevel(3, $levelArray)) >= 33): ?>
                </li>
                <li><a href="order.php"
                       target="_blank"><?= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; ?></a></li>
            <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class='row' style='margin-top:88px'>
    <div class='col-md-12'>
        <a style='margin-left:20px;' href="partner_info_action.php" class='btn btn-primary'>Add Partner</a>
    </div>
</div>
<div class="header-space"></div>
<div class="toggle_filter">
    Filter
    <span class="glyphicon glyphicon-chevron-down filter_open" aria-hidden="true"></span>
    <span class="glyphicon glyphicon-chevron-up filter_close" aria-hidden="true"></span>
</div>
<div class=" col-xs-12 par_data_table margin-top">
    <table id="data_table" class="table table-bordered table-hover">

        <thead>
        <tr>
            <th>
                #
            </th>
            <th>
                Name
            </th>
            <th>
                Level
            </th>
            <th>
                Company Name
            </th>
            <th>
                Phone
            </th>
            <th>
                Email
            </th>
            <th>
                ՀՎՀՀ համար
            </th>
            <th>
                Is partner
            </th>
            <th>
                Active
            </th>
            <th>
                Inform Delivery
            </th>
            <th>
                Action
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($total_result as $key => $value) {
            ?>
            <tr >
                <td>
                    <?= $value['id'] ?>
                </td>
                <td>
                    <?= $value['name'] ?>
                </td>
                <td>
                    <?= $value['level'] ?>
                </td>
                <td>
                    <?= $value['company_name'] ?>
                </td>
                <td>
                    <?= $value['phone'] ?>
                </td>
                <td>
                    <?= $value['email'] ?>
                </td>
                <td>
                    <?= $value['hvhh_number'] ?>
                </td>
                <td>
                    <label class="switch">
                      <input type="checkbox" <?= ($value['ispartner'] == 1)? 'checked' : '' ?> data-column="ispartner" data-id="<?=$value['id']?>" class='switchColumn'>
                      <span class="slider round"></span>
                    </label>
                </td>
                <td>
                    <label class="switch">
                      <input type="checkbox" <?= ($value['active'] == 1)? 'checked' : '' ?> data-column="active" data-id="<?=$value['id']?>" class='switchColumn'>
                      <span class="slider round"></span>
                    </label>
                </td>
                <td>
                    <label class="switch">
                      <input type="checkbox" <?= ($value['inform_delivery'] == 1)? 'checked' : '' ?> data-column="inform_delivery" data-id="<?=$value['id']?>" class='switchColumn'>
                      <span class="slider round"></span>
                    </label>
                </td>
                <td>
                    <a target='_blank' href="partner_info_action.php?partner_id=<?php echo $value['id'] ?>" class="edit_item" title="Փոփոխել">
                        <i class="glyphicon glyphicon-edit"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>

        </tbody>

    </table>
</div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>

<script src="<?= $rootF ?>/template/DataTables/datatables.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/js/validator.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#data_table').DataTable( {} );
        $(document).on("change",".switchColumn",function(){
            var id = $(this).attr('data-id');
            var column = $(this).attr('data-column');
            var val;
            if($(this).prop('checked')){
                val = 1
            }
            else{
                val = 0
            }
            $.ajax({
                type:"post",
                data:{action:"change_column",id:id,val:val,column:column},
                success:function(res){
                }
            })
        })
    })
</script>
</body>
</html>