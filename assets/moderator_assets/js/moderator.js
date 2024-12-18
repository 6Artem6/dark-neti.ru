$(document).ready(function() {
	$('body').on('click', 'a[data-button=action-moderator], button[data-button=action-moderator]', function(e) {
		e.preventDefault();
		var button = $(this);
		var action = $(this).data('action');
		var url  = `/api/moderator/${action}`;
		var id = $(this).data('id');
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
				success: function(result) {
					if (result.message) {
						display_alert(result.message, (result.status ? 'flash-info': 'flash-error'));
					}
					if (result.status) {
						change_button(button, action);
					}
				},
				error: function(result) {
					display_alert(result.message, false);
				}
			})
		}
	});

	$('.toast[role=alert]:not(#alertMessage)').each(function(e) {
		$(this).toast('show');
	});

	function display_alert(message, color = 'flash-info', reload = false) {
		alert = $('#alertMessage');
		alert.find('.toast-body').text(message);
		alert.find('rect').attr('class', color);
		alert.toast('show');
		if (reload) {
			document.location.reload();
		}
	}

	function change_button(button, action) {
		if (action == 'hide-question') {
			document.location.reload();
		} else if (action == 'show-question') {
			document.location.reload();
		} else if (action == 'hide-answer') {
			document.location.reload();
		} else if (action == 'show-answer') {
			document.location.reload();
		} else if (action == 'hide-comment') {
			document.location.reload();
		} else if (action == 'show-comment') {
			document.location.reload();
		}
	}

})