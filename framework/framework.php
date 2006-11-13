<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// FRAMEWORK
//----------------------------------------------------------------------------//
/**
 * FRAMEWORK
 *
 * The framework which links everything
 *
 * The framework which links all of our modules
 *
 * @file		framework.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// Framework
//----------------------------------------------------------------------------//
/**
 * Framework
 *
 * The framework which links everything
 *
 * The framework which links all of our modules
 *
 *
 * @prefix	fwk
 *
 * @package	framework
 * @class	Framework
 */
 class Framework
 {
	//------------------------------------------------------------------------//
	// errErrorHandler
	//------------------------------------------------------------------------//
	/**
	 * errErrorHandler
	 *
	 * Handles errors for application
	 *
	 * This object will handle all errors in the application
	 *
	 * @type		ErrorHandler
	 *
	 * @property
	 * @see			ErrorHandler
	 */
	public $errErrorHandler;
	
	private $_intStopwatchTime;
	private $_intLapTime;

	//------------------------------------------------------------------------//
	// Framework - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Framework()
	 *
	 * Constructor for the Framework object
	 *
	 * Constructor for the Framework object
	 *
	 * @param	<type>	<$name>	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 function __construct()
	 {
	 	ob_start();
		if (DEBUG_MODE == FALSE)
		{
			error_reporting(0);
		}
	 	$this->_errErrorHandler = new ErrorHandler(); 	
		set_exception_handler(Array($this->_errErrorHandler, "PHPExceptionCatcher"));
		set_error_handler(Array($this->_errErrorHandler, "PHPErrorCatcher"));
		
		// start timing
		$this->_intStartTime		= microtime(TRUE);
		$this->_intStopwatchTime	= microtime(TRUE);
		$this->_intLapTime			= microtime(TRUE);
	 }
	 
	 function Render()
	 {
	 	ob_flush();
	 }
	 
	 function Uptime()
	 {
	 	$intTime = microtime(TRUE);
	 	return round($intTime - $this->_intStartTime, 4);
	 }
	 
	 function StartWatch()
	 {
	 	$this->_intStopwatchTime = microtime(TRUE);
	 }
	 
	 function SplitWatch()
	 {
	 	return round(microtime(TRUE) - $this->_intStopwatchTime, 4);
	 }
	 
	 function LapWatch()
	 {
	 	$intOldLapTime		= $this->_intLapTime;
	 	$this->_intLapTime	= microtime(TRUE);
	 	return round($this->_intLapTime - $intOldLapTime, 4);
	 }
 }

//----------------------------------------------------------------------------//
// ApplicationBaseClass
//----------------------------------------------------------------------------//
/**
 * ApplicationBaseClass
 *
 * Abstract Base Class for Application Classes
 *
 * Use this class as a base for all application classes
 *
 *
 * @prefix		app
 *
 * @package		framework
 * @class		DatabaseAccess 
 */
 abstract class ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// ApplicationBaseClass() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * ApplicationBaseClass()
	 *
	 * Constructor for ApplicationBaseClass
	 *
	 * Constructor for ApplicationBaseClass

	 * @return		void
	 *
	 * @method
	 */ 
	function __construct()
	{
		// connect to database if not already connected
		if (!$GLOBALS['dbaDatabase'] || !($GLOBALS['dbaDatabase'] instanceOf DataAccess))
		{
			$_GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$GLOBALS['dbaDatabase'];
		
		// make global framework object available
		$this->Framework = &$GLOBALS['fwkFramework'];
	}
 }
?>
