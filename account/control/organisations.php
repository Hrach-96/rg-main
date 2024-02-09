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
$array_street = ['name_am', 'name_ru', 'name_eng', 'street', 'address', 'entrance', 'floor', 'door_code', 'type', 'region', 'phones', 'emails', 'managers', 'active'];
//start of saving

if(isset($_REQUEST['submit']) && strtolower($_REQUEST['submit']) == "save"){
    $action = "INSERT INTO";
    $result = '-1';
    $loc = "?organisations";
    if(isset($_REQUEST['organisation_id'])){
        $action = ($_REQUEST['organisation_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_street)){
                $sets .= "`".$key."` = '".addslashes($value)."', ";
            }
        }
        $sets = trim($sets,', ');

        $sql = $action." `organisations` SET {$sets}";
        if($_REQUEST['organisation_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['organisation_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['organisation_id'] > 0) ? $_REQUEST['organisation_id'] : $result;
        $loc = "?organisation={$result}";
    }else if (isset($_REQUEST['new_type'])){
        $name = $_REQUEST['name'];
        $active = $_REQUEST['active'];
        $query_new_type = "Insert into organisation_types (name, active) VALUES ('".$name."', '".$active."')";
        $result = getwayConnect::getwaySend($query_new_type, true);
        $result = ($_REQUEST['organisation'] > 0) ? $_REQUEST['organisation'] : $result;
        $loc = "?organisations";
    }
    header("location:{$loc}&action={$result}");
}
if(isset($_POST['get']) && $_POST['get'] == 'get_streets' && isset($_POST['region'])){
    $streets = getwayConnect::getwayData("SELECT `name`, `old_name`, `code`  FROM delivery_street where sub_code = '{$_POST['region']}' ORDER BY `order`");
    echo json_encode($streets);
    exit;
}

// end of save
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
    <style type="text/css">
        div.mce-fullscreen{
            margin-top: 50px;
        }
        hr{
            line-height: 0;
            margin-top:2px;
            margin-bottom:2px;
            border-color:#666;
        }
        #loading{
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
                <a class="btn btn-default" href="?organisations">&nbsp;Organisations</a>
            </div>
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-default" href="?type=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD Organisation Type</a>
                <a class="btn btn-default" href="?organisation=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD Organisation</a>
            </div>
        </div>
        <div class="panel-body">
			<div id="loading" style="width:100%;text-align:center;"><img src="../../template/icons/loader.gif"></div>
            <?php
                if(isset($_REQUEST['organisation_type']) && $_REQUEST['organisation_type'] != ''){
                    $data = getwayConnect::getwayData("SELECT `organisations`.*, delivery_subregion.name as region_name, delivery_street.`name` as street_name, delivery_street.`old_name` as street_old_name FROM `organisations` LEFT JOIN delivery_subregion on delivery_subregion.id = organisations.region LEFT JOIN delivery_street on delivery_street.code = organisations.street WHERE `type` = '{$_REQUEST['organisation_type']}' ORDER BY organisations.`order` DESC");
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name Am</th>
                                        <th>Name Ru</th>
                                        <th>Name Eng</th>
                                        <th>Address</th>
                                        <th>Street</th>
                                        <th>Entrance</th>
                                        <th>Floor</th>
                                        <th>Door Code</th>
                                        <th>Region</th>
                                        <th>Phones</th>
                                        <th>Emails</th>
                                        <th>Managers</th>
                                        <th>Active</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        if(is_array($data) && count($data) > 0){
                            foreach ($data as $value){
                                $table .= "<tr>
                                            <td>{$value['id']}</td>
                                            <td>{$value['name_am']}</td>
                                            <td>{$value['name_ru']}</td>
                                            <td>{$value['name_eng']}</td>
                                            <td>{$value['address']}</td>
                                            <td>{$value['street_name']}";
                                if(isset($value['street_old_name']) && $value['street_old_name'] != ''){
                                    $table .= " ( ". $value['street_old_name'] ." )";
                                }
                                $table .= "</td>
                                            <td>{$value['entrance']}</td>
                                            <td>{$value['floor']}</td>
                                            <td>{$value['door_code']}</td>
                                            <td>{$value['region']}</td>
                                            <td>{$value['phones']}</td>
                                            <td>{$value['emails']}</td>
                                            <td>{$value['managers']}</td>
                                            <td>";
                                if(isset($value['active']) && $value['active'] == 1){
                                    $table .= "YES";
                                } else {
                                    $table .= "NO";
                                }
                                $table .= "</td>
                                            <td>
                                                <a class=\"btn btn-default\" href=\"?organisation={$value['id']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                            </td>
                                        </tr>";
                            }
                        }
                        $table .= '</tbody>
                                </table>';
                        echo $table;

                } else if(isset($_REQUEST['organisation']) && ((int)$_REQUEST['organisation'] > 0 || $_REQUEST['organisation'] = 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `organisations` WHERE `id` = '{$_REQUEST['organisation']}'");
                    $sub_regions = getwayConnect::getwayData("SELECT * FROM delivery_subregion where id not in (13,14,15,16,17) ORDER BY `order` DESC");
                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    $types = getwayConnect::getwayData("SELECT * FROM organisation_types where 1");
                    ?>
                    <form method="POST" name="organisation_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="organisation_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="type">Type</label></th>
                                <th>
                                    <select  class="btn btn-default" name="type" id="type">
                                        <?php 
                                            foreach($types as $type){
                                                echo "<option value='".$type['id']."'";
                                                if($data['type'] == $type['id']){
                                                    echo 'selected="selected"';
                                                }
                                                echo ">".$type['name']."</option>";
                                            }
                                        
                                        ?>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="region">Region</label></th>
                                <th>
                                    <select  class="btn btn-default" name="region" id="region">
                                        <?php 
                                            foreach($sub_regions as $sub_region){
                                                echo "<option value='".$sub_region['code']."'";
                                                if($data['region'] == $sub_region['code']){
                                                    echo 'selected="selected"';
                                                }
                                                echo ">".$sub_region['name']."</option>";
                                            }
                                        
                                        ?>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="street">Street</label></th>
                                <th>
                                    <select name="street" id="street" class="form-control" data-val="<?=(isset($data['street'])) ? $data['street'] : ''?>">
                                        <option value="">Choose Street</option>
                                    </select>
                                    <!-- <input class="form-control" type="text" name="address_am" id="address_am" value="<?=(isset($data['address_am'])) ? $data['address_am'] : ''?>" placeholder="address_am"/> -->
                                </th>
                            </tr>
                            <tr>
                                <th><label for="name_am">Name AM</label></th>
                                <th><input class="form-control" type="text" name="name_am" id="name_am" value="<?=(isset($data['name_am'])) ? $data['name_am'] : ''?>" placeholder="name_am"/></th>
                            </tr>
                            <tr>
                                <th><label for="name_ru">Name Ru</label></th>
                                <th><input class="form-control" type="text" name="name_ru" id="name_ru" value="<?=(isset($data['name_ru'])) ? $data['name_ru'] : ''?>" placeholder="name ru"/></th>
                            </tr>
                            <tr>
                                <th><label for="name_eng">Name Eng</label></th>
                                <th><input class="form-control" type="text" name="name_eng" id="name_eng" value="<?=(isset($data['name_eng'])) ? $data['name_eng'] : ''?>" placeholder="name_eng"/></th>
                            </tr>
                            <tr>
                                <th><label for="address">Address</label></th>
                                <th><input class="form-control" type="text" name="address" id="address" value="<?=(isset($data['address'])) ? $data['address'] : ''?>" placeholder="address"/></th>
                            </tr>
                            <tr>
                                <th><label for="floor">Floor</label></th>
                                <th><input class="form-control" type="text" name="floor" id="floor" value="<?=(isset($data['floor'])) ? $data['floor'] : ''?>" placeholder="floor"/></th>
                            </tr>
                            <tr>
                                <th><label for="entrance">Entrance</label></th>
                                <th><input class="form-control" type="text" name="entrance" id="entrance" value="<?=(isset($data['entrance'])) ? $data['entrance'] : ''?>" placeholder="entrance"/></th>
                            </tr>
                            <tr>
                                <th><label for="door_code">Door code</label></th>
                                <th><input class="form-control" type="text" name="door_code" id="door_code" value="<?=(isset($data['door_code'])) ? $data['door_code'] : ''?>" placeholder="door_code"/></th>
                            </tr>
                            <tr>
                                <th><label for="phones">Phones</label></th>
                                <th><input class="form-control" type="text" name="phones" id="phones" value="<?=(isset($data['phones'])) ? $data['phones'] : ''?>" placeholder="phones"/></th>
                            </tr>
                            <tr>
                                <th><label for="emails">Emails</label></th>
                                <th><input class="form-control" type="text" name="emails" id="emails" value="<?=(isset($data['emails'])) ? $data['emails'] : ''?>" placeholder="emails"/></th>
                            </tr>
                            <tr>
                                <th><label for="managers">Managers</label></th>
                                <th><input class="form-control" type="text" name="managers" id="managers" value="<?=(isset($data['managers'])) ? $data['managers'] : ''?>" placeholder="managers"/></th>
                            </tr>
                            
                            <tr>
                                <th><label for="active">Active</label></th>
                                <th>
                                    <select  class="btn btn-default" name="active" id="active">
                                        <option value="0" <?=(isset($data['active']) && $data['active'] == 0) ? 'selected="selected"' : '';?>>NO</option>
                                        <option value="1" <?=(isset($data['active']) && $data['active'] == 1) ? 'selected="selected"' : '';?>>YES</option>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="submit" value="SAVE" name="submit"  class="btn btn-default"/></th>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                <?php
                } else if(isset($_REQUEST['type']) && $_REQUEST['type'] = 'new'){
                ?>
                    <form method="POST" name="new_type">
                        <input type="hidden" value="new_type" name="new_type" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th>'NEW'</th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th><input class="form-control" type="text" name="name" id="name" placeholder="name"/></th>
                            </tr>
                            <tr>
                                <th><label for="active">Active</label></th>
                                <th>
                                    <select  class="btn btn-default" name="active" id="active">
                                        <option value="0">NO</option>
                                        <option value="1">YES</option>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="submit" value="SAVE" name="submit"  class="btn btn-default"/></th>
                            </tr>
                            </tbody>
                        </table>
                    </form>
            <?php
                }else{
                    $data = getwayConnect::getwayData("SELECT * from organisation_types where 1");
                    // <tr> <th scope="row">1</th> <td>Mark</td> <td>Otto</td> <td>@mdo</td> </tr> <tr> <th scope="row">2</th> <td>Jacob</td> <td>Thornton</td> <td>@fat</td> </tr> <tr> <th scope="row">3</th> <td>Larry</td> <td>the Bird</td> <td>@twitter</td> </tr>
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Active</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <td>{$value['id']}</td>
                                        <td>{$value['name']}</td>
                                        <td>";
                            if(isset($value['active']) && $value['active'] == 1){
                                $table .= "YES";
                            } else {
                                $table .= "NO";
                            }
                            $table .= "</td>
                                        <td>
                                            <a class=\"btn btn-default\" href=\"?organisation_type={$value['id']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> Show Organisations</a>
                                        </td>
                                    </tr>";
                        }
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
    var result = <?=(isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : 0;?>;
    if(result > 0){
        alert('Success');
    }else if(result == -1){
        alert('fail');
    }
window.onload = function(){
    $("#loading").css("display","none");
    load_streets();
};
$(document).on('click', "button.read-more", function () {
   $(this).parent().find(".text").toggle();
});
tinymce.init({
    selector: "textarea.htmlEditor",
    theme: "modern",
    plugins: "fullscreen textcolor advlist autolink link image lists charmap print preview emoticons code",
    toolbar:"code | undo redo | styleselect | bold italic forecolor backcolor | advlist autolink link image lists charmap print preview | fullscreen",
    force_br_newlines : false,
    force_p_newlines : false,
    forced_root_block : ''
});
$('body').on('change', '#region', function(){
    load_streets();
})

function load_streets(){
    let region = $('#region').val();
    let val = $('#street').attr('data-val');
    $.ajax({
        type: 'post',
        url: location.href,
        data: {
            get: 'get_streets',
            region: region
        },
        success: function(resp){
            if(resp != undefined){
                let data = JSON.parse(resp);
                $('#street').html('');
                let html = '<option value="">Choose Street</option>';
                if(data.length){
                    data.forEach(street => {
                        html += '<option value="'+street.code+'"';
                        if(val != undefined && val != '' && val == street.code){
                            html += ' selected="selected"';
                        }
                        html += '>'+street.name;
                        if(street.old_name != undefined && street.old_name != ''){
                            html += ' ( ' + street.old_name + ' )';
                        }
                        html += '</option>'
                    });
                } else {
                    html += '<option value="E-1">none</option>';
                }
                $('#street').html(html);
            }
        }
    })
}
</script>
</body>
</html>