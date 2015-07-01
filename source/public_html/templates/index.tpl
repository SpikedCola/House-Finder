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
	{include file="search_options.tpl"}		
</div>
<div id="body-container">
	<h1>House Finder</h1>
	Welcome! This tool will help you find a house, in exactly the area you want, meeting your exact criteria (if such a house exists!)
	<br />
	{include file="tips.tpl"}
	<div id="listings-container">
		<table id="listings-table">
			<thead>
				<tr>
					<th>MLS Listing</th>
					<th>Google Maps</th>
					<th>Price</th>
					<th>Address</th>
					<th>Land Size</th>
					<th>Bed / Bath</th>
					<th>Age</th>
					<th>Heating Fuel</th>
					<th>Heating Type</th>
					<th>Cooling</th>
					<th>Water</th>
					<th>Sewer</th>
					<th>Photos</th>
					<th>Ignore</th>
				</tr>
			</thead>
			<tbody>
				<!-- Listings go here :) -->
				<tr id="listing-row-template" style="display:none;">
					<td>
						<a class="link" href="" title="MLS Listing" target="_blank"></a>
					</td>
					<td>
						<a class="maps-link" href="" title="Google Maps" target="_blank">Google Maps</a>
					</td>
					<td class="price"></td>
					<td class="address"></td>
					<td class="land-size"></td>
					<td><span class="bedrooms"></span> / <span class="bathrooms"></span></td>
					<td class="age"></td>
					<td class="heating-fuel"></td>
					<td class="heating-type"></td>
					<td class="cooling"></td>
					<td class="water"></td>
					<td class="sewer"></td>
					<td class="photos"></td>
					<td class="nofade">
						<a title="Hide This Listing" href="#" class="remove-link" onclick="">
							<img src="images/x.png" />
						</a>
						<a href="#" title="Undo" class="undo-link" style="display: none;" onclick="undoHideListing(' + id + ', \'' + listing.id + '\'); return false;">
							<img src="images/undo.png" />
						</a>
					</td>
					
				</tr>
			</tbody>
		</table>
	</div>
</div>