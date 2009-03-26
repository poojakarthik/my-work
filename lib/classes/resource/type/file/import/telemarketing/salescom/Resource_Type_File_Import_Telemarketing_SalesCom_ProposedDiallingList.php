<?php
/**
 * Resource_Type_File_Import_Telemarketing_SalesCom_ProposedDiallingList
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Telemarketing_SalesCom_ProposedDiallingList
 */
class Resource_Type_File_Import_Telemarketing_SalesCom_ProposedDiallingList
{
	protected	$_objFileImport;
	
	const		CALL_PERIOD_LENGTH_DAYS	= 21;
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($objFileImport, $intCustomerGroup, $intDealer)
	{
		if (!($objFileImport instanceof File_Import))
		{
			throw new Exception("Invalid File_Import object passed");
		}
		
		$this->_objFileImport		= $objFileImport;
		$this->_intCustomerGroupId	= $intCustomerGroup;
		$this->_intDealerId			= $intDealer;
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
		$this->_resFile	= $this->_objFileImport->fopen();
		
		// Parse each Line
		$intLine	= 0;
		$arrErrors	= array();
		while (!feof($this->_resFile))
		{
			$intLine++;
			
			if ($strLine = trim(fgets($this->_resFile)))
			{
				// Normalise the Line
				$arrNormalised	= $this->_normaliseLine($strLine);
				
				if ($arrNormalised['__WARNINGS__'])
				{
					$arrErrors[]	= "Line {$intLine} had the following warnings: ".implode('; ', $arrNormalised['__WARNINGS__']);
				}
				if (!$arrNormalised['__ERRORS__'])
				{
					
					// Insert the normalised Line into the Database
					$objProposedFNN	= new Telemarketing_FNN_Proposed();
					
					$objProposedFNN->fnn									= $arrNormalised['FNN'];
					$objProposedFNN->customer_group_id						= $this->_intCustomerGroupId;
					$objProposedFNN->proposed_list_file_import_id			= $this->_objFileImport->Id;
					$objProposedFNN->call_period_start						= $arrNormalised['CallPeriodStart'];
					$objProposedFNN->call_period_end						= $arrNormalised['CallPeriodEnd'];
					$objProposedFNN->dealer_id								= $this->_intDealerId;
					$objProposedFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED;
					$objProposedFNN->raw_record								= $strLine;
					
					//throw new Exception(print_r($objProposedFNN->toArray(), true));
					
					$objProposedFNN->save();
				}
				else
				{
					$arrErrors[]	= "Line {$intLine} had the following errors: ".implode('; ', $arrNormalised['__ERRORS__']);
				}
			}
		}
		
		// Close the file
		fclose($this->_resFile);
		
		return $arrErrors;
	}
	
	/**
	 * _normaliseLine()
	 *
	 * Normalises a raw data record for Import into Flex
	 * 
	 * @param	string	$strLine				Line of raw data to Normalise
	 * 
	 * @method
	 */
	protected static function _normaliseLine($strLine)
	{
		$arrNormalised					= array();
		$arrNormalised['__ERRORS__']	= array();
		$arrNormalised['__WARNINGS__']	= array();
		
		// Explode the CSV
		$arrExplode		= self::parseCSV($strLine);
		
		// Ensure that we have the correct number of fields
		$intActualColumns	= count($arrExplode);
		$intRequiredColumns	= count(self::_getFileFormatDefinition(null, '__COLUMNS__'));
		if ($intActualColumns !== $intRequiredColumns)
		{
			$arrNormalised['__WARNINGS__'][]	= "Incorrect number of CSV fields (Actual: {$intActualColumns};Expected: {$intRequiredColumns})";
		}
		
		$intCallPeriodLengthDays	= self::CALL_PERIOD_LENGTH_DAYS;
		
		// Pull the data
		$arrNormalised['FNN']				= $arrExplode[5];
		$arrNormalised['CallPeriodStart']	= date("Y-m-d 00:00:00");
		$arrNormalised['CallPeriodEnd']		= date("Y-m-d H:i:s", strtotime("+{$intCallPeriodLengthDays} days", strtotime($arrNormalised['CallPeriodStart'])));
		
		// Validate
		if (!preg_match("/^0[2378]\d{8}$/", $arrNormalised['FNN']))
		{
			$arrNormalised['__ERRORS__'][]	= "FNN '{$arrNormalised['FNN']}' is invalid!";
		}
		
		return $arrNormalised;
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
	
	public static function parseCSV($strLine, $strDelimiter=',', $strEnclose='"', $strEscape='\\')
	{
		$arrCSV			= array();
		
		$strField		= '';
		$bolInEscape	= false;
		$bolInEnclose	= false;
		for ($i = 0; $i < strlen($strLine); $i++)
		{
			$strChar	= $strLine{$i};
			
			switch ($strChar)
			{
				case $strEscape:
					// Escape Character
					$strField		.= ($bolInEscape) ? $strChar : '';
					$bolInEscape	= !$bolInEscape;
					break;
				
				case $strEnclose:
					// Enclose Character
					$strField		.= ($bolInEscape) ? $strChar : '';
					$bolInEnclose	= !$bolInEnclose;
					break;
				
				case $strDelimiter:
					// Delimiter
					if ($bolInEnclose)
					{
						$strField		.= $strChar;
					}
					else
					{
						// Push this field to the CSV array
						$arrCSV[]	= $strField;
						$strField	= '';
					}
					break;
				
				default:
					// Normal Character
					$strField	.= $strChar;
					break;
			}
		}
		// Push any remaining field to the CSV array
		$arrCSV[]	= $strField;
		
		return $arrCSV;
	}
}