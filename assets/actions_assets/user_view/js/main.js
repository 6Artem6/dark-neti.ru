
$(window).ready(function() {

	function check_user_tabs_size() {
		var t = $('#user-tabs');
		if (t.length) {
			if (t.width() < 635) {
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

	function check_badge_tabs_size() {
		var bg = $('#badge-tabs');
		if (bg.length) {
			if (bg.width() < 640) {
				if(bg.hasClass('btn-group')) {
					bg.addClass('btn-group-vertical');
					bg.removeClass('btn-group')
				}
			} else {
				if(bg.hasClass('btn-group-vertical')) {
					bg.addClass('btn-group')
					bg.removeClass('btn-group-vertical');
				}
			}
		}
	}

	$(window).resize(function() {
		check_badge_tabs_size();
		check_user_tabs_size();
	});

	check_badge_tabs_size();
	check_user_tabs_size();
});

if ($(window).prop('innerWidth') < 992) {
	var hash = $(location).prop('hash');
	if (hash == '#tabs') {
		offset = $('#tabs').offset().top;
		$("html").animate({ scrollTop: offset }, 125);
	}
}
