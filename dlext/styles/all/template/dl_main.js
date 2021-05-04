/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2015-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swapOptions(cat, openString1, closeString1, icon1, iconColor1, openString2, closeString2, icon2, iconColor2)
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
		$('#extra_page').fadeOut('fast');
		$('#hash_page').fadeOut('fast');
		$('#extra_page_open').html('<a href="#" onclick="pageToggle(\'extra_page\', true, \'' + openString1 + '\', \'' + closeString1 + '\', \'' + icon1 + '\', \'' + iconColor1 + '\')" class="button icon-button" title="' + openString1 + '"><i class="icon fa-' + icon1 + ' fa-fw" aria-hidden="true"></i> <span class="sr-only">' + openString1 + '</span></a>');
		$('#hash_page_open').html('<a href="#" onclick="pageToggle(\'hash_page\', true, \'' + openString2 + '\', \'' + closeString2 + '\', \'' + icon2 + '\', \'' + iconColor2 + '\')" class="button icon-button" title="' + openString2 + '"><i class="icon fa-' + icon2 + ' fa-fw" aria-hidden="true"></i> <span class="sr-only">' + openString2 + '</span></a>');
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

function hideDlButton()
{
	$('#dl_mod_button').fadeOut('fast');
}

function pageToggle(page, status, openString, closeString, icon, iconColor)
{
	if (status == true)
	{
		$('#' + page).fadeIn("slow");
		$('#' + page + '_open').html('<a href="#" onclick="pageToggle(\'' + page + '\', false, \'' + openString + '\', \'' + closeString + '\', \'' + icon + '\', \'' + iconColor + '\')" class="button icon-button" title="' + closeString + '"><i class="icon fa-' + icon + ' fa-fw ' + iconColor + '" aria-hidden="true"></i> <span class="sr-only">' + closeString + '</span></a>');
	}
	else
	{
		$('#' + page).fadeOut("fast");
		$('#' + page + '_open').html('<a href="#" onclick="pageToggle(\'' + page + '\', true, \'' + openString + '\', \'' + closeString + '\', \'' + icon + '\', \'' + iconColor + '\')" class="button icon-button" title="' + openString + '"><i class="icon fa-' + icon + ' fa-fw" aria-hidden="true"></i> <span class="sr-only">' + openString + '</span></a>');
	}
}
