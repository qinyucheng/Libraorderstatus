<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');

$array = array();

$result = mysqli_query($link, "SELECT * FROM `notshipped_orders` ");


while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>