function scrollBarTable(){
	jQuery(".table-responsive").mCustomScrollbar({
		axis:"x",
		theme:"light-3",
		advanced:{autoExpandHorizontalScroll:true}
	});
}

jQuery(window).ready(scrollBarTable);
jQuery(document).resize(scrollBarTable);