<?php
//print_r($_REQUEST);
//die();
session_start();
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');  
$pageName = "flower";
$rootF = "../../";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$user_country = '0';
$data = "";
$enable_count_edit = true;//enable count edit if set to false enabled price edit
$count_edit_force_disabled = false;//force count disabled (disable count edit ankax paymanic)
if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
	page::accessByLevel($level[0]["user_level"],$pageName);
	$levelArray = explode(",",$level[0]["user_level"]);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
    $user_country = $userData[0]["country_short"];
	if(is_file("../lang/language_{$cc}.php"))
	{
		include("../lang/language_{$cc}.php");	
	}else{
		include("../lang/language_am.php");
	}
}

$root = true;
include("engine/engine.php");
include("engine/storage.php");
//$userAccess = ;
date_default_timezone_set ("Asia/Yerevan");
$user = $userData[0]["username"];

$greeting_card = null;
if(isset($_REQUEST['orderId'])){
    $greetings_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
    $greetings_card_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$greetings_card_type[0]['id']}' and order_id = '{$_REQUEST['orderId']}'");
    $greeting_card = $greetings_card_row[0]['value'];
}
//print_r($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!-- main.js for other functions -->
    <title>Print Post Card</title>

    <!-- Bootstrap core CSS -->
    <link href="products/template/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="products/template/css/starter-template.css" rel="stylesheet">
      
    <link rel="stylesheet" href="<?=$rootF?>/global-styles.css">
  </head>

  <body>
    <div class="container">

	<div class="starter-template">
	<div style="width:100%;text-align:center;">
        <textarea name="content" id="content" class="htmlEditor">
            <?= $greeting_card ?>
        </textarea>
    </div>

	</div>
	</div>

    <script src="products/template/js/bootstrap.min.js"></script>
    <script src="<?=$rootF?>template/tinymce/tinymce.min.js"></script>
	<script type="text/javascript">
        tinymce.init({
            selector: "textarea.htmlEditor",
            theme: "modern",
            plugins: "fullscreen textcolor advlist autolink link image lists charmap print preview emoticons code",
            toolbar:"code | undo redo | styleselect | bold italic forecolor backcolor | advlist autolink link image lists charmap print preview | fullscreen fontsizeselect",
            force_br_newlines : false,
            force_p_newlines : false,
            forced_root_block : '',
            fontsize_formats: '11px 12px 14px 16px 18px 24px 36px 48px'
        });
	</script>
  </body>
</html>