
var hash = null;
var url_parts = $(location).attr('pathname').split('/');
var question = url_parts[3];

if (url_parts.length == 5) {
	hash = url_parts.pop();
}

function set_position(hash, time = 250) {
	if (!hash) {
		return null;
	}
	var div = $('#'+hash);
	if (div.length == 0) {
		return null;
	}
	if (time) {
		setTimeout(function() {
			set_position(hash, 0);
		}, time);
	} else {
		offset = div.offset().top - 50;
		$("html").animate({ scrollTop: offset }, 250);
		div.animate({opacity: '0.75'})
		.animate({opacity: '1'});
	}
}

function check_id() {
	parts = hash.split('-');
	if (parts.length != 2) {
		return null;
	}
	id = parts[1];
	if (id != parseInt(id)) {
		return null;
	}
	return id;
}

function check_action() {
	parts = hash.split('-');
	if ((parts.length == 1) || (parts.length == 2)) {
		return parts[0];
	}
	return null;
}

function check_field() {
	div = $('#' + hash);
	if (div.length != 1) {
		return null;
	}
	return div;
}

function switch_button_aria(id) {
	button = $(`[aria-controls="${id}"]`);
	button.attr('aria-expanded', 'true');
}

function check_block() {
	if (!hash) {
		return false;
	}
	action = check_action();
	block_id = check_id();
	if (action == 'allAnswers') {
		set_position(hash);
		return true;
	} else if (action == 'comments') {
		div = $('#comments');
		div.addClass('show');
		switch_button_aria(hash);
		set_position(hash);
		return true;
	} else if ((action == 'answerComments') || (action == 'editAnswer')) {
		if (block_id == null) {
			return false;
		}
		if ((div = check_field()) == null) {
			return false;
		}
		div.addClass('show');
		switch_button_aria(hash);
		if (hash.startsWith('editAnswer-')) {
			edit_div = $('[id=' + hash + ']');
			if (edit_div.length) {
				switch_button_aria(edit_div.attr('id'));
			}
		}
		set_position(hash);
		return true;
	} else if ((action == 'comment') || (action == 'editComment')) {
		if (block_id == null) {
			return false;
		}
		if ((div = check_field()) == null) {
			return false;
		}
		if (div.closest('[id^=answerComments]').length == 1) {
			answer_div = div.closest('[id^=answerComments]');
			answer_div.addClass('show');
			var id = answer_div.attr('id').split('-')[1];
			switch_button_aria(answer_div.attr('id'));
			if (hash.startsWith('editComment-')) {
				edit_div = $('[id=' + hash + ']');
				if (edit_div.length) {
					switch_button_aria(edit_div.attr('id'));
				}
			}
		} else if (div.closest('#comments').length == 1) {
			div.closest('#comments').addClass('show');
			switch_button_aria('comments');
		} else {
			return false;
		}
		set_position(hash);
		return true;
	} else if (action == 'answer') {
		if ((div = check_field()) == null) {
			return false;
		}
		set_position(hash);
		return true;
	}
	return false
}

$('body').on('click', "[data-button-type='comments'][data-record-type='question']", function (e) {
	e.preventDefault();
	if ($(this).data('loaded') == true) {
		return 1;
	}
	var button = $(this);

	param = $('meta[name="csrf-param"]').attr("content");
	token = $('meta[name="csrf-token"]').attr("content");
	data = {};
	data[param] = token;
	id = button.data('id');
	data.id = id;
	var block = $('#comments');
	var div = block.find('.question-comment-list');
	var loader = block.find('.div-loader');
	loader.addClass('active');
	$.ajax({
		url: '/api/list/question-comments',
		type: 'POST',
		data: data,
	})
	.done(function(response) {
		loader.removeClass('active').hide();
		if (response.totalCount) {
			count = $('<span>', {text: response.totalCount, class: 'count'});
			text = $('<div>', {text: `Комментарии (${count}):`, class: 'h5 ms-3 mt-0'});
			div.append(text.prop('outerHTML'));
			if (response.items.length) {
				for (var i = 0; i < response.items.length; i++) {
					div.append(response.items[i]);
				}
				if (response.scripts) {
					script = $('<script>', {text: response.scripts});
					$('body').append(script.prop('outerHTML'));
				}
			}
		} else {
			text = $('<div>', {text: 'Комментариев пока нет.', class: 'h5 text-center'});
			div.append(text.prop('outerHTML'));
		}
	});
	button.data('loaded', true);
})

$('body').on('click', "[data-button-type='comments'][data-record-type='answer']", function (e) {
	e.preventDefault();
	if ($(this).data('loaded') == true) {
		return 1;
	}
	var button = $(this);

	param = $('meta[name="csrf-param"]').attr("content");
	token = $('meta[name="csrf-token"]').attr("content");
	data = {};
	data[param] = token;
	id = button.data('id');
	data.id = id;
	var block = button.closest('.comment-item');
	var div = block.find('.answer-comment-list');
	var loader = block.find('.div-loader');
	loader.addClass('active');
	$.ajax({
		url: '/api/list/answer-comments',
		type: 'POST',
		data: data,
	})
	.done(function(response) {
		loader.removeClass('active').hide();
		if (response.items.length) {
			for (var i = 0; i < response.items.length; i++) {
				div.append(response.items[i]);
			}
			if (response.scripts) {
				script = $('<script>', {text: response.scripts});
				$('body').append(script.prop('outerHTML'));
			}
		}
	});
	button.data('loaded', true);
})


$('#solves').data('page', 1);
$('#answers').data('page', 1);

function check_solves() {
	var feed = $('#solves');
	if (!feed.length) return;
	if ((feed.data('loading') != 1) &&
		(feed.data('empty') != 1) &&
		(feed.data('end') != 1)) {

		var loader = $('.div-loader');
		var page = feed.data('page');
		var div = feed.find('.answer-list');
		if (page) {
			page += 1;
			feed.data('loading', 1);
			loader.addClass('active');

			param = $('meta[name="csrf-param"]').attr("content");
			token = $('meta[name="csrf-token"]').attr("content");
			data = {};
			data[param] = token;
			data.id = question;
			query = {};
			query.page = page;
			$.ajax({
				type: 'POST',
				url: '/api/list/question-answer-helped?' + $.param(query),
				data: data
			})
			.done(function(response) {
				loader.removeClass('active');
				feed.data('loading', 0);
				if (response.is_empty) {
					feed.data('empty', 1);
					loader.hide();
					return 0;
				}
				if (response.is_end) {
					feed.data('end', 1);
					loader.hide();
				}
				if (response.items.length) {
					for (var i = 0; i < response.items.length; i++) {
						div.append(response.items[i]);
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

function check_answers() {
	var feed = $('#answers');
	if (!feed.length) return;
	if ((feed.data('loading') != 1) &&
		(feed.data('empty') != 1) &&
		(feed.data('end') != 1)) {

		var loader = $('.div-loader');
		var page = feed.data('page');
		var div = feed.find('.answer-list');
		if (page) {
			page += 1;
			feed.data('loading', 1);
			loader.addClass('active');

			param = $('meta[name="csrf-param"]').attr("content");
			token = $('meta[name="csrf-token"]').attr("content");
			data = {};
			data[param] = token;
			data.id = question;
			query = {};
			query.page = page;
			$.ajax({
				type: 'POST',
				url: '/api/list/question-answer?' + $.param(query),
				data: data
			})
			.done(function(response) {
				loader.removeClass('active');
				feed.data('loading', 0);
				if (response.is_empty) {
					feed.data('empty', 1);
					loader.hide();
					return 0;
				}
				if (response.is_end) {
					feed.data('end', 1);
					loader.hide();
				}
				if (response.items.length) {
					for (var i = 0; i < response.items.length; i++) {
						div.append(response.items[i]);
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

setTimeout(function() {
	check_solves();
	check_answers();
	check_block();
}, 500);
