// process the listings we receive from our API. this should handle
// the condition of no listings returned. the 'delay' variable sets 
// the delay between showing pins on the map
var delay = 25;
var t1, t2; // timeouts

function processListings(data) {
	if (data.resultCount > 0) {
		// data is now grouped by provider. array index will indicate provider name
		for (var p in data.results) {
			var providerResults = data.results[p];
			var provider = providerResults.name;
			for (var i in providerResults.listings) {
				var listing = providerResults.listings[i];

				// make a new marker on the map for each listing.
				// also add results to the listings table.
				var markerId = addMarker(listing.address.latitude, listing.address.longitude);

				var row = $('#listing-row-template').clone().removeAttr('id').show();
				var encodedId = btoa([provider, listing.id, markerId].join('|'));

				row.attr('data-id', encodedId);
				row.attr('data-marker-id', markerId);
				row.attr('data-url', listing.MLSlink);
				row.find('.link').attr('href', listing.MLSlink).text(listing.MLSnumber);
				row.find('.maps-link').attr('href', 'http://maps.google.com/maps?q=' + encodeURIComponent(listing.address.text));
				row.find('.price').text(listing.price);
				row.find('.address')[0].innerHTML = listing.address.text.replace("\n", '<br />');
				row.find('.bedrooms').text(listing.bedrooms);
				row.find('.bathrooms').text(listing.bathrooms);
				row.find('.land-size').text(listing.landSize);
				row.find('.photos')[0].innerHTML = buildPhotosString(listing.photos.length);
				row.find('.remove-link').attr('onclick', 'hideListing("' + encodedId + '"); return false;');
				row.find('.undo-link').attr('onclick', 'undoHideListing("' + encodedId + '"); return false;');

				$("#listings-table tbody").append(row);
			}
		}
	}
	else {
		alert('There were no listings returned in the selected area.');
	}

	// tell the tablesorter that we've updated the table
	$("#listings-table").trigger("update");

	if (t1) {
		clearTimeout(t1);
	}
	t1 = setTimeout(function () {
		fetchAdditionalListingInformation();
	}, 500);
}

function clearListings() {
	$("#listings-table tbody tr").not('#listing-row-template').remove();
}

function fetchAdditionalListingInformation() {
	$('#listings-table tbody tr').not('#listing-row-template').each(function () {
		var row = $(this);
		$.post('ajax/details.php', {
			url: row.data('url'),
			provider: 'mls'
		}, function (data) {
			if (data.status === 'ok') {
				row.find('.age').text(data.info.age);
				row.find('.heating-fuel').text(data.info.heatingFuel);
				row.find('.heating-type').text(data.info.heatingType);
				row.find('.sewer').text(data.info.sewer);
				row.find('.water').text(data.info.water);
				row.find('.cooling').text(data.info.cooling);

				if (data.info.photos.length > 0) {
					row.find('.photos')[0].innerHTML = buildPhotosString(data.info.photos);
				}
			}
			else {
				console.log('Error fetching additional info for url ' + row.data('url'));
			}

			// set a timeout to update tablesorter after everything is done
			if (t2) {
				clearTimeout(t2);
			}
			t2 = setTimeout(function () {
				// tell the tablesorter that we've updated the table
				$("#listings-table").trigger("update");
			}, 500);
		}, 'json');
	});
}

function buildPhotosString(photos) {
	if (photos.length > 0) {
		var fancyPhotos = [];
		var fancyOptions = {padding: 0, loop: false};

		for (var p in photos) {
			fancyPhotos.push({href: photos[p], title: 'Photo ' + (parseInt(p, 10) + 1) + ' of ' + photos.length});
		}

		var onclickString = '$.fancybox.open(' + JSON.stringify(fancyPhotos) + ',' + JSON.stringify(fancyOptions) + ');';
		return '<a href=\'#\' onclick=\'' + onclickString + ' return false;\'>' + photos.length + ' photo(s)</a>';
	}
	return '---';
}