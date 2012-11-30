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
                        <div id="tips">
                                <h2>Tips:</h2>
                                <ul>
                                <li>To get started, you will need to adjust the rectangle on the map above to cover your desired living area.</li>
                                <li>To move the whole rectangle, drag the marker in the center. To resize the rectangle, drag one of the white circles along the edge.</li>
                                <li>If you would like to see more or less of the map, you can resize it by dragging the black line at the bottom of the map.</li>
                                </ul>
                                <p>MLS listings found inside the rectangle will automagically appear below.</p>
                        </div>
                        <div id="hide-tips">(<a id="hide-tips-button" href="#">Show/Hide Tips</a>)</div>
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
                                                <!-- Listings go here :) -->
                                        </tbody>
                                </table>
                        </div>
                </div>
        </body>
</html>