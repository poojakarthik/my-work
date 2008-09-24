<?php

//----------------------------------------------------------------------------//
// Employee_Message
//----------------------------------------------------------------------------//
/**
 * Employee_Message
 *
 * Models and employee_message record
 *
 * Models and employee_message record
 *
 * @class	Employee_Message
 */
class Employee_Message
{
	private $id				= NULL;
	private $createdOn		= NULL;
	private $effectiveOn	= NULL;
	private $message		= NULL;

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
	 * @param		array	$arrProperties 	Optional.  Associative array defining an Employee Message with keys for each field of the employee_message table
	 * @return		void
	 * @constructor
	 */
	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}
	
	// Inserts record into the employee_message table
	// throws an exception on failure
	// If $bolGetAsObject == FALSE then returns the id of the employee_message record
	// If $bolGetAsObject == TRUE then returns an Employee_Message object defining the Employee Message
	public static function declareMessage($strMessage, $strEffectiveOn, $bolGetAsObject=FALSE)
	{
		$arrData = array(	"created_on"	=> new MySQLFunction("NOW()"),
							"effective_on"	=> $strEffectiveOn,
							"message"		=> $strMessage
						);
		
		$insMessage = new StatementInsert("employee_message", $arrData);
		
		if (($intId = $insMessage->Execute($arrData)) === FALSE)
		{
			throw new Exception("Failed to save employee message: $strMessage - ". $insMessage->Error());
		}
		
		return ($bolGetAsObject)? self::getForId($intId) : $intId;
	}
	
	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Employee_Message object with the id specified
	 * 
	 * Returns the Employee_Message object with the id specified
	 *
	 * @param	int 		$intId		id of the employee_message record to retrieve		
	 * @return	mixed 							Employee_Message object	: if it exists
	 * 											NULL					: if it doesn't exist
	 * @method
	 */
	public static function getForId($intId)
	{
		static $selMessage;
		if (!isset($selMessage))
		{
			$selMessage = new StatementSelect("employee_message", self::getColumns(), "id = <Id>");
		}
		
		if (($intRecCount = $selMessage->Execute(array("Id"=>$intId))) === FALSE)
		{
			throw new Exception("Failed to retrieve Employee Message with id: $intId - ". $selEmployee->Error());
		}
		if ($intRecCount == 0)
		{
			// The record could not be found
			return NULL;
		}
		
		return new Employee_Message($selMessage->Fetch());
	}

	// Retrieves the most recent $intMaxRecords records that aren't overridden
	// returns an empty array, if no records were found 
	public static function getAll($intMaxRecords=20)
	{
		$qryQuery = new Query();
		
		$strColumns = implode(", ", self::getColumns());
		
		$strQuery = "	SELECT $strColumns
						FROM employee_message AS em1
						WHERE (	SELECT COUNT(id)
								FROM employee_message AS em2
								WHERE em2.id > em1.id AND em2.effective_on <= em1.effective_on AND em2.created_on >= em1.created_on
								) = 0
						ORDER BY em1.created_on DESC, em1.effective_on DESC
						LIMIT $intMaxRecords;";
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve employee_message records - " . $qryQuery->Error());
		}

		$arrRecordSet = array();
		
		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrRecordSet[] = new Employee_Message($arrRecord);
		}

		return $arrRecordSet;
	}
	
	// returns the effective message as of $strTime.  if $strTime is NULL, then it uses NOW()
	// returns Employee_Message object, if one was found, else returns NULL
	public static function getForTime($strTime=NULL)
	{
		$qryQuery	= new Query();
		
		if ($strTime === NULL)
		{
			$strTime = GetCurrentISODateTime();
		}
		
		$strColumns	= implode(", ", self::getColumns());
		
		$strQuery = "	SELECT $strColumns
						FROM employee_message AS em1
						WHERE effective_on <= '$strTime'
						ORDER BY created_on DESC
						LIMIT 1;";
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve Employee Message for time $strTime - " . $qryQuery->Error());
		}

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{	
			// There should at most be 1 record
			return new Employee_Message($arrRecord);
		}

		return NULL;
	}

	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the employee_message table
	 * 
	 * Returns array defining the columns of the employee_message table
	 *
	 * @return		array
	 * @method
	 */
	protected static function getColumns()
	{
		return array(
						"id"			=> "id",
						"created_on"	=> "created_on",
						"effective_on"	=> "effective_on",
						"message"		=> "message"
					);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Employee_Message object
	 * 
	 * Initialises the Employee_Message object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of employee_message table
	 * @return		void
	 * @method
	 */
	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}
	}


	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * accessor method
	 * 
	 * accessor method
	 *
	 * @param	string	$strName	name of property to get. in either of the formats xxxYyyZzz or xxx_yyy_zzz 
	 * @return	void
	 * @method
	 */
	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// tidyName
	//------------------------------------------------------------------------//
	/**
	 * tidyName()
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * 
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * If the string is already in the xxxYxxZzz format, then it will not be changed
	 *
	 * @param	string	$strName
	 * @return	string
	 * @method
	 */
	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
	
	
	
}

?>
