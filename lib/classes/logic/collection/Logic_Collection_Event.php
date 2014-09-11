<?php
abstract class Logic_Collection_Event implements DataLogic 
{
	protected $oParentDO;
	protected $oEventTypeDO;
	protected $oImplementationDO;

	//data members that provide the context for event invocation
	protected $oCollectionEventInstance;
	protected $aInvocationParameters;


	abstract protected function _invoke($aParameters = null);

	final public function invoke($aParameters = null)
	{
		if ($this->oCollectionEventInstance === NULL)
				throw new Exception("Cannot invoke an event unless there is an actual Collection Event Instance (account_collection_event_history).");
	   
		$this->_invoke($aParameters);
		////Log::getLog()->log("Event being invoked: ".get_class($this));
	   
	}

	public function getAccount()
	{
		if ($this->oCollectionEventInstance === null)
				return null;
		return Logic_Account::getInstance($this->oCollectionEventInstance->account_id);
	}

	public function getScenario()
	{
		if ($this->oCollectionEventInstance === null)
				return null;
		if ($this->oCollectionEventInstance->collection_scenario_collection_event_id !== null)
		{
			$oScenarioEvent = Logic_Collection_Scenario_Event::getForId($this->oCollectionEventInstance->collection_scenario_collection_event_id);
			 $oScenario = Logic_Collection_Scenario::getForId($oScenarioEvent->collection_scenario_id);

		}
		else
		{
			 $oScenario = $this->getAccount()->getCurrentScenarioInstance()->getScenario();
		}

		 return $oScenario;
	   

	}

	/**
	 * hierarchy:
	 * 1 implementation level
	 * 2 type level
	 * 3 scenario event level - not included here!
	 * 4 collection event level
	 * @param <type> $bExcludeEventLevelDefinition - possibility to exclude the event level definition of the invocation, added because the scenario event level takes precedence
	 * @return <type>
	 */


	public function getInvocationId($bEnforcedInvocationOnly = false)
	{
		$oImplementation = $this->getImplementation();
		if ($oImplementation->enforced_collection_event_invocation_id !== null)
				return $oImplementation->enforced_collection_event_invocation_id; 
		if ($this->getEventType()->collection_event_invocation_id !== null)
				return $this->getEventType()->collection_event_invocation_id;
		return $bEnforcedInvocationOnly ? null : $this->oParentDO->collection_event_invocation_id;
	   
	}


	protected function getImplementation()
	{
		$oEventType = $this->getEventType();
		if ($this->oImplementationDO === null)
				$this->oImplementationDO = Collection_Event_Type_Implementation::getForId($oEventType->collection_event_type_implementation_id);
		return  $this->oImplementationDO;
	}

	protected function getEventType()
	{
		if ($this->oEventTypeDO === null)
				$this->oEventTypeDO = Collection_Event_Type::getForId($this->collection_event_type_id);
		return $this->oEventTypeDO;
	}

	public static function getForId($iEventId) 
	{
		return self::makeEvent($iEventId);
	}

	public static function getEventTypeForId ($iEventId)
	{
		$oEventORM	= Collection_Event::getForId($iEventId);
		return $oEventORM->collection_event_type_id;
	}

	public static function getImplementationForId($iEventId)
	{
		 $oEventORM	= Collection_Event::getForId($iEventId);
		$oTypeORM	= Dummy::getForId('collection_event_type', $oEventORM->collection_event_type_id);
		$oImplementationORM = Dummy::getForId('collection_event_type_implementation', $oTypeORM->collectionEvent_type_implementation_id);
		return $oImplementationORM->id;
	}

	public static function getClassNameForId($iEventId)
	{
		$oEventORM	= Collection_Event::getForId($iEventId);
		$oTypeORM	= Collection_Event_Type::getForId( $oEventORM->collection_event_type_id);
		$oImplementationORM = Collection_Event_Type_Implementation::getForId( $oTypeORM->collection_event_type_implementation_id);
	 	return $oImplementationORM->class_name;
	}

	public static function getForEventInstance($oEventInstance) {
		$sClassName	= self::getClassNameForId($oEventInstance->collection_event_id);
		return new $sClassName($oEventInstance);
	}

	public static function makeEvent($iEventId)
	{		
		$oEventORM	= Collection_Event::getForId($iEventId);
		$sClassName	= self::getClassNameForId($iEventId);
		return new $sClassName($iEventId);
	}
	
	public function save()
	{
		// Not sure if this is needed
	}

	public function toArray()
	{
		return $this->oParentDO->toArray();
	}

	public function __get($sField)
	{
		return $this->oParentDO->$sField;
	}
	
	public function __set($sField, $mValue)
	{
		// Not sure if this is needed
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$sFrom	= "	collection_event ce
					JOIN		collection_event_type cet ON (cet.id = ce.collection_event_type_id)
					LEFT JOIN	collection_event_invocation cei ON (cei.id = ce.collection_event_invocation_id)
					JOIN		status s ON (s.id = ce.status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(ce.id) AS event_count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "ce.*, 
						cet.name AS collection_event_type_name, 
						cei.name AS collection_event_invocation_name, 
						s.name AS status_name";	
			$sOrderBy =	Statement::generateOrderBy(
							array(
								'id' 			=> 'ce.id', 
								'name' 			=> 'ce.name', 
								'description' 	=> 'ce.description'
							), 
							get_object_vars($oSort)
						);
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= 	Statement::generateWhere(
							array(
								'collection_event_invocation_id'	=> 'ce.collection_event_invocation_id',
								'status_id' 						=> 'ce.status_id'
							), 
							get_object_vars($oFilter)
						);
		$oSelect	=	new StatementSelect(
							$sFrom, 
							$sSelect, 
							$aWhere['sClause'], 
							$sOrderBy, 
							$sLimit
						);
		
		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Events. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['event_count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}
}
?>


 