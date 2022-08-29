/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
function AJAXDLUnassigned(cat_id, mode) {
	var dl_url = dlAjaxUnassignUrl;
	var dl_par = 'cat_id=' + cat_id + '&mode=' + mode;
	var seperator = '?';
	if (dl_url.indexOf('?') > 0 ) {
		seperator = '&';
	}
	$.ajax({
		url: dl_url + seperator + dl_par,
		type: "GET",
		success: function (data) { AJAXDLFinishUnassign(data); }
	});
}

function AJAXDLFinishUnassign(data) {
	var obj = $.parseJSON( data );

	$(".dl-unassigned").html(obj);
}
