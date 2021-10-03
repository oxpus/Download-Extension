/**
*
* @package   phpBB Extension - Oxpus Downloads
* @copyright (c) 2015-2021 OXPUS - www.oxpus.net
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function AJAXDLVote(df_id, points) {
	$("#rating_" + df_id).attr('readonly', true);
	$("#rating_" + df_id).attr('invisible', true);
	var dl_url = dlAjaxUrl;
	var dl_par = 'dl_id=' + df_id + '&rate_point=' + points;
	var seperator = '?';
	if (dl_url.indexOf('?') > 0 ) {
		seperator = '&';
	}
	$.ajax({
		url: dl_url + seperator + dl_par,
		type: "GET",
		success: function (data) { AJAXDLFinishRate(data); }
	});
}

function AJAXDLUnvote(df_id) {
	$("#rating_" + df_id).attr('readonly', true);
	$("#rating_" + df_id).attr('invisible', true);
	var dl_url = dlAjaxUrl;
	var dl_par = 'drop=1&dl_id=' + df_id;
	var seperator = '?';
	if (dl_url.indexOf('?') > 0) {
		seperator = '&';
	}
	$.ajax({
		url: dl_url + seperator + dl_par,
		type: "GET",
		success: function (data) { AJAXDLFinishRate(data); }
	});
}

function AJAXDLFinishRate(data) {
	var obj = $.parseJSON( data );
	var rate_img = '';
	var i = 0;

	if (obj.count.title != null) {
		rate_img = rate_img + '<span class="dl-rating dl-rate-block" title="' + obj.count.title + '">';
	}

	for (var i = 0; i < obj.count.max; i++) {

		if (obj.stars[i]['ajax'] > 0) {
			rate_img = rate_img + '<a href="#" class="dl-rating-img dl-rate-add" data-dfid="' + obj.count.dlId + '" data-points="' + obj.stars[i]['ajax'] + '">';
		}

		if (obj.stars[i]['icon'] == 'yes') {
			rate_img = rate_img + '<i class="icon fa-star fa-fw dl-green"></i>';
		}
		else {
			rate_img = rate_img + '<i class="icon fa-star-o fa-fw dl-yellow"></i>';
		}

		if (obj.stars[i]['ajax'] > 0) {
			rate_img = rate_img + '</a>';
		}
	}

	if (obj.count.undo == 1) {
		rate_img = rate_img + ' <a href="#" class="dl-rating-img dl-rate-del" data-dfid="' + obj.count.dlId + '"><i class="icon fa-times-circle fa-fw dl-red"></i></a>';
	}

	if (obj.count.count != '-') {
		rate_img = rate_img + ' <span class="dl-rating-count">' + obj.count.count + '</span>';
	}

	if (obj.count.title != null) {
		rate_img = rate_img + '</span>';
	}

	$("#rating_" + obj.count.dlId).html(rate_img);
	$("#rating_" + obj.count.dlId).attr('show', true);
	$("#rating_" + obj.count.dlId).attr('readonly', false);

	assignRateFunction();
}

function assignRateFunction() {
	$('.dl-rate-add').click(function () {
		var df_id = $(this).data('dfid');
		var points = $(this).data('points');

		AJAXDLVote(df_id, points);
	});

	$('.dl-rate-del').click(function () {
		var df_id = $(this).data('dfid');

		AJAXDLUnvote(df_id);
	});
}

$(document).ready(function () {
	assignRateFunction();
});
