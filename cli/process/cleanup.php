<?php
//----------------------------------------------------------------------------//
// cleanup
//----------------------------------------------------------------------------//
/**
 * cleanup
 *
 * Finalises any lost processes
 *
 * Finalises any processes that still appear active in the DB, but have actually finished
 *
 * @file		cleanup.php
 * @language	PHP
 * @package		master
 * @author		Rich 'Waste' Davis
 * @version		8.02
 * @copyright	2006-2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Load Framework and Application
require_once("../../flex.require.php");
$arrConfig	= LoadApplication();
$appProcess	= new ApplicationProcess($arrConfig);

// Run the Process
$appProcess->CleanProcesses();

?>