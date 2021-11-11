$(function(){
	$(document).ready(function() {
		var i,
			size,
			color,
			width = $(window).width(),
			height = $(window).height();	

			if(height>975){ height=975; }


		/*- Summer Parallax -*/
		//$('#slider1_container').append('<div class="layer6" data-parallaxify-range="' + Math.round(50*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height: 975px;"></div>');
		$('#slider1_container').prepend('');
		$('#slider1_container').prepend('<div class="layer5" data-parallaxify-range="' + Math.round(80*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider1_container').prepend('<div class="layer4" data-parallaxify-range="' + Math.round(-60*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider1_container').prepend('<div class="layer3" data-parallaxify-range="' + Math.round(350*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider1_container').prepend('<div class="layer2" data-parallaxify-range="' + Math.round(-100*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		//$('#slider1_container').prepend('<div class="layer1" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider1_container').prepend('<div class="layer0" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"><h1>Ryze česká společnost</h1></div>');


		/*- Winter Parallax -*/
		//$('#slider3_container').prepend('<div class="layer5" data-parallaxify-range="' + Math.round(80*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		//$('#slider3_container').prepend('<div class="layer4" data-parallaxify-range="' + Math.round(-40*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider3_container').prepend('<div class="layer3" data-parallaxify-range="' + Math.round(-350*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider3_container').prepend('<div class="layer2" data-parallaxify-range="' + Math.round(-90*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider3_container').prepend('<div class="layer1" data-parallaxify-range="' + Math.round(100*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider3_container').prepend('<div class="layer0" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');

		/*- Night Parallax -*/
		$('#slider5_container').prepend('<div class="layer5" data-parallaxify-range="' + Math.round(80*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider5_container').prepend('<div class="layer4" data-parallaxify-range="' + Math.round(-60*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider5_container').prepend('<div class="layer3" data-parallaxify-range="' + Math.round(150*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider5_container').prepend('<div class="layer2" data-parallaxify-range="' + Math.round(120*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider5_container').prepend('<div class="layer1" data-parallaxify-range="' + Math.round(-100*Math.random()) + '" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');
		$('#slider5_container').prepend('<div class="layer0" style="bottom: 0px; left: 0px; width: 100%; height:' + height + 'px;"></div>');



		$('#slider1_container, #slider1_container .slides, #slider1_container .slides .slide').height(height + 'px');
		$('#slider1_container, #slider1_container .slides, #slider1_container .slides .slide').width(width + 'px');
		$('#slider2_container, #slider2_container .slides, #slider2_container .slides .slide').height(height + 'px');
		$('#slider2_container, #slider2_container .slides, #slider2_container .slides .slide').width(width + 'px');
		$('#slider3_container, #slider3_container .slides, #slider3_container .slides .slide').height(height + 'px');
		$('#slider3_container, #slider3_container .slides, #slider3_container .slides .slide').width(width + 'px');
		$('#slider4_container, #slider4_container .slides, #slider4_container .slides .slide').height(height + 'px');
		$('#slider4_container, #slider4_container .slides, #slider4_container .slides .slide').width(width + 'px');
		$('#slider5_container, #slider5_container .slides, #slider5_container .slides .slide').height(height + 'px');
		$('#slider5_container, #slider5_container .slides, #slider5_container .slides .slide').width(width + 'px');

		$('.jssora21l, .jssora21r').css('top', (height/2) + 'px');
		var claimheight = 400;
		$('#slider3_container .claim').css('top',((height/2)-(claimheight/2)) + 'px');
		var claimheight = 250;
		$('#slider4_container .claim').css('top',((height/2)-(claimheight/2)) + 'px');
		var navigatorheight = 350;
		$('#slider2_container div.navigator').css("top",(height-navigatorheight) + 'px');


		$.parallaxify({
			positionProperty: 'transform',
			responsive: true,
			motionType: 'natural',
			mouseMotionType: 'gaussian',
			motionAngleX: 80,
			motionAngleY: 80,
			alphaFilter: 0.5,
			adjustBasePosition: true,
			alphaPosition: 0.025,
			height: height,
			width:width
		});


	$( ".menuItem a, .godown" ).click(function(e) {
		e.preventDefault();
		var linkhref = $(this).attr("href");
		$(".menu li").removeClass("active");
		$(this).parent().addClass("active");
		$("html,body").animate({scrollTop: parseInt($(linkhref).offset().top)-50+'px'}, 500);
		$(".menu li a").removeClass("pulse");
		$(this).addClass("pulse");
	});

	$( ".goup, .logo a" ).click(function(e) {
		e.preventDefault();
		$("html,body").animate({scrollTop: 0}, 500);
	});

    $(window).scroll(function(){
		if(isScrolledIntoView('#slider1_container')) {
			$(".menu li").removeClass("active");
			$(".menu li.slider1").addClass("active");
		}else if(isScrolledIntoView('#slider2_container')) {
			$(".menu li").removeClass("active");
			$(".menu li.slider2").addClass("active");
		}else if(isScrolledIntoView('#slider3_container')) {
			$(".menu li").removeClass("active");
			$(".menu li.slider3").addClass("active");
		}else if(isScrolledIntoView('#slider4_container')) {
			$(".menu li").removeClass("active");
			$(".menu li.slider4").addClass("active");
		}else if(isScrolledIntoView('#slider5_container')) {
			$(".menu li").removeClass("active");
			$(".menu li.slider5").addClass("active");
		}

	});

	function isScrolledIntoView(elem){
		var docViewTop = $(window).scrollTop();
		var docViewBottom = docViewTop + $(window).height();

		var elemTop = $(elem).offset().top;
		var elemBottom = elemTop + $(elem).height();

		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}




	});
});