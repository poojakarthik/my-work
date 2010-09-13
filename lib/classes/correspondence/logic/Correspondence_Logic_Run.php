<?php
class Correspondence_Logic_Run
{
	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence = array();
	protected $_oDO;
	public static $aNonSuppliedFields = array('processed_datetime', 'delivered_datetime', 'created_employee_id', 'created', 'data_file_export_id', 'pdf_file_export_id');

	public function __construct($mDefinition, $oCorrespondenceTemplate = null, $bIncludeCorrespondence = true, $bProcessNow = true)
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
			$this->_oDO = new Correspondence_Run($mDefinition);
			if ($bProcessNow)
				$this->process($mDefinition['forece_empty_run']);

		}
		else
		{
			$this->_oDO = $mDefinition;
			$this->_aCorrespondence = $bIncludeCorrespondence?Correspondence_Logic::getForRun($this):array();
			$this->_oCorrespondenceTemplate = Correspondence_Logic_Template::getForId($this->correspondence_template_id);
		}
	}

	public function getCarrierId()
	{
		return $this->_oCorrespondenceTemplate->carrier_id;
	}

	public function process($bNoDataOk = false)
	{
		try
		{
			$bPreprinted = $this->_oDO->preprinted==0?false:true;
			$aCorrespondence = $this->_oCorrespondenceTemplate->getData($bPreprinted, $bNoDataOk);
			foreach ($aCorrespondence as $oCorrespondence)
			{
				$oCorrespondence->_oCorrespondenceRun = $this;
			}
			$this->_aCorrespondence = $aCorrespondence;
			$this->processed_datetime = Data_Source_Time::currentTimestamp();
			$this->file_import_id = $this->_oCorrespondenceTemplate->importSource();
			$this->save();
		}
		catch(Correspondence_DataValidation_Exception $e)
		{
			$this->processed_datetime = Data_Source_Time::currentTimestamp();
			$this->file_import_id = $this->_oCorrespondenceTemplate->importSource();
			$this->correspondence_run_error_id = $e->iError;
			$this->save();
			$this->handleProcessError($e);
			throw $e;
		}
	}

	public function save()
	{
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
			throw $e;
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

	public function count()
	{
		return count($this->_aCorrespondence);
	}

	public function getAdditionalColumns($iNumberOfDefaultColumns)
	{
		return $this->_oCorrespondenceTemplate->getAdditionalColumnSet($iNumberOfDefaultColumns);
	}

	public function getCorrespondence()
	{
		if ($this->_aCorrespondence == null)
			$this->_aCorrespondence = Correspondence_Logic::getForRun($this);
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

	public function toArray()
	{
		$aRun = $this->_oDO->toArray();
		$aRun['template']= $this->_oCorrespondenceTemplate->toArray();
		$aRun['correspondence'] = array();
		foreach ($this->_aCorrespondence as $oCorrespondence)
		{
			$aRun['correspondence'][]= $oCorrespondence->toArray(true);
		}
		return $aRun;
	}


	public function setDeliveryDetails ($iDataFileExportId, $sDeliveredTimeStamp, $iPDFFileExportId, $iBatchId)
	{
		$this->data_file_export_id = $iDataFileExportId;
		$this->pdf_file_export_id = $iPDFFileExportId;
		$this->delivered_datetime = $sDeliveredTimeStamp;
		$this->correspondence_run_batch_id = $iBatchId;
	}


	public function getCarrierModule()
	{

		return $this->_oCorrespondenceTemplate->getCarrierModule();
	}

	public function getCorrespondenceCode()
	{
		return $this->_oCorrespondenceTemplate->template_code;
	}


	public static function getWaitingRuns($sScheduledDateTime = null)
	{
		if ($sScheduledDateTime == null)
			$sScheduledDateTime = Data_Source_Time::currentTimestamp();

		$aRunORM = Correspondence_Run::getForScheduledDateTime($sScheduledDateTime);

		$aRuns = array();
		foreach ($aRunORM as $oRunORM)
		{
			$oRun = new Correspondence_Logic_Run($oRunORM);
			if ($oRun->processed_datetime == null)
			{
				try
				{
					$oRun->process();
					$oRun->save();
					$aRuns[] = $oRun;
				}
				catch(Exception $e)
				{
					if (get_class($e)!= 'Correspondence_DataValidation_Exception')//datavalidation exceptions have already been dealt with at this stage.
						throw $e;
				}
			}
			else
			{
				$aRuns[] = $oRun;
			}
		}

		return $aRuns;

	}

	private function handleProcessError($oDataValidationException)
	{
		$sErrorReportFilePath;
		$sMessage = 'The following problems occurred when generating correspondence for run id '.$this->id.' (letter code '.$this->getTemplate()->template_code.'). ';
		if ($oDataValidationException->iError == CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX)
		{
			$sMessage.=' The data source sql query is invalid. No correspondence data could be retrieved. No correspondence was generated.';
			$this->correspondence_run_error_id = CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX;
		}
		else if ($oDataValidationException->iError == CORRESPONDENCE_RUN_ERROR_NO_DATA)
		{
			$sMessage.=' The data source contained no data. No correspondence was generated.';
			$this->correspondence_run_error_id = CORRESPONDENCE_RUN_ERROR_NO_DATA;
		}
		else
		{
			$sMessage.=' The generated correspondence data contains validation errors. See attached CSV file for details. No correspondence was generated.';
			$sErrorReportFilePath = $oDataValidationException->sFileName;

		}


		$this->sendErrorEmail($sMessage, $sErrorReportFilePath);
	}

	private function sendErrorEmail($sMessage, $sErrorReportFilePath = null)
	{

		//send email
		$oEmail = new Email_Notification();
		$oEmail->setSubject("Correspondence Run Error Notification for Letter Code ".$this->getCorrespondenceCode().", Run ID ".$this->id);
		if ($sErrorReportFilePath!=null)
		{
			$sFile = file_get_contents($sErrorReportFilePath);
			$sFileName = substr($sErrorReportFilePath, strrpos( $sErrorReportFilePath , "/" )+1);
			$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
		}
		$oEmail->setBodyHTML($sMessage);
		$oEmployee = Employee::getForId($this->created_employee_id);
		$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		//$oEmail->addCc($email, $name='');
		//$oEmail->addBcc($email);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}

	public static function getForBatchId($iBatchId, $bToArray = false)
	{
		$aRunORM = Correspondence_Run::getForBatchId($iBatchId);
		$aRuns = array();
		foreach ($aRunORM as $oRunORM)
		{
			$oRun = new self($oRunORM);

			$aRuns[]= $bToArray?$oRun->toArray():$oRun;
		}

		return $aRuns;
	}

	public static function getForId($iId, $bIncludeCorrespondence = true)
	{
		return new Correspondence_Logic_Run(Correspondence_Run::getForId($iId),null, $bIncludeCorrespondence);
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