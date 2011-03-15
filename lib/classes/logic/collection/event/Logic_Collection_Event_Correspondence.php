<?php
/**
 * Description of Collection_Logic_Event_Report
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Correspondence extends Logic_Collection_Event
{
    protected $oDO;  

    public function __construct($mDefinition)
    {
        
         if ($mDefinition instanceof Logic_Collection_Event_Instance)
        {
           $this->oCollectionEventInstance = $mDefinition;
           $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
           $this->oDO = Collection_Event_Correspondence::getForCollectionEventId($this->oParentDO->id);
        }
//        else if (get_class($mDefinition) == 'Collection_Event_Correspondence')
//        {
//           $this->oDO = $mDefinition;
//           // TODO: Implement further
//        }
//        else if (get_class($mDefinition) == 'Collection_Event')
//        {
//           $this->oParentDO	= $mDefinition;
//           $this->oDO 		= Collection_Event_Correspondence::getForCollectionEventId($this->oParentDO->id);
//        }
        else
        {
           throw new Exception('bad parameter passed into Collection_Logic_Event_Correspondence constructor');
        }
    }

    protected function _invoke($aParameters = null)
    {

    }

    public function __get($sField)
    {
        return $this->oDO->$sField;
    }

    private static function buildXML($iDocumentTemplateId, $aEventInstances)
    {
        //this mapping is obsolete, but the automatic invoice action is for the moment still used to determine the date by which late fees are waived if payment is received by it.
        $aNoticeTypes	= 	array(
                                        DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER 	=> AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER,
                                        DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE 		=> AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE,
                                        DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE 	=> AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE,
                                        DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND 		=> AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND,
                                     );
        // Append a backslash to the path, if it doesn't already end in one
        $strBasePath = FILES_BASE_PATH;
        if (substr($strBasePath, -1) != "/")
        {
                $strBasePath .= "/";
        }
        $iActionType = $aNoticeTypes[$iDocumentTemplateId];
        $aAccounts = array();

        $intEffectiveDate =  time();

        foreach ($aEventInstances as $oEventInstance)
        {
            $aAccounts[] = $oEventInstance->getEvent()->getAccount()->id;
        }

        $aAccounts = Account::getAccountDataForLateNotice($aAccounts);
        foreach ($aAccounts as $aAccount)
        {
           $oAccount = Logic_Account::getForId($aAccount['AccountId']);
            //first set the following data members on $aAccount:  'InvoiceId', 'OutstandingNotOverdue', 'Overdue',  'TotalOutstanding'
            $aAccount['InvoiceId'] = $oAccount->getSourceCollectable()->invoice_id;
            $aAccount['Overdue'] = $oAccount->getOverdueCollectableBalance();
            $aAccount['TotalOutstanding'] = $oAccount->getCollectableBalance();
            $aAccount['OutstandingNotOverdue'] = $aAccount['TotalOutstanding'] -  $aAccount['Overdue'];


            $mxdSuccess = BuildLatePaymentNotice($iDocumentTemplateId, $aAccount, $strBasePath, $intEffectiveDate, $iActionType);


            if ($mxdSuccess !== NULL)
            {
                    if ($mxdSuccess !== FALSE)
                    {
                            $arrGeneratedNotices['Successful'] += 1;
                            $i = count($arrGeneratedNotices['Details']);
                            $arrGeneratedNotices['Details'][$i]['Account'] = $aAccount;
                            $arrGeneratedNotices['Details'][$i]['XMLFilePath'] = $mxdSuccess;
                            $bolSuccess = TRUE;
                    }
                    else
                    {
                            $arrGeneratedNotices['Failed'] += 1;
                            $bolSuccess = FALSE;
                    }


            }



        }




		return $arrGeneratedNotices;

    }
    
    public static function complete($aEventInstances)
    {
        
        
        $oEventInstance = $aEventInstances[0];
        $oEventObject = self::getForEventInstance($oEventInstance);
        $iTemplateId =  $oEventObject->correspondence_template_id;
        $oTemplate	= Correspondence_Logic_Template::getForId($iTemplateId);
        $iDocumentTemplateId = $oEventObject->document_template_type_id;
        $aGeneratedPDFs;
        $iNow					= time();
        $sPathDate				= date('Ymd', $iNow);
        $aCorrespondence = array();
        $bPreprinted = false;

        if ($iDocumentTemplateId != null)
        {
            if ($oTemplate->getSourceType() == CORRESPONDENCE_SOURCE_TYPE_SQL_ACCOUNTS)
                    throw new Exception("Event Configuration Error: 'SQL ACCOUNTS' Source Type cannot have an associated document template");
            $bPreprinted = true;
            $aGeneratedXML = self::buildXML($iDocumentTemplateId, $aEventInstances);
            $sLetterType	= GetConstantDescription($iDocumentTemplateId, "DocumentTemplateType");
       
            $aSummary = array();
            // We now need to create correspondence for each of the notices that have been generated
            foreach( $aGeneratedXML['Details'] as $aDetails)
            {


                $iCustGrp 				= $aDetails['Account']['CustomerGroup'];
                $sCustGroupName			= $aDetails['Account']['CustomerGroupName'];
                $iAccountId 			= $aDetails['Account']['AccountId'];
                $sXMLFilePath 			= $aDetails['XMLFilePath'];
               // $iAutoInvoiceAction		= $aDetails['Account']['automatic_invoice_action'];
                $sLowerCustGroupName	= strtolower(str_replace(' ', '_', $sCustGroupName));
                $sLowerLetterType		= strtolower(str_replace(' ', '_', $sLetterType));


                // Build summary data
                if (!array_key_exists($sCustGroupName, $aSummary))
                {
                        $aSummary[$sCustGroupName]	= array();
                }

                // ... more summary setup
                if (!array_key_exists($sLetterType, $aSummary[$sCustGroupName]))
                {
                        $aSummary[$sCustGroupName][$sLetterType]['emails']				= array();
                        $aSummary[$sCustGroupName][$sLetterType]['prints']				= array();
                        $aSummary[$sCustGroupName][$sLetterType]['errors'] 				= array();
                        $aSummary[$sCustGroupName][$sLetterType]['output_directory']	= realpath(FILES_BASE_PATH) . '/' . $sLowerLetterType . '/' . 'pdf' . '/' . $sPathDate . '/' . $sLowerCustGroupName;
                }

                // We need to generate the pdf for the XML and save it to the
                // files/type/pdf/date/cust_group/account.pdf storage
                // Need to add a note of this to the email
                //$this->log("Generating print PDF $sLetterType for account ". $aDetails['Account']['AccountId']);
                $sPDFContent	= self::getPDFContent($iCustGrp, time(), $iDocumentTemplateId, $sXMLFilePath, 'PRINT');

                // Check the pdf content
                if (!$sPDFContent)
                {
                        // PDF generation failed.
                   //     $sError 	= $this->getCachedError();
                        $sMessage 	= "Failed to generate PDF $sLetterType for " . $iAccountId . "\n" . $sError;
                        $aSummary[$sCustGroupName][$sLetterType]['errors'][] = $sMessage;
                        $iErrors++;
                      //  $this->log($sMessage, TRUE);
                }
                else
                {
                        // We have a PDF, so we should store it for sending to the mail house (as correspondence)
                     //   $this->log("Storing PDF $sLetterType for account ". $iAccountId);

                        $sOutputDirectory	= $aSummary[$sCustGroupName][$sLetterType]['output_directory'];
                        if (!file_exists($sOutputDirectory))
                        {
                                // This ensures that the output directory exists by testing each directory in the hierarchy above (and including) the target directory
                                $aOutputDirectories	= explode('/', str_replace('\\', '/', $sOutputDirectory));
                                $sDirectory 		= '';
                                foreach($aOutputDirectories as $sSubDirectory)
                                {
                                        // If root directory on linux/unix
                                        if (!$sSubDirectory)
                                        {
                                                continue;
                                        }
                                        $sXdirectory	= $sDirectory . '/' . $sSubDirectory;
                                        if (!file_exists($sXdirectory))
                                        {
                                                $bOk	= @mkdir($sXdirectory);
                                                if (!$bOk)
                                                {
                                                       // $this->log("Failed to create directory for PDF output: $sXdirectory", TRUE);
                                                }
                                        }
                                        $sDirectory	= $sXdirectory . '/';
                                }
                                $sOutputDirectory	= realpath($sDirectory) . '/';
                        }
                        else
                        {
                                $sOutputDirectory	= realpath($sOutputDirectory) . '/';
                        }

                        $aSummary[$sCustGroupName][$sLetterType]['output_directory']	= $sOutputDirectory;

                        // Write the PDF file contents to storage
                        $sTargetFile	= $sOutputDirectory . $iAccountId . '.pdf';
                        $rFile			= @fopen($sTargetFile, 'w');
                        $bOk			= FALSE;
                        if ($rFile)
                        {
                                $bOk	= @fwrite($rFile, $sPDFContent);
                        }

                        if ($bOk === FALSE)
                        {
                                // Failed
                                $sMessage	= "Failed to write PDF $sLetterType for account $iAccountId to $sTargetFile.";
                                //$this->log($sMessage, TRUE);
                                $aSummary[$sCustGroupName][$sLetterType]['errors'][]	= $sMessage;
                        }
                        else
                        {
                                // PDF stored successfully
                                @fclose($rFile);

                                $aSummary[$sCustGroupName][$sLetterType]['pdfs'][]		= $sTargetFile;


                        }

                       // $this->log("Generating Correspondence Data for account ". $iAccountId);

                        // Cache the correspondence data for the notice
                        $aItem	= 	array(
                                                        'account_id'						=> $iAccountId,
                                                        'customer_group_id'					=> $iCustGrp,
                                                        'correspondence_delivery_method_id'	=> $sCorrespondenceDeliveryMethod,
                                                        'account_name'						=> $aDetails['Account']['BusinessName'],
                                                        'title'								=> $aDetails['Account']['Title'],
                                                        'first_name'						=> $aDetails['Account']['FirstName'],
                                                        'last_name'							=> $aDetails['Account']['LastName'],
                                                        'address_line_1'					=> $aDetails['Account']['AddressLine1'],
                                                        'address_line_2'					=> $aDetails['Account']['AddressLine2'],
                                                        'suburb'							=> $aDetails['Account']['Suburb'],
                                                        'postcode'							=> $aDetails['Account']['Postcode'],
                                                        'state'								=> $aDetails['Account']['State'],
                                                        'email'								=> $aDetails['Account']['Email'],
                                                        'mobile'							=> $aDetails['Account']['Mobile'],
                                                        'landline'							=> $aDetails['Account']['Landline'],
                                                        'pdf_file_path'						=> $sTargetFile,
                                                        'letter_type'						=> $iDocumentTemplateId
                                                );
                        $aCorrespondence[]	= $aItem;

                        // Update the summary information for the delivery method
                        $aSummary[$sCustGroupName][$sLetterType][$sSummaryDeliveryMethod][]	= $iAccountId;

                }

            }


        }
        else if ($oTemplate->getSourceType() == CORRESPONDENCE_SOURCE_TYPE_SQL_ACCOUNTS)
        {
           
            foreach ($aEventInstances as $oInstance)
            {
                $aCorrespondence[] = $oInstance->account_id;
            }
        }
        else
        {
            foreach ($aEventInstances as $oInstance)
            {
                $aCorrespondence[] = array('account_id' => $oInstance->account_id);
            }
        }   


        // Create the correspondence run
        $oRun	= $oTemplate->createRun($bPreprinted, $aCorrespondence);

        foreach ($aEventInstances as $oInstance)
        {
            $oInstance->complete();
        }

        
    }

    private static function getPDFContent($iCustGroupId, $sEffectiveDate, $iDocumentTypeId, $sPathToXMLFile, $sTargetMedia)
    {
            //$this->startErrorCatching();

            $fileContents	= file_get_contents($sPathToXMLFile);
            $oPDFTemplate	= new Flex_Pdf_Template($iCustGroupId, $sEffectiveDate, $iDocumentTypeId, $fileContents, $sTargetMedia, TRUE);
            $oPDF			= $oPDFTemplate->createDocument();
            $oPDFTemplate->destroy();
            $oPDF			= $oPDF->render();

            //if ($this->getCachedError())
            //{
            //	return FALSE;
            //}

            return $oPDF;
    }
}
?>
