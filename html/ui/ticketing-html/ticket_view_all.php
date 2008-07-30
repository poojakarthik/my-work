<table class="reflex">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Viewing All Tickets
		</div>
		<div id="caption_options" name="caption_options">
		Owner: 
		<select id="x" name="x">
			<option id="a" name="a">all</option>
			<option id="a" name="a">Lee Jell</option>
			<option id="a" name="a">Cane McKinnon</option>
		</select>
		Category: 
		<select id="y" name="y">
			<option id="a" name="a">all</option>
			<option id="a" name="a">Adds Moves Changes</option>
			<option id="a" name="a">Faults</option>
		</select>
		Status: 
		<select id="z" name="z">
			<option id="a" name="a">all</option>
			<option id="a" name="a">Active</option>
			<option id="a" name="a">Assigned</option>
		</select>
		</div>
		</div>
	</caption>
	<thead>
		<tr>
			<th>ID</th>
			<th>Subject</th>
			<th>Last Actioned</th>
			<th>Received</th>
			<th>Owner</th>
			<th>Category</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th colspan=8 align=right> Total Tickets: XXXX</th>
		</tr>
	</tfoot>
	<tbody>

		<?

		for ($i = 1; $i <= 24; $i++) {
		if ($i % 2) {
		$tr_alt =  "alt";
		} else {
		$tr_alt = "";
		}
		?>

		<tr class="<?=$tr_alt?>">
			<td><a href="<?=$basepath?>ticket/view/<?=$i?>"><?=$i?></a></td>
			<td>one two three four five six seven eight nine ten the quick brown fox jumps over the lazy dog.</td>
			<td> 10:00 28/7/08</td>
			<td> 9:00 28/7/08</td>
			<td> Lee Jell</td>
			<td> Faults</td>
			<td> Assigned</td>
			<td>a b c d e f</td>
		</tr>
		<? 

		}

		?>
	</tbody>
</table>
