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

// process the listings we receive from our API. this should handle
// the condition of no listings returned. the 'delay' variable sets 
// the delay between showing pins on the map
var markers = []; 
var delay = 25;

function processListings(data) {
	if (data.results.length > 0) {
		var results = data.results;
		
		for (var i in results) {
			var result = results[i];
                        var photos = result.photos;
                        
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
                        
                        // if no bedrooms were sent, display '---'
                        if (bedrooms.length == 0) {
                                bedrooms.push('---');
                        }

                        //same with address
			if (result.address == '') {
                                result.address = '---';
                        }
                        
                        var linkString = '0';
			
			if (photos.length > 0) {
                                var fancyPhotos = [];
                                var fancyOptions = { padding: 0, loop: false};
                                
				for (var p in photos) {
                                        fancyPhotos.push({ href: photos[p], title: 'Photo ' + (parseInt(p, 10) + 1) });
				}
                
                                var onclickString = '$.fancybox.open(' + JSON.stringify(fancyPhotos) + ',' + JSON.stringify(fancyOptions) + ');';
                                linkString = '<a href=\'#\' onclick=\'' + onclickString + ' return false;\'>' + photos.length + '</a>';
                        }
                
			//Map Marker	Price	Address	Bedrooms	Bathrooms	Photos
			var row = $('<tr class="' + (i % 2 == 0 ? 'even' : 'odd') + '">' + 
				'<td>' + (parseInt(i, 10) + 1) + '</td>' +
				'<td>' + result.price + ' ' + result.frequency + '</td>' +
				'<td>' + result.address + '</td>' +
				'<td>' + bedrooms.join(', ') + '</td>' +
				'<td>' + result.bathrooms + '</td>' +
				'<td>' + linkString + '</td>' +
				'</tr>');
			
			$("#listings-table tbody").append(row);
		}
	}
	else {
		alert('There were no listings returned from MLS in the selected area.');		
	}

        // tell the tablesorter that we've updated the table
        $("#listings-table").trigger("update"); 
}

function clearMarkers() {
	for (var i in markers) {
		markers[i].setMap(null); // clear from map
	}
	
	markers = []; // reset array
}