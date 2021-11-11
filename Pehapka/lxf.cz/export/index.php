<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function make_safe($string) {
	$string = preg_replace('#<!\[CDATA\[.*?\]\]>#s', '', $string);
	$string = strip_tags($string);
	// The next line requires PHP 5.2.3, unfortunately.
	//$string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
	// Instead, use this set of replacements in older versions of PHP.
	$string = str_replace('<', '&lt;', $string);
	$string = str_replace('>', '&gt;', $string);
	$string = str_replace('(', '&#40;', $string);
	$string = str_replace(')', '&#41;', $string);
	$string = str_replace('"', '&quot;', $string);
	$string = str_replace('\'', '&#039;', $string);
	return $string;
}

// Saving parsed and proceeded data as CSV again
function save_csv($array, $filename = "export.csv", $delimiter=";") {
	$f = fopen('php://memory', 'w'); 
	foreach ($array as $line) { 
		// Kontrola zda neposilam do csv nejakej shit
		if (is_array($line)){
		    fputcsv($f, $line, $delimiter); 
		}
	}
	fseek($f, 0);
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'";');
	fpassthru($f);
}


function find_key_value($array, $key, $val)
{
    foreach ($array as $item)
    {
        if (is_array($item) && find_key_value($item, $key, $val)) return true;
        if (isset($item[$key]) && $item[$key] == $val) return true;
    }

    return false;
}

$supplier = $_GET["supplier"];
$format = $_GET["format"];

if(isset($supplier) && isset($format)){
	
	include_once 'db-config.php';
	$db              =    	new DBController();
	$conn            =    	$db->connect();

	switch ($supplier) {
		case "Alumia":
			$sql = "SELECT * FROM alumia LEFT JOIN alumia ON alumialxf.realSKU = alumia.id ORDER BY pairCode DESC, code; ";
			$results = $conn->query($sql);
			$counter = 0;
			if ($results->num_rows > 0) {
				while($row = $results->fetch_assoc()) {
					$counter++;
					$updateSKU = false;
					$priceNew = 0;
					$purchasePriceNew = 0;
					/*
					$dostupnost = "neznámá";
					$idecko = $row["product_id"];
					$central = preg_replace('~\D~', '', $row["central_stock"]);
					$luxifer = preg_replace('~\D~', '', $row["luxifer_stock"]);

					if(($central >= 1) && ($luxifer <= 0)) { $dostupnost = "Na objednávku do 10 dní"; }
					if(($central == 0) && ($luxifer >= 1)) { $dostupnost = "Skladem"; }
					if(($central == 0) && ($luxifer <= 0)) { $dostupnost = "Na objednávku do 40 dní"; }
					if(($central >= 1) && ($luxifer > 1)) { $dostupnost = "Skladem"; }
					*/

					$pricelistMultiply = array(
						array("B6367LL9016-3", "B6367LL9016|1m", 3),
						array("1397-10M", "1397", 10),
						array("1397-3M", "1397", 3),
						array("1397-5M", "1397", 5),
						array("66", "1397", 1),
						array("00036-3", "00036|1m", 1),
						array("70499-1", "70499", 1),
						array("70499-2", "70499", 2),
						array("70499-3", "70499", 3),
						array("70499-5", "70499", 5),
						array("70499-10", "70499", 10),
						array("17059-1-5", "17059|3m", 0.5),
						array("70240-1M", "70240", 1),
						array("70240-2M", "70240", 2),
						array("70240-3M", "70240", 3),
						array("70240-5M", "70240", 5),
						array("70240-10M", "70240", 10),
						array("70310-1M", "70310", 1),
						array("70310-2M", "70310", 2),
						array("70310-3M", "70310", 3),
						array("70310-5M", "70310", 5),
						array("70310-10M", "70310", 10),
						array("B1888-3", "B1888|1m", 3),
						array("B5552LL9005-3", "B5552LL9005|1m", 3),
						array("18011LL9016-3", "18011|3m", 1),
						array("B3776LL9016-1", "B3776K7|1m", 1),
						array("B3776LL9016-2", "B3776K7|2m", 1),
						array("B3776LL9016-3", "B3776K7|3m", 1),
						array("B1718-3", "B1718|1m", 3),
						array("00085-9005", "00085-9005", 1),
						array("00085-9016", "00085-9016", 1),
						array("00802L", "00802L", 1),
						array("24338LL9016", "24338-9016", 1),
						array("962", "0962", 1),
						array("963", "0963", 1)
					);


					foreach ($pricelistMultiply as $items){
						if($items[0] == $row["code"]){
							$listProduct = $row["code"];
							$pricelistProduct = $items[1];
							$pricelistMultiplier = $items[2];
				
							$getPricelistItemsql = "SELECT * FROM alumia WHERE id = '$pricelistProduct' LIMIT 1";
							$getPricelistResult = $conn->query($getPricelistItemsql);
							if ($getPricelistResult->num_rows > 0) {
								while($pricelistPrice = $getPricelistResult->fetch_assoc()) {
									$pricelistPriceOfProduct = round($pricelistPrice["mocPriceNew"], 0);
									$pricelistPurchasePriceOfProduct = round($pricelistPrice["purchasePriceNew"], 0);

									$priceNew = $pricelistPriceOfProduct * $pricelistMultiplier;
									$purchasePriceNew = $pricelistPurchasePriceOfProduct * $pricelistMultiplier;

									$changePriceSql = "UPDATE alumialxf SET pricelistItem='".$pricelistPriceOfProduct."', pricelistMultiple='".$pricelistMultiplier."', price='".$price."' WHERE code='".$listProduct."'";
									$conn->query($changePriceSql);								}
							}
						}
					}

					$code = $row["code"];
					$pairCode = $row["pairCode"];
					$name = $row["name"];
					$guid = $row["guid"];
					$appendix = $row["appendix"];
					$manufacturer = $row["manufacturer"];
					$supplier = $row["supplier"];
					$ean = $row["ean"];

					if($priceNew != 0){
						$price = $priceNew;
					}else{
						$price = $row["mocPriceNew"];
					}
					$priceRatio = $row["priceRatio"];
					$standardPrice = $row["standardPrice"];
					if($purchasePriceNew != 0){
						$purchasePrice = $purchasePriceNew;
					}else{
						$purchasePrice = $row["purchasePriceNew"];
					}
					$currency = $row["currency"];
					$includingVat = $row["includingVat"];
					$centralStock = $row["centralStock"];
					$luxiferStock = $row["luxiferStock"];
					$stockMinSupply = $row["stockMinSupply"];
					$negativeAmount = $row["negativeAmount"];
					$availabilityOutOfStock	 = $row["availabilityOutOfStock"];
					$availabilityInStock = $row["availabilityInStock"];
					$textProperty2 = $row["textProperty2"];



					if (strpos($name, ';') !== false) {
						$name = str_replace(';', ',', $name);
					}

					
					if (strpos($code, '-43-') !== false) {
						$textProperty2 = str_replace('-43-', '+43|', $code);
						$textProperty2 = $textProperty2 . "m";
					}else if (strpos($code, '-1.5') !== false) {
						$textProperty2 = str_replace('-1.5', '|1,5m', $code);
					}else if (strpos($code, '-1-05') !== false) {
						$textProperty2 = str_replace('-1-05', '|1.05m', $code);
					}else if (strpos($code, '-1.05') !== false) {
						$textProperty2 = str_replace('-1-05', '|1,05m', $code);
					}else if (strpos($code, '-2-10') !== false) {
						$textProperty2 = str_replace('-2-10', '|2.10m', $code);
					}else if (strpos($code, '-2.10') !== false) {
						$textProperty2 = str_replace('-2.10', '|2,10m', $code);
					}else if (strpos($code, '-6') !== false) {
						$textProperty2 = str_replace('-6', '|6m', $code);
					}else if (strpos($code, '-3') !== false) {
						$textProperty2 = str_replace('-3', '|3m', $code);
					}else if (strpos($code, '-2') !== false) {
						$textProperty2 = str_replace('-2', '|2m', $code);
					}else if (strpos($code, '-1') !== false) {
						$textProperty2 = str_replace('-1', '|1m', $code);
					}else if (($code <= 100)&&(strpos($code, 'CH') === false)&&(strpos($code, '-') === false)&&(strpos($code, 'L') === false)&&(strpos($code, 'STM') === false)&&(strpos($code, 'FS') === false)){
						$textProperty2 = "000".$code;
					}else if (($code <= 1000)&&(strpos($code, 'CH') === false)&&(strpos($code, 'STM') === false)&&(strpos($code, 'L') === false)&&(strpos($code, '-') === false)&&(strpos($code, 'FS') === false)) {
						$textProperty2 = "00".$code;
					}else if (($code <= 50000)&&(strpos($code, 'CH') === false)) {
						$textProperty2 = $code;
					}else if (strpos($textProperty2, 'napájecí napětí;') !== false) {
						$textProperty2 = $code;
					}else if (strpos($textProperty2, 'výrobce;') !== false) {
						$textProperty2 = $code;
					}else if (strpos($textProperty2, 'průměr;') !== false) {
						$textProperty2 = $code;
					}else if (strpos($textProperty2, 'počet LED na metr;') !== false) {
						$textProperty2 = $code;
					}else if (strpos($textProperty2, 'barva;') !== false) {
						$textProperty2 = $code;
					}					



					$sku = "UPDATE alumialxf SET realSKU='".$textProperty2."' WHERE code='".$code."'";
//						$conn->query($sku);


					if($counter == 1){
						echo "code;pairCode;name;price;purchasePrice;<BR>";
					}
	
					if($format = "csv"){
						echo $code . ";" .
							 $pairCode . ";" .
							 $name . ";" .
							 $price . ";" .
							 $purchasePrice . ";<BR>";
					}



				}
			} else {
				echo "0 results";
			}
		break;
	}
}else{
	echo "Missing INPUT parameters";
}
?>