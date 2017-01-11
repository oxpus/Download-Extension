/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function AJAXDLVote(df_id, points) {
	var dl_url	= dl_ajax_url;
	var dl_par = '&df_id=' + df_id + '&rate_point=' + points;
	$.ajax({
        url: dl_url + dl_par,
		type: "GET",
        success: function(data) { AJAXDLFinishRate(data); }
	});
}

function AJAXDLFinishRate(data) {
	var obj = $.parseJSON( data );
	$( "#rating_" + obj.df_id ).html( obj.rate_img );
}
