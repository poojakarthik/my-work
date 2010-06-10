<?php
/**
 * FollowUp
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp
 */
class FollowUp extends ORM_Cached
{
	protected 			$_strTableName			= "followup";
	protected static	$_strStaticTableName	= "followup";

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

	public function getDetails()
	{
		$aDetails	= array();
		switch ($this->followup_type_id)
		{
			case FOLLOWUP_TYPE_NOTE:
				$oFollowUpNote	= FollowUp_Note::getForFollowUpId($this->id);
				if ($oFollowUpNote)
				{
					$oNote	= Note::getForId($oFollowUpNote->note_id);
					if ($oNote)
					{
						if ($oNote->Account)
						{
							$oAccount					= Account::getForId($oNote->Account);
							$aDetails['account_id']		= $oAccount->Id;
							$aDetails['account_name']	= $oAccount->BusinessName;
							$aDetails['customer_group']	= $oAccount->getCustomerGroup()->internalName;
						}
						
						if ($oNote->Service)
						{
							$oService					= Service::getForId($oNote->Service);
							$aDetails['service_id']		= $oService->Id;
							$aDetails['service_fnn']	= $oService->FNN;
						}
						
						if ($oNote->Contact)
						{
							$oContact					= Contact::getForId($oNote->Contact);
							$aDetails['contact_id']		= $oContact->Id;
							$aDetails['contact_name']	= "{$oContact->FirstName} {$oContact->LastName}";
						}
					}
				}
				break;
			case FOLLOWUP_TYPE_ACTION:
				$oFollowUpAction	= FollowUp_Action::getForFollowUpId($this->id);
				
				// Check the action_association_type (through the action_type & action_type_action_association_type)
				if ($oFollowUpAction)
				{
					$oAction	= Action::getForId($oFollowUpAction->action_id);
					if ($oAction)
					{
						$aAccounts	= $oAction->getAssociatedAccounts();
						foreach ($aAccounts as $iAccountId => $oAccount)
						{
							$aDetails['account_id']		= $iAccountId;
							$aDetails['account_name']	= $oAccount->BusinessName;
							$aDetails['customer_group']	= $oAccount->getCustomerGroup()->internalName;
						}
						
						$aServices	= $oAction->getAssociatedServices();
						foreach ($aServices as $iServiceId => $oService)
						{
							$aDetails['service_id']		= $iServiceId;
							$aDetails['service_fnn']	= $oService->FNN;
						}
						
						$aContacts	= $oAction->getAssociatedContacts();
						foreach ($aContacts as $iContactId => $oContact)
						{
							$aDetails['contact_id']		= $iContactId;
							$aDetails['contact_name']	= $oContact->FirstName.' '.$aContacts[0]->LastName;
						}
					}
				}
				break;
			case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				$oFollowUpTicketCorrespondence	= FollowUp_Ticketing_Correspondence::getForFollowUpId($this->id);
				
				if ($oFollowUpTicketCorrespondence)
				{
					$oTicketingCorrespondence	= Ticketing_Correspondance::getForId($oFollowUpTicketCorrespondence->ticketing_correspondence_id);
					if ($oTicketingCorrespondence)
					{
						$oTicket	= $oTicketingCorrespondence->getTicket();
						if ($oTicket)
						{
							$aDetails['ticket_id']	= $oTicket->id;
							
							if ($oTicket->account_id)
							{
								$oAccount					= Account::getForId($oTicket->account_id);
								$aDetails['account_id']		= $oAccount->Id;
								$aDetails['account_name']	= $oAccount->BusinessName;
								$aDetails['customer_group']	= $oAccount->getCustomerGroup()->internalName;
							}
						}
						
						$oContact	= $oTicketingCorrespondence->getContact();
						if ($oContact)
						{
							$aDetails['ticket_contact_name']	= $oContact->getName();
						}
					}
				}
				break;
		}
		
		return $aDetails;
	}

	public function getSummary($iCharacterLimit=30)
	{
		$sSummary	= '';
		switch ($this->followup_type_id)
		{
			case FOLLOWUP_TYPE_NOTE:
				$oFollowUpNote	= FollowUp_Note::getForFollowUpId($this->id);
				if ($oFollowUpNote)
				{
					$oNote	= Note::getForId($oFollowUpNote->note_id);
					if ($oNote)
					{
						$sSummary	= $oNote->Note;
					}
				}
				break;
			case FOLLOWUP_TYPE_ACTION:
				$oFollowUpAction	= FollowUp_Action::getForFollowUpId($this->id);
				
				// Check the action_association_type (through the action_type & action_type_action_association_type)
				if ($oFollowUpAction)
				{
					$oAction	= Action::getForId($oFollowUpAction->action_id);
					if ($oAction)
					{
						$sSummary	= $oAction->details;
					}
				}
				break;
			case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				$oFollowUpTicketCorrespondence	= FollowUp_Ticketing_Correspondence::getForFollowUpId($this->id);
				
				if ($oFollowUpTicketCorrespondence)
				{
					$oTicketingCorrespondence	= Ticketing_Correspondance::getForId($oFollowUpTicketCorrespondence->ticketing_correspondence_id);
					if ($oTicketingCorrespondence)
					{
						$sSummary	= $oTicketingCorrespondence->details;
					}
				}
				break;
		}
		
		// Remove whitespace
		$sSummary	= preg_replace('/\s/', ' ', $sSummary);
		
		// Limit to $iCharacterLimit characters (30 is default)
		if (strlen($sSummary) > $iCharacterLimit)
		{
			return substr($sSummary, 0, $iCharacterLimit).'...';
		}
		
		return $sSummary;
	}
	
	public function save()
	{
		// Update modified fields
		$this->modified_datetime	= date('Y-m-d H:i:s');
		$this->modified_employee_id	= Flex::getUserId();
		
		parent::save();
		
		// Create followup_history record
		$oFollowUpHistory						= new FollowUp_History();
		$oFollowUpHistory->followup_id			= $this->id;
		$oFollowUpHistory->due_datetime			= $this->due_datetime;
		$oFollowUpHistory->assigned_employee_id	= $this->assigned_employee_id;
		$oFollowUpHistory->modified_datetime	= $this->modified_datetime;
		$oFollowUpHistory->modified_employee_id	= $this->modified_employee_id;
		$oFollowUpHistory->save();
	}

	public static function searchFor($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null, $bCountOnly=false)
	{
		// Build query parts based on the limit, offset, sort and filter parameters
		$sFromClause	= '	followup f 
							LEFT JOIN	followup_closure fc 
											ON f.followup_closure_id = fc.id';
		$sSelectClause	= 'f.*, fc.followup_closure_type_id';
		$sWhereClause	= '';
		$sOrderByClause	= '';
		$sLimitClause	= '';
		$oQuery			= new Query();
		
		// WHERE clause for followup_search (includes closure constraints)
		$aWhereInfoSearch	= StatementSelect::generateWhere(null, $aFilter);
		
		// WHERE clause for followup. Any closure constraints are removed so that closed recurring fup iterations are included
		// in the temp table. These constraints are used when querying the resulting temp table (using $aWhereInfoSearch, defined above). 
		unset($aFilter['followup_closure_id']);
		unset($aFilter['followup_closure_type_id']);
		$aWhereInfoFollowUp	= StatementSelect::generateWhere(null, $aFilter);
		
		// ORDER BY clause (with field alias' for category and type)
		$sOrderByClause	=	StatementSelect::generateOrderBy(
								array(
									'followup_category_id'	=> 'fcat.name',
									'followup_type_id'		=> 'ft.name',
									'assigned_employee_id'	=> "CONCAT(e.FirstName, ' ', e.LastName)"
								), 
								$aSort
							);
		
		// LIMIT clause
		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);
			
		// Get followups (ignore orderby and limit for this query, will be done on the temporary table)
		$oFollowUpSelect	= new StatementSelect($sFromClause, $sSelectClause, $aWhereInfoFollowUp['sClause'], '', '');
		if ($oFollowUpSelect->Execute($aWhereInfoFollowUp['aValues']) === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oFollowUpSelect->Error());
		}
		
		// Search for recurring followups, first remove followup specific fields from the filter
		unset($aFilter['due_datetime']);
		$aRecurringFollowUps	= FollowUp_Recurring::searchFor(null, null, null, $aFilter);
		
		// Create temporary table 'followup_search'
		$mTempTablCheckResult	= $oQuery->Execute("	SELECT	count(*) 
														FROM 	followup_search");
		if ($mTempTablCheckResult === false)
		{
			$mResult	= $oQuery->Execute("	CREATE TEMPORARY TABLE followup_search
												(
													followup_id						INT			UNSIGNED	NULL,
													assigned_employee_id			BIGINT		UNSIGNED	NOT NULL,
													created_datetime				DATETIME				NOT NULL,
													due_datetime					DATETIME				NOT NULL,
													followup_type_id				INT			UNSIGNED	NOT NULL,
													followup_category_id			INT			UNSIGNED	NOT NULL,
													followup_closure_id				INT			UNSIGNED	NULL,
													followup_closure_type_id		INT						NULL,
													closed_datetime					DATETIME				NULL		DEFAULT NULL,
													followup_recurring_id			INT			UNSIGNED	NULL,
													followup_recurring_iteration	INT						NULL,
													modified_datetime				DATETIME				NOT NULL,
													modified_employee_id			BIGINT		UNSIGNED	NOT NULL,
													status							VARCHAR(128)			NOT NULL
												)");
			
			if ($mResult === false)
			{
				throw new Exception("Error creating temporary table. Database Error = ".$oQuery->Error());
			}
		}
		else
		{
			// Clear followup_search
			$oQuery->Execute("TRUNCATE followup_search");
		}
		
		// Insert followups into 'followup_search'
		while ($aRow = $oFollowUpSelect->Fetch())
		{
			// Build insert query, get each followup column value (except id, that is put into 'followup_id')
			$sInsert	= 	sprintf(
								"	INSERT INTO	followup_search (
													followup_id,
													assigned_employee_id, 
													created_datetime,
													due_datetime,
													followup_type_id,
													followup_category_id,
													followup_closure_id,
													followup_closure_type_id,
													closed_datetime,
													followup_recurring_id,
													modified_datetime,
													modified_employee_id,
													status
												)
									VALUES		(%s, %s, '%s', '%s', %s, %s, %s, %s, '%s', %s, '%s', %s, %s)",
								$aRow['id'],
								$aRow['assigned_employee_id'],
								$aRow['created_datetime'],
								$aRow['due_datetime'],
								$aRow['followup_type_id'],
								$aRow['followup_category_id'],
								$aRow['followup_closure_id'] 		? $aRow['followup_closure_id'] 		: 'NULL',
								$aRow['followup_closure_type_id'] 	? $aRow['followup_closure_type_id']	: 'NULL',
								$aRow['closed_datetime'] 			? $aRow['closed_datetime']			: 'NULL',
								$aRow['followup_recurring_id']		? $aRow['followup_recurring_id'] 	: 'NULL',
								$aRow['modified_datetime'],
								$aRow['modified_employee_id'],
								"'".FollowUp::getStatus($aRow['followup_closure_id'], $aRow['due_datetime'])."'"
							);
			
			$mResult	= $oQuery->Execute($sInsert);
			
			if ($mResult === false)
			{
				throw new Exception("Error inserting followup into temporary table. Database Error = ".$oQuery->Error().". Query = {$sInsert}");
			}
		}
		
		// Insert recurring followups into 'followup_search'
		foreach ($aRecurringFollowUps as $iId => $oRecurringFollowUp)
		{
			// Get all of the projected followups for this recurring followup
			$iEndDate		= strtotime($oRecurringFollowUp->end_datetime);
			$i				= 0;
			$iProjectedDate	= strtotime($oRecurringFollowUp->start_datetime);
			//echo "$iId::{$oRecurringFollowUp->start_datetime}::{$oRecurringFollowUp->start_datetime}\n";
			
			while(($iProjectedDate <= $iEndDate) && ($i < FollowUp_Recurring::ITERATION_LIMIT))
			{
				// Projected date is within recurrence date limit, convert into db string
				$sDueDateTime	= date('Y-m-d H:i:s', $iProjectedDate);
				//echo "  --- $iId::$i::$sDueDateTime::{$oRecurringFollowUp->end_datetime}\n";
				
				// Check that the iteration doesn't already exist as a once off (closed) follow-up
				$sCheck	=	sprintf(
								"	SELECT	followup_recurring_id
									FROM	followup_search
									WHERE	followup_recurring_id = %s
									AND		due_datetime = '%s'",
								$oRecurringFollowUp->id,
								$sDueDateTime
							);
						
				$mCheckResult	= $oQuery->Execute($sCheck);
				
				//echo "RECURR: ".var_dump($mCheckResult, true)."\n--".$sCheck."\n--".print_r($mCheckResult->fetch_assoc(), true)."\n--{$mCheckResult->num_rows}\n";
				
				if ($mCheckResult === false)
				{
					throw new Exception("Error looking for recurring followup iteration in temporary table. Database Error = ".$oQuery->Error().". Query = {$sCheck}");
				}
				else 
				{
					if ($mCheckResult->num_rows == 0)
					{
						// Insert new followup
						$sInsert	= 	sprintf(
											"	INSERT INTO	followup_search (
																assigned_employee_id, 
																created_datetime,
																due_datetime,
																followup_type_id,
																followup_category_id,
																followup_recurring_id,
																followup_recurring_iteration,
																modified_datetime,
																modified_employee_id,
																status
															)
												VALUES		(%s, '%s', '%s', %s, %s, %s, %s, '%s', %s, %s)",
											$oRecurringFollowUp->assigned_employee_id,
											$oRecurringFollowUp->created_datetime,
											$sDueDateTime,
											$oRecurringFollowUp->followup_type_id,
											$oRecurringFollowUp->followup_category_id,
											$oRecurringFollowUp->id,
											$i,
											$oRecurringFollowUp->modified_datetime,
											$oRecurringFollowUp->modified_employee_id,
											"'".FollowUp::getStatus(null, $sDueDateTime)."'"
										);
						
						//echo "> INSERT - oRecurringFollowUp->id:: $i $sDueDateTime\n";
						$mInsertResult	= $oQuery->Execute($sInsert);
						if ($mInsertResult === false)
						{
							throw new Exception("Error inserting recurring followup into temporary table. Database Error = ".$oQuery->Error().". Query = {$sInsert}");
						}
					}
					else
					{
						// Update existing one, set it's iteration
						$sUpdate	= 	sprintf(
											"	UPDATE	followup_search
												SET		followup_recurring_iteration = %s
												WHERE	followup_recurring_id = %s
												AND		due_datetime = '%s'",
											$i,
											$oRecurringFollowUp->id,
											$sDueDateTime
										);
						
						//echo "> UPDATE - $oRecurringFollowUp->id:: $i $sDueDateTime\n";
						$mUpdateResult	= $oQuery->Execute($sUpdate);
						if ($mUpdateResult === false)
						{
							throw new Exception("Error inserting recurring followup into temporary table. Database Error = ".$oQuery->Error().". Query = {$sInsert}");
						}
					}
				}
				
				// Calculates the projected followup date given an iteration
				$i++;
				$iProjectedDate	= $oRecurringFollowUp->getProjectedDueDate($i);
			}
		}
		
		/*echo '------------------------\n';
		$res	= $oQuery->Execute("select * from followup_search");
		while ($r = $res->fetch_assoc())
		{
			echo print_r($r, true).'\n';
		}
		echo '------------------------\n';*/
		
		// Query 'followup_search'
		$sSearchFrom	= '	followup_search fs
							JOIN	followup_category fcat
										ON fs.followup_category_id = fcat.id
							JOIN	followup_type ft
										ON fs.followup_type_id = ft.id
							JOIN	Employee e
										ON fs.assigned_employee_id = e.Id';
		$sSelect		= 'fs.*';
		
		if ($bCountOnly)
		{
			$sOrderByClause	= '';
			$sLimitClause	= '';
			$sSelect		= 'COUNT(COALESCE(fs.followup_id, fs.followup_recurring_id)) AS count';
		}
		
		$oFollowUpSearchSelect	= new StatementSelect($sSearchFrom, $sSelect, $aWhereInfoSearch['sClause'], $sOrderByClause, $sLimitClause);
		
		//echo 'QUERY: '.$oFollowUpSearchSelect->_strQuery.'\n';
		//echo 'VALUES: '.print_r($aWhereInfoSearch['aValues'], true).'\n';
		
		if ($oFollowUpSearchSelect->Execute($aWhereInfoSearch['aValues']) === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oFollowUpSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aCount	= $oFollowUpSearchSelect->Fetch();
			return $aCount['count'];
		}
		else
		{
			return $oFollowUpSearchSelect->FetchAll();
		}
	}

	public static function getStatus($iFollowUpClosureId, $sDueDateTime)
	{
		if ($iFollowUpClosureId)
		{
			// The followup has been closed, return the name of the followup_closure
			return FollowUp_Closure::getForId($iFollowUpClosureId)->name;
		}
		else
		{
			// Active, check the date to see if overdue or current
			$iDueDate	= strtotime($sDueDateTime);
			$iNow		= time();
			
			if ($iDueDate >= $iNow)
			{
				// Current
				return 'Current';
			}
			else
			{
				// Overdue
				return 'Overdue';
			}
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
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