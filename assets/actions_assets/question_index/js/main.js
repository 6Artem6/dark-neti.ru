
$(window).ready(function() {

	function bindScriptsS2(id) {
		var field = $('#' + id);
		var elem = field.closest('div').children('.select2');
		elem.remove();

		var select2Id = field.data('select2Id');
		var krajeeSelect2 = field.data('krajeeSelect2');
		var s2Options = field.data('s2Options');
		$.when(field.select2(window[krajeeSelect2])).done(initS2Loading(select2Id, s2Options));
	}

	function bindScriptsKvdate(id) {
		var field = $('#' + id);
		var datepicker_source = field.data('datepicker-source');
		var krajee_kvdatepicker = field.data('krajee-kvdatepicker');
		$('#' + datepicker_source).kvDatepicker(window[krajee_kvdatepicker]);
	}

	function bindScripts() {
		bindScriptsS2('is_closed');
		bindScriptsS2('is_answered');
		bindScriptsS2('teacher');
		bindScriptsS2('tag_list');
		bindScriptsS2('sort');
		bindScriptsKvdate('date_from');
		bindScriptsKvdate('date_to');

		$('.field-clear-input').click(function () {
			add_clear_input($(this));
		});
		$('.field-clear-select').click(function () {
			add_clear_select($(this));
		});
	}

	function check_size() {
		w = window.innerWidth;
		sb = $('#search-buttons');
		min = $('#extra-div-min');
		max = $('#extra-div-max');

		if (sb.width() < 500) {
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

		if (w < 768) {
			if (min.children('#extraDiv').length == 0) {
				extra_div = $('#extraDiv');
				min.append(extra_div.prop('outerHTML'));
				extra_div.remove();
				bindScripts();
			}
		} else {
			if (max.children('#extraDiv').length == 0) {
				extra_div = $('#extraDiv');
				max.append(extra_div.prop('outerHTML'));
				extra_div.remove();
				bindScripts();
			}
		}

		extra_div = $('#extraDiv');
		if (extra_div.is(':hidden') && (extra_div.data('is-extra') == true)) {
			$('#extraDivButton').click();
		}

		l_is_answered = $('[for="is_answered"]');
		l_is_closed = $('[for="is_closed"]');
		if (l_is_answered.height() != l_is_closed.height()) {
			l_is_closed.height( l_is_answered.height() );
		}

		l_faculty_id = $('[for="faculty_id"]');
		l_type_id = $('[for="type_id"]');
		l_teachers = $('[for="teachers"]');
		lt_h = l_teachers.height();
		if ((lt_h != l_faculty_id.height()) &&
			(lt_h != l_type_id.height())) {
			l_faculty_id.height( lt_h );
			l_type_id.height( lt_h );
		}
	}

	function check_question_tabs_size() {
		var t = $('#question-tabs');
		if (t.length) {
			if (t.width() < 325) {
				t.css('font-size', '10px');
			} else if (t.width() < 400) {
				t.css('font-size', '11px');
			} else if (t.width() < 500) {
				t.css('font-size', '12px');
			} else {
				t.css('font-size', '14px');
			}
		}
	}

	$(window).resize(function() {
		check_question_tabs_size();
		check_size();
	});
	check_question_tabs_size();

	setTimeout(function () {
		check_size();
	}, 300)

	window.search = function() {
		data = $('.form-field').serializeArray();
		$(location).attr('href', '?' + $.param(data));
	}
});
