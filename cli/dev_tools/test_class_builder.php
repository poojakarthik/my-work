<?php
require_once ('class_builder.php');


// get input text
$strInputText = $_POST['InputText'];

// instanciate class builder
$objClass = new ClassBuilder($strInputText);

// create class
if (!$strOutputText = $objClass->Process())
{
	$strOutputText = $objClass->strError;
}


?>


<html>
	<head>
	</head>
	<body>
		<form method="POST">
			<textarea name="InputText" style="height: 500px; width: 800px;"><?php echo $strInputText; ?></textarea>
			<textarea name="OutputText" style="height: 500px; width: 800px;"><?php echo $strOutputText; ?></textarea>
			<br>
			<input type="submit" value="Build Class">
		</form>
	</body>
</html>
