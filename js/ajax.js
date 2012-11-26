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
						showError();
						break;
				}
                        }
                        else {
                                showError();
                        }
                },
                error: function() {
			clearMarkers();
                        showError();     
                }
        });
}

// so we dont have duplicate code
function showError() {
        alert('An error occured while trying to get MLS listings. Please try again later.');
}

function showTooMany() {
	alert('Too many results were returned for the given search area. Please reduce the size of the search area and try again.');
}

function showNoResults() {
	alert('No results were found in the given search area.');
}

function clearMarkers() {
	for (var i in markers) {
		markers[i].setMap(null); // clear from map
	}
	
	markers = []; // reset array
}

// process the listings we receive from our API. this should handle
// the condition of no listings returned. the 'delay' variable sets 
// the delay between showing pins on the map
var markers = []; 
var delay = 25;

function processListings(data) {
	if (data.results.length > 0) {
		var results = data.results;
		
		for (var i in results) {
			// make a new marker on the map for each listing. also add
			// to the listings table
			
			var marker = new google.maps.Marker({ 
				map: map, 
				icon: 'http://www.mls.ca/presentation/images/en-ca/icons/dot_small.gif',
				position: new google.maps.LatLng(results[i].Latitude, results[i].Longitude)
			});

			markers.push(marker); // add marker to list so we can remove it later
		}
	}
	else {
		alert('There were no listings returned from MLS in the selected area.');		
	}
}