<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// skeleton
//----------------------------------------------------------------------------//
/**
 * skeleton
 *
 * Description
 *
 * Description
 *
 * @file		skel.php
 * @language	PHP
 * @package		skel
 * @author		Rich 'Waste' Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Include Application's Require file
require_once("require.php");

// Load the application
$arrConfig = LoadApplication();

// Initiate application and run code (always pass in $arrConfig, even if not using it)
$appSkeleton = new ApplicationSkeleton($arrConfig);
$appSkeleton->Execute();
?>
