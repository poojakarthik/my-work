<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// exception_vixen
//----------------------------------------------------------------------------//
/**
 * exception_vixen
 *
 * Custom Exception class
 *
 * Custom Exception class
 *
 * @file		exception_vixen
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExceptionVixen
//----------------------------------------------------------------------------//
/**
 * ExceptionVixen
 *
 * Custom Exception class
 *
 * Custom Exception class.  Non-fatal errors only.
 *
 *
 * @prefix		exv
 *
 * @package		framework
 * @class		ExceptionVixen
 */
 class ExceptionVixen extends Exception
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for ExceptionVixen
	 *
	 * Constructor for ExceptionVixen.  Allows for additional parameters to be passed
	 * in comparison to its parent's constructor.
	 *
	 * @param	<type>	<$name>	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct($strErrorMessage, $errErrorHandler, $intErrorCode)
 	{
 		// Call the parent constructor
 		parent::__construct($strErrorMessage, $intErrorCode);

 		return;

 		// Now that we're constructed, automatically pass our info to the error recorder
 		$errErrorHandler->PHPExceptionCatcher($this);

 		// Finally, if this a fatal exception, die
 		if ($intErrorCode >= FATAL_ERROR_LEVEL)
 		{
 			echo("Fatal Exception: " . $strErrorMessage . " (" . $intErrorCode . ")\n");
 			die();
 		}
 	}
 }
?>
