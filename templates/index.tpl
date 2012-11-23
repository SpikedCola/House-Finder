<html>
        <head>
                <script src="js/jquery-1.8.3.min.js"></script>
                <script src="js/jquery-ui-1.9.2.custom.min.js"></script>
                <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBIeGZ0goJKoy7ixt2ARwaGt6VBJyUXY1I&sensor=false"></script>
                <link href="css/main.css" rel="stylesheet">
                <link href="css/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
                <script src="js/map.js"></script>
                <script src="js/main.js"></script>
                <script src="js/mls.js"></script>
        </head> 
        <body>
                <div id="map-container"></div>
                <div id="map-button" class="up-arrow"><a href="#" class="fill-div" title="Show/Hide Map"></a></div>
                <div id="body-container">
                        <h1>MLS Listing Search</h1>
                        <p>Welcome! This tool will help you find MLS listings in an area of interest.</p>
                        <p>To get started, adjust the rectangle on the map above to cover your desired living area. MLS listings found inside the rectangle will automatically appear below.</p>
                        <div id="listings-container">
                                <table id="listings-table">
                                        <thead>
                                                <tr>
                                                        <th>Map Marker</th>
                                                        <th>Price</th>
                                                        <th>Address</th>
                                                        <th>Bedrooms</th>
                                                        <th>Bathrooms</th>
                                                        <th>Photos</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <tr>
                                                        
                                                </tr>
                                        </tbody>
                                </table>
                        </div>
                </div>
        </body>
</html>