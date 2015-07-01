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
                icon: 'images/red.png',
                position: new google.maps.LatLng(latitude, longitude),
                zIndex: markers.length
        });

        // also add event listeners for when we hover over the marker
        google.maps.event.addListener(marker, "mouseover", mouseEnteredMarker);
        google.maps.event.addListener(marker, "mouseout", mouseLeftMarker);
        google.maps.event.addListener(marker, "click", clickMarker);
	
        markers.push(marker); // add marker to list so we can remove it later

}

function mouseEnteredMarker() {
        this.setIcon('images/blue.png');
        var id = parseInt(markers.indexOf(this), 10);
        markers[id].setZIndex(1000);
        $('#listings-table tbody tr').eq(id).addClass('hover');
}

function mouseLeftMarker() {
        this.setIcon('images/red.png');
        var id = parseInt(markers.indexOf(this), 10);
        markers[id].setZIndex(id);
        $('#listings-table tbody tr').eq(id).removeClass('hover');
}

function clickMarker() {
        var id = parseInt(markers.indexOf(this), 10);
	var row = $('#listings-table tbody tr').eq(id);	
        google.maps.event.clearListeners(this, "mouseout");//, mouseLeftMarker);
	$('html, body').stop().animate({
		'scrollTop': row.offset().top
	}, 700, 'swing');
	setTimeout(function() {
		var color = '#ffffff';
		if (row.hasClass('odd')) {
			color = '#eee';
		}
		row.animate({ 'background-color': color }).removeClass('hover');
		google.maps.event.addListener(markers[id], "mouseout", mouseLeftMarker);
		google.maps.event.trigger(markers[id], "mouseout");
	}, 3000);
	
}