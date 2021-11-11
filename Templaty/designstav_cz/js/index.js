$('#toggle').click(function(event) {
   $(this).toggleClass('active');
   $('#overlay').toggleClass('open');
   event.stopPropagation();    
});
$('#toggleNav').click(function(event) {
   $(this).toggleClass('active');
   $('#overlay').toggleClass('open');
   event.stopPropagation();    
});

$(document).click(function(event) {
    $('#toggle.active').click();
    $('#toggle').removeClass("active");
    $('#toggleNav.active').click();
    $('#toggleNav').removeClass("active");
});



/* Sticky Navigation */
$(function(){
  
	createSticky(jQuery("#menu"));

});

function createSticky(sticky) {
	
	if (typeof sticky !== "undefined") {

		var	pos = sticky.offset().top,
				win = jQuery(window);
			
		win.on("scroll", function() {
    		win.scrollTop() >= pos ? sticky.addClass("fixed") : sticky.removeClass("fixed");      
		});			
	}
}







// Swiper Carousel  

var swiper = new Swiper('.swiper-container.you', {
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
        speed:1200,        
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: 4500,
        autoHeight:true
});

var swiper = new Swiper('.swiper-container.logos', {
            speed: 800,
            paginationClickable: true,
            slidesPerView: '3',
            centeredSlides: true,
            spaceBetween: 10,
            loop: true,
            autoplay: 4000,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
    breakpoints: {
        
            768: {
                slidesPerView: 3,
                spaceBetween: 5
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 1
            }
    }
});

var swiper = new Swiper('.swiper-container.corp', {
            speed: 800,
            slidesPerView: '8',
            centeredSlides: true,
            loop: true,
            spaceBetween: 1,
    breakpoints: {
        
            768: {
                slidesPerView: 3,
                spaceBetween: 5
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 20,
                spaceBetween: 1
            }
    }
});

var swiper = new Swiper('.swiper-container.projects', {
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        speed: 400,
        slidesPerView: 5,
        paginationClickable: true,
        spaceBetween: 10,
        loop: true,
        centeredSlides: true,
        slidesPerSlide: 'auto',
        autoplay: 4000,
        breakpoints: {
        
            768: {
                slidesPerView: 3,
                spaceBetween: 5
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 'auto',
                spaceBetween: 10,
                 autoplay: 500,
                slidesPerSlide: 'auto',
            }
    }
});

    var swiper = new Swiper('.swiper-container.turnkey-cover', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        // Disable preloading of all images
        preloadImages: false,
        loop: true,
        autoplay: 4000,
        // Enable lazy loading
        lazyLoading: true
    });

    var swiper = new Swiper('.swiper-container.service-gallery', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        // Disable preloading of all images
        preloadImages: false,
        autoplay: 5000,
        loop: true,
        effect: 'fade'
    });


// Counter 

    jQuery(document).ready(function( $ ) {
        $('.counter').counterUp({
            delay: 10,
            time: 4500
        });
    });

//Match Height 
$(window).load(function(){
    $('.blog-grid, .blog-grid:nth-child(7n+1) img').matchHeight();
  $('.blog-grid, .blog-grid:nth-child(7n+4) img').matchHeight();    
  $('.blog-grid').matchHeight();
    $('.point-story ').matchHeight();
    $('.icon-area, .info-area').matchHeight();
    
  //$('.blog-grid:nth-child(7n+3), .blog-grid:nth-child(7n+4)').matchHeight(); 
  //$('.blog-grid:nth-child(7n+3), .blog-grid:nth-child(7n+4)').matchHeight();     
  //$('.blog-grid-inner').matchHeight();
 //$('.blog-grid:nth-child(odd) .the-content, .blog-grid:nth-child(even) .the-content').matchHeight();
  $('.carousel .swiper-slide, .contain-carousel img').matchHeight(); 
})

//PopUp Gallery
        var swiper = new Swiper('.swiper-container.carousel', {
            pagination: '.swiper-pagination',
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            speed: 800,
            paginationClickable: true,
            slidesPerView: '2',
            centeredSlides: true,
            spaceBetween: 10,
            loop: true,
            autoplay: 4000,
            setWrapperSize:true,
              breakpoints: {

            1024: {
                slidesPerView: 'auto',
                spaceBetween: 10,
                 autoplay: 1000,
                slidesPerSlide: 'auto',
                speed: 1200
                }
                  
                  
            }
        });



//Reg Script

$(function() {
    $('h2').each(function(i, elem) {
        $(elem).html(function(i, html) {
            return html.replace(/(®)/, "<sup>$1</sup>");
        });
    });
        $('a').each(function(i, elem) {
        $(elem).html(function(i, html) {
            return html.replace(/(®)/, "<sup>$1</sup>");
        });
    });
});


$('a[href=#top]').click(function () {
    $('body,html').animate({
        scrollTop: 0
    }, 600);
    return false;
});

$(window).scroll(function () {
    if ($(this).scrollTop() > 50) {
        $('.totop').fadeIn();
    } else {
        $('.totop').fadeOut();
    }
});


//Accordion Overlay
  $('#accordion').find('.accordion-toggle').click(function(){

	    //Expand or collapse this panel
	    $(this).next().slideToggle('fast');

	    //changes arrow 
	    if( $(this).find('.arrow').hasClass('arrowUp')){
	    	$(this).find('.arrow').removeClass('arrowUp').addClass('arrowDown');
	    } else {
	    	$('#accordion').find('.arrow').removeClass('arrowUp').addClass('arrowDown');
	    	$(this).find('.arrow').removeClass('arrowDown').addClass('arrowUp');
	 	}
 	  	//Hide the other panels
     	$(".accordion-content").not($(this).next()).slideUp('fast');
      
    });


//Fit Vid Initiate

$(document).ready(function(){
  // Initiate FitVid.js
  $(".video-container-pop").fitVids();

  // Iframe/player variables
  var iframe = $('#video')[0];
  var player = $f(iframe);

  // Open on play
  $('.play').click(function(){
    $('.overlay-pop').css('left', 0)
    $('.overlay-pop').addClass('show')
    player.api("play");
  })
  
  

  // Closes on click outside
  $('.overlay-pop').click(function(){
    $('.overlay-pop').removeClass('show')
    setTimeout(function() {
      $('.overlay-pop').css('left', '-100%')
    }, 300);
    player.api("pause");
  })
 
});
