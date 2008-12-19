<?php
/**
 * Resource_Type
 *
 * Models a record of the resource_type table
 *
 * @class	Service
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
		while ($strLine = trim(fgets($this->_resFile)))
		{
			$intLine++;
			
			// Normalise the Line
			$arrNormalised	= $this->_normaliseLine($strLine);
			
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
				
				$objProposedFNN->save();
			}
			else
			{
				$arrErrors[]	= "Line {$intLine} had the following errors: ".implode('; ', $arrNormalised['__ERRORS__']);
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
		$arrNormalised	= array();
		
		// Explode the CSV
		$arrExplode		= explode('","', trim($strLine, '"'));		
		
		// Ensure that we have the correct number of fields
		$intActualColumns	= count($arrExplode);
		$intRequiredColumns	= count(self::_getFileFormatDefinition('__COLUMNS__'));
		if ($intActualColumns !== $intRequiredColumns)
		{
			$arrNormalised['__ERRORS__'][]	= "Incorrect number of CSV field (Actual: {$intActualColumns};Expected: {$intRequiredColumns})";
		}
		
		// Pull the data
		$arrNormalised['FNN']				= $arrExplode[5];
		$arrNormalised['CallPeriodStart']	= date("Y-m-d 00:00:00");
		$arrNormalised['CallPeriodEnd']		= strtotime("+{self::CALL_PERIOD_LENGTH_DAYS} days", strtotime(date("Y-m-d 00:00:00")));
		
		// Validate
		$arrNormalised['__ERRORS__']	= array();
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
														'Validation'	=> "/^0[2378]\d{8}$/"
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