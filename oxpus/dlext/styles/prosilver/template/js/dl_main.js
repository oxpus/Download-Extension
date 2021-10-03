/**
*
* @package   phpBB Extension - Oxpus Downloads
* @copyright (c) 2015-2021 OXPUS - www.oxpus.net
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function dlSwapOptions(cat, openString1, closeString1, icon1, iconColour1, openString2, closeString2, icon2, iconColour2)
{
	var activeOption = activeCat;

	// no need to set anything if we are clicking on the same tab again
	if (cat == activeOption)
	{
		return;
	}

	if (cat != 0)
	{
		$('#optionst').addClass('noshow');
	}
	else
	{
		$('#optionst').removeClass('noshow');
	}

	$('#tab_' + activeOption).removeClass('dl-tab-icon');
	$('#tab_' + cat).addClass('dl-tab-icon');

	$('#options' + activeOption).addClass('noshow');
	$('#options' + cat).removeClass('noshow');

	activeCat = cat;
}

$(document).ready(function () {
	$('.dl-detail-area').click(function () {
		var dl_cat = $(this).data('cat');
		var dl_open1 = $(this).data('open1');
		var dl_close1 = $(this).data('close1');
		var dl_icon1 = $(this).data('icon1');
		var dl_colour1 = $(this).data('colour1');
		var dl_open2 = $(this).data('open2');
		var dl_close2 = $(this).data('close2');
		var dl_icon2 = $(this).data('icon2');
		var dl_colour2 = $(this).data('colour2');

		dlSwapOptions(dl_cat, dl_open1, dl_close1, dl_icon1, dl_colour1, dl_open2, dl_close2, dl_icon2, dl_colour2);
	});

	$('.downloadbtn').click(function () {
		$('#dl_mod_button').fadeOut('fast', function () {
			$('#dl_hint').removeClass('noshow');
			setTimeout(function () {
				$('#dl_hint').removeClass('dl-red');
				$('#dl_hint').addClass('dl-blue');
			}, 1000);
			setTimeout(function () {
				$('#dl_hint').removeClass('dl-blue');
				$('#dl_hint').addClass('dl-green');
			}, 2000);
			setTimeout(function () {
				$('#dl_hint').fadeOut('slow');
			}, 3000);
		});
	});
});
