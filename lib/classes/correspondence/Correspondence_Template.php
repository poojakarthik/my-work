<?php

class Correspondence_Template
{

	protected $_oCorrespondenceSource;
	protected $_oDO;
	protected $_aRuns = array();
	protected $_aExtraColumns = array();
	protected $_aCorrespondenceFieldsNotSupplied = array('correspondence_run_id');

	public function __construct($aDefinition, $oSource = null, $aColumns = null)
	{
		if (array_key_exists('id', $aDefinition))
		{
			//implement code to retrieve data and instantiate the object

		}
		else
		{
			$this->_oDO = $aDefinition;
			$this->_oCorrespondenceSource = $oSource;

			$this->_aColumns = $aColumns;
		}
	}

	public function save()
	{
		$this->_oCorrespondenceSource->save();
		$this->oDO->correspondence_source_id = $this->_oCorrespondenceSource->id;
		$this->_oDO->save();
	}

	public function  createRun($bPreprinted = false, $sScheduleDateTime = null, $sProcessDateTime = null, $sTarFileName = null)
	{

		$aDefinition = array ('scheduled_datetime'=> $sScheduleDateTime, 'processed_datetime'=>$sProcessDateTime, $bPreprinted, $sTarFileName);
		$oRun = new Correspondence_Run($this, $aDefinition);
		$this->_aRuns[]=$oRun;
		return $oRun;

	}

	public function getData()
	{
		return $this->_oCorrespondenceSource->getData($this->createFullColumnSet());
	}

	public function createFullColumnSet($bIncludeNonSuppliedFields = false)
	{
		$aColumns = Correspondence_ORM::getFieldNames();
		if (!$bIncludeNonSuppliedFields)
		{
			foreach ($this->_aCorrespondenceFieldsNotSupplied as $sField)
			{
				$iIndex = array_search($sField,$aColumns);
				unset($aColumns[$iIndex]);
			}
		}


		if ($this->_aExtraColumns)
		{


			foreach ($this->_aExtraColumns as $sColumn)
			{
				$aColumns[$aColumn['column_index']] = $aColumns['name'];
			}
			return $aColumns;
		}

		return $aColumns;

		}




	public static function get($iId)
	{
		return new self (array('id'=>$iId));
	}

	public static function create($sName, $sDescription, $aColumns, $iCarrierId, $oSource)
	{
		$aDefinition = array('name'=>$sName, 'description'=>$sDescription, $iCarrierId);
		return new self ($aDefinition, $oSource);
	}

	public function __get($sField)
	{
		return $this->_oDO[$sField];
	}



}



?>