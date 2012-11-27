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
		
		// want to keep the var i = ... format so we have a row counter
		for (var i = 0; i < results.length; i++) {
			var result = results[i];
			// make a new marker on the map for each listing. also add
			// to the listings table
			
			var marker = new google.maps.Marker({ 
				map: map, 
				icon: 'images/dot_small.gif',
				position: new google.maps.LatLng(result.Latitude, result.Longitude)
			});

			markers.push(marker); // add marker to list so we can remove it later
			
			var bedrooms = [];
			
			if (result.bedrooms.above > 0) {
				bedrooms.push(result.bedrooms.above + ' above ground');
			}
			
			if (result.bedrooms.below > 0) {
				bedrooms.push(result.bedrooms.below + ' below ground');
			}
			
			var bedroomString = bedrooms.join(', ');
			var photoString = '';
			
			if (results.photos.length > 0) {
				for (var p in result.photos) {
					photoString += '';
				}
			}
			//Map Marker	Price	Address	Bedrooms	Bathrooms	Photos
			var row = $('<tr>' + 
				'<td>' + (i + 1) + '</td>' +
				'<td>' + result.price + ' ' + result.frequency + '</td>' +
				'<td>' + result.address + '</td>' +
				'<td>' + bedroomString + '</td>' +
				'<td>' + result.bathrooms + '</td>' +
				'<td>' + r + '</td>' +
				'</tr>');
			
			$("#listings-table tbody").append(row);
		}
	}
	else {
		alert('There were no listings returned from MLS in the selected area.');		
	}
}