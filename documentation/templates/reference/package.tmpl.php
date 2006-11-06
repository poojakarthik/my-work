<?php
	$package = $document['package'];
	
	echo "<h3>Package : $package</h3>";
	
	if (is_array($document['class']))
	{
		echo "<b>Classes</b>";
		echo "<ul>";
		foreach ($document['class'] as $class=>$description)
		{
			echo "<li><a href='$link$package.$class'>$class </a> -- $description</li>";
		}
		echo "</ul><br><br>";		
	}
	
	if (is_array($document['function']))
	{
		echo "<b>Global Functions</b>";
		echo "<ul>";
		foreach ($document['function'] as $function=>$description)
		{
			echo "<li><a href='$link$package.$function'>$function</a> -- $description</li>";
		}
		echo "</ul><br><br>";
	}
	
	if (is_array($document['variable']))
	{
		echo "<b>Global Variables</b>";
		echo "<ul>";
		foreach ($document['variable'] as $variable=>$description)
		{
			echo "<li><a href='$link$package.$variable'>$variable</a> -- $description</li>";
		}
		echo "</ul><br><br>";
	}

?>

<?php
	/*
	echo '<pre>';
	print_r($document);
	echo '<pre>';
	*/
?>
