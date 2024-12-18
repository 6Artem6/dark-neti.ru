
$(document).ready(function() {
	function switch_theme() {
		w = $(window).prop('innerWidth');
		if ($('#theme-mode').is(':checked')) {
			$('.logo-dark').hide();
			$('.logo-light').show();
		} else {
			$('.logo-dark').show();
			$('.logo-light').hide();
		}
		if (w < 695) {
			if ($('#theme-mode').is(':checked')) {
				$('.logo-lg.logo-light').hide();
				$('.logo-sm.logo-light').show();
			} else {
				$('.logo-lg.logo-dark').hide();
				$('.logo-sm.logo-dark').show();
			}
		} else {
			if ($('#theme-mode').is(':checked')) {
				$('.logo-lg.logo-light').show();
				$('.logo-sm.logo-light').hide();
			} else {
				$('.logo-lg.logo-dark').show();
				$('.logo-sm.logo-dark').hide();
			}
		}
	}
	$('.mode-switch #logo-dark, .mode-switch #logo-light').click(function (e) {
		e.preventDefault();
		$('#theme-mode').click();
		switch_theme();
	});
	$(window).resize(function(e) {
		e.preventDefault();
		switch_theme();
	});
	switch_theme();
});
