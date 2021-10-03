/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
var dl_traffic_active_cat = '0';

function dl_swap_cat_options(dl_traffic_cat) {
	var dl_traffic_active_option = dl_traffic_active_cat;

	// no need to set anything if we are clicking on the same tab again
	if (dl_traffic_active_option == dl_traffic_cat) {
		return;
	}

	// set active tab
	$('#tab' + dl_traffic_active_option).removeClass('activetab');
	$('#tab' + dl_traffic_cat).addClass('activetab');

	$('#options' + dl_traffic_active_option).addClass('dl-noshow');
	$('#options' + dl_traffic_cat).removeClass('dl-noshow');

	dl_traffic_active_cat = dl_traffic_cat;
}

$(document).ready(function () {
	$('.dl_traffic_tabs').click(function () {
		var dl_traffic_tab = $(this).data('tab');

		dl_swap_cat_options(dl_traffic_tab);
	});
});