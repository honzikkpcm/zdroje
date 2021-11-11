<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = '';
	$dbname = 'collager';
	$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);



	$typeid = $_POST["typeid"];
	$fileid = $_POST["fileid"];
	$userid = $_POST["userid"];

	if($typeid == "+"){
		$sql = "UPDATE wallfeed SET rank = rank+1 WHERE idecko = '$fileid'";
		$result = $mysqli->query($sql);
		$sql = "INSERT INTO liked (fileid, userid, dateLiked) VALUES ('$fileid', '$userid', NOW())";
		$result = $mysqli->query($sql);
	}
	
	if($typeid == "-"){
		$sql = "UPDATE wallfeed SET rank = rank-1 WHERE idecko = '$fileid'";
	}
?>