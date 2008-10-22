<?php
//----------------------------------------------------------------------------//
// Cli_App_Contracts
//----------------------------------------------------------------------------//
/**
 * Cli_App_Contracts
 *
 * Contract Manipulation CLI Application
 *
 * Contract Manipulation CLI Application
 *
 * @class	Cli_App_Contracts
 * @parent	Cli
 */
class Cli_App_Contracts extends Cli
{
	const	SWITCH_TEST_RUN			= "t";
	const	SWITCH_MODE				= "m";
	const	SWITCH_EFFECTIVE_DATE	= "d";
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}
			
			define('CLI_APP_TEST_MODE',	(bool)$this->_arrArgs[self::SWITCH_TEST_RUN]);

			// Any additional Includes
			//$this->requireOnce('flex.require.php');
			$this->requireOnce('lib/classes/Flex.php');
			Flex::load();

			// Start a new Transcation
			$bolTransactionResult	= DataAccess::getDataAccess()->TransactionStart();
			$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully started!");
			
			// Perform the operation
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'UPDATE':
					// Updates the Contract Details for each Service 
					$this->_updateContracts($this->_arrArgs[self::SWITCH_EFFECTIVE_DATE]);
					break;
					
				case 'FIX':
					// Fixes ServiceRatePlan records which were created before Contract Awareness was introduced
					$this->_fixContracts();
					break;
					
				default:
					throw new Exception("Invalid MODE '{$this->_arrArgs[self::SWITCH_MODE]}' specified!");
			}
			
			// If not in test mode, Commit the Transaction
			if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionCommit();
				$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully committed!");
			}
			else
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
				$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");
			}
			return 0;
		}
		catch(Exception $exception)
		{
			$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
			$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$strMessage	= $exception->__toString();
			}
			else
			{
				$strMessage	= $exception->getMessage();
			}

			// We can now show the error message
			$this->showUsage($strMessage);
			return 1;
		}
	}
	
	private function _updateContracts($strEffectiveDate=NULL)
	{
		$this->log(":: Updating Contract Details ::\n");
		
		$strEffectiveDate	= ($strEffectiveDate) ? date("Y-m-d H:i:s", strtotime($strEffectiveDate)) : date("Y-m-d H:i:s");
		$intEffectiveDate	= strtotime($strEffectiveDate);
		
		$arrLossStatuses	= Array(SERVICE_LINE_DISCONNECTED, SERVICE_LINE_CHURNED, SERVICE_LINE_REVERSED);
		$arrLossClosures	= Array(SERVICE_CLOSURE_DISCONNECTED, SERVICE_CLOSURE_ARCHIVED);
		
		// Statements
		$selContractServices	= new StatementSelect(	"Service JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service",
														"Service.Account, Service.FNN, Service.ClosedOn, Service.NatureOfClosure, Service.LineStatus, Service.LineStatusDate, SRP.*, SRP.Id AS ServiceRatePlanId",
														"SRP.Id = (SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND <EffectiveDate> BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn LIMIT 1) AND contract_status_id = ".CONTRACT_STATUS_ACTIVE." AND Service.Status != ".SERVICE_ARCHIVED);
		$ubiServiceRatePlan		= new StatementUpdateById("ServiceRatePlan", Array('contract_effective_end_datetime'=>NULL, 'contract_status_id'=>NULL, 'contract_breach_reason_id'=>NULL, 'contract_breach_reason_description'=>NULL));
		
		// Get list of Services/Contracts to update
		if ($selContractServices->Execute(Array('EffectiveDate' => $strEffectiveDate)) === FALSE)
		{
			throw new Exception($selContractServices->Error());
		}
		else
		{
			while ($arrContractService = $selContractServices->Fetch())
			{
				$this->log(" + {$arrContractService['Account']}::{$arrContractService['FNN']}... ", FALSE, FALSE);
				
				$intClosedOn				= strtotime($arrContractService['ClosedOn']);
				$intLineStatusDate			= strtotime($arrContractService['LineStatusDate']);
				$intScheduledEndDatetime	= strtotime($arrContractService['contract_scheduled_end_datetime']);
				
				// Has this Contract ended and why?
				$arrServiceRatePlan	= Array('Id' => $arrContractService['ServiceRatePlanId']);
				if ($intScheduledEndDatetime < $intEffectiveDate)
				{
					// Contract has expired
					$arrServiceRatePlan['contract_effective_end_datetime']	= $arrContractService['contract_scheduled_end_datetime'];
					$arrServiceRatePlan['contract_status_id']				= CONTRACT_STATUS_EXPIRED;
				}
				elseif ($intLineStatusDate < $intEffectiveDate)
				{
					// Contract has been Breached -- Loss notice via Carrier
					$arrServiceRatePlan['contract_effective_end_datetime']	= $arrContractService['LineStatusDate'];
					$arrServiceRatePlan['contract_status_id']				= CONTRACT_STATUS_BREACHED;
					
					switch ($arrContractService['LineStatus'])
					{
						case SERVICE_LINE_DISCONNECTED:
							$arrServiceRatePlan['contract_breach_reason_id']		= CONTRACT_BREACH_REASON_DISCONNECTED;
							break;
							
						case SERVICE_LINE_CHURNED:
							$arrServiceRatePlan['contract_breach_reason_id']		= CONTRACT_BREACH_REASON_CHURNED;
							break;
							
						default:
							// Line Status is not a Contract-breaker
							$this->log("SKIPPED");
							continue;
					}
				}
				elseif ($intClosedOn < $intEffectiveDate && in_array($arrContractService['NatureOfClosure'], $arrLossClosures))
				{
					// Contract has been Breached -- Service prematurely closed
					$arrServiceRatePlan['contract_effective_end_datetime']		= $arrContractService['ClosedOn'];
					$arrServiceRatePlan['contract_status_id']					= CONTRACT_STATUS_BREACHED;
					$arrServiceRatePlan['contract_breach_reason_id']			= CONTRACT_BREACH_REASON_OTHER;
					$arrServiceRatePlan['contract_breach_reason_description']	= "Service Prematurely Closed in Flex";
				}
				else
				{
					// Contract is still active
					$this->log("SKIPPED");
					continue;
				}
				
				// Fill the Description field
				if (!$arrServiceRatePlan['contract_breach_reason_description'])
				{
					$arrServiceRatePlan['contract_breach_reason_description']	= GetConstantDescription($arrServiceRatePlan['contract_breach_reason_id'], 'contract_breach_reason');
				}
				
				// Update the ServiceRatePlan record
				if ($ubiServiceRatePlan->Execute($arrServiceRatePlan) === FALSE)
				{
					throw new Exception($ubiServiceRatePlan->Error());
				}
				
				$this->log("{$arrServiceRatePlan['contract_breach_reason_description']} @ {$arrServiceRatePlan['contract_effective_end_datetime']}");
			}
		}
	}
	
	private function _fixContracts()
	{
		$strEffectiveDate		= date("Y-m-d 00:00:00");
		
		$this->log(":: Fixing Old Contracts ::\n");
		
		// Statements
		$selServiceRatePlans	= new StatementSelect(	"ServiceRatePlan SRP JOIN RatePlan ON RatePlan.Id = SRP.RatePlan",
														"SRP.*, RatePlan.ContractTerm, RatePlan.Name",
														"RatePlan.ContractTerm IS NOT NULL AND RatePlan.ContractTerm > 0 AND ServiceRatePlan.contract_scheduled_end_datetime IS NULL AND ServiceRatePlan.contract_status_id IS NULL AND ('{$strEffectiveDate}' BETWEEN StartDatetime AND EndDatetime OR EndDatetime = '9999-12-31 11:59:59')");
		$ubiServiceRatePlan		= new StatementUpdateById("ServiceRatePlan", Array('contract_scheduled_end_datetime'=>NULL, 'contract_status_id'=>NULL));
		
		// Get list of Contracted ServiceRatePlans that are either current, or are scheduled to continue until the end of time
		if ($selServiceRatePlans->Execute() === FALSE)
		{
			throw new Exception($selServiceRatePlans->Error());
		}
		else
		{
			while ($arrServiceRatePlan = $selServiceRatePlans->Fetch())
			{
				$this->log(" + {$arrServiceRatePlan['Service']}::{$arrServiceRatePlan['Name']}");
				// Calculate Scheduled End of Contract
				$arrServiceRatePlan['contract_scheduled_end_datetime']	= date("Y-m-d H:i:s", strtotime("-1 second", strtotime("+{$arrServiceRatePlan['ContractTerm']} months", strtotime($arrServiceRatePlan['StartDatetime']))));
				if ($ubiServiceRatePlan->Execute($arrServiceRatePlan) === FALSE)
				{
					throw new Exception($ubiServiceRatePlan->Error());
				}
			}
		}
	}
	
	public static function debug($mixMessage, $bolNewLine=TRUE)
	{
		if (defined('CLI_APP_TEST_MODE') && CLI_APP_TEST_MODE)
		{
			if (!is_scalar($mixMessage))
			{
				$mixMessage	= print_r($mixMessage, TRUE);
			}
			CliEcho($mixMessage, $bolNewLine);
		}
		else
		{
			// FIXME: Output to normal log
			CliEcho($mixMessage, $bolNewLine);
		}
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Revokes the transaction that encapsualtes Contract Manipulation, and provides debug data",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Contracts operation to perform [UPDATE|FIX]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("UPDATE","FIX"))'
			),

			self::SWITCH_EFFECTIVE_DATE => array(
				self::ARG_LABEL			=> "EFFECTIVE_DATE",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Effective Date for the Contract Calculations (YYYY-MM-DD format)",
				self::ARG_DEFAULT		=> date("Y-m-d"),
				self::ARG_VALIDATION	=> 'Cli::_validDate("%1$s")'
			)
		);
	}
}
?>