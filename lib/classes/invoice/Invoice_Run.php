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
	 * @param		array	$arrProperties 	Optional.  Associative array defining an invoice run with keys for each field of the InvoiceRun table
	 * @return		void
	 * @constructor
	 */
	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
		if (is_string($this->balanceData))
		{
			$this->balanceData = unserialize($this->balanceData);
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
	
	
	
}

?>
