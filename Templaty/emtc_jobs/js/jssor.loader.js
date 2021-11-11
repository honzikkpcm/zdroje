		var	width = $(window).width();
		var	height = $(window).height();	
		if(height>975){ height=975; }

		
jssor_slider1_starter = function (containerId) {
	
	var options = {
		$FillMode: 1,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
		$AutoPlay: true,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
		$AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
		$PauseOnHover: 1,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

		$ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
		$SlideEasing: $JssorEasing$.$EaseOutQuint,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
		$SlideDuration: 800,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
		$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
		$SlideWidth: width,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
		$SlideHeight: height,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
		$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
		$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
		$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
		$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
		$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
		$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
		$BulletNavigatorOptions: {                          //[Optional] Options to specify and enable navigator or not
			$Class: $JssorBulletNavigator$,                 //[Required] Class to create navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
			$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
			$SpacingX: 14,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
			$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
			$Orientation: 1,                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			$Scale: false,                                  //Scales bullets navigator or not while slider scale
		},

		$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
			$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
			$ChanceToShow: 0,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
		}
	};

	var jssor_slider1 = new $JssorSlider$(containerId, options);

	//responsive code begin
	//you can remove responsive code if you don't want the slider scales while window resizes
	function ScaleSlider() {
		var bodyWidth = document.body.clientWidth;
		if (bodyWidth){
		//	jssor_slider1.$ScaleWidth(Math.min(bodyWidth, 1920));
		//	jssor_slider1.$ScaleHeight($(window).height());
		}else{
			$Jssor$.$Delay(ScaleSlider, 30);
		}
	}

	ScaleSlider();
	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);
	//responsive code end


/*
	function ScaleSlider() {

		var bodyWidth = document.body.clientWidth;
		console.log(bodyWidth);
		if (bodyWidth){
			$('#slider1_container, #slider1_container .slides, #slider1_container .slide').width(document.body.clientWidth+'px');
		}
	}


	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);

*/



};

jssor_slider2_starter = function (containerId) {
	var options = {
		$FillMode: 2,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
		$AutoPlay: false,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
		$AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
		$PauseOnHover: 1,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

		$ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
		$SlideEasing: $JssorEasing$.$EaseOutQuint,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
		$SlideDuration: 800,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
		$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
		$SlideWidth: width,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
		$SlideHeight: height,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
		$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
		$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
		$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
		$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
		$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
		$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
		$BulletNavigatorOptions: {                          //[Optional] Options to specify and enable navigator or not
			$Class: $JssorBulletNavigator$,                 //[Required] Class to create navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
			$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
			$SpacingX: 38,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
			$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
			$Orientation: 1,                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			$Scale: false,                                  //Scales bullets navigator or not while slider scale
		},

		$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
			$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
		}
	};

	var jssor_slider2 = new $JssorSlider$(containerId, options);


	//responsive code begin
	//you can remove responsive code if you don't want the slider scales while window resizes
	function ScaleSlider() {

		var bodyWidth = document.body.clientWidth;
		console.log(bodyWidth);
		if (bodyWidth){
			$('#slider1_container').width(document.body.clientWidth+'px');
		}
	}

	ScaleSlider();
	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);
	//responsive code end
};


jssor_slider3_starter = function (containerId) {
	var options = {
		$FillMode: 2,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
		$AutoPlay: true,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
		$AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
		$PauseOnHover: 1,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

		$ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
		$SlideEasing: $JssorEasing$.$EaseOutQuint,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
		$SlideDuration: 800,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
		$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
		$SlideWidth: width,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
		$SlideHeight: height,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
		$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
		$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
		$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
		$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
		$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
		$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
		$BulletNavigatorOptions: {                          //[Optional] Options to specify and enable navigator or not
			$Class: $JssorBulletNavigator$,                 //[Required] Class to create navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
			$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
			$SpacingX: 14,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
			$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
			$Orientation: 1,                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			$Scale: false,                                  //Scales bullets navigator or not while slider scale
		},

		$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
			$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
		}
	};

	var jssor_slider3 = new $JssorSlider$(containerId, options);


	//responsive code begin
	//you can remove responsive code if you don't want the slider scales while window resizes
	function ScaleSlider() {

		var bodyWidth = document.body.clientWidth;
		console.log(bodyWidth);
		if (bodyWidth){
			$('#slider1_container').width(document.body.clientWidth+'px');
		}
	}


	ScaleSlider();
	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);
	//responsive code end
};


jssor_slider4_starter = function (containerId) {
	var options = {
		$FillMode: 2,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
		$AutoPlay: true,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
		$AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
		$PauseOnHover: 1,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

		$ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
		$SlideEasing: $JssorEasing$.$EaseOutQuint,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
		$SlideDuration: 800,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
		$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
		$SlideWidth: width,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
		$SlideHeight: height,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
		$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
		$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
		$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
		$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
		$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
		$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
		$BulletNavigatorOptions: {                          //[Optional] Options to specify and enable navigator or not
			$Class: $JssorBulletNavigator$,                 //[Required] Class to create navigator instance
			$ChanceToShow: 0,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
			$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
			$SpacingX: 8,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
			$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
			$Orientation: 1,                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			$Scale: false,                                  //Scales bullets navigator or not while slider scale
		},

		$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
			$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
			$ChanceToShow: 0,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
		}
	};

	var jssor_slider4 = new $JssorSlider$(containerId, options);


	//responsive code begin
	//you can remove responsive code if you don't want the slider scales while window resizes
	function ScaleSlider() {

		var bodyWidth = document.body.clientWidth;
		console.log(bodyWidth);
		if (bodyWidth){
			$('#slider1_container').width(document.body.clientWidth+'px');
		}
	}


	ScaleSlider();
	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);
	//responsive code end
};


jssor_slider5_starter = function (containerId) {
	var options = {
		$FillMode: 2,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
		$AutoPlay: false,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
		$AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
		$PauseOnHover: 1,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

		$ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
		$SlideEasing: $JssorEasing$.$EaseOutQuint,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
		$SlideDuration: 800,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
		$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
		$SlideWidth: width,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
		$SlideHeight: height,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
		$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
		$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
		$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
		$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
		$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
		$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
		$BulletNavigatorOptions: {                          //[Optional] Options to specify and enable navigator or not
			$Class: $JssorBulletNavigator$,                 //[Required] Class to create navigator instance
			$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
			$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
			$SpacingX: 14,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
			$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
			$Orientation: 1,                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			$Scale: false,                                  //Scales bullets navigator or not while slider scale
		},

		$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
			$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
			$ChanceToShow: 1,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
			$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
		}
	};

	var jssor_slider5 = new $JssorSlider$(containerId, options);


	//responsive code begin
	//you can remove responsive code if you don't want the slider scales while window resizes
	function ScaleSlider() {

		var bodyWidth = document.body.clientWidth;
		console.log(bodyWidth);
		if (bodyWidth){
			$('#slider1_container').width(document.body.clientWidth+'px');
		}
	}


	ScaleSlider();
	$Jssor$.$AddEvent(window, "load", ScaleSlider);

	$Jssor$.$AddEvent(window, "resize", $Jssor$.$WindowResizeFilter(window, ScaleSlider));
	$Jssor$.$AddEvent(window, "orientationchange", ScaleSlider);
	//responsive code end
};		