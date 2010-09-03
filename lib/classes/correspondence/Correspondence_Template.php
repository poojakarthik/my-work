<?php

class Correspondence_Template
{

	protected static $_aCorrespondence_Templates = array();

	protected $_oCorrespondenceSource;
	protected $_oDO;
	protected $_aRuns = array();
	protected $_aExtraColumns = array();
	public static $aNonSuppliedFields = array('created_employee_id', 'created_timestamp', 'system_name', 'status_id');
	protected $_oCarrierModule;

	private function __construct($mDefinition, $oSource = null, $aColumns = array())
	{
		$this->_oCorrespondenceSource = $oSource;
		if (is_numeric($mDefinition))
		{
			//implement code to retrieve data and instantiate the object
			$this->_oDO = Correspondence_Template_ORM::getForId($mDefinition);
			$this->_aExtraColumns = Correspondence_Template_Column::getForTemplate($this);
			//todo: instantiate the run and extra columns members

		}
		else if (is_array($mDefinition))
		{
			foreach (self::$aNonSuppliedFields as $sField)
			{
				$mDefinition[$sField] = null;
			}
			$this->_oDO = new Correspondence_Template_ORM($mDefinition);


			foreach($aColumns as $aColumn)
			{
				$this->_aExtraColumns[]= new Correspondence_Template_Column($aColumn, $this);
			}


		}
		else
		{
			$this->_oDO = $mDefinition;
			//retrieve all columns and runs from the database that belong to this template.

		}
	}

	public function getCarrierModule()
	{
		if ($this->_oCarrierModule !=null)
			return $this->_oCarrierModule;

		$aCarrierModules = Carrier_Module::getForCarrierModuleType(MODULE_TYPE_CORRESPONDENCE_EXPORT);
		foreach($aCarrierModules as $oCarrierModule)
		{
			if ($oCarrierModule->Carrier = $this->carrier_id)
			{
				$sClassName = $oCarrierModule->Module;
				$this->_oCarrierModule = new $sClassName($oCarrierModule);
				return $this->_oCarrierModule;
			}
		}
		return false;

	}

	public function save()
	{
				// Start a new database transaction
				$oDataAccess	= DataAccess::getDataAccess();

				if (!$oDataAccess->TransactionStart())
				{

					return 	array(
								"Success"	=> false,
								"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
							);
				}

				try
				{

					$this->_save();

					// Everything looks OK -- Commit!
					$oDataAccess->TransactionCommit();
					return $this->id;

			}

			catch (Exception $e)
			{
				// Exception caught, rollback db transaction
				$oDataAccess->TransactionRollback();

				return 	array(
							"Success"	=> false,
							"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database'
						);
			}

	}


	public function _save()
	{
		$this->_oCorrespondenceSource->save();
		$this->correspondence_source_id = $this->_oCorrespondenceSource->id;
		if ($this->id == null)
		{
			$this->created_employee_id = Flex::getUserId();
			//$this->system_name = strtoupper($this->name);
			$this->status_id = 1;
		}

		$this->_oDO->save();
		foreach ($this->_aExtraColumns as $oColumn)
		{
			$oColumn->save();
		}

		foreach ($this->_aRuns as $oRun)
		{
			$oRun->_save();
		}

	}

	public function  createRun($bPreprinted = false, $sScheduleDateTime = null, $sProcessDateTime = null, $sTarFileName = null, $bProcessNow = true)
	{

		$aDefinition = array ('scheduled_datetime'=> $sScheduleDateTime, 'preprinted'=>$bPreprinted, 'tar_file_name'=>$sTarFileName,$bProcessNow);
		$oRun = new Correspondence_Run($this, $aDefinition);
		$this->_aRuns[]=$oRun;
		return $oRun;

	}

	public function getColumnIdForName($sColumnName)
	{
		foreach ($this->_aExtraColumns as $oColumn)
		{
			if ($oColumn->name == $sColumnName)
				return $oColumn->id;
		}

		return false;
	}

	public function getData($bPreprinted)
	{
		return $this->_oCorrespondenceSource->getData($bPreprinted,$this->getAdditionalColumnSet(Correspondence::getStandardColumnCount($bPreprinted)));
	}

	public function createFullColumnSet( $bPreprinted,$bIncludeNonSuppliedFields = false)
	{

		$aColumns = Correspondence::getStandardColumns($bPreprinted,$bIncludeNonSuppliedFields);
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



	public static function getForSystemName($sSystemName, $aData)
	{
		$oSource = new Correspondence_Source_System($aData);
		$oDO = Correspondence_Template_ORM::getForSystemName($sSystemName);
		return self::createFromORM($oDO, $oSource);
	}

	public static function createFromORM($oORM, $oSource)
	{
		return new self ($oORM, $oSource);
	}


	public static function getForId($iId)
	{

		foreach(self::$_aCorrespondence_Templates as $iTemplateId => $oTemplate)
		{
			if ( $iTemplateId == $iId )
				return $oTemplate;

		}
		return new self ($iId);
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

public static function getInstance($iId = null, $sCode= null, $sName= null, $sDescription= null, $aColumns = null, $iCarrierId = null, $oSource = null)
{
		foreach(self::$_aCorrespondence_Templates as $iTemplateId => $oTemplate)
		{
			if ( $iTemplateId = $iId || $oTemplate->template_code = $sCode)
				return $oTemplate;

		}
		$aDefinition = array('id' => $iId, 'template_code' => $sCode, 'name'=>$sName, 'description'=>$sDescription, 'carrier_id'=>$iCarrierId);
		return new self ($aDefinition, $oSource, $aColumns);
}

}



?>