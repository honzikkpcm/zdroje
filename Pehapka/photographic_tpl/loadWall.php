<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("php-mp4info/MP4Info.php");
	require_once("resize_image.php");
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = '';
	$dbname = 'collager';
	$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	$dir = './wall/';
	$counterWidth = 0;	
	$imagesdom = "<li class='selected'></li>";	


	if($_GET["typeid"] == "liked"){
		$sql = "SELECT * FROM wallfeed LEFT JOIN liked ON wallfeed.idecko = liked.fileid WHERE liked.userid = 'PrdelniPrincezna' ORDER BY liked.dateLiked DESC LIMIT 500";
	}else{
		$sql = "SELECT * FROM wallfeed AS t1 JOIN (SELECT idecko FROM wallfeed ORDER BY RAND() LIMIT 1000) as t2 ON t1.idecko=t2.idecko LIMIT 50"; 
	}
	
	$arr = [];
	$counter = 0; 
	$idecka = ""; 	   

	$result = $mysqli->query($sql);

	if ( !empty($result->num_rows) && $result->num_rows > 0) { 
		while($row = $result->fetch_assoc()) {
			$idecko = $row["idecko"];
			$origWidth = (int)$row["width"];
			$origHeight = (int)$row["height"];
			$filename = $row["filename"];

			$maxWidth = (int)$_GET["maxWidth"];
			$maxHeight = (int)$_GET["maxHeight"];

			$resized = resizeTo($origWidth, $origHeight, $maxWidth, $maxHeight, $dir.$filename);
			$row["filename"] = $dir.$row["filename"];
			$row["width"] = $resized[0];
			$row["height"] = $resized[1];


			$arr[] = $row;
			$counter++; 
		}

		$json_array = json_encode($arr);
        echo $json_array;
    }else{
    	echo "0 results";
    }
?>
		