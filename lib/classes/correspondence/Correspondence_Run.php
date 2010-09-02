<?php
class Correspondence_Run
{
	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence = array();
	protected $_oDO;
	public static $aNonSuppliedFields = array('processed_datetime', 'delivered_datetime', 'created_employee_id', 'created', 'file_export_id');

	public function __construct($oCorrespondenceTemplate = null, $mDefinition, $bProcessNow = true)
	{
		$this->_oCorrespondenceTemplate = $oCorrespondenceTemplate;
		if (is_array($mDefinition))
		{
			foreach (self::$aNonSuppliedFields as $sField)
			{
				$mDefinition[$sField] = null;
			}
			if ($mDefinition['scheduled_datetime']== null)
			{
				$mDefinition['scheduled_datetime'] = Data_Source_Time::currentTimestamp();
			}

			$mDefinition['preprinted'] = $mDefinition['preprinted']?1:0;
			$this->_oDO = new Correspondence_Run_ORM($mDefinition);
			if ($bProcessNow)
				$this->process();
		}
		else
		{
			$this->_oDO = $mDefinition;
			$this->_aCorrespondence = Correspondence::getForRun($this);
			$this->_oCorrespondenceTemplate = Correspondence_Template::getForId($this->correspondence_template_id);
		}
	}


	public function process()
	{
		$x = time();
		$bPreprinted = $this->_oDO->preprinted==0?false:true;
		$aCorrespondence = $this->_oCorrespondenceTemplate->getData($bPreprinted);
		foreach ($aCorrespondence as $oCorrespondence)
		{
			$oCorrespondence->_oCorrespondenceRun = $this;
		}
		$this->_aCorrespondence = $aCorrespondence;
		$x = time() - $x;
		//echo count($aCorrespondence)." results processed in $x seconds.<br>";
		$this->processed_datetime = Data_Source_Time::currentTimestamp();
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
		if ($this->_oCorrespondenceTemplate->id == null)
			$this->_oCorrespondenceTemplate->save();
		$this->correspondence_template_id = $this->_oCorrespondenceTemplate->id;
		if ($this->id == null)
		{
			$this->created_employee_id = Flex::getUserId();

		}

		$this->_oDO->save();
		foreach ($this->_aCorrespondence as $oCorrespondence)
		{

			$oCorrespondence->save();
		}
	}

	public function getTemplate()
	{
		return $this->_oCorrespondenceTemplate;
	}

	public function getAllColumns()
	{
		$bPreprinted = $this->preprinted==1?true:false;
		return $this->_oCorrespondenceTemplate->createFullColumnSet( $bPreprinted);

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


	public function setDeliveryDetails ($iFileExportId, $sDeliveredTimeStamp, $iBatchId)
	{
		$this->file_export_id = $iFileExportId;
		$this->delivered_datetime = $sDeliveredTimeStamp;
		$this->correspondence_run_batch_id = $iBatchId;
	}


	public static function get($iId)
	{
		//create a new object based on the id passed in
	}



	public static function getWaitingRuns($sScheduledDateTime = null)
	{
		if ($sScheduledDateTime == null)
			$sScheduledDateTime = Data_Source_Time::currentTimestamp();

		$aRunORM = Correspondence_Run_ORM::getForScheduledDateTime($sScheduledDateTime);

		$aRuns = array();
		foreach ($aRunORM as $oRunORM)
		{
			$aRuns[]= new Correspondence_Run(null, $oRunORM);
		}


		//for each run: get the correspondence objects associated with it,by either simply retrieving them,or by processing the run first
		foreach ($aRuns as $oRun)
		{
			if ($oRun->processed_datetime == null)
				$oRun->process();
		}

		return $aRuns;



	}

	public function getCorrespondenceCode()
	{
		return $this->_oCorrespondenceTemplate->template_code;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField =$mValue;
	}
}