<h2>Search Options</h2>
<table id="optionsTable">
	<tbody>
		<tr>
			<td style="vertical-align: top; padding-top: 1px;">Show Properties For</td>
			<td>
				<label><input type="radio" name="for" value="rent" {if $user->search_type == 'rent'}checked="checked"{/if} />Rent</label><br />
				<label><input type="radio" name="for" value="sale" {if $user->search_type == 'sale'}checked="checked"{/if} />Sale</label>
			</td>
		</tr>	
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr>
			<td><label for="min-price">Min. Price:</label></td>
			<td><input id="min-price" class="price-textbox" name="min-price" type="number" min="0" max="99999999" value="{{$user->min_price|default:0}}" /></td>
		</tr>	
		<tr>
			<td><label for="max-price">Max. Price:</label></td>
			<td><input id="max-price" class="price-textbox" name="max-price" type="number" min="0" max="99999999" value="{{$user->max_price|default:2000}}" /></td>
		</tr>
	</tbody>
	<tfoot>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr>
			<td colspan="2"><label><input type="checkbox" id="photo" name="photo" {if $user->photos}checked="checked"{/if} /> Must have at least 1 photo</label></td>
		</tr>
		<tr>
			<td colspan="2"><label><input type="checkbox" id="address" name="address" {if $user->address}checked="checked"{/if} /> Must have a listed address</label></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2" style="text-align: center;"><img id="spinner" src="images/spinner.gif" /><img id="check" src="images/check.png" /><button id="save-button">Save Options</button></td></tr>
	</tfoot>
</table>