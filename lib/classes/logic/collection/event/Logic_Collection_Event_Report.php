<?php
/**
 * Description of Collection_Logic_Event_Report
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Report extends Logic_Collection_Event
{
    protected $oDO;
    

    public function __construct($mDefinition)
    {
       
         if ($mDefinition instanceof Logic_Collection_Event_Instance)
        {
           $this->oCollectionEventInstance = $mDefinition;
           $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
           $this->oDO = Collection_Event_Report::getForCollectionEventId($this->oParentDO->id);
        }
		else if (is_numeric($mDefinition))
		{
			$this->oParentDO = Collection_Event::getForId($mDefinition);
			$this->oDO = Collection_Event_Report::getForCollectionEventId($this->oParentDO->id);
		}
        else
        {
           throw new Exception('bad parameter passed into Collection_Logic_Event_Report constructor');
        }
    }

    public function getFileExtension()
    {
        $oOutputOrm = Collection_Event_Report_Output::getForId($this->collection_event_report_output_id);
        $oFileTypeOrm = File_Type::getForId($oOutputOrm->file_type_id);
        return $oFileTypeOrm->extension;

    }

    public function getReportOutPutId()
    {
        return $this->collection_event_report_output_id;
    }

    public function getMimeType()
    {
        $oOutputOrm = Collection_Event_Report_Output::getForId($this->collection_event_report_output_id);
        $oFileTypeOrm = File_Type::getForId($oOutputOrm->file_type_id);
        return $oFileTypeOrm->getPreferredMIMEType();
    }

    protected function _invoke($aParameters = null)
    {

    }

     public function __get($sField)
    {
        switch ($sField)
		{
			case 'name':
			case 'collection_event_type_id':
				return $this->oParentDO->$sField;
			default:
				return $this->oDO->$sField;
		}
    }
    
    public static function complete($aEventInstances)
    {
        //get the sql for the report
        $oEventInstance = $aEventInstances[0];
	$sEventName = $oEventInstance->getEventName();
        $oEventObject = self::getForEventInstance($oEventInstance);
        $sSql =  $oEventObject->report_sql;
        $iEmailNotification = $oEventObject->email_notification_id;
        $sFileExtension = $oEventObject->getFileExtension();
        $iReportOutput = $oEventObject->getReportOutPutId();
        $sFileType = $iReportOutput==COLLECTION_EVENT_REPORT_OUTPUT_CSV ? 'CSV' : ($iReportOutput==COLLECTION_EVENT_REPORT_OUTPUT_EXCEL ? 'Excel5' : 'Excel2007');
        $sMimeType = $oEventObject->getMimeType()->mime_content_type;
         $aAccountIds = array();
        foreach ($aEventInstances as $oEventInstance)
        {
           
            $aAccountIds[] = $oEventInstance->account_id;
        }

        $sSql = str_replace("<ACCOUNTS>", implode(",", $aAccountIds), $sSql);
        $oQuery = new Query();
        $mResult = $oQuery->Execute($sSql);
        $aResult = array();
        if ($mResult)
        {
            while ($aRecord = $mResult->fetch_assoc())
            {
               $aResult[] = $aRecord;
            }
        }
//
//       $aResult = array(
//                                array(
//                                    'Account|int'=>'1000008822',
//                                    'FNN|fnn'   =>'0405768976',
//                                    'Amount|currency'=>234.44,
//                                    'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008822',
//                                    'Whatever'=>'blah'
//                                ),
//                                array(
//                                    'Account|int'=>'1000008751',
//                                    'FNN|fnn'   =>'0405768976',
//                                    'Amount|currency'=>234.44,
//                                    'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008751',
//                                    'Whatever'=>'blah'
//                                )
//                            );
       /* if (count($aResult)>0)
        {
            $oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult, $sFileType);
            $sPath = FILES_BASE_PATH.'temp/';

            $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
            $sFilename	= "ReportsTest_$sTimeStamp.$sFileExtension";

            $oSpreadsheet->saveAs( $sPath.$sFilename, $sFileType);
            //send the email
            $sFile = file_get_contents($sPath.$sFilename);
            $oEmail	=  new Email_Notification($iEmailNotification);
            $oEmail->addAttachment($sFile, $sFilename, $sMimeType);
            //$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
            $oEmail->setSubject('Collections Report Test');
            $oEmail->setBodyText("Report Testing");
            $oEmployee = Employee::getForId(Flex::getUserId());
		if ($oEmployee!= null && $oEmployee->email!=null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
            $oEmail->send();
            
        }
*/
		$oEmail = Correspondence_Email::getForEmailNotificationSystemName(LATE_NOTICE_LIST);
	$oEmail->setSubject("$sEventName (Collection Event id $oEventInstance->collection_event_id)");
	$body = $oEmail->getBody();
	$oEmail->addTextHeader(3, $sEventName." (Collection Event id $oEventInstance->collection_event_id)");
	$sMessage;

	    $oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult, $sFileType);
            $sPath = FILES_BASE_PATH.'temp/';

            $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
            $sFilename	= "$sEventName"."_$sTimeStamp.$sFileExtension";

            $oSpreadsheet->saveAs( $sPath.$sFilename, $sFileType);
            //send the email
            $sFile = file_get_contents($sPath.$sFilename);
	   $oEmail->addAttachment($sFile, $sFilename, $sMimeType);
	    $oEmail->addTextHeader(4, "Please find the report attached. Summary: ");
	    $table =& $oEmail->setTable();
	    $oEmail->addPivotTableRow("Number of Rows", $oSpreadsheet->getRowCount());



	$oEmployee = Employee::getForId(Flex::getUserId());
	if ($oEmployee!= null && $oEmployee->email!=null)
	    $oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

	$oEmail->appendSignature();
	$oEmail->setBodyHTML();
	$oEmail->send();
        
        foreach ($aEventInstances as $oInstance)
        {
            $oInstance->complete();
        }
        
    }

    public static function emailReport($sEventName)
    {
	$oEmail = Correspondence_Email::getForEmailNotificationSystemName(LATE_NOTICE_LIST);
	$oEmail->setSubject("Report for Collection Event '$sEventName'");
	$body = $oEmail->getBody();
	$oEmail->addTextHeader(3, $sEventName." Report");
	$sMessage;

	    $oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult, $sFileType);
            $sPath = FILES_BASE_PATH.'temp/';

            $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
            $sFilename	= "$sEventName"."_$sTimeStamp.$sFileExtension";

            $oSpreadsheet->saveAs( $sPath.$sFilename, $sFileType);
            //send the email
            $sFile = file_get_contents($sPath.$sFilename);
	   $oEmail->addAttachment($sFile, $sFilename, $sMimeType);
	    $oEmail->addTextHeader(4, "Please find the report attached. Summary: ");
	    $table =& $oEmail->setTable();
	    $oEmail->addPivotTableRow("Number of Rows", $oSpreadsheet->getRowCount());



	$oEmployee = Employee::getForId(Flex::getUserId());
	if ($oEmployee!= null && $oEmployee->email!=null)
	    $oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);

	$oEmail->appendSignature();
	$oEmail->setBodyHTML();
	$oEmail->send();
    }
}
?>
