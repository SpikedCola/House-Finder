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
	<h1>MLS Listing Search</h1>
	Welcome! This tool will help you find MLS listings in exactly the area you want.
	<br />
	<div id="tips"{if isset($smarty.cookies.showGettingStarted) && ($smarty.cookies.showGettingStarted == 'false')} style="display: none;"{/if}>
		{include file="tips.tpl"}	
	</div>
	<br />
	<div id="hide-tips">(<a id="hide-button" href="#">{if isset($smarty.cookies.showGettingStarted) && ($smarty.cookies.showGettingStarted == 'false')}Show{else}Hide{/if} Getting Started</a>)</div>
	<div id="listings-container">
		<table id="listings-table">
			<thead>
				<tr>
					<th>Map Marker</th>
					<th></th>
					<th>Price</th>
					<th>Address</th>
					<th>Bedrooms</th>
					<th>Bathrooms</th>
					<th>Photos</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<!-- Listings go here :) -->
			</tbody>
		</table>
	</div>
</div>