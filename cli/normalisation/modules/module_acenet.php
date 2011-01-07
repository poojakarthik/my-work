<?php
/**
 * NormalisationModuleAcenet
 *
 * Normalisation module for Acenet CDR Files
 *
 * @class	NormalisationModuleAcenet
 */
class NormalisationModuleAcenet extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_ACENET;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET;
	
	const	RECORD_TYPE_USAGE						= 1;
	const	RECORD_TYPE_SERVICE_AND_EQUIPMENT		= 7;
	const	RECORD_TYPE_OTHER_CHARGES_AND_CREDITS	= 8;
	
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
		
		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 0;
		
		$this->_iSequence	= 0;
		
		// define the carrier CDR format
		$this->_arrDefineCarrier	= self::$_arrRecordDefinitions['TRANSACTION'];
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
		
		// SequenceNo
		$this->_AppendCDR('SequenceNo', $this->_iSequence++);
		
		$this->_SplitRawCDR($arrCDR['CDR']);
		
		//--------------------------------------------------------------------//
		if (trim($arrCDR['CDR']))
		{
			// Detail Record
			$this->_SplitRawCDR($arrCDR['CDR']);
			$this->_normalise();
		}
		else
		{
			// Any trailing or leading lines
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
		//--------------------------------------------------------------------//
		
		//Debug($this->_arrNormalisedData);
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	// Usage Records
	private function _normalise()
	{
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('UniqueId'));
		
		// FNN
		$sFNN	= self::RemoveAusCode(trim($this->_FetchRawCDR('ChargedNumber')));
		$this->_AppendCDR('FNN', $sFNN);
		
		// Source
		$this->_AppendCDR('Source', self::RemoveAusCode($this->_FetchRawCDR('ServiceNumber')));
		
		// Destination
		if ($sDestination = self::RemoveAusCode($this->_FetchRawCDR('NumberDialled')))
		{
			$this->_AppendCDR('Destination', $sDestination);
		}
		
		// Cost
		$fPrice	= (float)$this->_FetchRawCDR('Price');
		$this->_AppendCDR('Cost', abs($fPrice));
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		$sClassCode		= trim($this->_FetchRawCDR('ClassCode'));
		$aClassCode		= explode('-', $sClassCode);
		
		// RecordType (Call Type Group)
		$iAcenetRecordType	= (int)$this->_FetchRawCDR('RecordType');
		switch ($iAcenetRecordType)
		{
			case self::RECORD_TYPE_USAGE:
				$sRecordCode	= $this->FindRecordCode($sClassCode);

				// Debug: Throw a caught Assertion to notify YBS of missing Class Codes
				try
				{
					Flex::assert($sRecordCode !== false, "Acenet CDR File: Unrecognised Class Code '{$sClassCode}' encountered", print_r($this->DebugCDR(), true));
				}
				catch (Exception_Assertion $oException)	{}
				break;
			
			case self::RECORD_TYPE_SERVICE_AND_EQUIPMENT:
				$sRecordCode	= 'S&E';
				Flex::assert(false, "Acenet CDR File: Service & Equipment Record encountered", print_r($this->DebugCDR(), true));
				break;
			case self::RECORD_TYPE_OTHER_CHARGES_AND_CREDITS:
				$sRecordCode	= 'S&E';
				Flex::assert(false, "Acenet CDR File: Other Charges & Credits Record encountered", print_r($this->DebugCDR(), true));
				break;
		}
		$iRecordType	= $this->FindRecordType($iServiceType, $sRecordCode);
		$this->_AppendCDR('RecordType', $iRecordType);
		
		// Destination (sub-Call Type)
		$aDestination	= null;
		if ($this->_intContext)
		{
			$aDestination	= $this->FindDestination((isset($aClassCode[1])) ? $aClassCode[1] : null);
			$this->_AppendCDR('DestinationCode', $aDestination['Code']);
		}
		
		// Description
		$sDescription	= '';
		if ($aDestination)
		{
			if (!$aDestination['bolUnknownDestination'])
			{
				// Destination
				$sDescription	= $aDestination['Description'];
			}
		}
		$this->_AppendCDR('Description', $sDescription);
		
		// Units
		$iDuration	= (int)$this->_FetchRawCDR('Duration');	// Already in Seconds // FIXME: What about S&E or OC&C?
		$this->_AppendCDR('Units', abs($iDuration));
		
		// StartDatetime
		$sStartDatetime	= date('Y-m-d H:i:s', strtotime(trim($this->_FetchRawCDR('Date')).trim($this->_FetchRawCDR('Time'))));
		$this->_AppendCDR('StartDatetime', $sStartDatetime);
		
		// EndDatetime
		$sEndDatetime	= date('Y-m-d H:i:s', strtotime("+".abs($iDuration)." seconds", strtotime($sStartDatetime)));
		$this->_AppendCDR('EndDatetime', $sEndDatetime);
		
		// Credit
		// FIXME: Is this how it's done?  Or negative Price?
		$this->_AppendCDR('Credit', (int)($iDuration < 0 || $fPrice < 0));
		
		return;
	}
	
	static private	$_arrRecordDefinitions	=	array
												(
													'TRANSACTION'	=>	array
																		(
																			'Carrier'					=>	array
																											(
																												'Index'		=> 0
																											),
																			'UniqueId'					=>	array
																											(
																												'Index'		=> 1
																											),
																			'RecordType'				=>	array
																											(
																												'Index'		=> 2
																											),
																			'Local'						=>	array
																											(
																												'Index'		=> 3
																											),
																			'ServiceNumber'				=>	array	// Origin
																											(
																												'Index'		=> 4
																											),
																			'ChargedNumber'				=>	array
																											(
																												'Index'		=> 5
																											),
																			'Date'						=>	array
																											(
																												'Index'		=> 6
																											),
																			'Time'						=>	array
																											(
																												'Index'		=> 7
																											),
																			'Duration'					=>	array
																											(
																												'Index'		=> 8
																											),
																			'NumberDialled'				=>	array
																											(
																												'Index'		=> 9
																											),
																			'FromDescription'			=>	array
																											(
																												'Index'		=> 10
																											),
																			'ToDescription'				=>	array
																											(
																												'Index'		=> 11
																											),
																			'ClassCode'					=>	array
																											(
																												'Index'		=> 12
																											),
																			'CallType'					=>	array
																											(
																												'Index'		=> 13
																											),
																			'Price'						=>	array
																											(
																												'Index'		=> 14
																											),
																			'OtherInfo1'				=>	array
																											(
																												'Index'		=> 15
																											),
																			'OtherInfo2'				=>	array
																											(
																												'Index'		=> 16
																											)
																		)
												);
}
?>