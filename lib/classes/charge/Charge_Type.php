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
class Charge_Type extends ORM
{	
	protected	$_strTableName	= "ChargeType";
	
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