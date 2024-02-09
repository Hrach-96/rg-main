<?php
class action{
	public static function activation($uid,$mode){
		$mode = ($mode == 0 ) ? 1 : 0;
		return getwayConnect::getwaySend("UPDATE `user` SET `user_active` = '{$mode}' WHERE `id` = '{$uid}'");
	}
    public static function edit($uid,$data){
        if(is_array($data) && count($data) > 0) {
            $query = "";
            foreach ($data as $key => $value) {
                $query .= "`{$key}` = '$value',";
            }
            $query = trim($query,",");
            if(isset($_FILES['edit_image']) && $_FILES['edit_image']['tmp_name'] != ''){
                $target = "../user_images/";
                $targetFile = $target . $uid. ".". pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION);
                $types = ['jpeg', 'png', 'JPEG', 'jpg'];
                foreach($types as $type){
                    if(file_exists($target . $uid. ".". $type)){
                        unlink($target . $uid. ".". $type);
                    }
                }
                move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetFile);
            }
            return getwayConnect::getwaySend("UPDATE `user` SET {$query} WHERE `uid` = '{$uid}'");
        }else{
            return false;
        }
    }
	public static function remove($uid){
		//return getwayConnect::getwaySend("UPDATE `user` SET `user_active` = '{$mode}' WHERE `uid` = '{$uid}'");
	}
}
?>