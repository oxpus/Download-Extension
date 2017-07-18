$('.dlTooltip').hover(
	function() {cat = $(this).attr("id"); $('#desc_' + cat).show();},
	function() {cat = $(this).attr("id"); $('#desc_' + cat).hide();}
);
