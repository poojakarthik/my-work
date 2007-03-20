<table>
<td> &nbsp;&nbsp; </td>
<td>
<div class='td_title'>Class : <?php echo $document['class'] ?></div>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<div class='td_heading'>Description</div>
<p>
	<?php
		if (trim($document['parent']))
		{
			echo "{$document['parent']}$join{$document['class']}";
		}
		else
		{
			echo $document['class'];
		}
	?>
	<br>
	<br>
	<?php echo nl2br($document['long_description']); ?>
	<br>
	<br>
	<?php
		if (trim($document['parent']))
		{
			echo "Subclass of class : {$document['parent']}";
		}
		else
		{
			echo "Base class";
		}
	?>
	<br>
	<br>
	Member of package : <?php echo $document['package'] ?>
	<br>
	<br>
</p>


<?php	
	if (is_array($document['method']))
	{
		echo "<div class='td_heading'>Methods</div>";
		echo "<table cellpadding=2>";
		echo "<ul>";
		foreach ($document['method'] as $method=>$description)
		{
			echo "<tr><td><a href='$link$package.$instance.$method'>$method</a></td> <td>$description</td></tr>";
		}
		echo "</ul></table><br><br>";
	}
	
	if (is_array($document['property']))
	{
		echo "<div class='td_heading'>Properties</div>";
		echo "<table cellpadding=2>";
		echo "<ul>";
		foreach ($document['property'] as $property=>$description)
		{
			echo "<tr><td><a href='$link$package.$instance.$property'>$property</a></td> <td> $description</td></tr>";
		}
		echo "</ul></table><br><br>";
	}
?>
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

<?php
	/*
	echo '<pre>';
	print_r($document);
	echo '<pre>';
	*/
?>
</td>
</table>
