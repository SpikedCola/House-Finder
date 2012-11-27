<html>
        <head>
                {include file="head.tpl"}
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
                                        </tbody>
                                </table>
                        </div>
                </div>
        </body>
</html>