$(function() {
	// LOAD PLUGINS
		
	$.datepicker.regional['cs'] = {
					closeText: 'Cerrar',
					prevText: 'Předchozí',
					nextText: 'Další',
					currentText: 'Hoy',
					monthNames: ['Leden','Únor','Březen','Duben','Květen','Červen', 'Červenec','Srpen','Září','Říjen','Listopad','Prosinec'],
					monthNamesShort: ['Le','Ún','Bř','Du','Kv','Čn', 'Čc','Sr','Zá','Ří','Li','Pr'],
					dayNames: ['Neděle','Pondělí','Úterý','Středa','Čtvrtek','Pátek','Sobota'],
					dayNamesShort: ['Ne','Po','Út','St','Čt','Pá','So',],
					dayNamesMin: ['Ne','Po','Út','St','Čt','Pá','So'],
					weekHeader: 'Sm',
					dateFormat: 'dd.mm.yy',
					firstDay: 1,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''};
	 
	$.datepicker.regional['sk'] = {
					closeText: 'Zavrieť',
					prevText: '<Predchádzajúci',
					nextText: 'Nasledujúci>',
					currentText: 'Dnes',
					monthNames: ['Január','Február','Marec','Apríl','Máj','Jún',
					'Júl','August','September','Október','November','December'],
					monthNamesShort: ['Jan','Feb','Mar','Apr','Máj','Jún',
					'Júl','Aug','Sep','Okt','Nov','Dec'],
					dayNames: ['Nedel\'a','Pondelok','Utorok','Streda','Štvrtok','Piatok','Sobota'],
					dayNamesShort: ['Ned','Pon','Uto','Str','Štv','Pia','Sob'],
					dayNamesMin: ['Ne','Po','Ut','St','Št','Pia','So'],
					weekHeader: 'Ty',
					dateFormat: 'dd.mm.yy',
					firstDay: 0,            
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''};
	 
	$.datepicker.setDefaults($.datepicker.regional['cs']);

	$( "#datum" ).datepicker({ minDate: new Date(2012, 11 - 1, 1), maxDate: new Date(2013, 1 - 1, 20) });

	$(".podminky").fancybox({
        'type': 'inline',
				'titlePosition'		: 'inside',
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'overlayOpacity'    : 1,
				'width'   			: 500
	});
	
		var upload = new AjaxUpload('#file_upload', {
				//upload script 
				action: './php/upload.php',  
				onSubmit : function(file, extension){
				//show loading animation
					 $("#file_upload").show();
					 $(".loader").html("<img src='images/progress.gif'>");
				//check file extension
		
				if (! (extension && /^(jpg|png|jpeg|gif|pdf|doc|docx)$/.test(extension))){
			   // extension is not allowed
					 $("#file_upload").hide();
					 $(".loader").html("");
					new Messi('Tento formát souboru není podporován. Soubor může být pouze JPG, PNG, GIF.', {title: 'Nesprávný formát souboru', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]}); 
					// cancel upload
			   return false;
					} else {
					  // get rid of error
					$('.error').hide();
					}	
					//send the data
					upload.setData({'file': file});
				},
				onComplete : function(file, response){
				var oBody = $(".iframe").contents().find("div").html();	
				//hide the loading animation
				$("#file_upload").hide();
				$(".loader").html(oBody + "<br>Byl úspěšně nahrán!");
				//add display:block to success message holder
				$(".success").css("display", "block");
				
		//This lower portion gets the error message from upload.php file and appends it to our specifed error message block
				//find the div in the iFrame and append to error message	
				
				//alert(oBody);
				//add the iFrame to the errormes td
				//$(oBody).appendTo("#filefinal");
				$("#filefinal").val(oBody);
		//This is the demo dummy success message, comment this out when using the above code
				//$("#file_holder #errormes").html("<span class='success'>Your file was uploaded successfully</span>");
		}
			});		

		
	// OBEJCT ACTIONS

	$("#btn-produkty").click(function(){ 
	$("html").scrollTo( '#produkty', {duration:1000} );   
	});
	
	$("#btn-obchody").click(function(){ 
	$("html").scrollTo( '#obchody', {duration:1000} );   
	});	
	
 	$("#btn-zadost").click(function(){ 
	$("html").scrollTo( '#zadost', {duration:1000} );   
	});	
	
 	$(".souhlas, #checkbox").click(function(){ 
	var str = $("#checkbox").attr("src");
	if (str.indexOf("checked") >= 0) {
	$("#checkbox").attr("src","./images/checkbox.png");	
	}else{
	$("#checkbox").attr("src","./images/checkbox-checked.png");		
	}
	});		
   
  	$("#btn-ok").click(function(){ 
	$("#formular").submit();   
	});
	
	  
    $(".price").click(function(){ 
	$("html").scrollTo( '#zadost', {duration:1000} );  
    $("#produkt option[value='"+$(this).attr('id')+"']").attr('selected', 'selected');
   	});	
	
	// AJAX ACTION 
 
    $("#formular").submit(function(event){
    var validace = validate("formular");
    if(validace==true){
		
		//----------- AJAX FILEUPLOAD ----------//
	
		//upload.submit();
	
		// --------- END OF AJAX FILEUPLOAD ---------//
		
		
    // setup some local variables
    var $form = $(this),
        // let's select and cache all the fields
        $inputs = $form.find("input, select, button, textarea, submit"),
        // serialize the data in the form
        serializedData = $form.serialize();

    // let's disable the inputs for the duration of the ajax request
    //$inputs.attr("disabled", "disabled");

    // fire off the request to /core.php
    $.ajax({
        url: "./php/core.php",
        type: "post",
        data: serializedData,
        // callback handler that will be called on success
        success: function(response, textStatus, jqXHR){
            // log a message to the console
            new Messi('Formulář byl odeslán v pořádku, děkujeme!', {title: 'Formulář byl udeslán', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
            $(".btn").focus();
			$("#checkbox").attr("src","./images/checkbox.png");	
        },
        // callback handler that will be called on error
        error: function(jqXHR, textStatus, errorThrown){
            // log the error to the console
            console.log(
                "The following error occured: "+
                textStatus, errorThrown 
            );
            new Messi('Formulář nebyl odeslán v pořádku, prosíme kontaktujte nás!', {title: 'Formulář nebyl odeslaný', titleClass: 'anim error', buttons: [{id: 0, label: 'Close', val: 'X'}]});            
            $(".btn").focus();             
        },
        // callback handler that will be called on completion
        // which means, either on success or error
        complete: function(){
            //alert(response);
            // enable the inputs
        }
    });

    // prevent default posting of form
    event.preventDefault();
    }else{
    return false;
    }
});           

});     


// VALIDACE FORMULARE

function validate(form) { 

if($("#jmeno").val() == '')
{
 new Messi('Vyplňte prosím vaše jméno', {title: 'Jméno nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#prijmeni").val() == '')
{
 new Messi('Vyplňte prosím vaše příjmení', {title: 'příjmení nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

var emailktestu = $("#email").val();
  if(emailktestu!=""){
    if( !isValidEmailAddress( emailktestu ) ) {
      new Messi('Vyplňte prosím e-mail ve správném formátu', {title: 'E-mail nemá správný formát', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
      $(".btn").focus();
      return false;   
    }
  }else{
      new Messi('Vyplňte prosím e-mail', {title: 'E-mail nebyl vyplněn', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
      $(".btn").focus();
      return false;    
  }

if($("#ulice").val() == '')
{
 new Messi('Vyplňte prosím ulici a č.p', {title: 'Ulice a č.p. nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#mesto").val() == '')
{
 new Messi('Vyplňte prosím město', {title: 'Město nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}


if($("#produkt").val() == '')
{
 new Messi('Vyberte prosím produkt', {title: 'Produkt nebyl vybrán', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#ucet").val() == '')
{
 new Messi('Vyplňte prosím číslo účtu', {title: 'Číslo účtu nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#banka").val() == '')
{
 new Messi('Vyplňte prosím kód banky', {title: 'Kód banky nebyl vyplněn', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#datum").val() == '')
{
 new Messi('Vyplňte prosím datum nákupu', {title: 'Datum nákupu nebyl vyplněn', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#cislo").val() == '')
{
 new Messi('Vyplňte prosím sériové číslo', {title: 'Sériové číslo nebylo vyplněno', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#prodejce").val() == '')
{
 new Messi('Vyberte prosím prodejce', {title: 'Prodejce nebyl vyplněn', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

if($("#filefinal").val() == '')
{
 new Messi('Vyberte prosím soubor s účtenkou z Vašeho počítače', {title: 'Soubor s účtenkou nebyl vybrán', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
 $(".btn").focus();
 return false;
}

var str = $("#checkbox").attr("src");
	if (str.indexOf("checked") >= 0) {
	}else{
	 new Messi('Zaškrtněte prosím souhlas s podmínkami akce', {title: 'Musíte souhlasit s podmínkami akce', modal: true, buttons: [{id: 0, label: 'Zavřít', val: 'X'}]});
	 $(".btn").focus();
	 return false;	
	}


return true;
}


// CHECK RIGHT EMAIL FORM


function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function backfile(){
document.getElementById("file_upload").style.display = "block";
document.getElementById("templink").style.display = "none";			
};



