var vterina = 1000;
var minuta = vterina * 60;
var hodina = minuta * 60;
var den = hodina * 24;
var rok = den * 365.24219;

var slova = {
	roku: ["rok", "roky", "let"],
	dnu: ["den", "dny", "dnů"],
	hodin: ["hodina", "hodiny", "hodin"],
	minut: ["minuta", "minuty", "minut"],
	vterin: ["vteřina", "vteřiny", "vteřin"],
	bodu: ["bod", "body", "bodů"]
};

var searchPlaceholder  = (document.documentElement.clientWidth < 1000) ? "Hledat" : "Hledat na nova.cz";

function sklonovani(pocet, co) {
	if (pocet == 1) return slova[co][0];
	if (pocet < 5 && pocet > 0) return slova[co][1];
	return slova[co][2];
}

function odpocet(el) {
	var konec = new Date(el.getAttribute("data-konec"));
	var ted = new Date();
	var rozdil = konec - ted;
	if (rozdil < vterina) {
		el.innerHTML = el.getAttribute("data-hlaska");
		return;
	}
	var zbyva = {
		roku: Math.floor(rozdil / rok),
		dnu: Math.floor(rozdil % rok / den),
		hodin: Math.floor((rozdil % den) / hodina),
		minut: Math.floor((rozdil % hodina) / minuta),
		vterin: Math.floor((rozdil % minuta) / vterina)
	}

	var vypis = el.getAttribute("data-zbyva");
	for (co in zbyva) {
		var pocet = zbyva[co];
		if (pocet > 0) vypis += " " + pocet + "&nbsp;" + sklonovani(pocet, co);

	}

	el.innerHTML = vypis;
	setTimeout(function() {
	  odpocet(el); 
	}, vterina);
}

function toggleMenu(){
	document.getElementById("js_menu_toggler").classList.toggle("active");
	document.getElementById("js_menu").classList.toggle("active");
}

function change_class(id) {
	$(id).val($(id).val() == searchPlaceholder ? '' : $(id).value);
	$(id).className="input_text_focus";
}

function check_empty() {
	if($('#header_search_input').val() == "" || $('#header_search_input').val() == searchPlaceholder) {
		window.location.replace("http://tv.nova.cz/hledat/");
		return false;
	} else {
		return true;
	};
}

$(document).ready(function () {
	$('.quiz-sortable').sortable({
		cursor: 'move',
		update: function (event, ui) {
			var numbers = $('.quiz-picture[data-rel]').map(function () {
				return parseInt($(this).attr('data-rel'));
			});
			//console.log(numbers);
		}
	});

	$(".quiz-selectable").on("click", ".quiz-picture", function(){
		$(this).toggleClass("selected");
	});

	$('#header_search_input').val(searchPlaceholder);

	window.novaMenuDropdownTimeout = undefined;

	$('#nova_menu .shows').hover(function(){
		$('#drop_submenu').slideDown('slow');
		clearTimeout( window.novaMenuDropdownTimeout );
	}, function(){
		clearTimeout( window.novaMenuDropdownTimeout );
		window.novaMenuDropdownTimeout = setTimeout( function(){ $('#drop_submenu').slideUp('slow'); }, 1000 );
	});

	$('#nova_menu').on("click touchend", ".shows", function(){
		$('#drop_submenu').toggleClass('visible');
	});

	$('#nova_menu').on("click touchend", "#nav-expand-trigger", function(){
		$('#nav-expand').toggleClass('checked');
	});

	$('input[type=email]').on("keyup blur touchend", function( event ) {
	    if( !$(this).val() ) {
	        $(this).addClass('empty');
	        $(this).removeClass('not-empty');    	
	    }else{
	        $(this).addClass('not-empty');
	        $(this).removeClass('empty');    	
	    }
	});
});


/*(function() {
	var $scope = $('.help');

	if ($scope.length === 0) {
		return;
	}

	var Help = function(){
		this.container = $('.help__slides');
		this.slides = this.container.find('li');
		this.init();
	}

	Help.prototype = {

		init: function() {
			this.container.css('width',(this.slides.length * 100)+'%');
			this.container.find('li:first-child').addClass('active');
			this.resize();
			this.redrawPaging();
		},

		nextSlide: function() {
			var activeSlide = parseInt($scope.find('.active .help__number').html());

			if((activeSlide+1)<=this.slides.length){
				this.container.css('margin-left','-' + (activeSlide * 100) + '%');
				this.container.find('.active').removeClass('active');
				this.container.find('li:nth-child('+ (activeSlide + 1) + ')').addClass('active');
			}else{
				this.container.css('margin-left',0);
				this.container.find('.active').removeClass('active');
				this.container.find('li:first-child()').addClass('active');
			}

			this.resize();
			this.redrawPaging();
		},

		prevSlide: function() {
			var activeSlide = parseInt($scope.find('.active .help__number').html());

			if((activeSlide-1)>0){
				this.container.css('margin-left','-' + ((activeSlide-2) * 100) + '%');
				this.container.find('.active').removeClass('active');
				this.container.find('li:nth-child(' + (activeSlide - 1) + ')').addClass('active');
			}else{
				this.container.css('margin-left','-' + ((this.slides.length-1) * 100) + '%');
				this.container.find('.active').removeClass('active');
				this.container.find('li:nth-child(' + this.slides.length + ')').addClass('active');
			}

			this.resize();
			this.redrawPaging();
		},

		resize: function(){
			this.container.css('height',($scope.find('.active').height()) + 'px');
		},

		redrawPaging: function(){
			$scope.find('.page').html(parseInt($scope.find('.active .help__number').html())+'/'+this.slides.length);
		}

	}

	$scope.on('click', '.help__button', function(event) {
		event.preventDefault();
		$scope.toggleClass('active');
	});

	$scope.on('click', '.icon-arrow-blue-right', function(event) {
		event.preventDefault();
		Help.nextSlide();
	});

	$scope.on('click', '.icon-arrow-blue-left', function(event) {
		event.preventDefault();
		Help.prevSlide();
	});

	$scope.keydown(function(e) {
		if(e.keyCode==39){ Help.nextSlide(); }
		if(e.keyCode==37){ Help.prevSlide(); }
	});

	var Help = new Help;
})();
*/
