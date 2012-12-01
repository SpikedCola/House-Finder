// ideally we should do the requests on the client-side, but we will run
// into cross-domain trouble, so itll have to be server-side :(

function doSearch() {
        // get the rectangle's bounds
        var bounds = rectangle.getBounds();
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { location: bounds.toUrlValue(10) }, // thanks google!
                success: function(data) {
			clearMarkers();
                        clearListings();
			
                        if (data.status) {
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

function ignoreListing(listing) {
	var user_id = getCookie('uniqueId');
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { id: listing, user_id: user_id }, // thanks google!
                success: function() {
			// strike out the line
                },
                error: function() {
                        showIgnoreListingError();     
                }
        });
}