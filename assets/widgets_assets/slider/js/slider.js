
$(document).ready(function(){
	$('.slider').show();
	$('.slider').slick({
		prevArrow: '<button class="slick-prev slick-arrow"></button>',
		nextArrow: '<button class="slick-next slick-arrow"></button>',
		variableWidth: true,
		dots: false,
		respondTo: 'min',
		autoplay: true,
		autoplaySpeed: 10 * 1000,
	});

	function check_buttons() {
		$('.slick-arrow').css('display', '');
	}
	$(window).resize(function(e) {
		check_buttons();
	});
	check_buttons();
});
