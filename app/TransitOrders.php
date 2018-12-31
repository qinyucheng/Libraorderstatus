<?php
date_default_timezone_set("America/Chicago");
set_time_limit(30);
include ('connection.php');
$ShipDate = date("Y-m-d", strtotime("-2 days"));
$array = array();

$result = mysqli_query($link, "SELECT * FROM orders o LEFT JOIN( SELECT * FROM barcodescaninfo WHERE barcode IS NOT NULL AND barcode != '' ) AS b ON o.TrackingNumber = b.barcode WHERE ( o.TrackingStatus = 'In-Transit' ) AND StoreType <> 'RMA' and ShipDate<'$ShipDate' and OrderStatus=0");
//$result = mysqli_query($link, "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where ShipDate>='$ShipDate' and o.TrackingStatus<>'In-Transit' and o.TrackingStatus<>'Delivered' ");

//echo "SELECT * FROM orders o LEFT join barcodescaninfo b on o.TrackingNumber=b.barcode where o.shipdate ='$ShipDate' order by ShipDate DESC ";
while ($row = mysqli_fetch_object($result))
	{
	$array[]=$row;
	}

echo json_encode($array);


?>