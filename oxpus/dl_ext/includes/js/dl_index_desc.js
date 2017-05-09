$('.dlTooltip').hover(
	function() {cat = $(this).attr("id"); $('#desc_' + cat).fadeIn('slow');},
	function() {cat = $(this).attr("id"); $('#desc_' + cat).fadeOut('fast');}
);
