<?php

class Correspondence_Logic_Template
{
	protected $_oCorrespondenceSource;
	protected $_oDO;
	protected $_aRuns = array();
	protected $_aExtraColumns = array();
	public static $aNonSuppliedFields = array('created_employee_id', 'created_timestamp', 'status_id', 'correspondence_source_id');
	protected $_oCarrierModule;

	private function __construct($mDefinition, $aColumns = array())
	{
		is_numeric($mDefinition)?$this->_oDO = Correspondence_Template::getForId($mDefinition):$this->_oDO = $mDefinition;

		$this->_aExtraColumns = Correspondence_Logic_Template_Column::getForTemplate($this);

		$this->_oCorrespondenceSource = Correspondence_Logic_Source::factory($this);

		$this->_oDO->setSaved();
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
		if ($this->correspondence_source_id == null)
			$this->correspondence_source_id = $this->_oCorrespondenceSource->id;
		if ($this->id == null)
		{
			$this->created_employee_id = Flex::getUserId();
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

	public function  createRun($bPreprinted = false, $mData = null, $sScheduleDateTime = null, $bProcessNow = true)
	{
		$aDefinition = array ('scheduled_datetime'=> $sScheduleDateTime, 'preprinted'=>$bPreprinted);
		$oRun = new Correspondence_Logic_Run($aDefinition, $this);
		$bProcessNow?$oRun->process($mData):null;
		$oRun->save();
		//$oRun->sendRunCreatedEmail();
		return $oRun;
	}

	public function getData($bPreprinted)
	{
		return $this->_oCorrespondenceSource->getData($bPreprinted,$this->getAdditionalColumnSet(Correspondence_Logic::getStandardColumnCount($bPreprinted)));
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


	public function createFullColumnSet( $bPreprinted,$bIncludeNonSuppliedFields = false)
	{
		$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted,$bIncludeNonSuppliedFields);
		$iColumnCount = count($aColumns);
		$aAdditionalColumns = $this->getAdditionalColumnSet($iColumnCount);
		$aColumns = array_merge($aColumns, $aAdditionalColumns);
		return $aColumns;
	}

	public function getAdditionalColumnSet($iStandardColumnCount = 0)
	{

		$aColumns = array();
		foreach ($this->_aExtraColumns as $oColumn)
		{
			$aColumns[$iStandardColumnCount -1 + $oColumn->column_index] = $oColumn->name;
		}
		return $aColumns;

	}

	public function getSourceType()
	{
		return $this->_oCorrespondenceSource == null?Correspondence_Source::getForId($this->correspondence_source_id)->correspondence_source_type_id:$this->_oCorrespondenceSource->correspondence_source_type_id;
	}

	public function getSource()
	{
		return $this->_oCorrespondenceSource;
	}

	public function importSource()
	{
		return $this->_oCorrespondenceSource->import();
	}


	// getForInvoiceRunType: Uses the Invoice_Run_Type_Correspondence_Template linking class/table to retrieve the template for the given invoice run type
	public static function getForInvoiceRunType($iInvoiceRunTypeId)
	{
		$oDO 		= Invoice_Run_Type_Correspondence_Template::getTemplateForInvoiceRunType($iInvoiceRunTypeId);
		//$oSource	= new Correspondence_Logic_Source_System($oDO->id,$aData);
		return new self($oDO);
	}

	// getForInvoiceRunType: Uses the Automatic_Invoice_Action_Correspondence_Template linking class/table to retrieve the template for the given automatic invoice action
	public static function getForAutomaticInvoiceAction($iAutomaticInvoiceActionId)
	{
		$oDO 		= Automatic_Invoice_Action_Correspondence_Template::getTemplateForAutomaticInvoiceAction($iAutomaticInvoiceActionId);
		//$oSource	= new Correspondence_Logic_Source_System($oDO->id, $aData);
		return new self($oDO);
	}

	public static function getForId($iId)
	{
		return new self ($iId);
	}

	/*public static function create($sTemplateCode, $sName, $sDescription, $iCarrierId, $oSource, $aColumns)
	{
		$aDefinition = array('template_code'=>$sTemplateCode, 'name'=>$sName, 'description'=>$sDescription, 'carrier_id'=>$iCarrierId);
		return new self ($aDefinition, $oSource, $aColumns);
	}*/


	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}

	public function toArray()
	{
		$aTemplate = $this->_oDO->toArray();
		//add column data whenever needed
		return $aTemplate;
	}
}



?>