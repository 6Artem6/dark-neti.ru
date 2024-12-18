
$(window).ready(function() {
	function check_size() {
		w = window.innerWidth
		sb = $('#search-buttons');
		if (sb.width() < 400) {
			if(sb.hasClass('btn-group')) {
				sb.addClass('btn-group-vertical');
				sb.removeClass('btn-group')
			}
		} else {
			if(sb.hasClass('btn-group-vertical')) {
				sb.addClass('btn-group')
				sb.removeClass('btn-group-vertical');
			}
		}
	}
	$(window).resize(function() {
		check_size();
	});
	check_size();
});
