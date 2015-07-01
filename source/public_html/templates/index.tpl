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
	Welcome! This tool will help you find a house, in exactly the area you want, meeting your exact criteria.
	<br />
	{include file="tips.tpl"}
	<div id="listings-container">
		<table id="listings-table">
			<thead>
				<tr>
					<th>MLS Listing</th>
					<th>Price</th>
					<th>Address</th>
					<th>Land Size</th>
					<th>Bedrooms</th>
					<th>Bathrooms</th>
					<th>Photos</th>
					<th>Ignore Listing</th>
				</tr>
			</thead>
			<tbody>
				<!-- Listings go here :) -->
				<tr id="listing-row-template" style="display:none;">
					<td>
						<a id="link" href="" title="MLS Listing" target="_blank"></a>
					</td>
					<td id="price"></td>
					<td id="address"></td>
					<td id="land-size"></td>
					<td id="bedrooms"></td>
					<td id="bathrooms"></td>
					<td id="photos"></td>
					<td class="nofade">
						<a title="Hide This Listing" href="#" class="remove-link" onclick="hideListing(' + id + ', \'' + listing.id + '\'); return false;">
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