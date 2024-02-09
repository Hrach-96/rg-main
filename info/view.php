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
	if(strpos($logedUserInfo['user_level'], '222') !== false){
	}
	else{
    	header("location:../../index.php");
	}
	$from_date = '';
	$to_date = '';
	$website = 'all';
	$sender_country = 'all';
	if(isset($_GET['from_date'])){
		$from_date = $_GET['from_date'];
	}
	if(isset($_GET['to_date'])){
		$to_date = $_GET['to_date'];
	}
	if(isset($_GET['website'])){
		$website = $_GET['website'];
	}
	if(isset($_GET['sender_country'])){
		$sender_country = $_GET['sender_country'];
	}
	$orders = $database->getOrders($from_date,$to_date,$website,$sender_country);
	$regions = $database->getRegions();
	$website_array = [
		'18' => 'flowers-armenia.am',
		'2' => 'flowers-armenia.com',
	];
	if($_REQUEST){
		if(isset($_POST)){
			if($_POST['logout_function'] == true){
				$past = time() - 3600;
				foreach ( $_COOKIE as $key => $value )
				{
				    setcookie( $key, $value, $past, '/' );
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>View</title>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
</head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<body>
	<div class='col-10 offset-1 pt-3'>
		<form>
			<div class='row'>
				<div class='col-md-2'>
					<label for='from_date'>Start Date</label>
					<input name='from_date' value="<?=(!empty($from_date)? $from_date : '')?>" id='from_date' class='form-control' type='date'>
				</div>
				<div class='col-md-2'>
					<label for='to_date'>End Date</label>
					<input name='to_date' value="<?=(!empty($to_date)? $to_date : '')?>" id='to_date' class='form-control' type='date'>
				</div>
				<div class='col-md-2'>
					<label for='to_date'>Website</label>
					<select class='form-control' name='website'>
						<option value='all'>All</option>
						<?php
							foreach($website_array as $key=>$value){
								?>
									<option <?php echo ($website == $key)? 'selected':'' ?> value="<?php echo $key ?>"><?php echo $value ?></option>
								<?php
							}
						?>
					</select>
				</div>
				<div class='col-md-2'>
					<label for='to_date'>Sender Country</label>
					<select class='form-control' name='sender_country'>
						<option value='all'>All</option>
						<?php
							foreach($regions as $key=>$value){
								?>
									<option  <?php echo ($sender_country == $value['id'])? 'selected':'' ?> value="<?php echo $value['id'] ?>"><?php echo $value['name_am'] ?></option>
								<?php
							}
						?>
					</select>
				</div>
				<div class='col-md-1'>
					<label for='search'>Search</label>
					<button type='submit' id='search' class='btn btn-primary'>Search</button>
				</div>
				<div class='col-md-1 float-right'>
					<button type='button' id='search' class='btn btn-danger logoutBtn'>Log Out</button>
				</div>
			</div>
		</form>
	</div>
	<div class='col-10 offset-1 mt-5'>
		<table id="example" class="table table-striped table-bordered">
		  <thead>
		    <tr>
		      <th>Order ID</th>
		      <th>Website</th>
		      <th>Created Date</th>
		      <th>Referral</th>
		      <th>keywords</th>
		    </tr>
		  </thead>
		  <tbody>
		  	<?php
		  		if(!empty($_GET['from_date']) || !empty($_GET['to_date'])){
			  		foreach($orders as $key=>$value){
			  			$referral = $database->getReferral($value['id']);
			  			if($referral){
			  				$referral = $referral[0]['value'];
			  			}
			  			else{
			  				$referral = '-';
			  			}
			  			?>
						  	<tr>
						      <th scope="row"><?php echo $value['id'] ?></th>
						      <th><?php echo $website_array[$value['sell_point']] ?></th>
						      <td><?php echo $value['created_date'] ?></td>
						      <td><?php echo $referral; ?></td>
						      <td></td>
						    </tr>
			  			<?php
			  		}
			  	}
		  	?>
		  </tbody>
		</table>
	</div>
</body>
<?php
  @include('includes/scripts.php');
?>
  
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>

<script type="text/javascript">
	$(document).ready(function () {
	    $('#example').DataTable({
	        pagingType: 'full_numbers',
	    });
	    $(document).on('click','.logoutBtn',function(){
	    	$.ajax({
	    		url: location.href,
		        type: 'post',
		        data: {
		            logout_function: true,
		        },
		        success: function(resp){
		            location.reload();
		        }
	    	})
	    })
	});
</script>
</html>