
$(window).ready(function() {

	function check_discipline_tabs_size() {
		var t = $('#discipline-tabs');
		if (t.length) {
			if (t.width() < 760) {
				t.find('.icon-text').each(function () {
					$(this).hide();
				});
				t.css('font-size', '18px');
			} else {
				t.css('font-size', '12px');
				t.find('.icon-text').each(function () {
					$(this).show();
				});
			}
		}
	}

	$(window).resize(function() {
		check_discipline_tabs_size();
	});
	check_discipline_tabs_size();

});

if ($(window).prop('innerWidth') < 992) {
	var hash = $(location).prop('hash');
	if (hash == '#tabs') {
		offset = $('#tabs').offset().top;
		$("html").animate({ scrollTop: offset }, 125);
	}
}
