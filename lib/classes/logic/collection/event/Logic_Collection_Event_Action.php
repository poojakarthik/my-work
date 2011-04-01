<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Action extends Logic_Collection_Event
{
	protected $oDO;
	
	public function __construct($mDefinition)
	{	   
		if ($mDefinition instanceof Logic_Collection_Event_Instance)
		{
		   $this->oCollectionEventInstance = $mDefinition;
		   $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
		   $this->oDO = Collection_Event_Action::getForCollectionEventId($this->oParentDO->id);
		}
		else if (is_numeric($mDefinition))
		{
			$this->oParentDO = Collection_Event::getForId($mDefinition);
			$this->oDO = Collection_Event_Action::getForCollectionEventId($this->oParentDO->id);
		}
		else
		{
		   throw new Exception ('Bad definition of Logic_Collection_Event_Action, possibly a configuration error');
		}
	}

	protected function _invoke($aParameters = null)
	{	
		// Normalise things
		$intAccountId		= $this->getAccount()->id;
		$strExtraDetails	= trim($aParameters['extra_details']);
		$strExtraDetails	= ($strExtraDetails == "")? NULL : $strExtraDetails;

		// Retrieve the Action Type
		$actionType = Action_Type::getForId($this->action_type_id);

		// Check that each $intAccountId, $intServiceId and $intContactId can be associated with actions of type $actionType, and NULLify them if they can't
		$arrAllowableActionAssociationTypes = $actionType->getAllowableActionAssociationTypes();

		if (!array_key_exists(ACTION_ASSOCIATION_TYPE_ACCOUNT, $arrAllowableActionAssociationTypes))
		{
		   throw new Logic_Collection_Exception("Incorrect Action Type: Account is not an allowable association type. Configuration Error");
		}

		$intEmployeeId = Flex::getUserId()!== NULL ? Flex::getUserId() : Employee::SYSTEM_EMPLOYEE_ID;

		$oAction = Action::createAction($actionType, $strExtraDetails, $intAccountId,null, null, $intEmployeeId, $intEmployeeId);
		
	}

	public static function complete($aEventInstances)
	{
		foreach ($aEventInstances as $oInstance)
		{
			$oInstance->complete();
		}
	}

	public function __get($sField)
	{
		switch ($sField)
		{
			case 'name':
			case 'collection_event_type_id':
				return $this->oParentDO->$sField;
			default:
				return $this->oDO->$sField;
		}
	}
}
?>
