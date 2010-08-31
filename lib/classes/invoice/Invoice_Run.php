<?php

//----------------------------------------------------------------------------//
// Invoice_Run
//----------------------------------------------------------------------------//
/**
 * Invoice_Run
 *
 * Models a record of the InvoiceRun table
 *
 * Models a record of the InvoiceRun table
 *
 * @class	Invoice_Run
 */
class Invoice_Run
{
	private	$_arrTidyNames		= array();
	private	$_arrProperties		= array();

	public	$intInvoiceDatetime;
	public	$strInvoiceDatetime;
	public	$intLastInvoiceDatetime;
	public	$strLastInvoiceDatetime;

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
	 * @param	array	$arrProperties 		[optional]	Associative array defining an invoice run with keys for each field of the InvoiceRun table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the Invoice with the passed Id
	 *
	 * @return	void
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('InvoiceRun');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];

		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : (($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)))
			{
				$arrProperties	= $selById->Fetch();
			}
			elseif ($selById->Error())
			{
				throw new Exception("DB ERROR: ".$selById->Error());
			}
			else
			{
				throw new Exception(__CLASS__." with Id {$intId} does not exist!");
			}
		}

		// Set Properties
		if (is_array($arrProperties))
		{
			foreach ($arrProperties as $strName=>$mixValue)
			{
				// Load from the Database
				$this->{$strName}	= $mixValue;
			}
		}
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Invoice_Run object with the id specified
	 *
	 * Returns the Invoice_Run object with the id specified
	 *
	 * @param	int 				$intId		id of the InvoiceRun record to retrieve
	 * @return	mixed 							Invoice_Run object	: if it exists
	 * 											NULL				: if it doesn't exist
	 * @method
	 */
	public static function getForId($intId)
	{
		static $selInvoiceRun;
		if (!isset($selInvoiceRun))
		{
			$selInvoiceRun = new StatementSelect("InvoiceRun", self::getColumns(), "Id = <InvoiceRunId>");
		}

		if (($mixRecCount = $selInvoiceRun->Execute(array("InvoiceRunId"=>$intId))) === FALSE)
		{
			throw new Exception("Failed to retrieve InvoiceRun details for id: $intId - ". $selInvoiceRun->Error());
		}

		if ($mixRecCount === 0)
		{
			// Could not find the InvoiceRun record
			return NULL;
		}
		else
		{
			// Found it
			return new self($selInvoiceRun->Fetch());
		}
	}

	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the InvoiceRun table
	 *
	 * Returns array defining the columns of the InvoiceRun table
	 *
	 * @return		array
	 * @method
	 */
	protected static function getColumns()
	{
		$arrTableDefinition	= DataAccess::getDataAccess()->FetchTableDefine('InvoiceRun');
		$arrColumns			= array_keys($arrTableDefinition['Column']);
		array_unshift($arrColumns, $arrTableDefinition['Id']);

		return $arrColumns;
	}

	public function __get($strName)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	protected function __set($strName, $mxdValue)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;

		if (array_key_exists($strName, $this->_arrProperties))
		{
			$this->_arrProperties[$strName]	= $mxdValue;

			if ($this->{$strName} !== $mxdValue)
			{
				$this->_saved = FALSE;
			}
		}
		else
		{
			$this->{$strName} = $mxdValue;
		}
	}

	//------------------------------------------------------------------------//
	// tidyName
	//------------------------------------------------------------------------//
	/**
	 * tidyName()
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * If the string is already in the xxxYxxZzz format, then it will not be changed
	 *
	 * @param	string	$strName
	 * @return	string
	 * @method
	 */
	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}


	//------------------------------------------------------------------------//
	// generateSingle
	//------------------------------------------------------------------------//
	/**
	 * generateSingle()
	 *
	 * Generates an Invoice Run for a single Account
	 *
	 * Generates an Invoice Run for a single Account
	 *
	 * @param	integer	$intCustomerGroup						The Customer Group to generate for
	 * @param	integer	$intInvoiceRunType						The invoice_run_type (eg. INVOICE_RUN_TYPE_SAMPLES)
	 * @param	integer	$intInvoiceDatetime						The effective Datetime for this Invoice Run, invoiceable items must have been BEFORE this!
	 * @param	integer	$intAccount								The Account Id to Invoice
	 *
	 * @method
	 */
	public function generateSingle($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $intAccount)
	{
		$intAccount	= (int)$intAccount;
		
		// Init variables
		$dbaDB					= DataAccess::getDataAccess();
		
		if	(
				!(
					$intInvoiceRunType === INVOICE_RUN_TYPE_INTERIM
					|| $intInvoiceRunType === INVOICE_RUN_TYPE_INTERIM_FIRST
					|| $intInvoiceRunType === INVOICE_RUN_TYPE_FINAL
					|| $intInvoiceRunType === INVOICE_RUN_TYPE_SAMPLES
				)
			)
		{
			throw new Exception("InvoiceRun::generateSingle() only supports Interim, Final and Sample Invoice Runs");
		}

		$qryQuery	= new Query();
		$resAccount	= $qryQuery->Execute("SELECT * FROM Account WHERE Id = {$intAccount}");
		if ($resAccount === false)
		{
			throw new Exception($qryQuery->Error());
		}
		if ($arrAccount = $resAccount->fetch_assoc())
		{
			// If there are any Temporary InvoiceRuns with only this Account in them, then Revoke
			$resSoloInvoiceRuns	= $qryQuery->Execute(	"SELECT InvoiceRun.* " .
														"FROM Invoice JOIN InvoiceRun ON Invoice.invoice_run_id = InvoiceRun.Id " .
														"WHERE Invoice.Account = {$intAccount} AND InvoiceRun.invoice_run_type_id IN (".INVOICE_RUN_TYPE_INTERIM.", ".INVOICE_RUN_TYPE_FINAL.", ".INVOICE_RUN_TYPE_INTERIM_FIRST.") AND InvoiceRun.invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
			if ($resSoloInvoiceRuns === false)
			{
				throw new Exception($qryQuery->Error());
			}
			while ($arrSoloInvoiceRun = $resSoloInvoiceRuns->fetch_assoc())
			{
				$objSoloInvoiceRun	= new Invoice_Run($arrSoloInvoiceRun);
				$objSoloInvoiceRun->revoke();
			}

			// Calculate Billing Period
			$this->calculateBillingPeriodDates(date("Y-m-d", $intInvoiceDatetime), $intAccount);

			// Generate the Single Invoice
			$this->generate($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, array($arrAccount));
		}
		else
		{
			throw new Exception("There is no Account with Id '{$intAccount}'");
		}
	}


	//------------------------------------------------------------------------//
	// generateCustomerGroup
	//------------------------------------------------------------------------//
	/**
	 * generateCustomerGroup()
	 *
	 * Generates an Invoice Run for a Customer Group
	 *
	 * Generates an Invoice Run for a Customer Group
	 *
	 * @param	integer	$intCustomerGroup						The Customer Group to generate for
	 * @param	integer	$intInvoiceRunType						The invoice_run_type (eg. INVOICE_RUN_TYPE_SAMPLES)
	 * @param	integer	$intInvoiceDatetime						The effective Datetime for this Invoice Run, invoiceable items must have been BEFORE this!
	 * @param	integer	$intScheduledInvoiceRun		[optional]	The invoice_run_schedule.id to Run
	 *
	 * @method
	 */
	public function generateCustomerGroup($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $intScheduledInvoiceRun=null)
	{
		$intAccount	= (int)$intAccount;

		// Init variables
		$dbaDB					= DataAccess::getDataAccess();

		if (($intInvoiceRunType === INVOICE_RUN_TYPE_INTERIM) || ($intInvoiceRunType === INVOICE_RUN_TYPE_FINAL) || ($intInvoiceRunType === INVOICE_RUN_TYPE_INTERIM_FIRST))
		{
			throw new Exception("InvoiceRun::generateCustomerGroup() does not support Interim or Final Invoice Runs");
		}

		$this->BillingDate				= date("Y-m-d", $intInvoiceDatetime);
		$this->customer_group_id		= $intCustomerGroup;

		// Retrieve a list of Accounts to be Invoiced
		Log::getLog()->log(" * Getting list of Accounts to Invoice...");
		$selInvoiceableAccounts	= self::_preparedStatement('selInvoiceableAccounts');
		if ($selInvoiceableAccounts->Execute($this->toArray()) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableAccounts->Error());
		}

		// If there are any Temporary InvoiceRuns for this Customer Group, then Revoke them
		Invoice_Run::revokeByCustomerGroup($intCustomerGroup);

		// Generate the Billing Period Dates
		$this->calculateBillingPeriodDates(date("Y-m-d", $intInvoiceDatetime));

		$this->generate($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $selInvoiceableAccounts->FetchAll(), $intScheduledInvoiceRun);
	}

	public function generateForAccounts($iCustomerGroupId, $aAccountObjects, $iInvoiceRunType)
	{
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			throw new Exception("There was an internal error in Flex.  Please notify YBS of this issue with the following message: 'Unable to start the inner Transaction' ({$oFlexDataAccess->refMysqliConnection->error}).");
		}
		try
		{
			// Verify that the invoice run type is interim first
			if (!in_array($iInvoiceRunType, array(INVOICE_RUN_TYPE_INTERIM_FIRST)))
			{
				throw new Exception("Invoice Run Type is not allowed.");
			}
			
			// Validate account list
			$aAccountArrays	= array();
			foreach ($aAccountObjects as $oAccount)
			{
				// Is it in the right customer group?
				if ($oAccount->CustomerGroup != $iCustomerGroupId)
				{
					throw new Exception("All accounts must belong to the same customer group: Should be '{$iCustomerGroupId}' where Account '{$oAccount->Id}' was '{$oAccount->CustomerGroup}' instead}.");
				}
				// Is it able to be invoiced?
				else if (!Account_Status::getForId($oAccount->Archived)->can_invoice)
				{
					throw new Exception("Account {$oAccount->Id} cannot be invoiced.");
				}
				$aAccountArrays[]	= $oAccount->toArray();
			}
			
			$iInvoiceDateTime			= strtotime(date('Y-m-d', strtotime('+1 day')));
			$this->BillingDate			= date('Y-m-d', $iInvoiceDateTime);
			$this->customer_group_id	= $iCustomerGroupId;
			$this->calculateBillingPeriodDates(date("Y-m-d", $iInvoiceDateTime));
			$this->generate($iCustomerGroupId, $iInvoiceRunType, $iInvoiceDateTime, $aAccountArrays);
			
			$oDataAccess->TransactionCommit();
		}
		catch (Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			throw new Exception("Failed to generate invoice run for list of accounts. ".$oException->getMessage());
		}
	}

	//------------------------------------------------------------------------//
	// generate
	//------------------------------------------------------------------------//
	/**
	 * generate()
	 *
	 * Generates an Invoice Run
	 *
	 * Generates an Invoice Run
	 *
	 * @param	integer	$intCustomerGroup						The Customer Group to generate for
	 * @param	integer	$intInvoiceRunType						The invoice_run_type (eg. INVOICE_RUN_TYPE_SAMPLES)
	 * @param	integer	$intInvoiceDatetime						The effective Datetime for this Invoice Run, invoiceable items must have been BEFORE this!
	 * @param	array	$arrAccounts							Array of Accounts to Invoice
	 * @param	integer	$intScheduledInvoiceRun		[optional]	The invoice_run_schedule.id to Run
	 *
	 * @method
	 */
	public function generate($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $arrAccounts, $intScheduledInvoiceRun=NULL)
	{
		// Init variables
		static	$dbaDB;
		$dbaDB	= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();
		
		$fStopwatchStart	= microtime(true);
		Log::getLog()->log("\t* Invoice Run processing started at ".date("Y-m-d H:i:s", (int)$fStopwatchStart));
		
		// Create InvoiceRun record
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
			//------------------- START INVOICE RUN GENERATION -------------------//
			$aMicrotime	= explode(' ', microtime());
			
			// Create the initial InvoiceRun record
			$this->BillingDate				= date("Y-m-d", $intInvoiceDatetime);
			$this->InvoiceRun				= date("YmdHis").$aMicrotime[0];
			$this->invoice_run_type_id		= $intInvoiceRunType;
			$this->invoice_run_schedule_id	= $intScheduledInvoiceRun;
			$this->invoice_run_status_id	= INVOICE_RUN_STATUS_GENERATING;
			$this->customer_group_id		= $intCustomerGroup;
			
			$fltPreviousInvoiceRunBalance		= 0.0;
			$fltTotalPreviousInvoiceRunsBalance	= 0.0;
			$selInvoiceBalanceHistory			= self::_preparedStatement('selInvoiceBalanceHistory');
			$intRecordCount						= $selInvoiceBalanceHistory->Execute(Array('invoice_run_id'=>$this->Id, 'customer_group_id'=>$this->customer_group_id));
			if ($intRecordCount === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selInvoiceBalanceHistory->Error());
			}
			elseif ($intRecordCount == 0)
			{
				// There are no previous live AND committed invoice runs for this customer group
				$fltPreviousInvoiceRunBalance		= 0.0;
				$fltTotalPreviousInvoiceRunsBalance	= 0.0;
			}
			else
			{
				// There is at least 1 previous live and committed invoice run for this customer group
				// Note that the newly generated invoice run will not be considered because it has not been committed yet
				$arrPreviousBalanceTotal			= $selInvoiceBalanceHistory->Fetch();
				$fltPreviousInvoiceRunBalance		= $arrPreviousBalanceTotal['TotalBalance'];
				$fltTotalPreviousInvoiceRunsBalance	= $fltPreviousInvoiceRunBalance;
				
				while (($arrInvoiceRunBalanceTotal = $selInvoiceBalanceHistory->Fetch()) !== FALSE)
				{
					$fltTotalPreviousInvoiceRunsBalance += $arrInvoiceRunBalanceTotal['TotalBalance'];
				}
			}
			
			$this->previous_balance			= $fltPreviousInvoiceRunBalance;
			$this->total_balance			= $fltTotalPreviousInvoiceRunsBalance;
			
			$this->save();
			
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
		
		// Generate an Invoice for each Account
		$this->InvoiceCount	= 0;
		foreach ($arrAccounts as $arrAccount)
		{
			$objAccount	= new Account($arrAccount);
			Log::getLog()->log(" + Generating Invoice for {$objAccount->Id}...");
			
			$objInvoice	= Invoice::generateForInvoiceRunAndAccount($this, $objAccount);
			
			$this->InvoiceCount++;
		}
		
		// Generated Balance Data
		$this->calculateTotals();
		
		// Update Status
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
			$this->invoice_run_status_id	= INVOICE_RUN_STATUS_TEMPORARY;
			$this->save();
			
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
		
		$fStopwatchCommit	= microtime(true);
		Log::getLog()->log("\t* Total Invoice Run processing time: ".($fStopwatchCommit - $fStopwatchStart)."s...");
		//--------------------------------------------------------------------//
	}
	
	public function calculateTotals()
	{
		static	$dbaDB;
		$dbaDB	= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();
		
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
			$selInvoiceTotals	= self::_preparedStatement('selInvoiceTotals');
			if ($selInvoiceTotals->Execute(Array('invoice_run_id'=>$this->Id)) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selInvoiceTotals->Error());
			}
			$arrInvoiceTotals	= $selInvoiceTotals->Fetch();
	
			$selInvoiceCDRTotals	= self::_preparedStatement('selInvoiceCDRTotals');
			if ($selInvoiceCDRTotals->Execute(Array('invoice_run_id'=>$this->Id)) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selInvoiceCDRTotals->Error());
			}
			$arrInvoiceCDRTotals	= $selInvoiceCDRTotals->Fetch();
			
			// Finalised InvoiceRun record
			$this->BillCost					= $arrInvoiceCDRTotals['BillCost'];
			$this->BillRated				= $arrInvoiceCDRTotals['BillRated'];
			$this->BillInvoiced				= $arrInvoiceTotals['BillInvoiced'];
			$this->BillTax					= $arrInvoiceTotals['BillTax'];
			$this->save();
			
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
	}
	
	//------------------------------------------------------------------------//
	// revokeAll
	//------------------------------------------------------------------------//
	/**
	 * revokeAll()
	 *
	 * Revokes all Temporary Invoice Runs
	 *
	 * Revokes all Temporary Invoice Runs
	 *
	 * @method
	 */
	public static function revokeAll()
	{
		//Log::getLog()->log(" * ENTERING RevokeAll...");

		// Select all Temporary InvoiceRuns
		$selTemporaryInvoiceRuns	= self::_preparedStatement('selTemporaryInvoiceRuns');
		if ($selTemporaryInvoiceRuns->Execute() === FALSE)
		{
			throw new Exception("DB ERROR: ".$selTemporaryInvoiceRuns->Error());
		}
		while ($arrInvoiceRun = $selTemporaryInvoiceRuns->Fetch())
		{
			// Revoke each Invoice Run
			$objInvoiceRun = new Invoice_Run($arrInvoiceRun);
			Log::getLog()->log(" + Revoking Invoice Run with Id {$objInvoiceRun->Id}...");
			$objInvoiceRun->revoke();
		}
	}



	//------------------------------------------------------------------------//
	// revokeByCustomerGroup
	//------------------------------------------------------------------------//
	/**
	 * revokeByCustomerGroup()
	 *
	 * Revokes all Temporary Invoice Runs for a CustomerGroup
	 *
	 * Revokes all Temporary Invoice Runs for a CustomerGroup
	 *
	 * @param	integer	$intCustomerGroup				The CustomerGroup to revoke for
	 *
	 * @method
	 */
	public static function revokeByCustomerGroup($intCustomerGroup)
	{
		//Log::getLog()->log(" + ENTERING revokeByCustomerGroup({$intCustomerGroup})...");

		// Select all Temporary InvoiceRuns for this CustomerGroup
		$selTemporaryInvoiceRunsByCustomerGroup	= self::_preparedStatement('selTemporaryInvoiceRunsByCustomerGroup');
		if ($selTemporaryInvoiceRunsByCustomerGroup->Execute(Array('customer_group_id' => $intCustomerGroup)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selTemporaryInvoiceRunsByCustomerGroup->Error());
		}
		while ($arrInvoiceRun =$selTemporaryInvoiceRunsByCustomerGroup->Fetch())
		{
			// Revoke each Invoice Run
			$objInvoiceRun	= new Invoice_Run($arrInvoiceRun);
			$objInvoiceRun->revoke();
		}
	}

	//------------------------------------------------------------------------//
	// revoke
	//------------------------------------------------------------------------//
	/**
	 * revoke()
	 *
	 * Revokes a Temporary Invoice Run
	 *
	 * Revokes a Temporary Invoice Run
	 *
	 * @method
	 */
	public function revoke($bolOptimised=true)
	{
		static	$dbaDB;
		$dbaDB	= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();
		
		Log::getLog()->log(" * Revoking Invoice Run with Id {$this->Id}...");
		
		// Is this InvoiceRun Temporary?
		if (!in_array($this->invoice_run_status_id, Array(INVOICE_RUN_STATUS_TEMPORARY, INVOICE_RUN_STATUS_GENERATING)))
		{
			// No, throw an Exception
			throw new Exception("InvoiceRun '{$this->Id}' is not a Temporary InvoiceRun!");
		}
		
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
			// Revoke Invoice Run as a whole
			$this->_revokeOptimised();
			
			// Commit the Transaction
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
	}
	
	private function _revokeOptimised()
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		// Change CDR Statuses back to CDR_RATED
		$updCDRRevoke	= self::_preparedStatement('updCDRRevoke');
		if ($updCDRRevoke->Execute(Array('invoice_run_id'=>NULL, 'Status'=>CDR_RATED), $this->toArray()) === FALSE)
		{
			throw new Exception($updCDRRevoke->Error());
		}

		// Remove Billing-Time Charges
		$arrModules	= Billing_Charge::getModules();
		foreach ($arrModules as $intCustomerGroup=>$arrModuleTypes)
		{
			foreach ($arrModuleTypes as $strModuleType=>$arrModules)
			{
				foreach ($arrModules as $chgModule)
				{
					// Revoke charge
					$mixResult = $chgModule->RevokeAll($this);
				}
			}
		}

		// Remove Plan Charges
		if ($qryQuery->Execute("DELETE FROM Charge WHERE ChargeType IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND CreatedBy IS NULL AND invoice_run_id = {$this->Id}") === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}

		// Change Charge Statuses back to CHARGE_APPROVED
		$updChargeRevoke	= self::_preparedStatement('updChargeRevoke');
		if ($updChargeRevoke->Execute(Array('Status' => CHARGE_APPROVED, 'invoice_run_id' => NULL), $this->toArray()) === FALSE)
		{
			throw new Exception($updChargeRevoke->Error());
		}

		// Remove service_total_service Records
		if ($qryQuery->Execute("DELETE FROM service_total_service WHERE service_total_id = (SELECT Id FROM ServiceTotal WHERE invoice_run_id = {$this->Id} AND Id = service_total_id)") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove ServiceTotal Records
		if ($qryQuery->Execute("DELETE FROM ServiceTotal WHERE invoice_run_id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove ServiceTypeTotal Records
		if ($qryQuery->Execute("DELETE FROM ServiceTypeTotal WHERE invoice_run_id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove Invoice Record
		if ($qryQuery->Execute("DELETE FROM Invoice WHERE invoice_run_id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Remove entry from the InvoiceRun table
		Log::getLog()->log(" * Removing Invoice Run with Id {$this->Id}");
		if ($qryQuery->Execute("DELETE FROM InvoiceRun WHERE Id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
	}

	/**
	 * commit()
	 *
	 * Commits a Temporary Invoice Run
	 *
	 * @method
	 */
	public function commit($bolOptimised=true)
	{
		Log::getLog()->log(" * Committing Invoice Run with Id {$this->Id}...");
		
		// Is this InvoiceRun Temporary?
		if ($this->invoice_run_status_id !== INVOICE_RUN_STATUS_TEMPORARY)
		{
			// No, throw an Exception
			throw new Exception("InvoiceRun '{$this->Id}' is not a Temporary InvoiceRun!");
		}
		
		$dbaDB	= DataAccess::getDataAccess();
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
			// Commit Invoice Run as a whole
			$this->_commitOptimised();
			
			// Commit the Transaction
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
	}
	
	// deliver: Generates the pdf's for all invoices within this invoice run, tar's them up and then creates a 
	// correspondence run so that they are delivered to the mail house
	public function deliver()
	{
		$sInvoiceRunPDFBasePath	= PATH_INVOICE_PDFS ."pdf/$this->Id/";
		
		// Generate pdf's
		$aInvoices		= Invoice::getForInvoiceRunId($this->Id);
		$aPDFFilenames	= array();
		foreach ($aInvoices as $iId => $oInvoice)
		{
			// Generate the PDF file
			$iCreatedOn = strtotime("-1 month", strtotime($oInvoice->CreatedOn));
			$iYear 		= (int)date("Y", $iCreatedOn);
			$iMonth 	= (int)date("m", $iCreatedOn);
			GetPDFContent($oInvoice->Account, $iYear, $iMonth, $iId, $this->Id);
			$aPDFFilenames[$iId]	= $sInvoiceRunPDFBasePath.GetPdfFilename($oInvoice->Account, $iYear, $iMonth, $oInvoice->Id, $this->Id);
		}
		
		// Create tar file
		require_once("Archive/Tar.php");
		$oTar		= new Archive_Tar($sInvoiceRunPDFBasePath."{$this->Id}.tar");
		$aFiles		= array_values($aPDFFilenames);
		if (!$oTar->createModify($aFiles, '', $sInvoiceRunPDFBasePath))
		{
			 throw new Exception("Failed to create tar file for invoice run {$this->Id}. Files = ".print_r($aFiles, true));
		}
		
		// Generate the correspondence data
		$aCorrespondenceData	= array();
		foreach ($aPDFFilenames as $iInvoiceId => $sPDFFilename)
		{
			// Determine the correspondence_delivery_method for the invoice
			$oInvoice			= $aInvoices[$iInvoiceId];
			$iDeliveryMethod	= null;
			switch ($oInvoice->DeliveryMethod)
			{
				case DELIVERY_METHOD_POST:
					$iDeliveryMethod	= CORRESPONDENCE_DELIVERY_METHOD_POST;
					break;
				case DELIVERY_METHOD_EMAIL_SENT:
				case DELIVERY_METHOD_EMAIL:
					$iDeliveryMethod	= CORRESPONDENCE_DELIVERY_METHOD_EMAIL;
					break;
			}
			
			if (is_null($iDeliveryMethod))
			{
				// No appropriate correspondence_delivery_method was found. The invoice is not to be delivered, skip it.
				continue;
			}
			
			// Cache the correspondence data for the invoice
			$oAccount				= Account::getForId($oInvoice->Account);
			$oContact				= Contact::getForId($oAccount->PrimaryContact);
			$aCorrespondenceData[]	= 	array(
											'account_id'						=> $oAccount->Id,
											'correspondence_delivery_method_id'	=> $iDeliveryMethod,
											'title'								=> $oContact->title,
											'first_name'						=> $oContact->firstName,
											'last_name'							=> $oContact->lastName,
											'address_line_1'					=> $oAccount->Address1,
											'address_line2'						=> $oAccount->Address2,
											'suburb'							=> $oAccount->Suburb,
											'postcode'							=> $oAccount->Postcode,
											'state'								=> $oAccount->State,
											'email'								=> $oContact->Email,
											'mobile'							=> $oContact->Mobile,
											'landline'							=> $oContact->Phone,
											'tar_file_path'						=> basename($aPDFFilenames[$iInvoiceId])
										);
		}
		
		//echo "<pre>".print_r($aCorrespondenceData, true)."</pre>";
		
		/*
		// Create correspondence run 
		$oSource	= new Correspondeonce_Source_CSV($aCorrespondenceData);
		$oTemplate	= new Correspondence_Template::getForSystemName('INVOICE');
		$oRun		= $oTemplate->createRun();
		$oRun->save();*/
	}
	
	private function _commitOptimised()
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		// Commit the CDRs
		Log::getLog()->log(" * Committing CDRs...");
		$resCommitCDRs	= $qryQuery->Execute("UPDATE CDR SET Status = ".CDR_INVOICED." WHERE Status = ".CDR_TEMP_INVOICE." AND invoice_run_id = {$this->Id}");
		if ($resCommitCDRs === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}
		
		// Commit the Charges
		Log::getLog()->log(" * Committing Charges...");
		$resCommitCharges	= $qryQuery->Execute("UPDATE Charge SET Status = ".CHARGE_INVOICED." WHERE Status = ".CHARGE_TEMP_INVOICE." AND invoice_run_id = {$this->Id}");
		if ($resCommitCharges === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}
		
		//------------------------------ ACCOUNT -----------------------------//
		Log::getLog()->log(" * Updating Accounts...");
		$resUpdateAccounts	= $qryQuery->Execute(	"UPDATE Account JOIN Invoice ON Account.Id = Invoice.Account \n" .
													"SET Account.LastBilled = {$this->BillingDate}, Account.Sample = (CASE WHEN Account.Sample < 0 THEN Account.Sample + 1 ELSE Account.Sample END) \n" .
													"WHERE Invoice.invoice_run_id = {$this->Id}");
		if ($resUpdateAccounts === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}
		
		//------------------------------ SERVICE -----------------------------//
		Log::getLog()->log(" * Updating Services...");
		$resUpdateInvoices	= $qryQuery->Execute(	"UPDATE (ServiceTotal JOIN service_total_service ON ServiceTotal.Service = service_total_service.service_total_id) JOIN Service ON Service.Id = service_total_service.service_id " .
													" SET Service.discount_start_datetime = NULL, cdr_count = NULL, cdr_amount = NULL " .
													" WHERE invoice_run_id = {$this->Id}");
		if ($resUpdateInvoices === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}
		
		//------------------------------ INVOICE -----------------------------//
		Log::getLog()->log(" * Updating Invoices...");
		$resUpdateInvoices	= $qryQuery->Execute(	"UPDATE Invoice \n" .
													"SET Status = (CASE WHEN Balance > 0 THEN ".INVOICE_COMMITTED." ELSE ".INVOICE_SETTLED." END) \n" .
													"WHERE invoice_run_id = {$this->Id}");
		if ($resUpdateInvoices === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}
		
		// Finalise entry in the InvoiceRun table
		Log::getLog()->log(" * Finalising Invoice Run with Id {$this->Id}");
		$this->invoice_run_status_id	= INVOICE_RUN_STATUS_COMMITTED;
		$this->save();
	}

	//------------------------------------------------------------------------//
	// export
	//------------------------------------------------------------------------//
	/**
	 * export()
	 *
	 * Exports an Invoice Run to various formats
	 *
	 * @return		void
	 *
	 * @constructor
	 */
	public function export($aAccounts=null, $aExportModules=null)
	{
		Log::getLog()->log(" * Exporting Invoice Run {$this->Id}".((is_array($aAccounts) && count($aAccounts)) ? " (Accounts: ".implode(', ', $aAccounts) : '').")...");
	
		// Export Invoice Run as a whole
		$aCarrierModules	= Invoice_Run_Export::getModulesForCustomerGroup($this->customer_group_id);
		/*
		Log::getLog()->log(print_r($aAccounts, true));
		Log::getLog()->log(print_r($aExportModules, true));
		Log::getLog()->log(print_r($aCarrierModules, true));
		*/
		foreach ($aCarrierModules as $oCarrierModule)
		{
			$sInvoiceRunExportClass	= $oCarrierModule->Module;
			if ($aExportModules === null || in_array($sInvoiceRunExportClass, $aExportModules))
			{
				Log::getLog()->log("\t+ Module: {$sInvoiceRunExportClass}");
				
				$oInvoiceRunExport		= new $sInvoiceRunExportClass($this, $oCarrierModule);
				$oInvoiceRunExport->export((is_array($aAccounts) ? $aAccounts : null));
			}
		}
	}

	//------------------------------------------------------------------------//
	// checkTemporary()
	//------------------------------------------------------------------------//
	/**
	 * checkTemporary()
	 *
	 * Checks if there are any Gold Temporary Invoice Runs active (or an Interim or Final invoice run specific to an account)
	 *
	 * Checks if there are any Gold Temporary Invoice Runs active (or an Interim or Final invoice run specific to an account)
	 *
	 * @param	integer	$intCustomerGroupId		[optional]	The Customer Group to check for
	 * @param	integer	$intAccountId			[optional]	The Account to check for
	 *
	 * @return	boolean							TRUE if there is, FALSE if there isn't
	 *
	 * @method
	 */
	public static function checkTemporary($intCustomerGroupId=null, $intAccountId=null)
	{
		$selCheckTemporaryInvoiceRun	= self::_preparedStatement('selCheckTemporaryInvoiceRun');
		$mixResult						= $selCheckTemporaryInvoiceRun->Execute(Array('CustomerGroup'=>$intCustomerGroupId, 'Account'=>$intAccountId));
		if ($mixResult === FALSE)
		{
			throw new Exception($selCheckTemporaryInvoiceRun->Error());
		}
		else
		{
			return (bool)$mixResult;
		}
	}

	/**
	 * calculateBillingPeriodDates()
	 *
	 * Calculates the Billing Period Dates for this Invoice Run
	 *
	 * @param	[integer	$intAccount]
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	public function calculateBillingPeriodDates($strInvoiceDate=null, $intAccount=null)
	{
		$intInvoiceDatetime				= strtotime($strInvoiceDate);
		$this->intInvoiceDatetime		= $intInvoiceDatetime;
		$this->strInvoiceDatetime		= date("Y-m-d H:i:s", $intInvoiceDatetime);

		// Retrieve the Bill Date of the last Invoice Run...
		Log::getLog()->log(" * Billing Period Start Date\t: ", false);

		if ($intAccount > 0)
		{
			$objAccount						= new Account(array('Id'=>$intAccount), false, true);
			$this->strLastInvoiceDatetime	= $objAccount->getBillingPeriodStart($strInvoiceDate);
		}
		else
		{
			$this->strLastInvoiceDatetime	= Invoice_Run::getLastInvoiceDateByCustomerGroup($this->customer_group_id, $this->BillingDate);
		}

		$this->intLastInvoiceDatetime	= strtotime($this->strLastInvoiceDatetime);
		Log::getLog()->log($this->strLastInvoiceDatetime);

		Log::getLog()->log(" * Billing Period End Date\t: {$this->strInvoiceDatetime}");

		// Set the Billing Period
		$this->billing_period_start_datetime	= $this->strLastInvoiceDatetime;
		$this->billing_period_end_datetime		= date("Y-m-d H:i:s", strtotime("-1 second", $this->intInvoiceDatetime));
	}

	/**
	 * getLastInvoiceDateByCustomerGroup()
	 *
	 * Retrieves (or calculates) the Last Invoice Date for a Customer Group
	 *
	 * @return	string									Date of the last Invoice Run
	 *
	 * @method
	 */
	public static function getLastInvoiceDateByCustomerGroup($intCustomerGroup, $strEffectiveDate=null)
	{
		$strEffectiveDate	= ($strEffectiveDate) ? $strEffectiveDate : date('Y-m-d');
		
		//Debug('CustomerGroup: '.$intCustomerGroup);
		//Debug('EffectiveDate: '.$strEffectiveDate);
		$selPaymentTerms	= self::_preparedStatement('selPaymentTerms');

		$selInvoiceRun	= self::_preparedStatement('selLastInvoiceRunByCustomerGroup');
		if ($selInvoiceRun->Execute(Array('customer_group_id' => $intCustomerGroup, 'EffectiveDate'=>$strEffectiveDate)))
		{
			// We have an old InvoiceRun
			$arrLastInvoiceRun	= $selInvoiceRun->Fetch();
			//Debug('Old Invoice Run: '.$arrLastInvoiceRun['BillingDate']);
			return $arrLastInvoiceRun['BillingDate'] . ' 00:00:00';
		}
		elseif ($selInvoiceRun->Error())
		{
			throw new Exception("DB ERROR: ".$selInvoiceRun->Error());
		}
		elseif ($selPaymentTerms->Execute(Array('customer_group_id' => $intCustomerGroup)))
		{
			$arrPaymentTerms	= $selPaymentTerms->Fetch();
			//Debug('Invoice Day: '.$arrPaymentTerms['invoice_day']);

			// No InvoiceRuns, so lets calculate when it should have been
			$strDay = $arrPaymentTerms['invoice_day'];
			$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00", strtotime($strEffectiveDate)));
			//Debug('Day in Effective Date: '.(int)date("d", strtotime($strEffectiveDate)));
			if ((int)date("d", strtotime($strEffectiveDate)) < $arrPaymentTerms['invoice_day'])
			{
				// Billing Date is last Month
				//Debug('LAST MONTH');
				$intInvoiceDatetime	= strtotime("-1 month", $intInvoiceDatetime);
			}
			//Debug('Predicted Last Bill: '.date("Y-m-d H:i:s", $intInvoiceDatetime));
			return date("Y-m-d H:i:s", $intInvoiceDatetime);
		}
		elseif ($selPaymentTerms->Error())
		{
			throw new Exception("DB ERROR: ".$selPaymentTerms->Error());
		}
		else
		{
			throw new Exception("No Payment Terms specified for Customer Group {$intCustomerGroup}");
		}
	}

	/**
	 * predictNextInvoiceDate()
	 *
	 * Predicts the next Invoice Date for a Customer Group
	 *
	 * @param	integer	$intCustomerGroup				Customer Group to predict for
	 * @param	[string	$strDate			]			Date to predict from (Default: Today)
	 *
	 * @return	string									Date of the next Invoice Run
	 *
	 * @method
	 */
	public static function predictNextInvoiceDate($intCustomerGroup, $strEffectiveDate=null)
	{
		// Set default Date
		$strEffectiveDate	= (strtotime($strEffectiveDate)) ? $strEffectiveDate : date("Y-m-d");

		$selPaymentTerms	= self::_preparedStatement('selPaymentTerms');
		if ($selPaymentTerms->Execute(Array('customer_group_id' => $intCustomerGroup)))
		{
			$arrPaymentTerms	= $selPaymentTerms->Fetch();
		}
		elseif ($selPaymentTerms->Error())
		{
			throw new Exception("DB ERROR: ".$selPaymentTerms->Error());
		}
		else
		{
			throw new Exception("No Payment Terms specified for Customer Group {$intCustomerGroup}");
		}

		$strDay				= str_pad($arrPaymentTerms['invoice_day'], 2, '0', STR_PAD_LEFT);
		$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00", strtotime($strEffectiveDate)));
		if ((int)date("d", strtotime($strEffectiveDate)) > $arrPaymentTerms['invoice_day'])
		{
			// Billing Date is next Month
			$intInvoiceDatetime	= strtotime("+1 month", $intInvoiceDatetime);
		}
		return date("Y-m-d H:i:s", $intInvoiceDatetime);
	}

	/**
	 * archiveToCDRInvoiced()
	 *
	 * Archives a given Invoice Run's CDRs to the cdr_invoiced Table
	 *
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	public function archiveToCDRInvoiced()
	{
		static	$qryQuery;
		static	$dsCDRInvoiced;
		$qryQuery		= ($qryQuery) ? $qryQuery : new Query();
		$dsCDRInvoiced	= ($dsCDRInvoiced) ? $dsCDRInvoiced : Data_Source::get('cdr');

		// Determine the SQL Dump FileName
		$strCDRDumpFileName	= "cdr_invoiced_{$this->Id}.sql";
		$strCDRDumpFilePath	= FILES_BASE_PATH.$strCDRDumpFileName;

		// Dump the Invoiced CDRs using the mysqldump tool
		switch ($GLOBALS['**arrDatabase']['flex']['Type'])
		{
			case 'mysqli':
			case 'mysql':
				$strCommand	= "mysqldump -u {$GLOBALS['**arrDatabase']['flex']['User']} --password={$GLOBALS['**arrDatabase']['flex']['Password']} -h {$GLOBALS['**arrDatabase']['flex']['URL']} {$GLOBALS['**arrDatabase']['flex']['Database']} -t CDR --where='invoice_run_id = {$this->Id}' > {$strCDRDumpFilePath}";
				break;

			default:
				throw new Exception("Flex Databases of type '{$GLOBALS['**arrDatabase']['cdr']['Type']}' are not supported for archiving");
		}

		exec($strCommand);
		if (!@filesize($strCDRDumpFilePath))
		{
			throw new Exception("There was an error dumping the Invoiced CDRs (File Not Found/Bad Filesize)");
		}

		// Import the CDRs into the CDR Invoiced database/table
		switch ($GLOBALS['**arrDatabase']['cdr']['Type'])
		{
			case 'mysqli':
			case 'mysql':
				// Make the dump file MySQL-compatible
				$strMySQLFileName	= dirname($strCDRDumpFileName).'/'.basename($strCDRDumpFileName, 'sql').'mysql';
				exec("perl -pi -e 's/`cdr`/`CDRInvoiced`/' {$strCDRDumpFilePath}");
				if (!@filesize($strCDRDumpFilePath))
				{
					throw new Exception("There was an error converting the Invoiced CDRs to MySQL (Table Name Conversion)");
				}
				exec("grep \"INSERT INTO\" {$strCDRDumpFilePath} > {$strMySQLFileName}");
				if (!@filesize($strMySQLFileName))
				{
					throw new Exception("There was an error converting the Invoiced CDRs to MySQL (Excess Stripping)");
				}

				// Import the data
				$arrOutput	= array();
				$intReturn	= null;
				exec("psql -U {$GLOBALS['**arrDatabase']['cdr']['User']} -h {$GLOBALS['**arrDatabase']['cdr']['URL']} {$GLOBALS['**arrDatabase']['cdr']['Database']} < {$strMySQLFileName} ", $arrOutput, $intReturn);

				if ($intReturn)
				{
					throw new Exception("There was an error importing '{$strMySQLFileName}':\n\n ".implode("\n", $arrOutput));
				}
				break;

			case 'pgsql':
				$dsSalesPortal->beginTransaction();
				try
				{
					// Make the MySQL dump file PGSQL-Compatible
					$strPGSQLFileName	= dirname($strCDRDumpFileName).'/'.basename($strCDRDumpFileName, 'sql').'pgsql';
					exec("perl -pi -e 's/`cdr`/cdr_invoiced_{$this->Id}/' {$strCDRDumpFilePath}");
					if (!@filesize($strCDRDumpFilePath))
					{
						throw new Exception("There was an error converting the Invoiced CDRs to Postgres (Table Name Conversion)");
					}
					exec("perl ".FLEX_BASE_PATH."../../bin/mysql2pgsql.pl {$strCDRDumpFilePath} {$strPGSQLFileName}");
					if (!@filesize($strMySQLFileName))
					{
						throw new Exception("There was an error converting the Invoiced CDRs to Postgres (mysql2postgres)");
					}
					exec("grep \"INSERT INTO\" {$strCDRDumpFilePath} > {$strPGSQLFileName}");
					if (!@filesize($strMySQLFileName))
					{
						throw new Exception("There was an error converting the Invoiced CDRs to Postgres (Excess Stripping)");
					}

					$strTableName	= "cdr_invoiced_{$this->Id}";

					// Create the cdr_invoiced_* partition table
					$resCreateTable			= $dsCDRInvoiced->exec("CREATE TABLE {$strTableName} (CHECK (invoice_run_id = {$this->Id})) INHERITS (cdr_invoiced);");
					if (PEAR::isError($resCreateTable))
					{
						throw new Exception($resCreateTable->getMessage()." :: ".$resCreateTable->getUserInfo());
					}
					// Set its Owner
					$resOwner				= $dsCDRInvoiced->exec("ALTER TABLE ONLY {$strTableName} OWNER TO {$GLOBALS['**arrDatabase']['cdr']['User']};");
					if (PEAR::isError($resOwner))
					{
						throw new Exception($resOwner->getMessage()." :: ".$resOwner->getUserInfo());
					}
					// Set its Comment
					$resComment				= $dsCDRInvoiced->exec("COMMENT ON TABLE {$strTableName} IS 'Invoiced CDR Records for Invoice Run {$this->Id} dated {$this->BillingDate}';");
					if (PEAR::isError($resComment))
					{
						throw new Exception($resComment->getMessage()." :: ".$resComment->getUserInfo());
					}
					// Create an Index on the account Field
					$resAccountIndex		= $dsCDRInvoiced->exec("CREATE INDEX in_{$strTableName}_account ON {$strTableName} USING btree (account);");
					if (PEAR::isError($resAccountIndex))
					{
						throw new Exception($resAccountIndex->getMessage()." :: ".$resAccountIndex->getUserInfo());
					}
					// Create an Index on the invoice_run_id Field
					$resInvoiceRunIdIndex	= $dsCDRInvoiced->exec("CREATE INDEX in_{$strTableName}_invoice_run_id ON {$strTableName} USING btree (invoice_run_id);");
					if (PEAR::isError($resInvoiceRunIdIndex))
					{
						throw new Exception($resInvoiceRunIdIndex->getMessage()." :: ".$resInvoiceRunIdIndex->getUserInfo());
					}

					// Import the data
					$arrOutput	= array();
					$intReturn	= null;
					exec("psql -U {$GLOBALS['**arrDatabase']['cdr']['User']} -h {$GLOBALS['**arrDatabase']['cdr']['URL']} {$GLOBALS['**arrDatabase']['cdr']['Database']} < {$strPGSQLFileName} ", $arrOutput, $intReturn);

					if ($intReturn)
					{
						throw new Exception("There was an error importing '{$strPGSQLFileName}':\n\n ".implode("\n", $arrOutput));
					}

					$dsSalesPortal->commit();
				}
				catch (Exception $eException)
				{
					$dsSalesPortal->rollback();
					throw $eException;
				}
				break;

			default:
				throw new Exception("CDR Databases of type '{$GLOBALS['**arrDatabase']['cdr']['Type']}' are not supported for archiving");
		}

		// Delete the CDRs from the CDR table
		if ($qryQuery->Execute("DELETE FROM CDR WHERE invoice_run_id = {$this->Id}") === false)
		{
			throw new Exception($qryQuery->Error());
		}

		return true;
	}

	/**
	 * generateSampleList()
	 *
	 * Generates a list of Samples for this Invoice Run, and emails out to admins
	 *
	 * @return	boolean
	 */
	public function generateSampleList()
	{
		$selInvoiceRunSchedule	= self::_preparedStatement('selInvoiceRunSchedule');
		if ($selInvoiceRunSchedule->Execute($this->toArray()) === false)
		{
			throw new Exception($selInvoiceRunSchedule->Error());
		}
		$arrInvoiceRunSchedule	= $selInvoiceRunSchedule->Fetch();
		$strSampleType			= (isset($arrInvoiceRunSchedule['description'])) ? $arrInvoiceRunSchedule['description'] : '';
		$strSampleType			= trim($strSampleType . ((stripos($strSampleType, 'sample')) ? '' : ' Samples'));

		//$strInvoiceRunBlurb	= date("F Y", strtotime("-1 day", strtotime($this->BillingDate)))." {$strCustomerGroup} {$arrInvoiceRun['description']} Samples for ".Customer_Group::getForId($this->customer_group_id)->externalName;
		$strInvoiceRunBlurb	= Customer_Group::getForId($this->customer_group_id)->externalName." {$strSampleType} for ".date("F Y", strtotime("-1 day", strtotime($this->BillingDate)));

		$strTextContent	= '';
		$strHTMLContent	=	"<div style='font-family: Calibri, sans-serif;'>\n";

		// Generate Email Content
		$selSampleList	= self::_preparedStatement('selSampleList');
		$mixResult		= $selSampleList->Execute($this->toArray());
		if ($mixResult === false)
		{
			throw new Exception($selSampleList->Error());
		}
		elseif ($mixResult)
		{
			while ($arrSample = $selSampleList->Fetch())
			{
				$strBalance	= number_format($arrSample['Balance'], 2, '.', '');

				$strTextContent	.= "{$arrSample['Id']} | {$arrSample['BusinessName']} | Invoice Total: \${$strBalance} | Account Overview : {$arrSample['flex_url']}/admin/flex.php/Account/Overview/?Account.Id={$arrSample['Id']}\n";
				$strHTMLContent	.= "<a href='{$arrSample['flex_url']}/admin/flex.php/Account/Overview/?Account.Id={$arrSample['Id']}'>{$arrSample['Id']} | {$arrSample['BusinessName']} | Invoice Total: \${$strBalance}</a><br />\n";
			}
		}
		else
		{
			// No Sample Accounts
			$strHTMLContent	.= "There are no Accounts set to Sample in Flex.  However, you can still view sample PDFs for all Accounts on the Account Overview and Invoices & Payments screens.";
			$strTextContent	.= "There are no Accounts set to Sample in Flex.  However, you can still view sample PDFs for all Accounts on the Account Overview and Invoices & Payments screens.";
		}

		$strHTMLContent	.= "</div>\n";

		// Email to Admins
		$intEmailNotificationType	= ($this->invoice_run_type_id === INVOICE_RUN_TYPE_INTERNAL_SAMPLES) ? EMAIL_NOTIFICATION_INVOICE_SAMPLES_INTERNAL : EMAIL_NOTIFICATION_INVOICE_SAMPLES;
		$objEmailNotification		= new Email_Notification($intEmailNotificationType, $this->customer_group_id);

		$objEmailNotification->addHeader("X-Priority", "1 (Highest)");
		$objEmailNotification->addHeader("X-MSMail-Priority", "High");
		$objEmailNotification->addHeader("Importance", "High");

		$objEmailNotification->subject	= $strInvoiceRunBlurb;

		$objEmailNotification->text	= $strTextContent;
		$objEmailNotification->html	= $strHTMLContent;

		return $objEmailNotification->send();
	}

	//------------------------------------------------------------------------//
	// save
	//------------------------------------------------------------------------//
	/**
	 * save()
	 *
	 * Inserts or Updates the InvoiceRun Record for this instance
	 *
	 * Inserts or Updates the InvoiceRun Record for this instance
	 *
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	public function save()
	{
		// Do we have an Id for this instance?
		if ($this->Id !== NULL)
		{
			// Update
			Log::getLog()->log(" * Updating Invoice Run with Id {$this->Id}...");
			$ubiSelf	= self::_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute($this->toArray()) === FALSE)
			{
				throw new Exception("DB ERROR: ".$ubiSelf->Error());
			}
			return TRUE;
		}
		else
		{
			// Insert
			Log::getLog()->log(" * Inserting Invoice Run...");
			$insSelf	= self::_preparedStatement("insSelf");
			$mixResult	= $insSelf->Execute($this->toArray());
			if ($mixResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$insSelf->Error());
			}
			if (is_int($mixResult))
			{
				$this->Id	= $mixResult;
				return TRUE;
			}
			else
			{
				return $mixResult;
			}
		}
	}

	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray()
	{
		return $this->_arrProperties;
	}


	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by InvoiceRun
	 *
	 * Access a Static Cache of Prepared Statements used by InvoiceRun
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	private static function _preparedStatement($strStatement)
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selInvoicesByInvoiceRun':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice", "*", "invoice_run_id = <invoice_run_id>");
					break;
				case 'selTemporaryInvoiceRuns':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "*", "invoice_run_status_id IN (".INVOICE_RUN_STATUS_TEMPORARY.", ".INVOICE_RUN_STATUS_GENERATING.")");
					break;
				case 'selTemporaryInvoiceRunsByCustomerGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "*", "customer_group_id = <customer_group_id> AND invoice_run_status_id IN (".INVOICE_RUN_STATUS_TEMPORARY.", ".INVOICE_RUN_STATUS_GENERATING.")");
					break;
				case 'selInvoiceableAccounts':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account JOIN account_status ON Account.Archived = account_status.id", "Account.*, account_status.deliver_invoice", "CustomerGroup = <customer_group_id> AND Account.CreatedOn < <BillingDate> AND account_status.can_invoice = 1");

					// DEBUG VERSION
					//$arrPreparedStatements[$strStatement]	= new StatementSelect("Account JOIN account_status ON Account.Archived = account_status.id", "Account.*, account_status.deliver_invoice", "Account.Id = 1000154811 AND CustomerGroup = <customer_group_id> AND Account.CreatedOn < <BillingDate> AND account_status.can_invoice = 1");
					break;
				case 'selLastInvoiceRunByCustomerGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "BillingDate", "(customer_group_id = <customer_group_id> OR customer_group_id IS NULL) AND BillingDate < <EffectiveDate> AND invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED." AND invoice_run_type_id = ".INVOICE_RUN_TYPE_LIVE, "BillingDate DESC", 1);
					break;
				case 'selInvoiceTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice", "SUM(Invoice.Total) AS BillInvoiced, SUM(Invoice.Tax) AS BillTax", "invoice_run_id = <invoice_run_id>");
					break;
				case 'selInvoiceCDRTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("ServiceTypeTotal STT", "SUM(STT.Cost) AS BillCost, SUM(STT.Charge) AS BillRated", "invoice_run_id = <invoice_run_id>");
					break;
				case 'selInvoiceBalanceHistory':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun JOIN Invoice ON InvoiceRun.Id = Invoice.invoice_run_id", "InvoiceRun.Id AS InvoiceRunId, SUM(Invoice.Balance) AS TotalBalance", "InvoiceRun.Id <= <invoice_run_id> AND InvoiceRun.customer_group_id = <customer_group_id> AND InvoiceRun.invoice_run_type_id = ". INVOICE_RUN_TYPE_LIVE ." AND InvoiceRun.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED, "InvoiceRun.Id DESC", "", "InvoiceRun.Id");
					break;
				case 'selCheckTemporaryInvoiceRun':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"InvoiceRun",
																					"Id",
																					"(invoice_run_type_id = ".INVOICE_RUN_TYPE_LIVE." OR (<Account> IS NOT NULL AND (SELECT COUNT(Id) FROM Invoice WHERE invoice_run_id = InvoiceRun.Id AND Account = <Account>) > 0 AND invoice_run_type_id IN (".INVOICE_RUN_TYPE_INTERIM.", ".INVOICE_RUN_TYPE_FINAL.", ".INVOICE_RUN_TYPE_INTERIM_FIRST."))) AND invoice_run_status_id != ".INVOICE_RUN_STATUS_COMMITTED." AND (customer_group_id <=> <CustomerGroup> OR <CustomerGroup> IS NULL)",
																					null,
																					1);
					break;
				case 'selPaymentTerms':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("payment_terms", "*", "customer_group_id = <customer_group_id>", "id DESC", 1);
					break;
				case 'selInvoiceCDRCredits':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("CustomerGroup", "invoice_cdr_credits", "Id = <customer_group_id>");
					break;
				case 'selSampleList':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id", "Account.*, CustomerGroup.flex_url, Invoice.TotalOwing, Invoice.Balance", "Account.Sample != 0 AND invoice_run_id = <Id>");
					break;
				case 'selInvoiceRunSchedule':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun LEFT JOIN invoice_run_schedule ON InvoiceRun.invoice_run_schedule_id = invoice_run_schedule.id", "invoice_run_schedule.*", "InvoiceRun.Id = <Id>", null, 1);
					break;


				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("InvoiceRun");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("InvoiceRun");
					break;

				// UPDATES
				case 'updCDRRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("CDR", "invoice_run_id = <Id> AND Status = ".CDR_TEMP_INVOICE, Array('invoice_run_id'=>NULL, 'Status'=>CDR_RATED));
					break;
				case 'updChargeRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "invoice_run_id = <Id> AND Status = ".CHARGE_TEMP_INVOICE, Array('invoice_run_id'=>NULL, 'Status'=>CHARGE_APPROVED));
					break;

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}

?>
