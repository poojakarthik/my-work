<table>
<td> &nbsp;&nbsp; </td>
<td>
<div class='td_title'><?php echo $document['title'] ?></div>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<div class='td_heading'>Description</div>
<p>
	<?php echo $document['title'] ?>
	<br>
	<br>
	<?php echo nl2br($document['long_description']); ?>
	<br>
	<br>
	Global Constant
	<br>
	<br>
	Member of package : <?php echo $document['package'] ?>
	<br>
	<br>
</p>

<div class='td_heading'>Usage</div>
<p>
	<br>
	Type : &nbsp; <?php echo $document['type']; ?>
	<br>
	<br>
	<?php
		if (is_array($document['see']))
		{
			echo 'See also ';
			unset($see);
			foreach($document['see'] as $key=>$value)
			{
				$see[] = "<a href=\"$link{$document['package']}.".str_replace('->', '.', $value)."\">$value</a>";
			}
			$see = implode(', ', $see);
			echo $see;
		}
	?>
	<br>
	<br>
</p>


<?php
	/*
	echo '<pre>';
	print_r($document);
	echo '<pre>';
	*/
?>
</td>
</table>
