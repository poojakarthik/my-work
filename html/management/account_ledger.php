<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	
	$intAccount = $_GET['Id'];
	header("Location: ../ui/flex.php/Account/InvoicesAndPayments/?Account.Id=$intAccount");
	
?>
