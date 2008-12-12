<?php
/**
 * Sale_Item
 *
 * Models a record of the sale_item table
 *
 * @class	Sale_Item
 */
class Sale_Item extends ORM
{
	protected	$_strTableName	= "sale_item";
	
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
	
	// Returns the Flex sale_item object relating to $intServiceId (from sale_item table of flex database)
	// returns NULL if there is no sale_item relating to this record
	// There should only ever be (at most) 1 sale_item record relating to a service record 
	public static function getForServiceId($intServiceId, $bolIncludeEarlierServiceRecords=FALSE)
	{
		$objQuery = new Query();
		
		$intServiceId = intval($intServiceId);
		
		if ($bolIncludeEarlierServiceRecords)
		{
			// Include all previous service records that modelled the same service as $intServiceId does
			$strQuery = "	SELECT si.*
							FROM sale_item AS si INNER JOIN Service AS s ON si.service_id = s.Id
							WHERE s.FNN = (	SELECT FNN 
											FROM Service 
											WHERE Id = $intServiceId)
							AND si.service_id <= $intServiceId
							ORDER BY si.service_id DESC
							LIMIT 1;";
		}
		else
		{
			// Only find the most recent sale_item related directly to $intServiceId
			// There should only ever be, at most, one sale_item record relating to a service record, but this can't be gauranteed by the database
			// (not unless I stuck a uniqueness contraint on sale_item.service_id)
			$strQuery = "	SELECT *
							FROM sale_item
							WHERE service_id = $intServiceId
							ORDER BY id DESC
							LIMIT 1;";
		}
		
		if (($mixResult = $objQuery->Execute($strQuery)) === FALSE)
		{
			throw new Exception(__METHOD__ ." Failed to retrieve sale_item record using query - $strQuery - ". $objQuery->Error());
		}
		
		$mixRecord = $mixResult->fetch_assoc();
		
		if ($mixRecord === NULL)
		{
			return NULL;
		}
		else
		{
			return new self($mixRecord);
		}
	}
	
	// Returns the sale_item record which has the external reference
	public static function getForExternalReference($strExternalReference)
	{
		$objQuery = new Query();
		
		$strExternalReference = $objQuery->EscapeString($strExternalReference);
		
		$strQuery = "SELECT * FROM sale_item WHERE external_reference = '$strExternalReference' ORDER BY id DESC LIMIT 1;";
		
		if (($mixResult = $objQuery->Execute($strQuery)) === FALSE)
		{
			throw new Exception(__METHOD__ ." Failed to retrieve sale_item record using query - $strQuery - ". $objQuery->Error());
		}
		
		$mixRecord = $mixResult->fetch_assoc();
		
		if ($mixRecord === NULL)
		{
			return NULL;
		}
		else
		{
			return new self($mixRecord);
		}
	}
	
	// Retrieves the value part from the sale_item.external_reference string
	// This string should be of the form "sale_item.id=123" where 123 is the value 
	public function getExternalReferenceValue()
	{
		return intval(substr($this->externalReference, 13));
	}
	
	// Retrieves the DO_Sales_SaleItem object related to this object
	// Returns NULL if it can't be found
	public function getExternalReferenceObject()
	{
		try
		{
			$doSaleItem = DO_Sales_SaleItem::getForId($this->getExternalReferenceValue());
			
			if ($doSaleItem !== NULL)
			{
				return $doSaleItem;
			}
			throw new Exception("External Object was not found");
		}
		catch (Exception $e)
		{
			throw new Exception("Failed to retrieve externally referenced object for sale_item record with id: {$this->id}, ExternalReference: {$this->externalReference} - ". $e->getMessage());
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"sale_item", "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("sale_item");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("sale_item");
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