<?php
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$oblarrTypes	= $Style->attachObject (new dataArray ('Types'));
	
	$svtServiceType	= $oblarrTypes->Push (new NamedServiceType);
	
	$rtlRecordTypes = new RecordTypeSearch ();
	$rtlRecordTypes->Order ('Name', TRUE);
	$rtlRecordTypes->Sample ();
	$oblarrTypes->Push ($rtlRecordTypes);
	
	$Style->Output ("xsl/content/js/servicetype_recordtype.xsl");
	
?>
