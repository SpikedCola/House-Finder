// process the listings we receive from our API. this should handle
// the condition of no listings returned. the 'delay' variable sets 
// the delay between showing pins on the map
var delay = 25;

function processListings(data) {
	if (data.results.length > 0) {
		var results = data.results;
		
		for (var i in results) {
			var result = results[i];
                        var photos = result.photos;
                        var bedrooms = [];
			var id = parseInt(i, 10);
			
			// make a new marker on the map for each listing.
                        // also add results to the listings table.
			addMarker(result.latitude, result.longitude);
                        
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
                        
                        var linkString = '---';
			
			if (photos.length > 0) {
                                var fancyPhotos = [];
                                var fancyOptions = { padding: 0, loop: false };
                                
				for (var p in photos) {
                                        fancyPhotos.push({ href: photos[p], title: 'Photo ' + (parseInt(p, 10) + 1) + ' of ' + photos.length });
				}
                
                                var onclickString = '$.fancybox.open(' + JSON.stringify(fancyPhotos) + ',' + JSON.stringify(fancyOptions) + ');';
                                linkString = '<a href=\'#\' onclick=\'' + onclickString + ' return false;\'>' + photos.length + ' photo(s)</a>';
                        }
                
			//Map Marker	Price	Address	Bedrooms	Bathrooms	Photos
			var row = $('<tr class="' + (i % 2 != 0 ? 'odd' : '') + '">' + 
				'<td class="id">' + (id + 1) + '</td>' +
				'<td><a href="http://mls.ca/propertyDetails.aspx?propertyId=' + result.propertyId + '&PidKey=' + result.pidKey + '" title="MLS Listing" target="_blank">MLS Listing</a></td>' +
				'<td>' + result.price + ' ' + result.frequency + '</td>' +
				'<td>' + result.address + '</td>' +
				'<td>' + bedrooms.join(', ') + '</td>' +
				'<td>' + result.bathrooms + '</td>' +
				'<td>' + linkString + '</td>' +
				'<td class="nofade"><a title="Hide This Listing" href="#" class="remove-link" onclick="hideListing(' + id + ', \'' + result.id + '\'); return false;"><img src="images/x.png" /></a><a href="#" title="Undo" class="undo-link" style="display: none;" onclick="undoHideListing(' + id + ', \'' + result.id + '\'); return false;"><img src="images/undo.png" /></a></td>' +
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

function clearListings() {
        $("#listings-table tbody tr").remove();
}