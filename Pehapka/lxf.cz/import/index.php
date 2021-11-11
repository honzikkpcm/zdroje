<?php
header('Content-Encoding: UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	include_once '../php/db-config.php';
	include_once '../php/db-functions.php';


	$html = "";
	$feedData = "";
	$allOk = true;
	//$data = json_decode(file_get_contents('php://input'), true);

	$supplier = $_GET["supplier"];
	$supplierid = $_GET["supplierid"];

	scrapFeed($supplier, $supplierid);

	switch ($supplier) {
	    case "alumia":


			/*
			$news_items = element_set('SHOPITEM', $xml);
			foreach($news_items as $item) {
				$itemid = value_in('SKU', $item);
				$product_name = value_in('PRODUCTNAME', $item);
				$product = value_in('PRODUCT', $item);
				$description = value_in('DESCRIPTION', $item);
				$url = value_in('URL', $item);
				$imgurl = value_in('IMGURL', $item);
				$price = value_in('PRICE', $item);
				$price_vat = value_in('PRICE_VAT', $item);
				$brand = value_in('MANUFACTURER', $item);
				$item_type = value_in('ITEM_TYPE', $item);
				$category_text[] = value_in('CATEGORYTEXT', $item);
				$ean = value_in('EAN', $item);
				$sku = value_in('SKU', $item);
				$delivery_date = value_in('DELIVERY_DATE', $item);
				$item_group_id = value_in('ITEMGROUP_ID', $item);
				$weight = value_in('WEIGHT', $item);
			//	$timestamp = strtotime(value_in('pubDate', $item));
				$item_array[] = array(
					'id' => $itemid,
					'product_name' => make_safe($product_name),
					'product' => make_safe($product),
				    //'description' => make_safe($description),
				    'url' => make_safe($url),
				    'imgurl' => make_safe($imgurl),
				    'price' => make_safe($price),
				    'price_vat' => make_safe($price_vat),
				    'brand' => make_safe($brand),
				    'item_type' => make_safe($item_type),
				    //'category_text' => $category_text,
				    'ean' => make_safe($ean),
				    'sku' => make_safe($sku),
				    'delivery_date' => make_safe($delivery_date),
				    'item_group_id' => make_safe($item_group_id),
				    'weight' => make_safe($weight)
				);
				$sql = "INSERT INTO products (product_id, product_name, product, url, imgurl, price_no_vat, price_vat, brand, item_type, ean, sku, delivery_date, itemgroup_id, weight, supplier) VALUES ('$itemid', '$product_name', '$product', '$url', '$imgurl', '$price', '$price_vat', '$brand', '$item_type', '$ean', '$sku', '$delivery_date', '$item_group_id', '$weight', '$supplier') ";
			/*	
				if ($conn->query($sql) === TRUE) {
					$canIsaveIt = true;
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}
			*/

		

	        break;
	    case "AlumiaCentralStock":
			$news_items = element_set('item', $xml);
			foreach($news_items as $item) {
				$pieces = explode('"', $item);
				$itemid = $pieces[1];
				$sku = $pieces[3];
				$stock_quantity = value_in('stock_quantity', $item);
//					echo $itemid . ' / ' . $sku . ' / ' . $stock_quantity . '<br>';
				$sql = "UPDATE supplies SET central_stock='".$stock_quantity."' WHERE sku='".$sku."'";
				if ($conn->query($sql) === TRUE) {
//						echo "OK";
				} else {
					echo "FUCK" . $conn->error;
				}
			}	
			$stock_avaliability = csv_parse("https://www.lxf.cz/export/products.csv?patternId=56&hash=66b759edae927299abc59da8b37b28113a9b9ce6645065a2c31e0dc11837ac11&supplierId=11");				

//				print_r($stock_avaliability);

			foreach($stock_avaliability as $stock) {		

				
				$sku = explode(';', $stock["textProperty2"]);
//					echo $sku[1] . " - " . $stock["stock"];
				$sql = "UPDATE supplies SET luxifer_stock='".$stock["stock"]."' WHERE product_id='".$sku[1]."'";
				if ($conn->query($sql) === TRUE) {
//						echo "OK";
				} else {
					echo "FUCK" . $conn->error;
				}
			}

			$sql = "SELECT * FROM supplies";
			
				$results = $conn->query($sql);

				if ($results->num_rows > 0) {
			    // output data of each row
			    while($row = $results->fetch_assoc()) {
			    	$dostupnost = "neznámá";
			    	$idecko = $row["product_id"];
			    	$central = preg_replace('~\D~', '', $row["central_stock"]);
			    	$luxifer = preg_replace('~\D~', '', $row["luxifer_stock"]);

			    	if(($central >= 1) && ($luxifer <= 0)) { $dostupnost = "Na objednávku do 10 dní"; }
			    	if(($central == 0) && ($luxifer >= 1)) { $dostupnost = "Skladem"; }
			    	if(($central == 0) && ($luxifer <= 0)) { $dostupnost = "Na objednávku do 40 dní"; }
			    	if(($central >= 1) && ($luxifer > 1)) { $dostupnost = "Skladem"; }

			    	echo "central: " . $central . " - luxifer: " . $luxifer . " - dostupnost: " . $dostupnost . "<br>";
					$dostupnostsql = "UPDATE supplies SET stock_avaliability='".$dostupnost."' WHERE product_id='".$idecko."'";
					if ($conn->query($dostupnostsql) === TRUE) {
						echo "OK";
					} else {
						echo "FUCK" . $conn->error;
					}


			    }
			} else {
			    echo "0 results";
			}

	        break;
	    case "AlumiaLuxiferStock":
			$news_items = csv_parse("https://www.lxf.cz/export/products.csv?patternId=56&hash=66b759edae927299abc59da8b37b28113a9b9ce6645065a2c31e0dc11837ac11&supplierId=11");				
			foreach($news_items as $items) {		

//					print_r($items);

				echo $items["code"] . " - " . $items["stock"];
				//$items["code"]


/*					foreach($items as $item) {		
					//echo $item[0] . " - " . $item[5];
					echo $item . "<br>"; 
				}

*/
			}
	        break;

	    case "Azzardo":
	        echo "Azzardo";
	        break;

	    case "BE-LIGHT":
	        echo "BE-LIGHT";
	        break;

	    case "Creative Cables":
	        echo "Creative Cables";
	        break;

	    case "DAYLIGHT ITALIA":
	        echo "DAYLIGHT ITALIA";
	        break;

	    case "ERP":
	        echo "ERP";
	        break;

	    case "GLP":
	        echo "GLP";
	        break;

	    case "HOGO":
	        echo "HOGO";
	        break;

	    case "Immax":
	        echo "Immax";
	        break;

	    case "KANLUX":
	        echo "KANLUX";
	        break;

	    case "KLUŚ":
	        echo "KLUŚ";
	        break;

	    case "LED Solution":
	        echo "LED Solution";
	        break;

	    case "MeanWell":
			$news_items = element_set('SHOPITEM', $xml);
			foreach($news_items as $item) {
				$itemid = value_in('ITEM_ID', $item);
				$description = value_in('DESCRIPTION', $item);
				$price_purchase_no_vat = value_in('PRICE_PURCHASE_NO_VAT', $item);
				$price_no_vat = value_in('PRICE_NO_VAT', $item);
				$quantity = value_in('QUANTITY', $item);
//				$timestamp = strtotime(value_in('pubDate', $item));
				$item_array[] = array(
					'id' => $itemid,
				    'description' => $description,
				    'price_purchase_no_vat' => $price_purchase_no_vat,
				    'price_no_vat' => $price_no_vat,
				    'quantity' => $quantity
				);
				$sql = "INSERT INTO products (product_id, product_name, price_purchase_no_vat, price_no_vat, quantity, supplier) VALUES ('$itemid', '$description', '$price_purchase_no_vat', '$price_no_vat', '$quantity', '$supplier') ";
				$result = $conn->query($sql);
				if($result == 1){ $canIsaveIt = true; }
			}
	        break;

	    case "MWLighting":
	        echo "MWLighting";
	        break;

	    case "POS":
	        echo "POS";
	        break;

	    case "KLUŚ":
	        echo "KLUŚ";
	        break;

	    case "Sunricher":
	        echo "Sunricher";
	        break;

	    case "TRIO":
	        echo "TRIO";
	        break;

	    case "V-TAC":
	        echo "V-TAC";
	        break;
	}

				
	if($allOk == true){
//		header("HTTP/1.1 200 OK");
		echo json_encode($feedData);
//	header('Content-Disposition: attachment; filename=Customers_Export.csv');
	}else{
		echo "Nekde se stala chyba";
	}

?>