/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swap_options(cat)
{
	active_option = active_cat;

	var	old_tab = document.getElementById('tab_' + active_option);
	var new_tab = document.getElementById('tab_' + cat);

	// no need to set anything if we are clicking on the same tab again
	if (new_tab == old_tab)
	{
		return;
	}

	// set active tab
	old_tab.className = old_tab.className.replace(/\activetab/g, '');
	new_tab.className = new_tab.className + 'activetab';

	if (cat == active_option)
	{
		return;
	}

	document.getElementById('options' + active_option).className += " noshow";

	document.getElementById('options' + cat).className =
		document.getElementById('options' + cat).className.replace
		( /(?:^|\s)noshow(?!\S)/g , '' )

	active_cat = cat;
}

function hide_dl_button()
{
	var button = getElementById('dl_mod_button');
	button.style.display = 'none';
	return;
}