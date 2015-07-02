$(function() {       
	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'dollars',		
		type: 'numeric', // set type, either numeric or text 
		is: function() { return false; }, // return false so parser is not auto-detected
		format: function(s) { 
		    // format your data for normalization 
		    return s.replace('$', '').replace(/monthly/i,'').replace(',', ''); 
		}
	}); 
	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'photos', 
		type: 'numeric', // set type, either numeric or text 
		is: function() { return false; }, // return false so parser is not auto-detected
		format: function(s) { 
		    return s.replace(/ photo\(s\)/i,''); 
		}
	}); 
	
        $("#listings-table").tablesorter({
		headers: { 
			2: { 
				sorter: 'dollars' 
			},
			6: {
				sorter: 'photos'
			}
		} 
        }); 
        
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
                $.fancybox({
			type: 'inline',
			content: $("#options-container"),
			autoSize: true
		});
	});

	// save options button
	$("#save-button").on('click', function(e) {
		e.preventDefault();
		saveOptions();
	});

	// hide/show tips link
	$("#hide-button").on('click', function(e) {
		e.preventDefault();
		
		if ($("#tips").is(':visible')) {
                        $("#tips").slideUp(700, 'easeOutQuad', function() {
				$("#hide-button").text('Show Getting Started');
				setCookie('showGettingStarted', false);
			});
                }
                else {
                        $("#tips").slideDown(700, 'easeOutQuad', function() {
				$("#hide-button").text('Hide Getting Started');
				setCookie('showGettingStarted', true);
			});
                }
	});

        // these two handle the mouse hovering over the table rows
        // we would like to change the related marker to blue on hover
        // and possibly max out its z-index so we can see it if there
        // are multiple markers on one spot
        $("#listings-table tbody tr").live('mouseenter', function() {
                var id = parseInt($(this).data('marker-id'), 10);
                var marker = markers[id];
                marker.setIcon('images/blue.png');
                marker.setZIndex(1000);
                $(this).addClass('hover');
        });

        $("#listings-table tbody tr").live('mouseleave', function() {
                var id = parseInt($(this).data('marker-id'), 10);
                var marker = markers[id];
                marker.setIcon('images/red.png');
                marker.setZIndex(id - 1);
                $(this).removeClass('hover');
        });
});