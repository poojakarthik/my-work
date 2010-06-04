<?php
/**
 * FollowUp_Recurring
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp_Recurring
 */
class FollowUp_Recurring extends ORM_Cached
{
	protected 			$_strTableName			= "followup_recurring";
	protected static	$_strStaticTableName	= "followup_recurring";
	const				ITERATION_LIMIT			= 12;
	
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

	public static function searchFor($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null)
	{
		$sFromClause	= 'followup_recurring';
		$sSelectClause	= '*';
		$sWhereClause	= '';
		$sOrderByClause	= '';
		$sLimitClause	= '';
		
		// WHERE clause
		$aWhereInfo		= StatementSelect::generateWhere(null, $aFilter);
				
		// ORDER BY clause
		$sOrderByClause	= Statement::generateOrderBy($aSort);
				
		// LIMIT clause
		$sOrderByClause	= Statement::generateOrderBy($iLimit, $iOffset);
				
		// Get records
		$oSelect	= new StatementSelect($sFromClause, $sSelectClause, $aWhereInfo['sClause'], $sOrderByClause, $sLimitClause);
		if ($oSelect->Execute($aWhereInfo['aValues']) === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oSelect->Error());
		}
		
		// Create FollowUp_Recurring objects for each
		$aRecurringFollowUps	= array();
		while ($aRow = $oSelect->Fetch())
		{
			$aRecurringFollowUps[$aRow['id']]	= new self($aRow);
		}
		
		return $aRecurringFollowUps;
	}

	public function getDetails()
	{
		$aDetails	= array();
		switch ($this->followup_type_id)
		{
			case FOLLOWUP_TYPE_NOTE:
				$oFollowUpNote	= FollowUp_Recurring_Note::getForFollowUpRecurringId($this->id);
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
				$oFollowUpAction	= FollowUp_Recurring_Action::getForFollowUpRecurringId($this->id);
				
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
				$oFollowUpTicketCorrespondence	= FollowUp_Recurring_Ticketing_Correspondence::getForFollowUpRecurringId($this->id);
				
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

	public function getSummary()
	{
		$sSummary	= '';
		switch ($this->followup_type_id)
		{
			case FOLLOWUP_TYPE_NOTE:
				$oFollowUpNote	= FollowUp_Recurring_Note::getForFollowUpRecurringId($this->id);
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
				$oFollowUpAction	= FollowUp_Recurring_Action::getForFollowUpRecurringId($this->id);
				
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
				$oFollowUpTicketCorrespondence	= FollowUp_Recurring_Ticketing_Correspondence::getForFollowUpRecurringId($this->id);
				
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
		
		// Limit to 30 characters
		if (strlen($sSummary) > 30)
		{
			return substr($sSummary, 0, 30).'...';
		}
		
		return $sSummary;
	}

	public function getProjectedDueDate($iIteration=0)
	{
		$iStartDate		= strtotime($this->start_datetime);
		$iProjectedDate	= null;
		$iMultiplier	= (($iIteration + 1) * $this->recurrence_multiplier);
		
		switch ($this->followup_recurrence_period_id)
		{
			case FOLLOWUP_RECURRENCE_PERIOD_WEEK:
				$iProjectedDate	= strtotime('+'.$iMultiplier.' week', $iStartDate);
				break;
			case FOLLOWUP_RECURRENCE_PERIOD_MONTH:
				$iProjectedDate	= strtotime('+'.$iMultiplier.' month', $iStartDate);
				break;
		}
		
		return $iProjectedDate;
	}
	
	public function save()
	{
		// Update modified fields
		$this->modified_datetime	= date('Y-m-d H:i:s');
		$this->modified_employee_id	= Flex::getUserId();
		
		parent::save();
		
		// Create followup_recurring_history record
		$oFollowUpRecurringHistory							= new FollowUp_Recurring_History();
		$oFollowUpRecurringHistory->followup_recurring_id	= $this->id;
		$oFollowUpRecurringHistory->assigned_employee_id	= $this->assigned_employee_id;
		$oFollowUpRecurringHistory->end_datetime			= $this->end_datetime;
		$oFollowUpRecurringHistory->modified_datetime		= $this->modified_datetime;
		$oFollowUpRecurringHistory->modified_employee_id	= $this->modified_employee_id;
		$oFollowUpRecurringHistory->save();
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