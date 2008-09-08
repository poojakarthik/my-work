<?php

//----------------------------------------------------------------------------//
// Customer_Status_Assignment
//----------------------------------------------------------------------------//
/**
 * Customer_Status_Assignment
 *
 * Models the assignment of a customer status to a customer
 *
 * Models the assignment of a customer status to a customer
 *
 * @class	Customer_Status_Assignment
 */
class Customer_Status_Assignment
{
	// id of the customer_status_history record
	private $id							= NULL;
	
	// id of the account that this object relates to
	private $accountId					= NULL;
	
	// id of the invoice run that this object relates to 
	private $invoiceRunId				= NULL;
	
	// ISO datetime representing the time at which this record was last updated
	private $lastUpdated				= NULL;
	
	// id of the customer status that this object relates to
	private $customerStatusId			= NULL;
	
	private $_bolNeedsRefresh			= NULL;
	private $_bolSaved					= NULL;
	
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
	 * @param		array	$arrProperties 	Optional.  Associative array defining a customer status assignment with keys for each field of the customer_status table
	 * @return		void
	 * @constructor
	 */
	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
		
		$this->_bolNeedsRefresh = FALSE;

		if ($this->id == NULL)
		{
			$this->_bolSaved = FALSE;
		}
		else
		{
			$this->_bolSaved = TRUE;
		}
	}
	
	// Used to set the Customer Status property
	public function setCustomerStatusId($intStatus)
	{
		if ($this->_bolNeedsRefresh)
		{
			throw new Exception("Cannot modify Customer_Status_Object because it needs to be refreshed from the database");
		}
		$this->customerStatusId = $intStatus;
		$this->_bolSaved = FALSE;
	}
	
	// Used to save the changes you have made, to the database
	// If the CustomerStatus is being saved for the first time, then this function will set the id of the object, but will not refresh the object
	public function save()
	{
		static $insCustomerStatusHistory;
		static $updCustomerStatusHistory;
		
		if ($this->_bolNeedsRefresh)
		{
			throw new Exception("Cannot save Customer_Status_Object which currently needs to be refreshed from the database");
		}
		
		// Process the values to save
		$arrColumns	= self::getColumns();
		$arrFields	= array();
		foreach ($arrColumns as $strColumn)
		{
			$arrFields[$strColumn] = $this->__get($strColumn);
		}
		
		// The last_updated property has to be set to the SQL NOW() function
		$arrFields['last_updated'] = new MySQLFunction("NOW()");

		if ($this->id === NULL)
		{
			// We are inserting the record
			if (!isset($insCustomerStatusHistory))
			{
				// Create the StatementInsert object
				$insCustomerStatusHistory = new StatementInsert("customer_status_history", $arrFields);
			}
			
			// Make the insert
			if (($intNewId = $insCustomerStatusHistory->Execute($arrFields)) === FALSE)
			{
				throw new Exception("Failed to insert record into customer_status_history table for account: {$this->accountId}, invoice run: {$this->invoiceRunId} - ". $insCustomerStatusHistory->Error());
			}
			
			// Store the new id
			$this->id = $intNewId;
		}
		else
		{
			// We are updating an existing record
			if (!isset($updCustomerStatusHistory))
			{
				// Create the StatementInsert object
				$updCustomerStatusHistory = new StatementUpdateById("customer_status_history", $arrFields);
			}
			
			// Make the update 
			if (($intRecordsAffected = $updCustomerStatusHistory->Execute($arrFields)) === FALSE)
			{
				// Update failed
				throw new Exception("Failed to update record in customer_status_history table for account: {$this->accountId}, invoice run: {$this->invoiceRunId} (id: {$this->id})- ". $updCustomerStatusHistory->Error());
			}
			if ($intRecordsAffected === 0)
			{
				// Could not find record with the specified id
				throw new Exception("Failed to update record in customer_status_history table for account: {$this->accountId}, invoice run: {$this->invoiceRunId}, because the record with id = {$this->id}, could not be found");
			}
		}
		
		$this->_bolNeedsRefresh = TRUE;
		$this->_bolSaved = TRUE;
	}

	// Updates/Inserts the record in the customer_status_history table
	// throws an exception on failure
	// If $bolGetAsObject == FALSE then returns the id of the customer_status_record
	// If $bolGetAsObject == TRUE then returns a Customer_Status_Assignment object defining the Customer Status Assignment
	public static function declareAssignment($intAccountId, $intInvoiceRunId, $intCustomerStatusId, $bolGetAsObject=FALSE)
	{
		$objAssignment = self::getForAccountInvoiceRun($intAccountId, $intInvoiceRunId);
		
		if ($objAssignment === FALSE)
		{
			// There is currently not a record in the customer_status_history table representing this Account/InvoiceRun combination
			$arrProps = array(	"accountId"		=> $intAccountId,
								"invoiceRunId"	=> $intInvoiceRunId);
			$objAssignment = new Customer_Status_Assignment($arrProps);
		}
		
		$objAssignment->setCustomerStatusId($intCustomerStatusId);

		$objAssignment->save();
		
		if ($bolGetAsObject)
		{
			// The object will need refreshing
			$objAssignment->refresh();
			return $objAssignment;
		}
		else
		{
			// Return the id of the customer_status_history record which models this CustomerStatus assignment
			// Note that this won't use the __get function, which is good because I don't wont the object to refresh
			return $objAssignment->id;
		}
	}
	

	// Returns array of Customer_Status_Assignment objects for the specified account
	// The objects will be ordered in descending order of invoice_run_id
	// If $intLimit is specified then the most recent $intLimit assignments will be retrieved
	// IF there are no customer_status_history records for this account, then it will return an empty array
	public static function getForAccount($intAccountId, $intLimit=NULL)
	{
		return self::getFor("account_id = <AccountId>", array("AccountId"=>$intAccountId), $intLimit);
	}

	// Note that this method doesn't make use of the ::getFor method, because I want to cache the StatementSelect object used
	public static function getForAccountInvoiceRun($intAccountId, $intInvoiceRunId)
	{
		static $selCustomerStatusHistory;
		if (!isset($selCustomerStatusHistory))
		{
			$arrColumns = self::getColumns();
			$selCustomerStatusHistory = new StatementSelect("customer_status_history", $arrColumns, "account_id = <AccountId> AND invoice_run_id = <InvoiceRunId>");
		}
		
		if (($intNumRec = $selCustomerStatusHistory->Execute(array("AccountId"=>$intAccountId, "InvoiceRunId"=>$intInvoiceRunId))) === FALSE)
		{
			throw new Exception("Failed to retrieve Customer Status Asssignment details for account: $intAccountId - ". $selCustomerStatusHistory->Error());
		}
		if ($intNumRec > 1)
		{
			// More than 1 record was returned for this account_id/invoice_run_id combination, which should never be the case
			throw new Exception("Multiple records retrieved from customer_status_history table for account: $intAccountId and invoice_run_id: $intInvoiceRunId");
		}
		elseif ($intNumRec == 1)
		{
			// A record was found
			return new self($selCustomerStatusHistory->Fetch());
		}
		else
		{
			// No record was found
			return FALSE;
		}
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Customer_Status_Assignment object with the id specified
	 * 
	 * Returns the Customer_Status_Assignment object with the id specified
	 *
	 * @param	int 				$intId		id of the customer_status_history record to retrieve		
	 * @return	mixed 							Customer_Status_Assignment object	: if it exists
	 * 											FALSE								: if it doesn't exist
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrAssignments = self::getFor("id = <Id>", array("Id"=>$intId));
		
		if (is_array($arrAssignments) && count($arrAssignments) == 1)
		{
			return $arrAssignments[0];
		}
		else
		{
			return FALSE;
		}
	}

	// Returns array of Customer_Status_Assignment objects for the specified conditions
	private static function getFor($strWhere, $arrWhere, $intLimit=NULL)
	{
		$arrCustomerStatusHistory = array();
		$arrColumns = self::getColumns();
		$selCustomerStatusHistory = new StatementSelect("customer_status_history", $arrColumns, $strWhere, "invoice_run_id DESC", $intLimit);

		if (($outcome = $selCustomerStatusHistory->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve Customer Status Asssignment details for account: $intAccountId - ". $selCustomerStatusHistory->Error());
		}

		while ($arrRecord = $selCustomerStatusHistory->Fetch())
		{
			$arrCustomerStatusHistory[] = new self($arrRecord);
		}
		
		return $arrCustomerStatusHistory;
	}

	//------------------------------------------------------------------------//
	// getCustomerStatus
	//------------------------------------------------------------------------//
	/**
	 * getCustomerStatus()
	 *
	 * Returns the Customer_Status object representing the Customer_Status of this particular Customer_Status_Assignment
	 * 
	 * Returns the Customer_Status object representing the Customer_Status of this particular Customer_Status_Assignment
	 *
	 * @return		Customer_Status object
	 * @method
	 */
	public function getCustomerStatus()
	{
		return Customer_Status::getForId($this->customerStatusId);
	}
	
	// Returns the action description based on the user role, customer status and whether or not the account has an overdue amount
	// if $intUserRole === NULL then the default descriptions are used
	public function getActionDescription($intUserRole=NULL)
	{
		return $this->getCustomerStatus()->getActionDescription($intUserRole, $this->paymentRequired());
	}

	// returns the overdue amount for the account (including GST)
	// this value will be cahced if $bolForceRefesh == FALSE
	// This can never be negitive as you can never have an overdue credit
	public function paymentRequired($bolForceRefresh=FALSE)
	{
		static $bolPaymentRequired;
		if (!isset($bolPaymentRequired) || $bolForceRefresh)
		{
			if (($fltOverdueAmount = $GLOBALS['fwkFramework']->GetOverdueBalance($this->accountId)) === FALSE)
			{
				// An error occurred
				throw new Exception("Error occurred when trying to calculate the overdue balance for account: {$this->accountId}");
			}
			$bolPaymentRequired = (bool)($fltOverdueAmount > 0.0);
		}
		return $bolPaymentRequired;
	}

	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the customer_status_history table
	 * 
	 * Returns array defining the columns of the customer_status_history table
	 *
	 * @return		array
	 * @method
	 */
	protected static function getColumns()
	{
		return array(
						"id",
						"account_id",
						"invoice_run_id",
						"last_updated",
						"customer_status_id"
					);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Customer_Status_Assignment object
	 * 
	 * Initialises the Customer_Status_Assignment object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of customer_status_history table
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

	public function refresh($bolForceRefresh=FALSE)
	{
		static $selCustomerStatusAssignment;
		if ($this->_bolNeedsRefresh == FALSE && $bolForceRefresh == FALSE)
		{
			// The object doesn't need a refresh and we are not forcing one
			return;
		}
		if ($this->id === NULL)
		{
			throw new exception("Trying to refresh a Customer Status Assignment that doesn't have an id.  Account: {$this->accountId}, InvoiceRunId: {$this->invoiceRunId}");
		}
		
		if (!isset($selCustomerStatusAssignment))
		{
			// Set up the StatementSelect object
			$arrColumns = self::getColumns();
			$selCustomerStatusAssignment = new StatementSelect("customer_status_history", $arrColumns, "id = <Id> AND account_id = <AccountId> AND invoice_run_id = <InvoiceRunId>");
		}
		
		$arrWhere = array(	"Id"			=> $this->id,
							"AccountId"		=> $this->accountId,
							"InvoiceRunId"	=> $this->invoiceRunId
						);
		
		if (($intRecCount = $selCustomerStatusAssignment->Execute($arrWhere)) === FALSE)
		{
			// An error occurred
			throw new Exception("Failed to refresh Customer Status Asssignment details for account: {$this->accountId}, invoice run: {$this->invoiceRunId}, customer_status_history.id: {$this->id} - ". $selCustomerStatusAssignment->Error());
		}
		if ($intRecCount == 0)
		{
			// Could not find the record
			throw new Exception("Failed to find Customer Status Asssignment details for account: {$this->accountId}, invoice run: {$this->invoiceRunId}, customer_status_history.id: {$this->id}");
		}
		
		// Load in all the details
		$arrRecord = $selCustomerStatusAssignment->Fetch();
		foreach ($arrRecord as $strProp=>$mixValue)
		{
			$this->{$this->tidyName($strProp)} = $mixValue;
		}

		$this->_bolSaved = TRUE;
		$this->_bolNeedsRefresh	= FALSE;
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
		if ($strName[0] === '_')
		{
			// Don't allow access to data attributes that start with '_'
			return NULL;
		}
		
		if ($this->_bolNeedsRefresh)
		{
			// The object needs to be refreshed
			$this->refresh();
		}
		
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
