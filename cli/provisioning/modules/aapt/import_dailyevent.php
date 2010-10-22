<?php
/**
 * ImportAAPTDailyEvent
 *
 * Parses a Unitel Preselection Response file
 *
 * @class		ImportAAPTDailyEvent
 */
 class ImportAAPTDailyEvent extends ImportBase
 {
	public $intBaseCarrier		= CARRIER_UNITEL;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT;
	
	// Real Record Types
	const	RECORD_TYPE_HEADER	= 'H';
	const	RECORD_TYPE_TRAILER	= 'T';
	const	RECORD_TYPE_DETAIL	= 'D';
	
	// Psuedo Record Types to allow a single record to produce 3 Responses
	const	RECORD_TYPE_DETAIL_FULLSERVICEREBILL	= 'F';
	const	RECORD_TYPE_DETAIL_PRESELECTION			= 'P';
	const	RECORD_TYPE_DETAIL_WHITELISTING			= 'W';	// Not understood by Flex
	
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * @param	integer	$intCarrier				The Carrier using this Module
	 *
	 * @return	ImportBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		// Parent Constructor
 		parent::__construct($intCarrier);
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
 		
 		// Field Delimiter
 		$this->_strDelimiter	= ",";
 		$this->_strEndOfLine	= "\n";
 		
		$arrDefine['FNN']			['Index']		= 0;
 		
		$arrDefine['Type']			['Index']		= 1;
 		
		$arrDefine['Result']		['Index']		= 2;
 		
		$arrDefine['Description']	['Index']		= 3;
		
		$this->_arrDefine	=	array
								(
									'RowCode'			=>	array
															(
																'Index'	=>	0
															),
									'BatchNo'			=>	array
															(
																'Index'	=>	1
															),
									'IDNo'				=>	array
															(
																'Index'	=>	2
															),
									'BillingAccountNo'	=>	array
															(
																'Index'	=>	3
															),
									'ServiceNumber'		=>	array
															(
																'Index'	=>	4
															),
									'IsSpectrum'		=>	array
															(
																'Index'	=>	5
															),
									'MCPCode'			=>	array
															(
																'Index'	=>	6
															),
									'MCPDate'			=>	array
															(
																'Index'	=>	7
															),
									'RejectCode'		=>	array
															(
																'Index'	=>	8
															),
									'LossCode'			=>	array
															(
																'Index'	=>	9
															),
									'LossPSD'			=>	array
															(
																'Index'	=>	10
															),
									'NewServiceNumber'	=>	array
															(
																'Index'	=>	11
															),
									'WhitelistCode'		=>	array
															(
																'Index'	=>	12
															),
									'WhitelistDate'		=>	array
															(
																'Index'	=>	13
															),
									'WhitelistRefCode'	=>	array
															(
																'Index'	=>	14
															),
									'Comment'			=>	array
															(
																'Index'	=>	15
															),
									'eBillCode'			=>	array
															(
																'Index'	=>	16
															),
									'eBillCodeValue'	=>	array
															(
																'Index'	=>	17
															),
									'eBillDate'			=>	array
															(
																'Index'	=>	18
															)
								);
 	}
 	
	/**
	 * PreProcess()
	 *
	 * Pre-processes a file
	 *
	 * @param	array	$aRawData		File Data to parse
	 *
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function PreProcess($aRawData)
 	{
 		// Preprocess to break lines into separate Full Service Rebill, Preselection, and Whitelist components
 		
 		$aRawResponses	= array();
 		foreach ($aRawData as $sLine)
 		{
 			if ($sLine[0] === 'D')
 			{
	 			// FIXME: We're just going to be cheap, and define 3 new Record Types, and just put all data in each
	 			$aRawResponses[]	= 'F'.substr($sLine, 1);
	 			$aRawResponses[]	= 'P'.substr($sLine, 1);
	 			//$aRawResponses[]	= 'W'.substr($sLine, 1);	// We don't care about Whitelisting just yet, as Flex doesn't support the concept
 			}
 		}
 		
 		return $aRawResponses;
 	}
 	
	/**
	 * Normalise()
	 *
	 * Normalises a line from a Provisioning File
	 *
	 * @param	string	$strLine		Line to parse
	 *
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function Normalise($strLine, $intLineNumber)
 	{
 		try
 		{
	 		$arrPDR	= array();
	 		
	 		// Check for non-Detail records
	 		//if (stripos($strLine, 'D') !== 0)
	 		if (!in_array($strLine[0], array(self::RECORD_TYPE_DETAIL_FULLSERVICEREBILL, self::RECORD_TYPE_DETAIL_PRESELECTION)))
	 		{
				$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
				return $arrPDR;
	 		}
	 		
	 		// Split the Line using the file definition
	 		$arrData = $this->_SplitLine($strLine);
	 		
			//----------------------------------------------------------------//
			// FNN
			$arrPDR['FNN']	= $arrData['ServiceNumber'];
			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// CarrierRef
			$arrPDR['CarrierRef'] = $intLineNumber;
			//----------------------------------------------------------------//
			
			if ($arrData['RowCode'] === self::RECORD_TYPE_DETAIL_FULLSERVICEREBILL)
			{
				//----------------------------------------------------------------//
				// Type
				//----------------------------------------------------------------//
				// Default is Full Service
				$arrPDR['Type']	= PROVISIONING_TYPE_FULL_SERVICE;
				
				$iEbillCode			= (trim($arrData['eBillCode'])) ? (int)$arrData['eBillCode'] : null;
				$iEbillCodeValue	= (trim($arrData['eBillCodeValue'])) ? (int)$arrData['eBillCodeValue'] : null;
				if ($iEbillCode !== null)
				{
					switch ($iEbillCode)
					{
						case self::EBILL_CODE_COMPLETION_RECORD:
							// Request Completion
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= self::$_aEbillCompletionCodes[$iEbillCodeValue];
							break;
							
						case self::EBILL_CODE_REJECTION_RECORD:
							// Request Rejection
							$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
							$arrPDR['Description']		= self::$_aEBillRejectCodes[$iEbillCodeValue];
							
							// Rejection records can represent different Provisioning Types
							switch ($iEbillCodeValue)
							{
								case self::EBILL_REJECT_CODE_REVERSAL_NO_RECORD_OF_WNO_IN_NOMINATED_FILE:
								case self::EBILL_REJECT_CODE_REVERSAL_ORIGINAL_ALREADY_REJECTED:
								case self::EBILL_REJECT_CODE_REVERSAL_OUTSIDE_VALID_PERIOD:
								case self::EBILL_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_TRANSFER:
									$arrPDR['Type']	= PROVISIONING_TYPE_FULL_SERVICE_REVERSE;
									break;
							}
							break;
						
						case self::EBILL_CODE_LOSS_RECORD:
							// Loss
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= self::$_aEBillLossCodes[$iEbillCodeValue];
							
							// Churn vs Disconnection
							switch ($iEbillCodeValue)
							{
								case self::EBILL_LOSS_CODE_PRODUCT_SERVICE_CANCELLED:
									$arrPDR['Type']	= PROVISIONING_TYPE_DISCONNECT_FULL;
									break;
								
								default:
									$arrPDR['Type']	= PROVISIONING_TYPE_LOSS_FULL;
									break;
							}
							break;
							
						case self::EBILL_CODE_AAPT_REJECTION:
							// Rejected by AAPT's RPG Platform
							$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						case self::EBILL_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD:
							// Loss (Churn)
							$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_FULL;
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						case self::EBILL_CODE_REVERSAL_REQUESTS:
							// Reversal Success (treat it as a loss for now)
							$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_FULL;
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						default:
							Flex::assert(false, "AAPT Daily Event File: Unhandled eBill Code '{$iEbillCode}' (".self::$_aEBillCodes[$iEbillCode].")", print_r(array('PDR'=>$arrPDR,'Raw'=>$arrData), true));
							break;
					}
					
					// Effective Date
					$arrPDR['EffectiveDate']	= self::_convertDate_dMy($arrData['eBillDate']);
				}
				else
				{
					// No eBill Data
					$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
					return $arrPDR;
				}
			}
			elseif ($arrData['RowCode'] === self::RECORD_TYPE_DETAIL_PRESELECTION)
			{
				//----------------------------------------------------------------//
				// Type
				//----------------------------------------------------------------//
				// Default is Preselection
				$arrPDR['Type']	= PROVISIONING_TYPE_PRESELECTION;
				
				$iMCPCode		= (trim($arrData['MCPCode'])) ? (int)$arrData['MCPCode'] : null;
				$iMCPRejectCode	= (trim($arrData['RejectCode'])) ? (int)$arrData['RejectCode'] : null;
				$iMCPLossCode	= (trim($arrData['LossCode'])) ? (int)$arrData['LossCode'] : null;
				$iMCPLossPSD	= (trim($arrData['LossPSD'])) ? (int)$arrData['LossPSD'] : null;
				
				if ($iMCPCode !== null)
				{
					switch ($iMCPCode)
					{
						case self::MCP_CODE_COMPLETION_RECORD:
							// Request Completion
							// NOTE: MCP Completion Codes are incomplete, and are added to by guessing as unique codes come in
							// Additionally, it uses the RejectCode field
							$iMCPCompletionCode	= ($iMCPRejectCode === null) ? self::MCP_COMPLETION_CODE_TRANSFER : $iMCPRejectCode;
							
							Flex::assert(isset(self::$_aMCPCompletionCodes[$iMCPCompletionCode]), "AAPT Daily Event File: Unhandled MCP Completion Code '{$iMCPCompletionCode}' (".self::$_aMCPCompletionCodes[$iMCPCompletionCode].")", print_r(array('PDR'=>$arrPDR,'Raw'=>$arrData), true));
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= self::$_aMCPCompletionCodes[$iMCPCompletionCode];
							break;
							
						case self::MCP_CODE_REJECTION_RECORD:
							// Request Rejection
							$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
							$arrPDR['Description']		= self::$_aMCPRejectCodes[$iMCPRejectCode];
							
							// Rejection records can represent different Provisioning Types
							switch ($iMCPRejectCode)
							{
								case self::MCP_REJECT_CODE_REVERSAL_ERROR_NO_RECORD_OF_CNO_IN_NOMINATED_FILE:
								case self::MCP_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_PRESELECTION:
									$arrPDR['Type']	= PROVISIONING_TYPE_FULL_SERVICE_REVERSE;
									break;
							}
							break;
							
						case self::MCP_CODE_LOSS_RECORD:
							// Loss
							// TODO: List which PSD the Service was lost to
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= self::$_aMCPLossCodes[$iMCPLossCode];
							
							// Churn vs Disconnection
							switch ($iMCPLossCode)
							{
								case self::MCP_LOSS_CODE_PRODUCT_SERVICE_CANCELLED:
									$arrPDR['Type']	= PROVISIONING_TYPE_DISCONNECT_PRESELECT;
									break;
								
								default:
									$arrPDR['Type']	= PROVISIONING_TYPE_LOSS_PRESELECT;
									break;
							}
							break;
							
						case self::MCP_CODE_AAPT_REJECTION:
							// Rejected by AAPT's RPG Platform
							$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						case self::MCP_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD:
							// Loss (Churn)
							$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_PRESELECT;
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						case self::MCP_CODE_REVERSAL_REQUESTS:
							// Loss (Churn)
							$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_PRESELECT;
							$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
							$arrPDR['Description']		= trim($arrData['Comment']);
							break;
							
						default:
							Flex::assert(false, "AAPT Daily Event File: Unhandled MCP Code '{$iMCPCode}' (".self::$_aMCPCodes[$iMCPCode].")", print_r(array('PDR'=>$arrPDR,'Raw'=>$arrData), true));
							break;
					}
					
					$arrPDR['EffectiveDate']	= self::_convertDate_dMy($arrData['MCPDate']);
				}
				else
				{
					// No MCP Data
					$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
					return $arrPDR;
				}
			}
			else
			{
				Flex::assert(false, "AAPT Daily Event File: Unhandled Row Code '{$arrData['RowCode']}'", print_r(array('PDR'=>$arrPDR,'Raw'=>$arrData), true));
			}
	 		
			//----------------------------------------------------------------//
			// Description
			// Handled Elsewhere
			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// EffectiveDate
			// Handled Elsewhere
			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// Request Status
			// Handled Elsewhere
			//----------------------------------------------------------------//
	 		
			//----------------------------------------------------------------//
			// Find Owner
			$arrPDR	= $this->FindFNNOwner($arrPDR);
			//----------------------------------------------------------------//
	 		
	 		if (!$arrPDR['Type'])
	 		{
	 			Debug($arrData);
	 		}
	 		
	 		return $arrPDR;
 		}
 		catch (Exception_Assertion $oException)
 		{
 			$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
			return $arrPDR;
 		}
 	}
 	
 	static private function _convertDate_dMy($sDate)
 	{
 		$aTokens	= preg_split('/[\-\/]/', strtoupper($sDate));
 		
 		$sDay	= str_pad((int)$aTokens[0], 2, '0', STR_PAD_LEFT);
 		$sMonth	= $aTokens[1];
 		$iYear	= $aTokens[2];
 		
 		// strtotime() parsing rule (using example 2 "22DEC78"):
 		// Source: http://au.php.net/manual/en/datetime.formats.date.php
 		// Day, textual month and year:
 		//		dd ([ \t.-])* m ([ \t.-])* y
		//		(eg. "30-June 2008", "22DEC78", "14 III 1879")
 		return date('Y-m-d H:i:s', strtotime("{$sDay}{$sMonth}{$iYear}"));
 	}
 	
	/**
	 * LinkToRequest()
	 *
	 * Attempts to link a Response to a Request
	 *
	 * @param	array	$arrResponse	Response to match against
	 *
	 * @return	integer					Request Id
	 *
	 * @method
	 */
	function LinkToRequest($arrResponse)
	{
		// Run the default matcher
		return parent::LinkToRequest($arrResponse);
	}
	
	//------------------------------------------------------------------------//
	// eBill Codes
	//------------------------------------------------------------------------//
	const	EBILL_CODE_CHURN_NOTIFICATION_RECORD						= 10;
	const	EBILL_CODE_COMPLETION_RECORD								= 15;
	const	EBILL_CODE_REJECTION_RECORD									= 20;
	const	EBILL_CODE_LOSS_RECORD										= 25;
	const	EBILL_CODE_REVERSAL_REQUESTS								= 50;
	const	EBILL_CODE_PRESELECTION_QUERY_RECORD						= 80;
	const	EBILL_CODE_PRESELECTION_QUERY_REJECT_AND_COMPLETION_RECORD	= 85;
	const	EBILL_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD				= 90;
	const	EBILL_CODE_AAPT_REJECTION									= 99;
	
	protected static	$_aEBillCodes	= array(
		self::EBILL_CODE_CHURN_NOTIFICATION_RECORD							=> 'Churn Notification Record',
		self::EBILL_CODE_COMPLETION_RECORD									=> 'Completion Record',
		self::EBILL_CODE_REJECTION_RECORD									=> 'Rejection Record',
		self::EBILL_CODE_LOSS_RECORD										=> 'Loss Record',
		self::EBILL_CODE_REVERSAL_REQUESTS									=> 'Reversal Requests',
		self::EBILL_CODE_PRESELECTION_QUERY_RECORD							=> 'Pre-selection Query Record',
		self::EBILL_CODE_PRESELECTION_QUERY_REJECT_AND_COMPLETION_RECORD	=> 'Pre-selection Query Reject & Completion Record',
		self::EBILL_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD				=> 'AAPT Loss – Service lost to other PSD',
		self::EBILL_CODE_AAPT_REJECTION										=> 'AAPT Rejection'	// Refer comments field in the Daily Event File for a description
	);
	
	//------------------------------------------------------------------------//
	// eBill Completion Codes
	//------------------------------------------------------------------------//
	const	EBILL_COMPLETION_CODE_TRANSFER								= 1;
	const	EBILL_COMPLETION_CODE_NEW									= 2;
	const	EBILL_COMPLETION_CODE_CHANGE_OF_ADDRESS_OR_PRODUCT			= 3;
	const	EBILL_COMPLETION_CODE_CHANGE_OF_NUMBER						= 4;
	const	EBILL_COMPLETION_CODE_MOVE_NEW_NUMBER						= 6;
	const	EBILL_COMPLETION_CODE_REVERSAL								= 8;
	const	EBILL_COMPLETION_CODE_MIGRATION								= 12;
	const	EBILL_COMPLETION_CODE_NONEBILL_TO_EBILL_TRANSFER			= 13;
	const	EBILL_COMPLETION_CODE_DEFAULT_GAIN_DUE_TO_TRANSFER_REQUEST	= 14;
	const	EBILL_COMPLETION_CODE_REVERSAL_GAIN_FROM_COL_TRANSFER		= 15;
	
	protected static	$_aEbillCompletionCodes	= array(
		self::EBILL_COMPLETION_CODE_TRANSFER								=> 'Transfer',
		self::EBILL_COMPLETION_CODE_NEW										=> 'New',
		self::EBILL_COMPLETION_CODE_CHANGE_OF_ADDRESS_OR_PRODUCT			=> 'Change of Address or Product',
		self::EBILL_COMPLETION_CODE_CHANGE_OF_NUMBER						=> 'Change of Number',
		self::EBILL_COMPLETION_CODE_MOVE_NEW_NUMBER							=> 'Move - New Number',
		self::EBILL_COMPLETION_CODE_REVERSAL								=> 'Reversal',
		self::EBILL_COMPLETION_CODE_MIGRATION								=> 'Migration',
		self::EBILL_COMPLETION_CODE_NONEBILL_TO_EBILL_TRANSFER				=> 'non-eBill to eBill Transfer',
		self::EBILL_COMPLETION_CODE_DEFAULT_GAIN_DUE_TO_TRANSFER_REQUEST	=> 'Default gain due to Transfer Request (for Wholesale Redirection Group Codes 008 & 010 only)',
		self::EBILL_COMPLETION_CODE_REVERSAL_GAIN_FROM_COL_TRANSFER			=> 'Reversal gain from COL transfer (gain record for a service that has been reversed back to eBill after a COL transfer)'
	);
	
	//------------------------------------------------------------------------//
	// MCP Completion Codes (INCOMPLETE!)
	//------------------------------------------------------------------------//
	const	MCP_COMPLETION_CODE_TRANSFER								= 1;
	
	protected static	$_aMCPCompletionCodes	= array(
		self::MCP_COMPLETION_CODE_TRANSFER	=> 'Transfer'
	);
	
	//------------------------------------------------------------------------//
	// Whitelist Codes
	//------------------------------------------------------------------------//
	const	WHITELIST_CODE_SUCCESSFUL	= 10;
	const	WHITELIST_CODE_REJECT		= 20;
	
	protected static	$_aWhitelistCodes	= array(
		self::WHITELIST_CODE_SUCCESSFUL	=> 'White list successful',
		self::WHITELIST_CODE_REJECT		=> 'White list reject'
	);
	
	const	MCP_CODE_CHURN_NOTIFICATION_RECORD							= 10;
	const	MCP_CODE_COMPLETION_RECORD									= 15;
	const	MCP_CODE_REJECTION_RECORD									= 20;
	const	MCP_CODE_LOSS_RECORD										= 25;
	const	MCP_CODE_REVERSAL_REQUESTS									= 50;
	const	MCP_CODE_PRESELECTION_QUERY_RECORD							= 80;
	const	MCP_CODE_PRESELECTION_QUERY_REJECT_AND_COMPLETION_RECORD	= 85;
	const	MCP_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD				= 90;
	const	MCP_CODE_AAPT_REJECTION										= 99;
	
	protected static	$_aMCPCodes	= array(
		self::MCP_CODE_CHURN_NOTIFICATION_RECORD						=> 'Churn Notification Record',
		self::MCP_CODE_COMPLETION_RECORD								=> 'Completion Record',
		self::MCP_CODE_REJECTION_RECORD									=> 'Rejection Record',
		self::MCP_CODE_LOSS_RECORD										=> 'Loss Record',
		self::MCP_CODE_REVERSAL_REQUESTS								=> 'Reversal Requests',
		self::MCP_CODE_PRESELECTION_QUERY_RECORD						=> 'Pre-selection Query Record',
		self::MCP_CODE_PRESELECTION_QUERY_REJECT_AND_COMPLETION_RECORD	=> 'Pre-selection Query Reject & Completion Record',
		self::MCP_CODE_AAPT_LOSS_SERVICE_LOST_TO_OTHER_PSD				=> 'AAPT Loss – Service lost to other PSD',
		self::MCP_CODE_AAPT_REJECTION									=> 'AAPT Rejection'	// Refer comments field in the Daily Event File for a description
	);
	
	const	EBILL_LOSS_CODE_TRANSFER_TO_OTHER_SP							= 1;
	const	EBILL_LOSS_CODE_PRODUCT_SERVICE_CANCELLED						= 2;
	const	EBILL_LOSS_CODE_REVERSAL										= 4;
	const	EBILL_LOSS_CODE_PORTED_TO_ANOTHER_ASD							= 5;
	const	EBILL_LOSS_CODE_SERVICE_LOST_DUE_TO_COMMERCIAL_CHURN_TRANSFER	= 6;
	const	EBILL_LOSS_CODE_DEFAULT_LOSS_DUE_TO_CHURN_REQUEST				= 7;
	const	EBILL_LOSS_CODE_DEFAULT_LOSS_DUE_TO_REVERSAL_REQUEST			= 8;
	
	protected static	$_aEBillLossCodes	= array(
		self::EBILL_LOSS_CODE_TRANSFER_TO_OTHER_SP							=> 'Transfer to other SP',
		self::EBILL_LOSS_CODE_PRODUCT_SERVICE_CANCELLED						=> 'Product/Service Cancelled',
		self::EBILL_LOSS_CODE_REVERSAL										=> 'Reversal',
		self::EBILL_LOSS_CODE_PORTED_TO_ANOTHER_ASD							=> 'Ported to another ASD',
		self::EBILL_LOSS_CODE_SERVICE_LOST_DUE_TO_COMMERCIAL_CHURN_TRANSFER	=> 'Service lost due to commercial churn transfer',
		self::EBILL_LOSS_CODE_DEFAULT_LOSS_DUE_TO_CHURN_REQUEST				=> 'Default loss due to churn request',
		self::EBILL_LOSS_CODE_DEFAULT_LOSS_DUE_TO_REVERSAL_REQUEST			=> 'Default loss due to reversal request'
	);
	
	const	MCP_LOSS_CODE_TRANSFER_TO_OTHER_PSD							= 1;
	const	MCP_LOSS_CODE_PRODUCT_SERVICE_CANCELLED						= 2;
	const	MCP_LOSS_CODE_NO_POINT_OF_PRESENCE							= 3;
	const	MCP_LOSS_CODE_REVERSAL										= 4;
	
	protected static	$_aMCPLossCodes	= array(
		self::MCP_LOSS_CODE_TRANSFER_TO_OTHER_PSD		=> 'Transfer to other PSD',
		self::MCP_LOSS_CODE_PRODUCT_SERVICE_CANCELLED	=> 'Product/Service Cancelled',
		self::MCP_LOSS_CODE_NO_POINT_OF_PRESENCE		=> 'Provisioning Failed due to lack of POP (Point of Presence)',
		self::MCP_LOSS_CODE_REVERSAL					=> 'Reversal (incorrect claim by PSD)'
	);
	
	const	MCP_REJECT_CODE_SERVICE_NUMBER_NOT_FOUND							= 1;
	const	MCP_REJECT_CODE_SERVICE_NUMBER_IS_ON_DIVERSION						= 2;
	const	MCP_REJECT_CODE_INACTIVE_SERVICE									= 3;
	const	MCP_REJECT_CODE_DISCONNECTED_SERVICE								= 4;
	const	MCP_REJECT_CODE_SERVICE_NUMBER_FOUND_BUT_IS_NOT_PRESELECTABLE		= 5;
	const	MCP_REJECT_CODE_ENHANCED_SERVICE_OTHER								= 6;
	const	MCP_REJECT_CODE_REAL_TIME_METERING_FOUND							= 7;
	const	MCP_REJECT_CODE_SPECTRUM_CENTREX_GROUP								= 8;
	const	MCP_REJECT_CODE_PRESELECTION_CHOICE_ALREADY_IMPLEMENTED				= 9;
	const	MCP_REJECT_CODE_SERVICE_PORTED_TO_ANOTHER_ASD						= 10;
	const	MCP_REJECT_CODE_REQUESTED_SERVICE_TO_BE_PRESELECTED_IS_OWNED_BY_ASD	= 11;
	const	MCP_REJECT_CODE_RESTRICTED_ACCESS_SERVICE							= 12;
	const	MCP_REJECT_CODE_POINT_OF_PRESENCE_NOT_VALID							= 13;
	const	MCP_REJECT_CODE_ENHANCED_SERVICE_ISDN								= 14;
	const	MCP_REJECT_CODE_INCORRECT_ASD_NOMINATED								= 16;
	const	MCP_REJECT_CODE_REVERSAL_ERROR_NO_RECORD_OF_CNO_IN_NOMINATED_FILE	= 21;
	const	MCP_REJECT_CODE_INDIAL_SERVICE										= 25;
	const	MCP_REJECT_CODE_INVALID_PSD_NOMINATED								= 26;
	const	MCP_REJECT_CODE_OUTSIDE_ALLOWABLE_TIMEFRAME							= 31;
	const	MCP_REJECT_CODE_DUAL_NOTIFICATION_SAME_DAY_DIFFERENT_PSD			= 40;
	const	MCP_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_PRESELECTION			= 48;
	
	protected static	$_aMCPRejectCodes	= array(
		self::MCP_REJECT_CODE_SERVICE_NUMBER_NOT_FOUND								=> 'Service Number not found',
		self::MCP_REJECT_CODE_SERVICE_NUMBER_IS_ON_DIVERSION						=> 'Service Number is on diversion',
		self::MCP_REJECT_CODE_INACTIVE_SERVICE										=> 'Inactive service',
		self::MCP_REJECT_CODE_DISCONNECTED_SERVICE									=> 'Disconnected service',
		self::MCP_REJECT_CODE_SERVICE_NUMBER_FOUND_BUT_IS_NOT_PRESELECTABLE			=> 'Service Number found but is not preselectable (e.g. Satellite or mobile services, Incompatible exchange equipment, Telstra\'s EasyCall Multi Number)',
		self::MCP_REJECT_CODE_ENHANCED_SERVICE_OTHER								=> 'Enhanced service – other',
		self::MCP_REJECT_CODE_REAL_TIME_METERING_FOUND								=> 'Real Time Metering Found',
		self::MCP_REJECT_CODE_SPECTRUM_CENTREX_GROUP								=> 'Spectrum/Centrex Group',
		self::MCP_REJECT_CODE_PRESELECTION_CHOICE_ALREADY_IMPLEMENTED				=> 'Preselection choice already implemented',
		self::MCP_REJECT_CODE_SERVICE_PORTED_TO_ANOTHER_ASD							=> 'Service Ported to another ASD',
		self::MCP_REJECT_CODE_REQUESTED_SERVICE_TO_BE_PRESELECTED_IS_OWNED_BY_ASD	=> 'ASD Services - (Requested service to be preselected is owned by ASD)',
		self::MCP_REJECT_CODE_RESTRICTED_ACCESS_SERVICE								=> 'Restricted access service eg. Telstra\'s InContact product',
		self::MCP_REJECT_CODE_POINT_OF_PRESENCE_NOT_VALID							=> 'Point of presence not valid',
		self::MCP_REJECT_CODE_ENHANCED_SERVICE_ISDN									=> 'Enhanced Service – ISDN',
		self::MCP_REJECT_CODE_INCORRECT_ASD_NOMINATED								=> 'Incorrect ASD nominated',
		self::MCP_REJECT_CODE_REVERSAL_ERROR_NO_RECORD_OF_CNO_IN_NOMINATED_FILE		=> 'Reversal error - (no record of CNO in nominated file)',
		self::MCP_REJECT_CODE_INDIAL_SERVICE										=> 'Indial Service',
		self::MCP_REJECT_CODE_INVALID_PSD_NOMINATED									=> 'Invalid PSD nominated',
		self::MCP_REJECT_CODE_OUTSIDE_ALLOWABLE_TIMEFRAME							=> 'Outside Allowable Timeframe (Preselection request was older than 30 days when received by the ASD or Reversal request was older than 24 months when received by the ASD)',
		self::MCP_REJECT_CODE_DUAL_NOTIFICATION_SAME_DAY_DIFFERENT_PSD				=> 'Dual Notification Same Day Different PSD (A preselection or reversal request has been received when another preselection or reversal is being executed)',
		self::MCP_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_PRESELECTION				=> 'Reversal Rejected Subsequent Preselection (A reversal request can not be executed by the ASD because a preselection exists that is more recent than that for which reversal has been requested)'
	);
	
	const	EBILL_REJECT_CODE_SERVICE_NUMBER_NOT_FOUND													= 1;
	const	EBILL_REJECT_CODE_INACTIVE_SERVICE															= 3;
	const	EBILL_REJECT_CODE_DISCONNECTED_SERVICE														= 4;
	const	EBILL_REJECT_CODE_INCOMPATIBLE_SERVICE														= 5;
	const	EBILL_REJECT_CODE_WHOLESALE_CHOICE_ALREADY_IMPLEMENTED										= 9;
	const	EBILL_REJECT_CODE_SERVICE_PORTED_TO_ANOTHER_ASD												= 10;
	const	EBILL_REJECT_CODE_TELSTRA_SERVICES															= 11;
	const	EBILL_REJECT_CODE_RESTRICTED_ACCESS_SERVICE													= 12;
	const	EBILL_REJECT_CODE_INCOMPATIBLE_EXCHANGE_TECHNOLOGY											= 15;
	const	EBILL_REJECT_CODE_INCORRECT_ASD																= 16;
	const	EBILL_REJECT_CODE_PENDING_SERVICE_CHANGES													= 19;
	const	EBILL_REJECT_CODE_REVERSAL_NO_RECORD_OF_WNO_IN_NOMINATED_FILE								= 21;
	const	EBILL_REJECT_CODE_REVERSAL_ORIGINAL_ALREADY_REJECTED										= 22;
	const	EBILL_REJECT_CODE_INDIAL_SERVICE_OR_EXTENSION												= 25;
	const	EBILL_REJECT_CODE_REVERSAL_OUTSIDE_VALID_PERIOD												= 27;
	const	EBILL_REJECT_CODE_SP_SUSPENSION																= 28;
	const	EBILL_REJECT_CODE_LONG_DISTANCE_PACKAGE_REDIRECTION_ASSOCIATED_EBILL_REQUEST_REJECTED		= 29;
	const	EBILL_REJECT_CODE_LONG_DISTANCE_PACKAGE_REDIRECTION_SP_NOT_AUTHORISED						= 30;
	const	EBILL_REJECT_CODE_CA_DATE_OUTSIDE_ALLOWABLE_TIMEFRAME										= 31;
	const	EBILL_REJECT_CODE_THE_PRIMARY_WHOLESALE_REDIRECTION_GROUPS_ARE_NOT_ON_THE_SERVICE			= 35;
	const	EBILL_REJECT_CODE_EXCLUDED_SERVICE															= 36;
	const	EBILL_REJECT_CODE_INVALID_SP_WRG															= 43;
	const	EBILL_REJECT_CODE_WRG_CODE_INVALID															= 44;
	const	EBILL_REJECT_CODE_INVALID_PREFIX_SUFFIX_SERVICE_NUMBER										= 45;
	const	EBILL_REJECT_CODE_PRODUCT_INCOMPATIBILITY													= 46;
	const	EBILL_REJECT_CODE_LONG_DISTANCE_USAGE_WHOLESALE_REDIRECTION_GROUP_NOT_PRESENT_ON_SERVICE	= 47;
	const	EBILL_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_TRANSFER										= 48;
	const	EBILL_REJECT_CODE_REQUESTING_SP_IS_THE_LESSEE												= 49;
	const	EBILL_REJECT_CODE_DIFFERENT_SP_IS_THE_LESSEE												= 50;
	const	EBILL_REJECT_CODE_EFFECTIVE_BILL_DATE_IS_INVALID											= 62;
	const	EBILL_REJECT_CODE_INVALID_TITLE_OR_ADDRESS_ABBREVIATION										= 72;
	const	EBILL_REJECT_CODE_ALL_WRGS_NOT_REQUESTED_FOR_SP												= 74;
	const	EBILL_REJECT_CODE_INVALID_FORMATTED_REQUEST_ADVICE_RECORD									= 99;
	
	protected static	$_aEBillRejectCodes	= array(
		self::EBILL_REJECT_CODE_SERVICE_NUMBER_NOT_FOUND												=> 'Service number not found',
		self::EBILL_REJECT_CODE_INACTIVE_SERVICE														=> 'Inactive service (Temporarily Disconnected)',
		self::EBILL_REJECT_CODE_DISCONNECTED_SERVICE													=> 'Disconnected service (Disconnected or Pending Disconnection)',
		self::EBILL_REJECT_CODE_INCOMPATIBLE_SERVICE													=> 'Service number found but service is not available to LinxOnline eBill (e.g. Satellite, mobile)',
		self::EBILL_REJECT_CODE_WHOLESALE_CHOICE_ALREADY_IMPLEMENTED									=> 'Wholesale choice already implemented',
		self::EBILL_REJECT_CODE_SERVICE_PORTED_TO_ANOTHER_ASD											=> 'Service Ported to another ASD',
		self::EBILL_REJECT_CODE_TELSTRA_SERVICES														=> 'Telstra Services',
		self::EBILL_REJECT_CODE_RESTRICTED_ACCESS_SERVICE												=> 'Restricted access service',
		self::EBILL_REJECT_CODE_INCOMPATIBLE_EXCHANGE_TECHNOLOGY										=> 'Incompatible Exchange Technology',
		self::EBILL_REJECT_CODE_INCORRECT_ASD															=> 'Incorrect ASD',
		self::EBILL_REJECT_CODE_PENDING_SERVICE_CHANGES													=> 'Pending Service Changes',
		self::EBILL_REJECT_CODE_REVERSAL_NO_RECORD_OF_WNO_IN_NOMINATED_FILE								=> 'Reversal – No Record of WNO in nominated file',
		self::EBILL_REJECT_CODE_REVERSAL_ORIGINAL_ALREADY_REJECTED										=> 'Reversal – Original Already Rejected',
		self::EBILL_REJECT_CODE_INDIAL_SERVICE_OR_EXTENSION												=> 'Indial Service or Extension',
		self::EBILL_REJECT_CODE_REVERSAL_OUTSIDE_VALID_PERIOD											=> 'Reversal – Outside Valid Period',
		self::EBILL_REJECT_CODE_SP_SUSPENSION															=> 'SP Suspension',
		self::EBILL_REJECT_CODE_LONG_DISTANCE_PACKAGE_REDIRECTION_ASSOCIATED_EBILL_REQUEST_REJECTED		=> 'Long Distance Package Redirection – Associated eBill request rejected',
		self::EBILL_REJECT_CODE_LONG_DISTANCE_PACKAGE_REDIRECTION_SP_NOT_AUTHORISED						=> 'Long Distance Package Redirection - SP not authorised',
		self::EBILL_REJECT_CODE_CA_DATE_OUTSIDE_ALLOWABLE_TIMEFRAME										=> 'CA Date Outside Allowable Timeframe',
		self::EBILL_REJECT_CODE_THE_PRIMARY_WHOLESALE_REDIRECTION_GROUPS_ARE_NOT_ON_THE_SERVICE			=> 'The Primary Wholesale Redirection Groups are not on the service',
		self::EBILL_REJECT_CODE_EXCLUDED_SERVICE														=> 'Excluded Service',
		self::EBILL_REJECT_CODE_INVALID_SP_WRG															=> 'Invalid SP WRG',
		self::EBILL_REJECT_CODE_WRG_CODE_INVALID														=> 'WRG Code Invalid',
		self::EBILL_REJECT_CODE_INVALID_PREFIX_SUFFIX_SERVICE_NUMBER									=> 'Invalid Prefix/Suffix – Service Number',
		self::EBILL_REJECT_CODE_PRODUCT_INCOMPATIBILITY													=> 'Product Incompatibility',
		self::EBILL_REJECT_CODE_LONG_DISTANCE_USAGE_WHOLESALE_REDIRECTION_GROUP_NOT_PRESENT_ON_SERVICE	=> 'Long Distance Package Redirection – Long Distance Usage Wholesale Redirection Group not present on service',
		self::EBILL_REJECT_CODE_REVERSAL_REJECTED_SUBSEQUENT_TRANSFER									=> 'Reversal Rejected Subsequent Transfer',
		self::EBILL_REJECT_CODE_REQUESTING_SP_IS_THE_LESSEE												=> 'Requesting SP is the Lessee',
		self::EBILL_REJECT_CODE_DIFFERENT_SP_IS_THE_LESSEE												=> 'Different SP is the Lessee',
		self::EBILL_REJECT_CODE_EFFECTIVE_BILL_DATE_IS_INVALID											=> 'Effective Bill Date is Invalid',
		self::EBILL_REJECT_CODE_INVALID_TITLE_OR_ADDRESS_ABBREVIATION									=> 'Invalid Title or Address Abbreviation',
		self::EBILL_REJECT_CODE_ALL_WRGS_NOT_REQUESTED_FOR_SP											=> 'All WRG\'s not requested for SP',
		self::EBILL_REJECT_CODE_INVALID_FORMATTED_REQUEST_ADVICE_RECORD									=> 'Invalid Formatted Request Advice Record'
	);
 }
?>
