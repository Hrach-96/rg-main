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
    $variables = getwayConnect::getwayData("SELECT * FROM off_on");
    if(isset($_POST)){
        if($_POST['action'] == "change_action"){
            $id = $_POST['id'];
            $val = $_POST['val'];
            getwayConnect::getwayData("UPDATE off_on SET action = '" . $val . "' where id = '" . $id . "'");
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


    <title>Off / On</title>
</head>
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
    <table class="table table-bordered " style='margin-top:100px;'>
        <thead>
            <tr>
                <th>id</th>
                <th>Title</th>
                <th>Variable</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($variables as $key=>$value){
                    ?>
                        <tr>
                            <td><?=$value['id']?></td>
                            <td><?=$value['title']?></td>
                            <td><?=$value['variable']?></td>
                            <td>
                                <label class="switch">
                                  <input type="checkbox" <?= ($value['action'] == 1)? 'checked' : '' ?> data-id="<?=$value['id']?>" class='SwitchInput'>
                                  <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.4.1.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $(document).on("change",".SwitchInput",function(){
                var id = $(this).attr('data-id');
                var val;
                if($(this).prop('checked')){
                    val = 1
                }
                else{
                    val = 0
                }
                $.ajax({
                    type:"post",
                    data:{action:"change_action",id:id,val:val},
                    success:function(res){
                    }
                })
            })
        })
    </script>
</body>
</html>