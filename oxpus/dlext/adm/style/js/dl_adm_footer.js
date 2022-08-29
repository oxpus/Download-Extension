/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
$(document).ready(function () {
	$('.dl-marklist').click(function () {
		var webform = $(this).data('form');
		var webfield = $(this).data('field');

		marklist(webform, webfield, true);
	});

	$('.dl-unmarklist').click(function () {
		var webform = $(this).data('form');
		var webfield = $(this).data('field');

		marklist(webform, webfield, false);
	});

	$('.dl-finduser').click(function () {
		var user_href = $(this).data('href');

		find_username(user_href);
	});

	$('.dl-change-select').change(function () {
		var button = $(this).data('button');

		$('#' + button).click();
	});

	$('.dl-select-edit-cat').change(function () {
		var cat_id = $(this).val();

		AJAXDLUnassigned(cat_id, 'acp');
	});
});
