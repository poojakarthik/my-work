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
		if ($this->ptrFile)
		{
			fclose($this->ptrFile);
		}
		
		// check if the file exists
		if (!file_exists($strFilePath))
		{
			return FALSE;
		}
		
		// open the file
		if ((@$this->ptrFile = fopen($strFilePath, "r")) === FALSE)
		{
			// if it failed, return false
			return FALSE;
		}
		
		// skip forward to line no.
		for ($i = 0; $i < $intLine; $i++)
		{
			fgets($this->ptrFile);
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
		// $arrOutput['_Table'] =  		// 'CDR' || 'Invoice' || 'ServiceTypTotal' || 'ServiceTotal'
		// $arrOutput[FieldName]...
		// $arrOutput['FNN']			// alwayn return this as we have no Service field
		// $arrOutput['Account']
		
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
		// increment counter
		$this->intLine++;
		
		// If EOF, then return FALSE
		if (feof($this->ptrFile))
		{
			return FALSE;
		}

		// read next line from file
		if (($strLine = fgets($this->ptrFile)) === FALSE)
		{
			// There was an error
			return "!ERROR!";
		}
		
		// return the raw line
		return $strLine;				
	}
 }


?>
