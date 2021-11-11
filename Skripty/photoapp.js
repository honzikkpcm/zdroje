( function($) {
	$("#objects").hide();
	localStorage.setItem('idecka', 0);
	window.animating = false;

	$(document).on("click", "#background li", function(e){

		if(e.target === this){
			pauseAutoplay();
			var lastSelected = $("#background li.selected");
			lastSelected.removeClass("selected");
			$(this).addClass("selected");

			if(!$("#objects").hasClass("show")){
				$("#toolbox").fadeIn().css("left", parseInt(window.x - 200) + "px").css("top", parseInt(window.y - 200) + "px");
				$("#objects").addClass("show");
			}else{
				$("#objects").removeClass("show");
			}
		}
	});

	$(document).on("click", "#likePhoto", function(e){
		var request = $.ajax({
			url: "/like.php",
			type: "POST",
			data: {"typeid" : "+", "fileid" : $("#background li.selected").data("id"), "userid": "PrdelniPrincezna" },
			dataType: "html"
		});

		$("#toolbox").removeClass("show");

	});

	$(document).on("click", "#dislikePhoto", function(e){
		var request = $.ajax({
			url: "/like.php",
			type: "POST",
			data: {"typeid" : "-", "fileid" : $("#background li.selected").data("id"), "userid": "PrdelniPrincezna" },
			dataType: "html"
		});

		$("#toolbox").removeClass("show");
	});


	$(document).on("click", "#showCunt", function(e){
		var objects = $(".objects");
			objects.addClass("opened").fadeIn();
		$(this).addClass("zoom").parent().addClass("zoomed");
		var category = $(this).data("detail");
		$("#objects").removeClass("huba kozy kunda").addClass("kunda");
	});



/*			if($(this).find("#objects").length){
				$(this).removeClass("zoom").parent().removeClass("zoomed");
				var objects = $("#objects").fadeOut("slow");
				$("body").prepend(objects);
				pauseAutoplay();
			}else{
				
				;
				pauseAutoplay(true);
			}	
*/


	$(document).mousemove(function(e) {
		window.x = e.pageX;
		window.y = e.pageY;
	});
	$(window).on('DOMMouseScroll mousewheel', function (e) {
		pauseAutoplay();

		if(window.animating){
			return false;
		}
		var e = window.event || e; // old IE support
		var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));

		$("#toolbox").removeClass("show");
		$("#background li").removeClass("zoom");
		$("#background").removeClass("zoomed");

		var screenWidth = window.innerWidth;


		var lastSelected = $("#background li.selected");
		var newSelected;


		if(!lastSelected.length){
			lastSelected = $("#background li:first-child");
		}

		if(delta>=1){
			newSelected = lastSelected.next();
			if(newSelected.is(":last-child")){
				loadWall(localStorage.getItem('wallFeed'));
			}
		}else{
			newSelected = lastSelected.prev();
			if(newSelected.is(":first-child")){
				loadWall(localStorage.getItem('wallFeed'));
				newSelected = $("#background li:last-child");
			}
		}

		if(newSelected.length){
			lastSelected.removeClass("selected");
			newSelected.addClass("selected");
			$("#background").stop().scrollCenterORI(".selected", 300);
		}

	});


	function resumeAutoplay(){
		gotoRandom();
		window.randomPhotos = setInterval(gotoRandom, 5000); 
	}


	function pauseAutoplay(){
		/*const interval_id = window.setInterval(function(){}, Number.MAX_SAFE_INTEGER);
		for (let i = 1; i < interval_id; i++) {
		  window.clearInterval(i);
		}*/
	}

	for ( var i = 1, l = 36; i <= l; i++ ) {
		for ( var y = 1, r = 5; y <= r; y++ ) {
			var name = (femaleNames[Math.floor(Math.random()*femaleNames.length)]);
			$('.girls-list').append('<div class="item" data-pos="' + y + '" style="background:url(./girls/' + i + '.jpg) ' + (y * 238) + 'px 0px;"><div class="content"><button class="wanther">' + name + '</button></div></div>');
		}
	}

	var total = $('.girls-list .item').length,
	rand = Math.floor( Math.random() * total );

	$('.girls-list').slick({
		focusOnSelect: false,
		draggable: true,
		infinite: false,
		pauseOnHover: false,
		swipeToSlide: true,
		arrows: false,
		accessibility: false,
		initialSlide:rand,
		slidesToShow: 1,
		slidesToScroll: 1
	});	

	$(document).on("click", '.detailek',  function(){
		$(".detailek").toggleClass("zoom");
	})

	$(document).on("mouseover", '#toolbox li img',  function(){
		$(this).attr("src", $(this).data("over"));
	});	
	$(document).on("mouseout", '#toolbox li img',  function(){
		$(this).attr("src", $(this).data("out"));
	});	

	$(document).on("click", '.detail',  function(){
		var category = $(this).data("detail");

		$("#objects").removeClass("huba kozy kunda").addClass(category);
	})

	$(document).on("click", '#closezoom',  function(){
		$(".girls").removeClass("blowjob titties pussy").parent().find("button").fadeIn();
	})

	$(document).on("click", '#objects .detailek',  function(){
		$("#objects").fadeOut("slow");
		$("#background li").removeClass("zoom");
		$("#background").removeClass("zoomed");
	})
	
	var $carousel = $('.girls-list');
	$(document).on('keydown', function(e) {
		var lastSelected = $("#background li.selected");
		var newSelected;
		$("#toolbox").fadeOut("slow");
	
		if(e.keyCode == 37) { /* LEVA     */ 	
			newSelected = lastSelected.prev();
			if(newSelected.is(":first-child")()){
				loadWall(localStorage.getItem('wallFeed'));
			}
		}
		if(e.keyCode == 39) { /* PRAVA     */	
			newSelected = lastSelected.next();
			if(newSelected.is(":last-child")){
				loadWall(localStorage.getItem('wallFeed'));
			}
		}
		if(e.keyCode == 38) { /* NAHORU    */ 
			gotoRandom();
			return false; 	
		}
		if(e.keyCode == 40) { /* DOLU      */	 
			gobacktoRandom();
			return false;
		}
		if(e.keyCode == 13) { /* Prostredek */	
			var request = $.ajax({
				url: "/like.php",
				type: "POST",
				data: {"typeid" : "+", "fileid" : $("#background li.selected").data("id"), "userid": "PrdelniPrincezna" },
				dataType: "html"
			});


			$("#background li.selected").addClass("liked");

			setTimeout(function(){ 
				$("#background li.liked").addClass("small");
			}, 1500);


		}
		if(e.keyCode == 179) {
/* PLAY       */ resumeAutoplay();
		}
		if(e.keyCode == 166) {
/* ZPET       */ pauseAutoplay();	
		}
		if(e.keyCode == 93) {
/* MENICKO    */	
		}		
		if(e.keyCode == 33) { /* PAGEUP     */ 
			loadWall("liked");	
			return false;
		}	
		if(e.keyCode == 34) { /* PAGEDOWN   */ 
			loadWall("random");	
			return false;
		}	
		if(e.keyCode == 175) {
/* VOLUP      */	
		}	
		if(e.keyCode == 174) {
/* VOLDOWN    */	
		}	
		if(e.keyCode == 8) {
/* DELETE     */	
		}	



		if(newSelected.length){
			lastSelected.removeClass("selected");
			newSelected.addClass("selected");
			$("#background").stop().scrollCenterORI(".selected", 300);
		}
	});

	$(document).on("click", '#prevbitch',  function(){
		$carousel.slick('slickPrev');
	});

	$(document).on("click", '#nextbitch',  function(){
		$carousel.slick('slickNext');
	});


	$(document).on("click", '#nexttype',  function(e){
		var selected = $(".objects li.selectedTool")
			.removeClass("selectedTool")
			.next()
			.addClass("selectedTool");

		if(!selected.length){
			selected = $(".objects li").first().addClass("selectedTool");
		}

		$("#objects").removeClass("girls edge cutouts");


		if(selected.hasClass("girls")){
			$("#objects").addClass("girls");
		}
		if(selected.hasClass("edge")){
			initializeMagicEdge();
			$("#objects").addClass("edge");
		}
		if(selected.hasClass("cutouts")){
			$("#objects").addClass("cutouts");
		}		
	});	

	$(document).on("click", '.girls-list .item button',  function(){
		$(this).closest(".item").addClass("selected").css("opacity",1) ;
		var name = $(this).parent().parent().find("h3").html();
		var pict = $(this).parent().find("img").attr("src");
			$(".detail h3").html(name);
		var post = $(this).closest(".item").data("pos");
		var klon = $(this).closest(".item").clone();

		setTimeout(function(){ $('.girls-list .item:not(.slick-current)');
			$(".selected").addClass("selected2"); 
			$(".detail").addClass("visible");
			/*$('.girls-list').slick('slickRemove', null, null, true);
			$('.girls-list').slick('slickAdd',klon);*/

		}, 1000);
		setTimeout(function(){ 
			$(".detailek img").attr("src", pict);
			$(".detailek.kunda").addClass("loaduj load-"+post);
		}, 500);
	})	
	
	function getCssProperty(elmId, property){
		var elem = $(elmId + ".selected");
		return window.getComputedStyle(elem,null).getPropertyValue(property);
	}
	
	Array.prototype.remove = function(from, to) {
	  var rest = this.slice((to || from) + 1 || this.length);
	  this.length = from < 0 ? this.length + from : from;
	  return this.push.apply(this, rest);
	};

	$('.remove',$('#tools')).live('click',function(){
		var $this = $(this);
		var objid = $this.next().val();

		$this.parent().remove();

		var divwrapper = $('#'+objid).parent().parent();
		$('#'+objid).remove();

		var image_elem 		= $this.parent().find('img');
		var thumb_width 	= image_elem.attr('width');
		var thumb_height 	= image_elem.attr('height');
		var thumb_src 		= image_elem.attr('src');
		var origwidth   	=  image_elem.data('width');
		var origheight   	=  image_elem.data('height');
		var origwidth   	=  thumb_width;
		var origheight   	= thumb_height;
					  
		$('<img/>',{
			id 			: 	objid,
			src			: 	thumb_src,
			width		:	origwidth, 
			//height		:	thumb_height,
			className	:	'ui-widget-content'
		}).appendTo(divwrapper).resizable({
			handles	: 'se',
			stop	: resizestop 
		}).parent('.ui-wrapper').draggable({
			revert: 'invalid'
		});

		var index = exist_object(objid);
		data.images.remove(index);
	});
	
	function exist_object(id){
		for(var i = 0;i<data.images.length;++i){
			if(data.images[i].id == id)
				return i;
		}
		return -1;
	}

	function resizestop(event, ui) {
		var $this 		= $(this);
		var objid		= $this.find('.ui-widget-content').attr('id');
		var objwidth 	= ui.size.width;
		var objheight 	= ui.size.height;
	
		var index 		= exist_object(objid);
	
		if(index!=-1) {
			data.images[index].width 	= objwidth;
			data.images[index].height 	= objheight;
		}
	}

	$('#objects img').resizable({
		handles	: 'se',
		stop	: resizestop 
	}).parent('.ui-wrapper').draggable({
		revert	: 'invalid'
	});
	
	$('#objects .girls').resizable({
		handles	: 'se',
		stop	: resizestop 
	}).parent('.ui-wrapper').draggable({
		revert	: 'invalid'
	});
	


	$('#background').droppable({
		accept	: '#objects div, #tools div',
		drop	: function(event, ui) {
			var $this 		= $(this);

			++count_dropped_hits;
			var draggable_elem = ui.draggable;
			draggable_elem.css('z-index',count_dropped_hits);
			var objsrc 		= draggable_elem.find('.ui-widget-content').attr('src');
			var objwidth 	= parseFloat(draggable_elem.css('width'),10);
			var objheight 	= parseFloat(draggable_elem.css('height'),10);
			var origwidth   =  parseFloat(draggable_elem.find('.ui-widget-content').data('width'),10);
			var origheight  =  parseFloat(draggable_elem.find('.ui-widget-content').data('height'),10);
			var origwidth   =  origwidth
			var origheight  =  origheight
			var objtop		= ui.offset.top - $this.offset().top;
			var objleft		= ui.offset.left - $this.offset().left;
			var objid		= draggable_elem.find('.ui-widget-content').attr('id');
			var index 		= exist_object(objid);
			
			if(index!=-1) { 
				data.images[index].top 	= objtop;
				data.images[index].left = objleft;
			}
			else{	
				var newObject = { 
					'id' 		: objid,
					'src' 		: objsrc,
					'width' 	: origwidth,
					'height' 	: origheight,
					'top' 		: objtop,
					'left' 		: objleft,
					'rotation'  : '0'
				};
				data.images.push(newObject);
				
				$('<div/>',{
					className	:	'item'
				}).append(
					$('<div/>',{
						className	:	'thumb',
						html		:	'<img width="50" class="ui-widget-content" src="'+objsrc+'"></img>'
					})
				).append(
					$('<div/>',{
						className	:	'slider',
						html		:	'<span>Rotate</span><span class="degrees">0</span>'
					})
				).append(
					$('<a/>',{
						className	:	'remove'
					})
				).append(
					$('<input/>',{
						type		:	'hidden',
						value		:	objid		// keeps track of which object is associated
					})
				).appendTo($('#tools'));
			}
		}
	});

	$('#submit').bind('click',function(){
		var dataString  = JSON.stringify(data);
		$('#jsondata').val(dataString);
		$('#jsonform').submit();
	});

	function MouseWheelHandler(e)
	{

		var e = window.event || e; 
		var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));

		return false;
	}

	jQuery.fn.scrollCenter = function(elem, speed) {

		// this = #timepicker
		// elem = .active

		window.animating = true;
		var active = jQuery(this).find(elem); // find the active element
		//var activeWidth = active.width(); // get active width
		var activeWidth = active.width() / 2; // get active width center

		//alert(activeWidth)

		//var pos = jQuery('#timepicker .active').position().left; //get left position of active li
		// var pos = jQuery(elem).position().left; //get left position of active li
		//var pos = jQuery(this).find(elem).position().left; //get left position of active li
		var elpos = jQuery(this).scrollLeft(); // get current scroll position
		var elW = jQuery(this).width(); //get div width
		var pos = parseInt(active.data("left")) - (elW / 2) - activeWidth; //get left position of active li + center position
		//var divwidth = jQuery(elem).width(); //get div width
		pos = pos + elpos - elW / 2; // for center position if you want adjust then change this

		jQuery(this).animate({
			scrollLeft: pos
		}, speed == undefined ? 1000 : speed, function(){
			window.animating = false;
		});
		return this;
	};

	jQuery.fn.scrollCenterORI = function(elem, speed) {

		if(jQuery(elem).length){
			leftpozice =  jQuery(this).scrollLeft() - jQuery(this).offset().left + jQuery(elem).offset().left - 100;
			window.scrolled = jQuery(this).scrollLeft();

			jQuery(this).animate({
				scrollLeft: leftpozice
			}, speed == undefined ? 1000 : speed);
		}
		return this;
	};



	function initializeMagicEdge(){
	
		var a = new MagicEdge();
		a.setConfig({	
			width:"full",
			height:"full",
			zoomMultipliers: [0.1, 0.2, 0.4, 0.7, 1, 1.5, 2, 2.5, 3, 4, 6, 8, 10, 14, 20],
			saveNameSufix:"transparent-",
			saveAction: "download",
			ajaxUrl: "ajax.php",
			magicWandTolerance:5,
			magicWandBorderWidth:10,
			blurFilter: 0,
			featherFilter: 5,
			autoCrop: true
		});
		a.init(document.getElementById("edge"));
	}
	
	function gotoRandom(){
		$('#background li.selected').removeClass("selected");
		const selection = $('#background li');
		const randLi = selection[Math.floor(Math.random() * selection.length)];  
		$(randLi).addClass("selected");
		$("#background").stop().scrollCenterORI(randLi, 1000);
		window.lastRandom = window.newRandom;
		window.newRandom = randLi;

	}


	function gobacktoRandom(){
		if(window.lastRandom){
			$('#background li.selected').removeClass("selected");
			const selection = $('#background li');
			const randLi = window.lastRandom;
			$(randLi).addClass("selected");
			$("#background").stop().scrollCenterORI(randLi, 1000);
		}else{
			gotoRandom();
		}
	}

	window.addEventListener("message", function(event) {
		alert( "received: " + event.data );

	// can message back using event.source.postMessage(...)
	});


	function loadWall($loadwhat){
		localStorage.setItem('wallFeed', $loadwhat);
	
		$idecka = localStorage.getItem('idecka');


		localStorage.setItem('idecka', ($idecka + 50));

		var request2 = $.ajax({
			url:  "/loadWall.php?typeid="+$loadwhat+
							"&maxHeight="+window.innerHeight+
							"&maxWidth="+window.innerWidth,
			dataType: 'json',					
			method:"POST",
			contentType: 'application/json',
			data: JSON.stringify( { "typeid": $loadwhat } ),
			complete: function(jqXHR) {
		        if(jqXHR.readyState === 4) {
		       		$("#background li").remove();
					var response = $.parseJSON(jqXHR.responseText);
					var len = response.length;
					for(var i=0; i<len; i++){
						var idecko = response[i].idecko;
						var filename = response[i].filename;
						var height = response[i].height;
						var width = response[i].width;

						if($idecka != ""){ $idecka++; }else{ $idecka = 1; }

						var wallPaper = $('<li>');
							wallPaper.id = idecko;
							wallPaper.addClass("wallpaper");
							wallPaper.css("width", width + "px");
							wallPaper.css("height", height + "px");
							wallPaper.attr("data-bg", filename);
							wallPaper.html("<h2>"+$idecka+"<span>39107</span></h2>");

					    $("#background").append(wallPaper);
					}
		        }   

				$.lazyLoadXT.scrollContainer = '#background';
		        $(window).lazyLoadXT();
  				$("#background").stop().scrollCenterORI(".selected", 300);
		    }
		});
	}

    $('#marker').on('lazyshow', function () {
		loadWall('random');
        $(window).lazyLoadXT();
        $('#marker').lazyLoadXT({visibleOnly: false, checkDuplicates: false});
    });

	$(window).on('ajaxComplete', function() {
	  setTimeout(function() {
	    $(window).lazyLoadXT();
	  }, 50);
	});

	if(localStorage.getItem('wallFeed')){
	 	loadWall(localStorage.getItem('wallFeed'));
	}else{
		loadWall('random');
	}


	pauseAutoplay();
	var evt = window.document.createEvent('UIEvents'); 
	evt.initUIEvent('resize', true, false, window, 0); 
	window.dispatchEvent(evt);	
} ) ( jQuery );
/*	

setTimeout(function() { 
	var random = Math.floor(Math.random() * (('.girls-list .item').length - 0 + 1)) + 0;
	$('.girls-list').slick('slickGoTo', random);
}, 2500);
	}
	*/