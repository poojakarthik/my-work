<?php
/**
 * NormalisationModuleArborCOCE
 *
 * Normalisation module for Arbor COCE Monthly Charges Extract File
 *
 * @class	NormalisationModuleArborCOCE
 */
class NormalisationModuleArborCOCE extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_AAPT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE;
	
	const	TYPE_CODE_RECURRING_CHARGE		= 2;
	const	TYPE_CODE_NON_RECURRING_CHARGE	= 3;
	const	TYPE_CODE_ADJUSTMENT			= 4;
	
	const	BILLING_LEVEL_ACCOUNT	= 0;
	const	BILLING_LEVEL_SERVICE	= 1;
	
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
		$this->_arrDefineCarrier	= self::$_arrRecordDefinitions['PWTDET'];
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
		switch (substr($arrCDR['CDR'], 0, 6))
		{
			case 'PWTDET':
				$this->_SplitRawCDR($arrCDR['CDR']);
				$this->_normalise();
				break;
				
			case 'PWTHDR':
			case 'PWTTRL':
			default:
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;
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
		// Only allow Service-level Charges
		if ($this->_FetchRawCDR('BillingLevel') != self::BILLING_LEVEL_SERVICE)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
		
		// CarrierRef
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('COCERecordId'));
		
		// FNN
		$sFNN	= null;
		try
		{
			$iIdType	= (int)$this->_FetchRawCDR('ExternalIdType');
			switch ($iIdType)
			{
				case self::ID_TYPE_TELEPHONE_NUMBER:
				case self::ID_TYPE_MOBILE_SERVICE_NUMBER:
					// Allowable Id Types
					break;
					
				default:
					Flex::assert(false, "COCE CDR File: Invalid Id Type '".self::_getIdTypeDescription($iIdType)."' encountered", print_r($this->DebugCDR(), true));
					break;
			}
			
			$sFNN	= self::RemoveAusCode(trim($this->_FetchRawCDR('ExternalId')));
			$this->_AppendCDR('FNN', $sFNN);
		}
		catch (Exception_Assertion $oException)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE);
		}
		
		// Source
		$this->_AppendCDR('Source', $sFNN);
		
		// Destination
		// No Destination
		
		// Cost
		$fCost	= $this->_FetchRawCDR('Amount') / 100;	// Value is in cents
		$this->_AppendCDR('Cost', abs($fCost));	// Value is in cents
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		// RecordType
		// Build a custom Record Code
		$sCarrierRecordCode			= trim($this->_FetchRawCDR('TypeCode'));
		$sToDate					= trim($this->_FetchRawCDR('ToDate'));
		$sElementId					= trim($this->_FetchRawCDR('ElementId'));
		$sNonRecurringChargeTypeId	= trim($this->_FetchRawCDR('NonRecurringChargeTypeId'));
		$sRecordCode				= null;
		$sCarrierDestinationCode	= null;
		try
		{
			switch ((int)$sCarrierRecordCode)
			{
				case self::TYPE_CODE_RECURRING_CHARGE:
					$sRecordCode				= 'S&E';
					$sCarrierDestinationCode	= "{$sCarrierRecordCode}:".trim($this->_FetchRawCDR('ElementId'));
					break;
					
				case self::TYPE_CODE_NON_RECURRING_CHARGE:
					//Flex::assert(false, "COCE CDR File: Encountered a non-Recurring Charge", print_r($this->DebugCDR(), true));
					$sRecordCode				= 'S&E';
					$sCarrierDestinationCode	= "{$sCarrierRecordCode}:".trim($this->_FetchRawCDR('NonRecurringChargeTypeId'));
					break;
					
				case self::TYPE_CODE_ADJUSTMENT:
					//Flex::assert(false, "COCE CDR File: Encountered an Adjustment", print_r($this->DebugCDR(), true));
					
					// We only want to accept certain Adjustments
					if ($sToDate && $sElementId)
					{
						// This is most likely a Recurring Charge "Disconnect Credit" -- Accept
						$sRecordCode				= 'S&E';
						$sCarrierDestinationCode	= self::TYPE_CODE_RECURRING_CHARGE.":{$sElementId}";
					}
					elseif ($sToDate && $sNonRecurringChargeTypeId)
					{
						// This is most likely a Non-Recurring Charge -- Accept
						$sRecordCode				= 'S&E';
						$sCarrierDestinationCode	= self::TYPE_CODE_NON_RECURRING_CHARGE.":{$sNonRecurringChargeTypeId}";
					}
					else
					{
						// Usage Credit, or some other kind of unwanted Adjustment
						return $this->_ErrorCDR(CDR_CANT_NORMALISE);
					}
					break;
			}
		}
		catch (Exception_Assertion $oException)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE);
		}
		
		$this->_AppendCDR('RecordCode', $sRecordCode);
		$iRecordType	= $this->FindRecordType($iServiceType, $sRecordCode);
		$this->_AppendCDR('RecordType', $iRecordType);
		
		// Destination
		$aDestination	= null;
		if ($this->_intContext)
		{
			$aDestination	= $this->FindDestination($sCarrierDestinationCode);
			$this->_AppendCDR('DestinationCode', $aDestination['Code']);
		}
		
		// StartDatetime
		$sFromDate	= trim($this->_FetchRawCDR('FromDate'));
		$sToDate	= trim($this->_FetchRawCDR('ToDate'));
		if ($sFromDate)
		{
			$iStartDatetime	= strtotime($sFromDate);
		}
		elseif ($sToDate)
		{
			// Only has a "To" Date -- use it as the StartDatetime
			$iStartDatetime	= strtotime($sToDate);
		}
		else
		{
			$iStartDatetime	= strtotime(trim($this->_FetchRawCDR('TransactionDatetime')));
		}
		$this->_AppendCDR('StartDatetime', date('Y-m-d H:i:s', $iStartDatetime));
		
		// EndDatetime
		if ($sToDate)
		{
			// We can received a To-Date without a From-Date, in this case, pretend there is not To-Date
			if ($sFromDate)
			{
				// If there is a From Date and To Date, then the To Date is not inclusive, so we need to take 1 second off to make it inclusive
				$iEndDatetime	= strtotime($sToDate) - 1;
				$this->_AppendCDR('EndDatetime', date('Y-m-d H:i:s', $iEndDatetime));
			}
		}
		
		// Description
		$sDescription	= trim($this->_FetchRawCDR('Description'));
		if ($iStartDatetime)
		{
			$sDescription	.= " ".date('d/m/Y', $iStartDatetime);
			
			if ($iEndDatetime)
			{
				$sDescription	.= " to ".date('d/m/Y', $iEndDatetime);
			}
		}
		$this->_AppendCDR('Description', $sDescription);
		
		// Units
		// FIXME: Are there any cases where this isn't true?
		$iUnits	= 1;
		$this->_AppendCDR('Units', $iUnits);
		
		// Credit
		$this->_AppendCDR('Credit', (int)($fCost < 0));
		
		return;
	}
	
	static protected function _getIdTypeDescription($iIdType)
	{
		return (isset(self::$_aIdTypes[(int)$iIdType])) ? self::$_aIdTypes[(int)$iIdType] : (string)$iIdType;
	}
	
	static private	$_arrRecordDefinitions	=	array
												(
													'PWTDET'	=>	array
																(
																	'RecordType'					=>	array
																										(
																											'Start'		=> 0,
																											'Length'	=> 6
																										),
																	'AccountNumber'					=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 10
																										),
																	'InvoiceNumber'					=>	array
																										(
																											'Start'		=> 16,
																											'Length'	=> 10
																										),
																	'StatementDate'					=>	array
																										(
																											'Start'		=> 26,
																											'Length'	=> 14
																										),
																	'TypeCode'						=>	array
																										(
																											'Start'		=> 40,
																											'Length'	=> 6
																										),
																	'BillingLevel'					=>	array
																										(
																											'Start'		=> 46,
																											'Length'	=> 6
																										),
																	'TransactionDatetime'			=>	array
																										(
																											'Start'		=> 52,
																											'Length'	=> 14
																										),
																	'NonRecurringChargeTypeId'		=>	array
																										(
																											'Start'		=> 66,
																											'Length'	=> 6
																										),
																	'ElementId'						=>	array
																										(
																											'Start'		=> 72,
																											'Length'	=> 6
																										),
																	'ExternalId'					=>	array
																										(
																											'Start'		=> 78,
																											'Length'	=> 48
																										),
																	'ExternalIdType'				=>	array
																										(
																											'Start'		=> 126,
																											'Length'	=> 6
																										),
																	'Amount'						=>	array
																										(
																											'Start'		=> 132,
																											'Length'	=> 18
																										),
																	'Currency'						=>	array
																										(
																											'Start'		=> 150,
																											'Length'	=> 6
																										),
																	'Tax1'							=>	array
																										(
																											'Start'		=> 156,
																											'Length'	=> 18
																										),
																	'Tax2'							=>	array
																										(
																											'Start'		=> 174,
																											'Length'	=> 18
																										),
																	'Description'					=>	array
																										(
																											'Start'		=> 192,
																											'Length'	=> 50
																										),
																	'AdjustmentCode'				=>	array
																										(
																											'Start'		=> 242,
																											'Length'	=> 6
																										),
																	'COCERecordId'					=>	array
																										(
																											'Start'		=> 248,
																											'Length'	=> 20
																										),
																	'FromDate'						=>	array
																										(
																											'Start'		=> 268,
																											'Length'	=> 14
																										),
																	'ToDate'						=>	array
																										(
																											'Start'		=> 282,
																											'Length'	=> 14
																										),
																	'RebillReferenceId'				=>	array
																										(
																											'Start'		=> 296,
																											'Length'	=> 16
																										),
																	'ProviderId'					=>	array
																										(
																											'Start'		=> 312,
																											'Length'	=> 6
																										),
																	'Unused'						=>	array
																										(
																											'Start'		=> 318,
																											'Length'	=> 82
																										)
																)
												);
												
	
	
	const	ID_TYPE_TELEPHONE_NUMBER					= 1;
	const	ID_TYPE_ADHOC_DESCRIPTION					= 2;
	const	ID_TYPE_TELSTRA_SP_ACCOUNT					= 4;
	const	ID_TYPE_OPTUS_SP_ACCOUNT					= 5;
	const	ID_TYPE_VODAFONE_SP_ACCOUNT					= 6;
	const	ID_TYPE_SERVICE_ADDRESS_A					= 9;
	const	ID_TYPE_BT_TELECONFERENCING_REFERENCE_ID	= 10;
	const	ID_TYPE_MOBILE_SERVICE_NUMBER				= 11;
	const	ID_TYPE_SIM_SERIAL_NUMBER					= 12;
	const	ID_TYPE_SIM_SERIAL_WEL_PACK_NUMBER			= 13;
	const	ID_TYPE_CUSTOMER_SERVICE_REF_ID				= 14;
	const	ID_TYPE_ROUTER_MODEL						= 15;
	const	ID_TYPE_SERVICE_ADDRESS_B					= 16;
	const	ID_TYPE_TERMINATION_NUMBER					= 18;
	const	ID_TYPE_PSD_REQUEST_ID						= 20;
	const	ID_TYPE_PREVIOUS_ACCOUNT_NUMBER				= 21;
	const	ID_TYPE_LNP_LOSING_CSP						= 22;
	const	ID_TYPE_LNP_DONOR_CSP						= 23;
	const	ID_TYPE_DIAL_PORTS_TELEPHONE_NO				= 31;
	const	ID_TYPE_AAPT_SERVICE_IDENTIFER				= 37;
	const	ID_TYPE_DIRECT_CONNECT_TRUNK				= 38;
	const	ID_TYPE_EXTENDED_ACCESS_ID_A				= 39;
	const	ID_TYPE_EA_REFERENCE_NUMBER					= 40;
	const	ID_TYPE_IMA_PSI_A							= 41;
	const	ID_TYPE_IMA_PSI_B							= 42;
	const	ID_TYPE_CTS_TRUNK_ID						= 50;
	const	ID_TYPE_DIALLER_ID							= 60;
	const	ID_TYPE_NUMBER_OF_DIALLERS					= 61;
	const	ID_TYPE_COLOCATE_RACK_ID					= 70;
	const	ID_TYPE_ISDN_PRA_TAIL_ID					= 71;
	const	ID_TYPE_NUMBER_OF_BULK_COPIES				= 100;
	const	ID_TYPE_SITE_IP_ADDRESS						= 106;
	const	ID_TYPE_INTERFACE_TYPE_A					= 108;
	const	ID_TYPE_INTERFACE_TYPE_B					= 109;
	const	ID_TYPE_ACCESS_A							= 110;
	const	ID_TYPE_ACCESS_B							= 111;
	const	ID_TYPE_IP_ADDRESS_A1						= 112;
	const	ID_TYPE_SUBNET_ADDRESS_A1					= 113;
	const	ID_TYPE_COS_GOLD							= 114;
	const	ID_TYPE_COS_SILVER							= 115;
	const	ID_TYPE_COS_BRONZE							= 116;
	const	ID_TYPE_COS									= 119;
	const	ID_TYPE_FR_DCLI_NUMBER_A					= 510;
	const	ID_TYPE_FR_DCLI_NUMBER_B					= 511;
	const	ID_TYPE_ATM_VPI_NUMBER_A					= 520;
	const	ID_TYPE_ATM_VPI_NUMBER_B					= 521;
	const	ID_TYPE_ATM_VCI_NUMBER_A					= 522;
	const	ID_TYPE_ATM_VCI_NUMBER_B					= 523;
	const	ID_TYPE_DVS_CIRCUIT_QUANTITY				= 525;
	const	ID_TYPE_DVS_LISTED_DIRECTORY_NUMBER			= 526;
	
	static protected	$_aIdTypes	=	array
										(
											self::ID_TYPE_TELEPHONE_NUMBER						=> 'Telephone Number',
											self::ID_TYPE_ADHOC_DESCRIPTION						=> 'Ad hoc Description',
											self::ID_TYPE_TELSTRA_SP_ACCOUNT					=> 'Telstra SP Account Identifier',
											self::ID_TYPE_OPTUS_SP_ACCOUNT						=> 'Optus SP Account Identifier',
											self::ID_TYPE_VODAFONE_SP_ACCOUNT					=> 'Vodadone SP Account Identifier',
											self::ID_TYPE_SERVICE_ADDRESS_A						=> 'Service Address A',
											self::ID_TYPE_BT_TELECONFERENCING_REFERENCE_ID		=> 'BT Teleconferencing Reference Id',
											self::ID_TYPE_MOBILE_SERVICE_NUMBER					=> 'Mobile Service Number',
											self::ID_TYPE_SIM_SERIAL_NUMBER						=> 'SIM Serial Number',
											self::ID_TYPE_SIM_SERIAL_WEL_PACK_NUMBER			=> 'SIM Serial/Wel Pack Number',
											self::ID_TYPE_CUSTOMER_SERVICE_REF_ID				=> 'Customer Service Ref Id',
											self::ID_TYPE_ROUTER_MODEL							=> 'Router Model',
											self::ID_TYPE_SERVICE_ADDRESS_B						=> 'Service Address B',
											self::ID_TYPE_TERMINATION_NUMBER					=> 'Termination Number',
											self::ID_TYPE_PSD_REQUEST_ID						=> 'PSD Request ID',
											self::ID_TYPE_PREVIOUS_ACCOUNT_NUMBER				=> 'Previous Account Number',
											self::ID_TYPE_LNP_LOSING_CSP						=> 'LNP - Losing CSP',
											self::ID_TYPE_LNP_DONOR_CSP							=> 'LNP - Donor CSP',
											self::ID_TYPE_DIAL_PORTS_TELEPHONE_NO				=> 'Dial Ports Telephone No',
											self::ID_TYPE_AAPT_SERVICE_IDENTIFER				=> 'AAPT Service Identifer',
											self::ID_TYPE_DIRECT_CONNECT_TRUNK					=> 'Direct Connect Trunk',
											self::ID_TYPE_EXTENDED_ACCESS_ID_A					=> 'Extended Access ID A',
											self::ID_TYPE_EA_REFERENCE_NUMBER					=> 'EA Reference Number',
											self::ID_TYPE_IMA_PSI_A								=> 'IMA PSI A',
											self::ID_TYPE_IMA_PSI_B								=> 'IMA PSI B',
											self::ID_TYPE_CTS_TRUNK_ID							=> 'CTS Trunk ID',
											self::ID_TYPE_DIALLER_ID							=> 'Dialler ID',
											self::ID_TYPE_NUMBER_OF_DIALLERS					=> 'Number of Diallers',
											self::ID_TYPE_COLOCATE_RACK_ID						=> 'Co-Locate Rack Id',
											self::ID_TYPE_ISDN_PRA_TAIL_ID						=> 'ISDN PRA Tail Id',
											self::ID_TYPE_NUMBER_OF_BULK_COPIES					=> 'Number of Bulk Copies',
											self::ID_TYPE_SITE_IP_ADDRESS						=> 'Site IP Address',
											self::ID_TYPE_INTERFACE_TYPE_A						=> 'Interface Type A',
											self::ID_TYPE_INTERFACE_TYPE_B						=> 'Interface Type B',
											self::ID_TYPE_ACCESS_A								=> 'Access A',
											self::ID_TYPE_ACCESS_B								=> 'Access B',
											self::ID_TYPE_IP_ADDRESS_A1							=> 'IP Address A1',
											self::ID_TYPE_SUBNET_ADDRESS_A1						=> 'Subnet Address A1',
											self::ID_TYPE_COS_GOLD								=> 'CoS - Gold',
											self::ID_TYPE_COS_SILVER							=> 'CoS - Silver',
											self::ID_TYPE_COS_BRONZE							=> 'CoS - Bronze',
											self::ID_TYPE_COS									=> 'CoS',
											self::ID_TYPE_FR_DCLI_NUMBER_A						=> 'FR - DCLI Number A',
											self::ID_TYPE_FR_DCLI_NUMBER_B						=> 'FR - DCLI Number B',
											self::ID_TYPE_ATM_VPI_NUMBER_A						=> 'ATM - VPI Number A',
											self::ID_TYPE_ATM_VPI_NUMBER_B						=> 'ATM - VPI Number B',
											self::ID_TYPE_ATM_VCI_NUMBER_A						=> 'ATM - VCI Number A',
											self::ID_TYPE_ATM_VCI_NUMBER_B						=> 'ATM - VCI Number B',
											self::ID_TYPE_DVS_CIRCUIT_QUANTITY					=> 'DVS - Circuit Quantity',
											self::ID_TYPE_DVS_LISTED_DIRECTORY_NUMBER			=> 'DVS - Listed Directory Number'
										);
}
?>