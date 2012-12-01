<html>
        <head>
                {include file="head.tpl"}
        </head> 
        <body>
                <div id="map-container"></div>
                <div id="button-container">
		     <div id="map-button" class="up-arrow">
			     <a href="#" class="fill-div" title="Show/Hide Map"></a>
		     </div>
		     <div id="config-button">
			     <a href="#" class="fill-div" title="Search Options"></a>
		     </div>
		</div>
		<div id="options-container">
			<h2>Search Options</h2>
			<table id="config-table">
				<tbody>
					<tr>
						<td style="vertical-align: top; padding-top: 1px;">For:</td>
						<td><label><input type="radio" name="for" value="rent" checked="checked" />Rent</label><br /><label><input type="radio" name="for" value="sale" />Sale</label></td>
					</tr>	
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr>
						<td><label for="min-price">Min. Price:</label></td>
						<td><input id="min-price" class="price-textbox" maxlength="7" name="min-price" value="0" /></td>
					</tr>	
					<tr>
						<td><label for="max-price">Max. Price:</label></td>
						<td><input id="max-price" class="price-textbox" maxlength="7" name="max-price" value="1400" /></td>
					</tr>
				</tbody>
				<tfoot>
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr>
						<td colspan="2"><label><input type="checkbox" id="photo" name="photo" /> Must have at least 1 photo</label></td>
					</tr>
					<tr>
						<td colspan="2"><label><input type="checkbox" id="address" name="address" /> Must have a listed address</label></td>
					</tr>
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr><td colspan="2" style="text-align: center;"><img id="spinner" src="images/spinner.gif" /><img id="check" src="images/check.png" /><button id="save-button">Save Options</button></td></tr>
				</tfoot>
			</table>		
		</div>
                <div id="body-container">
                        <h1>MLS Listing Search</h1>
                        <p>Welcome! This tool will help you find MLS listings in an area of interest.</p>
                        <div id="tips">
                                <h2>Getting started</h2>
                                <ul>
                                <li>First, adjust the rectangle on the map above to cover your desired living area.</li>
                                <ul>
					<li>To move the whole rectangle, click drag the marker in the center.</li>
					<li>To resize the rectangle, click and drag one of the white circles along its edge.</li>
				</ul>
                                <li>If you would like to make the map bigger or smaller, you can resize it by dragging the thick black line on the bottom edge of the map.</li>
                                <ul><li>You can also hide and show the map by clicking the arrow just underneath it.</li></ul>
                                <li>Your desired location and search options will be remembered when you come back! No need to log in.</li>
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