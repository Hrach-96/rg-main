<?php
set_time_limit(0);
$i=0;
while(true)
{
	$i++;
	if($i == 1000)
	{
		echo "exit::{$i}";
		exit;
	}
}
?>