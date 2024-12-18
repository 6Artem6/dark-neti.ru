
$(document).ready(function(){

	window.tour = function() {
		var set = $('#nav-settings a');
		set.attr('href', set.attr('href') + '?tour=1');

		var positions;
		if (window.innerWidth < 992) {
			positions = ['top', 'top'];
		} else {
			positions = ['top', 'right'];
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
			title: 'Вкладка «поиск предметов».',
			text: `О, вот эта интересная штука!<br>` +
					`Смотри, благодаря гибким фильтрам ты легко сможешь найти нужный предмет, а так же увидишь список своих предметов за текущий семестр, нажав на кнопку «Показать за текущий семестр».<br>` +
					`После экскурсии не забудь подписаться на них!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_18.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return $(location).attr('href', '/feed?tour=1&step=8');
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
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
			id: 'step-9',
		});

		tour.addStep({
			title: 'Вкладка «настройки».',
			text: `Так, теперь нам сюда.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_19.svg" width="150"/></div>`,
			attachTo: {
				element: '#nav-settings',
				on: positions[1]
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
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return $(location).attr('href', set.attr('href'));
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-10',
		});

		var step = parseInt(window.getUrlParameter('step'));
		if ((step >= 9) && (step <= 10)) {
			if (step == 10) {
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
