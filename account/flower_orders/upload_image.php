<?php
session_start();
$pageName = "flowers";
$rootF = "../..";
include($rootF."/apay/pay.api.php");

include($rootF."/configuration.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
	//page::accessByLevel($level[0]["user_level"],$pageName);
	$levelArray = explode(",",$level[0]["user_level"]);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
}
function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}
if(!empty($_FILES)){
    
    $files = reArrayFiles($_FILES['file']);
    if(isset($_REQUEST['order_id'])){
		$orderInfo = getwayConnect::getwayData("SELECT * FROM rg_orders where id='{$_REQUEST['order_id']}'");
		$created_date = explode('-',$orderInfo[0]['created_date']);
    }
    else{
		$current_date = date('Y-m');
		$created_date = explode('-',$current_date);
    }
	$path =  $created_date[1] . '-' . substr($created_date[0], 2, 2) ;
	if (!is_dir('product_images/' . $path)) {
	    mkdir('product_images/' . $path, 0777, true);
	}
    $targetDir = "./product_images/" . $path . "/";
    $id = '';
    foreach ($files as $value) {
	    $fileName = $value['name'];
	    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
	    $uniqi = uniqid();
	    $gen_file_name = $uniqi.".".$ext;
	    $targetFile = $targetDir.$gen_file_name;
	    
	    if(move_uploaded_file($value['tmp_name'],$targetFile)){
	    	header('Content-type: text/json');
	    	header('Content-type:application/json');
	        exit(json_encode(array("name"=>$gen_file_name,"id"=>$uniqi)));
	    }
    }
}
if(isset($_REQUEST['remove'])){
	$orderInfo = getwayConnect::getwayData("SELECT * FROM rg_orders where id='{$_REQUEST['order_id']}'");
    $created_date = explode('-',$orderInfo[0]['created_date']);
	$path =  $created_date[1] . '-' . substr($created_date[0], 2, 2) ;
	if(file_exists("product_images/".$path."/".$_REQUEST['remove'])){
		unlink("product_images/".$path."/".$_REQUEST['remove']);
		getwayConnect::getwaySend("DELETE FROM `delivery_images` WHERE `image_source` = '{$_REQUEST['remove']}';");
	}
}
if(isset($_REQUEST['order_id'])){
	$result  = array();
	$orderInfo = getwayConnect::getwayData("SELECT * FROM rg_orders where id='{$_REQUEST['order_id']}'");
    $created_date = explode('-',$orderInfo[0]['created_date']);
	$path =  $created_date[1] . '-' . substr($created_date[0], 2, 2) ;
	$storeFolder = "product_images/" . $path . '/';
 	if($data = getwayConnect::getwayData("SELECT * FROM `delivery_images` WHERE `rg_order_id` = '{$_REQUEST['order_id']}';")){             //1
		if (is_array($data)) {
			$files = $data;
		    foreach ( $files as $file ) {
		        if (is_file($storeFolder.$file['image_source'])) {       //2
		            $obj['name'] = $file['image_source'];
		            $obj['size'] = filesize($storeFolder.$file['image_source']);
		            $obj['note'] = $file['image_note'];
		            $obj['price'] = $file['price'];
		            $obj['path'] = $path;
		            $obj['product_desc'] = $file['product_desc'];
					// Added By Dev for xml asop52f41v78x8z5
		            $obj['tax_quantity'] = $file['tax_quantity'];
		            $obj['tax_price_amd'] = $file['tax_price_amd'];
		            $obj['tax_account_id'] = $file['tax_account_id'];
		            // end asop52f41v78x8z5
		            $result[] = $obj;
		        }
		    }
		}
	}
    header('Content-type: text/json');              //3
    header('Content-type: application/json');
    echo json_encode($result);
}
?>