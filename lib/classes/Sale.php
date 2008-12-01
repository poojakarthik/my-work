<?php
/**
 * Sale
 *
 * Models a record of the Sale table
 *
 * @class	Sale
 */
class Sale extends ORM
{
	protected	$_strTableName	= "sale";
	
	protected static $_cache = array();
	
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
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
	
	// This will return a Sale object if found
	// If a record is not found then it will return NULL if $bolExceptionOnNotFound == FALSE OR throw an exception if $bolExceptionOnNotFound == TRUE
	// This will also throw an exception if 
	public static function getForId($intId, $bolExceptionOnNotFound=FALSE, $bolForceRefresh=FALSE)
	{
		if (array_key_exists($intId, self::$_cache) && !$bolForceRefresh)
		{
			// The Sale object is cached, and we are not forcing a refresh
			return self::cache($intId);
		}
		
		$selSale = self::_preparedStatement('selById');

		if (($intCount = $selSale->Execute(array('Id'=>$intId))) === FALSE)
		{
			throw new Exception("Failed to retrieve sale record with id: $intId. - ". $selSale->Error());
		}
		
		if ($intCount)
		{
			self::$_cache[$intId] = new self($selSale->Fetch());
			
			return self::$_cache[$intId];
		}
		elseif ($bolExceptionOnNotFound)
		{
			throw new Exception("sale record with id $intId could not be found");
		}
		else
		{
			return FALSE;
		}
	}
	
	public static function getForAccountId($intAccountId, $strOrderBy=NULL, $intLimit=NULL, $intOffset=NULL)
	{
		if ($intLimit !== NULL)
		{
			$strLimitClause = $intLimit . (($intOffset !== NULL)? ", $intOffset" : "");
		}
		
		$selSales = new StatementSelect("sale", "*", "account_id = <AccountId>", $strOrderBy, $strLimitClause);
		
		if ($selSales->Execute(array("AccountId"=>$intAccountId)) === FALSE)
		{
			throw new Exception("Failed to retrieve sales belonging to account: $intAccountId - ". $selSales->Error());
		}
		
		$arrSales = array();
		$arrRecordSet = $selSales->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$objSale = new self($arrRecord);
			$arrSales[$objSale->id] = $objSale;
		}
		
		return $arrSales;
	}
	
	// Retrieves the value part from the sale.external_reference string
	// This string should be of the form "sale.id=123" where 123 is the value 
	public function getExternalReferenceValue()
	{
		return intval(substr($this->externalReference, 8));
	}
	
	// Retrieves the DO_Sales_Sale object related to this object
	// Throws an Exception on Error, or when the object cannot be found (because the object should always be found)
	public function getExternalReferenceObject()
	{
		try
		{
			$doSale = DO_Sales_Sale::getForId($this->getExternalReferenceValue());
			
			if ($doSale !== NULL)
			{
				return $doSale;
			}
			throw new Exception("External Object was not found");
		}
		catch (Exception $e)
		{
			throw new Exception("Failed to retrieve externally referenced object for sale record with id: {$this->id}, ExternalReference: {$this->externalReference} - ". $e->getMessage());
		}
	}
	
	// This updates the status of the sale, if it should be set to Completed or Cancelled, and creates a system note if required
	public function setCompletedOrCancelledBasedOnSaleItems($intDealerId=NULL, $intEmployeeId=NULL)
	{
		try
		{
			if ($intDealerId === NULL)
			{
				$intDealerId = Dealer::SYSTEM_DEALER_ID;
			}
			
			if ($intEmployeeId === NULL)
			{
				$intEmployeeId = Employee::SYSTEM_EMPLOYEE_ID;
			}
			
			// Update the status of the sale in the sales database, if it needs updating
			$doSale = $this->getExternalReferenceObject();
			$intCurrentSaleStatus = $doSale->saleStatusId;
			$doSale->setCompletedOrCancelledBasedOnSaleItems($intDealerId);
			$intNewSaleStatus = $doSale->saleStatusId;
			
			// Check if the status was updated
			if ($intNewSaleStatus != $intCurrentSaleStatus)
			{
				// The status has been changed, so create a Note detailing this
				$arrSaleStatusHistory	= DO_Sales_SaleStatusHistory::listForSale($doSale, "id DESC", 1);
				$doSaleStatusHistory	= $arrSaleStatusHistory[0];
				$doStatus				= $doSaleStatusHistory->getSaleStatus();
				$strNote				= "Sale {$doSale->id} has now been flagged as having been {$doStatus->name} as at {$doSaleStatusHistory->changedOn} in the sales system";
				$strNote				.= ($doSaleStatusHistory->description !== NULL)? ". ({$doSaleStatusHistory->description})" : "";
				
				$objAccount = Account::getForId($this->accountId);
				
				Note::createSystemNote($strNote, $intEmployeeId, $objAccount->accountGroup, $this->accountId);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(__METHOD__ ." Failed - ". $e->getMessage());
		}
	}
	
	/**
	 * _preparedStatement()
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"sale", "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("sale");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("sale");
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