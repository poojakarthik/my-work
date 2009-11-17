<?php
/**
 * NormalisationModuleLinxMonthlyInvoiceFile
 *
 * Normalisation module for LinxOnline Monthly Invoice Files
 * 
 * @class	NormalisationModuleLinxMonthlyInvoiceFile
 */
class NormalisationModuleLinxMonthlyInvoiceFile extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_TELSTRA;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE;
	
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Module
	 * 
	 * @constructor
	 */
	function __construct($intCarrier)
	{
		// call parent constructor
		parent::__construct($intCarrier);
		
		// define row start (account for header rows)
		$this->_intStartRow = 0;
		
		$this->_iSequence	= 0;
		
		// define the carrier CDR format
		$this->_arrDefineCarrier	= array();
	}

	/**
	 * Normalise()
	 *
	 * Normalises raw data from the CDR
	 * 
	 * @param	array		arrCDR		Array returned from SELECT query on CDR
	 *
	 * @return	array					Normalised Data, ready for direct UPDATE
	 * 									into DB
	 *
	 * @method
	 */	
	function Normalise($arrCDR)
	{
		// set up CDR
		$this->_NewCDR($arrCDR);
		
		//--------------------------------------------------------------------//
		
		// SequenceNo
		$this->_AppendCDR('SequenceNo', $this->_iSequence++);
		
		// Determine File Record Type
		$sRawRecordType	= strtoupper(substr($arrCDR['CDR'], 0 , 3));
		switch ($sRawRecordType)
		{
			// Usage Records
			case 'SE':	// S&E Bulked
				$this->_arrDefineCarrier	= self::$_arrRecordDefinitions[$sRawRecordType];
				
				// covert CDR string to array
				$this->_SplitRawCDR($arrCDR['CDR']);
				$sMethod	= "_normalise{$sRawRecordType}";
				$this->$sMethod();
				break;
			
			// Other Records
			case 'HD':	// Header
			case 'AS':	// Account Summary
			case 'RM':	// Remit
			case 'UC':	// Usage Charges Summary
			case 'OO':	// Ons and Offs
			case 'OC':	// OC&C Bulked
			case 'DC':	// Directory Charges
			case 'AD':	// Adjustments
			case 'PY':	// Payments
			case 'PB':	// Previous Bill Details
			case 'MS':	// Messages
			case 'SS':	// Service Summary Details
			case 'DI':	// Discount Summary
			case 'DF':	// Daily File Inventory
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;
			
			// Headers/Trailers
			case 'FDR':	// File Designator Record
			case 'FTR':	// Trailer Record
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;
			
			default:
				// Unhandled File Record Type
				throw new Exception_Assertion("Unhandled LINX Daily Event File Record Type: '{$sRawRecordType}'", $arrCDR, "Unhandled LINX Daily Event File Record Type: '{$sRawRecordType}'");
				break;
		}
		
		//--------------------------------------------------------------------//
		
		Debug($this->_arrNormalisedData);
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	// Service & Equipment
	private function _normaliseSE()
	{
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('InvoiceArrangementId').'.'.$this->_FetchRawCDR('GlobalItemReferenceNumber'));
		
		// FNN
		$sFNN	= self::RemoveAusCode(self::_parseServiceNumber($this->_FetchRawCDR('FullNationalNumber')));
		$this->_AppendCDR('FNN', $sFNN);
		
		// Source
		$this->_AppendCDR('Source', $sFNN);
		
		// Destination
		$this->_AppendCDR('Destination', $sFNN);
		
		// Units
		$this->_AppendCDR('Units', abs($this->_FetchRawCDR('GenericQuantity')));	// Can be negative if being disconnected
		
		// StartDatetime
		$this->_AppendCDR('StartDatetime', $this->_FetchRawCDR('StartDate'));
		
		// EndDatetime
		$this->_AppendCDR('EndDatetime', $this->_FetchRawCDR('EndDate'));
		
		// Cost
		$this->_AppendCDR('Cost', ((int)$this->_FetchRawCDR('SummaryNetAmount')) / 10000);	// SummaryNetAmount has 4 implied decimal places
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		// RecordType
		$iRecordType	= $this->_FindRecordType('S&E', $iServiceType);
		$this->_AppendCDR('RecordType', $iRecordType);
		
		// Destination
		$aDestination	= $this->FindDestination(trim($this->_FetchRawCDR('BillingTransactionDescription')));
		if ($aDestination)
		{
			$this->_AppendCDR('Destination', $aDestination['Code']);
		}
		
		// Description
		$sDescription	= (!$aDestination || $aDestination['bolUnknownDestination']) ? trim($this->_FetchRawCDR('BillingTransactionDescription')) : $aDestination['Description'];
		$this->_AppendCDR('Description', $sDescription);
		
		// Credit
		$this->_AppendCDR('Credit', 0);
		
		return;
	}
	
	// Other Charges & Credits
	private function _normaliseOC()
	{
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('InvoiceArrangementId').'.'.$this->_FetchRawCDR('GlobalItemReferenceNumber'));
		
		// FNN
		$sFNN	= self::RemoveAusCode(self::_parseServiceNumber($this->_FetchRawCDR('FullNationalNumber')));
		$this->_AppendCDR('FNN', $sFNN);
		
		// Source
		$this->_AppendCDR('Source', $sFNN);
		
		// Destination
		$this->_AppendCDR('Destination', $sFNN);
		
		// Units
		$this->_AppendCDR('Units', abs($this->_FetchRawCDR('GenericQuantity')));	// Can be negative if being disconnected
		
		// StartDatetime
		$this->_AppendCDR('StartDatetime', $this->_FetchRawCDR('StartDate'));
		
		// EndDatetime
		$this->_AppendCDR('EndDatetime', $this->_FetchRawCDR('EndDate'));
		
		// Cost
		$this->_AppendCDR('Cost', ((int)$this->_FetchRawCDR('SummaryNetAmount')) / 10000);	// SummaryNetAmount has 4 implied decimal places
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		// RecordType
		$iRecordType	= $this->_FindRecordType('S&E', $iServiceType);
		$this->_AppendCDR('RecordType', $iRecordType);
		
		// Destination
		$aDestination	= $this->FindDestination(trim($this->_FetchRawCDR('BillingTransactionDescription')));
		if ($aDestination)
		{
			$this->_AppendCDR('DestinationCode', $aDestination['Code']);
		}
		
		// Description
		$sDescription	= (!$aDestination || $aDestination['bolUnknownDestination']) ? trim($this->_FetchRawCDR('BillingTransactionDescription')) : $aDestination['Description'];
		$this->_AppendCDR('Description', $sDescription);
		
		// Credit
		$this->_AppendCDR('Credit', 0);
		
		return;
	}
	
	static private function _parseServiceNumber($sServiceNumber)
	{
		switch (substr($sServiceNumber, 0, 1))
		{
			case 'A':	// Full National Number
				return trim(substr($sServiceNumber, 2, 6)).trim(substr($sServiceNumber, 8, 4));
				break;
			
			case 'O':	// International Number
				return trim(substr($sServiceNumber, 2, 15));
				break;
			
			default:	// Other
				return trim(substr($sServiceNumber, 1, 18));
				break;
		}
	}
	
	static private	$_arrRecordDefinitions	=	array
												(
													'SE'	=>	array
																(
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 10
																										),
																	'SectionType'					=>	array
																										(
																											'Start'		=> 37,
																											'Length'	=> 2
																										),
																	'LineType'						=>	array
																										(
																											'Start'		=> 39,
																											'Length'	=> 1
																										),
																	'GlobalItemReferenceNumber'		=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 7
																										),
																	'InvoiceCOSVCDescription'		=>	array
																										(
																											'Start'		=> 47,
																											'Length'	=> 30
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 77,
																											'Length'	=> 29
																										),
																	'GenericQuantity'				=>	array
																										(
																											'Start'		=> 106,
																											'Length'	=> 10
																										),
																	'BillingTransactionDescription'	=>	array
																										(
																											'Start'		=> 116,
																											'Length'	=> 50
																										),
																	'TransactionTypeDescription'	=>	array
																										(
																											'Start'		=> 166,
																											'Length'	=> 25
																										),
																	'TransactionTypeCode'			=>	array
																										(
																											'Start'		=> 191,
																											'Length'	=> 1
																										),
																	'StartDate'						=>	array
																										(
																											'Start'		=> 192,
																											'Length'	=> 8
																										),
																	'EndDate'						=>	array
																										(
																											'Start'		=> 200,
																											'Length'	=> 8
																										),
																	'SummaryGrossAmount'			=>	array
																										(
																											'Start'		=> 208,
																											'Length'	=> 14
																										),
																	'SummaryNetAmount'				=>	array
																										(
																											'Start'		=> 222,
																											'Length'	=> 14
																										),
																	'GSTAmount'						=>	array
																										(
																											'Start'		=> 236,
																											'Length'	=> 14
																										),
																	'SummaryPriceAmountIncGST'		=>	array
																										(
																											'Start'		=> 250,
																											'Length'	=> 14
																										),
																	'ServiceLocation1'				=>	array
																										(
																											'Start'		=> 264,
																											'Length'	=> 30
																										),
																	'ServiceLocation2'				=>	array
																										(
																											'Start'		=> 294,
																											'Length'	=> 30
																										),
																	'ServiceLocation3'				=>	array
																										(
																											'Start'		=> 324,
																											'Length'	=> 30
																										),
																	'ServiceLocation4'				=>	array
																										(
																											'Start'		=> 354,
																											'Length'	=> 30
																										),
																	'PurchaseOrderNumber'			=>	array
																										(
																											'Start'		=> 384,
																											'Length'	=> 16
																										),
																	'UnitRateIncGST'				=>	array
																										(
																											'Start'		=> 400,
																											'Length'	=> 16
																										),
																	'ProductBillingId'				=>	array
																										(
																											'Start'		=> 416,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 424,
																											'Length'	=> 8
																										),
																	'ServiceTypeDescription'		=>	array
																										(
																											'Start'		=> 432,
																											'Length'	=> 30
																										),
																	'ServiceNumberLabel'			=>	array
																										(
																											'Start'		=> 462,
																											'Length'	=> 30
																										),
																	'DataServiceType'				=>	array
																										(
																											'Start'		=> 492,
																											'Length'	=> 4
																										),
																	'Bandwidth'						=>	array
																										(
																											'Start'		=> 496,
																											'Length'	=> 10
																										),
																	'ChargeZone'					=>	array
																										(
																											'Start'		=> 506,
																											'Length'	=> 25
																										),
																	'AEndServiceNumber'				=>	array
																										(
																											'Start'		=> 531,
																											'Length'	=> 19
																										),
																	'AEndDLCI'						=>	array
																										(
																											'Start'		=> 550,
																											'Length'	=> 4
																										),
																	'AEndCIR'						=>	array
																										(
																											'Start'		=> 554,
																											'Length'	=> 7
																										),
																	'AEndVPI'						=>	array
																										(
																											'Start'		=> 550,
																											'Length'	=> 4
																										),
																	'AEndVCI'						=>	array
																										(
																											'Start'		=> 554,
																											'Length'	=> 5
																										),
																	'AEndFiller'					=>	array
																										(
																											'Start'		=> 559,
																											'Length'	=> 2
																										),
																	'BEndServiceNumber'				=>	array
																										(
																											'Start'		=> 561,
																											'Length'	=> 19
																										),
																	'BEndDLCI'						=>	array
																										(
																											'Start'		=> 580,
																											'Length'	=> 4
																										),
																	'BEndCIR'						=>	array
																										(
																											'Start'		=> 584,
																											'Length'	=> 7
																										),
																	'BEndVPI'						=>	array
																										(
																											'Start'		=> 580,
																											'Length'	=> 4
																										),
																	'BEndVCI'						=>	array
																										(
																											'Start'		=> 584,
																											'Length'	=> 5
																										),
																	'BEndFiller'					=>	array
																										(
																											'Start'		=> 589,
																											'Length'	=> 2
																										),
																	'LineSpeed'						=>	array
																										(
																											'Start'		=> 581,
																											'Length'	=> 10
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 601,
																											'Length'	=> 157
																										)
																),
													'OC'	=>	array
																(
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 10
																										),
																	'SectionType'					=>	array
																										(
																											'Start'		=> 37,
																											'Length'	=> 2
																										),
																	'LineType'						=>	array
																										(
																											'Start'		=> 39,
																											'Length'	=> 1
																										),
																	'GlobalItemReferenceNumber'		=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 7
																										),
																	'InvoiceCOSVCDescription'		=>	array
																										(
																											'Start'		=> 47,
																											'Length'	=> 30
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 77,
																											'Length'	=> 29
																										),
																	'GenericQuantity'				=>	array
																										(
																											'Start'		=> 106,
																											'Length'	=> 10
																										),
																	'BillingTransactionDescription'	=>	array
																										(
																											'Start'		=> 116,
																											'Length'	=> 50
																										),
																	'TransactionTypeDescription'	=>	array
																										(
																											'Start'		=> 166,
																											'Length'	=> 25
																										),
																	'TransactionTypeCode'			=>	array
																										(
																											'Start'		=> 191,
																											'Length'	=> 1
																										),
																	'StartDate'						=>	array
																										(
																											'Start'		=> 192,
																											'Length'	=> 8
																										),
																	'EndDate'						=>	array
																										(
																											'Start'		=> 200,
																											'Length'	=> 8
																										),
																	'PurchaseOrderNumber'			=>	array
																										(
																											'Start'		=> 208,
																											'Length'	=> 16
																										),
																	'UnitRateIncGST'				=>	array
																										(
																											'Start'		=> 224,
																											'Length'	=> 16
																										),
																	'SummaryGrossAmount'			=>	array
																										(
																											'Start'		=> 240,
																											'Length'	=> 14
																										),
																	'SummaryNetAmount'				=>	array
																										(
																											'Start'		=> 254,
																											'Length'	=> 14
																										),
																	'GSTAmount'						=>	array
																										(
																											'Start'		=> 268,
																											'Length'	=> 14
																										),
																	'SummaryPriceAmountIncGST'		=>	array
																										(
																											'Start'		=> 282,
																											'Length'	=> 14
																										),
																	'ServiceNumberLabel'			=>	array
																										(
																											'Start'		=> 296,
																											'Length'	=> 30
																										),
																	'FIDTextDescription'			=>	array
																										(
																											'Start'		=> 326,
																											'Length'	=> 90
																										),
																	'InstalmentPaymentNbr'			=>	array
																										(
																											'Start'		=> 416,
																											'Length'	=> 30
																										),
																	'InstalmentPaymentTotal'		=>	array
																										(
																											'Start'		=> 426,
																											'Length'	=> 10
																										),
																	'ProductBillingId'				=>	array
																										(
																											'Start'		=> 436,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 444,
																											'Length'	=> 8
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 452,
																											'Length'	=> 306
																										)
																)
												);
}
?>