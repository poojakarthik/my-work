<?php
//----------------------------------------------------------------------------//
// process
//----------------------------------------------------------------------------//
/**
 * process
 *
 * The container script for running other automated scripts
 *
 * The container script for running other automated scripts
 *
 * @file		process.php
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
$intStartTime	= time();
$strProcess		= trim($argv[1]);
CliEcho("Starting Process: '$strProcess' @ ".date("Y-m-d H:i:s"));
$appProcess->RunProcess($strProcess);
CliEcho("Process Completed @ ".date("Y-m-d H:i:s")." (".(time()-$intStartTime)."s)");

?>