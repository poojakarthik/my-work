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
		$intLine = (int)$intLine;
		
		// set status
		if (is_array($arrStatus))
		{
			$this->_arrStatus = $arrStatus;
		}
		else
		{
			$this->_arrStatus = Array();
		}
		
		// close existing file
		// to-do
		
		// check if the file exists
		// to-do
		
		// open the file
		// to-do
		
		// skip forward to line no.
		if ($intLine > 1)
		{
			// to-do
		}
		
		// set line no.
		$this->intLine = $intLine;
		
		// return TRUE if all went well
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// FetchNext
	//------------------------------------------------------------------------//
	/**
	 * FetchNext()
	 *
	 * Fetch the next record from the file
	 *
	 * Fetch the next record from the file
	 * 
	 *
	 * @return			mixed	array of record details
	 * 							bool	FALSE on EOF
	 *
	 * @method
	 */
 	function FetchNext()
 	{
		$arrOutput = Array();
		
		// read line
		$strLine = $this->ReadRawLine();
		if ($strLine === FALSE)
		{
			return FALSE;
		}
		elseif ($strLine == 'error')
		{
			$arrOutput['_LineType'] = 'error';
			return $arrOutput;
		}
		

		// decode line
		// to-do : decode the line into an output array
		// if a line is of a type that we don't care about you need to skip forward until we find a line we do want
		// sometimes you may need to read in more than 1 line ??
		// $arrOutput['_Table'] =  'CDR' || 'Invoice' || 'ServiceTypTotal' || 'ServiceTotal'
		// $arrOutput[FieldName]...
		
		// set status
		$arrOutput['_Status'] 	= $this->_arrStatus;
		
		// set line type for output
		$arrOutput['_LineType'] 	= 'data';
		
		// set line no for output
		$arrOutput['_LineNo'] 		= $this->intLine;
		
		// return record
		return $arrOutput;
	}
	
	//------------------------------------------------------------------------//
	// ReadRawLine
	//------------------------------------------------------------------------//
	/**
	 * ReadRawLine()
	 *
	 * Read the next raw line from the file
	 *
	 * Read the next raw line from the file
	 * 
	 *
	 * @return			mixed	string	contents of line
	 * 							bool	FALSE on EOF
	 *
	 * @method
	 */
 	function ReadRawLine()
 	{
		// incrament counter
		$this->intLine++;

		// read line from file
		// to-do
		
		// return line string or FALSE on EOF
		// return a string 'error' on error
		// to-do
	}
 }


?>
