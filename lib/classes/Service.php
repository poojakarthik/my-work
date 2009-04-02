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
	 * getCurrentServiceRatePlan()
	 *
	 * Gets the current Service Rate Plan for this Service
	 *
	 * @param	string	$strEffectiveDatetime		[optional]	The date to check against
	 *
	 * @return	Service_Rate_Plan
	 *
	 * @method
	 */
	public function getCurrentServiceRatePlan($strEffectiveDatetime=NULL)
	{
		$strEffectiveDatetime		= ($strEffectiveDatetime !== NULL) ? $strEffectiveDatetime : Data_Source_Time::currentTimestamp();
		$selCurrentServiceRatePlan	= self::_preparedStatement('selCurrentServiceRatePlan');
		
		$arrWhere = array(	"service_id"			=> $this->id,
							"effective_datetime"	=> $strEffectiveDatetime);
		
		if ($selCurrentServiceRatePlan->Execute($arrWhere) === FALSE)
		{
			throw new Exception($selCurrentServiceRatePlan->Error());
		}
		elseif (($arrCurrentServiceRatePlan = $selCurrentServiceRatePlan->Fetch()) !== FALSE)
		{
			return new Service_Rate_Plan($arrCurrentServiceRatePlan);
		}
		else
		{
			// No Current Plan
			return NULL;
		}
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
		$objServiceRatePlan	= $this->getCurrentServiceRatePlan($strEffectiveDatetime);
		if ($objServiceRatePlan instanceof Service_Rate_Plan)
		{
			return new Rate_Plan(array('Id'=>$objServiceRatePlan->RatePlan), true);
		}
		else
		{
			// No Current Plan
			return null;
		}
	}
	
	/**
	 * changePlan()
	 *
	 * Changes the Rate Plan for this Service
	 *
	 * @param	mixed	$mixRatePlan					Rate_Plan object, or the Id of the new Rate Plan
	 * @param	boolean	$bolStartThisBillingPeriod	[optional]	TRUE: Starts the new Plan this month; FALSE: Starts the new Plan next month
	 *
	 * @return	void
	 *
	 * @method
	 */
	public function changePlan($mixRatePlan, $bolStartThisBillingPeriod=TRUE)
	{
		$objAccount	= new Account(array('Id'=>$this->Account), FALSE, TRUE);
		
		// Check if the billing/invoice process is being run
		if (Invoice_Run::checkTemporary($objAccount->customerGroup, $objAccount->id))
		{
			throw new Exception("The Plan Change action is temporarily unavailable because a related, live invoice run is currently outstanding");
		}
		
		// Load the new RatePlan details
		$objNewRatePlan	= NULL;
		if ($mixRatePlan instanceof Rate_Plan)
		{
			$objNewRatePlan	= $mixRatePlan;
		}
		elseif (is_int($mixRatePlan))
		{
			$objNewRatePlan	= new Rate_Plan(array('Id'=>$mixRatePlan), TRUE);
		}
		else
		{
			throw new Exception("Invalid RatePlan ('{$mixRatePlan}') passed");
		}
		
		// Work out the StartDatetime for the new records of the ServiceRatePlan and ServiceRateGroup tables
		$strCurrentDateAndTime						= Data_Source_Time::currentTimestamp();
		$intStartDateTimeForCurrentBillingPeriod	= strtotime($objAccount->getBillingPeriodStart($strCurrentDateAndTime));
		$intStartDateTimeForNextBillingPeriod		= strtotime(Invoice_Run::predictNextInvoiceDate($objAccount->CustomerGroup, $strCurrentDateAndTime));

		if (!$bolStartThisBillingPeriod)
		{
			// Snap the plan change to the begining of the next billing period
			
			// First make sure the start of the current billing period isn't in the future (IF an interim or final invoice was produced today for the account, then this could be the case, because the BillingDate will be set to tomorrow)
			if ($intStartDateTimeForCurrentBillingPeriod > strtotime($strCurrentDateAndTime))
			{
				throw new Exception("The start of the current billing period (". date("H:i:s d-m-Y", $intStartDateTimeForCurrentBillingPeriod) .") is greater than the current timestamp (". date("H:i:s d-m-Y", strtotime($strCurrentDateAndTime)) .")");
			}
			
			// Get the StartDatetime for the next billing period
			$intStartDatetime = $intStartDateTimeForNextBillingPeriod;
			
			// Active field has been effectively deprecated, so just set it to 1
			$intActive = 1;
			
			// Declare the note part detailing when the Plan Change will come into effect
			$strNotePlanStart = "This plan change will come into effect as of the start of the next billing period. (". date("d/m/Y", $intStartDatetime) .")";
		}
		else
		{
			// Snap the plan change to the begining of the current billing period

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
		$objServiceRatePlan->EndDatetime 						= '9999-12-31 23:59:59';
		$objServiceRatePlan->LastChargedOn						= NULL;
		$objServiceRatePlan->Active								= $intActive;
		
		$intContractTerm										= (int)$objNewRatePlan->ContractTerm;
		$objServiceRatePlan->contract_scheduled_end_datetime	= ($intContractTerm > 0) ? date('Y-m-d H:i:s', strtotime("-1 second", strtotime("+{$intContractTerm} months", $intStartDatetime))) : NULL;
		$objServiceRatePlan->contract_effective_end_datetime	= NULL;
		$objServiceRatePlan->contract_status_id					= ($intContractTerm > 0) ? CONTRACT_STATUS_ACTIVE : NULL;
		
		$objServiceRatePlan->save();
		
		// Declare the new RateGroups for the service
		$strInsertRateGroupsIntoServiceRateGroup  = "INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ";
		$strInsertRateGroupsIntoServiceRateGroup .= "SELECT NULL, {$this->Id}, RateGroup, {$intUserId}, '{$strCurrentDateAndTime}', '{$strStartDatetime}', '9999-12-31 23:59:59', {$intActive} ";
		$strInsertRateGroupsIntoServiceRateGroup .= "FROM RatePlanRateGroup WHERE RatePlan = {$objNewRatePlan->Id} ORDER BY RateGroup";
		$qryInsertServiceRateGroup = new Query();
		if ($qryInsertServiceRateGroup->Execute($strInsertRateGroupsIntoServiceRateGroup) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed.  Exit gracefully
			throw new Exception($qryInsertServiceRateGroup->Error());
		}
		
		// If the plan goes into affect at the begining of the current month, then you must rerate all the cdrs which are currently
		// rated but not billed
		if ($bolStartThisBillingPeriod)
		{
			// The plan change is retroactive to the start of the current month
			// Set the status of all CDRs that are currently Rated, RateNotFound, ReRate or TempInvoice (CDR_RATED, CDR_RATE_NOT_FOUND, CDR_RERATE, CDR_TEMP_INVOICE)
			// to "ready for rating" (CDR_NORMALISED)
			$arrUpdate				= Array('Status' => CDR_NORMALISED);
			$strCDRStatusesToRerate	= implode(", ", array(CDR_RATED, CDR_RATE_NOT_FOUND, CDR_RERATE, CDR_TEMP_INVOICE));
			$updCDRs				= new StatementUpdate("CDR", "Service = <Service> AND Status IN ({$strCDRStatusesToRerate})", $arrUpdate);
			if ($updCDRs->Execute($arrUpdate, Array("Service"=>$this->Id)) === FALSE)
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
		
		Note::createSystemNote($strNote, $intUserId, $this->Account, $this->Id);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Retrieves a Service object, based on the Service record id passed
	 *
	 * Retrieves a Service object, based on the Service record id passed
	 * 
	 * @param	integer		$intServiceId							id of the service record
	 * @param	bool		$bolSilentFail							if FALSE, it will throw an exception, if the record can't be found (defaults to false)
	 * 																if TRUE, it will return NULL if the record can't be found
	 * @param	bool		$bolGetNewestRecordModellingService		if TRUE, then the newest record modelling this service (FNN on the Account that $intServiceId is associated with), is the one returned
	 * 																	Note, that this might not be the newest most record modelling this service, if the service has been moved to another account
	 * 																	by means of a Change of Lessee, or Change of Account action.
	 * 																if FALSE, then the record with id = $intServiceId is returned
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	public static function getForId($intServiceId, $bolSilentFail=FALSE, $bolGetNewestRecordModellingService=FALSE)
	{
		$objQuery = new Query();
		
		$intServiceId = intval($intServiceId);
		
		if ($bolGetNewestRecordModellingService)
		{
			// Retrieve the newest service record modelling this service (FNN) on the account that $intServiceId is associated with
			$strQuery = "SELECT s.* ". 
						"FROM Service AS s INNER JOIN Service AS s2 ON (s.FNN = s2.FNN AND s.Account = s2.Account) ".
						"WHERE s2.Id = $intServiceId ".
						"ORDER BY s.Id DESC ".
						"LIMIT 1;";
		}
		else
		{
			// Retrieve the Service record where id = $intServiceId
			$strQuery = "SELECT * ".
						"FROM Service ".
						"WHERE Id = $intServiceId;";
		}
		
		if (($mixResult = $objQuery->Execute($strQuery)) === FALSE)
		{
			throw new Exception(__METHOD__ ." Failed to retrieve Service record using query - $strQuery - ". $objQuery->Error());
		}
		
		$mixRecord = $mixResult->fetch_assoc();
		
		if ($mixRecord === NULL)
		{
			if ($bolSilentFail)
			{
				return NULL;
			}
			throw new Exception("Could not find Service with Service.Id = $intServiceId");
		}
		else
		{
			return new self($mixRecord);
		}
	}
	
	//------------------------------------------------------------------------//
	// onSaleItemCancellation
	//------------------------------------------------------------------------//
	/**
	 * onSaleItemCancellation()
	 *
	 * Handles Service related tasks that have to be carried out when a sale item associated with the service, is cancelled
	 *
	 * Handles Service related tasks that have to be carried out when a sale item associated with the service, is cancelled
	 * It is assumed the service is currently active or pending activation
	 * 
	 * @param	integer		$intEmployeeId		id of the employee who actioned the cancellation
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public function onSaleItemCancellation($intEmployeeId)
	{
		/* The following code sets the service to disconnected, however I don't think we should automatically do anything to the service when cancelling a sale,
		 * because a sale associated with a service, could represent the plan changing on the service, but nothing else.
		 */
		 
		/*if ($this->closedOn !== NULL)
		{
			throw new Exception("Cannot cancel a service that isn't currently active or pending activation");
		}

		$this->closedBy = $intEmployeeId;
		$this->natureOfClosure = SERVICE_CLOSURE_DISCONNECTED;
		
		if ($this->archived == SERVICE_PENDING)
		{
			// The service has not been activated yet.  Set the closedOn timestamp to be 1 second before the createdOn timestamp
			$this->closedOn = date("Y-m-d H:i:s", strtotime("-1 second {$this->createdOn}"));
		}
		else
		{
			// The service has already been activated.  Set the closedOn timestamp to now
			$this->closedOn = GetCurrentISODateTime();
		}
		
		$this->save();
		*/
	}
	
	/**
	 * getFNNInstances()
	 *
	 * Gets all Services which have the given FNN
	 * This does not consider FNNs in an indial 100 range, which aren't the primary FNN of the indial100
	 * 
	 * @param	string	$strFNN						The FNN to match
	 * @param	int		[ $intAccountId ]			Defaults to NULL.  If set to an account id, then only FNN Instances, associated with the account, will be returned
	 * @param	boolean	[ $bolAsArray ]				TRUE: Return arrays of Services; FALSE: Return Service Objects 
	 * 
	 * @return	array
	 *
	 * @method
	 */
	public static function getFNNInstances($strFNN, $intAccountId=null, $bolAsArray=true)
	{
		$selFNNInstances	= self::_preparedStatement('selFNNInstances');
		if ($selFNNInstances->Execute(array('FNN'=>$strFNN, 'AccountId'=>$intAccountId)) === false)
		{
			throw new Exception($selFNNInstances->Error());
		}
		
		if ($bolAsArray)
		{
			return $selFNNInstances->FetchAll();
		}
		else
		{
			$arrFNNInstances	= array();
			while ($arrFNNInstance = $selFNNInstances->Fetch())
			{
				$arrFNNInstances[]	= new Service($arrFNNInstance);
			}
			return $arrFNNInstances;
		}
	}
	
	/**
	 * getServiceAddress()
	 *
	 * Gets the Service Address details for this Service
	 * 
	 * @return	Service_Address
	 *
	 * @method
	 */
	public function getServiceAddress()
	{
		$selServiceAddress	= self::_preparedStatement("selServiceAddress");
		$mixResult			= $selServiceAddress->Execute($this->toArray());
		if ($mixResult === false)
		{
			throw new Exception($selServiceAddress->Error());
		}
		elseif ($arrServiceAddress = $selServiceAddress->Fetch())
		{
			return new Service_Address($arrServiceAddress);
		}
		else
		{
			// No Service Address Data
			return null;
		}
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceRatePlan", "*", "Service = <service_id> AND <effective_datetime> BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", 1);
					break;
				case 'selFNNInstances':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service", "*", "FNN = <FNN> AND (<AccountId> IS NULL OR Account = <AccountId>)", "Id ASC");
					break;
				case 'selServiceAddress':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceAddress", "*", "Service = <Id>");
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