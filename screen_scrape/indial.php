<?php
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("bash");
	
	// How many scrapes do we have?
	$sql = "SELECT CustomerId, ParseResponse FROM Scrapes ";
	$sql .= "WHERE ParseResponse LIKE '%a:___:%'";
	$query = mysql_query ($sql);
	
	while ($row = mysql_fetch_assoc ($query))
	{
		$Customer = unserialize ($row ['ParseResponse']);
		
		$numbers = Array ();
		
		foreach ($Customer ['sn'] AS $sn)
		{
			$numbers [substr ($sn ['Number'], 0, strlen ($sn ['Number']) - 2)] = 
			isset ($numbers [substr ($sn ['Number'], 0, strlen ($sn ['Number']) - 2)]) ?
			$numbers [substr ($sn ['Number'], 0, strlen ($sn ['Number']) - 2)] + 1 : 1;
		}
		
		foreach ($numbers AS $key => $value)
		{
			if ($value < 50)
			{
				unset ($numbers [$key]);
			}
		}
		
		foreach ($numbers AS $number => $count)
		{
			echo $row ['CustomerId'] . ",";
			echo $number . ",";
			echo $count . "\n";
		}
	}
	
?>
