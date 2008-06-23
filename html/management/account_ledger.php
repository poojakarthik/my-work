<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	
	$intAccount = $_GET['Id'];
	header("Location: flex.php/Account/InvoicesAndPayments/?Account.Id=$intAccount");
	
?>
