<?php
//----------------------------------------------------------------------------//
// Carrier
//----------------------------------------------------------------------------//
/**
 * Carrier
 *
 * Models a record of the Carrier table
 *
 * Models a record of the Carrier table
 *
 * @class	Carrier
 */
class Carrier extends ORM
{	
	protected $_strTableName = "Carrier";
	
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
	
	// The id will be the key to the array
	public static function listAll()
	{
		static $arrCache;
		if (!isset($arrCache))
		{
			$selAll = self::_preparedStatement('selAll');
			
			$mixResult = $selAll->Execute();
			if ($mixResult === FALSE)
			{
				throw new Exception(__METHOD__ ." - Failed to retrieve all carrier records from database - ". $selAll->Error());
			}
			$arrCache = array();
			
			while ($arrRecord = $selAll->Fetch())
			{
				$arrCache[$arrRecord['Id']] = new self($arrRecord);
			}
		}
		return $arrCache;
	}
	
	public static function listForCarrierTypeId($intCarrierTypeId)
	{
		$arrCarriers = self::listAll();
		$arrCarrierTypeCarriers = array();
		foreach ($arrCarriers as $objCarrier)
		{
			if ($objCarrier->carrierType == $intCarrierTypeId)
			{
				$arrCarrierTypeCarriers[$objCarrier->id] = $objCarrier;
			}
		}
		return $arrCarrierTypeCarriers;
	}
	
	public static function getForId($intId, $bolExceptionOnNotFound=FALSE)
	{
		$arrCarriers = self::listAll();
		if (array_key_exists($intId, $arrCarriers))
		{
			return $arrCarriers[$intId];
		}
		elseif ($bolExceptionOnNotFound)
		{
			throw new Exception(__METHOD__ ." - Could not find Carrier with id: $intId");
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Carrier", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Carrier", "*", "TRUE", "Name ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Carrier");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Carrier");
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