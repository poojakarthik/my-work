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
	 	$errErrorHandler = new ErrorHandler(); 	
	 	set_error_handler($errErrorHandler, "PHPErrorCatcher");
	 	set_exception_handler($errErrorHandler, "PHPExceptionCatcher");
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
		if (!$_GLOBALS['dbaDatabase'] || !is_a($_GLOBALS['dbaDatabase'], "DataAccess"))
		{
			$_GLOBALS['dbaDatabase'] = "hi world";
			//$_GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$_GLOBALS['dbaDatabase'];
	}
 }
?>
