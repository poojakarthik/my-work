<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// functions
//----------------------------------------------------------------------------//
/**
 * functions
 *
 * Global Functions
 *
 * This file exclusively declares global functions
 *
 * @file		functions.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// FUNCTIONS
//----------------------------------------------------------------------------//

//------------------------------------------------------------------------//
// Debug
//------------------------------------------------------------------------//
/**
 * Debug()
 *
 * display debug output
 *
 * display debug output
 *
 * @param	mixed	$mixOutput	value to output
 * @param	string	$strMode	optional mode, html (default) or txt 
 * @return	bool
 *
 * @method
 * @package	framework
 */
function Debug($mixOutput, $strMode="html")
{
	// check if we are in debug mode
	if (DEBUG_MODE !== TRUE)
	{
		// not in debug mode, exit
		return FALSE;
	}
	
	// output debug info in required format
	switch (strtolower($strMode))
	{
		case 'txt':
		case 'text':
			echo "\n";
			print_r($mixOutput);
			echo "\n";
			break;
			
		default:
			echo "\n<pre>\n";
			print_r($mixOutput);
			echo "\n</pre>\n";

	}
	return TRUE;
}

//------------------------------------------------------------------------//
// DebugBacktrace
//------------------------------------------------------------------------//
/**
 * DebugBacktrace()
 *
 * display debug backtrace output
 *
 * display debug backtrace output
 *
 * @param	string	$strMode	optional mode, html (default) or txt 
 * @return	bool
 *
 * @method
 * @package	framework
 */
function DebugBacktrace($strMode="html")
{
	// check if we are in debug mode
	if (DEBUG_MODE !== TRUE)
	{
		// not in debug mode, exit
		return FALSE;
	}
	
	// output debug info in required format
	switch (strtolower($strMode))
	{
		case 'txt':
		case 'text':
			echo "\n";
			debug_print_backtrace();
			echo "\n";
			break;
			
		default:
			echo "\n<pre>\n";
			debug_print_backtrace();
			echo "\n</pre>\n";

	}
	return TRUE;
}








?>
