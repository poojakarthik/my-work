<?php
//----------------------------------------------------------------------------//
// Provisioning_Request
//----------------------------------------------------------------------------//
/**
 * Provisioning_Request
 *
 * Models a record of the ProvisioningRequest table
 *
 * Models a record of the ProvisioningRequest table
 *
 * @class	Provisioning_Request
 */
class Provisioning_Request extends ORM {	
	protected $_strTableName = "ProvisioningRequest";
	
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
	public function __construct($aProperties=Array(), $bLoadById=FALSE) {
		// Parent constructor
		parent::__construct($aProperties, $bLoadById);
	}
	
	public function cancel() {
		// Only requests with Status == REQUEST_STATUS_WAITING can be cancelled
		if ($this->Status != REQUEST_STATUS_WAITING) {
			throw new Exception("Request cannot be cancelled as it has already been sent");
		}
		
		// Update the status of the request
		$this->Status = REQUEST_STATUS_CANCELLED;
		$this->save();
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
	 * @param	string		$sStatement						Name of the statement
	 * 
	 * @return	Statement									The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect("ProvisioningRequest", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert("ProvisioningRequest");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById("ProvisioningRequest");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>