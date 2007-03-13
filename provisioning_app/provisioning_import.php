<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Import Provisioning Reports
//----------------------------------------------------------------------------//

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appProvisioning = new ApplicationProvisioning($arrConfig);

// Import Provisioning Reports
$bolResponse = $appProvisioning->Import();

$appProvisioning->FinaliseReport();

// finished
echo("\n\n-- End of Provisioning --\n");
die();

?>
