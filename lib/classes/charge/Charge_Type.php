<?php
//----------------------------------------------------------------------------//
// Charge_Type
//----------------------------------------------------------------------------//
/**
 * Charge_Type
 *
 * Models a record of the ChargeType table
 *
 * Models a record of the ChargeType table
 *
 * @class	Service
 */
class Charge_Type
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
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('ChargeType');
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
	// getByCode()
	//------------------------------------------------------------------------//
	/**
	 * getByCode()
	 *
	 * Rerieves the ChargeType by its Code
	 *
	 * Rerieves the ChargeType by its Code
	 * 
	 * @param	string	$strCode		The ChargeType Code
	 * 
	 * @return	mixed					Charge_Type on Success
	 * 									NULL on Failure
	 *
	 * @method
	 */
	static public function getByCode($strCode)
	{
		$selByCode	= self::_preparedStatement("selByCode");
		if ($selByCode->Execute(Array('ChargeType'=>$strCode)))
		{
			return new Charge_Type($selByCode->Fetch());
		}
		elseif ($selByCode->Error())
		{
			throw new Exception($selByCode->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	//------------------------------------------------------------------------//
	// getContractExitFee()
	//------------------------------------------------------------------------//
	/**
	 * getContractExitFee()
	 *
	 * Rerieves the Contract Exit Fee Charge Type
	 *
	 * Rerieves the Contract Exit Fee Charge Type
	 * 
	 * @return	Charge_Type
	 *
	 * @method
	 */
	static public function getContractExitFee()
	{
		$selContractExitFee	= self::_preparedStatement("selContractExitFee");
		if ($selContractExitFee->Execute())
		{
			return new Charge_Type($selContractExitFee->Fetch());
		}
		elseif ($selContractExitFee->Error())
		{
			throw new Exception($selContractExitFee->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	//------------------------------------------------------------------------//
	// getContractPayoutFee()
	//------------------------------------------------------------------------//
	/**
	 * getContractPayoutFee()
	 *
	 * Rerieves the Contract Payout Fee Charge Type
	 *
	 * Rerieves the Contract Payout Fee Charge Type
	 * 
	 * @return	Charge_Type
	 *
	 * @method
	 */
	static public function getContractPayoutFee()
	{
		$selContractPayoutFee	= self::_preparedStatement("selContractPayoutFee");
		if ($selContractPayoutFee->Execute())
		{
			return new Charge_Type($selContractPayoutFee->Fetch());
		}
		elseif ($selContractPayoutFee->Error())
		{
			throw new Exception($selContractPayoutFee->Error());
		}
		else
		{
			return NULL;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ChargeType", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selByCode':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ChargeType", "*", "ChargeType = <ChargeType> AND Archived = 0", NULL, 1);
					break;
				case 'selContractPayoutFee':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.payout_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				case 'selContractExitFee':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.exit_fee_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("ChargeType");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("ChargeType");
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