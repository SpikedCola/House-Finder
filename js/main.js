$(function() {        
        $("#listings-table").tablesorter(); 
        
        $("#map-button").on('click', function(e) {
                e.preventDefault();
                
                if ($(this).hasClass('up-arrow')) {
                        $("#map-container").slideUp(1000, 'easeOutQuad', function() {
                                $("#map-button").removeClass('up-arrow').addClass('down-arrow');
                        });
                }
                else {
                        $("#map-container").slideDown(1000, 'easeOutQuad', function() {
                                map.panTo(centerMarker.getPosition());
                                $("#map-button").removeClass('down-arrow').addClass('up-arrow');
                        });
                }
        });
	
	// show/hide options container
        $("#config-button").on('click', function(e) {
                e.preventDefault();
                
		if ($("#options-container").is(':visible')) {
                        $("#options-container").slideUp(700, 'easeOutQuad');
                }
                else {
                        $("#options-container").slideDown(700, 'easeOutQuad');
                }
	});

	// hide the options container if the user clicks the outside it
	$("#body-container").on('click', function(e) {
		if ($("#options-container").is(":visible")) {
                        $("#options-container").slideUp(700, 'easeOutQuad');
		}
	});

	// save options button
	$("#save-button").on('click', function(e) {
		e.preventDefault();
		$(this).hide();
		$("#spinner").show();
		saveOptions();
	});

        // these two handle the mouse hovering over the table rows
        // we would like to change the related marker to blue on hover
        // and possibly max out its z-index so we can see it if there
        // are multiple markers on one spot
        $("#listings-table tbody tr").live('mouseenter', function() {
                var td = $(this).find(".id");
                var id = parseInt(td.text(), 10);
                var marker = markers[id - 1];
                marker.setIcon('images/blue.png');
                marker.setZIndex(1000);
                $(this).addClass('hover');
        });

        $("#listings-table tbody tr").live('mouseleave', function() {
                var td = $(this).find(".id");
                var id = parseInt(td.text(), 10);
                var marker = markers[id - 1];
                marker.setIcon('images/red.png');
                marker.setZIndex(id - 1);
                $(this).removeClass('hover');
        });
});