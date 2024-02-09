<!DOCTYPE HTML>
<?php
	session_start();
	@include('database.php');
	$database = new Database();

	@include('includes/styles.php');
	if(!$_COOKIE['suid']){
		header("Location: http://new.regard-group.ru/login/");
	}
	$logedUserInfo = $database->getRowByColumnEqual('user','uid',$_COOKIE['suid'])[0];
	$userPosition = $database->getUserPosition($logedUserInfo);
 ?>
<html>
<head>
	<title>RG Annoncment Board</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<body>
	<div class="container mt-5 mb-5">
	    <div class="d-flex justify-content-center row">
	        <div class="d-flex flex-column col-md-8">
				<button class='btn btn-primary w-25 btn_add_post'>Add Post</button>
				<div class='col-12 addPostDiv d-none'>
					<form action='controller.php' class='addPostForm' method='post' enctype="multipart/form-data">
						<input type='hidden' value='true' name='addPost'>
						<input type='checkbox' value='1' name='all' id ='all' class='pl-3 mt-4' style='margin-left:10px'>
						<label for='all'>All</label>
						<input type='checkbox' value='1' name='operators' id ='operators' class='pl-3 mt-4' style='margin-left:10px'>
						<label for='operators'>Operators</label>
						<input type='checkbox' value='1' name='drivers' id ='drivers' class='pl-3 mt-4' style='margin-left:10px'>
						<label for='drivers'>Drivers</label>
						<input type='checkbox' value='1' name='flourists' id ='flourists' class='pl-3 mt-4' style='margin-left:10px'>
						<label for='flourists'>Flourists</label>
						<input type='checkbox' value='1' name='hotel' id ='hotel' class='pl-3 mt-4' style='margin-left:10px'>
						<label for='hotel'>Hotel</label>
						<input type='text' required class='form-control addPostTitle mt-4' name='addPostTitle' placeholder='Title'>
						<textarea class="form-control mt-3 addPostTextarea" id='description' name='description' placeholder='Description'></textarea>
						<input type='file' class='form-control' name='postFiles[]' multiple>
						<button class="btn btn-success mt-4 publishPost">Publish</button> 
					</form>
				</div>
			</div>
			<?php
				foreach($database->getAllRowsOrderByModifyDate('info_posts',$userPosition) as $post){
					$postFiles = $database->getPostFiles($post['id']);
					$ownerPost = false;
					if($logedUserInfo['id'] == $post['user_id']){
						$ownerPost = true;
					}
					$display_post = false;
					foreach($userPosition as $value){
						if($post[$value] == 1){
							$display_post = true;
						}
					}
					if($display_post){
						$postUserInfo = $database->getRowByColumnEqual('user','id',$post['user_id'])[0];
						$postUserfullName = $postUserInfo['username'];
						if($postUserInfo['full_name_am']){
							$postUserfullName = $postUserInfo['full_name_am'];
						}
						?>
						<div class="d-flex flex-column col-md-8 border mt-2 shadow-lg pb-3"> 
							<div class='row'>
								<div class='col-md-3'>
									Հեղինակ: <?php echo $postUserfullName ?>
								</div>
								<?php
									if($ownerPost){
										?>
										<div style='color:green' class='editPost hoverClassEffect col-md-2' data-post-id="<?php echo $post['id'] ?>">Edit Post</div>
										<div style='color:red' class='removePost hoverClassEffect col-md-2' data-post-id="<?php echo $post['id'] ?>">Archive Post</div>
										<?php
									}
									else if($logedUserInfo['id'] == 4){
										?>
										<div style='color:red' class='removePost hoverClassEffect col-md-2' data-post-id="<?php echo $post['id'] ?>">Archive Post</div>
										<?php
									}
								?>
								</div>
				            <div class="d-flex flex-row align-items-center text-left comment-top p-3 border-bottom px-4">
				                <!-- <div class="profile-image">
				                	<img class="rounded-circle" src="https://i.imgur.com/t9toMAQ.jpg" width="70">
				                </div> -->

				                <div class="d-flex flex-column-reverse flex-grow-0 align-items-center votings ml-1">
				                	<i class="fa fa-sort-up fa-2x hit-voting"></i>
				                	<i class="fa fa-sort-down fa-2x hit-voting"></i>
				                </div>
					                    
				                <div class="d-flex flex-column ml-3 col-md-12">
				                	<div class="updatePostDynamic-<?php echo $post['id'] ?>"></div>
				                    <div class="d-flex flex-row post-title-html-<?php echo $post['id'] ?> post-title">
				                        <h3 class='title_of_post title_post_<?php echo $post['id'] ?> <?php echo ($database->checkIfView($logedUserInfo['id'],$post['id']) == false && $post['user_id'] != $logedUserInfo['id']) ? 'text-danger' : '' ?>'>
				                        	<?php
				                        		if(strlen($post['title']) > 0){    
				                        			echo $post['title'];
				                        		}
				                        		else{
				                        			echo "Հայտարարություն։";
				                        		}
				                        	?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; №-<?=$post['id'];?>
				                        </h3>
				                        <input type='hidden' class='flourist_check' value="<?php echo $post['flourist']?>">
				                        <input type='hidden' class='operators_check' value="<?php echo $post['operators']?>">
				                        <input type='hidden' class='driver_check' value="<?php echo $post['driver']?>">
				                        <input type='hidden' class='hotel_check' value="<?php echo $post['hotel']?>">
				                    </div>
				                    <div class="post-content-html-<?php echo $post['id'] ?> post-title">
				                        <span class='content_of_post'>
				                        	<?php
				                        		$view_date = '';
				                        		echo $post['content'];
				                        		if($post['user_id'] != $logedUserInfo['id']){
				                        			if($database->checkIfView($logedUserInfo['id'],$post['id']) == false){
					                        			?>
					                        				<img title='Հաստատում եմ, որ տեսել և հասկացել եմ այս հայտարարությունը' class='viewIcon hoverClassEffect' data-post-id="<?php echo $post['id']?>" src='images/viewed.png' style='width:45px;margin-left:15px'>
					                        			<?php
				                        			}
				                        			else{
				                        				$view_date = 'Հաստատված։ ' .  date('F d h:i a',strtotime($database->checkIfView($logedUserInfo['id'],$post['id'])['date']));
				                        			}
				                        		}
				                        	?>		
				                        </span>
				                    </div>
				                    <div class="d-flex flex-row align-items-center align-content-center post-title">
				                    	<span title="<?php echo $view_date ?>"><?php echo date('d F h:i a',strtotime($post['modify_date']));  ?></span>
				                    </div>
			                        <img title='Տեսնել թե ովքերեն հաստատել' class='viewHistoryIcon hoverClassEffect' data-post-id="<?php echo $post['id']?>" src='images/viewed.png' style='width:25px;margin-left:15px'>
			                        <div class="historyView<?php echo $post['id'] ?>"></div>
				                    <div class='row mt-5 text-center'>
									<?php
				                    	if(count($postFiles) > 0){
				                    		foreach($postFiles as $file){
				                    			if(strpos($file['file_name'], '.jpg') !== false ||strpos($file['file_name'], '.png') !== false ||strpos($file['file_name'], '.jpeg') !== false ){
				                    				?>
				                    					<div class='col-md-3'>
				                    						<div class='col-md-12 mb-3'>
					                    						<?php
					                    							if($ownerPost){
					                    								?>
					                    									<button class='rounded-circle btn btn-danger btnRemoveImg' data-file-id="<?php echo $file['id']?>">X</button>
					                    								<?php
					                    							}
					                    						?>
				                    						</div>
				                    						<a target="_blank" href="images/post_images/<?php echo $file['file_name'] ?>" class='postImages'>
				                    							<img src="images/post_images/<?php echo $file['file_name'] ?>" class='postImages'>
				                    						</a>
				                    					</div>
				                    				<?php
				                    			}
				                    			else{
				                    				?>
				                    					<div class='col-md-3'>
				                    						<div class='col-md-12 mb-3'>
					                    						<?php
					                    							if($ownerPost){
					                    								?>
					                    									<button class='rounded-circle btn btn-danger btnRemoveImg' data-file-id="<?php echo $file['id']?>">X</button>
					                    								<?php
					                    							}
					                    						?>
				                    						</div>
				                    						<a target="_blank" href="images/post_images/<?php echo $file['file_name'] ?>" class='postImages'><?php echo $file['file_name'] ?></a>
				                    					</div>
				                    				<?php
				                    			}

				                    		}
				                    	}
				                    ?>
								</div>
				                </div>
				            </div>
				            <div class="coment-bottom bg-white p-2 px-4">
				                <div class="d-flex flex-row add-comment-section mt-4 mb-4">
				                	<!-- <img class="img-fluid img-responsive rounded-circle mr-2" src="https://i.imgur.com/qdiP4DB.jpg" width="38"> -->
				                	<input type="text" class="form-control mr-3 commentText" data-post-id="<?php echo $post['id'] ?>" placeholder="Add comment">
				                	<button class="btn btn-primary addCommentToPost" type="button">Add</button>
				                </div>
				                <?php
				                	foreach($database->getAllRowsOrderByModifyDateValue('info_post_comments','post_id',$post['id']) as $comment){
										$commentUserInfo = $database->getRowByColumnEqual('user','id',$comment['user_id'])[0];
										$commentUserfullName = $postUserInfo['username'];
										if($commentUserInfo['full_name_am']){
											$commentUserfullName = $commentUserInfo['full_name_am'];
										}
										$ownerComment = false;
										if($comment['user_id'] == $logedUserInfo['id']){
											$ownerComment = true;
										}
				                		?>
				                			Comments
				                			<div class="commented-section mt-2 border p-3">
							                    <div class="d-flex flex-row align-items-center commented-user">
							                    	<span class="dot mb-1"></span>
							                    	<h5 style='margin-left:10px'><?php echo $commentUserfullName?></h5>
							                    </div>
							                    <?php
													if($ownerComment){
														?>
															<div style='color:red' data-comment-id="<?php echo $comment['id'] ?>" class='removeComment hoverClassEffect' data-post-id="<?php echo $post['id'] ?>">Remove Comment</div>
														<?php
													}
												?>
							                    <div class="comment-text-sm">
							                    	<span><?php echo $comment['content'] ?></span>
							                    </div>
							                    <span class="mb-1 text-muted h6 small" ><?php echo date('F d h:i a',strtotime($comment['modify_date']));  ?></span>
							        		</div>
				                		<?php
				                	}
				                ?>
				    		</div>
				    	</div> 
						<?php
					}
				}
			?>
	    </div>
	</div>
<?php
  @include('includes/scripts.php');
?>
<script>
  tinymce.init({
    selector: '#description',
  });
	function ifTrueReturn1(val){
		if(val == true){
			return 1;
		}
		return 0;
	}
 //  $(document).on('click','.publishPost',function(){
	// 	var addPostTitle = $(".addPostTitle").val();
	// 	var operators = ifTrueReturn1($('#operators')[0].checked);
	// 	var drivers = ifTrueReturn1($('#drivers')[0].checked);
	// 	var flourists = ifTrueReturn1($('#flourists')[0].checked);
	// 	var addPostTextarea = tinymce.get("description").getContent();
	// 	var hotel = ifTrueReturn1($('#hotel')[0].checked);

	// 	var fd = new FormData();
 //        var files = $('#postFiles')[0].files;
 //       	fd.append('file',files[0]);
 //       	console.log(fd);

	// 	if(addPostTextarea.length > 0){
	// 		$.ajax({
	// 			type:'post',
	// 			url:'controller.php',
	// 			data:{addPost:true,addPostTextarea:addPostTextarea,addPostTitle:addPostTitle,operators:operators,drivers:drivers,flourists:flourists,hotel:hotel},
	// 			success:function(res){
	// 				$(".addPostDiv").addClass('d-none');
	// 				// alert('Success!');
	// 				// location.reload();
	// 			}
	// 		})
	// 	}
	// 	else{
	// 		alert('Empty Post');
	// 	}
	// })
</script>
</body>
</html>