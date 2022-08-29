/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
function dl_footer_show_area(area, status)
{
	if (status == true)
	{
		$("#" + area).fadeIn("slow");
	}
	else
	{
		$("#" + area).fadeOut("fast");
	}
}

$(document).ready(function () {
	$('#dl-legend-footer').click(function (ev) {
		dl_footer_show_area('dl-footer', false);
		dl_footer_show_area('dl-legend', true);
		ev.stopPropagation();
	});

	$('#dl-stats-footer').click(function (ev) {
		dl_footer_show_area('dl-legend', false);
		dl_footer_show_area('dl-footer', true);
		ev.stopPropagation();
	});

	$('.dl-close-footer').click(function () {
		var dl_footer_area = $(this).data('area');
		$('#' + dl_footer_area).fadeOut("fast");
	});

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

	$('.dl-smiley-insert').click(function () {
		var smiley = $(this).data('smiley');

		insert_text(smiley, true);
	});

	$('.dl-smiley-popup').click(function () {
		var url = $(this).data('url');

		window.open(url, '_blank', 'height=200,resizable=yes,scrollbars=yes,width=400');
	});

	$('.dl-change-select').change(function () {
		var button = $(this).data('button');

		$('#' + button).click();
	});

	$('.dl-select-edit-cat').change(function () {
		var cat_id = $(this).val();

		AJAXDLUnassigned(cat_id, 'ext');
	});
});

$(window).click(function(ev) {
	if ($(ev.target).attr('id') != "#dl-legend") {
		dl_footer_show_area('dl-legend', false);
		ev.stopPropagation();
	}

	if ($(ev.target).attr('id') != "#dl-footer") {
		dl_footer_show_area('dl-footer', false);
		ev.stopPropagation();
	}
});
