
$(document).ready(function(){

	window.tour = function() {
		var tel = $('#telegram-link');
		tel.attr('href', tel.attr('href') + '?tour=1');

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
			title: 'Настройка уведомлений.',
			text: `Здесь ты можешь выбрать, какие уведомления хочешь получать.` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_17.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return $(location).attr('href', '/discipline?tour=1&step=10');
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
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
			id: 'step-11',
		});

		tour.addStep({
			title: 'Уведомления на сайте.',
			text: `Уведомления будут приходить тебе сюда!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_16.svg" width="150"/></div>`,
			attachTo: {
				element: '#notifDropdown',
				on: 'top'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
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
			id: 'step-12',
		});

		tour.addStep({
			title: 'Уведомления в Telegram.',
			text: `Чуть не забыл! У нас же есть Телеграм-бот, который может присылать тебе уведомления, чтобы постоянно не заходить на сайт!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_7.svg" width="150"/></div>`,
			attachTo: {
				element: '#telegram-link',
				on: 'top'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return $(location).attr('href', tel.attr('href'));
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-13',
		});

		step = parseInt(window.getUrlParameter('step'));
		if ((step >= 11) && (step <= 13)) {
			tour.show('step-' + step, true);
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
