<?php
//----------------------------------------------------------------------------//
// Compression_Algorithm
//----------------------------------------------------------------------------//
/**
 * Compression_Algorithm
 *
 * Models a record of the compression_algorithm table
 *
 * Models a record of the compression_algorithm table
 *
 * @class	Compression_Algorithm
 */
class Compression_Algorithm extends ORM
{
	protected	$_strTableName	= "compression_algorithm";
	
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
	
	/**
	 * getForId()
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
	public static function getForId($intCompressionAlgorithmId, $bolAsArray=false, $bolRecache=false)
	{
		static	$arrCache;
		
		if (!$arrCache || $bolRecache)
		{
			// Cache the values
			$selAll	= self::_preparedStatement('selAll');
			if ($selAll->Execute() === false)
			{
				throw new Exception($selAll->Error());
			}
			
			while ($arrCompressionAlgorithm = $selAll->Fetch())
			{
				$arrCache[$arrCompressionAlgorithm['id']]	= $arrCompressionAlgorithm;
			}
		}
		return ($bolAsArray) ? $arrCache[$intCompressionAlgorithmId] : new Compression_Algorithm($arrCache[$intCompressionAlgorithmId]);
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"compression_algorithm", "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"compression_algorithm", "*", "1");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("compression_algorithm");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("compression_algorithm");
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