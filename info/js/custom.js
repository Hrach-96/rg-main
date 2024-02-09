$(document).ready(function(){
	$(document).on('click','.btn_add_post',function(){
		$(".addPostDiv").removeClass('d-none');
	})
	$(document).on('click','.addCommentToPost',function(){
		var commentText = $(this).parent().find('.commentText').val();
		var post_id = $(this).parent().find('.commentText').attr('data-post-id');
		$.ajax({
			type:'post',
			url:'controller.php',
			data:{addComment:true,commentText:commentText,post_id:post_id},
			success:function(res){
				alert('Comment Added SuccessFully!');
			}
		})
	})
	function ifTrueReturn1(val){
		if(val == true){
			return 1;
		}
		return 0;
	}
	$(document).on('change','#all',function(){
		var all = ifTrueReturn1($('#all')[0].checked);
		$("#operators").attr('checked',true);
		$("#drivers").attr('checked',true);
		$("#flourists").attr('checked',true);
		$("#hotel").attr('checked',true);
	})
	$(document).on('change','.allEdit',function(){
		var post_id = $(this).attr('data-post-id');
		var all = ifTrueReturn1($('.allEdit'+post_id)[0].checked);
		if(all == 1){
			$("#operators" + post_id).attr('checked',true);
			$("#drivers" + post_id).attr('checked',true);
			$("#flourists" + post_id).attr('checked',true);
			$("#hotel" + post_id).attr('checked',true);
		}
		else{
			$("#operators" + post_id).attr('checked',false);
			$("#drivers" + post_id).attr('checked',false);
			$("#flourists" + post_id).attr('checked',false);
			$("#hotel" + post_id).attr('checked',false);
		}
		console.log(all);
	})
	$(document).on('click','.btnRemoveImg',function(){
		if (confirm('Are you sure ?')) {
			var file = $(this);
			var file_id = $(file).attr('data-file-id');
			$.ajax({
				type:'post',
				url:'controller.php',
				data:{removeFile:true,file_id:file_id},
				success:function(res){
					$(file).parent().parent().remove();
				}
			})
		}
	})
	$(document).on('click','.removePost',function(){
		if (confirm('Are you sure ?')) {
			var post_id = $(this).attr('data-post-id');
			$.ajax({
				type:'post',
				url:'controller.php',
				data:{removePost:true,post_id:post_id},
				success:function(res){
					alert('Post Was Archived Successfully!')
					location.reload();
				}
			})
		}
	})
	$('form.addPostForm').submit(function(){ 
	    if (! $('#operators')[0].checked && !$('#drivers')[0].checked && !$('#flourists')[0].checked && !$('#hotel')[0].checked  ){
	    	alert('Please select who can view this post!');
	       return false;
	    }
	});
	$(document).on('click','.editPost',function(){
		$(this).hide();
		$(".post-title-html-" + post_id).remove();
		$(".post-content-html-"+post_id).remove();
		var htmlForm = "<form action='controller.php' method='post' enctype='multipart/form-data'><input type='hidden' value='true' name='updatePost'>";
		var post_id = $(this).attr('data-post-id');
		var title_of_post = $(this).parent().parent().find('.title_of_post');
		var flourist = $(this).parent().parent().find('.flourist_check').val();
		var operators = $(this).parent().parent().find('.operators_check').val();
		var driver = $(this).parent().parent().find('.driver_check').val();
		var hotel = $(this).parent().parent().find('.hotel_check').val();
		var flourist_check = '';
		var operators_check = '';
		var driver_check = '';
		var hotel_check = '';
		if(flourist == 1){
			flourist_check = 'checked';
		}
		if(operators == 1){
			operators_check = 'checked';
		}
		if(driver == 1){
			driver_check = 'checked';
		}
		if(hotel == 1){
			hotel_check = 'checked';
		}
		console.log(flourist_check,operators_check,driver_check,hotel_check);
		htmlForm+="<input type='checkbox' value='1' name='all' class ='allEdit allEdit"+ post_id +"' id ='allEdit"+ post_id +"' data-post-id='"+ post_id +"' class='pl-3 mt-4' style='margin-left:10px;margin-right:5px'><label for='allEdit"+ post_id +"'>All</label>"
		htmlForm+="<input type='checkbox' value='1' " + operators_check + " name='operators' id ='operators"+ post_id +"' class='pl-3 mt-4' style='margin-left:10px;margin-right:5px'><label for='operators"+ post_id +"'>Operators</label>"
		htmlForm+="<input type='checkbox' value='1' " + driver_check + " name='drivers' id ='drivers"+ post_id +"' class='pl-3 mt-4' style='margin-left:10px;margin-right:5px'><label for='drivers"+ post_id +"'>Drivers</label>"
		htmlForm+="<input type='checkbox' value='1' " + flourist_check + " name='flourists' id ='flourists"+ post_id +"' class='pl-3 mt-4' style='margin-left:10px;margin-right:5px'><label for='flourists"+ post_id +"'>Flourists</label>"
		htmlForm+="<input type='checkbox' value='1' " + hotel_check + " name='hotel' id ='hotel"+ post_id +"' class='pl-3 mt-4' style='margin-left:10px;margin-right:5px'><label for='hotel"+ post_id +"'>Hotel</label>"

		var title_of_post_html = $(title_of_post).html();
		$(title_of_post).remove();
		htmlForm+= "<input name='title' class='form-control editPostTitleHtml" + post_id + "' value='" + title_of_post_html + "'>";

		var content_of_post = $(this).parent().parent().find('.content_of_post');
		var content_of_post_html = $(content_of_post).html();
		$(content_of_post).remove();
		htmlForm+= "<div class='col-md-12'><textarea name='content' id='editPostContentHtml" + post_id + "' class='form-control mt-3 mb-3 editPostContentHtml" + post_id + "'>";
			htmlForm+= content_of_post_html
		htmlForm+= "</textarea></div>";
		htmlForm+= "<input type='hidden' name='post_id' value='" + post_id + "'>";
		htmlForm+= "<input type='file' class='form-control' name='postFiles[]' multiple>";
		htmlForm+= "<div class='col-md-12 mt-4 mb-4'><button class='btn btn-primary btnUpdatePost'>Save</button></div>";
		htmlForm+= "</form>";
		setTimeout(function(){
			tinymce.init({
			    selector: '#editPostContentHtml' + post_id,
			});
		},500)
		$(".updatePostDynamic-"+post_id).append(htmlForm);

	})
	$(document).on('click','.viewIcon',function(){
		var post_id = $(this).attr('data-post-id');
		var icon = $(this);
		$.ajax({
			type:'post',
			url:'controller.php',
			data:{addView:true,post_id:post_id},
			success:function(res){
				$(icon).after('<br>Շնորհակալություն, դուք հաստատեցիք, որ տեսել և հաստատել եք այս հայտարարությունը:');
				$(icon).remove();
				$(".title_post_" + post_id).removeClass('text-danger');
				console.log(res)
			}
		})
	})
	$(document).on('click','.viewHistoryIcon',function(){
		var post_id = $(this).attr('data-post-id');
		$.ajax({
			type:'post',
			url:'controller.php',
			data:{seeHistoryView:true,post_id:post_id},
			success:function(res){
				console.log(res);
				if(res.length > 5){
					res = JSON.parse(res);
					var html = '';
						html += '<ul>';
						for(var i = 0;i<res.length;i++){
							html += '<li>';
								html += res[i].username + ' - ' + res[i].date;
							html += '</li>';
						}
						html += '</ul>';

					$('.historyView'+post_id).html(html);
					console.log(res);
				}
			}
		})
	})
	$(document).on('click','.removeComment',function(){
		var comment_id = $(this).attr('data-comment-id');
		$.ajax({
			type:'post',
			url:'controller.php',
			data:{removeComment:true,comment_id:comment_id},
			success:function(res){
				alert('Comment Was Removed Successfully!')
				location.reload();
			}
		})
	})
	



})