<h3><?php echo $document['title'] ?></h3>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<h3>Description</h3>
<p>
	<?php echo $document['title'] ?>()
	<br>
	<br>
	<?php echo nl2br($document['long_description']); ?>
	<br>
	<br>
	Global Function
	<br>
	<br>
	Member of package : <?php echo $document['package'] ?>
	<br>
	<br>
</p>

<h3>Usage</h3>
<p>
	<?php
		$params = array();
		$required_params = array();
		$has_optional_params = FALSE;
		if (is_array($document['param']))
		{
			foreach($document['param'] as $key=>$param)
			{
				$p = $param['type'].' '.$param['name'];
				if ($param['optional'])
				{
					$p = " [$p]";
					$has_optional_params = TRUE;
				}
				else
				{
					$required_params[] = " $p";
				}
				$params[] = " $p";
			}
		}
		$params = implode(',',$params);
		$required_params = implode(',',$required_params);	
	?>
	
	<?php
		if ($has_optional_params)
		{
			echo $document['return']['type'].' &nbsp;&nbsp; '.$document['title'].' ('.$required_params.' )<br><br>';
		}
	?>
	
	
	<?php echo $document['return']['type'].' &nbsp;&nbsp; '.$document['title'].' ('.$params.' )'; ?>
	<br>
	<br>
	<?php
		if (is_array($document['param']))
		{
			echo "Parameters :<br>";
			echo "<table border=0>";
			foreach($document['param'] as $key=>$param)
			{
	?>
				<tr>
					<td>
						<?php echo $param['type']; ?>
					</td>
					<td>
						&nbsp; &nbsp; <?php echo $param['name']; ?>
					</td>
					<td>
						&nbsp; &nbsp; <?php echo $param['description']; ?>
					</td>
				</tr>
	
	<?php
			}
			echo "</table>";
		}
	
	?>
	<br>
	Returns : &nbsp; <?php echo $document['return']['type']; ?>&nbsp; &nbsp; <?php echo $document['return']['description']; ?>
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
