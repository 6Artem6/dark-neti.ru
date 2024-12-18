$(document).ready(function() {
	var l = document.querySelectorAll('.scroll-body').length;
	for (var i = 0; i < l; i++) {
		Scrollbar.init(document.querySelectorAll('.scroll-body')[i]);
	}

	function prepare_buttons() {
		$('body').on('click', 'a[data-button=action], button[data-button=action]', function(e) {
			e.preventDefault();
			send_button_data($(this));
		});
	}

	window.prepare_button = function(button) {
		if (button.attr('data-button') == 'action') {
			button.click(function(e) {
				e.preventDefault();
				send_button_data($(this));
			});
		}
	}

	$('.toast[role=alert]:not(#alertMessage)').each(function(e) {
		$(this).toast('show');
	});

	$('body').on('click', "a.nav-link[href^='#']", function (e) {
		e.preventDefault();
	});

	window.send_button_data = function(button) {
		if (button.attr('disabled')) {
			return false;
		}
		var action = button.data('action');
		var url  = `/api/user/${action}`;
		var id = button.data('id');
		if (id !== undefined) {
			url  = `${url}/${id}`;
		}
		if (action) {
			param = $('meta[name="csrf-param"]').attr("content");
			token = $('meta[name="csrf-token"]').attr("content");
			data = {};
			data[param] = token;
			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				success: function(response) {
					if (response.message) {
						display_alert(response.message, (response.status ? 'flash-info': 'flash-error'));
					}
					if (response.status) {
						change_button_click(button, action);
					}
				},
				error: function(response) {
					display_alert(response.message, 'flash-error');
				}
			});
		}
	}

	$('body').on('click', '.field-clear-input', function (e) {
		add_clear_input($(this));
	});

	$('body').on('click', '.field-clear-select', function (e) {
		add_clear_select($(this));
	});

	window.add_clear_input = function(button) {
		field = button.closest('div').children('input');
		if (field.val()) {
			field.val(null);
			field.trigger('change');
		}
	}

	window.add_clear_select = function(button) {
		field = button.closest('div').children('select');
		if ($.isArray(field.val())) {
			if (field.val().length) {
				field.val([]);
				field.trigger('change');
			}
		} else {
			if (field.val()) {
				field.val(null);
				field.trigger('change');
			}
		}
	}

	function check_clear_input(button) {
		var clear = button.closest('.input-group').find('.bi');
		if ($.isArray(button.val())) {
			if (button.val().length) {
				clear.removeClass('text-gray').addClass('text-dark');
			} else {
				clear.removeClass('text-dark').addClass('text-gray');
			}
		} else {
			if (button.val()) {
				clear.removeClass('text-gray').addClass('text-dark');
			} else {
				clear.removeClass('text-dark').addClass('text-gray');
			}
		}
	}

	$('body').on('change', '.form-field', function(e) {
		e.preventDefault();
		check_clear_input($(this));
	});

	$('.form-field').each(function() {
		check_clear_input($(this));
	});

	function change_button_click(button, action) {
		if (action == 'follow-question') {
			set_active(button, 'bi-bell', 'Вы подписаны', 'Отписаться от вопроса');
			button.data('action', 'unfollow-question');
		} else if (action == 'unfollow-question') {
			set_inactive(button, 'bi-bell', 'Подписаться', 'Подписаться на вопрос');
			button.data('action', 'follow-question');
		} else if (action == 'follow-user') {
			set_active(button, 'bi-bell', 'Вы подписаны', 'Отписаться от пользователя');
			button.data('action', 'unfollow-user');
		} else if (action == 'unfollow-user') {
			set_inactive(button, 'bi-bell', 'Подписаться', 'Подписаться на пользователя');
			button.data('action', 'follow-user');
		} else if (action == 'follow-discipline') {
			if (button.hasClass('discipline-bell')) {
				set_active(button, 'bi-bell', null, 'Отписаться от предмета');
			} else {
				set_active(button, 'bi-bell', 'Вы подписаны', 'Отписаться от предмета');
			}
			button.data('action', 'unfollow-discipline');
		} else if (action == 'unfollow-discipline') {
			if (button.hasClass('discipline-bell')) {
				set_inactive(button, 'bi-bell', null, 'Подписаться на предмет');
			} else {
				set_inactive(button, 'bi-bell', 'Подписаться', 'Подписаться на предмет');
			}
			button.data('action', 'follow-discipline');
		} else if (action == 'add-like-answer') {
			set_active(button, 'bi-hand-thumbs-up', null, 'Убрать отметку');
			button.data('action', 'remove-like-answer');
		} else if (action == 'remove-like-answer') {
			set_inactive(button, 'bi-hand-thumbs-up', null, 'Отметить полезным');
			button.data('action', 'add-like-answer');
		} else if (action == 'add-like-comment') {
			set_active(button, 'bi-hand-thumbs-up', null, 'Убрать отметку');
			button.data('action', 'remove-like-comment');
		} else if (action == 'remove-like-comment') {
			set_inactive(button, 'bi-hand-thumbs-up', null, 'Отметить полезным');
			button.data('action', 'add-like-comment');
		} else if (action == 'answer-helped') {
			set_active(button, 'bi-check-circle', null, 'Убрать отметку');
			button.data('action', 'answer-not-helped');
			check = $('<span>', {
				class: 'bi bi-check-circle-fill p-2 text-success helped',
				style: 'font-size: 20px;',
				'data-toggle': 'tooltip',
				title: 'Ответ решил вопрос'
			}).tooltip();
			button.closest('.comment-item').find('.avatar').append(check);
		} else if (action == 'answer-not-helped') {
			set_inactive(button, 'bi-check-circle', null, 'Поставить отметку');
			button.data('action', 'answer-helped');
			button.closest('.comment-item').find('.avatar .helped').remove();
		} else if (action == 'remove-notification') {
			dec_notification(button);
		} else if (action == 'open') {
			window.location.reload();
		} else if (action == 'close') {
			window.location.reload();
		} else if (action == 'question-delete') {
			document.location.reload();
		} else if (action == 'question-restore') {
			document.location.reload();
		} else if (action == 'answer-delete') {
			document.location.reload();
		} else if (action == 'answer-restore') {
			document.location.reload();
		} else if (action == 'comment-delete') {
			document.location.reload();
		} else if (action == 'comment-restore') {
			document.location.reload();
		}
	}

	function set_active(elem, bi = null, text = null, title = null) {
		elem.removeClass('btn-outline-light');
		elem.addClass('btn-light');
		if (bi !== null) {
			elem.find('.bi').removeClass(bi).addClass(bi + '-fill');
		}
		if (text !== null) {
			elem.find('.icon-text').text(text);
		}
		if (title !== null) {
			change_tooltip(elem, title)
		}
		if (elem.find('.count').length) {
			inc_count(elem.find('.count'));
		}
	}

	function set_inactive(elem, bi = null, text = null, title = null) {
		elem.removeClass('btn-light');
		elem.addClass('btn-outline-light');
		if (bi !== null) {
			elem.find('.bi').removeClass(bi + '-fill').addClass(bi);
		}
		if (text !== null) {
			elem.find('.icon-text').text(text);
		}
		if (title !== null) {
			change_tooltip(elem, title)
		}
		if (elem.find('.count').length) {
			dec_count(elem.find('.count'));
		}
	}

	function change_tooltip(elem, title) {
		elem.tooltip('dispose');
		elem.attr('title', title);
		elem.tooltip();
	}

	function inc_count(elem) {
		if (elem.length == 1) {
			count = parseInt(elem.text());
			count++;
			elem.text(count);
		}
	}

	function dec_count(elem) {
		if (elem.length == 1) {
			count = parseInt(elem.text());
			count--;
			elem.text(count);
		}
	}

	function display_alert(message, color = 'flash-info', reload = false) {
		alert = $('#alertMessage');
		alert.find('.toast-body').html(message);
		alert.find('rect').attr('class', color);
		alert.toast('show');
		if (reload) {
			document.location.reload();
		}
	}

	function delete_div(button) {
		var id = button.data('div-id');
		var delteDiv = $('#' + id + '');
		delteDiv.remove();
	}

	function set_delete() {
		$('body').on('click', ".btn-delete", function (e) {
			e.preventDefault();
			if (!$(this).data('button')) {
				delete_div($(this));
			}
		});
	}

	set_delete();

	function set_share() {
		$('body').on('click', ".btn-share-link", function (e) {
			e.preventDefault();
			var id = $(this).attr('id');
			var toastDiv = $('[data-share-id=' + id + ']');
			// toastDiv.toast('show');
			var shareField = toastDiv.children('.toast-body').children('input');
			shareField.select();
			navigator.clipboard.writeText(shareField.val());
			shareField.select(false);
			$(this).tooltip('hide');
			display_alert('Ссылка скопирована!', 'flash-info');
		});
	}

	set_share();

	function dec_notification(button) {
		id = button.data('id');
		div_last = button.closest('#messages-last');
		div_all = button.closest('#messages-all');
		if (div_last.length) {
			dec_notification_last(button);
			div_all = $('#messages-all');
			if (div_all.length) {
				all_button = div_all.find(`[data-action='remove-notification'][data-id='${id}']`);
				dec_notification_all(all_button);
			}
		} else if (div_all.length) {
			dec_notification_all(button);
			div_last = $('#messages-last');
			all_button = div_last.find(`[data-action='remove-notification'][data-id='${id}']`);
			dec_notification_last(all_button);
		}

	}

	function dec_notification_last(button) {
		div_last = button.closest('#messages-last');
		if (div_last.length) {
			div = button.closest('#notifDiv');
			if (div.length) {
				delete_div(button);
				count_text_block = div.find('#notif-count');
				count_block = count_text_block.find('.count');
				count = count_text_block.data('count');
				if (count) {
					count--;
					count_text_block.data('count', count);
					count_block.text(count);
				}
				if (count) {
					$('#notifCircle').show();
					if (!div_last.find('.message-block-last').length) {
						updateNotification();
					}
				} else {
					count_text_block.hide();
					$('#notifCircle').hide();
					message = $('<h5>', {
						class: 'text-center my-5',
						text: 'Новых уведомлений нет'
					});
					div_last.html(message.prop('outerHTML'));
				}
			}
		}
	}

	function dec_notification_all(button) {
		div_all = button.closest('#messages-all');
		if (div_all) {
			div = button.closest('.accordion-item');
			if (div.length) {
				delete_div(button);
				count_block = div.find('.accordion-button .count');
				count = div.find('.message-block').length;
				count_block.text(count);
				if (!count) {
					div.remove();
				}
			}
			if (!div_all.find('.accordion-item').length) {
				message = $('<h5>', {
					class: 'text-center my-5',
					text: 'Новых уведомлений нет'
				});
				div_all.html(message.prop('outerHTML'));
			}
		}
	}

	$('.images').each(function() {
		$(this).viewer({
			viewed: function() {
				$(this).viewer('zoomTo', 1.25);
			}
		});
	});

	$('.avatar-image').each(function() {
		$(this).viewer({
			toolbar: false,
			navbar: false
		});
	});

	function get_duplicate_question_request_list(form) {
		if (!form.find('.is-invalid').length) {
			var q = form.find('.duplicate-question-request-q').val();
			var id = form.find('.duplicate-question-request-id').val();
			var list_group = form.closest('.modal-content').find('.list-group');
			data = {
				q: q,
				id: id
			}
			$.ajax({
				type: form.attr('method'),
				url: form.attr('action'),
				data: data
			})
			.done(function(response) {
				if (response.error) {
					list_group.html(response.error);
				} else {
					list_group.html('');
					for (var i = 0; i < response.items.length; i++) {
						list_group.append(response.items[i]);
					}
					if (response.scripts) {
						script = $('<script>', {text: response.scripts});
						$('body').append(script.prop('outerHTML'));
					}
				}
				prepare_duplicate_question_request_forms();

				Scrollbar.init(document.querySelector('#duplicate-question-request-list-body-' + id));
			});
		}
	}

	function get_duplicate_question_response_list(div, id) {
		var list_group = div.find('.list-group');
		data = {id: id}
		$.ajax({
			type: 'POST',
			url: '/api/list/duplicate-question-response',
			data: data
		})
		.done(function(response) {
			if (response.error) {
				list_group.html(response.error);
			} else {
				list_group.html('');
				for (var i = 0; i < response.items.length; i++) {
					list_group.append(response.items[i]);
				}
				if (response.scripts) {
					script = $('<script>', {text: response.scripts});
					$('body').append(script.prop('outerHTML'));
				}
			}
			prepare_duplicate_question_response_forms();
			Scrollbar.init(document.querySelector('#duplicate-question-response-list-body-' + id));
		});
	}

	$('body').on('change', 'select[data-list]', function (e) {
		var id = $(this).attr('id');
		var type = $(this).val();
		$(`div[data-list=${id}]`).each(function() {
			if ($(this).data('type') == type) {
				$(this).removeClass('d-none');
			} else {
				$(this).addClass('d-none');
			}
		})
	});

	$('body').on('beforeSubmit', '.report-form', function (e) {
		var form = $(this);
		if (!form.find('.is-invalid').length) {
			$.ajax({
				type: form.attr('method'),
				url: form.attr('action'),
				data: form.serializeArray()
			})
			.done(function(data) {
				if(data.status) {
					message = $('<h5>', {
						text: data.message,
						class: 'text-center text-success'
					});
					form.find('.modal-body').html(message);
					setTimeout(function() {
						form.closest('.modal').modal('hide');
					}, 1000);
				}
			});
		}
		return false;
	});

	$('body').on('click', '.save-form [type="submit"]', function (e) {
		$('.preloader').css('display', 'block');
	});

	$('body').on('beforeSubmit', '.save-form', function (e) {
		var form = $(this);
		if (!form.find('.is-invalid').length) {
			var data = new FormData(this);
			$.ajax({
				type: form.attr('method'),
				url: form.attr('action'),
				data: data, // form.serializeArray(),
				cache: false,
				processData: false,
				contentType: false
			})
			.done(function(response) {
				display_alert(response.message, (response.status ? 'flash-success': 'flash-error'));
				if (response.status) {
					$(location).attr('href', response.link);
					return true;
				}
			});
		}
		$('.preloader').css('display', 'none');
		return false;
	});

	$('body').on('afterValidate', '.save-form', function (event, messages, errorAttributes) {
		let data = $(this).data('yiiActiveForm');
		if (data.submitting && errorAttributes.length > 0) {
			$('.preloader').css('display', 'none');
		}
	});

	$('body').on('ajaxComplete', '.save-form', function (event, messages, errorAttributes) {
		let data = $(this).data('yiiActiveForm');
		if (data.submitting && errorAttributes.length > 0) {
			$('.preloader').css('display', 'none');
		}
	});

	$('body').on('change filebatchselected filecleared fileremoved filedeleted', '.answer-files', function (event) {
		var text = $(this).closest('.save-form').find('.answer-text');
		var default_text = 'Ответ прикреплён в файлах.';
		if ($(this).val() && !text.val()) {
			text.val(default_text);
		} else if (!$(this).val() && (text.val() == default_text)) {
			text.val('');
		}
	});

	$('body').on('keyup', '.duplicate-question-request-q', function (event) {
		$(this).closest('.duplicate-question-request').submit();
	});

	$('body').on('beforeSubmit', '.duplicate-question-request', function (event) {
		var form = $(this);
		get_duplicate_question_request_list(form);
		return false;
	});

	window.prepare_duplicate_question_request_forms = function() {
		f = $('.duplicate-question-request-form');
		if (f.length) {
			f.yiiActiveForm();
			f.on('beforeSubmit', function() {
				var form = $(this);
				if (!form.find('.is-invalid').length) {
					$.ajax({
						type: form.attr('method'),
						url: form.attr('action'),
						data: form.serializeArray()
					})
					.done(function(response) {
						if(response.status) {
							message = $('<p>', {
								text: 'Вопрос был предложен',
								class: 'mb-1 h6 text-success'
							});
							form.html(message);
							setTimeout(function() {
								form.closest('.modal').modal('hide');
							}, 1000);
						} else {
							display_alert(response.message, 'flash-error');
						}
					});
				}
				return false;
			});
		}
	}

	prepare_duplicate_question_request_forms();


	window.prepare_duplicate_question_response_forms = function() {
		f = $('.duplicate-question-response-form');
		if (f.length) {
			f.yiiActiveForm();
			f.on('beforeSubmit', function() {
				var form = $(this);
				if (!form.find('.is-invalid').length) {
					data = form.serializeArray();
					button = form.data('yiiActiveForm').submitObject[0].getAttribute('name');
					data.push({name: 'button', value: button});
					$.ajax({
						type: form.attr('method'),
						url: form.attr('action'),
						data: data
					})
					.done(function(response) {
						if(response.status) {
							form.find(`.duplicate-question-response-submitbutton.d-none`).removeClass('d-none');
							form.find(`[name=${button}]`).addClass('d-none');
							request_result = form.find('.request-result');
							request_result.text(result.message);
							request_result.removeClass('d-none');
							display_alert(response.message, 'flash-success');
							window.location.reload();
							// setTimeout(function() {
							// 	form.closest('.modal').modal('hide');
							// }, 1000);
						} else {
							display_alert(response.message, 'flash-error');
						}
					});
				}
				return false;
			});
		}
	}

	prepare_duplicate_question_response_forms();

	$('body').on('click', '.duplicateQuestionRequestButton', function (e) {
		var id = $(this).data('id');
		var div = $('#duplicateQuestionRequestForm-' + id);
		if (div.length) {
			form = div.find('.duplicate-question-request');
			get_duplicate_question_request_list(form);
		}
	});

	$('body').on('click', '.duplicateQuestionResponseButton', function (e) {
		var id = $(this).data('id');
		var div = $('#duplicateQuestionResponseForm-' + id);
		if (div.length) {
			get_duplicate_question_response_list(div, id);
		}
	});

	function check_feed_size() {
		var t = $('.question-nav, .feed-nav, .feed-answer-nav, .feed-comment-nav');
		if (t.length) {
			if (t.hasClass('feed-nav')) {
				w = 580;
			} else {
				w = 530;
			}
			if (t.width() < w) {
				t.find('.icon-text').each(function () {
					$(this).hide();
				});
			} else {
				t.find('.icon-text').each(function () {
					$(this).show();
				});
			}
		}
	}
	$(window).resize(function() {
		check_feed_size();
	});
	check_feed_size();


	function check_rightbar_size() {
		var t = $('.rightbar-list');
		if (t.length) {
			if (t.width() < 330) {
				t.find('.icon-text').each(function () {
					$(this).hide();
				});
			} else {
				t.find('.icon-text').each(function () {
					$(this).show();
				});
			}
		}
	}
	$(window).resize(function() {
		check_rightbar_size();
	});
	check_rightbar_size();

	function search_navbar(text) {
		param = $('meta[name="csrf-param"]').attr("content");
		token = $('meta[name="csrf-token"]').attr("content");
		data = {};
		data[param] = token;
		data.q = text;
		$.ajax({
			type: 'POST',
			url: '/api/list/search',
			data: data,
			success: function(response) {
				classes = 'list-group-item list-group-item-action py-1 py-md-2';
				if (response) {
					list_div = $('#search-list');
					list_div.html('');
					list_div.removeClass('d-none');
					for (var i = 0; i < response.length; i++) {
						link_text = '';
						if (response[i].img) {
							link_text += $('<img>', {
								src: response[i].img,
								class: 'rounded-circle img-search bg-white'
							}).prop('outerHTML');
						} else if (response[i].icon) {
							link_text += $('<span>', {
								class: response[i].icon + ' mx-2'
							}).prop('outerHTML');
						}
						link_text += response[i].title;
						link = $('<a>', {
							html: link_text,
							href: response[i].link,
							class: classes
						});
						list_div.append(link.prop('outerHTML'));
					}
				}
				/*
				action = $('#search-form').prop('action');
				name = $('#navbar-search-field').prop('name');
				link = $('<a>', {
					text: `Поиск результатов по запросу "${text}"`,
					href: `${action}?${name}=${text}`,
					class: classes
				});
				$('#search-list').append(link.prop('outerHTML'));
				*/
			}
		});
	}

	var delayTimer;
	$('body').on('input paste keyup touchend', '#navbar-search-field', function (event) {
		clearTimeout(delayTimer);
		delayTimer = setTimeout(function() {
			var text = $('#navbar-search-field').val().trim();
			if (text) {
				search_navbar(text);
			} else {
				$('#search-list').addClass('d-none');
			}
			return false;
		}, 350);
	});

	$('body').on('blur', '#navbar-search-field', function (event) {
		setTimeout(function() {
			$('#search-list').addClass('d-none');
		}, 500);
	});

	$('body').on('focus', '#navbar-search-field', function (event) {
		if ($('#search-list').html()) {
			$('#search-list').removeClass('d-none');
		}
	});


	var infoTimer = {};

	$('body').on('touchstart mouseenter', '.avatar-img, .avatar-img-lg', function (event) {
		var img = $(this);
		var avatar_div = img.closest('.avatar-div');

		if (avatar_div.length) {
			var info_div = avatar_div.find('.info-div');
			if (info_div.length) {
				if (info_div.data('id') == undefined) {
					info_div.data('id', Date.now());
				}
				var id = info_div.data('id')
				var username = avatar_div.data('username');

				user_info_card = $('.user-info').find(`[data-username='${username}']`);
				clearTimeout(infoTimer[id]);
				if (!user_info_card.length) {
					$.ajax({
						type: 'POST',
						url: '/api/info/user',
						data: {
							username: username
						},
						success: function(response) {
							if (response) {
								$('.user-info').append(response);
								info_div.html(response);
							}
						}
					});
				} else {
					user_info_card = $('.user-info').find(`[data-username='${username}']`);
					if (user_info_card.length) {
						info_div.html(user_info_card.clone());
					}
				}

				$('.info-div:visible').each(function() {
					$(this).hide().html('');
				});
				info_div.show();

				setTimeout(function() {
					check_info_position(info_div);
				}, 300)
			}
		}
	});

	$('body').on('touchstart mouseenter', '.info-div', function (event) {
		if ($(this).is(':visible')) {
			var id = $(this).data('id');
			clearTimeout(infoTimer[id]);
			$(this).show();
		}
	});

	$('body').on('mouseleave', '.avatar-img, .avatar-img-lg', function (event) {
		var avatar_div = $(this).closest('.avatar-div');
		if (avatar_div.length) {
			var info_div = avatar_div.find('.info-div');
			if (info_div.length) {
				var id = info_div.data('id');
				infoTimer[id] = setTimeout(function() {
					info_div.hide().html('');
				}, 300);
			}
		}
	});

	$('body').on('mouseleave', '.info-div', function (event) {
		var info_div = $(this);
		var id = info_div.data('id')
		infoTimer[id] = setTimeout(function() {
			info_div.hide().html('');
		}, 300);
	});

	function check_info_position(info_div) {
		var avatar_div = info_div.closest('.avatar-div');
		var p = avatar_div.find('.position-absolute');
		if (!p.length) {
			return;
		}
		var w = 275;
		var h = 200;
		var right = info_div.offset().left + w - $(window).width();

		if (right > -10) {
			if (p.hasClass('start-0')) {
				p.removeClass('start-0').addClass('end-50');
			}
		} else {
			if (p.hasClass('end-50')) {
				p.removeClass('end-50').addClass('start-0');
			}
		}

		var bottom = ($(window).scrollTop() + $(window).height()) - (info_div.offset().top + h);
		if (bottom < 0) {
			if (p.hasClass('top-0')) {
				p.removeClass('top-0').addClass('bottom-100');
			}
		} else {
			if (p.hasClass('bottom-100')) {
				p.removeClass('bottom-100').addClass('top-0');
			}
		}
	}

	function check_touch_info(e) {
		e.preventDefault();
		if (!e.touches.length) {
			var info_div = $('.info-div:visible');
			if (info_div.length) {
				info_div.hide().html('');
			}
			var menu = $('.dropdown-menu:visible');
			if (menu.length) {
				menu.dropdown('hide');
			}
			return false;
		}
		var elem = $(e.touches[0].target);
		if (elem.hasClass('avatar-img') || elem.hasClass('avatar-img-lg')) {
			return false;
		}
		if (!elem.hasClass('.info-div') && !elem.closest('.info-div').length) {
			var info_div = $('.info-div:visible');
			if (info_div.length) {
				info_div.hide().html('');
			}
		}
		if (!elem.hasClass('dropdown-menu')) {
			var menu = $('.dropdown-menu:visible');
			if (menu.length) {
				setTimeout(function() {
					menu.dropdown('hide');
				}, 500);
			}
		}
	}

	function check_scroll_info_position(e) {
		e.preventDefault();
		var info_div = $('.info-div:visible');
		if (info_div.length) {
			check_info_position(info_div);
		}
	}

	document.addEventListener('touchmove', check_touch_info, false);
	document.addEventListener('touchstart', check_touch_info, false);
	document.addEventListener('scroll', check_scroll_info_position, false);

	$('body').on('click', '#dropdown-search', function (event) {
		$('#profileDropdown').click();
	});

	var end_block = $('<span>', {
		html: $('<span>', {text: 'Время вышло', class: 'icon-text'}),
		class: 'text-secondary bi bi-x-circle-fill bi_icon'
	});

	var left_block = $('<span>', {
		html: $('<span>', {text: 'Осталось: ', class: 'icon-text'}),
		class: 'text-secondary bi bi-clock bi_icon'
	});

	var checkTime = function() {
		$('.time').each(function () {
			countDownDate = new Date($(this).data('time')).getTime();
			now = new Date().getTime();
			distance = countDownDate - now;

			days = Math.floor(distance / (1000 * 60 * 60 * 24));
			hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			seconds = Math.floor((distance % (1000 * 60)) / 1000);

			time_class = 'text-white bg-info';
			time = "";
			if (days) {
				time += days + "д ";
			}
			if (!days && hours) {
				time += hours + "ч ";
				time_class = 'text-dark bg-warning';
			}
			if (!days && (hours || minutes)) {
				time += minutes + "м";
				if (!hours) {
					time_class = 'text-white bg-danger';
				}
			}
			if (!days && !hours && !minutes && seconds) {
				time += seconds + "с";
				if (seconds % 2) {
					time_class = 'text-dark';
				} else {
					time_class = 'text-white bg-danger';
				}
			}

			time_block = $('<span>', {
				text: time,
				class: 'badge rounded-pill icon_time ' + time_class,
			});
			time_text = left_block.prop('outerHTML') + time_block.prop('outerHTML');
			$(this).html(time_text);

			if(!$(this).is(':visible')) {
				$(this).closest('.nav').find('.time-dot').show();
				$(this).show();
			}
			if (distance < 0) {
				// clearInterval(timeLeft);
				$(this).html(end_block.prop('outerHTML'));
				$(this).closest('.nav').find('.time-dot').hide();
				$(this).removeClass('time');
				$(this).hide();
			}
		});
	};

	checkTime();

	var timeLeft = setInterval(checkTime, 1000);

	var updateNotification = function() {
		param = $('meta[name="csrf-param"]').attr("content");
		token = $('meta[name="csrf-token"]').attr("content");
		data = {};
		data[param] = token;
		$.ajax({
			type: 'POST',
			url: '/api/user/update-notification',
			data: data
		}).done(function(response) {
			div = $('#messages-last');
			div.html('');
			count_text_block = $('#notif-count');
			count_block = count_text_block.find('.count');
			if(response.totalCount) {
				for (var i = response.items.length - 1; i >= 0; i--) {
					div.append(response.items[i]);
				}
				count_text_block.data('count', response.totalCount);
				count_block.text(response.totalCount);
				count_text_block.show();
				$('#notifCircle').show();
			} else {
				message = $('<h5>', {
					class: 'text-center my-5',
					text: 'Новых уведомлений нет'
				});
				div.html(message.prop('outerHTML'));
				count_text_block.hide();
				$('#notifCircle').hide();
			}
		})
	};

	updateNotification();

	setInterval(updateNotification, 5 * 60 * 1000);

	prepare_buttons();

	$('[data-toggle="tooltip"]').tooltip();

})
