// ideally we should do the requests on the client-side, but we will run
// into cross-domain trouble, so itll have to be server-side :(

function doSearch() {
        // get the rectangle's bounds
        var bounds = rectangle.getBounds();
        $.ajax({
                url: '/ajax/search.php',
                type: 'post',
                dataType: 'json',
                data: { location: bounds.toUrlValue(10) }, // thanks google!
                success: function(data) {
			clearMarkers();
                        clearListings();
			
                        if (data !== null && data.status) {
				switch (data.status) {
					case 'ok':
						processListings(data);
						break;
					case 'none':
						showNoResults();
						break;
					case 'toomany':
						showTooMany();
						break;
					default: 
						showSearchError();
						break;
				}
                        }
                        else {
                                showSearchError();
                        }
                },
                error: function() {
			clearMarkers();
                        clearListings();
                        showSearchError();     
                }
        });
}

function hideListing(id, listing) {
        $.ajax({
                url: '/ajax/ignore.php',
                type: 'post',
                dataType: 'json',
                data: { id: listing }, 
                success: function() {
			// strike out the row
			var row = $("#listings-table tbody tr").eq(id);
			row.find('td').not('.nofade').animate({ opacity: 0.25 }, 400, 'easeOutQuad', function() {
				row.find('.remove-link').hide();
				row.find('.undo-link').show();
				markers[id].setMap(null);
				row.css('text-decoration', 'line-through');
			});
                },
                error: function() {
                        showIgnoreListingError();     
                }
        });
}


function undoHideListing(id, listing) {
        $.ajax({
                url: '/ajax/ignore.php',
                type: 'post',
                dataType: 'json',
                data: { undo: true, id: listing }, 
                success: function() {
			// strike out the line
			var row = $("#listings-table tbody tr").eq(id);
			row.css('text-decoration', 'none');
			row.find('.undo-link').hide();
			row.find('.remove-link').show();
			row.find('td').not('.nofade').animate({ opacity: 1 }, 400, 'easeOutQuad', function() {
				markers[id].setMap(map);
			});
                },
                error: function() {
                        showUndoIgnoreListingError();     
                }
        });
}

function saveOptions() {
	var type = $("input[name='for']:checked").val();
	var minPrice = parseInt($("#min-price").val(), 10);
	var maxPrice = parseInt($("#max-price").val(), 10);
	var photos = $("#photo").attr('checked') == 'checked';
	var address = $("#address").attr('checked') == 'checked';
	
        $.ajax({
                url: '/ajax/save_options.php',
                type: 'post',
                dataType: 'json',
                data: { 
			type: type,
			minPrice: minPrice,
			maxPrice: maxPrice,
			photos: photos,
			address: address
		}, 
                success: function(data) {
			if (data.error) {
				alert(data.error);
			}
			else {
				doSearch(); // refresh listings after updating settings
				$.fancybox.close();
			}
                },
                error: function() {
                        showSaveOptionsError(); 
                }
        });
}