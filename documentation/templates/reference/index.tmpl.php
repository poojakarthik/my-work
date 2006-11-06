<h3>APhPLIX Packages</h3>
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
