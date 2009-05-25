<?php
/**
 * NormalisationModuleIseekData
 *
 * Normalisation module for iSeek Data batch files
 * 
 * @class	NormalisationModuleIseekData
 */
class NormalisationModuleIseekData extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_ISEEK;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA;
	
	const	OCTETS_TO_KILOBYTES_MULTIPLIER	= 1024;
	const	USAGE_WINDOW_SECONDS			= 300;
	
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
		$this->_strDelimiter = " ";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine['username']				['Index']		= 0;	// FNN/MSN of the Modem SIM 
		$arrDefine['ip']					['Index']		= 1;	// IP Address
		$arrDefine['qos']					['Index']		= 2;	// Quality of Service (ignore)
		$arrDefine['uptxoctets']			['Index']		= 3;	// Uploaded Data in Octects/Bytes
		$arrDefine['downrxoctets']			['Index']		= 4;	// Downloaded Data in Octects/Bytes
		$arrDefine['seconds']				['Index']		= 5;	// Session Length in Seconds
		
		$this->_arrDefineCarrier = $arrDefine;
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
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR['CDR']);
		
		// ignore header/footer rows
		$strUsername	= $this->_FetchRawCDR('username');
		switch ($strUsername)
		{
			case '#':
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
				break;
		}
		
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		try
		{
			$arrUsername	= array();
			if (Flex::assert(preg_match('/^0[123478]\d{8}/', $strUsername, $arrUsername), "iSeek Data Normalisation: Invalid Username '{$strUsername}'", print_r($this->_arrRawData, true)))
			{
				$strRawFNN		= $arrUsername[0];
				$intFNNAreaCode	= (int)$arrUsername[1];
				
				if ($intFNNAreaCode === 4)
				{
					// Mobile -- Wireless Broadband
					$this->_AppendCDR('FNN', $strUsername);
					
					// Apply Ownership
					$this->ApplyOwnership();
					
					// ServiceType
					$this->_AppendCDR('ServiceType', SERVICE_TYPE_MOBILE);
					
					// Record Type
					$this->_AppendCDR('RecordType', $this->FindRecordType(SERVICE_TYPE_MOBILE, '3G'));
				}
				else
				{
					// ADSL -- Append FNN with an 'i'
					$this->_AppendCDR('FNN', $strUsername.'i');
					if ($this->ApplyOwnership())
					{
						// ServiceType
						$this->_AppendCDR('ServiceType', SERVICE_TYPE_ADSL);
						
						// Record Type
						$this->_AppendCDR('RecordType', $this->FindRecordType(SERVICE_TYPE_ADSL, 'MonthlyUsage'));
					}
				}
			}
		}
		catch (Exception_Assertion $eException)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
		
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_GenerateUID());
		
		// Cost (Don't get a Cost, so hack it in)
		$this->_AppendCDR('Cost', 0.0);
		
		$intFileTimestamp	= strtotime($arrCDR['FileName']);
		
		// StartDatetime
		$strStartDatetime	= date("Y-m-d H:i:s", $intFileTimestamp - self::USAGE_WINDOW_SECONDS);
		$this->_AppendCDR('StartDatetime', $strStartDatetime);
		
		// EndDatetime
		$intDuration	= (int)$this->_FetchRawCDR('seconds');
		$strEndDatetime	= date("Y-m-d H:i:s", $intFileTimestamp - self::USAGE_WINDOW_SECONDS + $intDuration);
		$this->_AppendCDR('EndDatetime', $strEndDatetime);
		
		// Units
		// FIXME: This won't be what we want when we actually start supporting normal ADSL
		$intUploadedOctets		= (int)$this->_FetchRawCDR('uptxoctets');
		$intDownloadedOctets	= (int)$this->_FetchRawCDR('downrxoctets');
		$intTotalUnits			= ($intUploadedOctets + $intDownloadedOctets) * self::OCTETS_TO_KILOBYTES_MULTIPLIER;
		$this->_AppendCDR('Units', $intTotalUnits);
		
		//--------------------------------------------------------------------//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	private static function convertTime($strNativeTimestamp)
	{
		return date("Y-m-d H:i:s", strtotime($strNativeTimestamp));
	}
}
?>