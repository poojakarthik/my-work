<?php
/**
 * Dealer_Config
 *
 * Models the Dealer Config table
 * This class shouldn't really extend ORM because the rows of the dealer config table describe the history of the dealer_config.
 * This means the entire table is effectively modelling the one object, and ORM is not intended for that sort of thing
 *
 * @class	Dealer_Config
 */
class Dealer_Config extends ORM
{
	protected	$_strTableName	= "dealer_config";
	
	// This will return the current Dealer_Config object
	// This will throw an exception if it can't find the record.  It is assumed there is always at least one record in the dealer_config table
	public static function getConfig()
	{
		$selDealerConfig = new StatementSelect("dealer_config", "*", "TRUE", "id DESC", "1");
		
		if (($intCount = $selDealerConfig->Execute()) === FALSE)
		{
			throw new Exception("Failed to retrieve the dealer_config record. - ". $selDealerConfig->Error());
		}
		
		if ($intCount == 0)
		{
			throw new Exception("Failed to retrieve the dealer_config record.  No records exist in this table.");
		}
		
		$arrRecord = $selDealerConfig->Fetch();
		
		return new self($arrRecord);
	}
	
	// Returns array of all dealers who are eligible for becoming the DefaultEmployeeManagerDealer
	public static function getEligibleEmployeeManagerDealers()
	{
		// Only Employees are eligible
		$strWhere	= "id != ". Dealer::SYSTEM_DEALER_ID . " AND dealer_status_id = ". Dealer_Status::ACTIVE ." AND employee_id IS NOT NULL";
		$arrOrderBy	= array("username" => TRUE);
		
		return Dealer::getAll($strWhere, $arrOrderBy);
	}
	
	public function save()
	{
		// Retrieve the current configuration, and compare to see if anything was changed, and if so, set the id field to NULL so a new record is created
		$objCurrentConfig = self::getConfig();
		
		$arrCurrentConfig = $objCurrentConfig->toArray(TRUE);
		
		unset($arrCurrentConfig['id']);
		
		// Check if anything has changed
		$bolHasChanges = FALSE;
		foreach ($arrCurrentConfig as $strTidyName=>$mixValue)
		{
			if ($this->{$strTidyName} != $mixValue)
			{
				$bolHasChanges = TRUE;
				break;
			}
		}
		
		if ($bolHasChanges)
		{
			parent::save();
		}
	}
	
	public static function parseConfigDetails($arrDetails)
	{
		$arrDealers	= Dealer_Config::getEligibleEmployeeManagerDealers();
		
		$arrProblems = array();
		
		// Make sure defaultEmployeeManagerDealerId is valid
		if (!array_key_exists("defaultEmployeeManagerDealerId", $arrDetails))
		{
			$arrProblems[] = "defaultEmployeeManagerDealerId has not been declared";
			return $arrProblems;
		}
		
		if ($arrDetails['defaultEmployeeManagerDealerId'] !== NULL && !array_key_exists($arrDetails['defaultEmployeeManagerDealerId'], $arrDealers))
		{
			$arrProblems[] = "dealer with id: {$arrDetails['defaultEmployeeManagerDealerId']} cannot be used for defaultEmployeeManagerDealerId";
			return $arrProblems;
		}
		
		$objDealerConfig = new Dealer_Config();
		foreach ($arrDetails as $strTidyName=>$mixValue)
		{
			$objDealerConfig->{$strTidyName} = $mixValue;
		}
		
		return $objDealerConfig;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"dealer_config", "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("dealer_config");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("dealer_config");
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