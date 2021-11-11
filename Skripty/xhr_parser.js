$(document).ready(function (e) {

	$('#modalx-popBounce').popup({
		pagecontainer: '.container',
		transition: 'all 0.6s',
		color: '#000',
		opacity: '0.5',
		opacity: '0.4',
		escape: true
	});

	var sponka = $(document).find($("#sponka video");
	seeThru.create(sponka);



});


 




	function filtryIntegroid(){
		var integroid = document.getElementById("integroid");
		if(integroid.style.display == "block"){
			integroid.style.display = "none";
		}else{
			integroid.style.display = "block";
		}
	} 

	function cookieShoptet(){
		var shoptetCookie = document.getElementById("shoptetCookie");
		if(shoptetCookie.style.display == "block"){
			shoptetCookie.style.display = "none";
		}else{
			shoptetCookie.style.display = "block";
		}
	} 


	function startUpload(e){
		if($("#supplier").find(':selected').attr("value") != ""){
			$(".container").removeClass("loading");
//				document.getElementById('f1_upload_process').style.visibility = 'visible';
//				document.getElementById('f1_upload_form').style.visibility = 'hidden';
		}else{
			$(".container").removeClass("loading");
			alert("Musíte nejprve vybrat dodavatele");
			event.preventDefault();
		}

	}

	function stopUpload(success){
		var result = '';
		$(".container").addClass("loading");			
		var supplier = $("#supplier").find(':selected').attr("value");
		var supplierid = $("#supplier").find(':selected').data("shoptetid");
		var pricelist = $('#xmlFeed').val();

		if (success == 1){
			var request = $.ajax({
				url: "/php/csv.php",
				type: "POST",
				data: {"supplier" : supplier, "supplierid": supplierid, "pricelist" : $("#csvFeed").value },
				dataType: "html"
			});


			request.done(function(msg) {
				$("#csvFeed").attr("value", "Vyberte prodejce");

				$(".container").removeClass("loading");

				msg = msg.replace(/\n/g,"</tr><tr><td>");
				msg = msg.replace(/;/g,"</td><td>");
				msg = "<table><tr><td>" + msg + "</table>";
				$("#parserResult")
					.show()
					.html(msg)
					.append("<a href=\"/parser/\" id=\"parserDownload\" download>Download CSV</a>");
			});

			request.fail(function(jqXHR, textStatus) {
				$(".container").removeClass("loading");
				$("#parserResult").show().html(msg);
			});

		 result = '<span class="msg">The file was uploaded successfully!<\/span><br/><br/>';
		}
		else if(success == 2){

			var request2 = $.ajax({
				url: "/php/xml.php?supplier="+supplier+"&supplierid="+supplierid,
			    dataType: 'json',					
				method:"POST",
				contentType: 'application/json',
					data: JSON.stringify( { "supplier": supplier, "supplierid": supplierid, "pricelist": pricelist} ),
		        success: function(response){
					$(".container").removeClass("loading");
					msg = "<table><tr><tbody></tbody></table>";
					$("#parserResult")
						.show()
						.append(msg)
		            var len = response.length;
		            for(var i=0; i<len; i++){
		                var id = response[i].id;
		                var description = response[i].description;
		                var price_purchase_no_vat = response[i].price_purchase_no_vat;
		                var price_no_vat = response[i].price_no_vat;
		                var quantity = response[i].quantity;

		                var tr_str = "<tr>" +
		                    "<td align='center'>" + id + "</td>" +
		                    "<td align='center'>" + description + "</td>" +
		                    "<td align='center'>" + price_purchase_no_vat + "</td>" +
		                    "<td align='center'>" + price_no_vat + "</td>" +
		                    "<td align='center'>" + quantity + "</td>" +
		                    "</tr>";

						   $("#parserResult tbody").append(tr_str);							
		            }
				}	
			});
		}
		else {
		 result = '<span class="emsg">There was an error during file upload!<\/span><br/><br/>';
		}
/*			document.getElementById('f1_upload_process').style.visibility = 'hidden';
		document.getElementById('f1_upload_form').innerHTML = result + '<label>File: <input name="myfile" type="file" size="30" /><\/label><label><input type="submit" name="submitBtn" class="sbtn" value="Upload" /><\/label>';
		document.getElementById('f1_upload_form').style.visibility = 'visible';      
*/


		return true;   
	}

	function runParser(e){
		e.preventDefault();


		if($("#csvFeed").val()){
			alert("csv");
			$("form").submit();
		}


		if($("#xmlFeed").val()){
			alert("xml");

		
		}
	}

	const lastSupplier = localStorage.getItem('lastSupplier');

	console.log(lastSupplier);	
		$('#supplier option[value="'+lastSupplier+'"]').prop('selected', 'selected').change();
		$("#supplier").change(function (){
			var hasXml = $(this).find(':selected').attr("data-hasxml");
			if(hasXml){
				$("#xmlFeed").val(hasXml);
			}else{
				$(".filename").html("");
			}

			localStorage.setItem('lastSupplier', $("#supplier").find(':selected').attr("value"));
		});

		/*$("#csvFeed").change(function (){
			$("#xmlFeed").value = "";
		});
		$("#xmlFeed").change(function (){
			var fileName = $(this).val();
			$(".filename").html(fileName);
			$("#csvFeed").val("");
		});

		*/


