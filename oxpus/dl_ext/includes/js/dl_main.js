/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swapOptions(cat)
{
	activeOption = activeCat;

	if (cat == activeOption)
	{
		return;
	}

	var	oldTab = document.getElementById('tab_' + activeOption);
	var newTab = document.getElementById('tab_' + cat);

	// no need to set anything if we are clicking on the same tab again
	if (newTab == oldTab)
	{
		return;
	}

	// set active tab
	oldTab.className = oldTab.className = "icon";
	newTab.className = newTab.className += " fa-eye fa-fw";

	document.getElementById('options' + activeOption).className += " noshow";

	document.getElementById('options' + cat).className =
		document.getElementById('options' + cat).className.replace(/(?:^|\s)noshow(?!\S)/g , '')

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
