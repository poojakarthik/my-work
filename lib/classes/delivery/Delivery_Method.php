<?php
/**
 * Delivery_Method
 *
 * Models a record of the delivery_method table
 *
 * @class	Delivery_Method
 */
class Delivery_Method extends ORM
{
	protected			$_strTableName				= "delivery_method";
	protected static	$_strStaticTableName		= "delivery_method";
	
	protected static	$_arrStaticCache			= array();
	
	/**
	 * __construct()
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
	
	/**
	 * getForId()
	 *
	 * Returns a Delivery_Method object or array for the given Id
	 *
	 * @param	integer		$intId						The Id of the Record to return
	 * @param	[boolean	$bolAsArray				]	TRUE	: Return associative array
	 * 													FALSE	: Return Delivery_Method object (default)
	 * @param	[boolean	$bolForceRecache		]	TRUE	: Refresh the Enumeration cache
	 * 													FALSE	: Use the existing cache if available (default)
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public static function getForId($intId, $bolAsArray=false, $bolForceRecache=false)
	{
		$arrCache	= self::getAll($bolForceRecache);
		
		// Return the cached instance
		if (array_key_exists($intId, $arrCache))
		{
			return ($bolAsArray) ? $arrCache[$intId]->toArray() : $arrCache[$intId];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * getAll()
	 *
	 * Returns an array of Delivery_Method objects or arrays
	 *
	 * @param	[boolean	$bolForceRecache		]	TRUE	: Refresh the Enumeration cache
	 * 													FALSE	: Use the existing cache if available (default)
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public static function getAll($bolForceRecache=false)
	{
		if (!self::$_arrStaticCache || $bolForceRecache)
		{
			self::$_arrStaticCache	= array();
			
			// Cache the Enumeration
			$selAll	= self::_preparedStatement('selAll');
			if ($selAll->Execute() === false)
			{
				throw new Exception($selAll->Error());
			}
			while ($arrEnum = $selAll->Fetch())
			{
				self::$_arrStaticCache[$arrEnum['id']]	= $arrEnum;
			}
		}
		
		return self::$_arrStaticCache;
	}
	
	/**
	 * getCustomerGroupSettings()
	 *
	 * Returns a Delivery_Method_Customer_Group object or array for the given Id
	 *
	 * @param	integer		$intCustomerGroupId					The Customer Group Id of the Record to return
	 * @param	[boolean	$bolAsArray				]	TRUE	: Return associative array
	 * 													FALSE	: Return Delivery_Method_Customer_Group object (default)
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function getCustomerGroupSettings($intCustomerGroupId, $bolAsArray=false, $bolForceRecache=false)
	{
		// Retrieve the Data
		$selCustomerGroupDeliveryMethod	= self::_preparedStatement('selCustomerGroupDeliveryMethod');
		if ($selAll->Execute(array('id'=>$this->id, 'customer_group_id'=>$intCustomerGroupId)) === false)
		{
			throw new Exception($selAll->Error());
		}
		if ($arrCustomerGroupDeliveryMethod = $selCustomerGroupDeliveryMethod->Fetch())
		{
			return ($bolAsArray) ? $arrCustomerGroupDeliveryMethod : new Delivery_Method_Customer_Group($arrCustomerGroupDeliveryMethod);
		}
		else
		{
			return null;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selCustomerGroupDeliveryMethod':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("customer_group_delivery_method", "*", "customer_group_id = <customer_group_id> AND delivery_method_id = <id>", "id DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
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