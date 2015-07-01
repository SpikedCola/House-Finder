// process the listings we receive from our API. this should handle
// the condition of no listings returned. the 'delay' variable sets 
// the delay between showing pins on the map
var delay = 25;

function processListings(data) {
	if (data.listings.length > 0) {
		// data is now grouped by provider. array index will indicate provider name
		for (var provider in data.listings) {
			var listings = data.listings[provider];
			for (var i in listings) {
				var listing = listings[i];
			
				var id = parseInt(i, 10);

				// make a new marker on the map for each listing.
				// also add results to the listings table.
				addMarker(listing.address.latitude, listing.address.longitude);

				var linkString = '---';

				if (listing.photos.length > 0) {
					var photos = listing.photos;
					var fancyPhotos = [];
					var fancyOptions = { padding: 0, loop: false };

					for (var p in photos) {
						fancyPhotos.push({ href: photos[p], title: 'Photo ' + (parseInt(p, 10) + 1) + ' of ' + photos.length });
					}

					var onclickString = '$.fancybox.open(' + JSON.stringify(fancyPhotos) + ',' + JSON.stringify(fancyOptions) + ');';
					linkString = '<a href=\'#\' onclick=\'' + onclickString + ' return false;\'>' + photos.length + ' photo(s)</a>';
				}
				
				var row = $('#listing-row-template').clone().removeAttr('id').show();
				
				row.attr('data-id', id);
				row.find('#link').attr('href', listing.MLSlink).text(listing.MLSnumber);
				row.find('#price').text(listing.price);
				row.find('#address').text(listing.address.text);
				row.find('#bedrooms').text(listing.bedrooms);
				row.find('#bathrooms').text(listing.bathrooms);
				row.find('#land-size').text(listing.landSize);
				row.find('#photos')[0].innerHTML = linkString;

				$("#listings-table tbody").append(row);
			}
		}
	}
	else {
		alert('There were no listings returned from MLS in the selected area.');		
	}

        // tell the tablesorter that we've updated the table
        $("#listings-table").trigger("update"); 
}

function clearListings() {
        $("#listings-table tbody tr").not('#listing-row-template').remove();
}