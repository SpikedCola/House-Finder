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

function ignoreListing(listing) {
	var user_id = readCookie('uniqueId');
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { action: 'ignore', id: listing, user_id: user_id }, 
                success: function() {
			// strike out the line
                },
                error: function() {
                        showIgnoreListingError();     
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