<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// etech_reader
//----------------------------------------------------------------------------//
/**
 * etech_reader
 *
 * Read an etech billing file
 *
 * Read an etech billing file
 *
 * @file		etech_reader.php
 * @language	PHP
 * @package		Billing
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 

//----------------------------------------------------------------------------//
// EtechReader
//----------------------------------------------------------------------------//
/**
 * EtechReader
 *
 * Read an etech billing file
 *
 * Read an etech billing file
 *
 *
 * @prefix		sux
 *
 * @package		billing_app
 * @class		EtechReader
 */
 class EtechReader extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
	}
	
	//------------------------------------------------------------------------//
	// OpenFile
	//------------------------------------------------------------------------//
	/**
	 * OpenFile()
	 *
	 * Open an etech billing file
	 *
	 * Open an etech billing file
	 * 
	 * @param	string	$strFilePath	full path to file
	 * @param	int		$intLine		line no. to start reading from
	 *									first line of the file is line 1
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function OpenFile($strFilePath, $intLine=0, $arrStatus=NULL)
 	{
	
	
	}
	
?>
