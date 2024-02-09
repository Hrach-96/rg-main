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
$array_template = ['name','desc','type','lang','subject','template','active','order','messenger'];
$array_contact = ['name','content','lang','active','order'];
$array_footer = ['s','u','p','lang'];
$array_type = ['name','mfrom','mto'];
if(isset($_REQUEST['submit']) && strtolower($_REQUEST['submit']) == "save"){
    $action = "INSERT INTO";
    $result = '-1';
    $loc = "?templates=true&action={$result}";
    if(isset($_REQUEST['template_id'])){
        $action = ($_REQUEST['template_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        $_REQUEST['type'] = implode(",", $_REQUEST['type']);
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_template)){
				if($key == 'lang'){
					$value = strtolower($value);
				}
                $sets .= "`".$key."` = '".addslashes($value)."', ";
            }
        }
        $sets = trim($sets,', ');
        $sql = $action." `mail_content` SET {$sets}";
        if($_REQUEST['template_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['template_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['template_id'] > 0) ? $_REQUEST['template_id'] : $result;
        $loc = "?template={$result}";
    }else if(isset($_REQUEST['contact_id'])){
        $action = ($_REQUEST['contact_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_contact)){
                $sets .= "`".$key."` = '".addslashes($value)."', ";
            }
        }
        $sets = trim($sets,', ');
        $sql = $action." `mail_contacts` SET {$sets}";
        if($_REQUEST['contact_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['contact_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['contact_id'] > 0) ? $_REQUEST['contact_id'] : $result;
        $loc = "?contact={$result}";
    }else if(isset($_REQUEST['footer_id'])){
        $action = ($_REQUEST['footer_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_footer)){
                $sets .= "`".$key."` = '".addslashes($value)."', ";
            }
        }
        $sets = trim($sets,', ');
        $sql = $action." `mail_footer` SET {$sets}";
        if($_REQUEST['footer_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['footer_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['footer_id'] > 0) ? $_REQUEST['footer_id'] : $result;
        $loc = "?footer={$result}";
    }else if(isset($_REQUEST['type_id'])){
        $action = ($_REQUEST['type_id'] > 0) ? 'UPDATE' : $action;
        $sets = '';
        foreach ($_REQUEST as $key => $value){
            if(in_array($key,$array_type)){
                $sets .= "`".$key."` = '".addslashes($value)."', ";
            }
        }
        $sets = trim($sets,', ');
        $sql = $action." `mail_type` SET {$sets}";
        if($_REQUEST['type_id'] > 0){
            $sql .= " WHERE `id` = '{$_REQUEST['type_id']}'";
        }
        $result = getwayConnect::getwaySend($sql,true);
        $result = ($_REQUEST['type_id'] > 0) ? $_REQUEST['type_id'] : $result;
        $loc = "?type={$result}";
    }
    header("location:{$loc}&action={$result}");
}
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
                <a class="btn btn-default" href="?templates"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;TEMPLATES</a>
                <a class="btn btn-default" href="?show_mail_contacts=true"><span class="glyphicon glyphicon-send" aria-hidden="true"></span>&nbsp;CONTACTS</a>
                <a class="btn btn-default" href="?show_mail_footer=true"><span class="glyphicon glyphicon-circle-arrow-left" aria-hidden="true"></span>&nbsp;FOOTERS</a>
                <a class="btn btn-default" href="?show_mail_types=true"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;TYPES</a>
            </div>
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-default" href="?template=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD TEMPLATE</a>
                <a class="btn btn-default" href="?contact=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD CONTACT</a>
                <a class="btn btn-default" href="?footer=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD FOOTER</a>
                <a class="btn btn-default" href="?type=new"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> ADD TYPE</a>
            </div>
        </div>
        <div class="panel-body">
			<div id="loading" style="width:100%;text-align:center;"><img src="../../template/icons/loader.gif"></div>
            <?php
                if(isset($_REQUEST['template']) && ((int)$_REQUEST['template'] > 0 || $_REQUEST['template'] = 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_content` WHERE `id` = '{$_REQUEST['template']}'");

                    $types = getwayConnect::getwayData("SELECT * FROM `mail_type`");
                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    ?>
                    <form method="POST" name="template_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="template_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th><input class="form-control" type="text" name="name" id="name" value="<?=(isset($data['name'])) ? $data['name'] : ''?>" placeholder="name"/></th>
                            </tr>
                            <tr>
                                <th><label for="desc">Description</label></th>
                                <th><input class="form-control" type="text" name="desc" id="desc" value="<?=(isset($data['desc'])) ? $data['desc'] : ''?>" placeholder="description"/></th>
                            </tr>
                            <tr>
                                <th><label for="type">Type</label></th>
                                <th>
                                    <select class="btn btn-default" name="type[]" id="type" multiple>
                                        <option value="0">SELECT ONE</option>
                                    <?php
                                    if(is_array($types) && count($types) > 0){
                                        foreach ($types as $tvalue){
                                            $selected =(isset($data['type']) && (strpos($data['type'], "{$tvalue['id']}") !== false)) ? 'selected="selected"' : '';
                                            echo '<option value="'.$tvalue['id'].'" '.$selected.'>'.$tvalue['name'].'</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="lang">Language</label></th>
                                <th><input class="form-control" type="text" name="lang" id="lang" value="<?=(isset($data['lang'])) ? $data['lang'] : ''?>" placeholder="Language"/></th>
                            </tr>
                            <tr>
                                <th><label for="subject">Subject</label></th>
                                <th><input class="form-control" type="text" name="subject" id="subject" value="<?=(isset($data['subject'])) ? $data['subject'] : ''?>" placeholder="subject"/></th>
                            </tr>
                            <tr>
                                <th><label for="template">Template</label></th>
                                <th>
                                    <textarea name="template" id="template" class="htmlEditor">
                                        <?=(isset($data['template'])) ? $data['template'] : ''?>
                                    </textarea>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="messenger">Messenger</label></th>
                                <th>
                                    <textarea name="messenger" id="messenger"  cols="40" rows="8"><?=(isset($data['messenger'])) ? $data['messenger'] : ''?></textarea>
                                </th>
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
                                <th><label for="order">Order</label></th>
                                <th>
                                    <input name="order" id="order" class="order" value="<?=(isset($data['order'])) ? $data['order'] : '0.00'?>" />
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
                }else if(isset($_REQUEST['contact']) && ((int)$_REQUEST['contact'] > 0 || $_REQUEST['contact'] = 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_contacts` WHERE `id` = '{$_REQUEST['contact']}'");

                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    ?>
                    <form method="POST" name="template_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="contact_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th><input class="form-control" type="text" name="name" id="name" value="<?=(isset($data['name'])) ? $data['name'] : ''?>" placeholder="name"/></th>
                            </tr>
                            <tr>
                                <th><label for="content">Content</label></th>
                                <th>
                                    <textarea name="content" id="content" class="htmlEditor">
                                        <?=(isset($data['content'])) ? $data['content'] : ''?>
                                    </textarea>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="lang">Language</label></th>
                                <th><input class="form-control" type="text" name="lang" id="lang" value="<?=(isset($data['lang'])) ? $data['lang'] : ''?>" placeholder="Language"/></th>
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
                                <th><label for="order">Order</label></th>
                                <th><input class="form-control" type="text" name="order" id="order" value="<?=(isset($data['order'])) ? $data['order'] : '0.00'?>" placeholder="Order"/></th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="submit" value="SAVE" name="submit"  class="btn btn-default"/></th>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                    <?php
                }else if(isset($_REQUEST['footer']) && ((int)$_REQUEST['footer'] > 0 || $_REQUEST['footer'] = 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_footer` WHERE `id` = '{$_REQUEST['footer']}'");

                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    ?>
                    <form method="POST" name="template_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="footer_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="s">Subscribe</label></th>
                                <th>
                                    <textarea name="s" id="s" class="htmlEditor">
                                        <?=(isset($data['s'])) ? $data['s'] : ''?>
                                    </textarea>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="u">Unsubscribe</label></th>
                                <th>
                                    <textarea name="u" id="u" class="htmlEditor">
                                        <?=(isset($data['u'])) ? $data['u'] : ''?>
                                    </textarea>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="p">Promo</label></th>
                                <th>
                                    <textarea name="p" id="p" class="htmlEditor">
                                        <?=(isset($data['p'])) ? $data['p'] : ''?>
                                    </textarea>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="lang">Language</label></th>
                                <th><input class="form-control" type="text" name="lang" id="lang" value="<?=(isset($data['lang'])) ? $data['lang'] : ''?>" placeholder="Language"/></th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="submit" value="SAVE" name="submit"  class="btn btn-default"/></th>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                    <?php
                }else if(isset($_GET['type']) && ((int)$_GET['type'] > 0 || $_GET['type'] = 'new')){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_type` WHERE `id` = '{$_GET['type']}'");

                    $data = (is_array($data) && count($data) > 0) ? $data[0] : '';
                    ?>
                    <form method="POST" name="template_data">
                        <input type="hidden" value="<?=(isset($data['id'])) ? $data['id'] : 0 ?>" name="type_id" />
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th><?=(isset($data['id'])) ? $data['id'] : 'NEW'?></th>
                            </tr>
                            <tr>
                                <th><label for="name">Name</label></th>
                                <th>
                                    <input class="form-control" type="text" name="name" id="name" value="<?=(isset($data['name'])) ? $data['name'] : ''?>" placeholder="Name"/>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="mfrom">Mail from</label></th>
                                <th>
                                    <input class="form-control" type="text" name="mfrom" id="mfrom" value="<?=(isset($data['mfrom'])) ? $data['mfrom'] : ''?>" placeholder="Mail from"/>
                                </th>
                            </tr>
                            <tr>
                                <th><label for="mto">Mail to</label></th>
                                <th>
                                    <input class="form-control" type="text" name="mto" id="mto" value="<?=(isset($data['mto'])) ? $data['mto'] : ''?>" placeholder="Mail to"/>
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
                }else if(isset($_REQUEST['show_mail_footer'])){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_footer`");
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subscribe</th>
                                        <th>Unsubscribe</th>
                                        <th>Promo</th>
                                        <th>Language</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <th>{$value['id']}</th>
                                        <th><textarea readonly>{$value['s']}</textarea><br/>{$value['s']}</th>
                                        <th><textarea readonly>{$value['u']}</textarea><br/>{$value['u']}</th>
                                        <th><textarea readonly>{$value['p']}</textarea><br/>{$value['p']}</th>
                                        <th>{$value['lang']}</th>
                                        <th><a class=\"btn btn-default\" href=\"?footer={$value['id']}\"><span class=\"glyphicon glyphicon - pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                        </th>
                                    </tr>";
                        }
                    }
                    $table .= '</tbody>
                            </table>';
                    echo $table;
                }else if(isset($_REQUEST['show_mail_types'])){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_type` order by name");
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Mail From</th>
                                        <th>Mail TO</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <th>{$value['id']}</th>
                                        <th>{$value['name']}</th>
                                        <th>{$value['mfrom']}</th>
                                        <th>{$value['mto']}</th>
                                        <th><a class=\"btn btn-default\" href=\"?type={$value['id']}\"><span class=\"glyphicon glyphicon - pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                        </th>
                                    </tr>";
                        }
                    }
                    $table .= '</tbody>
                            </table>';
                    echo $table;
                }else if(isset($_REQUEST['show_mail_contacts'])){
                    $data = getwayConnect::getwayData("SELECT * FROM `mail_contacts` order by name");
                    // <tr> <th scope="row">1</th> <td>Mark</td> <td>Otto</td> <td>@mdo</td> </tr> <tr> <th scope="row">2</th> <td>Jacob</td> <td>Thornton</td> <td>@fat</td> </tr> <tr> <th scope="row">3</th> <td>Larry</td> <td>the Bird</td> <td>@twitter</td> </tr>
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Content</th>
                                        <th>L</th>
                                        <th>Active</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <th>{$value['id']}</th>
                                        <th>{$value['name']}</th>
                                        <th style='word-break: break-all;'>
                                        <div class='big_content'>
                                        <button class=\"read-more\">VIEW ".strlen($value['content'])."</button>
                                        <div class=\"text short\" style=\"max - width:100 %;display:none; \">{$value['content']}</div>
                                        </div>
                                        </th>
                                        <th>{$value['lang']}</th>
                                        <th>{$value['active']}</th>
                                        <th>{$value['order']}</th>
                                        <th><a class=\"btn btn-default\" href=\"?contact={$value['id']}\"><span class=\"glyphicon glyphicon - pencil\" aria-hidden=\"true\"></span> EDIT</a>
                                        </th>
                                    </tr>";
                        }
                    }
                    $table .= '</tbody>
                            </table>';
                    echo $table;
                }else{
                    $data = getwayConnect::getwayData("SELECT MC.* FROM `mail_content` AS MC ORDER BY MC.TYPE, MC.desc");
                    if(is_array($data) && count($data) > 0){
                        foreach($data as $key => $val){
                            $types = explode(",", $val['type']);
                            $data[$key]['t_name'] = '';
                            if(count($types) > 0){
                                foreach($types as $type){
                                    $type_name = getwayConnect::getwayData("SELECT `name` from mail_type where id = '{$type}'");
                                    if(isset($type_name) && !empty($type_name)){
                                        if(count($types) > 1){
                                            $data[$key]['t_name'] .= $type_name[0]['name']. ', ';
                                        } else {
                                            $data[$key]['t_name'] = $type_name[0]['name'];
                                        }
                                    }
                                }
                                $data[$key]['t_name'] = rtrim($data[$key]['t_name'], ',');
                            }
                        }
                    }
                    // <tr> <th scope="row">1</th> <td>Mark</td> <td>Otto</td> <td>@mdo</td> </tr> <tr> <th scope="row">2</th> <td>Jacob</td> <td>Thornton</td> <td>@fat</td> </tr> <tr> <th scope="row">3</th> <td>Larry</td> <td>the Bird</td> <td>@twitter</td> </tr>
                    $table = '<table class="table table-bordered">';
                    $table .= '<thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>L</th>
                                        <th>Template</th>
                                        <th>Subject</th>
                                        <th>Active</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    if(is_array($data) && count($data) > 0){
                        foreach ($data as $value){
                            $table .= "<tr>
                                        <td>{$value['id']}</td>
                                        <td>{$value['name']}</td>
                                        <td>{$value['t_name']}</td>
                                        <td>{$value['desc']}</td>
                                        <td>{$value['lang']}</td>
                                        <td style='word-break: break-all;'>
                                        <div class='big_content'>
                                        <button class=\"read-more\">VIEW ".strlen($value['template'])."</button>
                                        <div class=\"text short\" style=\"max-width:100%;display:none; \">{$value['template']} <hr> {$value['messenger']}</div>
                                        </div>
                                        </td>
                                        <td>{$value['subject']}</td>
                                        <td>{$value['active']}</td>
                                        <td>{$value['order']}</td>
                                        <td>
                                        <a class=\"btn btn-default\" href=\"?template={$value['id']}\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> EDIT</a>
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
