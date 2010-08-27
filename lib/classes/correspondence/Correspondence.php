<?php

class Correspondence
{
	protected $_oDO;
	protected $_oCorrespondenceRun;


	public function __construct($aData)
	{
		$this->_oDO = $aData;
	}

	public function toArray()
	{
		return $this->_oDO;//change this to a real toArray when the time is right.
	}


	public function save()
	{

		if ($this->_oCorrespondenceRun == null)
			throw new Exception();

		$this->oCorrespondenceRun->save();
		$this->oDO->correspondence_run_id = $this->oCorrespondenceRun->id;
		$this->oDO->save();
	}

	public function __set($sField, $mValue)
	{
		switch ($sField)
		{
			case '_oCorrespondenceRun':
									$this->_oCorrespondenceRun = $mValue;
									break;


		}
	}


}


?>