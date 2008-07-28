<? include('header.php'); ?>

		<table id="ticketing" name="ticketing">
			<caption>
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
					<td><?=$i?></td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
				</tr>
<? 

}

?>
			</tbody>
		</table>
<? include('footer.php'); ?>
