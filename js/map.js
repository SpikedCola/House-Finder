var map, rectangle;

function initialize() {
        var mapOptions = {
                center: new google.maps.LatLng(43.56, -79.67),
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById("map-container"), mapOptions);
         
        // constructs a rectangle from the points at 
        // its south-west and north-east corners
        var bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(43.5, -79.74), 
                new google.maps.LatLng(43.6, -79.62)
        );
        
        rectangle = new google.maps.Rectangle({
                bounds: bounds,
                editable: true,
                map: map
        });
        
        marker = new google.maps.Marker({ 
                map: map, 
                position: rectangle.getBounds().getCenter(), // initially place the marker at the center of the rectangle
                draggable: true, 
                raiseOnDrag:false
        });
        
        marker.setAnimation(null);

        // we need to give the map an initial 'center', but let's
        // move the map to be centered on the marker. there's 
        // probably a better way to do this
        map.setCenter(rectangle.getBounds().getCenter());
        
        // when we drag the marker, move the rectangle to the center of the new position
        google.maps.event.addListener(marker, 'drag', function(event) {
                rectangle.setCenter(event.latLng);
        });

        // when we resize the rectangle, update the position of the marker
        google.maps.event.addListener(rectangle, 'bounds_changed', function() {;
                marker.setPosition(rectangle.getBounds().getCenter());
        });

        // one-time trigger to get search results on page load
        google.maps.event.addListenerOnce(map, 'idle', doSearch);
}
     
google.maps.Rectangle.prototype.setCenter = function(latLng) {
        var lat = latLng.lat();
        var lng = latLng.lng();

        var sw = this.getBounds().getSouthWest();
        var ne = this.getBounds().getNorthEast();
        
        var width = ne.lng() - sw.lng();
        var height = ne.lat() - sw.lat();
        
        var bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(lat - height/2.0, lng - width/2.0),
                new google.maps.LatLng(lat + height/2.0, lng + width/2.0)
        );
        
        this.setOptions({ bounds: bounds });
};

google.maps.event.addDomListener(window, 'load', initialize);