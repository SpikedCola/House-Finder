<div id="tips"{if isset($smarty.cookies.showGettingStarted) && ($smarty.cookies.showGettingStarted == 'false')} style="display: none;"{/if}>
	<h2>Getting started</h2>
	<ul id="tips-list">
		<li>Adjust the rectangle on the map above to cover your desired living area.</li>
		<ul>
			<li>To move the whole rectangle, click drag the marker in the center.</li>
			<li>To resize the rectangle, click and drag one of the white circles along its edge.</li>
		</ul>
		<li class="no-bullet">&nbsp;</li>
		<li>Listings found inside the rectangle will automagically appear in the table below, as well as on the map.</li>
		<li class="no-bullet">&nbsp;</li>
		<li>The map can be made bigger or smaller by dragging the thick black line on the bottom edge of the map.</li>
		<ul><li>You can also hide and show the map by clicking the arrow just underneath it.</li></ul>
		<li class="no-bullet">&nbsp;</li>
		<li>Your desired location and search options will be remembered when you come back! No need to log in.</li>
		<li class="no-bullet">&nbsp;</li>
		<li>Results you've chosen to hide will be removed from the list when you change the search area or refresh the page.</li>
	</ul>
</div>
<br />
<div id="hide-tips">(<a id="hide-button" href="#">{if isset($smarty.cookies.showGettingStarted) && ($smarty.cookies.showGettingStarted == 'false')}Show{else}Hide{/if} Getting Started</a>)</div>	