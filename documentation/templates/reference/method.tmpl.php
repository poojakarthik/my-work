<table>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<div class="td_title"><?php echo $document['class'].$join.$document['title'] ?></div>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<div class="td_heading">Description</div>
<p>
	<table>
		<tr>
			<td colspan ="2">
				<?php echo $document['class'].$join.$document['title'] ?>()
			</td>
		</tr>
		<tr>
			<td colspan = "2"> &nbsp; </td>
		</tr>
		<tr>
			<td colspan = "2">
				<?php echo nl2br($document['long_description']); ?>
			</td>
		</tr>
		<tr>
			<td colspan = "2"> &nbsp; </td>
		</tr>
		<tr>
			<td>Method of class : </td>
			<td> &nbsp; &nbsp; <?php echo $document['class'] ?></td>
		</tr>
		<tr>
			<td>Member of package : </td>
			<td> &nbsp; &nbsp; <?php echo $document['package'] ?></td>
		</tr>
		<tr>
		</tr>
	</table>
	
</p>

<br>
<br>

<div class="td_heading">Usage</div>
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
			echo $document['return']['type'].' &nbsp;&nbsp; '.$document['class'].$join.$document['title'].' ('.$required_params.' )<br><br>';
		}
	?>
	
	<?php echo $document['return']['type'].' &nbsp;&nbsp; '.$document['class'].$join.$document['title'].' ('.$params.' )'; ?>
	<br>
	<br>
	<?php
		if (is_array($document['param']))
		{
			echo "Parameters :<br>";
			echo "<table border=0 cellpadding=5>";
			foreach($document['param'] as $key=>$param)
			{
	?>
				<tr align="left" valign="top">
					<td>
						<?php echo $param['type']; ?>
					</td>
					<td>
						<?php echo $param['name']; ?>
					</td>
					<td>
						<?php echo $param['description']; ?>
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
</td>
</table>
<?php
	/*
	echo '<pre>';
	print_r($document);
	echo '<pre>';
	*/
?>
