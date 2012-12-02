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