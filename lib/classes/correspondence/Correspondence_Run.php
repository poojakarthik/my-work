<?php
class Correspondence_Run
{
	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence;
	protected $_oDO;


	public function __construct($oCorrespondenceTemplate, $mDefinition)
	{
		$this->_oCorrespondenceTemplate = $oCorrespondenceTemplate;
		if (is_array($mDefinition))
		{
			if ($mDefinition['schedule_datetime']== null)
			{
				$mDefinition['schedule_datetime'] = Data_Source_Time::currentTimestamp();
			}

			$this->_oDO = $mDefinition;
			if ($mDefinition['process_datetime']==null)
				$this->process();


		}
		else
		{
			$this->_oDO = $mDefinition;
			$this->_aCorrespondence = Correspondence::getForRunId($this->oDO->id);
		}
	}


	public function process()
	{
		$x = time();
		$aCorrespondence = $this->_oCorrespondenceTemplate->getData();
		foreach ($aCorrespondence as $oCorrespondence)
		{
			$oCorrespondence->_oCorrespondenceRun = $this;
		}
		$x = time() - $x;
		echo count($aCorrespondence)." results processed in $x seconds.<br>";
		$this->_oDO['process_datetime'] = Data_Source_Time::currentTimestamp();
	}

	public function save()
	{
		$this->_oCorrespondenceTemplate->save();
		$this->oDO->correspondence_template_id = $this->_oCorrespondenceTemplate->id;
		$this->oDO->save();

	}

	public function getCorrespondence()
	{
		return $this->_aCorrespondence;
	}

	public static function get($iId)
	{
		//create a new object based on the id passed in
	}

}