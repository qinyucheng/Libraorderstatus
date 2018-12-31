<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');

$ShipDate = date("Y-m-d");
$array = array();

$result = mysqli_query($link, "SELECT * FROM orders o LEFT JOIN( SELECT * FROM barcodescaninfo WHERE barcode IS NOT NULL AND barcode != '' ) AS b ON o.TrackingNumber = b.barcode WHERE ( o.TrackingStatus = 'Wating' OR o.TrackingStatus = 'Error' OR o.TrackingStatus = '' ) AND StoreType <> 'RMA' AND ShipDate > '$ShipDate' and OrderStatus=0 ORDER BY ShipDate DESC");
//$result = mysqli_query($link, "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where ShipDate>='$ShipDate' and o.TrackingStatus<>'In-Transit' and o.TrackingStatus<>'Delivered' ");

//echo "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where o.shipdate ='$ShipDate' order by ShipDate DESC ";
while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>