
$(document).ready(function(){

	window.tour = function() {
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
			title: 'Привязка Telegram.',
			text: `Так, вот тебе инструкция, тут всё просто и понятно. Думаю, разберешься сам, а мне уже пора.<br>` +
					`Надеюсь, тебе было интересно проводить со мной время, хотя бы чуууть-чуть.<br>` +
					`Не забывай, что ты лучший!<br>` +
					`Пока-пока!` +
					`<div class="w-100 text-center pt-3"><img src="/assistant/assistant_20.svg" width="150"/></div>`,
			buttons: [
				{
					action() {
						return $(location).attr('href', '/user/settings?tour=1&step=13');
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						url = $(location).attr('pathname');
						return $(location).attr('href', url);
					},
					text: 'Завершить',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-14',
		});

		tour.start();
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
