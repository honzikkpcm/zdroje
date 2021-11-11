<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("php-mp4info/MP4Info.php");
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = '';
	$dbname = 'collager';
	$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	$maxWidth  = 1680;
	$maxHeight = 840;
	$imagesdom = "";
	$counter = 0; 
	$counterWidth = 0;
	$dir = './wall/';


	$folders = scandir($dir);
	$images = array();
	$images = array_merge($images + glob($dir . '*.{jpg,jpeg,png,gif,mp4}', GLOB_BRACE));

	foreach ($images as $image) {
		$extension = explode('.', $image);
		$extension = end($extension);

		if($extension != "mp4"){
			list($width, $height) = getimagesize($image);
		}else{
			$info = MP4Info::getInfo($image);
			if($info->hasVideo) {
			  echo $info->video->width . ' x ' . $info->video->height;
			}
		}

		$imageshort = str_replace("./wall/", "", $image);
		$mysqli->query("INSERT INTO wallfeed ".
		   "(filename,width,height, rank) "."VALUES ".
		   "('$imageshort','$width','$height', 10)");
	
	}
?>