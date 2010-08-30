<?php

class Correspondence
{
	protected $_oDO;
	protected $_oCorrespondenceRun;


	public function __construct($aData)
	{
		$this->_oDO = new Correspondence_ORM($aData);
	}

	public function toArray()
	{
		//return an associative array that can be used for csv file genereation
		//for this, retrieve the column list from the template object, through the run object

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