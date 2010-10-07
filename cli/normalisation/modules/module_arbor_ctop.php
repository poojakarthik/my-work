<?php
/**
 * NormalisationModuleArborCTOP
 *
 * Normalisation module for Arbor CTOP Daily Usage Extract Files
 *
 * @class	NormalisationModuleArborCTOP
 */
class NormalisationModuleArborCTOP extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_AAPT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ARBOR_CTOP;
	
	const	UNIT_TYPE_SECONDS			= 100;
	const	UNIT_TYPE_TENTH_OF_A_SECOND	= 101;
	const	UNIT_TYPE_MINUTES			= 120;
	const	UNIT_TYPE_HOURS				= 130;
	const	UNIT_TYPE_BYTES				= 200;
	const	UNIT_TYPE_KILOBYTES			= 201;
	const	UNIT_TYPE_MEGABYTES			= 202;
	const	UNIT_TYPE_GIGABYTES			= 203;
	
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
		
		//--------------------------------------------------------------------//
		$this->_normalise();
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
		$this->_AppendCDR('CarrierRef', $this->_FetchRawCDR('CTOPRecordId'));
		
		// FNN
		$sFNN	= null;
		try
		{
			$iIdType	= (int)$this->_FetchRawCDR('IdValue');
			Flex::assert($iIdType === 1, "CTOP CDR File: Invalid Id Type '".self::_getIdTypeDescription($iIdType)."' encountered", print_r($this->DebugCDR(), true));
			
			$sFNN	= self::RemoveAusCode(trim($this->_FetchRawCDR('IdValue')));
			$this->_AppendCDR('FNN', $sFNN);
		}
		catch (Exception_Assertion $oException)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE);
		}
		
		// Source
		$this->_AppendCDR('Source', self::RemoveAusCode($this->_FetchRawCDR('Origin')));
		
		// Destination
		$this->_AppendCDR('Destination', self::RemoveAusCode($this->_FetchRawCDR('Target')));
		
		// Cost
		$this->_AppendCDR('Cost', $this->_FetchRawCDR('AmountCharged') / 100);	// Value is in cents
		
		// ServiceType
		$iServiceType	= self::_getServiceTypeForFNN($sFNN);
		$this->_AppendCDR('ServiceType', $iServiceType);
		
		// RecordType
		$sRecordCode	= $this->FindRecordCode(trim($this->_FetchRawCDR('UsageType')));
		$this->_AppendCDR('RecordCode', $sRecordCode);
		$iRecordType	= $this->FindRecordType($iServiceType, $sRecordCode);
		$this->_AppendCDR('RecordType', $iRecordType);
		
		// Destination
		$aDestination	= null;
		if ($this->_intContext)
		{
			$aDestination	= $this->FindDestination(trim($this->_FetchRawCDR('Jurisdiction')));
			$this->_AppendCDR('DestinationCode', $aDestination['Code']);
		}
		
		// Description
		$sDescription	= '';
		if ($aDestination)
		{
			if (!$aDestination['bolUnknownDestination'])
			{
				// Destination
				$sDescription	= $this->_FetchRawCDR('Destination', $aDestination['Description']);
			}
		}
		$this->_AppendCDR('Description', $sDescription);
		
		// Units
		$aUnitSets	= array();
		
		$aUnitSets[]	= array('iUnits'=>(int)$this->_FetchRawCDR('RawUnits'), 'iUnitType'=>(int)$this->_FetchRawCDR('UnitOfMeasureCode'));
		
		$aUnits	= $aUnitSets[0];	// FIXME: If there is more than one unit set, we should try to match with Record Type
		$this->_AppendCDR('Units',abs(self::_normaliseUnits($aUnits['iUnits'], $aUnits['iUnitType'])));
		
		// StartDatetime
		$sStartDatetime	= trim($this->_FetchRawCDR('TransactionDatetime'));
		$this->_AppendCDR('StartDatetime', $sStartDatetime);
		
		// EndDatetime
		$sEndDatetime	= null;
		try
		{
			switch ($aUnits['iUnitType'])
			{
				case self::UNIT_TYPE_TENTH_OF_A_SECOND:
				case self::UNIT_TYPE_SECONDS:
				case self::UNIT_TYPE_MINUTES:
				case self::UNIT_TYPE_HOURS:
					// Units is in seconds and represents the duration of the usage record
					$sEndDatetime	= date('Y-m-d H:i:s', strtotime("+{$aUnits['iUnits']} seconds", strtotime($sStartDatetime)));
					break;
				
				default:
					Flex::assert(false, "CTOP CDR File: Unhandled Unit Type '{$aUnits['iUnitType']}'", print_r($this->DebugCDR(), true));
					break;
			}
		}
		catch (Exception_Assertion $oException)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE);
		}
		$this->_AppendCDR('EndDatetime', $sEndDatetime);
		
		// Credit
		$this->_AppendCDR('Credit', (int)($aUnits['iUnits'] < 0));
		
		return;
	}
	
	static protected function _normaliseUnits($iUnits, $iUnitType)
	{
		switch ($iUnitType)
		{
			case self::UNIT_TYPE_SECONDS:
				$iUnits	= $iUnits;
				break;
			case self::UNIT_TYPE_TENTH_OF_A_SECOND:
				$iUnits	= ceil($iUnits / 10);
				break;
			case self::UNIT_TYPE_MINUTES:
				$iUnits	= ceil($iUnits * 60);
				break;
			case self::UNIT_TYPE_HOURS:
				$iUnits	= ceil($iUnits * 60 * 60);
				break;
			case self::UNIT_TYPE_BYTES:
				$iUnits	= ceil($iUnits / 1024);
				break;
			case self::UNIT_TYPE_KILOBYTES:
				$iUnits	= ceil($iUnits);
				break;
			case self::UNIT_TYPE_MEGABYTES:
				$iUnits	= ceil($iUnits * 1024);
				break;
			case self::UNIT_TYPE_GIGABYTES:
				$iUnits	= ceil($iUnits * 1024 * 1024);
				break;
			
			default:
				// TODO
				break;
		}
		return $iUnits;
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
																	'TransactionId'					=>	array
																										(
																											'Start'		=> 6,
																											'Length'	=> 20
																										),
																	'ProductId'						=>	array
																										(
																											'Start'		=> 26,
																											'Length'	=> 6
																										),
																	'UsageType'						=>	array
																										(
																											'Start'		=> 32,
																											'Length'	=> 6
																										),
																	'IdType'						=>	array
																										(
																											'Start'		=> 38,
																											'Length'	=> 6
																										),
																	'IdValue'						=>	array
																										(
																											'Start'		=> 44,
																											'Length'	=> 48
																										),
																	'TransactionDatetime'			=>	array
																										(
																											'Start'		=> 92,
																											'Length'	=> 14
																										),
																	'SecondaryDatetime'				=>	array
																										(
																											'Start'		=> 106,
																											'Length'	=> 14
																										),
																	'Target'						=>	array
																										(
																											'Start'		=> 120,
																											'Length'	=> 24
																										),
																	'Origin'						=>	array
																										(
																											'Start'		=> 144,
																											'Length'	=> 24
																										),
																	'RatedUnits'					=>	array
																										(
																											'Start'		=> 168,
																											'Length'	=> 10
																										),
																	'AmountCharged'					=>	array
																										(
																											'Start'		=> 178,
																											'Length'	=> 18
																										),
																	'Jurisdiction'					=>	array
																										(
																											'Start'		=> 196,
																											'Length'	=> 18
																										),
																	'FNN'							=>	array
																										(
																											'Start'		=> 214,
																											'Length'	=> 18
																										),
																	'ForeignAmount'					=>	array
																										(
																											'Start'		=> 232,
																											'Length'	=> 18
																										),
																	'Currency'						=>	array
																										(
																											'Start'		=> 250,
																											'Length'	=> 6
																										),
																	'Recipient'						=>	array
																										(
																											'Start'		=> 256,
																											'Length'	=> 10
																										),
																	'CompletionCode'				=>	array
																										(
																											'Start'		=> 266,
																											'Length'	=> 3
																										),
																	'CTOPRecordId'					=>	array
																										(
																											'Start'		=> 269,
																											'Length'	=> 22
																										),
																	'RawUnits'						=>	array
																										(
																											'Start'		=> 291,
																											'Length'	=> 10
																										),
																	'RawUnitsType'					=>	array
																										(
																											'Start'		=> 301,
																											'Length'	=> 6
																										),
																	'RatedUnitsType'				=>	array
																										(
																											'Start'		=> 307,
																											'Length'	=> 6
																										),
																	'BaseAmount'					=>	array
																										(
																											'Start'		=> 313,
																											'Length'	=> 18
																										),
																	'SecondUnits'					=>	array
																										(
																											'Start'		=> 331,
																											'Length'	=> 10
																										),
																	'ThirdUnits'					=>	array
																										(
																											'Start'		=> 341,
																											'Length'	=> 10
																										),
																	'SpecialPurposeField1'			=>	array
																										(
																											'Start'		=> 351,
																											'Length'	=> 24
																										),
																	'Unused'						=>	array
																										(
																											'Start'		=> 375,
																											'Length'	=> 24
																										)
																)
												);
												
	
	
	const	ID_TYPE_TELEPHONE_NUMBER					= 1;
	const	ID_TYPE_ADHOC_DESCRIPTION					= 2;
	const	ID_TYPE_TELSTRA_SP_ACCOUNT					= 4;
	const	ID_TYPE_OPTUS_SP_ACCOUNT					= 5;
	const	ID_TYPE_VODAFONE_SP_ACCOUNT					= 6;
	const	ID_TYPE_SERVICE_ADDRESS_A					= 6;
	const	ID_TYPE_BT_TELECONFERENCING_REFERENCE		= 6;
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