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
	 * @method
	 */
	public function normalise()
	{
		// Open the file
		$this->_resFile	= $this->_objFileImport->fopen();
		
		// Parse each Line
		while ($strLine = fgets($this->_resFile))
		{
			// Normalise the Line
			$arrNormalised	= $this->_normaliseLine($strLine);
			
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
		
		// Close the file
		fclose($this->_resFile);
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
	protected function _normaliseLine($strLine)
	{
		$arrExplode		= explode('","', trim($strLine, '"'));
		
		$arrNormalised	= array();
		$arrNormalised['FNN']				= $arrExplode[5];
		$arrNormalised['CallPeriodStart']	= date("Y-m-d 00:00:00");
		$arrNormalised['CallPeriodEnd']		= strtotime("+{self::CALL_PERIOD_LENGTH_DAYS} days", strtotime(date("Y-m-d 00:00:00")));
		
		return $arrNormalised;
	}
}