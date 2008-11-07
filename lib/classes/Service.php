<?php
//----------------------------------------------------------------------------//
// Service
//----------------------------------------------------------------------------//
/**
 * Service
 *
 * Models a record of the Service table
 *
 * Models a record of the Service table
 *
 * @class	Service
 */
class Service extends ORM
{	
	protected	$_strTableName	= "Service";

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining a Service with keys for each field of the InvoiceRun table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the Service with the passed Id
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * getCurrentPlan()
	 *
	 * Gets the current Rate Plan for this Service
	 *
	 * @param	string	$strEffectiveDatetime		[optional]	The date to check against
	 *
	 * @return	Rate_Plan
	 *
	 * @method
	 */
	public function getCurrentPlan($strEffectiveDatetime=NULL)
	{
		$strEffectiveDatetime		= (strtotime($strEffectiveDatetime)) ? $strEffectiveDatetime : date("Y-m-d H:i:s");
		$selCurrentServiceRatePlan	= self::_preparedStatement('selCurrentServiceRatePlan');
		if ($selCurrentServiceRatePlan->Execute() === FALSE)
		{
			throw new Exception($selCurrentServiceRatePlan->Error());
		}
		elseif ($arrCurrentServiceRatePlan = $selCurrentServiceRatePlan->Fetch())
		{
			return new Rate_Plan(array('Id'=>$arrCurrentServiceRatePlan['RatePlan']), TRUE);
		}
		else
		{
			// No Current Plan
			return NULL;
		}
	}
	
	/**
	 * changePlan()
	 *
	 * Changes the Rate Plan for this Service
	 *
	 * @param	mixed	$mixRatePlan					Rate_Plan object, or the Id of the new Rate Plan
	 * @param	boolean	$bolStartThisMonth	[optional]	TRUE: Starts the new Plan this month; FALSE: Starts the new Plan next month
	 *
	 * @return	void
	 *
	 * @method
	 */
	public function changePlan($mixRatePlan, $bolStartThisMonth=TRUE)
	{
		$objAccount	= new Account(array('Id'=>$this->Account));
		
		// Check if the billing/invoice process is being run
		if (Invoice_Run::checkTemporary())
		{
			// There are currently records in the InvoiceTemp table, which means a bill run is taking place.
			// Plan Changes cannot be made when a bill run is taking place
			$strErrorMsg =  "Billing is in progress.  Plans cannot be changed while this is happening.  ".
							"Please try again in a couple of hours.  If this problem persists, please ".
							"notify your system administrator";
			return $strErrorMsg;
		}
		
		// Load the new RatePlan details
		$objNewRatePlan	= NULL;
		if ($mixRatePlan instanceof Rate_Plan)
		{
			$objNewRatePlan	= $mixRatePlan;
		}
		elseif (is_int($mixRatePlan))
		{
			$objNewRatePlan	= new Rate_Plan(array('Id'=>$mixRatePlan));
		}
		else
		{
			throw new Exception("Invalid RatePlan ('{$mixRatePlan}') passed");
		}
		
		// Work out the StartDatetime for the new records of the ServiceRatePlan and ServiceRateGroup tables
		$strCurrentDateAndTime						= GetCurrentDateAndTimeForMySQL();
		$intStartDateTimeForCurrentBillingPeriod	= strtotime(Invoice_Run::getLastInvoiceDate($objAccount->CustomerGroup, $strCurrentDateAndTime));
		$intStartDateTimeForNextBillingPeriod		= strtotime(Invoice_Run::predictNextInvoiceDate($objAccount->CustomerGroup, $strCurrentDateAndTime));
		
		if (!$bolStartThisMonth)
		{
			// Get the StartDatetime for the next billing period
			$intStartDatetime = $intStartDateTimeForNextBillingPeriod;
			
			// Active field has been effectively deprecated, so just set it to 1
			$intActive = 1;
			
			// Declare the note part detailing when the Plan Change will come into effect
			$strNotePlanStart = "This plan change will come into effect as of the start of the next billing period. (". date("d/m/Y", $intStartDatetime) .")";
		}
		else
		{
			// Get the StartDatetime for the current billing period
			$intStartDatetime = $intStartDateTimeForCurrentBillingPeriod;
			
			// The records defining the new plan should have their "Active" property set to 1 (Active)
			$intActive = 1;
			
			// Declare the note part detailing when the Plan Change will come into effect
			$strNotePlanStart = "This plan change has come into effect as of the beginning of the current billing period. (". date("d/m/Y", $intStartDatetime) .")";
		}
		$strStartDatetime = date("Y-m-d H:i:s", $intStartDatetime);
		
		// Work out the EndDatetime for the old records of the ServiceRatePlan and ServiceRateGroup tables, which have an EndDatetime
		// greater than $strStartDatetime
		// The EndDatetime will be set to 1 second before the StartDatetime of the records relating to the new plan
		$intOldPlanEndDatetime = $intStartDatetime - 1;
		$strOldPlanEndDatetime = date("Y-m-d H:i:s", $intOldPlanEndDatetime);
		
		// Find the current plan (if there is one)
		$objCurrentRatePlan = $this->getCurrentPlan();
		
		// Check that the Plan is active and is of the appropriate ServiceType and CustomerGroup
		if ($objNewRatePlan->Archived != RATE_STATUS_ACTIVE)
		{
			return "ERROR: This Plan is not currently active";
		}
		if ($objNewRatePlan->ServiceType != $this->ServiceType)
		{
			return "ERROR: This Plan is not of the same ServiceType as the Service";
		}
		if ($objNewRatePlan->customer_group != $objAccount->CustomerGroup)
		{
			return "ERROR: This Plan does not belong to the CustomerGroup that this account belongs to";
		}
		
		// Set the EndDatetime to $strOldPlanEndDatetime for all records in the ServiceRatePlan and ServiceRateGroup tables
		// which relate this service.  Do not alter the records' "Active" property regardless of what it is.
		
		// Update existing ServiceRateGroup records
		$arrUpdate = Array('EndDatetime' => $strOldPlanEndDatetime);
		$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
		if ($updServiceRateGroup->Execute($arrUpdate, Array("Service"=>$this->Id, "StartDatetime"=>$strStartDatetime)) === FALSE)
		{
			throw new Exception($updServiceRateGroup->Error());
		}
		
		// Update existing ServiceRatePlan records
		$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
		if ($updServiceRatePlan->Execute($arrUpdate, Array("Service"=>$this->Id, "StartDatetime"=>$strStartDatetime)) === FALSE)
		{
			// Could not update records in ServiceRatePlan table. Exit gracefully
			throw new Exception($updServiceRatePlan->Error());
		}
		
		// Get the current User
		$intUserId	= Flex::getUserId();
		$intUserId	= ($intUserId) ? $intUserId : 0;
		
		// Declare the new plan for the service
		// Insert a record into the ServiceRatePlan table
		$objServiceRatePlan	= new Service_Rate_Plan();
		$objServiceRatePlan->Service 							= $this->Id;
		$objServiceRatePlan->RatePlan 							= $objNewRatePlan->Id;
		$objServiceRatePlan->CreatedBy 							= $intUserId;
		$objServiceRatePlan->CreatedOn 							= $strCurrentDateAndTime;
		$objServiceRatePlan->StartDatetime 						= $strStartDatetime;
		$objServiceRatePlan->EndDatetime 						= END_OF_TIME;
		$objServiceRatePlan->LastChargedOn						= NULL;
		$objServiceRatePlan->Active								= $intActive;
		
		$intContractTerm										= (int)$objNewRatePlan->ContractTerm;
		$objServiceRatePlan->contract_scheduled_end_datetime	= ($intContractTerm > 0) ? date('Y-m-d H:i:s', strtotime("-1 second", strtotime("+{$intContractTerm} months", $intStartDatetime))) : NULL;
		$objServiceRatePlan->contract_effective_end_datetime	= NULL;
		$objServiceRatePlan->contract_status_id					= ($intContractTerm > 0) ? CONTRACT_STATUS_ACTIVE : NULL;
		
		$objServiceRatePlan->save();
		
		// Declare the new RateGroups for the service
		$strInsertRateGroupsIntoServiceRateGroup  = "INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ";
		$strInsertRateGroupsIntoServiceRateGroup .= "SELECT NULL, {$this->Id}, RateGroup, {$intUserId}, '{$strCurrentDateAndTime}', '{$strStartDatetime}', '0000-00-00 00:00:00', {$intActive} ";
		$strInsertRateGroupsIntoServiceRateGroup .= "FROM RatePlanRateGroup WHERE RatePlan = {$objNewRatePlan->Id} ORDER BY RateGroup";
		$qryInsertServiceRateGroup = new Query();
		if ($qryInsertServiceRateGroup->Execute($strInsertRateGroupsIntoServiceRateGroup) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed.  Exit gracefully
			throw new Exception($qryInsertServiceRateGroup->Error());
		}
		
		// If the plan goes into affect at the begining of the current month, then you must rerate all the cdrs which are currently
		// rated but not billed
		if ($bolStartThisMonth)
		{
			// The plan change is retroactive to the start of the current month
			// Set the status of all CDRs that are currently "rated" (CDR_RATED) to "ready for rating" (CDR_NORMALISED)
			$arrUpdate	= Array('Status' => CDR_NORMALISED);
			$updCDRs	= new StatementUpdate("CDR", "Service = <Service> AND Status = <CDRRated>", $arrUpdate);
			if ($updCDRs->Execute($arrUpdate, Array("Service"=>$this->Id, "CDRRated"=>CDR_RATED)) === FALSE)
			{
				throw new Exception($updCDRs->Error());
			}
			
			// Only update the Carrier and CarrierPreselect fields of the Service record, 
			// if the new plan comes into affect at the beging of the current billing period
			$arrUpdate = Array(	"Carrier"			=> $objNewRatePlan->CarrierFullService,
								"CarrierPreselect"	=> $objNewRatePlan->CarrierPreselection);
			
			$updService = new StatementUpdate("Service", "Id = <Service>", $arrUpdate);
			if ($updService->Execute($arrUpdate, Array("Service" => $this->Id)) === FALSE)
			{
				throw new Exception($updService->Error());
			}
		}
		
		//TODO! Do automatic provisioning here
		
		// Add a system note describing the change of plan
		$strCurrentRatePlan	= ($objCurrentRatePlan) ? $objCurrentRatePlan->Name : "undefined";
		$strNote  = "This service has had its plan changed from '{$strCurrentRatePlan}' to '{$objNewRatePlan->Name}'.  $strNotePlanStart";
		
		SaveSystemNote($strNote, $this->AccountGroup, $this->Account, NULL, $this->Id);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selCurrentServiceRatePlan':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceRatePlan", "RatePlan", "Service = <service_id> AND <effective_datetime> BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Service");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Service");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>