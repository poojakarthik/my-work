<?php
/**
 * NormalisationModuleLinxDailyEventFile
 *
 * Normalisation module for LinxOnline Daily Event Files
 * 
 * @class	NormalisationModuleLinxDailyEventFile
 */
class NormalisationModuleLinxDailyEventFile extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_TELSTRA;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE;
	
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
		$sRawRecordType	= strtoupper(substr($arrCDR['Raw'], 0 , 3));
		switch ($sRawRecordType)
		{
			// Usage Records
			case 'UIR':	// Usage Information Record
			case 'UAR':	// Usage Information Adjustment Record
				$this->_arrDefineCarrier	= $this->_arrRecordDefinitions[$sRawRecordType];
				
				// covert CDR string to array
				$this->_SplitRawCDR($arrCDR['CDR']);
				call_user_method("_normalise{$sRawRecordType}", $this);
				break;
			
			// Other Records (read from Monthly Invoice File instead)
			case 'OCR':	// Other Charges & Credits (OC&C) Record
			case 'OAR':	// OC&C Adjustment Record
			case 'SER':	// Service & Equipment (S&E) Record
			
			// Rating Records
			case 'NTR':	// Non-Usage Tariff Record
			case 'UTR':	// Usage Tariff Record
			case 'CNR':	// CNR Usage Tariff Record
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;
			
			// Headers/Trailers
			case 'FDR':	// File Designator Record
			case 'UIT':	// Usage Information Trailer Record
			case 'UAT':	// Usage Information Adjustment Trailer Record
			case 'SET':	// Service & Equipment (S&E) Trailer Record
			case 'OCT':	// Other Charges & Credits (OC&C) Trailer Record
			case 'OAT':	// OC&C Adjustment Trailer Record
			case 'NTT':	// Non-Usage Tariff Trailer Record
			case 'UTT':	// Usage Tariff Trailer Record
			case 'CNT':	// CNR Usage Tariff Trailer Record
			case 'FTR':	// Usage Processing File Trailer Record
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;
			
			default:
				// Unhandled File Record Type
				throw new Exception_Assertion("Unhandled LINX Daily Event File Record Type: '{$sRawRecordType}'", $arrCDR, "Unhandled LINX Daily Event File Record Type: '{$sRawRecordType}'");
				break;
		}
		
		//--------------------------------------------------------------------//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	// Usage Records
	private function _normaliseUIR()
	{
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('EventFileInstanceId').'.'.$this->_FetchRawCDR('EventRecordSequenceNumber').'.'.$this->_FetchRawCDR('InputLinxOnlineEBillFileId'));
		
		// FNN
		$sFNN	= self::RemoveAusCode($this->_FetchRawCDR('FullNationalNumber'));
		$this->_AppendCDR('FNN', $sFNN);
		
		// Source
		$this->_AppendCDR('Source', self::RemoveAusCode($this->_FetchRawCDR('OriginatingNumber')));
		
		// Destination
		$this->_AppendCDR('Destination', self::RemoveAusCode($this->_FetchRawCDR('DestinationNumber')));
		
		// Units
		$fUnits		= (int)$this->_FetchRawCDR('Quantity') / 100000;			// Quantity has 5 implied decimal places
		$sUnitType	= trim($this->_FetchRawCDR('UnitOfMeasureCode'));
		$iUnits		= null;
		switch ($sUnitType)
		{
			case 'CALL':	// Calls		(Not Used Very Often)
				$iUnits	= ceil($fUnits);
				break;
			case 'EVENT':	// Events		(e.g. Call Return or Surcharge)
				$iUnits	= ceil($fUnits);
				break;
			case 'ITEM':	// Items		(Not Used Very Often)
				$iUnits	= ceil($fUnits);
				break;
			case 'KBYTE':	// Kilobytes	(Not Used Very Often)
				$iUnits	= ceil($fUnits);
				break;
			case 'MIN':		// Minutes		(Not Used Very Often)
				$iUnits	= ceil($fUnits * 60);
				break;
			case 'PULSE':	// Pulses		(e.g. Payphones)
				$iUnits	= ceil($fUnits);
				break;
			case 'SEC':		// Seconds		(e.g. STD Call)
				$iUnits	= ceil($fUnits);
				break;
			case '6SEC':	// Time Period	(Not Used Very Often)
				$iUnits	= ceil($fUnits * 6); // I think??
				break;
			case 'SEG':		// Segments		(e.g. Data Billing)
				$iUnits	= ceil($fUnits);
				break;
			
			default:
				// TODO
				break;
		}
		$this->_AppendCDR('Units', $iUnits);
		
		// StartDatetime
		$sOriginatingDate		= trim($this->_FetchRawCDR('OriginatingDate'));
		$sOriginatingTime		= $this->_FetchRawCDR('OriginatingTime');
		$sOriginatingDatetime	= substr($sOriginatingDate, 0, 4) . '-'
								. substr($sOriginatingDate, 4, 2) . '-'
								. substr($sOriginatingDate, 6, 2) . ' '
								. substr($sOriginatingTime, 0, 2) . ':'
								. substr($sOriginatingTime, 3, 2) . ':'
								. substr($sOriginatingTime, 6, 2);
		$iOriginatingTimestamp	= strtotime($sOriginatingDatetime);
		$this->_AppendCDR('StartDatetime', $sOriginatingDatetime);
		
		// EndDatetime
		$iDurationSeconds	= null;
		if (in_array($sUnitType, array('SEC', 'MIN')))
		{
			// Derive duration from Units for time-based usage
			$iDurationSeconds	= $iUnits;
		}
		else
		{
			// Determine duration from the Call Duration field
			$sDuration			= $this->_FetchRawCDR('CallDuration');
			$iDurationSeconds	= ((int)substr($sDuration, -2, 2))
								+ ((int)substr($sDuration, -4, 2))
								+ ((int)substr($sDuration, 0, -4));
		}
		$this->_AppendCDR('EndDatetime', date("Y-m-d H:i:s", $iOriginatingTimestamp + $iDurationSeconds));
		
		// Cost
		$this->_AppendCDR('Cost', ((int)$this->_FetchRawCDR('Price')) / 10000000);	// Price has 7 implied decimal places
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		// RecordType
		$sRecordCode	= $this->FindRecordCode($this->_FetchRawCDR('ProductBillingIdentifier'));
		$iRecordType	= $this->FindRecordType($iServiceType, $sRecordCode);
		
		// Destination
		$aDestination	= null;
		if ($this->_intContext)
		{
			$aDestination	= $this->FindDestination(trim($this->_FetchRawCDR('DistanceRangeCode')));
			$this->_AppendCDR('Destination', $aDestination['Code']);
		}
		
		// Description
		$sDescription	= '';
		if ($aDestination)
		{
			if ($aDestination['bolUnknownDestination'])
			{
				// Unknown IDD Destination
				$sDescription	= trim($this->_FetchRawCDR('ToArea'));
			}
			else
			{
				// Destination
				$sDescription	= $this->_FetchRawCDR('Destination', $aDestination['Description']);
			}
		}
		elseif ($sRecordCode == 'OneNineHundred')
		{
			// 1900s have a special Description
			$sDescription	= trim($this->_FetchRawCDR('1900CallDescription'));
		}
		else
		{
			// Revert to ToArea
			$sDescription	= trim($this->_FetchRawCDR('ToArea'));
		}
		$this->_AppendCDR('Description', $sDescription);
		
		// Credit
		$this->_AppendCDR('Credit', 0);
		
		return;
	}
	
	// Usage Credits/Refunds
	private function _normaliseUAR()
	{
		// Process as a UIR
		$this->_normaliseUIR();
		
		// Credit
		$this->_AppendCDR('Credit', 1);
	}
	
	static private	$_arrRecordDefinitions	=	array
												(
													'UIR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'EventInstanceFileId'			=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 8
																										),
																	'EventRecordSequenceNumber'		=>	array
																										(
																											'Start'		=> 14,
																											'Length'	=> 8
																										),
																	'InputLinxOnlineEBillFileId'	=>	array
																										(
																											'Start'		=> 22,
																											'Length'	=> 8
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 30,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 38,
																											'Length'	=> 8
																										),
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 46,
																											'Length'	=> 10
																										),
																	'ServiceArrangementId'			=>	array
																										(
																											'Start'		=> 56,
																											'Length'	=> 10
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 66,
																											'Length'	=> 29
																										),
																	'OriginatingNumber'				=>	array
																										(
																											'Start'		=> 95,
																											'Length'	=> 25
																										),
																	'DestinationNumber'				=>	array
																										(
																											'Start'		=> 120,
																											'Length'	=> 25
																										),
																	'OriginatingDate'				=>	array
																										(
																											'Start'		=> 145,
																											'Length'	=> 10
																										),
																	'OriginatingTime'				=>	array
																										(
																											'Start'		=> 155,
																											'Length'	=> 8
																										),
																	'ToArea'						=>	array
																										(
																											'Start'		=> 163,
																											'Length'	=> 12
																										),
																	'UnitOfMeasureCode'				=>	array
																										(
																											'Start'		=> 175,
																											'Length'	=> 5
																										),
																	'Quantity'						=>	array
																										(
																											'Start'		=> 180,
																											'Length'	=> 13
																										),
																	'CallDuration'					=>	array
																										(
																											'Start'		=> 193,
																											'Length'	=> 9,
																											'Validate'	=> '/^\d{9}$/'
																										),
																	'CallTypeCode'					=>	array
																										(
																											'Start'		=> 202,
																											'Length'	=> 3
																										),
																	'RecordType'					=>	array
																										(
																											'Start'		=> 205,
																											'Length'	=> 1
																										),
																	'Price'							=>	array
																										(
																											'Start'		=> 206,
																											'Length'	=> 15
																										),
																	'DistanceRangeCode'				=>	array
																										(
																											'Start'		=> 221,
																											'Length'	=> 4
																										),
																	'ClosedUserGroupId'				=>	array
																										(
																											'Start'		=> 225,
																											'Length'	=> 5
																										),
																	'ReversalChargeIndicator'		=>	array
																										(
																											'Start'		=> 230,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|\ )$/i'
																										),
																	'1900CallDescription'			=>	array
																										(
																											'Start'		=> 231,
																											'Length'	=> 30
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 261,
																											'Length'	=> 253
																										)
																),
													'UAR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'EventInstanceFileId'			=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 8
																										),
																	'EventRecordSequenceNumber'		=>	array
																										(
																											'Start'		=> 14,
																											'Length'	=> 8
																										),
																	'InputLinxOnlineEBillFileId'	=>	array
																										(
																											'Start'		=> 22,
																											'Length'	=> 8
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 30,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 38,
																											'Length'	=> 8
																										),
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 46,
																											'Length'	=> 10
																										),
																	'ServiceArrangementId'			=>	array
																										(
																											'Start'		=> 56,
																											'Length'	=> 10
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 66,
																											'Length'	=> 29
																										),
																	'OriginatingNumber'				=>	array
																										(
																											'Start'		=> 95,
																											'Length'	=> 25
																										),
																	'DestinationNumber'				=>	array
																										(
																											'Start'		=> 120,
																											'Length'	=> 25
																										),
																	'OriginatingDate'				=>	array
																										(
																											'Start'		=> 145,
																											'Length'	=> 10
																										),
																	'OriginatingTime'				=>	array
																										(
																											'Start'		=> 155,
																											'Length'	=> 8
																										),
																	'ToArea'						=>	array
																										(
																											'Start'		=> 163,
																											'Length'	=> 12
																										),
																	'UnitOfMeasureCode'				=>	array
																										(
																											'Start'		=> 175,
																											'Length'	=> 5
																										),
																	'Quantity'						=>	array
																										(
																											'Start'		=> 180,
																											'Length'	=> 13
																										),
																	'CallDuration'					=>	array
																										(
																											'Start'		=> 193,
																											'Length'	=> 9,
																											'Validate'	=> '/^\d{9}$/'
																										),
																	'CallTypeCode'					=>	array
																										(
																											'Start'		=> 202,
																											'Length'	=> 3
																										),
																	'RecordType'					=>	array
																										(
																											'Start'		=> 205,
																											'Length'	=> 1
																										),
																	'Price'							=>	array
																										(
																											'Start'		=> 206,
																											'Length'	=> 15
																										),
																	'UsageAdjustmentReasonCode'		=>	array
																										(
																											'Start'		=> 221,
																											'Length'	=> 3
																										),
																	'ClosedUserGroupId'				=>	array
																										(
																											'Start'		=> 224,
																											'Length'	=> 5
																										),
																	'ReversalChargeIndicator'		=>	array
																										(
																											'Start'		=> 229,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|\ )$/i'
																										),
																	'1900CallDescription'			=>	array
																										(
																											'Start'		=> 230,
																											'Length'	=> 30
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 260,
																											'Length'	=> 254
																										)
																),
													'SER'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'CustomerProductItemId'			=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 10
																										),
																	'InputLinxOnlineEBillFileId'	=>	array
																										(
																											'Start'		=> 16,
																											'Length'	=> 8
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 24,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 32,
																											'Length'	=> 8
																										),
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 10
																										),
																	'ServiceArrangementId'			=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 10
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 60,
																											'Length'	=> 29
																										),
																	'ProductActionCode'				=>	array
																										(
																											'Start'		=> 89,
																											'Length'	=> 1
																										),
																	'PurchaseOrderNumber'			=>	array
																										(
																											'Start'		=> 90,
																											'Length'	=> 16
																										),
																	'FormattedProductEffectiveDate'	=>	array
																										(
																											'Start'		=> 106,
																											'Length'	=> 10
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 116,
																											'Length'	=> 15
																										),
																	'ItemQuantity'					=>	array
																										(
																											'Start'		=> 131,
																											'Length'	=> 5
																										),
																	'OrderNegotiatedRateIndicator'	=>	array
																										(
																											'Start'		=> 136,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'BillingTransactionDescription'	=>	array
																										(
																											'Start'		=> 137,
																											'Length'	=> 50
																										),
																	'ProductDescriptionText1'		=>	array
																										(
																											'Start'		=> 187,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText2'		=>	array
																										(
																											'Start'		=> 217,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText3'		=>	array
																										(
																											'Start'		=> 247,
																											'Length'	=> 30
																										),
																	'DataServiceType'				=>	array
																										(
																											'Start'		=> 277,
																											'Length'	=> 4
																										),
																	'Bandwidth'						=>	array
																										(
																											'Start'		=> 281,
																											'Length'	=> 10
																										),
																	'ChargeZone'					=>	array
																										(
																											'Start'		=> 291,
																											'Length'	=> 25
																										),
																	'ServiceLocation1'				=>	array
																										(
																											'Start'		=> 316,
																											'Length'	=> 60
																										),
																	'ServiceLocation2'				=>	array
																										(
																											'Start'		=> 376,
																											'Length'	=> 60
																										),
																	'AEndServiceNumber'				=>	array
																										(
																											'Start'		=> 436,
																											'Length'	=> 19
																										),
																	'AEndDLCI'						=>	array
																										(
																											'Start'		=> 455,
																											'Length'	=> 4
																										),
																	'AEndCIR'						=>	array
																										(
																											'Start'		=> 459,
																											'Length'	=> 7
																										),
																	'AEndVPI'						=>	array
																										(
																											'Start'		=> 466,
																											'Length'	=> 4
																										),
																	'AEndVCI'						=>	array
																										(
																											'Start'		=> 470,
																											'Length'	=> 5
																										),
																	'BEndServiceNumber'				=>	array
																										(
																											'Start'		=> 475,
																											'Length'	=> 19
																										),
																	'BEndDLCI'						=>	array
																										(
																											'Start'		=> 494,
																											'Length'	=> 4
																										),
																	'BEndCIR'						=>	array
																										(
																											'Start'		=> 498,
																											'Length'	=> 7
																										),
																	'BEndVPI'						=>	array
																										(
																											'Start'		=> 505,
																											'Length'	=> 4
																										),
																	'BEndVCI'						=>	array
																										(
																											'Start'		=> 509,
																											'Length'	=> 5
																										)
																),
													'OCR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'CustomerProductItemId'			=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 10
																										),
																	'InputLinxOnlineEBillFileId'	=>	array
																										(
																											'Start'		=> 16,
																											'Length'	=> 8
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 24,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 32,
																											'Length'	=> 8
																										),
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 10
																										),
																	'ServiceArrangementId'			=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 10
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 60,
																											'Length'	=> 29
																										),
																	'PurchaseOrderNumber'			=>	array
																										(
																											'Start'		=> 89,
																											'Length'	=> 16
																										),
																	'FormattedProductEffectiveDate'	=>	array
																										(
																											'Start'		=> 105,
																											'Length'	=> 10
																										),
																	'AmountSignIndicator'			=>	array
																										(
																											'Start'		=> 115,
																											'Length'	=> 1,
																											'Validate'	=> '/^(\-|\ )$/'
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 116,
																											'Length'	=> 15
																										),
																	'ServiceOrderItemQuantity'		=>	array
																										(
																											'Start'		=> 131,
																											'Length'	=> 5
																										),
																	'OrderNegotiatedRateIndicator'	=>	array
																										(
																											'Start'		=> 136,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'TotalInstalmentQuantity'		=>	array
																										(
																											'Start'		=> 137,
																											'Length'	=> 2
																										),
																	'BillingTransactionDescription'	=>	array
																										(
																											'Start'		=> 139,
																											'Length'	=> 50
																										),
																	'ProductDescriptionText1'		=>	array
																										(
																											'Start'		=> 189,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText2'		=>	array
																										(
																											'Start'		=> 219,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText3'		=>	array
																										(
																											'Start'		=> 249,
																											'Length'	=> 30
																										),
																	'Price'						=>	array
																										(
																											'Start'		=> 279,
																											'Length'	=> 15
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 294,
																											'Length'	=> 220
																										)
																),
													'OAR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'CustomerProductItemId'			=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 10
																										),
																	'InputLinxOnlineEBillFileId'	=>	array
																										(
																											'Start'		=> 16,
																											'Length'	=> 8
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 24,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 32,
																											'Length'	=> 8
																										),
																	'InvoiceArrangementId'			=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 10
																										),
																	'ServiceArrangementId'			=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 10
																										),
																	'FullNationalNumber'			=>	array
																										(
																											'Start'		=> 60,
																											'Length'	=> 29
																										),
																	'PurchaseOrderNumber'			=>	array
																										(
																											'Start'		=> 89,
																											'Length'	=> 16
																										),
																	'FormattedProductEffectiveDate'	=>	array
																										(
																											'Start'		=> 105,
																											'Length'	=> 10
																										),
																	'AmountSignIndicator'			=>	array
																										(
																											'Start'		=> 115,
																											'Length'	=> 1,
																											'Validate'	=> '/^(\-|\ )$/'
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 116,
																											'Length'	=> 15
																										),
																	'ServiceOrderItemQuantity'		=>	array
																										(
																											'Start'		=> 131,
																											'Length'	=> 5
																										),
																	'OrderNegotiatedRateIndicator'	=>	array
																										(
																											'Start'		=> 136,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'TotalInstalmentQuantity'		=>	array
																										(
																											'Start'		=> 137,
																											'Length'	=> 2
																										),
																	'BillingTransactionDescription'	=>	array
																										(
																											'Start'		=> 139,
																											'Length'	=> 50
																										),
																	'ProductDescriptionText1'		=>	array
																										(
																											'Start'		=> 189,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText2'		=>	array
																										(
																											'Start'		=> 219,
																											'Length'	=> 30
																										),
																	'ProductDescriptionText3'		=>	array
																										(
																											'Start'		=> 249,
																											'Length'	=> 30
																										),
																	'Price'						=>	array
																										(
																											'Start'		=> 279,
																											'Length'	=> 15
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 294,
																											'Length'	=> 220
																										)
																),
													'NTR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'WholesaleRedirectionGroup'		=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 6
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 12,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 20,
																											'Length'	=> 8
																										),
																	'ActivityCompletedIndicator'	=>	array
																										(
																											'Start'		=> 28,
																											'Length'	=> 1,
																											'Validate'	=> '/^\ $/'
																										),
																	'ServiceChargeItemGroup'		=>	array
																										(
																											'Start'		=> 29,
																											'Length'	=> 20
																										),
																	'UnbilledIndicator'				=>	array
																										(
																											'Start'		=> 49,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|\ )$/i'
																										),
																	'GSTPercentageRate'				=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 7
																										),
																	'RateStructureStartDate'		=>	array
																										(
																											'Start'		=> 57,
																											'Length'	=> 10
																										),
																	'RateStructureEndDate'			=>	array
																										(
																											'Start'		=> 67,
																											'Length'	=> 10
																										),
																	'AmountSignIndicator'			=>	array
																										(
																											'Start'		=> 77,
																											'Length'	=> 1,
																											'Validate'	=> '/^(\-|\ )$/'
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 78,
																											'Length'	=> 15
																										),
																	'BillingElementCategoryCode'	=>	array
																										(
																											'Start'		=> 93,
																											'Length'	=> 1
																										),
																	'Description'					=>	array
																										(
																											'Start'		=> 94,
																											'Length'	=> 50
																										),
																	'UnitHighQuantity'				=>	array
																										(
																											'Start'		=> 144,
																											'Length'	=> 8
																										),
																	'UnitLowQuantity'				=>	array
																										(
																											'Start'		=> 152,
																											'Length'	=> 8
																										),
																	'CustomerAgreementId'			=>	array
																										(
																											'Start'		=> 160,
																											'Length'	=> 10
																										),
																	'AgreementStartDate'			=>	array
																										(
																											'Start'		=> 170,
																											'Length'	=> 10
																										),
																	'AgreementEndDate'				=>	array
																										(
																											'Start'		=> 180,
																											'Length'	=> 10
																										),
																	'TariffChangeIndicator'			=>	array
																										(
																											'Start'		=> 190,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 191,
																											'Length'	=> 323
																										)
																),
													'UTR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'WholesaleRedirectionGroup'		=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 6
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 12,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 20,
																											'Length'	=> 8
																										),
																	'ActivityCompletedIndicator'	=>	array
																										(
																											'Start'		=> 28,
																											'Length'	=> 1,
																											'Validate'	=> '/^\ $/'
																										),
																	'ServiceChargeItemGroup'		=>	array
																										(
																											'Start'		=> 29,
																											'Length'	=> 20
																										),
																	'UnbilledIndicator'				=>	array
																										(
																											'Start'		=> 49,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|\ )$/i'
																										),
																	'GSTPercentageRate'				=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 7
																										),
																	'RateStructureStartDate'		=>	array
																										(
																											'Start'		=> 57,
																											'Length'	=> 10
																										),
																	'RateStructureEndDate'			=>	array
																										(
																											'Start'		=> 67,
																											'Length'	=> 10
																										),
																	'Description'					=>	array
																										(
																											'Start'		=> 77,
																											'Length'	=> 80
																										),
																	'UnitOfMeasureCode'				=>	array
																										(
																											'Start'		=> 157,
																											'Length'	=> 5
																										),
																	'RatePeriod'					=>	array
																										(
																											'Start'		=> 162,
																											'Length'	=> 1
																										),
																	'RatePeriodDescription'			=>	array
																										(
																											'Start'		=> 163,
																											'Length'	=> 10
																										),
																	'FlagfallAmountSignIndicator'	=>	array
																										(
																											'Start'		=> 173,
																											'Length'	=> 1,
																											'Validate'	=> '/^(\-|\ )$/'
																										),
																	'FlagfallAmount'				=>	array	/* ex-GST */
																										(
																											'Start'		=> 174,
																											'Length'	=> 15
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 189,
																											'Length'	=> 15
																										),
																	'PulseInterval'					=>	array
																										(
																											'Start'		=> 204,
																											'Length'	=> 7
																										),
																	'DayOfWeekStart'				=>	array
																										(
																											'Start'		=> 211,
																											'Length'	=> 1
																										),
																	'DayOfWeekEnd'					=>	array
																										(
																											'Start'		=> 212,
																											'Length'	=> 1
																										),
																	'CallRangeStartTime'			=>	array
																										(
																											'Start'		=> 213,
																											'Length'	=> 8
																										),
																	'CallRangeEndTime'				=>	array
																										(
																											'Start'		=> 221,
																											'Length'	=> 8
																										),
																	'DistanceRangeCode'				=>	array
																										(
																											'Start'		=> 229,
																											'Length'	=> 4
																										),
																	'DistanceRangeDescription'		=>	array
																										(
																											'Start'		=> 233,
																											'Length'	=> 50
																										),
																	'UnitRateMethod'				=>	array
																										(
																											'Start'		=> 283,
																											'Length'	=> 1
																										),
																	'UnitHighQuantity'				=>	array
																										(
																											'Start'		=> 284,
																											'Length'	=> 8
																										),
																	'UnitLowQuantity'				=>	array
																										(
																											'Start'		=> 292,
																											'Length'	=> 8
																										),
																	'MaximumChargeAmount'			=>	array
																										(
																											'Start'		=> 300,
																											'Length'	=> 11
																										),
																	'MinimumChargeAmount'			=>	array
																										(
																											'Start'		=> 311,
																											'Length'	=> 11
																										),
																	'MaximumCallLength'				=>	array
																										(
																											'Start'		=> 322,
																											'Length'	=> 11
																										),
																	'MinimumCallLength'				=>	array
																										(
																											'Start'		=> 333,
																											'Length'	=> 11
																										),
																	'RateQuantityDependant'			=>	array
																										(
																											'Start'		=> 344,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'TariffChangeIndicator'			=>	array
																										(
																											'Start'		=> 345,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 346,
																											'Length'	=> 168
																										)
																),
													'CTR'	=>	array
																(
																	'InterfaceRecordType'			=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 3
																										),
																	'ServiceProviderCode'			=>	array
																										(
																											'Start'		=> 3,
																											'Length'	=> 3
																										),
																	'WholesaleRedirectionGroup'		=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 6
																										),
																	'ProductBillingIdentifier'		=>	array
																										(
																											'Start'		=> 12,
																											'Length'	=> 8
																										),
																	'BillingElementCode'			=>	array
																										(
																											'Start'		=> 20,
																											'Length'	=> 8
																										),
																	'ActivityCompletedIndicator'	=>	array
																										(
																											'Start'		=> 28,
																											'Length'	=> 1,
																											'Validate'	=> '/^\ $/'
																										),
																	'ServiceChargeItemGroup'		=>	array
																										(
																											'Start'		=> 29,
																											'Length'	=> 20
																										),
																	'UnbilledIndicator'				=>	array
																										(
																											'Start'		=> 49,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|\ )$/i'
																										),
																	'GSTPercentageRate'				=>	array
																										(
																											'Start'		=> 50,
																											'Length'	=> 7
																										),
																	'RateStructureStartDate'		=>	array
																										(
																											'Start'		=> 57,
																											'Length'	=> 10
																										),
																	'RateStructureEndDate'			=>	array
																										(
																											'Start'		=> 67,
																											'Length'	=> 10
																										),
																	'Description'					=>	array
																										(
																											'Start'		=> 77,
																											'Length'	=> 80
																										),
																	'UnitOfMeasureCode'				=>	array
																										(
																											'Start'		=> 157,
																											'Length'	=> 5
																										),
																	'RatePeriod'					=>	array
																										(
																											'Start'		=> 162,
																											'Length'	=> 1
																										),
																	'RatePeriodDescription'			=>	array
																										(
																											'Start'		=> 163,
																											'Length'	=> 10
																										),
																	'FlagfallAmountSignIndicator'	=>	array
																										(
																											'Start'		=> 173,
																											'Length'	=> 1,
																											'Validate'	=> '/^(\-|\ )$/'
																										),
																	'FlagfallAmount'				=>	array	/* ex-GST */
																										(
																											'Start'		=> 174,
																											'Length'	=> 15
																										),
																	'UnitRate'	/* ex-GST */		=>	array
																										(
																											'Start'		=> 189,
																											'Length'	=> 15
																										),
																	'PulseInterval'					=>	array
																										(
																											'Start'		=> 204,
																											'Length'	=> 7
																										),
																	'DayOfWeekStart'				=>	array
																										(
																											'Start'		=> 211,
																											'Length'	=> 1
																										),
																	'DayOfWeekEnd'					=>	array
																										(
																											'Start'		=> 212,
																											'Length'	=> 1
																										),
																	'CallRangeStartTime'			=>	array
																										(
																											'Start'		=> 213,
																											'Length'	=> 8
																										),
																	'CallRangeEndTime'				=>	array
																										(
																											'Start'		=> 221,
																											'Length'	=> 8
																										),
																	'DistanceRangeCode'				=>	array
																										(
																											'Start'		=> 229,
																											'Length'	=> 4
																										),
																	'DistanceRangeDescription'		=>	array
																										(
																											'Start'		=> 233,
																											'Length'	=> 50
																										),
																	'UnitRateMethod'				=>	array
																										(
																											'Start'		=> 283,
																											'Length'	=> 1
																										),
																	'UnitHighQuantity'				=>	array
																										(
																											'Start'		=> 284,
																											'Length'	=> 8
																										),
																	'UnitLowQuantity'				=>	array
																										(
																											'Start'		=> 292,
																											'Length'	=> 8
																										),
																	'MaximumChargeAmount'			=>	array
																										(
																											'Start'		=> 300,
																											'Length'	=> 11
																										),
																	'MinimumChargeAmount'			=>	array
																										(
																											'Start'		=> 311,
																											'Length'	=> 11
																										),
																	'MaximumCallLength'				=>	array
																										(
																											'Start'		=> 322,
																											'Length'	=> 11
																										),
																	'MinimumCallLength'				=>	array
																										(
																											'Start'		=> 333,
																											'Length'	=> 11
																										),
																	'RateQuantityDependant'			=>	array
																										(
																											'Start'		=> 344,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'CustomerArrangementId'			=>	array
																										(
																											'Start'		=> 345,
																											'Length'	=> 10
																										),
																	'AgreementStartDate'			=>	array
																										(
																											'Start'		=> 355,
																											'Length'	=> 10
																										),
																	'AgreementEndDate'				=>	array
																										(
																											'Start'		=> 365,
																											'Length'	=> 10
																										),
																	'TariffChangeIndicator'			=>	array
																										(
																											'Start'		=> 375,
																											'Length'	=> 1,
																											'Validate'	=> '/^(Y|N)$/i'
																										),
																	'Filler'						=>	array
																										(
																											'Start'		=> 376,
																											'Length'	=> 138
																										)
																)
												);
}
?>