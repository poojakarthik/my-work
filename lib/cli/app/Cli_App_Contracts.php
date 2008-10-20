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
					$this->_updateContracts();
					break;

				case 'CHARGE':
					// Charges any outstanding Contract Fees
					$this->_chargeFees();
					break;

				case 'ALL':
					// Updates the Contract Details and charges any outstanding Contract Fees
					$this->_updateContracts();
					$this->_chargeFees();
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
		$strEffectiveDate	= ($strEffectiveDate) ? $strEffectiveDate : date("Y-m-d H:i:s");
		$intEffectiveDate	= strtotime($strEffectiveDate);
		
		$arrLossStatuses	= Array(SERVICE_LINE_DISCONNECTED, SERVICE_LINE_CHURNED, SERVICE_LINE_REVERSED);
		$arrLossClosures	= Array(SERVICE_CLOSURE_DISCONNECTED, SERVICE_CLOSURE_ARCHIVED);
		
		// Statements
		$selContractServices	= new StatementSelect(	"Service JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service",
														"Service.ClosedOn, Service.NatureOfClosure, Service.LineStatus, Service.LineStatusDate, SRP.*, SRP.Id AS ServiceRatePlanId",
														"SRP.Id = (SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND <EffectiveDate> BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn LIMIT 1) AND contract_status_id = ".CONTRACT_STATUS_ACTIVE." AND Service.Status != ".SERVICE_STATUS_ARCHIVED);
		$ubiServiceRatePlan		= new StatementUpdateById("ServiceRatePlan", Array('contract_effective_end_datetime'=>NULL, 'contract_status_id'=>NULL));
		
		// Get list of Services/Contracts to update
		if ($selContractServices->Execute(Array('EffectiveDate' => $strEffectiveDate)) === FALSE)
		{
			throw new Exception($selContractServices->Error());
		}
		else
		{
			while ($arrContractService = $selContractServices->Fetch())
			{
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
				elseif ($intEffectiveDate > $intLineStatusDate && in_array($arrContractService['LineStatus'], $arrLossStatuses))
				{
					// Contract has been Breached -- Loss notice via Carrier
					$arrServiceRatePlan['contract_effective_end_datetime']	= $arrContractService['LineStatusDate'];
					$arrServiceRatePlan['contract_status_id']				= CONTRACT_STATUS_BREACHED;
				}
				elseif ($intEffectiveDate > $intClosedOn && in_array($arrContractService['NatureOfClosure'], $arrLossClosures))
				{
					// Contract has been Breached -- Service prematurely closed
					$arrServiceRatePlan['contract_effective_end_datetime']	= $arrContractService['ClosedOn'];
					$arrServiceRatePlan['contract_status_id']				= CONTRACT_STATUS_BREACHED;
				}
				else
				{
					// Contract is still active
					continue;
				}
				
				// Update the ServiceRatePlan record
				if ($ubiServiceRatePlan->Execute($arrServiceRatePlan) === FALSE)
				{
					throw new Exception($ubiServiceRatePlan->Error());
				}
			}
		}
	}
	
	private function _chargeFees()
	{
		
	}
	
	public static function debug($mixMessage, $bolNewLine=TRUE)
	{
		if (defined('BILLING_TEST_MODE') && BILLING_TEST_MODE)
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
				self::ARG_DESCRIPTION	=> "Contracts operation to perform [UPDATE|CHARGE|ALL]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("UPDATE","CHARGE","ALL"))'
			)
		);
	}
}
?>