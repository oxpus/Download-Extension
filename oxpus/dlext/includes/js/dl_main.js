/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swapOptions(cat)
{
	activeOption = activeCat;

	// no need to set anything if we are clicking on the same tab again
	if (cat == activeOption)
	{
		return;
	}

	$('#tab_' + activeOption).removeClass('fa-eye');
	$('#tab_' + cat).addClass('fa-eye');

	$('#options' + activeOption).addClass('noshow');
	$('#options' + cat).removeClass('noshow');

	activeCat = cat;

	return;
}

function hideDlButton()
{
	$('#dl_mod_button').fadeOut('fast');
}

function pageToggle(page, status, openString, closeString)
{
	if (status == true)
	{
		$('#' + page).fadeIn("slow");
		$('#' + page + '_open').html('<a href="#" onclick="pageToggle(\'' + page + '\', false, \'' + openString + '\', \'' + closeString + '\')"><i class="icon fa-eye-slash fa-fw" aria-hidden="true"></i> <span>' + closeString + '</span></a>');
	}
	else
	{
		$('#' + page).fadeOut("fast");
		$('#' + page + '_open').html('<a href="#" onclick="pageToggle(\'' + page + '\', true, \'' + openString + '\', \'' + closeString + '\')"><i class="icon fa-eye fa-fw" aria-hidden="true"></i> <span>' + openString + '</span></a>');
	}
}
