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
	private $id					= NULL;
	private $invoiceRun			= NULL;
	private $billingDate		= NULL;
	private $invoiceCount		= NULL;
	private $billCost			= NULL;
	private $billRated			= NULL;
	private $billInvoiced		= NULL;
	private $billTax			= NULL;
	private $balanceData		= NULL;
	private $cdrArchivedState	= NULL;
	
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
	private function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Joel's implementation
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
		if (is_string($this->balanceData))
		{
			$this->balanceData = unserialize($this->balanceData);
		}
		
		// Rich's implementation
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('InvoiceRun');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->{$strName}	= NULL;
		}
		
		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : ($arrProperties['id']) ? $arrProperties['id'] : NULL;
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
				// Do we want to Debug something?
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
		return array(
						"id"					=> "Id",
						"invoice_run"			=> "InvoiceRun",
						"billing_date"			=> "BillingDate",
						"invoice_count"			=> "InvoiceCount",
						"bill_cost"				=> "BillCost",
						"bill_rated"			=> "BillRated",
						"bill_invoiced"			=> "BillInvoiced",
						"bill_tax"				=> "BillTax",
						"balance_data"			=> "BalanceData",
						"cdr_archived_state"	=> "CDRArchivedState"
					);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the InvoiceRun object
	 * 
	 * Initialises the InvoiceRun object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of InvoiceRun table
	 * @return		void
	 * @method
	 */
	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}
	}


	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * accessor method
	 * 
	 * accessor method
	 *
	 * @param	string	$strName	name of property to get. in either of the formats xxxYyyZzz or xxx_yyy_zzz 
	 * @return	void
	 * @method
	 */
	public function __get($strName)
	{
		// Joel's implementation
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
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
	 * @param	integer	$intScheduledInvoiceRun		[optional]	The invoice_run_schedule.id to Run
	 *
	 * @method
	 */
	public function generate($intCustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $intScheduledInvoiceRun=NULL)
	{
		// Init variables
		$dbaDB					= DataAccess::getDataAccess();
		$strInvoiceDatetime		= date($intInvoiceDatetime);
		
		// If there are any Temporary InvoiceRuns for this Customer Group, then Revoke them
		Cli_App_Billing::debug(" * Revoking any Temporary Invoice Runs...");
		Invoice_Run::revokeByCustomerGroup($intCustomerGroup);
		
		//------------------- START INVOICE RUN GENERATION -------------------//
		Cli_App_Billing::debug(" * Creating initial InvoiceRun record...");
		// Create the initial InvoiceRun record
		$this->BillingDate				= date("Y-m-d", $intInvoiceDatetime);
		$this->invoice_run_type_id		= $intInvoiceRunType;
		$this->invoice_run_schedule_id	= $intScheduledInvoiceRun;
		$this->invoice_run_status		= INVOICE_RUN_STATUS_TEMPORARY;
		$this->customer_group_id		= $intCustomerGroup;
		$this->save();
		
		$this->intInvoiceDatetime		= $intInvoiceDatetime;
		
		// Retrieve the Bill Date of the last Invoice Run...
		Cli_App_Billing::debug(" * Getting Last Invoice Date...");
		$selInvoiceRun	= self::_preparedStatement('selLastInvoiceRunByCustomerGroup');
		if ($selInvoiceRun->Execute(Array('customer_group_id' => $intCustomerGroup)))
		{
			// We have an old InvoiceRun
			$arrLastInvoiceRun	= $selInvoiceRun->Fetch();
			$objInvoiceRun->intLastInvoiceDatetime	= strtotime($arrLastInvoiceRun['BillingDate']);
		}
		elseif ($selInvoiceRun->Error())
		{
			throw new Exception("DB ERROR: ".$selInvoiceRun->Error());
		}
		else
		{
			// No InvoiceRuns, so lets calculate when it should have been
			// For now, we will (and can probably always) assume that the Bill was supposed to be run exactly 1 month ago
			$objInvoiceRun->intLastInvoiceDatetime	= strtotime("-1 month", $intInvoiceDatetime);
		}
		
		// Retrieve a list of Accounts to be Invoiced
		Cli_App_Billing::debug(" * Getting list of Accounts to Invoice...");
		$selInvoiceableAccounts	= self::_preparedStatement('selInvoiceableAccounts');
		if ($selInvoiceableAccounts->Execute(get_object_vars($this)) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableAccounts->Error());
		}
		
		// Generate an Invoice for each Account
		while ($arrAccount = $selInvoiceableAccounts->Fetch())
		{
			$objAccount	= new Account($arrAccount);
			Cli_App_Billing::debug(" + Generating Invoice for {$objAccount->Id}...");
			$objInvoice	= new Invoice();
			$objInvoice->generate($objAccount, $this);
		}
		//--------------------------------------------------------------------//
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
		Cli_App_Billing::debug(" * ENTERING RevokeAll...");
		
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
			Cli_App_Billing::debug(" + Revoking Invoice Run with Id {$objInvoiceRun->Id}...");
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
		Cli_App_Billing::debug(" + ENTERING revokeByCustomerGroup({$intCustomerGroup})...");
		
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
			Cli_App_Billing::debug(" + Revoking Invoice Run with Id {$objInvoiceRun->Id}...");
			$objInvoiceRun->revoke();
		}
	}
	
	//------------------------------------------------------------------------//
	// revoke
	//------------------------------------------------------------------------//
	/**
	 * revoke()
	 *
	 * Generates an Invoice Run
	 *
	 * Generates an Invoice Run
	 *
	 * @method
	 */
	public function revoke()
	{
		Cli_App_Billing::debug(" * ENTERING revoke()...");
		
		// Is this InvoiceRun Temporary?
		if ($this->invoice_run_status_id !== INVOICE_RUN_STATUS_TEMPORARY)
		{
			// No, throw an Exception
			throw new Exception("InvoiceRun '{$this->Id}' is not a Temporary InvoiceRun!");
		}
		
		// Init variables
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		// Get list of Invoices to Revoke
		Cli_App_Billing::debug(" * Getting list of Invoices to Revoke...");
		$selInvoicesByInvoiceRun	= self::_preparedStatement('selInvoicesByInvoiceRun');
		if ($selInvoicesByInvoiceRun->Execute(Array('invoice_run_id' => $this->Id)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selInvoicesByInvoiceRun->Error());
		}
		while ($arrInvoice = $selInvoicesByInvoiceRun->Fetch())
		{
			// Revoke each Invoice
			$objInvoice = new Invoice($arrInvoice);
			Cli_App_Billing::debug(" * Revoking Invoice with Id {$objInvoice->Id}...");
			$objInvoice->revoke();
		}
		
		// Remove entry from the InvoiceRun table
		Cli_App_Billing::debug(" * Removing Invoice Run with Id {$this->Id}");
		if ($qryQuery->Execute("DELETE FROM InvoiceRun WHERE Id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
	}
	
	//------------------------------------------------------------------------//
	// export
	//------------------------------------------------------------------------//
	/**
	 * export()
	 *
	 * Exports an Invoice Run to XML
	 * 
	 * Exports an Invoice Run to XML.  The path used is [FILES_BASE_PATH]/invoices/xml/[Invoice_Run.Id]/[Invoice.Id].xml
	 * 
	 * @return		void
	 * 
	 * @constructor
	 */
	public function export()
	{
		// Select all Invoices for this Invoice Run
		$selInvoicesByInvoiceRun	= self::_preparedStatement('selInvoicesByInvoiceRun');
		if ($selInvoicesByInvoiceRun->Execute(Array('invoice_run_id' => $this->Id)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selInvoicesByInvoiceRun->Error());
		}
		while ($arrInvoice = $selInvoicesByInvoiceRun->Fetch())
		{
			// Export the Invoice
			$objInvoice = new Invoice($arrInvoice);
			$objInvoice->export();
		}
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
		Cli_App_Billing::debug(" * ENTERING save()...");
		
		// Do we have an Id for this instance?
		if ($this->Id !== NULL)
		{
			// Update
			Cli_App_Billing::debug(" * Updating Invoice Run with Id {$this->Id}...");
			$ubiSelf	= self::_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute(get_object_vars($this)) === FALSE)
			{
				throw new Exception("DB ERROR: ".$ubiSelf->Error());
			}
			return TRUE;
		}
		else
		{
			// Insert
			Cli_App_Billing::debug(" * Inserting Invoice Run...");
			$insSelf	= self::_preparedStatement("insSelf");
			$mixResult	= $insSelf->Execute(get_object_vars($this));
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "*", "invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
					break;
				case 'selTemporaryInvoiceRunsByCustomerGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "*", "customer_group_id = <customer_group_id> AND invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
					break;
				case 'selInvoiceableAccounts':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account JOIN account_status ON Account.Archived = account_status.id", "Account.*", "CustomerGroup = <customer_group_id> AND Account.CreatedOn < '{$strInvoiceDatetime}' AND account_status.can_invoice = 1");
				case 'selLastInvoiceRunByCustomerGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun", "BillingDate", "customer_group_id = <customer_group_id> AND invoice_run_status_id = ".INVOICE_STATUS_COMMITTED, "BillingDate DESC", 1);
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
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}

?>
