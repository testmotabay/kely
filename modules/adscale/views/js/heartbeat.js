/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

;(function ($) {
	$(window).on( 'load', function () {
		
		if (!window.adscale_heartbeat_link || !window.adscale_heartbeat_action) {
			return;
		}
		
		$.ajax({
			type: "POST",
			url: window.adscale_heartbeat_link,
			headers: { "cache-control": "no-cache" },
			dataType: 'json',
			data: {
				action: window.adscale_heartbeat_action,
			},
			beforeSend: function () {
			},
			success: function (response) {
				console.log(response);
			},
			error: function (xhr, textStatus, thrownError) {
				console.log('ajax ERROR');
			},
			complete: function () {
				console.log('ajax PROCESS  COMPLETED');
			}
		});
		
	});
})(jQuery);
