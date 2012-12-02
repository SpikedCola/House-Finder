// ideally we should do the requests on the client-side, but we will run
// into cross-domain trouble, so itll have to be server-side :(

function doSearch() {
        // get the rectangle's bounds
        var bounds = rectangle.getBounds();
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { action: 'search', location: bounds.toUrlValue(10) }, // thanks google!
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
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { action: 'ignore', id: listing }, 
                success: function() {
			// strike out the line
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
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { action: 'ignore', undo: true, id: listing }, 
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
	var user_id = readCookie('uniqueId');
	var type = $("input[name='for']:checked").val();
	var minPrice = parseInt($("#min-price").val(), 10);
	var maxPrice = parseInt($("#max-price").val(), 10);
	var photo = $("#photo").attr('checked') == 'checked' ? true : false;
	var address = $("#address").attr('checked') == 'checked' ? true : false;
	
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { 
			action: 'save',
			type: type,
			minPrice: minPrice,
			maxPrice: maxPrice,
			photo: photo,
			address: address 
		}, 
                success: function() {
			$("#spinner").hide();
			$("#check").show();
			$("#options-container").delay(1000).slideUp(700, function() {
				$("#check").hide();
				$("#save-button").show();
			});
                },
                error: function() {
			$("#spinner").hide();
			$("#save-button").show();
                        showSaveOptionsError(); 
                }
        });
}