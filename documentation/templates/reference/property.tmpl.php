<h3><?php echo $document['class'].$join.$document['title'] ?></h3>

<p>
	<?php echo $document['short_description'] ?>
	<br>
	<br>
</p>

<h3>Description</h3>
<p>
	<?php echo $document['class'].$join.$document['title'] ?>
	<br>
	<br>
	<?php echo nl2br($document['long_description']); ?>
	<br>
	<br>
	Property of class : <?php echo $document['class'] ?>
	<br>
	<br>
	Member of package : <?php echo $document['package'] ?>
	<br>
	<br>
</p>

<h3>Usage</h3>
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
