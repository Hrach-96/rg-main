<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
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
                        for($fi = 0 ; $fi < count($fData);$fi++){
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
<ol class="breadcrumb" id="activeFilters" style="position:fixed;top:51px;width: 100%;z-index: 99;border-bottom:dashed #777 1px;">
</ol>
<div class="container" style="margin-top:81px;width: 100%">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-default" href="?templates"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;User Storages</a>
                <a class="btn btn-default" href="?show_mail_contacts=true"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;Storages</a>
            </div>
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-default" href="?template=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD Storage</a>
            </div>
        </div>
        <div class="panel-body">
	    <?php
		if(!isset($_REQUEST['view'])){
		    $users_query = "SELECT `user`.`username`,
				    `user`.`id` AS uid,
				    SU.*,
				    S.`name` AS storage_name,
				    SUC.`category_id`,
				    OC.`cname` AS category_name
				    FROM
				    `user`
				    LEFT JOIN `storage_user` AS SU ON SU.`user_id` = `user`.`id`
				    LEFT JOIN `storage` AS S ON S.`id` = SU.`storage_id`
				    LEFT JOIN `storage_user_category` AS SUC ON SUC.`user_id` = SU.`user_id`
				    LEFT JOIN `orders_categories` AS OC ON SUC.`category_id` = OC.`id`
				    WHERE SUC.`active` = 1 ORDER BY uid
				    ;";
		    $data = getwayConnect::getwayData($users_query);
		    $combine = new stdClass();
		    $combine->{'user'} = array();
		    foreach($data as $value){
			if(!isset($combine->user[$value['username']])){
			    $combine->user[$value['username']] = array();
			    $combine->user[$value['username']]['id'] = $value['uid'];
			    $combine->user[$value['username']]['storage_list'] = array();
			    $combine->user[$value['username']]['category_list'] = array();
			}
			if(!isset($combine->user[$value['username']]['storage_list'][$value['storage_id']])){
			    $combine->user[$value['username']]['storage_list'][$value['storage_id']] = $value['storage_name']."({$value['storage_id']})";
			}
			if(!isset($combine->user[$value['username']]['category_list'][$value['storage_name']])){
			    $combine->user[$value['username']]['category_list'][$value['storage_name']] = array();
			}
			if(!isset($combine->user[$value['username']]['category_list'][$value['storage_name']][$value['category_id']])){
			    $combine->user[$value['username']]['category_list'][$value['storage_name']][$value['category_id']] = $value['category_name']."({$value['category_id']})";
			}
			//echo "<br>{$value['storage_name']}::{$value['category_name']}";
		    }
		    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Storages</th>
                                        <th>Categories</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
		    foreach($combine->user as $user_key => $user_data){
			$storage_categry = '';
			foreach($user_data['category_list'] as $sn => $cn){
			    $storage_categry .= '<strong>'.$sn.'</strong><p>'.implode(",",$cn).'</p>';
			}
			$table .= "<tr>
				    <td>{$user_data['id']}</td>
				    <td>{$user_key}</td>
				    <td>".implode(",",$user_data['storage_list'])."</td>
				    <td>{$storage_categry}</td>
				    <td>
					<a class=\"btn btn-default\" href=\"?view=user_storage_edit&user_id={$user_data['id']}\"><span class=\"glyphicon glyphicon - pencil\" aria-hidden=\"true\"></span> EDIT</a>
				    </td>
				</tr>";
		    }
		    $table .= '</tbody>
                            </table>';
                    echo $table;
		}
	    ?>
        </div>
    </div>


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
<script src="<?=$rootF?>/template/tinymce/tinymce.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.min.js"></script>
<script type="text/javascript">
window.onload = function(){
    $("#loading").css("display","none");
};
</script>
</body>
</html>