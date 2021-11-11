<?php 
ini_set('display_errors', 1);
include('./php/db-config.php');
?>
<!DOCTYPE html>
<html id="css" lang="cs">
<head>
	<title>lxfDBOl</title>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<meta name="googlebot" content="noindex,nofollow">
	<meta name="author" content="Luxifeři">
	<meta name="copyright" content="LXR.cz">
	<link href="php/css/main.css" rel="stylesheet">
</head>
<body>
<div class="bg"></div>
<div class="container">
	<div id="modalx-popBounce" class="body-modalx">
		<h1 class="text-center">Modal Animated</h1>
		<p></p>
		<img src="...">
		<button class="modalx-popBounce_close btn-close-modalx pull-right">Fechar </button>
	</div>

	<div id="sponka">
		<video id="sponkavid" preload="auto" crossorigin="Anonymous" loop playsinline muted>
			<source src="./mov/sponka.mp4" type="video/mp4">
		</video>
	</div>

	<form action="./php/upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
	
	<h2>Stay a while and listen...</h2>

	<ul style="list-style: none; margin:0; padding:0;">
		<li>
			<div id="parserResult">
			</div>
		</li>
		<li>
            <p id="f1_upload_form" align="center"><br/>
				<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
				<select id="supplier" name="supplier" style="max-width: 200px">
					<option value="">Vyberte dodavatele</option>
					<option value="alumia" data-shoptetid = "11" data-hasxml = "https://www.alumia.cz/index.php?option=com_xmlfeedcz&task=feed&code=H">ALUMIA</option>
					<option value="AlumiaCentralStock" data-hasxml = "https://www.alumia.cz/index.php?option=com_xmlfeedcz&task=feed&code=dostupnost">ALUMIA CENTRAL STOCK</option>
					<option value="AlumiaLuxiferStock" data-hasxml = "https://www.lxf.cz/export/products.csv?patternId=56&hash=66b759edae927299abc59da8b37b28113a9b9ce6645065a2c31e0dc11837ac11&supplierId=11">ALUMIA LUXIFER STOCK</option>
					<option value="azzardo">Azzardo</option>
					<option value="belight">BE-LIGHT</option>
					<option value="creativecables">Creative Cables</option>
					<option value="daylightitalia">DAYLIGHT ITALIA</option>
					<option value="erp">ERP</option>
					<option value="glp">GLP</option>
					<option value="hogo">HOGO</option>
					<option value="Immax">Immax</option>
					<option value="KANLUX">KANLUX</option>
					<option value="KLUŚ">KLUŚ</option>
					<option value="KONTAKT CHEMIE">KONTAKT CHEMIE</option>
					<option value="LED Solution">LED Solution</option>
					<option value="MW Lighting">MW Lighting</option>
					<option value="MeanWell" data-hasxml="https://www.eshop-meanwell.cz/modules/connector/partner_feed/meanwell_feed.xml">Mean Well</option>
					<option value="POS">POS</option>
					<option value="Sunricher">Sunricher</option>
					<option value="T-LED">T-LED</option>
					<option value="TEKABEN">TEKABEN</option>
					<option value="TRIDONIC">TRIDONIC</option>
					<option value="TRIO">TRIO</option>
					<option value="TUFF-TAPE">TUFF-TAPE</option>
					<option value="V-TAC" data-hasCsv="">V-TAC</option>
					<option value="WAGO">WAGO</option>
					<option value="ZBL">ZBL</option>
				</select><br>
			</li>
			<li>
				<h3>CSV</h3>
				<input type="file" name="myfile" id="csvFeed" class="form-control">
			</li>
			<li>
				<h3>XML</h3>
				<input type="text" name="xmlFeed" id="xmlFeed" xmlclass="form-control" value=""><br>
			</li>
			<li>
				<button type="submit" name="import" class="btn btn-success"> Import Data</button><br>
			</li>
		</ul>
		</p>
	</form>


	<h3>Odkazy</h3>
	<ul style="list-style: none; margin:0; padding:0;">
		<li>
			<a href="#">MYSQL dotazy</a>
			</a>
			<button onclick="sqlDotazy();">Zobrazit</button>
			<pre id="sqlDotazy">
// Zapnuti cookies
 UPDATE products
SET sku = REPLACE(sku, '|1m', '-1')
			</pre>
		</li>
		<li>
			<a href="#">Shoptet - Debug Timestamp</a>
			</a>
			<button onclick="cookieShoptet();">Zobrazit</button>
			<pre id="shoptetCookie">
// Zapnuti cookies
 shoptet.cookie.create('debugTimestamp', 1, {days: 1});

// Zadani do adminu
https://cdn.myshoptet.com/usr/www.lxf.cz/user/documents/main.css?v=#DEBUG_TIMESTAMP#
			</pre>
		</li>
		<li>
			<a href="https://www.myintegroid.com/admin/">Hlídání skladu</a>
				<br><small>info@lxf.cz - Jermanova</small>
			</a>
			<button onclick="filtryIntegroid();">Filtry</button>
			<code id="integroid">
				$("tr").each(function( index ) {
					if( !$( this ).find(".dark-gray:contains('WAGO'), .dark-gray:contains('TUFF-TAPE'), .dark-gray:contains('POS'), .dark-gray:contains('GLP')").length){
						var url = $( this ).find("input").data("url") + "&alert=0";
						$.ajax(url, {success: function(data) {console.log("%cIGNORED - " + url, "color: red");}});
					}else{
						var url = $( this ).find("input").data("url") + "&alert=1";
						$.ajax(url, {success: function(data) {console.log("%cPASSED - " + url, "color: green");}});
					}
				});
			</code>
		</li>
	</ul>





	<h3>Eshopy</h3>
	<ul style="list-style: none; margin:0; padding:0;">
		<li>
			<a href="http://lxf.cz/admin/html-kody/">LXF.cz</a>
		</li>
		<li>
			<a href="http://klusprofile.cz/admin/html-kody/">Klusprofile.cz</a>
		</li>
		<li>
			<a href="http://klusprofile.sk/admin/html-kody/">klusprofile.sk</a>
		</li>
		<li>
			<a href="http://klusprofile.de/admin/html-kody/">klusprofile.de</a>
		</li>
	</ul>

	<div class="loader">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>
</center>
<script src="php/js/app.js"></script>	
</body>
</html>