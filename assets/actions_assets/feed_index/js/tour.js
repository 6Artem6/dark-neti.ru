
$(document).ready(function(){

	window.tour = function() {
		var dis = $('#nav-disciplines a');
		dis.attr('href', dis.attr('href') + '?tour=1');

		var positions;
		if (window.innerWidth < 992) {
			positions = ['bottom', 'top', 'bottom', 'top'];
		} else {
			positions = ['bottom', 'left', 'right', 'right'];
		}

		function show_left_bar() {
			if (window.innerWidth < 992) {
				$('#left-bar').offcanvas('show');
			}
		}

		function hide_left_bar() {
			if (window.innerWidth < 992) {
				$('#left-bar').offcanvas('hide');
			}
		}

		const tour = new Shepherd.Tour({
			defaultStepOptions: {
				cancelIcon: {
					enabled: true
				},
				scrollTo: { behavior: 'smooth', block: 'center' }
			},
			modalOverlayOpeningPadding: '10',
			useModalOverlay: true
		});

		tour.addStep({
			title: '«Ну наконец-то…»',
			text: `А вот и ты! Я уже тебя заждался.<br>` +
				`Ты уж прости, но разработчики заставили меня провести краткую экскурсию по моему дому.<br>` +
				`Постараюсь рассказать все быстро и интересно!` +
				`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_4.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-welcome-div',
			id: 'step-1'
		});

		tour.addStep({
			title: '«О DARK-NETi»',
			text: `Мои разработчики - такие же студенты, как и ты. Поэтому проект предназначен для помощи студентам в обучении.<br>` +
					`По секрету скажу, что они планируют нечто глобальное, только тсс... я тебе ничего не говорил.<br>` +
					`Кхм-кхм, так вот, DARK-NETi ещё дорабатывается, и бла-бла-бла...<br>` +
					`Тут я должен рассказать тебе про нашу цель и так далее, но это все скучно и неинтересно.<br>` +
					`Ну ты понял, будем считать, что я тебе это рассказал.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_6.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-welcome-div',
			id: 'step-2'
		});

		tour.addStep({
			title: '«Предложение для тебя»',
			text: `Кстати, скажу сразу, что если хочешь, чтобы у тебя увеличился шанс получить ответ на твой вопрос - поделись ссылкой на сайт с другими.<br>` +
					`Вот увидишь, жить станет намного легче!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_8.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						link = "https://dark-neti.ru";
						navigator.clipboard.writeText(link);
					},
					text: 'Скопировать ссылку',
					classes: 'btn btn-sm btn-info rounded-pill'
				},
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-welcome-div',
			id: 'step-3'
		});

		tour.addStep({
			title: '«Немного о рейтинге»',
			text: `Короче, на сайте есть рейтинговая система, а сам рейтинг начисляется за:<br>` +
					`<ol>` +
					`<li>ответы на вопросы</li>` +
					`<li>полученные лайки</li>` +
					`</ol>` +
					`Он нужен для мотивации. В строках кода я отрыл информацию, что планируется вознаграждение студентов, но только я тебе ничего не говорил.<br>` +
					`Да, что-то я сегодня много болтаю. Идём дальше!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_7.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-welcome-div',
			id: 'step-4'
		});

		tour.addStep({
			title: '«Лента предметов»',
			text: `Вот здесь будут отображаться предметы, на которые ты подписан или которые будут тебе интересны.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_12.svg" width="150"/></div>`,
			attachTo: {
				element: '#slider',
				on: positions[0]
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-5'
		});

		tour.addStep({
			title: '«Активные пользователи»',
			text: `Здесь отображаются мои любимчики - самые активные пользователи сайта!<br>` +
					`Это моя гордость!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_14.svg" width="150"/></div>`,
			attachTo: {
				element: '.active-users-list-div',
				on: positions[1]
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						show_left_bar();
						var t = this;
						setTimeout(function() {
							t.next();
						}, 250);
						return true;
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-6'
		});

		tour.addStep({
			title: 'Вкладка «Моя лента».',
			text: `Так, вот тут твоя лента. Здесь отображаются вопросы по предметам, на которые ты подписан, или вопросы пользователей, на которые ты подписан.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_13.svg" width="150"/></div>`,
			attachTo: {
				element: '#nav-feed',
				on: positions[2]
			},
			buttons: [
				{
					action() {
						hide_left_bar();
						var t = this;
						setTimeout(function() {
							t.back();
						}, 250);
						return true;
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-7'
		});

		tour.addStep({
			title: 'Вкладка «предметы».',
			text: `Так, давай-ка перейдём сюда!.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_15.svg" width="150"/></div>`,
			attachTo: {
				element: '#nav-disciplines',
				on: positions[2]
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					text: 'Назад',
					classes: 'btn btn-sm btn-light rounded-pill'
				},
				{
					action() {
						return $(location).attr('href', dis.attr('href'));
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-8'
		});

		var step = parseInt(window.getUrlParameter('step'));
		if ((step >= 1) && (step <= 8)) {
			if (step == 7 || step == 8) {
				show_left_bar();
				setTimeout(function() {
					tour.show('step-' + step, true);
				}, 250);
			} else {
				tour.show('step-' + step, true);
			}
		} else {
			tour.start();
		}
	}

	var start_tour = getUrlParameter('tour');
	setTimeout(function() {
		var badge = $('body').data('badge');
		if ((start_tour == 1) && (badge != true)) {
			tour();
		}
	}, 750);
	$('.modal-alert [data-bs-dismiss]').click(function (e) {
		e.preventDefault();
		setTimeout(function() {
			if ((start_tour == 1) && !$('.modal-alert:visible').length) {
				tour();
			}
		}, 500)
	});
});
