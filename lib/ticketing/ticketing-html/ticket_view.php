<table id="ticketing" name="ticketing">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Viewing ticket: <?=$args[2]?>
		</div>
		<div id="caption_options" name="caption_options">
            <a href="#">Take</a> |
			<a href="#">Re-assign</a> |
			<a href="#">Assign</a>
		</div>
		</div>
	</caption>
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th colspan="2"><input type="submit" id="submit" name="submit" value="Edit" /></th>
		</tr>
	</tfoot>
	<tbody>
		<form id="add_ticket" name"add_ticket">
		<tr class="alt">
			<td class="title">Subject: </td>
			<td>a</td>
		</tr>
		<tr class="alt">
			<td class="title">Owner: </td>
			<td>Lee Jell</td>
		</tr>
		</form>
	</tbody>
</table>
