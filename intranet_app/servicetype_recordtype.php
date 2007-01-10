<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE_TYPE | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	$oblarrTypes	= $Style->attachObject (new dataArray ('Types'));
	
	$svtServiceType	= $oblarrTypes->Push (new ServiceTypes);
	
	$rtlRecordTypes = new RecordTypes ();
	$rtlRecordTypes->Order ('Name', TRUE);
	$rtlRecordTypes->Sample ();
	$oblarrTypes->Push ($rtlRecordTypes);
	
	$Style->Output ("xsl/content/js/servicetype_recordtype.xsl");
	
?>
