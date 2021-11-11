
jQuery(function() {
	function timeout() {
	    setTimeout(function () {
	    	jQuery(".carSelector").submit();
	        timeout();
	    }, 10000);
	}

	jQuery('.logo').on("click touchend", function(e) {
	    e.preventDefault();
	    jQuery("html").addClass("scrolling");	    
		jQuery('.navbar-nav a').removeClass("active");
	    jQuery('html, body').animate({
	        scrollTop: "0px"
	    }, 1000, function() { jQuery("html").removeClass("scrolling");  } );
	});	

	jQuery('.navbar-nav li:not(.smaller) a').on("click touchend", function(e) {
  	    e.preventDefault();
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery(this).addClass("active");
	    jQuery("html").addClass("scrolling");
	    jQuery('html, body').animate({
	        scrollTop: jQuery(jQuery(this).attr("href")).offset().top - 80 + "px"
	    }, 1000, function() { jQuery("html").removeClass("scrolling"); } );
	});

	jQuery('.how').on('inview', function(event, isInView) {
	  if (isInView && !jQuery("html").hasClass("scrolling")) {
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#jak-to-funguje"]').addClass("active");
	  }
	});

	jQuery('.map').on('inview', function(event, isInView) {
	  if (isInView && !jQuery("html").hasClass("scrolling")) {
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#nase-auta"]').addClass("active");
	  }
	});	

	jQuery('.aplications').on('inview', function(event, isInView) {
	  if (isInView && !jQuery("html").hasClass("scrolling")) {
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#aplikace"]').addClass("active");
	  }
	});		

	jQuery('.cars').on('inview', function(event, isInView) {
	  if (isInView && !jQuery("html").hasClass("scrolling")) {
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#ceny"]').addClass("active");
	  }
	});		

	jQuery('.contact').on('inview', function(event, isInView) {
	  if (isInView && !jQuery("html").hasClass("scrolling")) {
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#kontakty"]').addClass("active");
	  }
	});		

	jQuery('.tabs a').on("click touchend", function(e) {
	    e.preventDefault();
	    jQuery('.tabs a').removeClass("active");
	    jQuery(this).addClass("active");
	    jQuery('.tabs-content li').removeClass("active");	  	
	    jQuery('.tabs-content '+jQuery(this).attr("href")).addClass("active");
	});

	jQuery('.navbar-toggler').on("click", function(e) {
		jQuery(this).toggleClass("active");
		jQuery('#navbarNavDropdown').toggleClass("opened");
	});

	jQuery('.shader').on("click", function(e) {
		jQuery('html, body').removeClass("login");
	});


	jQuery('.cars .columns').slick({
	  dots: true,
	  infinite: true,
	  speed: 300,
	  slidesToShow: 4,
	  slidesToScroll: 4,
	  responsive: [
	    {
	      breakpoint: 1300,
	      settings: {
	        slidesToShow: 3,
	        slidesToScroll: 3,
	        infinite: true,
	        dots: true
	      }
	    },
	    {
	      breakpoint: 768,
	      settings: {
	        slidesToShow: 2,
	        slidesToScroll: 2
	      }
	    },
	    {
	      breakpoint: 480,
	      settings: {
	        slidesToShow: 1,
	        slidesToScroll: 1
	      }
	    }
	    // You can unslick at a given breakpoint now by adding:
	    // settings: "unslick"
	    // instead of a settings object
	  ]
	});

	function animateHowTo(){
        if(jQuery(".how").length){
            window.setTimeout(function(){jQuery(".how_register").addClass("animation");}, 500);
            window.setTimeout(function(){jQuery(".how_meet").addClass("animation");}, 1000);
            window.setTimeout(function(){jQuery(".how_quantity").addClass("animation");}, 1500);
            window.setTimeout(function(){jQuery(".how_advert").addClass("animation");}, 2000);
        }
    }

    animateHowTo();


	jQuery('select').selectBox();

	jQuery(window).on("resize", function () {
		if(jQuery(window).width() < 1280){
			if(!jQuery('.how_features').hasClass("slick-initialized")){
				jQuery('.how_features').slick({
					dots: true,
					infinite: true,
					adaptiveHeight: true,
					speed: 300,
					slidesToShow: 1,
					slidesToScroll: 1,
				});
			}
		}else{
			if(jQuery('.how_features').hasClass("slick-initialized")){
				jQuery('.how_features').slick('unslick');			
			}
		}	
	}).resize();

	jQuery('a[href="/Account/Login"]').on("click touchend", function(e) {
	    e.preventDefault();
	    jQuery("html").addClass("login");
	});

	jQuery('a[href="/kontaktovat"]').on("click touchend", function(e) {
	    e.preventDefault();
	    jQuery("html").addClass("scrolling");
	    jQuery('html, body').animate({
	        scrollTop: jQuery("#kontakty").offset().top - 80 + "px"
	    }, 1000, function() { jQuery("html").removeClass("scrolling"); } );

	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#kontakty"]').addClass("active");
	});

	jQuery('.cars a.button').on("click touchend", function(e) {
	    e.preventDefault();
	    jQuery("html").addClass("scrolling");
	    jQuery('html, body').animate({
	        scrollTop: jQuery("#map").offset().top - 80 + "px"
	    }, 1000, function() { jQuery("html").removeClass("scrolling"); } );
	    jQuery('.navbar-nav a').removeClass("active");	  	
	    jQuery('a[href="#nase-auta"]').addClass("active");
	});

	jQuery('.carSelect').on("change", function(e) {
		jQuery("form.carSelector").submit();
	});


	jQuery('#locateMe').on("click touchend", function(e) {
		e.preventDefault();
		var infoWindow = new google.maps.InfoWindow({map: map});
		
		// Try HTML5 geolocation.
		if (navigator.geolocation) {
		    navigator.geolocation.getCurrentPosition(function(position) {
		        var pos = {
		            lat: position.coords.latitude,
		            lng: position.coords.longitude
		        };

		        infoWindow.setPosition(pos);
		        infoWindow.setContent('Vaše lokalita');
		        //window.map.setCenter(pos);
		    }, function() {
		        handleLocationError(true, infoWindow, window.map.getCenter());
		    });
		} else {
		    // Browser doesn't support Geolocation
		    handleLocationError(false, infoWindow, window.map.getCenter());
		}
	});

	function handleLocationError(browserHasGeolocation, infoWindow, pos) {
	    infoWindow.setPosition(pos);
	    infoWindow.setContent(browserHasGeolocation ?
	                          'Omlouváme se, ale nepovedlo se nám Vás lokalizovat' :
	                          'Error: Your browser doesn\'t support geolocation.');
	}

	jQuery(".carSelector").submit(function(){
		var data = {
			"action": "test"
		};
		data = jQuery(this).serialize() + "&" + jQuery.param(data);
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: "http://crm.onlyfy.cz/Vehicle/GetGpsAvailableVehicles?filter=_all", //Relative or absolute path to response.php file
			data: data,
			success: function(data) {
				console.log(data);

				if (window.markersArray) {
					for (i in window.markersArray) {
						window.markersArray[i].setMap(null);
					}
					window.markersArray.length = 0;
				}

				var pointMarkerImage = new Array();
				var pointMarker = new Array();

				jQuery.each(data, function(i, item) {
					var location = item.split(",");

					pointMarkerImage[i] = {
					    url: "/wp-content/themes/only_fy/img/point.svg", // url
					    scaledSize: new google.maps.Size(41, 41), // scaled size
					    origin: new google.maps.Point(0,0), // origin
					    anchor: new google.maps.Point(0, 0) // anchor
					};

					pointMarker[i] = new google.maps.Marker({

						position: new google.maps.LatLng(location[0], location[1]),
						map: window.map,
						icon: pointMarkerImage[i],
						text: i
					});

					window.markersArray.push(pointMarker[i]);
					
					google.maps.event.addListener(pointMarker[i], 'click', function(){
						window.open("carLocations.php?car="+i, '_blank');
						win.focus();
					});
				});
			}
		});
		return false;
	});	

	timeout()
});

function initMap() {
	window.markersArray = [];
	var prague = {lat: 50.075345, lng: 14.454680};
	var defaultZoom = 11;
	var canZoom = false;
	var canDrag = false;

	if(jQuery(window).width() < 767){
		defaultZoom = 10;
		canZoom = true;
		canDrag = true;
	}

	if(jQuery(window).width() > 1150){
		defaultZoom = 11.5;
	}

	window.map = new google.maps.Map(document.getElementById('map'), {
		zoom: defaultZoom,
		center: prague,
		disableDefaultUI: true,
		navigationControl: false,
		mapTypeControl: false,
		scaleControl: canZoom,
		draggable: canDrag						
	});
	var polygonMask = new google.maps.Polygon({
	map:window.map,
	strokeColor: '#9b9898',
	strokeOpacity: 0.5,
	strokeWeight: 2,
	fillColor: '#ffffff',
	fillOpacity: 0.8,
	paths: [[new google.maps.LatLng(49.530508, 13.569552),
	new google.maps.LatLng(50.588472, 13.569552),
	new google.maps.LatLng(50.588472, 15.361696),
	new google.maps.LatLng(49.530508, 15.361696),
	new google.maps.LatLng(49.530508, 13.569552)],
	[new google.maps.LatLng(49.977397, 14.332201),
	new google.maps.LatLng(49.976595, 14.332502),
	new google.maps.LatLng(49.975923, 14.332566),
	new google.maps.LatLng(49.975519, 14.330162),
	new google.maps.LatLng(49.975303, 14.330033),
	new google.maps.LatLng(49.975064, 14.329625),
	new google.maps.LatLng(49.974477, 14.329279),
	new google.maps.LatLng(49.974545, 14.329942),
	new google.maps.LatLng(49.974213, 14.330112),
	new google.maps.LatLng(49.973852, 14.329074),
	new google.maps.LatLng(49.973128, 14.327771),
	new google.maps.LatLng(49.971929, 14.326794),
	new google.maps.LatLng(49.971463, 14.326859),
	new google.maps.LatLng(49.971028, 14.327057),
	new google.maps.LatLng(49.970704, 14.327492),
	new google.maps.LatLng(49.971049, 14.332095),
	new google.maps.LatLng(49.972146, 14.333468),
	new google.maps.LatLng(49.973747, 14.337804),
	new google.maps.LatLng(49.974692, 14.341001),
	new google.maps.LatLng(49.974782, 14.342740),
	new google.maps.LatLng(49.974685, 14.346131),
	new google.maps.LatLng(49.974105, 14.345447),
	new google.maps.LatLng(49.973414, 14.346698),
	new google.maps.LatLng(49.972528, 14.347140),
	new google.maps.LatLng(49.971931, 14.346059),
	new google.maps.LatLng(49.970892, 14.345537),
	new google.maps.LatLng(49.969476, 14.345264),
	new google.maps.LatLng(49.968768, 14.345289),
	new google.maps.LatLng(49.968088, 14.345056),
	new google.maps.LatLng(49.967003, 14.344204),
	new google.maps.LatLng(49.966296, 14.342909),
	new google.maps.LatLng(49.966025, 14.342154),
	new google.maps.LatLng(49.965755, 14.341292),
	new google.maps.LatLng(49.966248, 14.339503),
	new google.maps.LatLng(49.966853, 14.337908),
	new google.maps.LatLng(49.967668, 14.335045),
	new google.maps.LatLng(49.966511, 14.332366),
	new google.maps.LatLng(49.965188, 14.329935),
	new google.maps.LatLng(49.964492, 14.328354),
	new google.maps.LatLng(49.963438, 14.327331),
	new google.maps.LatLng(49.962117, 14.326974),
	new google.maps.LatLng(49.959863, 14.326055),
	new google.maps.LatLng(49.958271, 14.324986),
	new google.maps.LatLng(49.957113, 14.325525),
	new google.maps.LatLng(49.956465, 14.326319),
	new google.maps.LatLng(49.956121, 14.327350),
	new google.maps.LatLng(49.955517, 14.329629),
	new google.maps.LatLng(49.955083, 14.330537),
	new google.maps.LatLng(49.954655, 14.331410),
	new google.maps.LatLng(49.953331, 14.333800),
	new google.maps.LatLng(49.952656, 14.334902),
	new google.maps.LatLng(49.950655, 14.337163),
	new google.maps.LatLng(49.948450, 14.337539),
	new google.maps.LatLng(49.948505, 14.341200),
	new google.maps.LatLng(49.948092, 14.341396),
	new google.maps.LatLng(49.947567, 14.343032),
	new google.maps.LatLng(49.947582, 14.343922),
	new google.maps.LatLng(49.947764, 14.344802),
	new google.maps.LatLng(49.948210, 14.346734),
	new google.maps.LatLng(49.948111, 14.348274),
	new google.maps.LatLng(49.948136, 14.349825),
	new google.maps.LatLng(49.948152, 14.352123),
	new google.maps.LatLng(49.947786, 14.353522),
	new google.maps.LatLng(49.948682, 14.358765),
	new google.maps.LatLng(49.947520, 14.360455),
	new google.maps.LatLng(49.948313, 14.362637),
	new google.maps.LatLng(49.948788, 14.364069),
	new google.maps.LatLng(49.949434, 14.365366),
	new google.maps.LatLng(49.949360, 14.365749),
	new google.maps.LatLng(49.949612, 14.366195),
	new google.maps.LatLng(49.949854, 14.366463),
	new google.maps.LatLng(49.950187, 14.367020),
	new google.maps.LatLng(49.950792, 14.366993),
	new google.maps.LatLng(49.951321, 14.367170),
	new google.maps.LatLng(49.951024, 14.369743),
	new google.maps.LatLng(49.950432, 14.371541),
	new google.maps.LatLng(49.949799, 14.372047),
	new google.maps.LatLng(49.949113, 14.372072),
	new google.maps.LatLng(49.947851, 14.373110),
	new google.maps.LatLng(49.947653, 14.374920),
	new google.maps.LatLng(49.946667, 14.376880),
	new google.maps.LatLng(49.949256, 14.379764),
	new google.maps.LatLng(49.949215, 14.381628),
	new google.maps.LatLng(49.949213, 14.384247),
	new google.maps.LatLng(49.949190, 14.385086),
	new google.maps.LatLng(49.949573, 14.386098),
	new google.maps.LatLng(49.949703, 14.386878),
	new google.maps.LatLng(49.949549, 14.388136),
	new google.maps.LatLng(49.948080, 14.388980),
	new google.maps.LatLng(49.947601, 14.388694),
	new google.maps.LatLng(49.947088, 14.388741),
	new google.maps.LatLng(49.946574, 14.389045),
	new google.maps.LatLng(49.945878, 14.388859),
	new google.maps.LatLng(49.945183, 14.389059),
	new google.maps.LatLng(49.944951, 14.389738),
	new google.maps.LatLng(49.944471, 14.390718),
	new google.maps.LatLng(49.944042, 14.390860),
	new google.maps.LatLng(49.943848, 14.391625),
	new google.maps.LatLng(49.943350, 14.392518),
	new google.maps.LatLng(49.941924, 14.395405),
	new google.maps.LatLng(49.942317, 14.396135),
	new google.maps.LatLng(49.942992, 14.396567),
	new google.maps.LatLng(49.943736, 14.397044),
	new google.maps.LatLng(49.944836, 14.397011),
	new google.maps.LatLng(49.945988, 14.396473),
	new google.maps.LatLng(49.947352, 14.395911),
	new google.maps.LatLng(49.949606, 14.395162),
	new google.maps.LatLng(49.951943, 14.394799),
	new google.maps.LatLng(49.954712, 14.394824),
	new google.maps.LatLng(49.956881, 14.395239),
	new google.maps.LatLng(49.959001, 14.396202),
	new google.maps.LatLng(49.960140, 14.396896),
	new google.maps.LatLng(49.961057, 14.397611),
	new google.maps.LatLng(49.963168, 14.398559),
	new google.maps.LatLng(49.970697, 14.400566),
	new google.maps.LatLng(49.970601, 14.402166),
	new google.maps.LatLng(49.969428, 14.403616),
	new google.maps.LatLng(49.968979, 14.404304),
	new google.maps.LatLng(49.968600, 14.405143),
	new google.maps.LatLng(49.967607, 14.407832),
	new google.maps.LatLng(49.967222, 14.408746),
	new google.maps.LatLng(49.966231, 14.409931),
	new google.maps.LatLng(49.965629, 14.411443),
	new google.maps.LatLng(49.965419, 14.413308),
	new google.maps.LatLng(49.965192, 14.414420),
	new google.maps.LatLng(49.964711, 14.415014),
	new google.maps.LatLng(49.963859, 14.416545),
	new google.maps.LatLng(49.963315, 14.419521),
	new google.maps.LatLng(49.963689, 14.421739),
	new google.maps.LatLng(49.963858, 14.425102),
	new google.maps.LatLng(49.964169, 14.427409),
	new google.maps.LatLng(49.966640, 14.430006),
	new google.maps.LatLng(49.967602, 14.433766),
	new google.maps.LatLng(49.968234, 14.437612),
	new google.maps.LatLng(49.971211, 14.442832),
	new google.maps.LatLng(49.973031, 14.444498),
	new google.maps.LatLng(49.973210, 14.448133),
	new google.maps.LatLng(49.971975, 14.455014),
	new google.maps.LatLng(49.970795, 14.462024),
	new google.maps.LatLng(49.972402, 14.462876),
	new google.maps.LatLng(49.974396, 14.465016),
	new google.maps.LatLng(49.980695, 14.465803),
	new google.maps.LatLng(49.981085, 14.473922),
	new google.maps.LatLng(49.983156, 14.474720),
	new google.maps.LatLng(49.985517, 14.481298),
	new google.maps.LatLng(49.985648, 14.486905),
	new google.maps.LatLng(49.987507, 14.486833),
	new google.maps.LatLng(49.987627, 14.484345),
	new google.maps.LatLng(49.989844, 14.484775),
	new google.maps.LatLng(49.992401, 14.483875),
	new google.maps.LatLng(49.994874, 14.495507),
	new google.maps.LatLng(49.997514, 14.507096),
	new google.maps.LatLng(49.995334, 14.508348),
	new google.maps.LatLng(49.993313, 14.509102),
	new google.maps.LatLng(49.992943, 14.508492),
	new google.maps.LatLng(49.993490, 14.508155),
	new google.maps.LatLng(49.993487, 14.507644),
	new google.maps.LatLng(49.992216, 14.508205),
	new google.maps.LatLng(49.992256, 14.509939),
	new google.maps.LatLng(49.992594, 14.513574),
	new google.maps.LatLng(49.994459, 14.513396),
	new google.maps.LatLng(49.994840, 14.515560),
	new google.maps.LatLng(49.995224, 14.515934),
	new google.maps.LatLng(49.995705, 14.515714),
	new google.maps.LatLng(49.995836, 14.517041),
	new google.maps.LatLng(49.995022, 14.517587),
	new google.maps.LatLng(49.994705, 14.517795),
	new google.maps.LatLng(49.994488, 14.518026),
	new google.maps.LatLng(49.994609, 14.518326),
	new google.maps.LatLng(49.994723, 14.518650),
	new google.maps.LatLng(49.994876, 14.518841),
	new google.maps.LatLng(49.995780, 14.518433),
	new google.maps.LatLng(49.996232, 14.518207),
	new google.maps.LatLng(49.996161, 14.519050),
	new google.maps.LatLng(49.997028, 14.519151),
	new google.maps.LatLng(49.998060, 14.524576),
	new google.maps.LatLng(50.000592, 14.529011),
	new google.maps.LatLng(50.004553, 14.525863),
	new google.maps.LatLng(50.008061, 14.521799),
	new google.maps.LatLng(50.010554, 14.528605),
	new google.maps.LatLng(50.011567, 14.534835),
	new google.maps.LatLng(50.010063, 14.540087),
	new google.maps.LatLng(50.008601, 14.542008),
	new google.maps.LatLng(50.008874, 14.544132),
	new google.maps.LatLng(50.007877, 14.549925),
	new google.maps.LatLng(50.009083, 14.553787),
	new google.maps.LatLng(50.011604, 14.554988),
	new google.maps.LatLng(50.011683, 14.563225),
	new google.maps.LatLng(50.009743, 14.563564),
	new google.maps.LatLng(50.009284, 14.568191),
	new google.maps.LatLng(50.007484, 14.568861),
	new google.maps.LatLng(50.009399, 14.572434),
	new google.maps.LatLng(50.012897, 14.575801),
	new google.maps.LatLng(50.015482, 14.581678),
	new google.maps.LatLng(50.010834, 14.581245),
	new google.maps.LatLng(50.011025, 14.586729),
	new google.maps.LatLng(50.008798, 14.589302),
	new google.maps.LatLng(50.009769, 14.593934),
	new google.maps.LatLng(50.008955, 14.594445),
	new google.maps.LatLng(50.007436, 14.595294),
	new google.maps.LatLng(50.009631, 14.601974),
	new google.maps.LatLng(50.007634, 14.602303),
	new google.maps.LatLng(50.002508, 14.602917),
	new google.maps.LatLng(50.002188, 14.604317),
	new google.maps.LatLng(50.002649, 14.606945),
	new google.maps.LatLng(50.001697, 14.609284),
	new google.maps.LatLng(50.000399, 14.608124),
	new google.maps.LatLng(49.998906, 14.610097),
	new google.maps.LatLng(49.999286, 14.611210),
	new google.maps.LatLng(49.999770, 14.612834),
	new google.maps.LatLng(49.998531, 14.614711),
	new google.maps.LatLng(49.997487, 14.619150),
	new google.maps.LatLng(49.998595, 14.619598),
	new google.maps.LatLng(49.998376, 14.621346),
	new google.maps.LatLng(49.998846, 14.621490),
	new google.maps.LatLng(49.998337, 14.623643),
	new google.maps.LatLng(49.996813, 14.627037),
	new google.maps.LatLng(49.996175, 14.628520),
	new google.maps.LatLng(49.995704, 14.629454),
	new google.maps.LatLng(49.996173, 14.634106),
	new google.maps.LatLng(49.995413, 14.636989),
	new google.maps.LatLng(49.995324, 14.639204),
	new google.maps.LatLng(49.994658, 14.640161),
	new google.maps.LatLng(49.998822, 14.646712),
	new google.maps.LatLng(50.001317, 14.642756),
	new google.maps.LatLng(50.005040, 14.639015),
	new google.maps.LatLng(50.005635, 14.638151),
	new google.maps.LatLng(50.006388, 14.639737),
	new google.maps.LatLng(50.006185, 14.642654),
	new google.maps.LatLng(50.006452, 14.642939),
	new google.maps.LatLng(50.006609, 14.643824),
	new google.maps.LatLng(50.005901, 14.644738),
	new google.maps.LatLng(50.004956, 14.645277),
	new google.maps.LatLng(50.004829, 14.645411),
	new google.maps.LatLng(50.005321, 14.645636),
	new google.maps.LatLng(50.005753, 14.646473),
	new google.maps.LatLng(50.008768, 14.649648),
	new google.maps.LatLng(50.004253, 14.657910),
	new google.maps.LatLng(50.006377, 14.660698),
	new google.maps.LatLng(50.007453, 14.662048),
	new google.maps.LatLng(50.008281, 14.662516),
	new google.maps.LatLng(50.008750, 14.661954),
	new google.maps.LatLng(50.009329, 14.661821),
	new google.maps.LatLng(50.010709, 14.662835),
	new google.maps.LatLng(50.011979, 14.662374),
	new google.maps.LatLng(50.012368, 14.661453),
	new google.maps.LatLng(50.012814, 14.661583),
	new google.maps.LatLng(50.013266, 14.662703),
	new google.maps.LatLng(50.013674, 14.663397),
	new google.maps.LatLng(50.013387, 14.665301),
	new google.maps.LatLng(50.012757, 14.666019),
	new google.maps.LatLng(50.013538, 14.667197),
	new google.maps.LatLng(50.013501, 14.668522),
	new google.maps.LatLng(50.016167, 14.669161),
	new google.maps.LatLng(50.018727, 14.669631),
	new google.maps.LatLng(50.018998, 14.668148),
	new google.maps.LatLng(50.022479, 14.664956),
	new google.maps.LatLng(50.027511, 14.658358),
	new google.maps.LatLng(50.032721, 14.657521),
	new google.maps.LatLng(50.037407, 14.658594),
	new google.maps.LatLng(50.040604, 14.667606),
	new google.maps.LatLng(50.046998, 14.655418),
	new google.maps.LatLng(50.045455, 14.644690),
	new google.maps.LatLng(50.053171, 14.638767),
	new google.maps.LatLng(50.058020, 14.644775),
	new google.maps.LatLng(50.062705, 14.656017),
	new google.maps.LatLng(50.066949, 14.672752),
	new google.maps.LatLng(50.071910, 14.697983),
	new google.maps.LatLng(50.080949, 14.709991),
	new google.maps.LatLng(50.089331, 14.707916),
	new google.maps.LatLng(50.096400, 14.696899),
	new google.maps.LatLng(50.103490, 14.678984),
	new google.maps.LatLng(50.103575, 14.666501),
	new google.maps.LatLng(50.105506, 14.658015),
	new google.maps.LatLng(50.115534, 14.657521),
	new google.maps.LatLng(50.124138, 14.659281),
	new google.maps.LatLng(50.122854, 14.647694),
	new google.maps.LatLng(50.124690, 14.635507),
	new google.maps.LatLng(50.131003, 14.630358),
	new google.maps.LatLng(50.130422, 14.621433),
	new google.maps.LatLng(50.128381, 14.606330),
	new google.maps.LatLng(50.133101, 14.598098),
	new google.maps.LatLng(50.138140, 14.595366),
	new google.maps.LatLng(50.145576, 14.588528),
	new google.maps.LatLng(50.149886, 14.594078),
	new google.maps.LatLng(50.156746, 14.599686),
	new google.maps.LatLng(50.156541, 14.593896),
	new google.maps.LatLng(50.155456, 14.590166),
	new google.maps.LatLng(50.154386, 14.585453),
	new google.maps.LatLng(50.150926, 14.582549),
	new google.maps.LatLng(50.149066, 14.565411),
	new google.maps.LatLng(50.159425, 14.561349),
	new google.maps.LatLng(50.166063, 14.547730),
	new google.maps.LatLng(50.160945, 14.535829),
	new google.maps.LatLng(50.168302, 14.532627),
	new google.maps.LatLng(50.175098, 14.528968),
	new google.maps.LatLng(50.175496, 14.514785),
	new google.maps.LatLng(50.171896, 14.507017),
	new google.maps.LatLng(50.174370, 14.498348),
	new google.maps.LatLng(50.170686, 14.478693),
	new google.maps.LatLng(50.170521, 14.463158),
	new google.maps.LatLng(50.160185, 14.460068),
	new google.maps.LatLng(50.158205, 14.436035),
	new google.maps.LatLng(50.155016, 14.428825),
	new google.maps.LatLng(50.153421, 14.428310),
	new google.maps.LatLng(50.153064, 14.423761),
	new google.maps.LatLng(50.152665, 14.420457),
	new google.maps.LatLng(50.151036, 14.420865),
	new google.maps.LatLng(50.150001, 14.421927),
	new google.maps.LatLng(50.149044, 14.419196),
	new google.maps.LatLng(50.148455, 14.415256),
	new google.maps.LatLng(50.147868, 14.409765),
	new google.maps.LatLng(50.147079, 14.406762),
	new google.maps.LatLng(50.147620, 14.403200),
	new google.maps.LatLng(50.148000, 14.400647),
	new google.maps.LatLng(50.147256, 14.399800),
	new google.maps.LatLng(50.142748, 14.397248),
	new google.maps.LatLng(50.141541, 14.392636),
	new google.maps.LatLng(50.145508, 14.387875),
	new google.maps.LatLng(50.147281, 14.378010),
	new google.maps.LatLng(50.147757, 14.368140),
	new google.maps.LatLng(50.147775, 14.364235),
	new google.maps.LatLng(50.142843, 14.357584),
	new google.maps.LatLng(50.141119, 14.355697),
	new google.maps.LatLng(50.140202, 14.358100),
	new google.maps.LatLng(50.137433, 14.354667),
	new google.maps.LatLng(50.135499, 14.356556),
	new google.maps.LatLng(50.129690, 14.357328),
	new google.maps.LatLng(50.129316, 14.358916),
	new google.maps.LatLng(50.124507, 14.355590),
	new google.maps.LatLng(50.120892, 14.358734),
	new google.maps.LatLng(50.118093, 14.361164),
	new google.maps.LatLng(50.116364, 14.361006),
	new google.maps.LatLng(50.115939, 14.350627),
	new google.maps.LatLng(50.116938, 14.340803),
	new google.maps.LatLng(50.116557, 14.332801),
	new google.maps.LatLng(50.116366, 14.325195),
	new google.maps.LatLng(50.115170, 14.320533),
	new google.maps.LatLng(50.119745, 14.317859),
	new google.maps.LatLng(50.123904, 14.315664),
	new google.maps.LatLng(50.128734, 14.315940),
	new google.maps.LatLng(50.127187, 14.311271),
	new google.maps.LatLng(50.128945, 14.307735),
	new google.maps.LatLng(50.130263, 14.300594),
	new google.maps.LatLng(50.127237, 14.300766),
	new google.maps.LatLng(50.125063, 14.295359),
	new google.maps.LatLng(50.121225, 14.296432),
	new google.maps.LatLng(50.117654, 14.290445),
	new google.maps.LatLng(50.116309, 14.287795),
	new google.maps.LatLng(50.116297, 14.284753),
	new google.maps.LatLng(50.118713, 14.278254),
	new google.maps.LatLng(50.113647, 14.260757),
	new google.maps.LatLng(50.114746, 14.256643),
	new google.maps.LatLng(50.112983, 14.256134),
	new google.maps.LatLng(50.109846, 14.245148),
	new google.maps.LatLng(50.112020, 14.241371),
	new google.maps.LatLng(50.111125, 14.238281),
	new google.maps.LatLng(50.109247, 14.237938),
	new google.maps.LatLng(50.107647, 14.239483),
	new google.maps.LatLng(50.107398, 14.236822),
	new google.maps.LatLng(50.106447, 14.233432),
	new google.maps.LatLng(50.105284, 14.230621),
	new google.maps.LatLng(50.104289, 14.228185),
	new google.maps.LatLng(50.104341, 14.225922),
	new google.maps.LatLng(50.102662, 14.225063),
	new google.maps.LatLng(50.101051, 14.226694),
	new google.maps.LatLng(50.101788, 14.230771),
	new google.maps.LatLng(50.101605, 14.234526),
	new google.maps.LatLng(50.101184, 14.237262),
	new google.maps.LatLng(50.103616, 14.249960),
	new google.maps.LatLng(50.099767, 14.258368),
	new google.maps.LatLng(50.098943, 14.253475),
	new google.maps.LatLng(50.096769, 14.255148),
	new google.maps.LatLng(50.096343, 14.261134),
	new google.maps.LatLng(50.087540, 14.260866),
	new google.maps.LatLng(50.086222, 14.268456),
	new google.maps.LatLng(50.087325, 14.272938),
	new google.maps.LatLng(50.081488, 14.276037),
	new google.maps.LatLng(50.081213, 14.283767),
	new google.maps.LatLng(50.077330, 14.287803),
	new google.maps.LatLng(50.076910, 14.289413),
	new google.maps.LatLng(50.075709, 14.289961),
	new google.maps.LatLng(50.074475, 14.290449),
	new google.maps.LatLng(50.073472, 14.290092),
	new google.maps.LatLng(50.073461, 14.287075),
	new google.maps.LatLng(50.074339, 14.282000),
	new google.maps.LatLng(50.073346, 14.281180),
	new google.maps.LatLng(50.073124, 14.277851),
	new google.maps.LatLng(50.071361, 14.278247),
	new google.maps.LatLng(50.073399, 14.274840),
	new google.maps.LatLng(50.072545, 14.266098),
	new google.maps.LatLng(50.071292, 14.257865),
	new google.maps.LatLng(50.068021, 14.258212),
	new google.maps.LatLng(50.067432, 14.259157),
	new google.maps.LatLng(50.064603, 14.258686),
	new google.maps.LatLng(50.062803, 14.253987),
	new google.maps.LatLng(50.062454, 14.247861),
	new google.maps.LatLng(50.059855, 14.247330),
	new google.maps.LatLng(50.057256, 14.248516),
	new google.maps.LatLng(50.056758, 14.250420),
	new google.maps.LatLng(50.056978, 14.255621),
	new google.maps.LatLng(50.054442, 14.257192),
	new google.maps.LatLng(50.055489, 14.262183),
	new google.maps.LatLng(50.052458, 14.264335),
	new google.maps.LatLng(50.053340, 14.268029),
	new google.maps.LatLng(50.054304, 14.270305),
	new google.maps.LatLng(50.054180, 14.271443),
	new google.maps.LatLng(50.053071, 14.271368),
	new google.maps.LatLng(50.050670, 14.272704),
	new google.maps.LatLng(50.049415, 14.270754),
	new google.maps.LatLng(50.049090, 14.268234),
	new google.maps.LatLng(50.048873, 14.268176),
	new google.maps.LatLng(50.048488, 14.269735),
	new google.maps.LatLng(50.047745, 14.270600),
	new google.maps.LatLng(50.046767, 14.271076),
	new google.maps.LatLng(50.046003, 14.271099),
	new google.maps.LatLng(50.044408, 14.270123),
	new google.maps.LatLng(50.043500, 14.269764),
	new google.maps.LatLng(50.043397, 14.268934),
	new google.maps.LatLng(50.042660, 14.269349),
	new google.maps.LatLng(50.041276, 14.269742),
	new google.maps.LatLng(50.039726, 14.270307),
	new google.maps.LatLng(50.037492, 14.271870),
	new google.maps.LatLng(50.036003, 14.273789),
	new google.maps.LatLng(50.035187, 14.275133),
	new google.maps.LatLng(50.034384, 14.276873),
	new google.maps.LatLng(50.032921, 14.280204),
	new google.maps.LatLng(50.031844, 14.282076),
	new google.maps.LatLng(50.031454, 14.282473),
	new google.maps.LatLng(50.029158, 14.285498),
	new google.maps.LatLng(50.027461, 14.288543),
	new google.maps.LatLng(50.025708, 14.293134),
	new google.maps.LatLng(50.025032, 14.296212),
	new google.maps.LatLng(50.024735, 14.296850),
	new google.maps.LatLng(50.024631, 14.297617),
	new google.maps.LatLng(50.023577, 14.297585),
	new google.maps.LatLng(50.023515, 14.297789),
	new google.maps.LatLng(50.023764, 14.297919),
	new google.maps.LatLng(50.023958, 14.298263),
	new google.maps.LatLng(50.024003, 14.298845),
	new google.maps.LatLng(50.024118, 14.298936),
	new google.maps.LatLng(50.024433, 14.299226),
	new google.maps.LatLng(50.024400, 14.299998),
	new google.maps.LatLng(50.024665, 14.300320),
	new google.maps.LatLng(50.024631, 14.301070),
	new google.maps.LatLng(50.024575, 14.304609),
	new google.maps.LatLng(50.024079, 14.309283),
	new google.maps.LatLng(50.023609, 14.315799),
	new google.maps.LatLng(50.023304, 14.315699),
	new google.maps.LatLng(50.021896, 14.316200),
	new google.maps.LatLng(50.020833, 14.316408),
	new google.maps.LatLng(50.019571, 14.316232),
	new google.maps.LatLng(50.018016, 14.315651),
	new google.maps.LatLng(50.015378, 14.314932),
	new google.maps.LatLng(50.014348, 14.314700),
	new google.maps.LatLng(50.014040, 14.314370),
	new google.maps.LatLng(50.013499, 14.312982),
	new google.maps.LatLng(50.013133, 14.312438),
	new google.maps.LatLng(50.012660, 14.312681),
	new google.maps.LatLng(50.012341, 14.312996),
	new google.maps.LatLng(50.011850, 14.313067),
	new google.maps.LatLng(50.011360, 14.312667),
	new google.maps.LatLng(50.010447, 14.312196),
	new google.maps.LatLng(50.009480, 14.311703),
	new google.maps.LatLng(50.008501, 14.311393),
	new google.maps.LatLng(50.007238, 14.311066),
	new google.maps.LatLng(50.007273, 14.308936),
	new google.maps.LatLng(50.008095, 14.306707),
	new google.maps.LatLng(50.009132, 14.305254),
	new google.maps.LatLng(50.011400, 14.303249),
	new google.maps.LatLng(50.011826, 14.300782),
	new google.maps.LatLng(50.010941, 14.300432),
	new google.maps.LatLng(50.007792, 14.300062),
	new google.maps.LatLng(50.004418, 14.300418),
	new google.maps.LatLng(50.004068, 14.299327),
	new google.maps.LatLng(50.003036, 14.297918),
	new google.maps.LatLng(50.002573, 14.295959),
	new google.maps.LatLng(50.002254, 14.295043),
	new google.maps.LatLng(50.000621, 14.297430),
	new google.maps.LatLng(50.000107, 14.299803),
	new google.maps.LatLng(49.999851, 14.301762),
	new google.maps.LatLng(49.998922, 14.300789),
	new google.maps.LatLng(49.997865, 14.300839),
	new google.maps.LatLng(49.997504, 14.302471),
	new google.maps.LatLng(49.997530, 14.304533),
	new google.maps.LatLng(49.997541, 14.306037),
	new google.maps.LatLng(49.996016, 14.307582),
	new google.maps.LatLng(49.995446, 14.307368),
	new google.maps.LatLng(49.994541, 14.309343),
	new google.maps.LatLng(49.993785, 14.310094),
	new google.maps.LatLng(49.994427, 14.311950),
	new google.maps.LatLng(49.994766, 14.313935),
	new google.maps.LatLng(49.993956, 14.314230),
	new google.maps.LatLng(49.993049, 14.314612),
	new google.maps.LatLng(49.992255, 14.315031),
	new google.maps.LatLng(49.992116, 14.315187),
	new google.maps.LatLng(49.991980, 14.315358),
	new google.maps.LatLng(49.991703, 14.315720),
	new google.maps.LatLng(49.991415, 14.316098),
	new google.maps.LatLng(49.991123, 14.316433),
	new google.maps.LatLng(49.990404, 14.317193),
	new google.maps.LatLng(49.990001, 14.317233),
	new google.maps.LatLng(49.989651, 14.317634),
	new google.maps.LatLng(49.988882, 14.318037),
	new google.maps.LatLng(49.989897, 14.321675),
	new google.maps.LatLng(49.991480, 14.326413),
	new google.maps.LatLng(49.992662, 14.330715),
	new google.maps.LatLng(49.993182, 14.332314),
	new google.maps.LatLng(49.993587, 14.334009),
	new google.maps.LatLng(49.993984, 14.335296),
	new google.maps.LatLng(49.993784, 14.336325),
	new google.maps.LatLng(49.993245, 14.337655),
	new google.maps.LatLng(49.992582, 14.337782),
	new google.maps.LatLng(49.991559, 14.338508),
	new google.maps.LatLng(49.990644, 14.342536),
	new google.maps.LatLng(49.989332, 14.338875),
	new google.maps.LatLng(49.988683, 14.335215),
	new google.maps.LatLng(49.987273, 14.334675),
	new google.maps.LatLng(49.986271, 14.335914),
	new google.maps.LatLng(49.985715, 14.337821),
	new google.maps.LatLng(49.984912, 14.338603),
	new google.maps.LatLng(49.983558, 14.339041),
	new google.maps.LatLng(49.982464, 14.337613),
	new google.maps.LatLng(49.981711, 14.338362),
	new google.maps.LatLng(49.980757, 14.339604),
	new google.maps.LatLng(49.978402, 14.335105),
	new google.maps.LatLng(49.978117, 14.335284),
	new google.maps.LatLng(49.977602, 14.334440),
	new google.maps.LatLng(49.977486, 14.333267),
	new google.maps.LatLng(49.977397, 14.332201)]]});
}