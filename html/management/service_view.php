<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	$intService = $_GET['Id'];
	header("Location: ../ui/flex.php/Service/View/?Service.Id=$intService");

?>
