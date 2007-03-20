<table>
<td> &nbsp;&nbsp; </td>
<td>
<?php
	$package = $document['package'];
	
	echo "<div class='td_title'> Package : $package </div>";
	echo "<br><br>";
	
	if (is_array($document['class']))
	{
		echo "<div class='td_heading'>Classes</div>";
		echo "<table cellpadding='2'>";
		echo "<ul>";
		foreach ($document['class'] as $class=>$description)
		{
			echo "<tr><td><a href='$link$package.$class'>$class </a></td> <td>$description</td></tr>";
		}
		echo "</ul></table><br><br>";		
	}
	
	if (is_array($document['function']))
	{
		echo "<div class='td_heading'>Global Functions</div>";
		echo "<table cellpadding='2'>";
		echo "<ul>";
		foreach ($document['function'] as $function=>$description)
		{
			echo "<tr><td><a href='$link$package.$function'>$function</a></td> <td>$description</td></tr>";
		}
		echo "</ul></table><br><br>";
	}
	
	if (is_array($document['variable']))
	{
		echo "<div class='td_heading'>Global Variables</div>";
		echo "<table cellpadding='2'>";
		echo "<ul>";
		foreach ($document['variable'] as $variable=>$description)
		{
			echo "<tr><td><a href='$link$package.$variable'>$variable</a></td> <td>$description</td></tr>";
		}
		echo "</ul></table><br><br>";
	}

?>
</td>
</table>
<?php
	/*
	echo '<pre>';
	print_r($document);
	echo '<pre>';
	*/
?>
