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

	public static function searchFor($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null, $bCountOnly=false, $bIncludeExtraFields=false)
	{
		$sFromClause	= '	followup_recurring fr
							LEFT JOIN followup f ON fr.id = f.followup_recurring_id';
		$sSelectClause	= 'fr.*, MAX(f.closed_datetime) AS last_actioned';
		$sWhereClause	= '';
		$sOrderByClause	= '';
		$sLimitClause	= '';
		$sHavingClause	= '';
		
		$aAliases		= 	array(
								'assigned_employee_id'	=> 'fr.assigned_employee_id',
								'created_datetime'		=> 'fr.created_datetime',
								'start_datetime'		=> 'fr.start_datetime',
								'end_datetime'			=> 'fr.end_datetime',
								'followup_type_id'		=> 'fr.followup_type_id',
								'followup_category_id'	=> 'fr.followup_category_id',
								'followup_category_id'	=> 'fr.followup_category_id',
								'modified_datetime'		=> 'fr.modified_datetime',
								'last_actioned'			=> 'MAX(f.closed_datetime)'
							);
		
		if (isset($aFilter['last_actioned']))
		{
			if ($aFilter['last_actioned']->mFrom || $aFilter['last_actioned']->mTo)
			{
				// Last Actioned date is being filtered, add it as a special having constraint
				// Uses the same set of aliases as where, although on 'last_actioned' is really used
				$aHavingInfo	= 	StatementSelect::generateWhere(
										$aAliases, 
										array('last_actioned' => $aFilter['last_actioned'])
									);
				$sHavingClause	= ' HAVING '.$aHavingInfo['sClause'];
			}
			
			unset($aFilter['last_actioned']);
		}
		
		// WHERE clause
		$aWhereInfo				= StatementSelect::generateWhere($aAliases, $aFilter);
		if ($aWhereInfo['sClause'] == '')
		{
			$aWhereInfo['sClause']	.= ' 1';
		}
		$aWhereInfo['sClause']	.= ' GROUP BY fr.id';
		 
		if ($sHavingClause != '')
		{
			// Add the having clause to the where clause
			$aWhereInfo['sClause']	.=	$sHavingClause;
			
			// Add the having values into the where values array
			foreach ($aHavingInfo['aValues'] as $sAlias => $mValue)
			{
				$aWhereInfo['aValues'][$sAlias]	= $mValue;
			}
		}
		
		if ($bCountOnly)
		{
			$sSelectClause	= 'COUNT(DISTINCT fr.id) AS count';
		}
		else
		{
			// ORDER BY clause
			$sOrderByClause	= Statement::generateOrderBy($aAliases, $aSort);
			
			// LIMIT clause
			$sLimitClause	= Statement::generateLimit($iLimit, $iOffset);
		}
		
		// Get records
		$oSelect	= 	new StatementSelect(
							$sFromClause, 
							$sSelectClause, 
							$aWhereInfo['sClause'], 
							$sOrderByClause, 
							$sLimitClause
						);
	
		$iCount	= $oSelect->Execute($aWhereInfo['aValues']);
		if ($iCount === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			return $iCount;
		}
		else
		{
			// Create FollowUp_Recurring objects for each
			$aRecurringFollowUps	= array();
			while ($aRow = $oSelect->Fetch())
			{
				$oFollowUpRecurring	= new self($aRow);
				
				if ($bIncludeExtraFields)
				{
					// Convert the object to Std class and include the 'last_actioned' field
					$oStdClass							= $oFollowUpRecurring->toStdClass();
					$oStdClass->last_actioned			= $aRow['last_actioned'];
					$aRecurringFollowUps[$aRow['id']]	= $oStdClass;
				}
				else
				{
					$aRecurringFollowUps[$aRow['id']]	= $oFollowUpRecurring;
				}
			}
			
			return $aRecurringFollowUps;
		}
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
		
		if ($bRemoveWhitespace)
		{
			// Remove whitespace
			$sSummary	= preg_replace('/\s/', ' ', $sSummary);
		}
		
		// Limit to $iCharacterLimit characters (default 30)
		if (!is_null($iCharacterLimit) && (strlen($sSummary) > $iCharacterLimit))
		{
			return substr($sSummary, 0, $iCharacterLimit).'...';
		}
		
		return $sSummary;
	}

	public function getProjectedDueDateSeconds($iIteration=0)
	{
		$iStartDate		= strtotime($this->start_datetime);
		$iProjectedDate	= null;
		$iMultiplier	= ($iIteration * $this->recurrence_multiplier);
		
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
	
	public function isAssignedTo($iEmployeeId)
	{
		return $iEmployeeId == $this->assigned_employee_id;
	}
	
	public function assignTo($iEmployeeId, $iReassignReasonId)
	{
		// Do reassign
		$this->assigned_employee_id	= $iEmployeeId;
		$this->save(null, $iReassignReasonId);
		
		// Send email (need to set include path for the zend mail class require to work)
		set_include_path(get_include_path().PATH_SEPARATOR.realpath(dirname(__FILE__).'/../../'));
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
			$sUrl			= $oCustomerGroup->flexUrl."/admin/reflex.php/FollowUp/ManageRecurring/#{$this->id}";
			$sType			= Constant_Group::getConstantGroup('followup_type')->getConstantName($this->followup_type_id);
			$sCategory		= FollowUp_Category::getForId($this->followup_category_id)->name;
			$aDueNext		= $this->getNextDueDateInformation();
			$sDueOn			= date('l jS M Y g:i A', strtotime($aDueNext['sDueDateTime']));
			$sEmailContent	=	"<div style='font-family: Calibri,sans-serif;'>\n" .
								"	You have been assigned a Recurring Follow-Up (of type '{$sType}') by {$sAssignedBy} with a category of '{$sCategory}' that is due next on <span style='font-weight: bold;'>{$sDueOn}</span>.<br/><br/>\n" .
								"	<a href='{$sUrl}'>Click here</a> to go to your Recurring Follow-Up Management page.\n" .
								"</div>";
			
			$oEmail	= new Zend_Mail();
			$oEmail->setBodyHtml($sEmailContent);
			$oEmail->setFrom("followups@ybs.net.au");
			$oEmail->addTo($sUserEmail, $oEmployee->getName());
			$oEmail->setSubject("You have been assigned a recurring follow-up");
			$oEmail->send();
		}
	}
	
	public function save($iModifyReasonId=null, $iReassignReasonId=null)
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
		
		if (!is_null($iModifyReasonId))
		{
			// Create a modification record for the history record and the given modification reason id
			$oModify					= new FollowUp_Recurring_History_Modify_Reason();
			$oModify->history_id		= $oFollowUpRecurringHistory->id;
			$oModify->modify_reason_id	= $iModifyReasonId;
			$oModify->save();
		}
		else if (!is_null($iReassignReasonId))
		{
			// Create a modification record for the history record and the given reassign reason id
			$oModify						= new FollowUp_Recurring_History_Reassign_Reason();
			$oModify->history_id			= $oFollowUpRecurringHistory->id;
			$oModify->reassign_reason_id	= $iReassignReasonId;
			$oModify->save();
		}
		
	}
	
	public function isClosed()
	{
		return (strtotime($this->end_datetime) <= time());
	}
	
	public function getOccurrenceDetails($bClosedOccurencesOnly=false, $bPastOnly=false)
	{
		// For each possible iteration fetch it's due_datetime, closure_type_id & ...
		$aDetails		= array('aOccurrences' => array(), 'bHasMore' => false);
		$iProjectedDate	= strtotime($this->start_datetime);
		$iEndDate		= strtotime($this->end_datetime);
		$iNow			= time();
		$iAfterNow		= 0;
		$i				= 0;
		
		while(($iProjectedDate <= $iEndDate) && 
			  (!$bPastOnly || ($iProjectedDate <= $iNow)) && 
			  ($bPastOnly || ($iAfterNow <= FollowUp_Recurring::ITERATION_LIMIT)))
		{
			$sDueDateTime	= date('Y-m-d H:i:s', $iProjectedDate);
			$aOccurence		= array();
			$oFollowUp		= FollowUp::getForDateAndRecurringId($sDueDateTime, $this->id);
			if ($oFollowUp || !$bClosedOccurencesOnly)
			{
				$aOccurence['sDueDatetime']			= ($oFollowUp ? $oFollowUp->due_datetime : $sDueDateTime);
				$aOccurence['sClosedDatetime']		= ($oFollowUp ? $oFollowUp->closed_datetime : null);
				$aOccurence['oFollowUpClosure']		= ($oFollowUp ? FollowUp_Closure::getForId($oFollowUp->followup_closure_id)->toStdClass() : null);
				$aOccurence['iAssignedEmployeeId']	= ($oFollowUp ? $oFollowUp->assigned_employee_id : null);
				$aOccurence['sAssignedEmployee']	= ($oFollowUp ? Employee::getForId($oFollowUp->assigned_employee_id)->getName() : null);
				$aDetails['aOccurrences'][]			= $aOccurence;
			}
			
			// Calculates the projected followup date given an iteration
			$i++;
			$iProjectedDate	= $this->getProjectedDueDateSeconds($i);
			if ($iProjectedDate > $iNow)
			{
				$iAfterNow++;
			}
		}
		
		if ($iAfterNow > FollowUp_Recurring::ITERATION_LIMIT)
		{
			// The iteration was stopped, but not by an end date, there must be more interations to come
			$aDetails['bHasMore']	= true;
		}
		
		return $aDetails;
	}
	
	public function getNextDueDateInformation()
	{
		$sDateFormat		= "Y-m-d H:i:s";
		$iProjectedDate		= strtotime($this->start_datetime);
		$iEndDate			= strtotime($this->end_datetime);
		$sDueDateTime		= date($sDateFormat, $iProjectedDate);
		$iNow				= time();
		$i					= 0;
		while((($iProjectedDate <= $iNow) && ($iProjectedDate <= $iEndDate)) || FollowUp::getForDateAndRecurringId($sDueDateTime, $this->id))
		{
			$i++;
			$iProjectedDate	= $this->getProjectedDueDateSeconds($i);
			$sDueDateTime	= date($sDateFormat, $iProjectedDate);
		}
		
		// Determine whether there are more to come, if the projected date is after the end date
		$bNoMore	= false;
		if ($iProjectedDate > $iEndDate)
		{
			$bNoMore	= true;
		}
		
		return array("sDueDateTime" => $sDueDateTime, "iIteration" => $i, "bNoMore" => $bNoMore);
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