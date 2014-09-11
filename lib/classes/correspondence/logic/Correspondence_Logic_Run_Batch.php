<?php
class Correspondence_Logic_Run_Batch
{
	protected $_oDO;

	public function __construct($mDefinition, $aColumns = array())
	{
		if (is_numeric($mDefinition))
		{
			$this->_oDO	= Correspondence_Run_Batch::getForId($mDefinition);
		}
		else
		{
			$this->_oDO	= $mDefinition;
		}
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}
	
	public function getDispatchRecords()
	{
		$oQuery	= new Query();
		$sQuery	= "	SELECT	crd.*
					FROM	correspondence_run_batch crb
					JOIN	correspondence_run_dispatch crd ON (crd.correspondence_run_batch_id = crb.id)
					WHERE	crb.id = {$this->id}";
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get dispatch details for batch {$this->id}. ".$oQuery->Error());
		}
		
		$aRecords	= array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$oORM					= new Correspondence_Run_Dispatch($aRow);
			$aRecords[$oORM->id]	= new Correspondence_Logic_Run_Dispatch($oORM);
		}
		return $aRecords;
	}
}
?>