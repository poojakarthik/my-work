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
							$oAccount						= Account::getForId($oNote->Account);
							$aDetails['account_id']			= $oAccount->Id;
							$aDetails['account_name']		= $oAccount->BusinessName;
							$aDetails['customer_group']		= $oAccount->getCustomerGroup()->internalName;
							$aDetails['customer_group_id']	= $oAccount->getCustomerGroup()->id;
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
						
						$aDetails['note_id']		= $oNote->Id;
						$aDetails['note_type_id']	= $oNote->NoteType;
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
							$aDetails['account_id']			= $iAccountId;
							$aDetails['account_name']		= $oAccount->BusinessName;
							$aDetails['customer_group']		= $oAccount->getCustomerGroup()->internalName;
							$aDetails['customer_group_id']	= $oAccount->getCustomerGroup()->id;
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
						
						$aDetails['action_id']	= $oAction->id;
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
								$oAccount						= Account::getForId($oTicket->account_id);
								$aDetails['account_id']			= $oAccount->Id;
								$aDetails['account_name']		= $oAccount->BusinessName;
								$aDetails['customer_group']		= $oAccount->getCustomerGroup()->internalName;
								$aDetails['customer_group_id']	= $oAccount->getCustomerGroup()->id;
							}
						}
						
						$oContact	= $oTicketingCorrespondence->getContact();
						if ($oContact)
						{
							$aDetails['ticket_contact_name']	= $oContact->getName();
						}
						
						$aDetails['ticketing_correspondence_id']	= $oTicketingCorrespondence->id;
					}
				}
				break;
		}
		
		return $aDetails;
	}

	public function getSummary($iCharacterLimit=30, $bRemoveWhitespace=true)
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
		if ($bRemoveWhitespace)
		{
			$sSummary	= preg_replace('/\s/', ' ', $sSummary);
		}
		
		// Limit to $iCharacterLimit characters (30 is default)
		if (!is_null($iCharacterLimit) && (strlen($sSummary) > $iCharacterLimit))
		{
			return substr($sSummary, 0, $iCharacterLimit).'...';
		}
		
		return $sSummary;
	}
	
	public function assignTo($iEmployeeId, $iReassignReasonId)
	{
		// Do reassign
		$this->assigned_employee_id	= $iEmployeeId;
		$this->save(null, $iReassignReasonId);
		
		// Send email (need to set include path for the zend mail class require to work)
		set_include_path(get_include_path().PATH_SEPARATOR.realpath(dirname(__FILE__).'/../'));
		require_once 'Zend/Mail.php';
		
		$oEmployee	= Employee::getForId($iEmployeeId);
		if ($oEmployee->email)
		{
			$sUserEmail		= $oEmployee->email;
			
			// DEBUG
			//$sUserEmail		= "rmctainsh@yellowbilling.com.au";
			// DEBUG
			
			$aDetails		= $this->getDetails();
			$oCustomerGroup	= Customer_Group::getForId($aDetails['customer_group_id']);
			$sAssignedBy	= Flex::getDisplayName();
			$sUrl			= $oCustomerGroup->flexUrl."/admin/reflex.php/FollowUp/Manage/#{$this->id}";
			$sType			= Constant_Group::getConstantGroup('followup_type')->getConstantName($this->followup_type_id);
			$sCategory		= FollowUp_Category::getForId($this->followup_category_id)->name;
			$sDueOn			= date('l jS M Y g:i A', strtotime($this->due_datetime));
			$sEmailContent	=	"<div style='font-family: Calibri,sans-serif;'>\n" .
								"	You have been assigned a Follow-Up (of type '{$sType}') by {$sAssignedBy} with a category of '{$sCategory}' that is due on <span style='font-weight: bold;'>{$sDueOn}</span>.<br/><br/>\n" .
								"	<a href='{$sUrl}'>Click here</a> to go to your Follow-Up Management page.\n" .
								"</div>";
			
			$oEmail	= new Zend_Mail();
			$oEmail->setBodyHtml($sEmailContent);
			$oEmail->setFrom("followups@ybs.net.au");
			$oEmail->addTo($sUserEmail, $oEmployee->getName());
			$oEmail->setSubject("You have been assigned a follow-up");
			$oEmail->send();
		}
	}
	
	public function save($mModifyReasonId=null, $iReassignReasonId=null)
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
		
		if (!is_null($mModifyReasonId))
		{
			// Modified
			// Create a modification record for the history record and the given modification reason id
			$oModify					= new FollowUp_History_Modify_Reason();
			$oModify->history_id		= $oFollowUpHistory->id;
			$oModify->modify_reason_id	= $mModifyReasonId;
			$oModify->save();
		}
		else if (!is_null($iReassignReasonId))
		{
			// Reassigned
			// Create a modification record for the history record and the given reassign reason id
			$oModify						= new FollowUp_History_Reassign_Reason();
			$oModify->history_id			= $oFollowUpHistory->id;
			$oModify->reassign_reason_id	= $iReassignReasonId;
			$oModify->save();
		}
	}

	public static function getForDateAndRecurringId($sDueDateTime, $iFollowUpRecurringId)
	{
		$oSelect	= self::_preparedStatement('selByDueDatetimeAndFollowUpRecurringId');
		$oSelect->Execute(array('due_datetime' => $sDueDateTime, 'followup_recurring_id' => $iFollowUpRecurringId));
		if ($aRow	= $oSelect->Fetch())
		{
			return new self($aRow);
		}
		else
		{
			return false;
		}
	}
	
	public static function getForRecurringId($iFollowUpRecurringId)
	{
		$oSelect	= self::_preparedStatement('selByFollowUpRecurringId');
		$oSelect->Execute(array('followup_recurring_id' => $iFollowUpRecurringId));
		$aResults	= array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']]	= new self($aRow);
		}
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
		$iNow			= time();
		
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
			$iAfterNow		= 0;
			$iProjectedDate	= strtotime($oRecurringFollowUp->start_datetime);
			
			while(($iProjectedDate <= $iEndDate) && ($iAfterNow <= FollowUp_Recurring::ITERATION_LIMIT))
			{
				// Projected date is within recurrence date limit, convert into db string
				$sDueDateTime	= date('Y-m-d H:i:s', $iProjectedDate);
				
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
						
						$mUpdateResult	= $oQuery->Execute($sUpdate);
						if ($mUpdateResult === false)
						{
							throw new Exception("Error inserting recurring followup into temporary table. Database Error = ".$oQuery->Error().". Query = {$sInsert}");
						}
					}
				}
				
				// Calculates the projected followup date given an iteration
				$i++;
				$iProjectedDate	= $oRecurringFollowUp->getProjectedDueDateSeconds($i);
				if ($iProjectedDate > $iNow)
				{
					$iAfterNow++;
				}
			}
		}
		
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

	public static function getStatus($iFollowUpClosureId, $sDueDateTime, $iNowSeconds=false)
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
			$iNow		= ($iNowSeconds ? $iNowSeconds : time());
			
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
	
	public function isClosed()
	{
		return !(is_null($this->followup_closure_id) && is_null($this->closed_datetime));
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
				case 'selByDueDatetimeAndFollowUpRecurringId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "due_datetime = <due_datetime> AND followup_recurring_id = <followup_recurring_id>");
					break;
				case 'selByFollowUpRecurringId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "followup_recurring_id = <followup_recurring_id>");
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