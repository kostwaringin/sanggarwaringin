jQuery(function($) {

	var initAjaxControls = function() {
		$('.factory-ajax-checkbox').on('change', function(ev) {
			var action = $(this).data('action');
			var new_value = $(this).val();

			var data = {};
			data['action'] = 'wbcr-upm-change-flag';
			data['theme'] = $(this).data('theme-slug');
			if( !data['theme'] ) {
				data['plugin'] = $(this).data('plugin-slug');
			}
			data['flag'] = $(this).data('action');
			data['value'] = new_value;

			var disable_group = $(this).data('disable-group');
			if( disable_group ) {

				if( new_value == true ) {
					$("." + disable_group).find('button, input').prop('disabled', true);
					var row = $(this).parents('tr');
					row.removeClass('active').addClass('inactive');

				} else {
                    $("." + disable_group).each(function(k, v) {
                        if( !$(v).hasClass('global-disabled') ) {
                            $(v).find('button, input').prop('disabled', false);
                        }

                        var row = $(this).parents('tr');
                        if( !row.hasClass('row-global-disabled') ){
                            row.removeClass('inactive').addClass('active');
                        }
                    });
				}

			}
			$.ajax({
				url: ajaxurl,
				method: 'post',
				data: data,
				success: function(response) {

					if( !response || !response.success ) {
						if( response.data.error_message ) {
							$.wbcr_factory_clearfy_206.app.showNotice('Error: [' + response.data.error_message + ']', 'danger');
						}
						return false;
					}

					/*var noticeId = $.wbcr_factory_clearfy_206.app.showNotice('Settings successfully updated', 'success');

					setTimeout(function() {
						$.wbcr_factory_clearfy_206.app.hideNotice(noticeId);
					}, 5000);*/
				},
				error: function(xhr, ajaxOptions, thrownError) {
					console.log(xhr.status);
					console.log(xhr.responseText);
					console.log(thrownError);

					$.wbcr_factory_clearfy_206.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
				}
			});
		});

	};

	initAjaxControls();
});