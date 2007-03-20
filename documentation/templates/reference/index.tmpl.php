<table>
<td> &nbsp;&nbsp; </td>
<td>

<div class='td_title'>APhPLIX Packages</div>
<br>
<?php
	if (is_array($document['package']))
	{
		foreach ($document['package'] as $key=>$package)
		{
			echo "<a href='{$link}package.$package'>$package</a><br>";
		}
	}
	else
	{
		echo 'no packages';
	}
?>

</td>
</table>
