<?php
/**
 * Resource_Type_File_Import_Telemarketing_SalesCom_DiallerReport
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Telemarketing_SalesCom_DiallerReport
 */
class Resource_Type_File_Import_Telemarketing_SalesCom_DiallerReport
{
	protected	$_oFileImport;
	protected	$_iCustomerGroupId;
	protected	$_iDealerId;
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($oFileImport, $iCustomerGroup, $iDealer)
	{
		if (!($oFileImport instanceof File_Import))
		{
			throw new Exception("Invalid File_Import object passed");
		}
		
		$this->_oFileImport			= $oFileImport;
		$this->_iCustomerGroupId	= (int)$iCustomerGroup;
		$this->_iDealerId			= (int)$iDealer;
	}
	
	/**
	 * normalise()
	 *
	 * Normalises and Imports the File into Flex
	 * 
	 * @return	array						Array of Error messages
	 * 
	 * @method
	 */
	public function normalise()
	{
		// Open the file
		$rFile	= $this->_oFileImport->fopen();
		
		// Parse each Line
		$iLine		= 0;
		$aErrors	= array();
		while (!feof($rFile))
		{
			$iLine++;
			
			if ($sLine = trim(fgets($rFile)))
			{
				// Normalise the Line
				$aNormalised	= $this->_normaliseLine($sLine);
				
				if ($aNormalised['__WARNINGS__'])
				{
					$aErrors[]	= "Line {$iLine} had the following warnings: ".implode('; ', $aNormalised['__WARNINGS__']);
				}
				if (!$aNormalised['__ERRORS__'])
				{
					// Insert the normalised Line into the Database
					$oDialledFNN	= new Telemarketing_FNN_Dialled();
					
					$oDialledFNN->fnn									= $aNormalised['FNN'];
					$oDialledFNN->customer_group_id						= $this->_iCustomerGroupId;
					$oDialledFNN->file_import_id						= $this->_oFileImport->Id;
					$oDialledFNN->dialled_on							= $aNormalised['CallDatetime'];
					$oDialledFNN->dealer_id								= $this->_iDealerId;
					$oDialledFNN->dialled_by							= '';	// Not used yet
					$oDialledFNN->telemarketing_fnn_dialled_result_id	= $aNormalised['telemarketing_fnn_dialled_result_id'];
					
					//throw new Exception(print_r($oDialledFNN->toArray(), true));
					
					$oDialledFNN->save();
				}
				else
				{
					$aErrors[]	= "Line {$iLine} had the following errors: ".implode('; ', $aNormalised['__ERRORS__']);
				}
			}
		}
		
		// Close the file
		fclose($rFile);
		
		return $aErrors;
	}
	
	/**
	 * _normaliseLine()
	 *
	 * Normalises a raw data record for Import into Flex
	 * 
	 * @param	string	$sLine				Line of raw data to Normalise
	 * 
	 * @method
	 */
	protected static function _normaliseLine($sLine)
	{
		$aNormalised					= array();
		$aNormalised['__ERRORS__']		= array();
		$aNormalised['__WARNINGS__']	= array();
		
		// Explode the CSV
		$aExplode		= File_CSV::parseLine($sLine);
		
		// Ensure that we have the correct number of fields
		$aColumns			= self::_getFileFormatDefinition(null, '__COLUMNS__');
		$iActualColumns		= count($aExplode);
		$iRequiredColumns	= count(self::_getFileFormatDefinition(null, '__COLUMNS__'));
		if ($iActualColumns !== $iRequiredColumns)
		{
			$aNormalised['__WARNINGS__'][]	= "Incorrect number of CSV fields (Actual: {$iActualColumns};Expected: {$iRequiredColumns})";
		}
		
		// Pull the data
		$aIndexes						= self::_getFileFormatDefinition(null, '__INDEXES__');
		
		$aNormalised['FNN']									= $aExplode[$aColumns['FNN']['Index']];
		$aNormalised['CallDatetime']						= $aExplode[$aColumns['CallDatetime']['Index']];
		$aNormalised['OutcomeCode']							= (int)$aExplode[$aColumns['OutcomeCode']['Index']];
		
		// Validate
		if (!preg_match($aColumns['FNN']['Validation'], $aNormalised['FNN']))
		{
			$aNormalised['__ERRORS__'][]	= "FNN '{$aNormalised['FNN']}' is invalid!";
		}
		if (!preg_match($aColumns['CallDatetime']['Validation'], $aNormalised['CallDatetime']))
		{
			$aNormalised['__ERRORS__'][]	= "Call Datetime '{$aNormalised['CallDatetime']}' is invalid! (does not match 'YYYY-MM-DD' or 'YYYY-MM-DD HH:II:SS')";
		}
		if (!preg_match($aColumns['OutcomeCode']['Validation'], (string)$aNormalised['OutcomeCode']))
		{
			$aNormalised['__ERRORS__'][]	= "Outcome Code '{$aNormalised['OutcomeCode']}' is invalid! (not a 3-digit number)";
		}
		
		try
		{
			$aNormalised['telemarketing_fnn_dialled_result_id']	= Telemarketing_FNN_Dialled_Result::getForId($aNormalised['OutcomeCode'])->id;
		}
		catch (Exception $oException)
		{
			$aNormalised['__ERRORS__'][]	= "Outcome Code '{$aNormalised['OutcomeCode']}' is invalid! (does not exist in Flex)";
		}
		
		return $aNormalised;
	}
	
	/**
	 * _getFileFormatDefinition()
	 *
	 * Normalises a raw data record for Import into Flex
	 * 
	 * @param	string	$strLine				Line of raw data to Normalise
	 * 
	 * @method
	 */
	protected static function _getFileFormatDefinition($strField=null, $strProperty=null)
	{
		static	$arrFileFormatDefinition;
		if (!$arrFileFormatDefinition)
		{
			$arrColumns	=	array
							(
								'CompanyName'	=>	array
													(
														'Index'			=> 0
													),
								'Address'		=>	array
													(
														'Index'			=> 1
													),
								'Suburb'		=>	array
													(
														'Index'			=> 2
													),
								'State'			=>	array
													(
														'Index'			=> 3
													),
								'Postcode'		=>	array
													(
														'Index'			=> 4
													),
								'FNN'			=>	array
													(
														'Index'			=> 5,
														'Validation'	=> "/^0[23478]\d{8}$/"
													),
								'CallDatetime'	=>	array
													(
														'Index'			=> 6,
														'Validation'	=> "/^(\d{4}-\d{2}-\d{2})( \d{2}\:\d{2}\:\d{2})?$/"
													),
								'OutcomeCode'	=>	array
													(
														'Index'			=> 7,
														'Validation'	=> "/^\d{3}$/"
													)
							);
			
			$arrFileFormatDefinition	= array();
			$arrFileFormatDefinition['__COLUMNS__']	= $arrColumns;
			
			$arrFileFormatDefinition['__INDEXES__']	= array();
			foreach ($arrColumns as $strName=>$arrDefinition)
			{
				$arrFileFormatDefinition['__INDEXES__'][$arrDefinition['Index']]	= array_merge(array('Name'=>$strName), $arrDefinition);
			}
		}
		
		// Return the Definition
		if ($strField !== null)
		{
			if ($strProperty !== null)
			{
				return $arrFileFormatDefinition['__COLUMNS__'][$strField][$strProperty];
			}
			else
			{
				return $arrFileFormatDefinition['__COLUMNS__'][$strField];
			}
		}
		elseif($strProperty !== null)
		{
			return $arrFileFormatDefinition[$strProperty];
		}
		else
		{
			return $arrFileFormatDefinition;
		}
	}
}