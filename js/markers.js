var markers = []; 

function clearMarkers() {
	for (var i in markers) {
		markers[i].setMap(null); // clear from map
	}
	
	markers = []; // reset array
}

function addMarker(latitude, longitude) {
        var marker = new google.maps.Marker({ 
                map: map, 
                icon: 'images/dot_small.gif',
                position: new google.maps.LatLng(latitude, longitude)
        });

        markers.push(marker); // add marker to list so we can remove it later
        
        // also add event listeners for when we hover over the marker
        google.maps.event.addListener(marker, "mouseover", mouseEnteredMarker);
        google.maps.event.addListener(marker, "mouseout", mouseLeftMarker);
}

function mouseEnteredMarker(e) {
}

function mouseLeftMarker(e) {
}