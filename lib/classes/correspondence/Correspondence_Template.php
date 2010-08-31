<?php

class Correspondence_Template
{

	protected $_oCorrespondenceSource;
	protected $_oDO;
	protected $_aRuns = array();
	protected $_aExtraColumns = array();
	public static $aNonSuppliedFields = array('created_employee_id', 'created_timestamp', 'system_name', 'status_id');


	public function __construct($mDefinition, $oSource = null, $aColumns = array())
	{
		if (is_numeric($mDefinition))
		{
			//implement code to retrieve data and instantiate the object

		}
		else if (is_array($mDefinition))
		{
			foreach (self::$aNonSuppliedFields as $sField)
			{
				$mDefinition[$sField] = null;
			}
			$this->_oDO = new Correspondence_Template_ORM($mDefinition);
			$this->_oCorrespondenceSource = $oSource;

			foreach($aColumns as $aColumn)
			{
				$this->_aExtraColumns[]= new Correspondence_Template_Column($aColumn, $this);
			}


		}
		else
		{
			$this->_oDO = $mDefinition;
			//implement this further
		}
	}

	public function save()
	{
		$this->_oCorrespondenceSource->save();
		$this->correspondence_source_id = $this->_oCorrespondenceSource->id;
		if ($this->id == null)
		{
			$this->created_employee_id = Flex::getUserId();
			$this->system_name = strtoupper($this->name);
			$this->status_id = 1;
		}

		$this->_oDO->save();
		foreach ($this->_aExtraColumns as $oColumn)
		{
			$oColumn->save();
		}

		foreach ($this->_aRuns as $oRun)
		{
			$oRun->save();
		}

	}

	public function  createRun($bPreprinted = false, $sScheduleDateTime = null, $sProcessDateTime = null, $sTarFileName = null)
	{

		$aDefinition = array ('scheduled_datetime'=> $sScheduleDateTime, 'processed_datetime'=>$sProcessDateTime, 'preprinted'=>$bPreprinted, 'tar_file_name'=>$sTarFileName);
		$oRun = new Correspondence_Run($this, $aDefinition);
		$this->_aRuns[]=$oRun;
		return $oRun;

	}

	public function getData($bPreprinted)
	{
		return $this->_oCorrespondenceSource->getData($bPreprinted,$this->getAdditionalColumnSet(Correspondence::getStandardColumnCount($bPreprinted)));
	}

	public function createFullColumnSet( $bPreprinted,$bIncludeNonSuppliedFields = false)
	{

		$aColumns = Correspondence::getStandardColums($bPreprinted,$bIncludeNonSuppliedFields);
		$iColumnCount = count($aColumns);
		$aAdditionalColumns = $this->getAdditionalColumnSet($iColumnCount);
		$aColumns = array_merge($aColumns, $aAdditionalColumns);

		return $aColumns;

	}



	public function getAdditionalColumnSet($iStandardColumnCount)
	{

		$aColumns = array();
		foreach ($this->_aExtraColumns as $oColumn)
		{
			$aColumns[$iStandardColumnCount -1 + $oColumn->column_index] = $oColumn->name;
		}
		return $aColumns;

	}








	public static function get($iId)
	{
		return new self (array('id'=>$iId));
	}

	public static function create($sName, $sDescription, $aColumns, $iCarrierId, $oSource)
	{
		$aDefinition = array('name'=>$sName, 'description'=>$sDescription, 'carrier_id'=>$iCarrierId);
		return new self ($aDefinition, $oSource, $aColumns);
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}



}



?>