<?php


/**
 * Description of Logic_Collection_BatchProcess_Report
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_BatchProcess_Report {
    const REPORT_ALL = "Collections Batch Process Report";
    const REPORT_PROMISES = "Collection Promises Batch Process Report";
    const REPORT_SUSPENSIONS = "Collection Suspensions Batch Process Report";
    const REPORT_ACCOUNTS = "Accounts";

    const INVOCATION_TYPE_ACCOUNT = "Single Account Mode";
    const INVOCATION_TYPE_BATCH = "Batch Mode";
   
    public static $aCompletedPromises = array();
    public static $aContinuingPromises = array();
    public static $aPromiseExceptions = array();

    public static $aSuspensionExceptions = array();
    public static $aEndedSuspensions = array();
    public static $aContinuingSuspensions = array();

    public static $aScheduledEventInstances = array();
    public static $aCompletedEventInstances = array();
    public static $aFailedEventInstances = array();
    public static $aNoNextEventScheduled = array();
    public static $aExitCollections = array();

    public static $aExceptions = array();

    public static $sInvocationType;

    public static function setProcessInvocationType($sInvocationType)
    {
	self::$sInvocationType = $sInvocationType;
    }

    public static function getExceptions($bToArray = false) {

        if (!$bToArray)
            return self::$aExceptions;
        $aResult = array();
        foreach (self::$aExceptions as $oException)
        {
            $aResult[] = array('message'=> $oException->getMessage(), 'detail'=>$oException->__toString());
        }
         return $aResult;
    }

    public static function getFailedEventInstances($bToArray = false) {
        if (!$bToArray)
            return self::$aFailedEventInstances;

        $aResult = array();
        foreach (self::$aFailedEventInstances as $oInstance)
        {
            $aResult[] = $oInstance->toArray();
        }

        return $aResult;

    }    
    
    
    public static function getCompletedEventInstances($bToArray = false) {
         if (!$bToArray)
            return self::$aCompletedEventInstances;

          $aResult = array();
        foreach (self::$aCompletedEventInstances as $oInstance)
        {
            $aResult[] = $oInstance->toArray();
        }

        return $aResult;

    }


    


    public static function getAccountsWithExceptions()
    {
        $aResult = array();
        foreach (self::$aPromiseExceptions as $oPromise)
        {
            $aResult[] = $oPromise->account_id;
        }

        foreach (self::$aSuspensionExceptions as $oSuspension)
        {
            $aResult[] = $oSuspension->account_id;
        }

        foreach (self::$aFailedEventInstances as $oInstance)
        {
            $aResult[] = $oInstance->account_id;
        }

        return $aResult;

    }
    
   

    public static function addException($e)
    {
        self::$aExceptions[] = $e;
    }

    public static function addAccount($oAccount)
    {
        if (count(self::getEventsForAccount($oAccount)) == 0)
            self::$aNoNextEventScheduled[] = $oAccount;
    }

    public static function getEventsForAccount($oAccount)
    {
        $aEvents = array();
        foreach (self::$aScheduledEventInstances as $oInstance)
           {
                if ($oInstance->account_id == $oAccount->Id)
                    $aEvents[] = $oInstance;
           }

            foreach (self::$aCompletedEventInstances as $oInstance)
           {
               if ($oInstance->account_id == $oAccount->Id)
                    $aEvents[] = $oInstance;
           }

           foreach (self::$aFailedEventInstances as $oInstance)
           {
               if ($oInstance->account_id == $oAccount->Id)
                    $aEvents[] = $oInstance;
           }
           return $aEvents;
    }

    public static function addEvent($oEventInstance)
    {
        if ($oEventInstance->getException() !== null)
        {
                self::$aFailedEventInstances[] = $oEventInstance;
                return;
        }
        switch ($oEventInstance->account_collection_event_status_id)
        {
            case ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED:
                self::$aScheduledEventInstances[] = $oEventInstance;
                break;
            case ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED:
		if ($oEventInstance->isExitEvent())
		    self::$aExitCollections[] = $oEventInstance;
		else
		    self::$aCompletedEventInstances[] = $oEventInstance;
                break;
        }
    }

    public static function addSuspension($oSuspension)
    {
        if ($oSuspension->getException() !== null)
            self::$aSustpensionExceptions[] = $oSuspension;
	else if ($oSuspension->effective_end_datetime === null)
	    self::$aContinuingSuspensions[] = $oSuspension;
        else
            self::$aEndedSuspensions[] = $oSuspension;
    }

    public static function addPromise($oPromise)
    {
        if ($oPromise->getException() != null)
            self::$aPromiseExceptions[] = $oPromise;
	else if ($oPromise->completed_datetime === null)
	    self::$aContinuingPromises[] = $oPromise;
        else
            self::$aCompletedPromises[] = $oPromise;
    }

    public static function hasDataToReport($sReportType = self::REPORT_ALL)
    {
		
	if (
		($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_PROMISES)
		&& (count(self::$aCompletedPromises)>0 || count(self::$aPromiseExceptions)>0 || count(self::$aContinuingPromises) > 0)
	    )
		return true;
	
	
	if (
		($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_SUSPENSIONS)
		&& (count(self::$aEndedSuspensions)>0 || count(self::$aSuspensionExceptions)>0 || count(self::$aContinuingSuspensions) > 0)
	    )
		 return true;
		
	
	    
	if (
		($sReportType === self::REPORT_ALL || $sReportType == self::REPORT_ACCOUNTS)
		&& (count(self::$aScheduledEventInstances)> 0 || count(self::$aCompletedEventInstances)> 0 || count(self::$aFailedEventInstances)> 0 || count(self::$aNoNextEventScheduled) > 0 || count(self::$aExitCollections) > 0 )
	    )
		return true;
	
	
	return false;
	
    }

    public static function generateSummaryReport($sReportType) {
	$aReport = array();
	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_PROMISES)
	{
	    $aReport['Broken Promises'] = count(self::getBrokenPromises());
	    $aReport['Fulfilled Promises'] = count(self::getFulfilledPromises());
	    $aReport['Ongoing Promises'] = count(self::$aContinuingPromises);
	    $aReport['Promise Process Errors'] =  count(self::$aPromiseExceptions);
	}

	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_SUSPENSIONS)
	{
	    $aReport['Ended Suspensions'] = count(self::$aEndedSuspensions);
	    $aReport['Ongoing Suspensions'] = count(self::$aContinuingSuspensions);
	    $aReport['Suspension Process Errors'] =  count(self::$aSuspensionExceptions);
	}

	if ($sReportType === self::REPORT_ALL)
	{
	    $aReport['Scheduled Events'] = count(self::$aScheduledEventInstances);
	    $aReport['Completed Events'] = count(self::$aCompletedEventInstances);
	    $aReport['Failed Events'] = count(self::$aFailedEventInstances);
	    $aReport['No Next Event'] = count(self::$aNoNextEventScheduled);

	}

	return $aReport;
    }

        public static function newGenerateSummaryReport($sReportType) {
	$aReport = array('Processed'=>array(), 'Ongoing'=>array());

	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_PROMISES)
	{
	    $aReport['Processed']['Broken Promises'] = count(self::getBrokenPromises());
	    $aReport['Processed']['Fulfilled Promises'] = count(self::getFulfilledPromises());
	    $aReport['Ongoing']['Ongoing Promises'] = count(self::$aContinuingPromises);
	    $aReport['Processed']['Promise Process Errors'] =  count(self::$aPromiseExceptions);
	}

	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_SUSPENSIONS)
	{
	    $aReport['Processed']['Ended Suspensions'] = count(self::$aEndedSuspensions);
	    $aReport['Ongoing']['Ongoing Suspensions'] = count(self::$aContinuingSuspensions);
	    $aReport['Processed']['Suspension Process Errors'] =  count(self::$aSuspensionExceptions);
	}

	if ($sReportType === self::REPORT_ALL)
	{
	    $aReport['Processed']['Scheduled Events'] = count(self::$aScheduledEventInstances);
	    $aReport['Processed']['Completed Events'] = count(self::$aCompletedEventInstances);
	     $aReport['Processed']['Exit Collections'] = count(self::$aExitCollections);
	    $aReport['Processed']['Failed Events'] = count(self::$aFailedEventInstances);
	    $aReport['Ongoing']['No Next Event'] = count(self::$aNoNextEventScheduled);

	}

	return $aReport;
    }




    public static function getBrokenPromises() {
	$aResult = array();
	foreach(self::$aCompletedPromises as $oPromise)
	{
	    if ($oPromise->collection_promise_completion_id === COLLECTION_PROMISE_COMPLETION_BROKEN)
		    $aResult[] = $oPromise;
	}

	return $aResult;
    }

    public static function getFulfilledPromises()
    {
	$aResult = array();
	foreach(self::$aCompletedPromises as $oPromise)
	{
	    if ($oPromise->collection_promise_completion_id === COLLECTION_PROMISE_COMPLETION_KEPT)
		    $aResult[] = $oPromise;
	}

	return $aResult;
    }

    public static function generateReport($sFilePath, $sFileFormat, $sReportType = self::REPORT_ALL)
    {
	$oSpreadsheet = new Logic_Spreadsheet(array());
	 $oSpreadsheet->addRecord(array("Process Invocation: ", self::$sInvocationType));
	  $oSpreadsheet->addRecord(array(" "));
	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_PROMISES)
	{
	    $oSpreadsheet->addRecord(array("Promises Batch Process"));
	    if (self::hasDataToReport(self::REPORT_PROMISES))
	    {
		$oSpreadsheet->addRecord(array("Completed Promises"));
		if (count(self::$aCompletedPromises)>0)
		{
		    
		    $oSpreadsheet->addRecord(array("Account ID", "Promise ID", "Completion Reason"));
		    foreach(self::$aCompletedPromises as $oPromise)
		    {
			$sReason = Collection_Promise_Completion::getForId($oPromise->collection_promise_completion_id)->name;
			$oSpreadsheet->addRecord(array($oPromise->account_id,$oPromise->id,  $sReason));
		    }

		}
		else
		{
		    $oSpreadsheet->addRecord(array("No Promises were completed."));
		}

		$oSpreadsheet->addRecord(array(" "));
		 $oSpreadsheet->addRecord(array("Ongoing Promises"));
		if (count(self::$aContinuingPromises)>0)
		{

		   
		    $oSpreadsheet->addRecord(array("Account ID", "Promise ID"));
		    foreach(self::$aContinuingPromises as $oPromise)
		    {
			$sReason = Collection_Promise_Completion::getForId($oPromise->collection_promise_completion_id)->name;
			$oSpreadsheet->addRecord(array($oPromise->account_id,$oPromise->id));
		    }

		}
		else
		{
		     $oSpreadsheet->addRecord(array("There are no ongoing Promises"));
		}

		 $oSpreadsheet->addRecord(array(" "));

		if (count(self::$aPromiseExceptions)>0)
		{
		     $oSpreadsheet->addRecord(array("Failed Promises (these were excluded from the batch collections process)"));
		     $oSpreadsheet->addRecord(array("Account ID","Promise ID",  "Failure Reason"));
		     foreach(self::$aPromiseExceptions as $oPromise)
		     {
			 $sReason = $oPromise->getException()->getMessage();
			$oSpreadsheet->addRecord(array( $oPromise->account_id,$oPromise->id, $sReason));
		     }

		}

	    }
	    else
	    {
		$oSpreadsheet->addRecord(array("No active Promises were found."));
	    }
	}

	$oSpreadsheet->addRecord(array(" "));

	if ($sReportType === self::REPORT_ALL || $sReportType === self::REPORT_SUSPENSIONS)
	{
	    $oSpreadsheet->addRecord(array("Suspensions Batch Process"));
	    if (self::hasDataToReport(self::REPORT_SUSPENSIONS))
	    {
		$oSpreadsheet->addRecord(array("Ended Suspensions"));
		if (count(self::$aEndedSuspensions)>0)
		{
		    
		    $oSpreadsheet->addRecord(array("Account ID",  "Suspension ID", "Completion Reason"));
		    foreach(self::$aEndedSuspensions as $oSuspension)
		    {

			$oSpreadsheet->addRecord(array($oSuspension->account_id, $oSuspension->id,  "Expired"));
		    }

		}
		else
		{
		    $oSpreadsheet->addRecord(array("No suspensions were ended."));
		}

		 $oSpreadsheet->addRecord(array(" "));

		 $oSpreadsheet->addRecord(array("Continuing Suspensions"));
		 if (count(self::$aContinuingSuspensions)>0)
		{
		    
		     $oSpreadsheet->addRecord(array("Account ID","Suspension ID"));
		     foreach(self::$aContinuingSuspensions as $oSuspension)
		     {

			$oSpreadsheet->addRecord(array( $oSuspension->account_id, $oSuspension->id));
		     }

		}
		else
		{
		     $oSpreadsheet->addRecord(array("There are no continuing suspensions."));
		}

		 $oSpreadsheet->addRecord(array(" "));

		if (count(self::$aSuspensionExceptions)>0)
		{
		     $oSpreadsheet->addRecord(array("Failed Suspensions (these were excluded from the batch collections process)"));
		     $oSpreadsheet->addRecord(array("Account ID","Suspension ID",  "Failure Reason"));
		     foreach(self::$aSuspensionExceptions as $oSuspension)
		     {
			 $sReason = $oSuspension->getException()->getMessage();
			$oSpreadsheet->addRecord(array( $oSuspension->account_id, $oSuspension->id, $sReason));
		     }

		}
	    }
	    else
	    {
		$oSpreadsheet->addRecord(array("No active Collections Suspensions were found."));
	    }
	}

	$oSpreadsheet->addRecord(array(" "));

	if ($sReportType === self::REPORT_ALL || $sReportType = self::REPORT_ACCOUNTS)
	{
	    $oSpreadsheet->addRecord(array("Accounts Batch Collections Process"));

	    if (self::hasDataToReport(self::REPORT_ACCOUNTS))
	    {
		$oSpreadsheet->addRecord(array("Scheduled Events"));
		if (count(self::$aScheduledEventInstances)> 0)
		{
		    
		    $oSpreadsheet->addRecord(array("Account ID", "Event History ID",  "Event Name"));
		    foreach (self::$aScheduledEventInstances as $oInstance)
		    {
			$oSpreadsheet->addRecord(array($oInstance->account_id, $oInstance->id,  $oInstance->getEventName()));
		    }
		}
		else
		{
		    $oSpreadsheet->addRecord(array("No events were scheduled."));
		}

		$oSpreadsheet->addRecord(array("Completed Events"));
		if (count(self::$aCompletedEventInstances)> 0)
		{
		    
		    $oSpreadsheet->addRecord(array("Account ID", "Event History ID",  "Event Name"));
		    foreach (self::$aCompletedEventInstances as $oInstance)
		    {
			$oSpreadsheet->addRecord(array($oInstance->account_id, $oInstance->id,  $oInstance->getEventName()));
		    }
		}
		else
		{
		    $oSpreadsheet->addRecord(array("No events were completed."));
		}

		$oSpreadsheet->addRecord(array(" "));

		$oSpreadsheet->addRecord(array("Exit Collections"));
		if (count(self::$aExitCollections)> 0)
		{
		    
		    $oSpreadsheet->addRecord(array("Account ID", "Event History ID"));
		    foreach (self::$aExitCollections as $oInstance)
		    {
			$oSpreadsheet->addRecord(array($oInstance->account_id, $oInstance->id));
		    }
		}
		else
		{
		    $oSpreadsheet->addRecord(array("No Accounts exited Collections"));
		}

		$oSpreadsheet->addRecord(array(" "));

		if (count(self::$aFailedEventInstances)> 0)
		{
		   $oSpreadsheet->addRecord(array("Failed Events"));
		   $oSpreadsheet->addRecord(array("Account ID", "Event History ID",  "Event Name", "Failure Reason"));
		   foreach (self::$aFailedEventInstances as $oInstance)
		   {
			$oSpreadsheet->addRecord(array( $oInstance->account_id,$oInstance->id, $oInstance->getEventName(), $oInstance->getException()->getMessage()));
		   }

		}

		$oSpreadsheet->addRecord(array("Accounts for which a next event was not triggered."));
		if (count(self::$aNoNextEventScheduled) > 0 )
		{
		   
		   $oSpreadsheet->addRecord(array("Account ID", "Reason"));
		   foreach (self::$aNoNextEventScheduled as $oAccount)
		   {
		       $oException = $oAccount->getException();
		       $sReason = $oException!== null ? $oException->getMessage() : ($oAccount->previousEventNotCompleted() ? "Previous event still awaiting manual completion" : "Day offset since last event did not result in next event.");
		       $oSpreadsheet->addRecord(array($oAccount->Id,  $sReason));
		   }
		}
		else
		{
		    $oSpreadsheet->addRecord(array("There are no accounts in collections for which a next event was not triggered."));
		}
		$oSpreadsheet->addRecord(array(" "));
	    }
	    else
	    {
		 $oSpreadsheet->addRecord(array("There are no accounts that need to have a collections event triggered."));
	    }
	}
	$oSpreadsheet->addRecord(array(" "));
	if (count( self::$aExceptions) > 0 )
	{
	   $oSpreadsheet->addRecord(array("Exceptions"));
	   $oSpreadsheet->addRecord(array("Exception Reason"));
	   foreach (self::$aExceptions as $oException)
	   {
		$oSpreadsheet->addRecord(array($oException->getMessage()));
	   }
	}

	$oSpreadsheet->saveAs( $sFilePath, $sFileFormat);


    }

    public static function emailReport($sReportType = self::REPORT_ALL)
    {
	$oEmail = Correspondence_Email::getForEmailNotificationSystemName(LATE_NOTICE_LIST);
	$oEmail->setSubject($sReportType);
	$body = $oEmail->getBody();
	$oEmail->addTextHeader(3, $sReportType." (Process invocation: ".self::$sInvocationType.")");
	$sMessage;
	if (self::hasDataToReport($sReportType))
	{
	    $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
	    $sFilename	= "Collections_BatchProcess_Report_$sTimeStamp.csv";
	    self::generateReport($sPath.$sFilename, "CSV", $sReportType);
	    $sFile = file_get_contents($sPath.$sFilename);
	    $oEmail->addAttachment($sFile, $sFilename, 'text/csv');
	    $oEmail->addTextHeader(4, "Report Summary (see attached csv for full report):");
	    $table =& $oEmail->setTable();
	    $aSummary = self::newGenerateSummaryReport($sReportType);
	    $oEmail->addMultiPivotTableHeader(array('Processed', '', 'Ongoing', ''));
	    $aOngoing = array_keys($aSummary['Ongoing']);
	    $aProcessed = array_keys($aSummary['Processed']);
	    $imaxLength = count($aOngoing) > count ($aProcessed) ? count($aOngoing) : count($aProcessed);
	    for ($i=0;$i<$imaxLength;$i++)
	    {
		$aRow = array();

		if ($i<count($aProcessed))
		{
		    $aRow[] = $aProcessed[$i];
		    $aRow[] = $aSummary['Processed'][$aProcessed[$i]];
		}
		else
		{
		    $aRow[] = "";
		    $aRow[] = "";
		}


		if ($i<count($aOngoing))
		{
		    $aRow[] = $aOngoing[$i];
		    $aRow[] = $aSummary['Ongoing'][$aOngoing[$i]];
		}
		else
		{
		    $aRow[] = "";
		    $aRow[] = "";
		}

		
		$oEmail->addMultiPivotTableRow($aRow);
	    }

	   // foreach ($aSummary as $sColumn => $mValue)
	   // {
	//	    $oEmail->addPivotTableRow($sColumn, $mValue);
	    //}
	}
	else
	{
	    $sMessage = $sReportType == self::REPORT_ALL ? "There were no active promises, suspensions or accounts to process." : ($sReportType == self::REPORT_PROMISES ? "There were no active promises to process" : "There are no active suspensions to process");
	    $oEmail->addTextHeader(4,$sMessage );
	}

	$oEmployee = Employee::getForId(Flex::getUserId());
	if ($oEmployee!= null && $oEmployee->email!=null)
	    $oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

	$oEmail->appendSignature();
	$oEmail->setBodyHTML();
	$oEmail->send();
    }


}
?>
