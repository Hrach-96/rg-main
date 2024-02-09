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
$array_street = ['sub_code', 'code', 'name', 'name_ru', 'name_en', 'old_name','duplicate','duplicate_count', 'distance', 'delivery_time', 'zone', 'wiki_url','delivery_price', 'coordinates', 'updated_by'];
$zones = getwayConnect::getwayData("SELECT * FROM delivery_zone where active=1");
//start of saving
if(isset($_REQUEST['getDublicatesSubCodes']) && $_REQUEST['getDublicatesSubCodes']){
    $result = getwayConnect::getwayData("SELECT * FROM delivery_street where name='" . $_REQUEST['name'] . "'");
    print json_encode($result);die;
}
if(isset($_REQUEST['getDublicates']) && $_REQUEST['getDublicates']){
    $result = getwayConnect::getwayData("SELECT name, COUNT(*) c FROM delivery_street GROUP BY name HAVING c > 1 order by c desc");
    print json_encode($result);die;
}
if(isset($_REQUEST['submit']) && strtolower($_REQUEST['submit']) == "save"){
    $action = "INSERT INTO";
    $result = '-1';
    $loc = "?templates=true&action={$result}";
    if(isset($_REQUEST['street_id'])){
        $action = ($_REQUEST['street_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_street)){
                $val = trim($value);
                if($key == 'old_name'){
                    if($val == ''){
                        $val = NULL;
                    }
                }
                if($key == 'duplicate'){
                    if($val != 1){
                        $val = 0;
                    }
                }
                $sets .= "`".$key."` = '".addslashes($val)."', ";
            }
        }
        $sets = trim($sets,', ');

        $sql = $action." `delivery_street` SET {$sets}";
        if($_REQUEST['street_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['street_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['street_id'] > 0) ? $_REQUEST['street_id'] : $result;
        $loc = "?street={$result}";
    }else if (isset($_REQUEST['new_region'])){
        $name = $_REQUEST['name'];
        $code = $_REQUEST['code'];
        $active = $_REQUEST['active'];
        $sub_code = $_REQUEST['sub_code'];
        $query_subregion = "Insert into delivery_subregion (sub_code, code, name, active) VALUES ('".$sub_code."', '".$code."', '".$name."', '".$active."')";
        $query_op_subregion = "Insert into delivery_op_subregion (id, name, active, level) VALUES ('".$code."', '".$name."', '".$active."', '30,31,32,33,34,35,36,37,38,39')";
        $result = getwayConnect::getwaySend($query_subregion, true);
        $result = getwayConnect::getwaySend($query_op_subregion, true);
        $result = ($_REQUEST['street_id'] > 0) ? $_REQUEST['street_id'] : $result;
        $loc = "?regions";
    }
    else if (isset($_REQUEST['edit_region'])){
        $name = $_REQUEST['name'];
        $code = $_REQUEST['code'];
        $active = $_REQUEST['active'];
        $sub_code = $_REQUEST['sub_code'];
        $query_subregion = "UPDATE delivery_subregion SET `sub_code`='".$sub_code."',  `name`='".$name."', `active`= '".$active."' WHERE `code`='".$code."'";
        $query_op_subregion = "UPDATE delivery_op_subregion  SET `name`='".$name."', `active`= '".$active."' WHERE `id`='".$code."'";
        $result = getwayConnect::getwaySend($query_subregion, true);
        $result = getwayConnect::getwaySend($query_op_subregion, true);
        $result = ($_REQUEST['street_id'] > 0) ? $_REQUEST['street_id'] : $result;
        $loc = "?regions";
    }
    header("location:{$loc}&action={$result}");
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
                <a class="btn btn-default" href="?regions">&nbsp;Regions</a>
            </div>
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-default" href="?street=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD Street</a>
                <a class="btn btn-default" href="?region=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD Region</a>
                <?php
                    if($_REQUEST['region']){
                        ?>
                            <a class="btn btn-default" href="download-excel-main.php?download_region_data=true&region=<?php echo $_REQUEST['region'] ?>" target='_blank'><img src="/images/excel.png" style="height:18px"> CVS</a>
                        <?php
                    }
                ?>
                <?php
                    if(isset($_REQUEST['street']) && (int)$_REQUEST['street'] > 0){
                        $str = getwayConnect::getwayData("SELECT * FROM delivery_street where id='{$_REQUEST['street']}'");
                        if(isset($str) && !empty($str[0])){
                            echo "<a class='btn btn-default' href='?region=".$str[0]['sub_code']."'>Back</a>";
                        }
                    }
                ?>
            </div>
        </div>
        <div class="panel-body">
            <div id="loading" style="width:100%;text-align:center;"><img src="../../template/icons/loader.gif"></div>
            <?php
                if(isset($_REQUEST['region']) && $_REQUEST['region'] != '' && $_REQUEST['region'] != 'new'){
                    $data = getwayConnect::getwayData("SELECT * FROM `delivery_street` WHERE `sub_code` = '{$_REQUEST['region']}' ORDER BY `name_en`");
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Sub Code</th>
                                        <th>Code</th>
                                        <th>Old Name</th>
                                        <th>Name Ru</th>
                                        <th>Name En</th>
                                        <th>Distance</th>
										<th>Delivery Price</th>
                                        <th>Delivery Time</th>
                                        <th>Zone</th>
                                        <th>Wiki Url</th>
                                        <th>Coordinates</th>
                                        <th>Active</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        if(is_array($data) && count($data) > 0){
                            foreach ($data as $value){
                                $updated_by = '';
                                if($value['updated_by']){
                                    $updated_by = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$value['updated_by']}'")[0]['full_name_am'];
                                }
                                $see_road_Google = '<a target="blank" href="https://www.google.com/maps/dir/Yervand+Kochar+Street,+Yerevan,+Armenia/' . $value['coordinates'] .'/@40.2000815,44.4699913,12z/data=!3m1!4b1!4m12!4m11!1m5!1m1!1s0x406abcf595178223:0xf074b89337f1809!2m2!1d44.5177361!2d40.1713366!1m3!2m2!1d45.18!2d40.38!3e0">Google</a>'; // Դիտել Ճանապարհը
						        $see_road_Yandex = '<a target="blank" href="https://yandex.ru/maps/?rtext=40.169435,44.516355~' . str_replace(" ", "", $value['coordinates']).'&rtt=auto&whatshere[zoom]=17">Yandex </a>'; 
					   		    $table .= "<tr>
                                            <td>{$value['id']}</td>
                                            <td>{$value['name']}</td>
                                            <td>{$value['sub_code']}</td>
                                            <td>{$value['code']}</td>
                                            <td>{$value['old_name']}</td>
                                            <td>{$value['name_ru']}</td>
                                            <td>{$value['name_en']}</td>
                                            <td>{$value['distance']}</td>
											<td><b>{$value['delivery_price']}</b></td>
                                            <td>{$value['delivery_time']}</td>
                                            <td>{$value['zone']}</td>
                                            <td><a href='{$value['wiki_url']}' target='_blank'>{$value['wiki_url']}</a><br>{$updated_by}</td>

                                            <td>{$see_road_Google} || {$see_road_Yandex}<br> {$value['coordinates']}</td>
                                            <td>";
                                if(isset($value['active']) && $value['active'] == 1){
                                    $table .= "YES";
                                } else {
                                    $table .= "NO";
                                }
                                $table .= "</td>
                                            <td>
                                                <a class=\"btn btn-default\" href=\"?street={$value['id']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                            </td>
                                        </tr>";
                            }
                        }
                        $table .= '</tbody>
                                </table>';
                        echo $table;
                        ?>
                            <button class='btn btn-warning displayDublicates'>Ցույց տալ կրկնորինակները</button>
                            <div class='dublicateResult mt-4'></div>
                        <?php
                } else if(isset($_REQUEST['street']) && ((int)$_REQUEST['street'] > 0 || $_REQUEST['street'] == 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `delivery_street` WHERE `id` = '{$_REQUEST['street']}'");
                    $sub_regions = getwayConnect::getwayData("SELECT * FROM delivery_subregion where id not in (13,14,16,17) Order By `order` DESC");
                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    ?>
                    <form method="POST" name="street_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="street_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="sub_code">Sub Code</label></th>
                                <th>
                                    <select  class="btn btn-default" name="sub_code" id="sub_code">
                                        <?php 
                                            foreach($sub_regions as $sub_region){
                                                echo "<option value='".$sub_region['code']."'";
                                                if($data['sub_code'] == $sub_region['code']){
                                                    echo 'selected="selected"';
                                                }
                                                echo ">".$sub_region['name']."</option>";
                                            }
                                        
                                        ?>
                                    </select>
                                </th>
                            </tr>  
							<tr>
							<?
							    $see_road_Google = '<a target="blank" href="https://www.google.com/maps/dir/Yervand+Kochar+Street,+Yerevan,+Armenia/' . str_replace(" ", "", $data['coordinates']) .'/@40.2000815,44.4699913,12z/data=!3m1!4b1!4m12!4m11!1m5!1m1!1s0x406abcf595178223:0xf074b89337f1809!2m2!1d44.5177361!2d40.1713366!1m3!2m2!1d45.18!2d40.38!3e0">Google</a>'; // Դիտել Ճանապարհը
                                $see_road_Yandex = '<a target="blank" href="https://yandex.ru/maps/?rtext=40.169435,44.516355~' . str_replace(" ", "", $data['coordinates']).'&rtt=auto&whatshere[zoom]=17">Yandex</a>';

							?>
														<tr>
                            <th><label for="delivery_time">Delivery Time (in minutes)</label> <img src="https://new.regard-group.ru/account/flower_orders/ico/chvert.png" style="vertical-alig:middle;width:70px;align:left;"></th>
                                <th>мин<input class="form-control" type="number" name="delivery_time" id="delivery_time" value="<?=(isset($data['delivery_time'])) ? $data['delivery_time'] : ''?>" placeholder="delivery_time"/></th>
                            </tr>
							 <tr>
                                <th><label for="coordinates">Coordinates</label></th>
                                <th><input class="form-control" type="text" name="coordinates" id="coordinates" value="<?=(isset($data['coordinates'])) ? $data['coordinates'] : ''?>" placeholder="coordinates"/></th>
                            </tr>
                            <th><label for="distance">Distance (in kilometers) <? echo $see_road_Google. " | ".$see_road_Yandex; ?> </a></label></th>
                                <th>km<input class="form-control" maxlength="3" type="number" name="distance" id="distance" value="<?=(isset($data['distance'])) ? $data['distance'] : ''?>" placeholder="distance"/></th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th><input class="form-control" type="text" name="name" id="name" value="<?=(isset($data['name'])) ? $data['name'] : ''?>" placeholder="name"/></th>
                            </tr>
                            <tr>
                                <th><label for="name_ru">Name Ru</label></th>
                                <th><input class="form-control" type="text" name="name_ru" id="name_ru" value="<?=(isset($data['name_ru'])) ? $data['name_ru'] : ''?>" placeholder="name ru"/></th>
                            </tr>
                            <tr>
                                <th><label for="name_en">Name En</label></th>
                                <th><input class="form-control" type="text" name="name_en" id="name_en" value="<?=(isset($data['name_en'])) ? $data['name_en'] : ''?>" placeholder="name_en"/></th>
                            </tr>
                            <tr>
                                <th><label for="old_name">Old Name</label></th>
                                <th><input class="form-control" type="text" name="old_name" id="old_name" value="<?=(isset($data['old_name'])) ? $data['old_name'] : ''?>" placeholder="old_name"/></th>
                            </tr>
                            <tr>
                                <th>
                                    <label>Duplicate</label><br>
                                    <label>Duplicate count</label> 
                                </th>
                                <th>
                                    <select  class="btn btn-default" name="duplicate" id="duplicate">
                                        <option value="0" <?=(isset($data['duplicate']) && $data['duplicate'] == 0) ? 'selected="selected"' : '';?>>NO</option>
                                        <option value="1" <?=(isset($data['duplicate']) && $data['duplicate'] == 1) ? 'selected="selected"' : '';?>>YES</option>
                                    </select>
                                    <select class='btn btn-default' name='duplicate_count'>
                                        <option <?php echo ($data['duplicate_count'] == 1)? 'selected' : '' ?> >1</option>
                                        <option <?php echo ($data['duplicate_count'] == 2)? 'selected' : '' ?> >2</option>
                                        <option <?php echo ($data['duplicate_count'] == 3)? 'selected' : '' ?> >3</option>
                                        <option <?php echo ($data['duplicate_count'] == 4)? 'selected' : '' ?> >4</option>
                                        <option <?php echo ($data['duplicate_count'] == 5)? 'selected' : '' ?> >5</option>
                                    </select>
                                </th>
                            </tr>
							<tr>
                                <th><label for="delivery_price">Delivery Price</label></th>
                                <th><input disabled class="form-control" type="number" name="delivery_price" id="delivery_price" value="<?=(isset($data['delivery_price'])) ? $data['delivery_price'] : ''?>" placeholder="Delivery Price"/></th>
                            </tr>
                            <tr>
                                <th><label for="code">Code</label></th>
                                <th><input class="form-control" type="text" name="code" id="code" value="<?=(isset($data['code'])) ? $data['code'] : ''?>" placeholder="code"/></th>
                            </tr>							
                            <tr>
                                <th><label for="zone">Zone</label></th>
                                <!-- <th><input class="form-control" type="number" name="zone" id="zone" value="<?=(isset($data['zone'])) ? $data['zone'] : 0?>" placeholder="zone"/></th> -->
                                <th>
                                    <select class="btn btn-default" name="zone" id="zone">
                                        <?php
                                            foreach($zones as $zone){
                                                echo "<option value='".$zone['id']."'";
                                                if(isset($data['zone']) && $data['zone'] == $zone['id']){
                                                    echo "selected='selected'";
                                                }
                                                echo ">";
                                                echo $zone['zone'];
                                                echo "</option>";
                                            }
                                        ?>
                                    </select>
                                    <span>
                                        <?php
                                            if(isset($data['zone'])){
                                                foreach($zones as $zone){
                                                    if($data['zone'] == $zone['id']){
                                                        echo $zone['price'];
                                                    }
                                                }
                                            }
                                        ?>
                                    </span>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="wiki_url"><a href="<?php echo $data['wiki_url'] ?>" target='_blank' >Wiki Url</a></label></th>
                                <th><input class="form-control" type="text" name="wiki_url" id="wiki_url" value="<?=(isset($data['wiki_url'])) ? $data['wiki_url'] : ''?>" placeholder="wiki_url"/></th>
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
                        <input type='hidden' name="updated_by" value="<?php echo $userData[0]['id'] ?>"> 
                    </form>
                <?php
                } else if(isset($_REQUEST['region']) && $_REQUEST['region'] == 'new'){
                    $regions = getwayConnect::getwayData("SELECT * FROM delivery_region where active = 1");
                    ?>
                    <form method="POST" name="new_region">
                        <input type="hidden" value="new_region" name="new_region" />
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
                                <th><label for="code">Code</label></th>
                                <th><input class="form-control" type="text" name="code" id="code" placeholder="code"/></th>
                            </tr>
                            <tr>
                                <th><label for="sub_code">Sub Code</label></th>
                                <th>
                                    <select  class="btn btn-default" name="sub_code" id="sub_code">
                                        <?php
                                            foreach($regions as $region){
                                                echo "<option value='".$region['code']."'>".$region['name']."</option>";
                                            }
                                        ?>
                                    </select>
                                </th>
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
                } else if(isset($_REQUEST['edit_region']) && $_REQUEST['edit_region'] != ''){
                    $edit_region = getwayConnect::getwayData("SELECT * from delivery_subregion where id='{$_REQUEST['edit_region']}'")[0];
                    $regions = getwayConnect::getwayData("SELECT * FROM delivery_region where active = 1");
                ?>
                    <form method="POST" name="edit_region">
                        <input type="hidden" value="<?=$_REQUEST['edit_region']?>" name="edit_region" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th>EDIT</th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th><input class="form-control" type="text" name="name" id="name" placeholder="name" value="<?=$edit_region['name']?>"/></th>
                            </tr>
                            <tr>
                                <th><label for="code">Code</label></th>
                                <th><input class="form-control" type="text" name="code" id="code" placeholder="code" value="<?=$edit_region['code']?>" readonly /></th>
                            </tr>
                            <tr>
                                <th><label for="sub_code">Sub Code</label></th>
                                <th>
                                    <select  class="btn btn-default" name="sub_code" id="sub_code">
                                        <?php
                                            foreach($regions as $region){
                                                echo "<option value='".$region['code']."'";
                                                if($edit_region['sub_code'] == $region['code']){
                                                    echo "selected='selected'";
                                                }
                                                echo ">".$region['name']."</option>";
                                            }
                                        ?>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="active">Active</label></th>
                                <th>
                                    <select  class="btn btn-default" name="active" id="active">
                                        <option value="0" <?=(isset($edit_region['active']) && $edit_region['active'] == '0' ? 'selected="selected"' : '' )?>>NO</option>
                                        <option value="1" <?=(isset($edit_region['active']) && $edit_region['active'] == '1' ? 'selected="selected"' : '' )?>>YES</option>
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
                } else{

                    $data = getwayConnect::getwayData("SELECT * from delivery_subregion where id NOT IN (13,14,16,17) ORDER BY `order` DESC");
                    // <tr> <th scope="row">1</th> <td>Mark</td> <td>Otto</td> <td>@mdo</td> </tr> <tr> <th scope="row">2</th> <td>Jacob</td> <td>Thornton</td> <td>@fat</td> </tr> <tr> <th scope="row">3</th> <td>Larry</td> <td>the Bird</td> <td>@twitter</td> </tr>
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Sub Code</th>
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <td>{$value['id']}</td>
                                        <td>{$value['name']}</td>
                                        <td>{$value['sub_code']}</td>
                                        <td>";
                            if(isset($value['active']) && $value['active'] == 1){
                                $table .= "YES";
                            } else {
                                $table .= "NO";
                            }
                            $table .= "</td>
                                        <td>
                                            <a class=\"btn btn-default\" href=\"?edit_region={$value['id']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                            <a class=\"btn btn-default\" href=\"?region={$value['code']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> Show Streets</a>
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
};
$(document).on('click', ".display_sub_codes", function () {
    var td = $(this);
    var name = $(td).attr('data-name');
    $.ajax({
        url: location.href,
        type: 'post',
        data: {
            name: name,
            getDublicatesSubCodes: true,
        },
        success: function(resp){
            resp = JSON.parse(resp);
            var html_sub_table = "<br>";
            for(var i = 0;i< resp.length;i++){
                html_sub_table+= resp[i]['sub_code'] + ", ";
            }
            $(td).find(".sub_code_result").html(html_sub_table.slice(0,-1));
        }
    })
})
$(document).on('click', ".displayDublicates", function () {
    $.ajax({
        url: location.href,
        type: 'post',
        data: {
            getDublicates: true,
        },
        success: function(resp){
            resp = JSON.parse(resp);
            var html_table = "<table class='table table-bordered'>";
                    html_table+= "<tr>";
                        html_table+= "<td>";
                            html_table+= "Անվանում";
                        html_table+= "</td>";
                        html_table+= "<td>";
                            html_table+= "Քանակ";
                        html_table+= "</td>";
                    html_table+= "</tr>";
                    for(var i = 0; i < resp.length;i++ ){
                        html_table+= "<tr>";
                            html_table+= "<td data-name='" + resp[i]['name'] + "' class='display_sub_codes'>";
                                html_table+= resp[i]['name'];
                            html_table+= "<div class='sub_code_result'></div>";
                            html_table+= "</td>";
                            html_table+= "<td>";
                                html_table+= resp[i]['c'];
                            html_table+= "</td>";
                        html_table+= "</tr>";
                    }
                html_table+= "</table>";
                $(".dublicateResult").html(html_table);
        }
    })
})
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
</script>
</body>
</html>