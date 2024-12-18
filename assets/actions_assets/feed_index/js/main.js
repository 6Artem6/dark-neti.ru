
$(document).ready(function(){
	$('.discipline-bell').hover(function() {
		if ($(this).data('action') == 'follow-discipline') {
			$(this).addClass('bi-bell-fill').removeClass('bi-bell');
		}
	}, function() {
		if ($(this).data('action') == 'follow-discipline') {
			$(this).addClass('bi-bell').removeClass('bi-bell-fill');
		}
	});

	$('#feed').data('page', 1);

	function check_feed() {
		var feed = $('#feed');
		if ((feed.data('loading') != 1) &&
			(feed.data('empty') != 1) &&
			(feed.data('end') != 1)) {
			var top = $(window).scrollTop() + $(window).height();
			var height = feed.offset().top + feed.height() - 750;
			if(top > height) {
				var loader = $('.div-loader');
				var page = feed.data('page');
				if (page) {
					page += 1;
					feed.data('loading', 1);
					loader.addClass('active');

					param = $('meta[name="csrf-param"]').attr("content");
					token = $('meta[name="csrf-token"]').attr("content");
					query = {};
					data = {};
					data[param] = token;
					query.page = page;
					if (feed.data('discipline')) {
						data.discipline = feed.data('discipline');
					}
					$.ajax({
						type: 'POST',
						url: '/api/list/feed?' + $.param(query),
						data: data
					})
					.done(function(response) {
						loader.removeClass('active');
						feed.data('loading', 0);
						if (response.is_empty) {
							feed.data('empty', 1);
							loader.hide();
							// feed.append('Записей не было найдено.');
							return 0;
						}
						if (response.is_end) {
							feed.data('end', 1);
							loader.hide();
							// feed.append('Вы достигли конца ленты.');
						}
						if (response.items.length) {
							for (var i = 0; i < response.items.length; i++) {
								feed.append(response.items[i]);
							}
							if (response.scripts) {
								script = $('<script>', {text: response.scripts});
								$('body').append(script.prop('outerHTML'));
							}
						}
						feed.data('page', page);
						return 1;
					});
				}
			}
		}
	}

	$(window).scroll(function() {
		check_feed();
	});
	setTimeout(function() {
		check_feed();
	}, 500);
});
