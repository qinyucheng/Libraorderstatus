<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');
$ShipDate = date("Y-m-d", strtotime("-2 days"));
$array = array();

$result = mysqli_query($link, "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where o.TrackingStatus<>'Delivered' order by ShipDate DESC ");
//$result = mysqli_query($link, "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where ShipDate>='$ShipDate' and o.TrackingStatus<>'In-Transit' and o.TrackingStatus<>'Delivered' ");


while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>