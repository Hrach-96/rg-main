<?php
session_start();
$pageName = "control";
$rootF = "../..";
include($rootF."/apay/pay.api.php");

include($rootF."/configuration.php");
include("actions.class.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$notify = "";
if(!$access){
    header("location:../../login");
}else{
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"],$pageName);
    $levelArray = explode(",",$level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
}
page::cmd();
if(isset($_REQUEST["submit"]) && $_REQUEST["submit"] == "edit")
{
    $data = array();
    if(isset($_REQUEST["username"]) && isset($_REQUEST["access_level"]) && isset($_REQUEST["uid"])){
        $data['username'] = $_REQUEST["username"];
        $data['user_level'] = $_REQUEST["access_level"];
        if(action::edit($_REQUEST["uid"],$data)){
            $notify = '<div class="alert alert-success" role="alert"><strong>Success!</strong> User info changed ;)!
            </div>';
        }else{
            $notify = '<div class="alert alert-danger" role="alert">
              <strong>Fail!</strong> User info not changed :( !
            </div>';
        }
    }else if(isset($_REQUEST["uid"]) && isset($_REQUEST["password"]) && isset($_REQUEST["username"])){
        $uExist = auth::checkUserExist($_REQUEST["username"]);
        $password = $_REQUEST["password"];
        if($uExist)
        {
            $data['password'] = auth::hash($password,$secureKey);
        }
        if(action::edit($_REQUEST["uid"],$data)) {
            $notify = '<div class="alert alert-success" role="alert">
              <strong>Success!</strong> Password changed ;)!
            </div>';
        }else{
            $notify = '<div class="alert alert-danger" role="alert">
              <strong>Fail!</strong> Password not changed :( !
            </div>';
        }
    }
}
if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "activate")
{
    if(isset($_REQUEST["mode"]) && isset($_REQUEST["uid"])){
        action::activation($_REQUEST["uid"],$_REQUEST["mode"]);
    }
}
if(isset($_GET['uid'])){
    $userData = getwayConnect::getwayData("SELECT * FROM `user` WHERE `uid` = '{$_GET['uid']}'",PDO::FETCH_ASSOC);
}else{
    header("location:../");
}
/*if(is_file("lang/language_{$cc}.php"))
{
	include("lang/language_{$cc}.php");
}else{
	include("lang/language_am.php");
}*/

$types = ['jpeg', 'png', 'JPEG', 'jpg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Apay gateway">
    <meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
    <meta name="author" content="Davit Gabrielyan, Ruben Mnatsakanyan">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
    <!-- Bootstrap minified CSS -->
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap optional theme -->
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
    <style>
        .article {word-wrap: break-word; max-width:125px;}
        .article .text {
            font-size: 13px;
            line-height: 17px;
            font-family: arial;
        }
        .article .text.short {
            height: 0px;
            overflow: hidden;
        }
        .article .text.full {

        }
        .read-more {

        }
        .date-picker-wrapper{
            z-index:99999999;
        }
        .datepicker{
            z-index:99999999;
        }
        hr{
            line-height: 0;
            margin-top:2px;
            margin-bottom:2px;
            border-color:#666;
        }
        #loading{
            position: fixed;
            z-index:9999999999;
            top: -6px;
            left: -8px;
            display: block;
        }
    </style>
    <title>Controller</title>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">RG-SYSTEM</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?=page::buildMenu($level[0]["user_level"])?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Filters <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"],"control");
                        for($fi = 0 ; $fi < count($fData);$fi++)
                        {
                            //echo "<li class=\"divider\"></li>";
                            //echo "<li class=\"dropdown-header\">{$fData[$fi][0]}</li>";
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>

                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container" style="margin-top:81px;width: 100%">
    <?=$notify;?>
    <form id="editUser" name="editU" method="POST" enctype="multipart/form-data">
        <?php
        if(!isset($_GET['pass'])):
        ?>
        <input type="text" name="username" placeholder="username" value="<?=(isset($userData[0]) && isset($userData[0]['username'])) ? $userData[0]['username'] : '';?>"/><br>
        <input type="text" name="access_level" placeholder="access level" value="<?=(isset($userData[0]) && isset($userData[0]['user_level'])) ? $userData[0]['user_level'] : '';?>"/><br>
        <input type="file" name="edit_image" placeholder="user image" accept="image/png, image/jpeg, image/jpg"><br>
        <?php foreach($types as $type){
            if(file_exists('../user_images/'.$userData[0]['uid'].'.'.$type)){ ?>
                <img src="<?= '../user_images/'.$userData[0]['uid'].'.'.$type ?>" alt="user image" width="100" height="100"><br>
        <?php    }
            }
        ?>

        <?php
        endif;
        if(isset($_GET['pass'])):
        ?>
            <input type="text" readonly name="username" placeholder="username" value="<?=(isset($userData[0]) && isset($userData[0]['username'])) ? $userData[0]['username'] : '';?>"/>
        <input type="text" name="password" placeholder="password" required/><br>
        <?php
        endif;
        ?>
        <input type="submit" name="submit" value="edit">
    </form>
</div>

<!-- initialize library-->
<!-- Latest jquery compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<!-- Bootstrap minified JavaScript -->
<script src="<?=$rootF?>/template/bootstrap/js/bootstrap.min.js"></script>
<!--end initialize library-->
<!-- Menu Toggle Script -->
<!-- Bootstrap minified JavaScript -->
<script src="<?=$rootF?>/template/js/accounting.min.js"></script>
<script src="<?=$rootF?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?=$rootF?>/template/js/phpjs.js"></script>
<script src="<?=$rootF?>/template/rangedate/moment.min.js"></script>
<script src="<?=$rootF?>/template/rangedate/jquery.daterangepicker.js"></script>
<script>

</script>
</body>
</html>