<?php
	define('ELEMENT_CONTENT_ONLY', true);
	define('ELEMENT_PRESERVE_TAGS', false);

	function value_in($element_name, $xml, $content_only = true) {
		if ($xml == false) {
			return false;
		}
		$found = preg_match('#<'.$element_name.'(?:\s+[^>]+)?>(.*?)'.
				'</'.$element_name.'>#s', $xml, $matches);
		if ($found != false) {
			if ($content_only) {
				return $matches[1];  //ignore the enclosing tags
			} else {
				return $matches[0];  //return the full pattern match
			}
		}
		return false;
	}

	function element_set($element_name, $xml, $content_only = false) {
		if ($xml == false) {
			return false;
		}
		$found = preg_match_all('#<'.$element_name.'(?:\s+[^>]+)?>' .
				'(.*?)</'.$element_name.'>#s',
				$xml, $matches, PREG_PATTERN_ORDER);
		if ($found != false) {
			if ($content_only) {
				return $matches[1];  //ignore the enlosing tags
			} else {
				return $matches[0];  //return the full pattern match
			}
		}
		return false;
	}

	function element_attributes($element_name, $xml) {
		if ($xml == false) {
			return false;
		}
		$found = preg_match('#<'.$element_name.
				'\s+([^>]+(?:"|\'))\s?/?>#',
				$xml, $matches);
		if ($found == 1) {
			$attribute_array = array();
			$attribute_string = $matches[1];
			$found = preg_match_all(
					'#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#',
					$attribute_string, $matches, PREG_SET_ORDER);
			if ($found != 0) {
				foreach ($matches as $attribute) {
					$attribute_array[$attribute[1]] =
							substr($attribute[2], 1, -1);
				}
				return $attribute_array;
			}
		}
		return false;
	}

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

	function create_insert_query($tablename, $array) {
		foreach($array as $arr) {		
			$key = array_keys($arr);
			$val = array_values($arr);
			$query = "REPLACE INTO $tablename (" . implode(', ', $key) . ") "
			 . "VALUES ('" . implode("', '", $val) . "')";
			 //  ON DUPLICATE KEY UPDATE 
			 //price='".$arr["price"]."',standardPrice='".$arr["standardPrice"]."',purchasePrice='".$arr["purchasePrice"]."'";
			//INSERT IGNORE
		}
		return($query);
	}

	function scrapFeed($supplier,$supplierid){
		$db              =      new DBController();
		$conn            =      $db->connect();
		$feedArr = array();

		$feedURL = "https://www.lxf.cz/export/productsSupplier.xml?patternId=-4&hash=4d8980e7eb923f9c18bd2e4ed9601022fd6b168e2ff674e7a0e9203ea6a9d570&supplierId=".$supplierid;

		$html = "";

		$xml = file_get_contents($feedURL);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $feedURL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$xml = curl_exec($ch);
		curl_close($ch);

		$channel_title = value_in('ITEM_ID', $xml);
		$channel_link = value_in('link', $xml);

		$feedArr[] = array();

		$feedData = element_set('SHOPITEM', $xml);

		foreach($feedData as $feed) {		
			$code 						= value_in('CODE', $feed);   
			$pairCode 					= value_in('PAIRCODE', $feed);   
			$name 						= value_in('NAME', $feed);
			$guid 						= value_in('GUID', $feed);    
			$appendix 					= value_in('APPENDIX', $feed);    
			$shortDescription 			= value_in('SHORTDESCRIPTION', $feed);    
			$manufacturer 				= value_in('MANUFACTURER', $feed);    
			$supplier 					= value_in('SUPPLIER', $feed);    
			$ean 						= value_in('EAN', $feed); 
			$price  					= value_in('PRICE', $feed);  
			$priceRatio					= value_in('PRICERATION', $feed);  
			$standardPrice 				= value_in('STANDARDPRICE', $feed);   
			$purchasePrice 				= value_in('PURCHASEPRICE', $feed);   
			$currency   				= value_in('CURRENCY', $feed);  
//				$includingVat  	 			= value_in('includingVat', $feed);  
//				$centralStock  				= value_in('centralStock', $feed);   
//				$luxiferStock   			= value_in('luxiferStock', $feed);  
			$stockMinSupply  			= value_in('STOCKMINSUPPLY', $feed);
//				$negativeAmount  			= value_in('negativeAmount', $feed);
			$availabilityOutOfStock   	= value_in('AVALIABILITYOUTOFSTOCK', $feed);
			$availabilityInStock  		= value_in('AVALIABILITYINSTOCK', $feed);
			$textProperty2   			= value_in('TEXT_PROPERTIES', $feed); 
			$feedArr[] = array(
				'code' 				=> $code,
				'pairCode'			=> make_safe($pairCode),
				'name' 				=> make_safe($name),
				'guid' 				=> make_safe($guid),
				'appendix' 			=> make_safe($appendix),
				'shortDescription'  => make_safe($shortDescription),
				'manufacturer' 		=> make_safe($manufacturer),
				'supplier' 			=> make_safe($supplier),
				'ean' 				=> make_safe($ean),
				'price' 			=> make_safe($price),
				'priceRatio' 		=> make_safe($priceRatio),
				'standardPrice' 	=> make_safe($standardPrice),
				'purchasePrice' 	=> make_safe($purchasePrice),
				'currency' 			=> make_safe($currency),
				'stockMinSupply' 	=> make_safe($stockMinSupply),
				'availabilityOutOfStock' => make_safe($availabilityOutOfStock),
				'availabilityInStock' 	 => make_safe($availabilityInStock),
				'textProperty2' 		 => make_safe($textProperty2)
			);
	
			$sql = create_insert_query($supplier, $feedArr);
			if ($conn->query($sql) != TRUE) {
				$allOk = false;
			}
		}
	}

?>