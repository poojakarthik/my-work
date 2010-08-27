<?php

class Correspondence_Template
{

	protected $_oCorrespondenceSource;
	protected $_oDO;
	protected $_aRuns;

	public function __construct($aDefinition, $oSource = null)
	{
		if (array_key_exists('id', $aDefinition))
		{
			//implement code to retrieve data and instantiate the object

		}
		else
		{
			$this->_oDO = $aDefinition;
			$this->_oCorrespondenceSource = $oSource;
		}
	}

	public function save()
	{
		$this->_oCorrespondenceSource->save();
		$this->oDO->correspondence_source_id = $this->_oCorrespondenceSource->id;
		$this->_oDO->save();
	}

	public function  createRun($sScheduleDateTime = null, $sProcessDateTime = null)
	{

		$aDefinition = array ('scheduled_datetime'=> $sScheduleDateTime, 'processed_datetime'=>$sProcessDateTime);
		$this->_aRuns[]= new Correspondence_Run($this, $aDefinition);

	}

	public function getData()
	{
		return $this->_oCorrespondenceSource->getData();
	}




	public static function get($iId)
	{
		return new self (array('id'=>$iId));
	}

	public static function create($sName, $sDescription, $oSource)
	{
		$aDefinition = array('name'=>$sName, 'description'=>$sDescription);
		return new self ($aDefinition, $oSource);
	}

	public function __get($sField)
	{
		return $this->_oDO[$sField];
	}



}



?>