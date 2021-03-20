$(document).ready(function () {
    $(".dl_help_close").click(function () {
        $("#dl_help_popup").fadeOut("fast");
        $("#dl_help_bg").fadeOut("fast");
    });
});

var seperator = '?';
if (dl_help_path.indexOf('?') > 0) {
	seperator = '&';
}

$('.help').click(function () {
    var help_key = $(this).attr('key');
    var param = $(this).attr('param');
    var url_param = '';

    if (param != null) {
        url_param = '&value=' + param;
    }

    $.ajax({
        url: dl_help_path + seperator + 'help_key=' + help_key + url_param,
        type: "GET",
        success: function (data) { AJAXDLHelpDisplay(data); }
    });
});

function AJAXDLHelpDisplay(data) {
	var obj = $.parseJSON( data );

    $("#dl_help_title").html(obj.title);
    $("#dl_help_option").html(obj.option);
    $("#dl_help_string").html(obj.string);

    $("#dl_help_bg").css("opacity", "0.7");
    $("#dl_help_bg").fadeIn("fast");
    $("#dl_help_popup").fadeIn("fast");
}

$(window).click(function(ev){
	if ($(ev.target).attr('id') != "#dl_help_popup") {
		$("#dl_help_popup").fadeOut("fast");
		$("#dl_help_bg").fadeOut("fast");
		ev.stopPropagation();
	}
});
