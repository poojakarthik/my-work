<?php


/**
 * Description of Collection_Logic_Event_Severity
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Severity extends Logic_Collection_Event
{
	protected $oSeverity;
	protected $oDO;
	public function __construct($mDefinition)
	{
		if ($mDefinition instanceof Logic_Collection_Event_Instance)
		{
			$this->oCollectionEventInstance = $mDefinition;
			$this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
			$this->oDO = Collection_Event_Severity::getForCollectionEventId($this->oParentDO->id);
		}
		else if (is_numeric($mDefinition))
		{
			$this->oParentDO = Collection_Event::getForId($mDefinition);
			$this->oDO = Collection_Event_Severity::getForCollectionEventId($this->oParentDO->id);
		}
		else
		{
		   throw new Exception('bad parameter passed into Collection_Logic_Event_Report constructor');
		}
	}

	public function getSeverity()
	{
		if ($this->oSeverity === null)
			$this->oSeverity = Collection_Severity::getForId($this->collection_severity_id);
		return $this->oSeverity;
	}

	protected function _invoke($aParameters = null)
	{
		
		$oAccount = $this->getAccount();
		$oAccount->collection_severity_id = $this->collection_severity_id;
		//file_put_contents('/home/rmctainsh/log.txt', print_r($oAccount->toArray(), true));
		$oAccount->save();
		
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

	public static function complete($aEventInstances)
	{
		foreach ($aEventInstances as $oInstance)
		{
			$oInstance->complete();
		}
	}
}
?>
