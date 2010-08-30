<?php
class Correspondence_Run
{
	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence;
	protected $_oDO;


	public function __construct($oCorrespondenceTemplate, $mDefinition, $bProcessNow = true)
	{
		$this->_oCorrespondenceTemplate = $oCorrespondenceTemplate;
		if (is_array($mDefinition))
		{
			if ($mDefinition['scheduled_datetime']== null)
			{
				$mDefinition['scheduled_datetime'] = Data_Source_Time::currentTimestamp();
			}

			$mDefinition['bPreprinted'] = $mDefinition['bPreprinted']?1:0;
			$this->_oDO = new Correspondence_Run_ORM($mDefinition);
			if ($bProcessNow)
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
		$this->_aCorrespondence = $aCorrespondence;
		$x = time() - $x;
		echo count($aCorrespondence)." results processed in $x seconds.<br>";
		$this->_oDO['processed_datetime'] = Data_Source_Time::currentTimestamp();
	}

	public function save()
	{
		$this->_oCorrespondenceTemplate->save();
		$this->oDO->correspondence_template_id = $this->_oCorrespondenceTemplate->id;
		$this->oDO->save();
		foreach ($this->_aCorrespondence as $oCorrespondence)
		{
			$oCorrespondence->correspondence_run_id = $this->id;
			$oCorresponcence->save();
		}

	}

	public function getCorrespondence()
	{
		if ($this->_aCorrespondence == null)
			$this->_aCorrespondence = Correspondence::getForRunId($this->id);
		return $this->_aCorrespondence;
	}

	public function getTemplateName()
	{
		return $this->_oCorrespondenceTemplate->name;
	}

	public function getTemplateId()
	{
		return $this->_oCorrespondenceTemplate->id;
	}


	public function setDeliveryDetails ($iFileExportId, $sDeliveredTimeStamp)
	{
		$this->file_export_id = $iFileExportId;
		$this->delivered_timestamp = $sDeliveredTimeStamp;
	}


	public static function get($iId)
	{
		//create a new object based on the id passed in
	}



	public static function getWaitingRuns($sScheduledDateTime = null)
	{
		if ($sScheduledDateTime == null)
			$sScheduledDateTime = Data_Source_Time::currentTimestamp();

		$aRuns = array();
		//retrieve from the database the set of Correspondence_Run ORM objects that must be run now.
		//retrieve the corresponding template ORMs, process these into template objects,then construct the Run objects.

		//for initial testing purposes create new objects instead of retrieving data
		$oSource = new Correspondence_Source_Csv();
		$oTemplate = Correspondence_Template::create('motorpass correspondence', 'blah blah', $oSource);
			$aDefinition = array ('scheduled_datetime'=> Data_Source_Time::currentTimestamp(), 'processed_datetime'=>Data_Source_Time::currentTimestamp());
		$aRuns[]= new Correspondence_Run($oTemplate, $aDefinition);



		//for each run: get the correspondence objects associated with it,by either simply retrieving them,or by processing the run first
		foreach ($aRuns as $oRun)
		{
			if ($oRun->processed_dateTime == null)
				$oRun->process();
		}

		return $aRuns;



	}

	public function __get($sField)
	{
		return $this->_oDO[$sField];
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO[$sField]=$mValue;
	}
}