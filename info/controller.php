<?php
	session_start();
	@include('database.php');
	$database = new Database();
	if(isset($_SERVER['REQUEST_METHOD']) == "POST"){
		$logedUserInfo = $database->getRowByColumnEqual('user','uid',$_COOKIE['suid'])[0];
		if(isset($_POST['addPost'])){
			$post_id = $database->addPost($_POST,$logedUserInfo['id']);
			$total_count = count($_FILES['postFiles']['name']);
			if($total_count > 0){
				for( $i=0 ; $i < $total_count ; $i++ ) {
				   	$tmpFilePath = $_FILES['postFiles']['tmp_name'][$i];
				   	if ($tmpFilePath != ""){
				   		if(strpos($_FILES['postFiles']['name'][$i], '.jpg') !== false ||strpos($_FILES['postFiles']['name'][$i], '.png') !== false ||strpos($_FILES['postFiles']['name'][$i], '.jpeg') !== false ){
	 						$randomName = substr(md5(mt_rand()), 0, 8);
				   			$array = explode('.', $_FILES['postFiles']['name'][$i]);
							$extension = end($array);
						    // $newFilePath = "images/post_images/" . $_FILES['postFiles']['name'][$i];
						    $newFilePath = "images/post_images/" . $randomName . "." . strtolower($extension);
						    move_uploaded_file($tmpFilePath, $newFilePath);
						    $database->addFileToPost($post_id,$randomName . "." . strtolower($extension));
						    // $database->addFileToPost($post_id,$_FILES['postFiles']['name'][$i]);
				   		}
				   		else{
						    $newFilePath = "images/post_images/" . strtolower($_FILES['postFiles']['name'][$i]);
						    move_uploaded_file($tmpFilePath, $newFilePath);
						    $database->addFileToPost($post_id,strtolower($_FILES['postFiles']['name'][$i]));
				   		}
				   	}
				}
			}
			header("Location: /info");exit;
		}
		if(isset($_POST['addComment'])){
			$database->addComment($_POST,$logedUserInfo['id']);
			return true;
		}
		if(isset($_POST['removeFile'])){
			$fileName = $database->getRowByColumnEqual('info_post_files','id',$_POST['file_id']);
			$database->removeFromTable('id','info_post_files',$_POST['file_id']);
			unlink('images/post_images/' . $fileName[0]['file_name']);
			return true;
		}
		if(isset($_POST['removePost'])){
			$database->removePost($_POST);
			return true;
		}
		if(isset($_POST['removeComment'])){
			$database->removeComment($_POST);
			return true;
		}
		if(isset($_POST['updatePost'])){
			$database->updatePost($_POST);
			$total_count = count($_FILES['postFiles']['name']);
			if($total_count > 0){
				for( $i=0 ; $i < $total_count ; $i++ ) {
				   	$tmpFilePath = $_FILES['postFiles']['tmp_name'][$i];
				   	if ($tmpFilePath != ""){
				   		if(strpos($_FILES['postFiles']['name'][$i], '.jpg') !== false ||strpos($_FILES['postFiles']['name'][$i], '.png') !== false ||strpos($_FILES['postFiles']['name'][$i], '.jpeg') !== false ){
	 						$randomName = substr(md5(mt_rand()), 0, 8);
				   			$array = explode('.', $_FILES['postFiles']['name'][$i]);
							$extension = end($array);
						    // $newFilePath = "images/post_images/" . $_FILES['postFiles']['name'][$i];
						    $newFilePath = "images/post_images/" . $randomName . "." . strtolower($extension);
						    move_uploaded_file($tmpFilePath, $newFilePath);
						    $database->addFileToPost($_POST['post_id'],$randomName . "." . strtolower($extension));
						    // $database->addFileToPost($_POST['post_id'],$_FILES['postFiles']['name'][$i]);
				   		}
				   		else{
						    $newFilePath = "images/post_images/" . strtolower($_FILES['postFiles']['name'][$i]);
						    move_uploaded_file($tmpFilePath, $newFilePath);
						    $database->addFileToPost($_POST['post_id'],strtolower($_FILES['postFiles']['name'][$i]));
				   		}
				   	}
				}
			}
			header("Location: /info");exit;
		}
		if(isset($_POST['addView'])){
			$database->addView($_POST,$logedUserInfo['id']);
			return true;
		}
		if(isset($_POST['seeHistoryView'])){
			$history = $database->viewHistory($_POST['post_id']);
			print json_encode($history);die;
		}
	}