<?php
/**
 * Email
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Email
 */
class Email extends ORM_Cached
{
	protected 			$_strTableName			= "email";
	protected static	$_strStaticTableName	= "email";

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	public function setStatus($iEmailStatus)
	{
		$this->email_status_id	= $iEmailStatus;
		$this->save();
	}
	
	public function getAttachments()
	{
		return Email_Attachment::getForEmail($this->id);
	}
	
	public static function getForQueue($iEmailQueueId)
	{
		$oStmt	= self::_preparedStatement('selByQueue');
		$oStmt->Execute(array('email_queue_id' => $iEmailQueueId));
		
		$aQueues	= array();
		while($aRow = $oStmt->Fetch())
		{
			$aQueues[$aRow['id']]	= new self($aRow);
		}
		return $aQueues;
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$aAliases = array(
			'id'					=> "e.id",
			'recipients'			=> "e.recipients",
			'sender'				=> "e.sender",
			'subject'				=> "e.subject",
			'email_status_id'		=> "e.email_status_id",
			'email_status_name'		=> "es.name",
			'created_datetime'		=> "e.created_datetime",
			'created_employee_id'	=> "e.created_employee_id",
			'created_employee_name'	=> "CONCAT(e_created.FirstName, ' ', e_created.LastName)"
		);
		
		$sFrom = "	email e
					JOIN email_status es ON (es.id = e.email_status_id)
					JOIN Employee e_created ON (e_created.Id = e.created_employee_id)";
		if ($bCountOnly) {
			$sSelect 	= "COUNT(e.id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		} else {
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause) {
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect	= implode(', ', $aSelectLines);
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false) {
			throw new Exception_Database("Failed to get search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly) {
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selByQueue':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "email_queue_id = <email_queue_id>", "created_datetime ASC");
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