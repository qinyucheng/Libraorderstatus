<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');
$start_date = stripslashes(trim($_POST['startDate']));
$end_date = stripslashes(trim($_POST['endDate']));

$array = array();

//$result = mysqli_query($link, "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where shipdate >= '$start_date' AND shipdate <= '$end_date'   order by ShipDate DESC ");
$result = mysqli_query($link, "SELECT * FROM orders o LEFT JOIN( SELECT * FROM barcodescaninfo WHERE barcode IS NOT NULL AND barcode != '' ) AS b ON o.TrackingNumber = b.barcode WHERE shipdate >= '$start_date' AND shipdate <= '$end_date'   order by ShipDate DESC  ");
while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>