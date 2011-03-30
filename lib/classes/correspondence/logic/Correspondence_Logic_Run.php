<?php
class Correspondence_Logic_Run
{
	const UNDELIVERED 		= 1;
	const DELIVERED 		= 2;
	const PART_DELIVERED 	= 3;
	const DELIVERY_FAILED	= 4;

	protected $_oCorrespondenceTemplate;
	protected $_aCorrespondence = array();
	protected $_oDO;
	protected $_oDataValidationException;
	public static $aNonSuppliedFields = array('processed_datetime', 'delivered_datetime', 'created_employee_id', 'created', 'data_file_export_id', 'pdf_file_export_id');
	protected $_aFileDeliveries = array();
	protected $bDispatchError = false;
	protected $bDispatchedDateTime;

	public function __construct($mDefinition, $oCorrespondenceTemplate = null, $bIncludeCorrespondence = true)
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
		}
		else
		{
			$this->_oDO 					= $mDefinition;
			$this->_aCorrespondence 		= $bIncludeCorrespondence ? Correspondence_Logic::getForRun($this) : array();
			$this->_oCorrespondenceTemplate	= Correspondence_Logic_Template::getForId($this->correspondence_template_id);
			$this->_aFileDeliveries 		= Correspondence_Logic_Run_Dispatch::getForRunId($this->id);
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

	public function process($aData = null)
	{
		Log::getLog()->log('staring run process');
		$oSource = $this->_oCorrespondenceTemplate->getSource();
		if ($aData!=null)
		{
			$this->file_import_id = $oSource->setData($aData);
		}
		
		$bPreprinted = $this->_oDO->preprinted==0?false:true;
		try
		{
			$this->_aCorrespondence 	=  $oSource->getCorrespondence($bPreprinted, $this);
			$this->processed_datetime	= Data_Source_Time::currentTimestamp();
		}
		catch(Correspondence_DataValidation_Exception $e)
		{
			$this->processed_datetime 			= Data_Source_Time::currentTimestamp();
			$this->correspondence_run_error_id 	= $e->iError;
			$this->save();
			$this->handleProcessError($e);
			$this->_oDataValidationException 	= $e;
			throw $e;
		}

		Log::getLog()->log("Run completed in: ".Logic_Stopwatch::getInstance()->lap());
	}

	public function save()
	{
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			throw new Exception("Failed to start template save transaction.");
		}
		try
		{
			$this->_save();
			$oDataAccess->TransactionCommit();
			return $this->id;
		}
		catch (Exception $e)
		{
			// Exception caught, rollback db transaction
			if (!$oDataAccess->TransactionRollback())
			{
				throw new Exception("Failed to start template save transaction.");
			}
			throw $e;
		}
	}

	public function _save()
	{
		if ($this->_oCorrespondenceTemplate->id == null)
		{
			$this->_oCorrespondenceTemplate->save();
		}
		
		$this->correspondence_template_id 	= $this->_oCorrespondenceTemplate->id;
		$this->delivered_datetime 			= $this->bDispatchError ? null : $this->bDispatchedDateTime;
		
		if ($this->id == null)
		{
			// For cli apps we use the system user id (0)
			$this->created_employee_id = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
		}

		$this->_oDO->save();

		foreach($this->_aFileDeliveries as $oDeliveryDetails)
		{			
			$oDeliveryDetails->save();
		}

		foreach ($this->_aCorrespondence as $aCorrespondence)
		{
			foreach($aCorrespondence as $oCorrespondence)
			{
				$oCorrespondence->save();
			}
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
		$x = $this->getCorrespondenceCount();
		return $x['total'];
	}

	public function getAdditionalColumns($iNumberOfDefaultColumns)
	{
		return $this->_oCorrespondenceTemplate->getAdditionalColumnSet($iNumberOfDefaultColumns);
	}

	public function getCorrespondence()
	{
		if ($this->_aCorrespondence == null)
		{
			$this->_aCorrespondence = Correspondence_Logic::getForRun($this);
		}
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
		$aRun 						= $this->_oDO->toArray();
		$aRun['template']			= $this->_oCorrespondenceTemplate->toArray();
		$aRun['correspondence'] 	= array();
		foreach ($this->_aCorrespondence as $iDeliveryMethod => $aCorrespondence)
		{
			foreach ($aCorrespondence as $oCorrespondence)
			{
				$aRun['correspondence'][]	= $oCorrespondence->toArray(true);
			}
		}
		return $aRun;
	}

	public function getDispatchObjectForCarrierModuleId($correspondenceTemplateCarrierModuleId)
	{
		foreach ($this->_aFileDeliveries as $oFileDispatch)
		{
			if ($oFileDispatch->isForCarrierModule($correspondenceTemplateCarrierModuleId))
			{
				return $oFileDispatch;
			}
		}
		return null;
	}

	public function addFileDeliveryDetails($iDataFileExportId, $iPDFFileExportId, $iCorrespondenceTemplateCarrierModuleId, $aDeliveryMethods, $mBatchId)
	{
		$oDispatchObject	= $this->getDispatchObjectForCarrierModuleId($iCorrespondenceTemplateCarrierModuleId);
		if ($oDispatchObject === null)
		{
			$oDispatchObject			= Correspondence_Logic_Run_Dispatch::getForRunAndTemplateCarrierModule($this->id, $iCorrespondenceTemplateCarrierModuleId);
			$this->_aFileDeliveries[]	= $oDispatchObject;
		}

		$oDispatchObject->correspondence_run_id 					= $this->id;
		$oDispatchObject->data_file_export_id 						= $iDataFileExportId;
		$oDispatchObject->pdf_file_export_id 						= $iPDFFileExportId;
		$oDispatchObject->correspondence_template_carrier_module_id = $iCorrespondenceTemplateCarrierModuleId;
		$oDispatchObject->correspondence_run_batch_id 				= (is_numeric($mBatchId) ? $mBatchId : null);
		$oDispatchObject->setDeliveryMethods($aDeliveryMethods);

		if (!is_numeric($mBatchId))
		{
			$oDispatchObject->setException($mBatchId);
			$this->bDispatchError = true;
		}
	}

	public function setDeliveryDetails($sDeliveredDateTime)
	{
		$this->bDispatchedDateTime = $sDeliveredDateTime;
	}

	public function getCarrierModules()
	{
		return $this->_oCorrespondenceTemplate->getCarrierModules();
	}

	public function getCorrespondenceCodeForCarrierModule($iCarrierModuleId)
	{
		return $this->_oCorrespondenceTemplate->getCorrespondenceCodeForCarrierModule($iCarrierModuleId);
	}

	public function getDataValidationException()
	{
		return $this->_oDataValidationException==null?false:$this->_oDataValidationException;
	}

	public static function getWaitingRuns($sScheduledDateTime = null)
	{
		if ($sScheduledDateTime == null)
		{
			$sScheduledDateTime = Data_Source_Time::currentTimestamp();
		}
		
		$aRunORM 	= Correspondence_Run::getForScheduledDateTime($sScheduledDateTime);
		$aRuns 		= array();
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
					if (get_class($e)!= 'Correspondence_DataValidation_Exception')
					{
						// Data validation exceptions have already been dealt with at this stage.
						throw $e;
					}
					Log::getLog()->log('Run '.$oRun->id.' failed to process due to '.$e->failureReasonToString());
				}
			}
			$aRuns[] = $oRun;
		}
		return $aRuns;
	}

	private function handleProcessError($oDataValidationException)
	{
		$sErrorReportFilePath	= null;
		$sMessage 				= null;
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
                else if ($oDataValidationException->iError == CORRESPONDENCE_RUN_ERROR_DATASET_MISMATCH)
                {
                    $sMessage =' The generated correspondence records did not match up with the supplied correspondence generation parameters.';
                    $this->correspondence_run_error_id = CORRESPONDENCE_RUN_ERROR_DATASET_MISMATCH;
                }
		else
		{
			$sMessage =' The generated correspondence data contains validation errors. See attached CSV file for details. No correspondence was generated.';
			$sErrorReportFilePath = $oDataValidationException->sFileName;

		}
		Log::getLog()->log("Sending Correspondence Run Error email");
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

	public function getCreatedEmployeeName()
	{
		$oEmployee = Employee::getForId($this->created_employee_id);
		return $this->created_employee_id==0?'System User':$oEmployee->FirstName." ".$oEmployee->LastName;

	}

	public function getDataFileInfo()
	{
		$aResult = array();
		foreach($this->_aFileDeliveries as $oDeliveryDetails)
		{
			$aResult[] = $oDeliveryDetails->getFileInfo();
		}
		return $aResult;
	}

	public function getExportFileNames()
	{
		$aFileNames = array();
		foreach($this->_aFileDeliveries as $oDeliveryDetails)
		{
			$aFileNames[]= $oDeliveryDetails->getDataFileName();
		}
		return $aFileNames;
	}

	public function getDispatchInfoByDeliveryMethod()
	{
		$aResult = array('post'=>null, 'email'=>null);
		foreach(array_keys($aResult) as $sMethod )
		{
			$aResult[$sMethod] = $this->getDispatchInfoForDeliveryMethod(strtoupper($sMethod));
		}

		return $aResult;
	}



	public function getDispatchInfoForDeliveryMethod($sMethod)
	{
		foreach($this->_aFileDeliveries as $oDeliveryDetails)
		{
			if ($oDeliveryDetails->isForDeliveryMethod($sMethod))
			{
				return $oDeliveryDetails->getFileInfo();
			}
		}
		return null;
	}

	public function getCarriersByDeliveryMethod()
	{
		$aResult = array('post'=>null, 'email'=>null);
		foreach(array_keys($aResult) as $sMethod)
		{
			$aResult[$sMethod] = $this->getCarrierForDeliveryMethod(strtoupper($sMethod));
		}

		return $aResult;
	}

	public function getCarrierForDeliveryMethod($sMethod)
	{
		foreach($this->_aFileDeliveries as $oDeliveryDetails)
		{
			if ($oDeliveryDetails->isForDeliveryMethod($sMethod))
			{
				return 	$oDeliveryDetails->getCarrierName();
			}
		}
		return null;
	}

	public function getCorrespondenceCount()
	{
		$aCount = array('total' => 0, 'email' => 0, 'post' => 0);
		$iTotal	= 0;
		foreach ($this->_aCorrespondence as $iDeliveryMethod => $aCorrespondence)
		{
			$iCount	= count($aCorrespondence);
			$iTotal	+= $iCount;
			if ($iDeliveryMethod == CORRESPONDENCE_DELIVERY_METHOD_POST)
			{
				$aCount['post']	= $iCount;
			}
			else
			{
				$aCount['email']	= $iCount;
			}
		}
		$aCount['total']	= $iTotal;
		return $aCount;
	}

	public function getDeliveryStatus()
	{
		if (count($this->_aFileDeliveries) == 0)
			return self::UNDELIVERED;

		$bNotAllFilesDelivered = false;
		$bFilesDelivered = false;
		foreach ($this->_aFileDeliveries as $oDelivery)
		{
			$iStatus = $oDelivery->getStatus();
			$bFilesDelivered = $iStatus == Correspondence_Logic_Run_Dispatch::DELIVERED ? true : $bFilesDelivered;
			$bNotAllFilesDelivered =  $oDelivery->getStatus() == Correspondence_Logic_Run_Dispatch::DELIVERY_FAILED ? true : $bNotAllFilesDelivered;
		}

		return $bFilesDelivered && $bNotAllFilesDelivered ? self::PART_DELIVERED : (!$bNotAllFilesDelivered ? self::DELIVERED : self::DELIVERY_FAILED );
	}

	public function sendRunCreatedEmail()
	{
		$oEmail	= Correspondence_Email::getForEmailNotificationSystemName('CORRESPONDENCE');
		$oEmail->setSubject("Correspondence Run Scheduling Notification for '".$this->getTemplateName());
		$sHeader	= "Correspondence Run Delivery Scheduling Notification for '".$this->getTemplateName();
		$sMessage 	= 'Details for Run ID '.$this->id.":";

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
		if ($oEmployee!= null && $oEmployee->email!=null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();

	/*  	$myFile = "email.html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $oEmail->toString());
		fclose($fh);*/
	}

	private function sendErrorEmail($sMessage, $sErrorReportFilePath = null)
	{
		// Send email
		$oEmail 	= Correspondence_Email::getForEmailNotificationSystemName('CORRESPONDENCE');
		$oEmail->setSubject("Correspondence Run Error Notification for Template '".$this->getTemplateName()."'");
		$sHeader 	= "Correspondence Procsessing Error Notification for '".$this->getTemplateName()."', Run ID ".$this->id;
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
		if ($oEmployee!= null && $oEmployee->email!=null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
		Log::getLog()->log("\t ... Email success: ".(string)$oEmail->getSendStatus());
	}

	public function formatDatTime($sDateTime, $bDateOnly = false)
	{
		$sDate =  date('d/m/Y', strtotime($sDateTime));//." - ".date('H:i:s', strtotime($sDateTime));
		$sTime = date('H:i:s', strtotime($sDateTime));

		return $bDateOnly ? $sDate : $sDate." - ".$sTime;
	}

	/*
	 * The following different scenarios can occur,each requiring its own variation on the basic run report format
	 * 1 - The Run fully succeeded: this results in the run status reported as 'Dispatched', and all data file and carrier information is listed
	 * 2 - An exception occurred in the export process (ie the export file creation in the case of non api delivery): this results in the run status reported as Dispatch Failed, with a failure reson listed
	 * 3 - An exception occurred in the delivery of one or more files, but some files were delivered successfully: status will be 'Partially Dispatched' and the error will be reported on against the individual file delivery that failed.
	 * 4 - Exceptions occcured during the delivery of all files: the run status will be 'Dispatch Failed', any errors will be reported on against individual data files
	 * 5 - Upon nth attempt at delivery of a run, ie part of the run was delivered succesfully in the past, the delivery datetime of the past success is reported, along with any success or failures in the present attempt at delivery.
	 * 6 - If on nth attempt at delivery an error occurs during file creation, the overall run error will be reported against individual file deliveries, and the overall run will have a status of 'Partially Dispatched'.
	 */

	public function sendDispatchEmail($iCurrentBatch)
	{
		$iStatus 			= $this->getDeliveryStatus();
		$bDispatchFailed 	= $iStatus == self::UNDELIVERED || $iStatus == self::DELIVERY_FAILED ? true : false;
		$sDispatchStatus 	= $this->generateDispatchStatusForReport();
		$sDispatchDate 		= $this->bDispatchedDateTime != null ? $this->formatDatTime($this->bDispatchedDateTime) : $this->formatDatTime(Data_Source_Time::currentTimestamp());

		$oEmail 	= Correspondence_Email::getForEmailNotificationSystemName('CORRESPONDENCE');
		$sFailure 	= $bDispatchFailed?'Failure':null;
		$oEmail->setSubject("Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."'");//."' (Letter Code ".$this->getCorrespondenceCode().")");
		$sHeader	= "Correspondence Delivery ".$sFailure." Notification for '".$this->getTemplateName()."'";//."' (Letter Code ".$this->getCorrespondenceCode().")";
		$sMessage 	= 'Delivery '.$sFailure.' Details for Run ID '.$this->id.":";
		$this->generateReportEmailBody($oEmail,$sHeader,$sMessage);
		
		$oEmail->addPivotTableRow('Correspondence Items', $this->generateDeliveryMethodReport());
		
		// If $this->_oDataValidationException is null this means that the problem occurred during file delivery to all carriers
		if (!$bDispatchFailed || $this->_oDataValidationException== null)
		{

			$oEmail->addPivotTableRow('Dispatch Date', $sDispatchDate);
			$oEmail->addPivotTableRow( 'Data Files', $this->generateFileDeliveryReport());
			$oEmail->addPivotTableRow( 'Status', $sDispatchStatus);
		}
		else
		{
			$oEx			= $this->_oDataValidationException;
			$sReason		= (($oEx instanceof Correspondence_Dispatch_Exception) ? $oEx->failureReasonToString() : 'Failure');
			$sMessage		= $oEx->getMessage();
			$sErrorMessage	= $sMessage!= NULL ? "Error Description: {$sMessage}" : NULL;
			$oEmail->addPivotTableRow( 'Status', "Dispatch Failed. Reason: '{$sReason}'. {$sErrorMessage}");
		}
		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmployee = Employee::getForId($this->created_employee_id);
		if ($oEmployee!= null && $oEmployee->email!=null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}

	public function generateDispatchStatusForReport()
	{
		$iStatus 			= $this->getDeliveryStatus();
		$sDispatchStatus	= (($iStatus == self::DELIVERED) ? 'Dispatched' : ($iStatus == self::PART_DELIVERED ? 'Partially Dispatched' : 'Dispatch Failed'));
		return $sDispatchStatus;
	}

	public function generateFileDeliveryReport($bPreformatted = true)
	{
		$aFileInfo 		= $this->getDataFileInfo();
		$aFileBreakDown	= array();
		foreach ($aFileInfo as $aItem)
		{
			// If the run was partially dispatched in a different batch, and during this batch an exception occurred during the export file creation
			// process, there will be an exception on the overall run, as opposed to an individual file dispatcth. So in that case we have to report
			// the overall run exception against the individual file dispatch, and not mark the whole run as 'failed' because part of the run was indeed delivered.
			$sErrorString 		= (($this->_oDataValidationException != null) ? $this->_oDataValidationException->failureReasonToString() : $aItem['file']);
			$sFileString 		= (($aItem['status_code'] == Correspondence_Logic_Run_Dispatch::DELIVERY_FAILED) ? $aItem['status']." - ".$sErrorString : $aItem['file']);
			$aDeliveryMethods	= array();
			foreach($aItem['delivery_methods'] as $sMethod => $iMethod)
			{
				$aDeliveryMethods[]	= $sMethod;
			}
			$sDeliveryMethods	= implode(', ', $aDeliveryMethods);

			if ($bPreformatted)
			{
				$aFileBreakDown[]	= "- ".$aItem['carrier'].": ".$sFileString;
				if ($sDeliveryMethods != null)
				{
					$aFileBreakDown[]	= $sDeliveryMethods;
				}
			}
			else
			{
				$aFileBreakDown[]	= array('carrier' => $aItem['carrier'], 'file' => $sFileString, 'delivery_methods' => $aDeliveryMethods);
			}
		}
		return $aFileBreakDown;
	}

	public function generateDeliveryMethodReport($bPreformatted=true)
	{
		$aCount 				= $this->getCorrespondenceCount();
		$aFilesByDeliveryMethod	= $this->getDispatchInfoByDeliveryMethod();
		$sEmailDeliveryDetails 	= null;
		$sPostDeliveryDetails	= null;
		if ($aCount['post'] > 0)
		{
			$sStatusInfo	= null;
			if ($aFilesByDeliveryMethod['post'] === NULL)
			{
				$sPostDeliveryDetails	= "Delivery was not done.";
			}
			else
			{
				if ($aFilesByDeliveryMethod['post']['status_code'] == Correspondence_Logic_Run_Dispatch::DELIVERY_FAILED)
				{
					$sStatusInfo	= ", ".$aFilesByDeliveryMethod['post']['status'];
				}

				if (($aFilesByDeliveryMethod['post']['batch'] != null))
				{
					$sPostDeliveryDetails 	= $aFilesByDeliveryMethod['post']['carrier'].", at ".$this->formatDatTime($aFilesByDeliveryMethod['post']['dispatch_date'])." , Batch ".$aFilesByDeliveryMethod['post']['batch'];
				}
				else
				{
					$sPostDeliveryDetails	= $aFilesByDeliveryMethod['post']['carrier'].$sStatusInfo;
				}
			}
		}
		
		if ($aCount['email'] > 0)
		{
			$sStatusInfo	= null;
			if ($aFilesByDeliveryMethod['email'] === NULL)
			{
				$sEmailDeliveryDetails	= "Delivery was not done."; 
			}
			else
			{
				if ($aFilesByDeliveryMethod['email']['status_code'] == Correspondence_Logic_Run_Dispatch::DELIVERY_FAILED)
				{
					$sStatusInfo	= ", ".$aFilesByDeliveryMethod['email']['status'];
				}

				if (($aFilesByDeliveryMethod['email']['batch'] != null))
				{
					$sEmailDeliveryDetails	= $aFilesByDeliveryMethod['email']['carrier']." at ".$this->formatDatTime($aFilesByDeliveryMethod['email']['dispatch_date'])." ,Batch ".$aFilesByDeliveryMethod['email']['batch'];
				}
				else
				{
					$sEmailDeliveryDetails	= $aFilesByDeliveryMethod['email']['carrier'].$sStatusInfo;
				}
			}
		}

		$aItemBreakDown	= array();
		if ($bPreformatted)
		{
			$aItemBreakDown = 	array(
									"- Post : ".$aCount['post']." (".$sPostDeliveryDetails.")",
									"- Email: ".$aCount['email']." (".$sEmailDeliveryDetails.")",
									"- Total: ".$aCount['total']
								);
		}
		else
		{
			$aItemBreakDown['POST'] 	=	array(
												'count' 	=> $aCount['post'], 
												'details'	=> $sPostDeliveryDetails, 
												'file_info'	=> $aFilesByDeliveryMethod['post']
											);
			$aItemBreakDown['EMAIL'] 	= 	array(
												'count' 	=> $aCount['email'], 
												'details'	=> $sEmailDeliveryDetails, 
												'file_info'	=> $aFilesByDeliveryMethod['email']
											);
			$aItemBreakDown['TOTAL'] 	= 	array(
												'count' 	=> $aCount['total'], 
												'details'	=> ''
											);
		}

		return $aItemBreakDown;
	}

	public function generateReportEmailBody($oEmail,$sHeader, $sMessage)
	{
		$body = $oEmail->getBody();

		$oEmail->addTextHeader(3, $sHeader);

		$oEmail->addTextHeader(4, $sMessage);


		$table =& $oEmail->setTable();

		$oEmail->addPivotTableRow('Process Date', $this->processed_datetime==null?'process at delivery time':date('d/m/Y', strtotime($this->processed_datetime))." - ".date('H:i:s', strtotime($this->processed_datetime)));

		$sSourceType = Correspondence_Source_Type::getForId($this->getSourceType())->name;
		$sSourceFile = $this->getSourceFileName()==null?null:" (".$this->getSourceFileName().")";
		$oEmail->addPivotTableRow('Data Source', $sSourceType.$sSourceFile);
		$oEmail->addPivotTableRow('Template', $this->getTemplateName());
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

	public function getBatchIdForTemplateCarrierModule($oTemplateCarrierModuleObject)
	{
		$oDO	= Correspondence_Run_Dispatch::getForRunAndTemplateCarrierModule($this->id, $oTemplateCarrierModuleObject->id);
		return ($oDO == null ? null : $oDO->correspondence_run_batch_id);
	}

	public function getDeliveryMethods()
	{
		return array_keys($this->_aCorrespondence);
	}

	public function validateCarrierModuleConfiguration()
	{
		$aDispatchers = $this->getCarrierModules();
		$aRunDeliveryMethods = $this->getDeliveryMethods();
		$aDispatcherDeliveryMethods = array();
		foreach($aDispatchers as $oDispatcher)
		{
			$aDispatcherDeliveryMethods = array_merge( $oDispatcher->getDeliveryMethods(), $aDispatcherDeliveryMethods );
		}

		$aDiff = array_diff($aRunDeliveryMethods, $aDispatcherDeliveryMethods );

		return count($aDiff) === 0;
	}

	// sendWaitingRuns: Dispatches all correspondence runs that are processed and scheduled for delivery.
	public static function sendWaitingRuns()
	{
		// Retrieve the set of correspondence runs that should be sent
		Log::getLog()->log('Retrieving Correspondence Runs');
		$aRuns = Correspondence_Logic_Run::getWaitingRuns();
		if (count($aRuns)>0)
		{
			$oDataAccess	= DataAccess::getDataAccess();
			if (!$oDataAccess->TransactionStart())
			{
				throw new Exception("Failed to start transaction before sending waiting correspondence runs.");
			}
			
			$oBatch					= new Correspondence_Run_Batch();
			$oBatch->batch_datetime	= Data_Source_Time::currentTimestamp();
			$oBatch->save();

			foreach ($aRuns as $oRun)
			{
				if (!$oRun->getDataValidationException())
				{
					Log::getLog()->log('Dispatching Run '.$oRun->id);
					try
					{
						if (!$oRun->validateCarrierModuleConfiguration())
						{
							throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::SYSTEM_CONFIG, new Exception("Not every delivery method in the Run has a carrier module associated with it."));
						}
						
						$aDispatchers = $oRun->getCarrierModules();

						foreach ($aDispatchers as $oDispatcher)
						{							
							$oDispatcher->exportRun($oRun, $oBatch->id);							
						}
					}
					catch(Exception $e)
					{
						$oRun->setException($e);
						$oRun->save();
						
						$sReason	= (($e instanceof Correspondence_Dispatch_Exception) ? $e->failureReasonToString() : '');
						Log::getLog()->log($oRun->id." could not be dispatched. Reason: '{$sReason}' (message: ".$e->getMessage()."). Full Exception Details: ".$e->__toString());
						
						$oRun->sendDispatchEmail($oBatch->id);
						continue;
					}

					foreach ($aDispatchers as $oDispatcher)
					{
						$oDispatcher->deliverRun();
					}
					
					$oRun->setDeliveryDetails(Data_Source_Time::currentTimestamp());
					$oRun->save();
					$oRun->sendDispatchEmail($oBatch->id);
				}
			}
			
			if ($oBatch->hasDispatchRecords())
			{
				// Dispatch records created, keep the batch
				Log::getLog()->log('Sending Batch Delivery Email');
				
				// Send the batch report email
				self::sendBatchDeliveryEmail($oBatch, $aRuns);
				
				if (!$oDataAccess->TransactionCommit())
				{
					throw new Exception("Failed to commit transaction before sending waiting correspondence runs.");
				}
				
				Log::getLog()->log('Finished Batch '.$oBatch->id);
			}
			else
			{
				Log::getLog()->log('Batch is empty, removing (rollback)');
				
				// Send the batch report email (no batch, means it failed)
				self::sendBatchDeliveryEmail(null, $aRuns);
				
				// Abandon the batch, rollback
				if (!$oDataAccess->TransactionRollback())
				{
					throw new Exception("Failed to rollback transaction before sending waiting correspondence runs.");
				}
			}
		}
		$sMessage = count($aRuns)==0?"There were no runs to process":(count($aRuns)==1?"Processed ".count($aRuns)." run":"Processed ".count($aRuns)." runs");
		Log::getLog()->log($sMessage);
	}


	public static  function sendBatchDeliveryEmail($oBatch=null, $aRuns)
	{
		$oEmail	= Correspondence_Email::getForEmailNotificationSystemName('CORRESPONDENCE');
		
		if ($oBatch !== null)
		{
			// Batch succeeded (maybe partially)
			$oEmail->setSubject("Correspondence Delivery Summary for Batch ID  ".$oBatch->id);
			$oEmail->addTextHeader(3, "Correspondence Delivery Summary for Batch ID ".$oBatch->id);
			$oEmail->addTextHeader(4, "Batch ID ".$oBatch->id." was dispatched on ".date('d/m/Y', strtotime($oBatch->batch_datetime))." - ".date('H:i:s', strtotime($oBatch->batch_datetime)));
			$oEmail->addTextHeader(4, "Details:");
		}
		else
		{
			// Batch failed
			$iNow	= time();
			$oEmail->setSubject("Correspondence Delivery Summary: Failed Batch");
			$oEmail->addTextHeader(3, "Correspondence Delivery Summary: Failed Batch");
			$oEmail->addTextHeader(4, "An attempted Batch delivery was completed unsuccessfully on ".date('d/m/Y', $iNow)." - ".date('H:i:s', $iNow));
			$oEmail->addTextHeader(4, "Details:");
		}
		
		$oEmail->setTable(array('Processed','Source','Template','Created By','Items','Data File','Status'));

		$sTableStyle	= Correspondence_Email::TD_STYLE;
		foreach ($aRuns as $oRun)
		{
			$aRowData 		= array();
			$aRowData[]		= date('d/m/Y', strtotime($oRun->processed_datetime))." - ".date('H:i:s', strtotime($oRun->processed_datetime));
			$sSourceType 	= Correspondence_Source_Type::getForId($oRun->getSourceType())->name;
			$sSourceFile	= $oRun->getSourceFileName()==null?null:" (".$oRun->getSourceFileName().")";
			$aRowData[]		= $sSourceType.$sSourceFile;
			$aRowData[]		= $oRun->getTemplateName();
			$aRowData[]		= $oRun->getCreatedEmployeeName();
			$aRowData[]		= $oRun->generateDeliveryMethodReport();
			$aRowData[]		= $oRun->generateFileDeliveryReport();
			$oException 	= $oRun->getDataValidationException();
			$sStatus 		= $oException != null && get_class($oException) != 'Correspondence_Dispatch_Exception'? "Data Processing Failed" : $oRun->generateDispatchStatusForReport();
			$sFailureReason	= null;
			if (($oException !== null) && ($oException instanceof Correspondence_Dispatcher_Exception))
			{
				$sFailureReason	= $oException->failureReasonToString();
			}
			
			$sFailureReason .= $sFailureReason == "Invalid Data" ? ". See attached error report." : null;
			$aRowData[]		= $sFailureReason == null?$sStatus:$sStatus." - ".$sFailureReason;
			$tr 			= $oEmail-> addTableRow($aRowData, $sTableStyle);

			if (($oException != null) && ($oException->sFileName != null))
			{
				$sFile = file_get_contents($oException->sFileName);
				$sFileName = substr($oException->sFileName, strrpos($oException->sFileName , "/" )+1);
				$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
			}
			
			$sTableStyle	= $sTableStyle==Correspondence_Email::TD_STYLE?Correspondence_Email::TD_ALTERNATIVE_STYLE:Correspondence_Email::TD_STYLE;
		}

		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}
}
?>