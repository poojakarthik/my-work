<?php

//TODO! This class uses the exact column names as the property member variables, instead of tidied names, 
// which means you have to use $objRatePlan->ContractTerm for the ContractTerm of the record,
// and $objRatePlan->customer_group for the customer group of the record


//----------------------------------------------------------------------------//
// Rate_Plan
//----------------------------------------------------------------------------//
/**
 * Rate_Plan
 *
 * Models a record of the RatePlan table
 *
 * Models a record of the RatePlan table
 *
 * @class	Rate_Plan
 */
class Rate_Plan
{	
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
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('RatePlan');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->{$strName}	= NULL;
		}
		
		// Automatically load the object using the passed Id
		$intId	= $arrProperties['Id'] ? $arrProperties['Id'] : ($arrProperties['id'] ? $arrProperties['id'] : NULL);

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
	// save
	//------------------------------------------------------------------------//
	/**
	 * save()
	 *
	 * Inserts or Updates the DB Record for this instance
	 *
	 * Inserts or Updates the DB Record for this instance
	 * 
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	public function save()
	{
		// Do we have an Id for this instance?
		if ($this->Id)
		{
			// Update
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"RatePlan", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("RatePlan");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("RatePlan");
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