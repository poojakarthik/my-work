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

	private function sendErrorEmail($sMessage, $sErrorReportFilePath = null)
	{
		//send email
		$oEmail = new Email_Notification(EMAIL_NOTIFICATION_CORRESPONDENCE);
		$oEmail->setSubject("Correspondence Run Error Notification for Letter Code ".$this->getCorrespondenceCode());
		$sHeader = "Correspondence Procsessing Error Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode()."), Run ID ".$this->id;
		$body = $this->generateReportEmailBody($sHeader,"Details:" );
		$this->generateReportEmailTableRow($body->html->body->table(0),'Error Details', $sMessage);

		if ($sErrorReportFilePath!=null)
		{
			$sFile = file_get_contents($sErrorReportFilePath);
			$sFileName = substr($sErrorReportFilePath, strrpos( $sErrorReportFilePath , "/" )+1);
			$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
		}
		self::appendEmailSignature($body);

		$sHtml = $body->saveHTML();
		$oEmail->setBodyHTML($sHtml);
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		//$oEmail->addCc($email, $name='');
		//$oEmail->addBcc($email);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}

	public function sendDispatchEmail($bDispatchFailed = false)
	{
		$oEmail = new Email_Notification(EMAIL_NOTIFICATION_CORRESPONDENCE);
		$oEmail = new Email_Notification();

		$sFailure = $bDispatchFailed?'Failure':null;
		$oEmail->setSubject("Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")");
		$sHeader = "Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."' (Letter Code ".$this->getCorrespondenceCode().")";
		$sMessage = 'Delivery '.$sFailure.' Details for Run ID '.$this->id.":";
		$body = $this->generateReportEmailBody($sHeader,$sMessage );

		$aCount = $this->getCorrespondenceCount();
		$this->generateReportEmailTableRow($body->html->body->table(0),'Correspondence Items', array("Post : ".$aCount['post'], "Email: ".$aCount['email'], "Total: ".$aCount['total']));
		if (!$bDispatchFailed)
		{
			$this->generateReportEmailTableRow($body->html->body->table(0),'Dispatch Date', date('d/m/Y', strtotime($this->delivered_datetime))." - ".date('h:i:s', strtotime($this->delivered_datetime)));
			$this->generateReportEmailTableRow($body->html->body->table(0), 'Data File', $this->getExportFileName());
			$this->generateReportEmailTableRow($body->html->body->table(0), 'Status', 'Dispatched');
		}
		else
		{
			$this->generateReportEmailTableRow($body->html->body->table(0), 'Status', 'Dispatch Failed. Reason:'.$this->_oDataValidationException->failureReasonToString());
		}
		self::appendEmailSignature($body);
		$sHtml = $body->saveHTML();

		$oEmail->setBodyHTML($sHtml);
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}

	public function generateReportEmailTableRow(&$table, $key, $mValue)
	{
		$strTHStyle			= "text-align: right; vertical-align: top;color: #eee; background-color: #333; width: 15em; padding-right:10px;";
		$strTDStyle			= "text-align: left;vertical-align: top; color: #333; background-color: #eee; padding-left:10px;";
		$tr =& $table->tr();
		$tr->td(0)->setValue($key);
		if (is_array($mValue))
		{
			$td = $tr->td(1);
			$iDivCount = 0;
			foreach ($mValue as $value)
			{
				$td->div($iDivCount)->setValue($value);
				$iDivCount++;
			}

		}
		else
		{
			$tr->td(1)->setValue($mValue);
		}

		$tr->td(0)->style = $strTHStyle;
		$tr->td(1)->style = $strTDStyle;

	}

	public function generateReportEmailBody($sHeader, $sMessage)
	{
		$strTHStyle			= "text-align: right; vertical-align: top;color: #eee; background-color: #333; width: 15em; padding-right:10px;";
		$strTDStyle			= "text-align: left;vertical-align: top; color: #333; background-color: #eee; padding-left:10px;";
		$strTDAutoStyle		= "";
		$strTDWidthStyle	= "min-width: 15em; max-width: 15em;";
		$strTableStyle		= "font-family: Calibri, Arial, sans-serif; width:99%; border: .1em solid #333; border-spacing: 0; border-collapse: collapse;";
		$sStyle				= "font-family: Calibri, Arial, sans-serif;";

		$body = new Flex_Dom_Document();

		$h3 = $body->html->body->h3();
		$h3->setValue ($sHeader);
		$h3->style = $sStyle;

		$h4 = $body->html->body->h4();
		$h4->setValue ($sMessage);
		$h4->style =  $sStyle;



		$table =& $body->html->body->table();
		$table->style = $strTableStyle;

		$tr =& $table->tr();
		$tr->td(0)->setValue('Process Date');
		$tr->td(1)->setValue(date('d/m/Y', strtotime($this->processed_datetime))." - ".date('h:i:s', strtotime($this->processed_datetime)));
		$tr->td(0)->style = $strTHStyle;
		$tr->td(1)->style = $strTDStyle;

		$tr =& $table->tr();
		$tr->td(0)->setValue('Data Source');

		$sSourceType = Correspondence_Source_Type::getForId($this->getSourceType())->name;
		$sSourceFile = $this->getSourceFileName()==null?null:" (".$this->getSourceFileName().")";

		$tr->td(1)->setValue($sSourceType.$sSourceFile);
		$tr->td(0)->style = $strTHStyle;
		$tr->td(1)->style = $strTDStyle;

		$tr =& $table->tr();
		$tr->td(0)->setValue('Template');
		$tr->td(1)->setValue($this->getTemplateName()."(Letter Code ".$this->getCorrespondenceCode().")");
		$tr->td(0)->style = $strTHStyle;
		$tr->td(1)->style = $strTDStyle;

		$tr =& $table->tr();
		$tr->td(0)->setValue('Created By');
		$tr->td(1)->setValue($this->getCreatedEmployeeName());
		$tr->td(0)->style = $strTHStyle;
		$tr->td(1)->style = $strTDStyle;
		return $body;
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

	public static function appendEmailSignature(&$body)
	{
		$body->div();
		$div = $body->div();
		$div->setValue("Regards");
		$div->style = "font-family: Calibri, Arial, sans-serif;";
		$div = $body->div();
		$div->setValue("Flexor");
		$div->style = "font-family: Calibri, Arial, sans-serif;font-weight:bold;";;
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