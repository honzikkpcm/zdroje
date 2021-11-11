<?php

 // @extract($_POST);
	$regdate = date("d.n.Y v H:i");   
	$date = mysql_escape_string($_POST["datum"]);
	$dateTime = new DateTime($date);
	$formatted_date=date_format ( $dateTime, 'Y-m-d' );  
	mysql_connect("siris.zarea.net", "creativenow", "fEKnBUCy") or header("Location: regfalse.html");
	mysql_select_db("creativenow") or die(mysql_error());
	mysql_query("SET CHARACTER SET utf8");
	$sql ="INSERT INTO can_cashback2012 (rname,rsurname,rstreet,rcity,rtelefon,remail,rdatebuy,rsernum,rreseller,ruploaded,date,revent,lang,raccept,rproduct,rucet,rbank)
	VALUES ('".mysql_escape_string($_POST["jmeno"])."','".mysql_escape_string($_POST["prijmeni"])."','".mysql_escape_string($_POST["ulice"])."','".mysql_escape_string($_POST["mesto"])."','".mysql_escape_string($_POST["telefon"])."','".mysql_escape_string($_POST["email"])."','".$formatted_date."','".mysql_escape_string($_POST["cislo"])."','".mysql_escape_string($_POST["prodejce"])."','".mysql_escape_string($_POST["filefinal"])."',NOW(),'EOS Cashback 2012','SK','1','".mysql_escape_string($_POST["produkt"])."','".mysql_escape_string($_POST["ucet"])."','".mysql_escape_string($_POST["banka"])."')";
	
	mysql_query( $sql);
    echo mysql_error();
   
	$from = "robot@canon.cz";
	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/plain; charset=utf-8\n";
	$headers .= "From: ".$from." \r\n";
	$headers .= "Return-Path: pr@creativedreams.cz \n";	
	mail(mysql_escape_string($_POST["email"]), "Canon EOS Cashback 2012", "Dobrý den, registrace Vašeho nákupu byla přijata, v případě nesrovnalostí Vás budeme kontaktovat.", $headers);    
?>