var map, centerMarker, rectangle, dragging;

function initialize() {
        var startLocation = new google.maps.LatLng(43.56, -79.67); 
        var rectangleBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(43.5, -79.74), 
                new google.maps.LatLng(43.6, -79.62)
        );
        var startZoom = 12;
        
        lastLocation = readCookie('lastLocation');
        if (lastLocation !== null) {
                // have a location from last time. center the map and set the rectangle
                var coords = lastLocation.split(',');
                if (coords.length == 4) {
                        var sw = new google.maps.LatLng(coords[0], coords[1]);
                        var ne = new google.maps.LatLng(coords[2], coords[3]);
                        rectangleBounds = new google.maps.LatLngBounds(sw, ne);
                        startLocation = rectangleBounds.getCenter();
                }
        }
        
        lastZoom = readCookie('lastZoom');
        if (lastZoom !== null) {
                startZoom = parseFloat(lastZoom);
        }
        
        var mapOptions = {
                center: startLocation,
                zoom: startZoom,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false, // disable street view
		mapTypeControl: false // disable satellite view
        };

        map = new google.maps.Map(document.getElementById("map-container"), mapOptions);
         
        // constructs a rectangle from the points at 
        // its south-west and north-east corners
        rectangle = new google.maps.Rectangle({
                bounds: rectangleBounds,
                editable: true,
                map: map
        });
        
	// this marker will be the center of the rectangle
        centerMarker = new google.maps.Marker({ 
                map: map, 
                position: rectangle.getBounds().getCenter(),
                draggable: true, 
                raiseOnDrag:false
        });
        
        centerMarker.setAnimation(null);

        // we gave the map an initial position, but let's
        // move the map to be centered on the marker. there's 
        // probably a better way to do this
        map.setCenter(rectangle.getBounds().getCenter());
        
        // == event listeners ==
        
	// we need to differentiate between bounds_changed when we are dragging
	// (automatically resizing the rectangle) vs the user changing the bounds
	// and thus wanting to doSearch. flag in dragstart and dragend
	// so we know when we're dragging
	
        // when we drag the marker, center the rectangle on the new position
        google.maps.event.addListener(centerMarker, 'dragstart', function() {
		dragging = true;
        });

        // when we drag the marker, center the rectangle on the new position
        google.maps.event.addListener(centerMarker, 'drag', function(event) {
                rectangle.setCenter(event.latLng);
        });

        // when we stop dragging the marker, get new search results
        google.maps.event.addListener(centerMarker, 'dragend', function() {
		doSearch();
                setCookie('lastLocation', rectangle.bounds.toUrlValue(10)); // cookie the new location
		dragging = false; // flag after searching so we dont search twice
        });

        // when we resize the rectangle, update the position of the marker
        google.maps.event.addListener(rectangle, 'bounds_changed', function(e) {;
                centerMarker.setPosition(rectangle.getBounds().getCenter());
		if (!dragging) {
			doSearch(); // only search if we're not dragging
                        setCookie('lastLocation', rectangle.bounds.toUrlValue(10)); // cookie the new location
		}
        });

        // drop a cookie when the user changes zoom levels so we can remember this too
        google.maps.event.addListener(map, 'zoom_changed', function() {
                setCookie('lastZoom', map.getZoom()); // cookie the new location
        });
        // one-time trigger to get search results on page load
        google.maps.event.addListenerOnce(map, 'idle', mapReady);
}

google.maps.event.addDomListener(window, 'load', initialize);

// this function will center a rectangle on the given latLng.
// *should* take the size of the rectangle into account
google.maps.Rectangle.prototype.setCenter = function(latLng) {
        var lat = latLng.lat();
        var lng = latLng.lng();

	// need to know the size of our rectangle
        var sw = this.getBounds().getSouthWest();
        var ne = this.getBounds().getNorthEast();
        var width = ne.lng() - sw.lng();
        var height = ne.lat() - sw.lat();
        
	// create the new bounds of the rectangle
        var bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(lat - height/2.0, lng - width/2.0),
                new google.maps.LatLng(lat + height/2.0, lng + width/2.0)
        );
        
        this.setOptions({ bounds: bounds });
};

// function called when the map is all done
function mapReady() {
        $("#map-container").resizable({ handles: 's', resize: function() { google.maps.event.trigger(map, 'resize'); } }); // only allow bottom resizing
        doSearch();
}