<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_unitel
//----------------------------------------------------------------------------//
/**
 * module_unitel
 *
 * Unitel Module for the provisioning engine
 *
 * Unitel Module for the provisioning engine
 *
 * @file		module_unitel.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleUnitel
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleUnitel
 *
 * Unitel Module for the provisioning engine
 *
 * Unitel Module for the provisioning engine.  There is one per carrier.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleUnitel
 */
 class ProvisioningModuleUnitel
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * @return		ProvisioningModuleUnitel
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		parent::__construct($ptrDB);
		
				
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
		
		
		// define the carrier input format
		$arrDefine ['RecordType']	['Start']		= 0;
		$arrDefine ['RecordType']	['Length']		= 1;
		
		$arrDefine ['Sequence']		['Start']		= 1;
		$arrDefine ['Sequence']		['Length']		= 5;
		
		$arrDefine ['OrderId']		['Start']		= 6;
		$arrDefine ['OrderId']		['Length']		= 9;

		$arrDefine ['OrderType']	['Start']		= 15;
		$arrDefine ['OrderType']	['Length']		= 2;
		
		$arrDefine ['OrderDate']	['Start']		= 17;
		$arrDefine ['OrderDate']	['Length']		= 8;
		
		$arrDefine ['ServiceNo']	['Start']		= 25;
		$arrDefine ['ServiceNo']	['Length']		= 29;
		
		$arrDefine ['Basket']		['Start']		= 54;
		$arrDefine ['Basket']		['Length']		= 3;
		
		$arrDefine ['EffectiveDate']['Start']		= 57;
		$arrDefine ['EffectiveDate']['Length']		= 8;
		
		$arrDefine ['NewNo']		['Start']		= 65;
		$arrDefine ['NewNo']		['Length']		= 29;
		
		$arrDefine ['ReasonCode']	['Start']		= 94;
		$arrDefine ['ReasonCode']	['Length']		= 3;
		
		$arrDefine ['LostTo']		['Start']		= 97;
		$arrDefine ['LostTo']		['Length']		= 3;
		
		$arrDefine ['RSLReference']	['Start']		= 100;
		$arrDefine ['RSLReference']	['Length']		= 9;					
		
		$this->_arrDefineInput = $arrDefine;
		
		//##----------------------------------------------------------------##//
		
 	}

 	//------------------------------------------------------------------------//
	// Add()
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Adds a line to the module from a file
	 *
	 * Parses and adds a "line" to the module from a line status file.
	 *
	 * @return		int				Success/Failure code
	 *
	 * @method
	 */
 	function Add($strLine)
	{
		// Split the line
		$arrLineData = $this->_SplitLine($strLine);
		
		// Ignore header and trailer line
		if($arrLineData['RecordType'] == "T")
		{
			return PRV_TRAILER_RECORD;
		}
		elseif($arrLineData['RecordType'] == "H")
		{
			return PRV_HEADER_RECORD;
		}
		
		// Line Status
		$arrParsedData['LineStatus']	= $this->_DetermineStatus($arrLineData['RecordType']);
		
		// FNN
		$arrParsedData['FNN']			= RemoveAusCode($arrLineData['ServiceNo']);
		
		// Date
		$arrParsedData['Date']			= $arrLineData['EffectiveDate'];
		
		// Add split line to File data array
		$this->_arrFileData[] = $arrParsedData;
	} 	

 	//------------------------------------------------------------------------//
	// UpdateRequests()
	//------------------------------------------------------------------------//
	/**
	 * UpdateRequests()
	 *
	 * Updates the Request table
	 *
	 * Updates the Request table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function UpdateRequests()
	{
		// TODO
	} 	
 	
 	//------------------------------------------------------------------------//
	// UpdateService()
	//------------------------------------------------------------------------//
	/**
	 * UpdateService()
	 *
	 * Updates the Service table
	 *
	 * Updates the Service table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function UpdateService()
	{
		// TODO
	} 	
 
 
  	//------------------------------------------------------------------------//
	// BuildRequest()
	//------------------------------------------------------------------------//
	/**
	 * BuildRequest()
	 *
	 * Builds a request file
	 *
	 * Builds a request file to be sent off, based on info from the DB
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function BuildRequest()
	{
		// TODO
	} 	
 	
  	//------------------------------------------------------------------------//
	// SendRequest()
	//------------------------------------------------------------------------//
	/**
	 * SendRequest()
	 *
	 * Sends the current request
	 *
	 * Sends the current request
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendRequest()
	{
		// TODO
	} 	
 }

?>
