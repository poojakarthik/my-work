<?php

//----------------------------------------------------------------------------//
// Payment_Terms
//----------------------------------------------------------------------------//
/**
 * Payment_Terms
 *
 * Models a record of the payment_terms table
 *
 * Models a record of the payment_terms table
 *
 * @class	Rate_Plan
 */
class Payment_Terms extends ORM
{	
	protected	$_strTableName	= "payment_terms";
	
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
	
	// Returns NULL if there isn't a current payment_terms record for the customer group passed
	public static function getCurrentForCustomerGroup($intCustomerGroupId)
	{
		$selCurrentPT = self::_preparedStatement("selCurrentForCustomerGroupId");
		
		$mixResult = $selCurrentPT->Execute(array("CustomerGroupId"=>$intCustomerGroupId));
		if ($mixResult === FALSE)
		{
			throw new Exception("Failed to retrieve current Payment Terms record for customer group: $intCustomerGroupId - ". $selCurrentPT->Error());
		}
		if ($mixResult === 0)
		{
			// There are no current details
			return NULL;
		}
		
		// A record must have been found
		$arrPaymentTerms = $selCurrentPT->Fetch();
		return new self($arrPaymentTerms);
	}
	
	
	// Returns all RatePlan objects in an array where the id of the RatePlan is the key to the array
	// This array is sorted by RatePlan.Name
	public static function getAll() 
	{
		$selRatePlan = new StatementSelect("RatePlan", "*", "", "Name ASC");
		
		if ($selRatePlan->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve all RatePlans - ". $selRatePlan->Error());
		}
		
		$arrRatePlans = array();
		$arrRecordSet = $selRatePlan->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$objRatePlan = new self($arrRecord);
			$arrRatePlans[$objRatePlan->Id] = $objRatePlan;
		}
		return $arrRatePlans;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"payment_terms", "*", "id = <Id>", NULL, 1);
					break;
				
				case 'selCurrentForCustomerGroupId':
					// This assumes the record with the highest id is the current record for any given customer group
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"payment_terms", "*", "customer_group_id = <CustomerGroupId>", "id DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("payment_terms");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("payment_terms");
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