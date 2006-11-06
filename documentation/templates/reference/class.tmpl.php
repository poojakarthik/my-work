<h3>Class : <?php echo $document['class'] ?></h3>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<h3>Description</h3>
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
		echo "<b>Methods</b>";
		echo "<ul>";
		foreach ($document['method'] as $method=>$description)
		{
			echo "<li><a href='$link$package.$instance.$method'>$method</a> -- $description</li>";
		}
		echo "</ul><br><br>";
	}
	
	if (is_array($document['property']))
	{
		echo "<b>Properties</b>";
		echo "<ul>";
		foreach ($document['property'] as $property=>$description)
		{
			echo "<li><a href='$link$package.$instance.$property'>$property</a> -- $description</li>";
		}
		echo "</ul><br><br>";
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
