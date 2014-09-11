<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Generate Pending Email Data Reports
//----------------------------------------------------------------------------//

// load application
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appReport = new ApplicationReport($arrConfig);

// Generate Reports & Email them off
$bolResponse = $appReport->Execute();

// finished
echo("\n\n-- End of Report Generation --\n");

?>