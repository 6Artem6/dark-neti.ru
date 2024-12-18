
$(document).ready(function(){
	$('#fio').on('change blur', function (e) {
		e.preventDefault();
		var tab = $('#nav-group_name-tab');
		if ($('#fio').val()) {
			tab.removeClass('disabled');
		} else {
			tab.addClass('disabled');
		}
		setTimeout(function() {
			if ($('#fio').parent('.has-error').length) {
				$('#nav-fio-tab').removeClass('text-success').addClass('text-danger');
			} else {
				$('#nav-fio-tab').removeClass('text-danger').addClass('text-success');
			}
		}, 300);
	});
	$('#group_name').on('change blur', function (e) {
		e.preventDefault();
		var tab = $('#nav-student_email-tab');
		if ($(this).val()) {
			tab.removeClass('disabled');
		} else {
			tab.addClass('disabled');
		}
		setTimeout(function() {
			if ($('#group_name').parent('.has-error').length) {
				$('#nav-group_name-tab').removeClass('text-success').addClass('text-danger');
			} else {
				$('#nav-group_name-tab').removeClass('text-danger').addClass('text-success');
			}
		}, 300);
	});
	$('#student_email').on('change blur', function (e) {
		e.preventDefault();
		var tab = $('#nav-birth_date-tab');
		if ($(this).val()) {
			tab.removeClass('disabled');
		} else {
			tab.addClass('disabled');
		}
		setTimeout(function() {
			if ($('#student_email').parent('.has-error').length) {
				$('#nav-student_email-tab').removeClass('text-success').addClass('text-danger');
			} else {
				$('#nav-student_email-tab').removeClass('text-danger').addClass('text-success');
			}
		}, 300);
	});
	$('#birth_date').on('change blur', function (e) {
		e.preventDefault();
		var tab = $('#nav-register-tab');
		if ($(this).val() && $('#agreement').is(':checked')) {
			tab.removeClass('disabled');
		} else {
			tab.addClass('disabled');
		}
		setTimeout(function() {
			if ($('#birth_date').parent('.has-error').length || $('#agreement').parent('.has-error').length) {
				$('#nav-birth_date-tab').removeClass('text-success').addClass('text-danger');
			} else {
				$('#nav-birth_date-tab').removeClass('text-danger').addClass('text-success');
			}
		}, 300);
	});
	$('#agreement').on('change blur', function(e) {
		e.preventDefault();
		var tab = $('#nav-register-tab');
		if ($('#birth_date').val() && $(this).is(':checked')) {
			tab.removeClass('disabled');
		} else {
			tab.addClass('disabled');
		}
		if ($(this).is(':checked')) {
			$('#register-button').removeAttr('disabled');
		} else {
			$('#register-button').attr('disabled', true);
		}
		setTimeout(function() {
			if ($('#birth_date').parent('.has-error').length || $('#agreement').parent('.has-error').length) {
				$('#nav-birth_date-tab').removeClass('text-success').addClass('text-danger');
			} else {
				$('#nav-birth_date-tab').removeClass('text-danger').addClass('text-success');
			}
		}, 300);
	});

	$('#register-form').on('beforeSubmit', function (event, messages, errorAttributes) {
		if ($('#fio').parent('.has-error').length) {
			$('#nav-fio-tab').removeClass('text-success').addClass('text-danger');
		} else {
			$('#nav-fio-tab').removeClass('text-danger').addClass('text-success');
		}
		if ($('#group_name').parent('.has-error').length) {
			$('#nav-group_name-tab').removeClass('text-success').addClass('text-danger');
		} else {
			$('#nav-group_name-tab').removeClass('text-danger').addClass('text-success');
		}
		if ($('#student_email').parent('.has-error').length) {
			$('#nav-student_email-tab').removeClass('text-success').addClass('text-danger');
		} else {
			$('#nav-student_email-tab').removeClass('text-danger').addClass('text-success');
		}
		if ($('#birth_date').parent('.has-error').length || $('#agreement').parent('.has-error').length) {
			$('#nav-birth_date-tab').removeClass('text-success').addClass('text-danger');
		} else {
			$('#nav-birth_date-tab').removeClass('text-danger').addClass('text-success');
		}
	});

	function check_fields() {
		if ($('#fio').val()) {
			$('#nav-group_name-tab').removeClass('disabled');
		} else {
			$('#nav-group_name-tab').addClass('disabled');
		}
		if ($('#group_name').val()) {
			$('#nav-student_email-tab').removeClass('disabled');
		} else {
			$('#nav-student_email-tab').addClass('disabled');
		}
		if ($('#student_email').val()) {
			$('#nav-birth_date-tab').removeClass('disabled');
		} else {
			$('#nav-birth_date-tab').addClass('disabled');
		}
		if ($('#birth_date').val() && $('#agreement').is(':checked')) {
			$('#nav-register-tab').removeClass('disabled');
		} else {
			$('#nav-register-tab').addClass('disabled');
		}
	}
	check_fields();
});
