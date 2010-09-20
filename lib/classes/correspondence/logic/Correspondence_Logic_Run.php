<?php
class Correspondence_Logic_Run
{
	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence = array();
	protected $_oDO;
	protected $_oDataValidationException;
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

			$bProcessNow?$this->process():$this->save();
			$bProcessNow?null:$this->sendRunCreatedEmail();

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

	public function setException($oException)
	{
		$this->_oDataValidationException = $oException;
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
			$this->sendRunCreatedEmail();
		}
		catch(Correspondence_DataValidation_Exception $e)
		{
			$this->processed_datetime = Data_Source_Time::currentTimestamp();
			$this->file_import_id = $this->_oCorrespondenceTemplate->importSource();
			$this->correspondence_run_error_id = $e->iError;
			$this->save();
			$this->handleProcessError($e);
			$this->_oDataValidationException = $e;
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
			//for cli apps we use the system user id (0)
			$this->created_employee_id = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
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

	public function getDataValidationException()
	{
		return $this->_oDataValidationException==null?false:$this->_oDataValidationException;
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
					Log::getLog()->log('Processing Run'.$oRun->id);
					$oRun->process();
					$oRun->save();

				}
				catch(Exception $e)
				{
					if (get_class($e)!= 'Correspondence_DataValidation_Exception')//datavalidation exceptions have already been dealt with at this stage.
						throw $e;
					Log::getLog()->log('Run '.$oRun->id.' failed to process due to '.$e->failureReasonToString());
				}
			}

			$aRuns[] = $oRun;
		}
		return $aRuns;
	}

	private function handleProcessError($oDataValidationException)
	{
		$sErrorReportFilePath;
		$sMessage ;
		if ($oDataValidationException->iError == CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX)
		{
			$sMessage =' The data source sql query is invalid. No correspondence data could be retrieved. No correspondence was generated.';
			$this->correspondence_run_error_id = CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX;
		}
		else if ($oDataValidationException->iError == CORRESPONDENCE_RUN_ERROR_NO_DATA)
		{
			$sMessage =' The data source contained no data. No correspondence was generated.';
			$this->correspondence_run_error_id = CORRESPONDENCE_RUN_ERROR_NO_DATA;
		}
		else
		{
			$sMessage =' The generated correspondence data contains validation errors. See attached CSV file for details. No correspondence was generated.';
			$sErrorReportFilePath = $oDataValidationException->sFileName;

		}
		$this->sendErrorEmail($sMessage, $sErrorReportFilePath);
	}



	public function getSourceType()
	{
		return $this->_oCorrespondenceTemplate->getSourceType();
	}

	public function getSourceFileName()
	{
		if ($this->getSourceType() == CORRESPONDENCE_SOURCE_TYPE_CSV)
		{
			$oFileImport = File_Import::getForId($this->file_import_id);
			return $oFileImport==null?null:$oFileImport->FileName;
		}
		else
		{
			return null;
		}
	}

/*	public function getTemplateName()
	{
		return $this->_oCorrespondenceTemplate->name;
	}*/

	public function getCreatedEmployeeName()
	{
		$oEmployee = Employee::getForId($this->created_employee_id);
		return $oEmployee->FirstName." ".$oEmployee->LastName;

	}

	public function getExportFileName()
	{
		$oFileExport = File_Export::getForId($this->data_file_export_id);
		return $oFileExport==null?null:$oFileExport->FileName;
	}

	public function getCorrespondenceCount()
	{
		$aCount = array('total'=>count($this->_aCorrespondence), 'email'=>0, 'post'=>0);
		foreach ($this->_aCorrespondence as $oCorrespondence)
		{
			$oCorrespondence->correspondence_delivery_method_id == CORRESPONDENCE_DELIVERY_METHOD_POST?$aCount['post']++:$aCount['email']++;
		}
		return $aCount;
	}

	private function sendRunCreatedEmail()
	{
		$oEmail = new Correspondence_Email(EMAIL_NOTIFICATION_CORRESPONDENCE);

		$oEmail->setSubject("Correspondence Run Scheduling Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")");
		$sHeader = "Correspondence Run Delivery Scheduling Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")";
		$sMessage = 'Details for Run ID '.$this->id.":";

		$this->generateReportEmailBody($oEmail,$sHeader,$sMessage);
		$oEmail->addPivotTableRow('Scheduled for Delivery', date('d/m/Y', strtotime($this->scheduled_datetime))." - ".date('H:i:s', strtotime($this->scheduled_datetime)));
		if ($this->processed_datetime!=null)
		{
			$aCount = $this->getCorrespondenceCount();
			$oEmail->addPivotTableRow('Correspondence Items', array("Post : ".$aCount['post'], "Email: ".$aCount['email'], "Total: ".$aCount['total']));
			$oEmail->addPivotTableRow('Status', 'Processed');
		}
		else
		{
			$oEmail->addPivotTableRow('Status', 'Submitted');
		}

		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
			//For query debug purpose
	  	$myFile = "email.html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $oEmail->toString());
		fclose($fh);

	}

	private function sendErrorEmail($sMessage, $sErrorReportFilePath = null)
	{
		//send email
		$oEmail = new Correspondence_Email(EMAIL_NOTIFICATION_CORRESPONDENCE);
		$oEmail->setSubject("Correspondence Run Error Notification for Letter Code ".$this->getCorrespondenceCode());
		$sHeader = "Correspondence Procsessing Error Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode()."), Run ID ".$this->id;
		$this->generateReportEmailBody($oEmail,$sHeader,"Details:");
		$oEmail->addPivotTableRow('Error Details', $sMessage);

		if ($sErrorReportFilePath!=null)
		{
			$sFile = file_get_contents($sErrorReportFilePath);
			$sFileName = substr($sErrorReportFilePath, strrpos( $sErrorReportFilePath , "/" )+1);
			$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
		}
		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();

		$myFile = "email.html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $oEmail->toString());
		fclose($fh);
	}

	public function sendDispatchEmail($bDispatchFailed = false)
	{
		$oEmail = new Correspondence_Email(EMAIL_NOTIFICATION_CORRESPONDENCE);
		$sFailure = $bDispatchFailed?'Failure':null;
		$oEmail->setSubject("Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")");
		$sHeader = "Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")";
		$sMessage = 'Delivery '.$sFailure.' Details for Run ID '.$this->id.":";
		$this->generateReportEmailBody($oEmail,$sHeader,$sMessage);

		$aCount = $this->getCorrespondenceCount();
		$oEmail->addPivotTableRow('Correspondence Items', array("Post : ".$aCount['post'], "Email: ".$aCount['email'], "Total: ".$aCount['total']));
		if (!$bDispatchFailed)
		{
			$oEmail->addPivotTableRow('Dispatch Date', date('d/m/Y', strtotime($this->delivered_datetime))." - ".date('H:i:s', strtotime($this->delivered_datetime)));
			$oEmail->addPivotTableRow( 'Data File', $this->getExportFileName());
			$oEmail->addPivotTableRow( 'Status', 'Dispatched');
		}
		else
		{
			$oEmail->addPivotTableRow( 'Status', 'Dispatch Failed. Reason:'.$this->_oDataValidationException->failureReasonToString());
		}
		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
		$myFile = "email.html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $oEmail->toString());
		fclose($fh);
	}

	public function generateReportEmailBody($oEmail,$sHeader, $sMessage)
	{
		$body = $oEmail->getBody();

		$h3 = $body->html->body->h3();
		$h3->setValue ($sHeader);
		$h3->style = Correspondence_Email::FONT_STYLE;

		$h4 = $body->html->body->h4();
		$h4->setValue ($sMessage);
		$h4->style =  Correspondence_Email::FONT_STYLE;

		$table =& $oEmail->setTable();


		$oEmail->addPivotTableRow('Process Date', $this->processed_datetime==null?'process at delivery time':date('d/m/Y', strtotime($this->processed_datetime))." - ".date('H:i:s', strtotime($this->processed_datetime)));

		$sSourceType = Correspondence_Source_Type::getForId($this->getSourceType())->name;
		$sSourceFile = $this->getSourceFileName()==null?null:" (".$this->getSourceFileName().")";
		$oEmail->addPivotTableRow('Data Source', $sSourceType.$sSourceFile);
		$oEmail->addPivotTableRow('Template', $this->getTemplateName()."(Letter Code ".$this->getCorrespondenceCode().")");
		$oEmail->addPivotTableRow('Created By', $this->getCreatedEmployeeName());
	}

	public static function getForBatchId($iBatchId, $bToArray = false)
	{
		$aRunORM = Correspondence_Run::getForBatchId($iBatchId);
		$aRuns = array();
		foreach ($aRunORM as $oRunORM)
		{
			$oRun 	= new self($oRunORM);
			$aCount	= $oRun->getCorrespondenceCount();
			if ($bToArray)
			{
				$aRun							= $oRun->toArray();
				$aRun['correspondence_count']	= $aCount;
				$aRuns[]						= $aRun;
			}
			else
			{
				$oRun->correspondence_count	= $aCount;
				$aRuns[]					= $oRun;
			}
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