/**
 * ownCloud - filesmv
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

(function ($, OC) {

	$(document).ready(function () {
		if(/(public)\.php/i.exec(window.location.href)!=null) return; // escape when the requested file is public.php
		$('#hello').click(function () {
			alert('Hello from your script file');
		});

		$('#echo').click(function () {
			var url = OC.generateUrl('/apps/filesmv/echo');
			var data = {
				echo: $('#echo-content').val()
			};

			$.post(url, data).success(function (response) {
				$('#echo-result').text(response.echo);
			});

		});
	});

})(jQuery, OC);
