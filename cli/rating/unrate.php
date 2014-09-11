<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appRating = new ApplicationRating($arrConfig);

// run the Rate method until there is nothing left to rate
while ($appRating->UnRate())
{

}

// finished
echo("\n-- End of UnRating --\n");
die();




?>
