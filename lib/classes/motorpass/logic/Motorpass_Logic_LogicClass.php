<?php
abstract class Motorpass_Logic_LogicClass
{

	protected $aUneditable = array();
	protected $aErrors = array();
	protected $oDO;
	protected $sDOClassName;
	protected $bUnsavedChanges;


	public function __construct($mDetails, $sDOClassName)
	{

		$this->sDOClassName = $sDOClassName;

		if ($mDetails && get_class($mDetails)=='stdClass')
		{
			foreach ($this->aUneditable as $sField)
			{
				$mDetails->$sField = null;
			}

			$this->oDO = new $this->sDOClassName((array)$mDetails);
		}
		else if ($mDetails && get_class($mDetails)==$this->sDOClassName)
		{
			$this->oDO = $mDetails;
		}
		else
		{
			throw new Exception_InvalidJSONObject("incorrect data passed into constructor for class: ".get_class($this).".");
		}

		$this->mergeWithStoredDataRecord();
		$this->setUnsavedChangesFlag();

		return $this->id;
	}

	public function setUnsavedChangesFlag()
	{
		$aChangedFields = array();
		$bChanged = false;
		if ($this->oDO->id!=null)
		{
			$dBaseRecord = $this->oDO->getForId($this->oDO->id);
			foreach ($dBaseRecord->toArray() as $key=>$value)
			{
				if ($this->{$key} != $value)
				{
					$bChanged = true;
					$aChangedFields[$key]=$value;
				}
			}
		}
		else //this is a record without id, so either a new record that must be saved, or an empty object that should not be saved
		{
			foreach ( $this->oDO->toArray() as $key=>$value)
			{
				if ($value !=null)
				{
					$bChanged = true;
					$aChangedFields[$key]=$value;
				}
			}

		}
		$this->bUnsavedChanges = $bChanged;
		return $aChangedFields;
	}


	public function validate()
	{
		$props = array_keys($this->oDO->toArray());
		$vals = $this->oDO->toArray();
		$errors = array();
		for ($i = 0, $l = count($props); $i < $l; $i++)
		{
			if (!$this->oDO->isValidValue($props[$i], $this->oDO->{$props[$i]}))
			{
				$this->aErrors[] = "Invalid value specified for '" . $props[$i] . "': '".$this->_aProperties[$props[$i]]."'";// . $this->properties[$props[$i]];
			}
		}
		return $this->aErrors;
	}

	public function toStdClass()
	{
		return $this->oDO->toStdClass();
	}

	public function setUnsaved()
	{
		$this->oDO->setUnsavedChangesFlag();
	}


	public function  __get($sField)
	{
		return $this->oDO->{$sField};
	}

	public function __call($sFunction, $sArgs = null)
	{
		if ($sFunction == 'toArray')
			return $this->oDO->toArray();

	}

	public function __set($sField, $mValue)
	{
		$this->oDO->{$sField} = $mValue;
	}

	public function mergeWithStoredDataRecord()
	{
		if (isset($this->aUneditable)&& $this->oDO->id!=null)
		{
			$oDO = $this->oDO->getForId($this->oDO->id);
			foreach ($this->aUneditable as $sField)
			{

				$this->oDO->$sField = $oDO->$sField;
			}
		}
	}



	abstract public function _save();

}