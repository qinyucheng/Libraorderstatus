<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');
$TrackingNumber = stripslashes(trim($_POST['TrackingNumber']));


$array = array();

$result = mysqli_query($link, "SELECT * FROM orders o LEFT JOIN( SELECT * FROM barcodescaninfo WHERE barcode IS NOT NULL AND barcode != '' ) AS b ON o.TrackingNumber = b.barcode WHERE TrackingNumber = '$TrackingNumber'   ORDER BY ShipDate DESC ");
while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>