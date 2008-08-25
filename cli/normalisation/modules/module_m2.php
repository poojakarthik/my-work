<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_m2
//----------------------------------------------------------------------------//
/**
 * module_m2.php
 *
 * Normalisation module for M2 CDR batch files
 *
 * Normalisation module for M2 CDR batch files
 *
 * @file			module_m2.php
 * @language		PHP
 * @package			normalisation
 * @author			Rich Davis
 * @version			8.07
 * @copyright		2008 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// NormalisationModuleM2
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleM2
 *
 * Normalisation module for M2 CDR batch files
 *
 * Normalisation module for M2 CDR batch files
 *
 * @prefix			nrm
 *
 * @package			normalisation
 * @class			NormalisationModuleM2
 */
class NormalisationModuleM2 extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_M2;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Module
	 *
	 * Constructor for the Normalising Module
	 *
	 *
	 * @method
	 */
	function __construct($intCarrier)
	{
		// call parent constructor
		parent::__construct($intCarrier);
		
		// define the column delimiter
		$this->_strDelimiter = "|";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine['RecordType']			['Index']		= 0;	// HE: Header/TR: Trailer/US: Usage/OC: Other Charges
		$arrDefine['CarrierId']				['Index']		= 1;	// Carrier the Charge Originated from
		$arrDefine['ReferenceNumber']		['Index']		= 2;	// Unique ID for the record (when combined with the RecordType)
		$arrDefine['FNN']					['Index']		= 3;	// Billed FNN
		$arrDefine['Source']				['Index']		= 4;	// Originating phone number
		$arrDefine['Destination']			['Index']		= 5;	// Destination phone number
		$arrDefine['ChargeDate']			['Index']		= 6;	// Date the call occurred on
		$arrDefine['ChargeTime']			['Index']		= 7;	// Time the call started
		$arrDefine['Duration']				['Index']		= 8;	// Seconds/KB
		$arrDefine['ChargeDateEnd']			['Index']		= 9;	// Charge Period End Date (Recurring Charges Only)
		$arrDefine['Description']			['Index']		= 10;	// Charge Description (Other Charges Only)
		$arrDefine['Quantity']				['Index']		= 11;	// Quantity of a product
		$arrDefine['ChargeAmount']			['Index']		= 12;	// Charge to YBS Customer in Cents
		$arrDefine['Tariff']				['Index']		= 13;	// M2 Tariff Code (Call Type)
		$arrDefine['ForeignChargeType']		['Index']		= 14;	// Originating Carrier Call Type (M2 Internal)
		$arrDefine['ForiegnCallDescCode']	['Index']		= 15;	// International Roaming Foreign Carrier Code
		$arrDefine['ServiceName']			['Index']		= 16;	// Name associated with ther Service number
		$arrDefine['ServiceDepartment']		['Index']		= 17;	// Department that the Service number belongs to
		$arrDefine['InstalmentNumber']		['Index']		= 18;	// Instalment number for this record of this product
		$arrDefine['InstalmentOf']			['Index']		= 19;	// Total Number of Installment payments that there will be
		$arrDefine['InstalmentTaxTreatment']['Index']		= 20;	// How GST should be applied to the Installment Charge
		$arrDefine['InstalmentTotalCharge']	['Index']		= 21;	// Total Charge for the Product
		$arrDefine['ExtensionPrimeNumber']	['Index']		= 22;	// If FNN is an Indial Extension, this is it's prime number
		
		$this->_arrDefineCarrier = $arrDefine;
	}

	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises raw data from the CDR
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
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR['CDR']);
		
		// ignore header/footer rows
		$strRecordType	= $this->_FetchRawCDR('RecordType');
		switch ($strRecordType)
		{
			case 'HE':
			case 'TR':
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
				break;
			
			case 'US':
				break;
				
			case 'OC':
				// M2 say that they will never send 'OC' records, but this is here just incase they decide to
				SendEmail("rich@voiptelsystems.com.au", "M2 CDR RecordType 'OC' Encountered!", "Unhandled M2 CDR RecordType 'OC' Encountered in CDR #{$arrCDR['Id']}");
			default:
				// Unrecognised Type
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
		
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// FNN
		$strFNN = $this->_FetchRawCDR('FNN');
		$strFNN	= $this->RemoveAusCode($strFNN);
		$this->_AppendCDR('FNN', $strFNN);
		
		// ServiceType
		$intServiceType	= ServiceType($strFNN);
		if ($intServiceType)
		{
			$this->_AppendCDR('ServiceType', $intServiceType);
		}
		else
		{
			// Temporary Debugging until ServiceType() can parse all FNNs
			SendEmail("rich@voiptelsystems.com.au", "ServiceType() Unhandled FNN", "ServiceType() was unable to handle the FNN: '{$strFNN}'");
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
		
		// CarrierRef
		$mixValue 						= $strRecordType.$this->_FetchRawCDR('ReferenceNumber');
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// Record Type-specific fields
		$mixCarrierCode					= $this->_FetchRawCDR('Tariff');
		$strRecordCode					= NULL;
		switch ($strRecordType)
		{
			// Usage Records
			case 'US':
				// Determine Call Type
				$strRecordCode 					= $this->FindRecordCode($mixCarrierCode);
				$intRecordType 					= $this->FindRecordType($intServiceType, $strRecordCode); 
				$this->_AppendCDR('RecordType', $intRecordType);
				
				// Units (seconds or KB)
				$this->_AppendCDR('Units', (int)$this->_FetchRawCDR('Duration'));
				
				// StartDatetime & EndDatetime
				$strStartDatetime	= $this->ConvertTime($this->_FetchRawCDR('ChargeDate')).' '.$this->_FetchRawCDR('ChargeTime');
				$this->_AppendCDR('StartDatetime', $strStartDatetime);
				if (in_array($strRecordCode, Array('3G', 'GPRS')))
				{
					// Data
					// No EndDatetime
				}
				else
				{
					// Call
					$intSeconds			= (int)$this->_FetchRawCDR('Duration');
					$strEndDatetime		= date("Y-m-d H:i:s", strtotime("+{$intSeconds} seconds", strtotime($strStartDatetime)));
					$this->_AppendCDR('EndDatetime', $strEndDatetime);
				}
				break;
			
			// Other Charges
			case 'OC':
				// Unhandled at the moment - this is just a placeholder
		}
		
		// Destination Code & Description (only if we have a context)
		if ($this->_intContext > 0)
		{
			$arrDestinationCode 		= $this->FindDestination($mixCarrierCode);
			if ($arrDestinationCode)
			{
				$this->_AppendCDR('DestinationCode'	, $arrDestinationCode['Code']);
				$this->_AppendCDR('Description'		, $arrDestinationCode['Description']);
			}
		}
		
		// Cost
		$mixValue	= ((float)$this->_FetchRawCDR('ChargeAmount')) / 100;
		$this->_AppendCDR('Cost', (float)$mixValue);
		
		// Source
		$mixValue	= ($this->_FetchRawCDR('Source')) ? $this->_FetchRawCDR('Source') : $this->_FetchRawCDR('FNN');
		$mixValue	= ((int)$mixValue) ? $mixValue : NULL;
		$this->_AppendCDR('Source', $this->RemoveAusCode($mixValue));
		
		// Destination
		$mixValue	= ($this->_FetchRawCDR('Destination')) ? $this->_FetchRawCDR('Destination') : $this->_FetchRawCDR('FNN');
		$mixValue	= ((int)$mixValue) ? $mixValue : NULL;
		$this->_AppendCDR('Destination', $this->RemoveAusCode($mixValue));
		
		// Is Credit?
		$this->_IsCredit();
		
		//--------------------------------------------------------------------//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	//------------------------------------------------------------------------//
	// ConvertTime
	//------------------------------------------------------------------------//
	/**
	 * ConvertTime()
	 *
	 * Convert time format
	 *
	 * Converts a datetime string from carrier's format to our own
	 *
	 * @param	string	$strTime	Datetime string to be converted
	 * @return	string				Converted Datetime string
	 *
	 * @method
	 */
	function ConvertTime($strTime)
	{
		return str_replace('/', '-', $strTime);
	}
	
	//------------------------------------------------------------------------//
	// RawDestinationCode
	//------------------------------------------------------------------------//
	/**
	 * RawDestinationCode()
	 *
	 * Returns the Raw Destination Code from the CDR
	 *
	 * Returns the Raw Destination Code from the CDR
	 * 
	 *
	 * @return	mixed					Raw Destination Code
	 *
	 * @method
	 */
	 function RawDestinationCode()
	 {
	 	return $this->_FetchRawCDR('RateId');
	 }
	 
	//------------------------------------------------------------------------//
	// RawDescription
	//------------------------------------------------------------------------//
	/**
	 * RawDescription()
	 *
	 * Returns the Raw Description from the CDR
	 *
	 * Returns the Raw Description from the CDR
	 * 
	 *
	 * @return	mixed					Raw Description
	 *
	 * @method
	 */
	 function RawDescription()
	 {
	 	return $this->_FetchRawCDR('Description');
	 }
	 
	//------------------------------------------------------------------------//
	// RawRecordType
	//------------------------------------------------------------------------//
	/**
	 * RawRecordType()
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 * 
	 *
	 * @return	mixed					Raw RawRecord Type
	 *
	 * @method
	 */
	 function RawRecordType()
	 {
	 	return (int)$this->_FetchRawCDR('CallType');
	 }
}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleRSLCOM
	//------------------------------------------------------------------------//
?>
