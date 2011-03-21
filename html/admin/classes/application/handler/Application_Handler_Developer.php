<?php
class Application_Handler_Developer extends Application_Handler
{
	const	URL_TYPE_JS		= 'onclick';
	const	URL_TYPE_HREF	= 'href';
	
	// View the Developer Page
	public function ViewList($subPath)
	{
		$bolIsGOD	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Build list of Developer Functions
			$arrFunctions	= array();
			
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Operation-based Permission Tests',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_OperationPermission();'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'AJAX Dataset & Pagination Test (Cached)',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_DatasetPagination(1);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'AJAX Dataset & Pagination Test (Uncached)',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_DatasetPagination(0);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Tab Control',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_TabGroup();'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Datepicker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_slider.js", "reflex_slider_handle.js", "reflex_date_picker.js"], function(){var oDatePicker = new Reflex_Date_Picker(); oDatePicker.show();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'FX',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["developer_animation.js"], function(){var oPopup = new Developer_Animation(25); oPopup.setContent("<div style=\\"margin: 2.5em;\\">Magical animated Popup!</div>"); oPopup.addCloseButton(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Tree',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_style.js", "reflex_fx_reveal.js", "reflex_control.js", "reflex_control_tree.js", "reflex_control_tree_node.js", "reflex_control_tree_node_root.js", "developer_tree.js"], function(){var oPopup = new Developer_Tree(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Ticker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_control.js", "reflex_control_ticker.js", "developer_ticker.js"], function(){var oPopup = new Developer_Ticker(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Old Date Time Picker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["component_date_picker.js", "developer_old_date_picker.js"], function(){var oPopup = new Developer_Old_Date_Picker();}, true);'
																)
													);
			$aScripts	= array	(
									'reflex_control.js',
									'reflex_control_textfield.js',
									//'reflex_control_textarea.js',
									//'reflex_control_fieldset.js',
									'developer_controls.js',
								);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Form Controls',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["'.implode('","', $aScripts).'"], function(){var oPopup = new Developer_Ticker(); oPopup.display();}, true);'
																)
													);
			
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Destination Import',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["popup_destination_import.js","popup_destination_import_manual.js","control_field.js","control_field_select.js","control_field_text_ajax.js","filter.js"], function(){(new Popup_Destination_Import()).display()}, true);'
																)
													);
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['arrFunctions']	= $arrFunctions;
			
			$this->LoadPage('developer_console', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	public function MatchDestinationsFromCSV($subPath)
	{
		//require_once(dirname(__FILE__).'/../../json/handler/JSON_Handler_Destination.php');
		
		try
		{
			if (($sFileContents = @file_get_contents(ini_get('upload_tmp_dir').'/'.$_FILES['destinations']['tmp_name'])) === false)
			{
				throw new Exception("There was an error while reading the uploaded file (".$php_errormsg.")");
			}
			$aIgnoreWords	= preg_split('/[\s\,\;\|]+/', $_REQUEST['ignore_words'], null, PREG_SPLIT_NO_EMPTY);
			
			$aResult	= JSON_Handler_Destination::matchDestinationsCSV($sFileContents, ($aIgnoreWords) ? $aIgnoreWords : array());
		}
		catch (Exception $oException)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.';
			$aResult	= array(
							'Success'	=> false,
							'sMessage'	=> $sMessage,
							'Message'	=> $sMessage
						);
		}
		$sJSONResponse	= @JSON_Services::instance()->encode($aResult);
		
		if ($sJSONResponse === false)
		{
			echo "Error producing JSON output: ".$php_errormsg;
		}
		elseif (PEAR::isError($sJSONResponse))
		{
			echo "PEAR Error:\n";
			echo print_r($sJSONResponse, true);
		}
		else
		{
			//echo "Debug:\n";
			//echo print_r($sJSONResponse, true);
			echo $sJSONResponse;
		}
		
		die;
	}
	
	protected static function _stdClassFactory($arrProperties)
	{
		$objStdClass	= new stdClass();
		
		foreach ($arrProperties as $strName=>$mixValue)
		{
			$objStdClass->{$strName}	= $mixValue;
		}
		
		return $objStdClass;
	}
	
	// 
	// COLLECTIONS LOGIC TESTING
	//
	public function logMessage($sMessage, $bolAddNewLine=true)
	{
		echo "{$sMessage}<br/>";
	}

        public function phpExcelRead()
        {
            require_once $_SERVER['DOCUMENT_ROOT'].'/../lib/PHPExcel/Classes/PHPExcel.php';
            $objReader = new PHPExcel_Reader_Excel2007();
            
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load( FILES_BASE_PATH.'temp/CollectionsReport-5.xlsx');
            $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
            $sheet = $objPHPExcel->getActiveSheet();
            $sStyle = $sheet->getStyleByColumnAndRow("A", 2);
            $oStyle3 =  $sheet->getStyleByColumnAndRow("A", 3);
            foreach($rowIterator as $row)
            {

                $rowIndex = $row->getRowIndex ();
                $array_data[$rowIndex] = array('A'=>'', 'B'=>'','C'=>'','D'=>'');

                $cell = $sheet->getCell('A' . $rowIndex);
                $array_data[$rowIndex]['A'] = $cell->getCalculatedValue();
                $cell = $sheet->getCell('B' . $rowIndex);
                $array_data[$rowIndex]['B'] = $cell->getCalculatedValue();
                $cell = $sheet->getCell('C' . $rowIndex);
                $array_data[$rowIndex]['C'] = PHPExcel_Style_NumberFormat::toFormattedString($cell->getCalculatedValue(), 'YYYY-MM-DD');
                $cell = $sheet->getCell('D' . $rowIndex);
                $array_data[$rowIndex]['D'] = $cell->getCalculatedValue();
            }
        }

        public function testAccountsQuery()
        {
            Account::getForBalanceRedistribution();
            die;
        }

        public function balanceRedistribution()
        {
            
            Log::registerFunctionLog('Developer_Balance_Redistribute', 'logMessage', 'Application_Handler_Developer');
            Log::setDefaultLog('Developer_Balance_Redistribute');
            $oDataAccess	= DataAccess::getDataAccess();
            
            //if (!$oDataAccess->TransactionStart())
           // {
           //     throw new Exception("transcaction failed to start.");
          //  }
            try
            {
                $aAccounts = Account::getForBalanceRedistribution(1000005012);
		foreach ($aAccounts as $oAccount)
		{
		    Log::getLog()->log($oAccount->Id);
		}
		
		Logic_Account::batchRedistributeBalances($aAccounts);
              // $oDataAccess->TransactionRollback();
            }
            catch(Exception $e)
            {
                throw $e;
              //  $oDataAccess->TransactionRollback();
            }
            die;
        }

	public function dispatcherLoadTest()
	{
	    Correspondence_Logic_Run::sendWaitingRuns();
	    die;
	}

        public function getPayablesTest()
        {
            $oAccount = Account::getForId(1000005195);
            $aPayables = $oAccount->getPayables();
            die;
        }

	public function getMostRecentEventTest()
	{
	    $oAccount = Account::getForId(1000160104);
	    $oEvent = Logic_Collection_Event_Instance::getMostRecentForAccount($oAccount, ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED);
	    echo $oEvent->getEventName();
	    $oEvent = Logic_Collection_Event_Instance::getMostRecentForAccount($oAccount);
	    echo $oEvent->getEventName();
	    die;
	}

	public function getScenariosTest() 
	{
	    $aScenarios =  Logic_Collection_Scenario_Instance::getForAccount(Logic_Account::getInstance(1000005195), FALSE);
	    $x=5;
	    die;
	}

    
	public function OCATest()
	{
	    Log::registerFunctionLog('Developer_OCATest', 'logMessage', 'Application_Handler_Developer');
		    Log::setDefaultLog('Developer_OCATest');
	    $aAccountOCAReferrals = Account_OCA_Referral::getAll();
	    $oResourceType = Resource_Type_File_Export_OCA_Referral::exportOCAReferrals(array_keys($aAccountOCAReferrals));
	    die;
	}

    public function CollectionsLogic()
	{
		Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
		Log::setDefaultLog('Developer_CollectionsLogic');
                try
			{
                            $oDataAccess = DataAccess::getDataAccess();
                            $oDataAccess->TransactionStart();


                            $iAccountsBatchProcessIteration = 1;                           
                           
                            try
                            {
                                $aPromises =  Logic_Collection_Promise::getActivePromises();
                               // Logic_Collection_Promise::batchProcess($aPromises);
                            }
                            catch (Exception $e)
                            {
                                 Logic_Collection_BatchProcess_Report::addException($e);
                                if ($e instanceof Exception_Database)
                                {
                                    throw $e;
                                }
                            }

                            try
                            {
                                $aActiveSuspensions = Collection_Suspension::getActive();
                              //  Logic_Collection_Suspension::batchProcess($aActiveSuspensions);
                            }
                            catch(Exception $e)
                            {
                                if ($e instanceof Exception_Database)
                                {
                                    throw $e;
                                }
                                else
                                {
                                    Logic_Collection_BatchProcess_Report::addException($e);
                                }
                            }

                            try
                            {
                                 //Log::getLog()->log('&&&&&&&&& Accounts Batch Process Iteration '.$iAccountsBatchProcessIteration++.'  &&&&&&&&&&&&&');
                                $aExcludedAccounts = Logic_Collection_BatchProcess_Report::getAccountsWithExceptions();
                                $aAccounts = array(Logic_Account::getInstance(1000165623));//Logic_Account::getForBatchCollectionProcess($aExcludedAccounts);
                                $iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);

                                while ($iCompletedInstances > 0)
                                {
                                    //Log::getLog()->log('&&&&&&&&& Accounts Batch Process Iteration '.$iAccountsBatchProcessIteration++.'  &&&&&&&&&&&&&');
                                    $iCompletedInstances = Logic_Account::batchProcessCollections( $aAccounts);

                                }
                            }
                            catch (Exception $e)
                            {
                                Logic_Collection_BatchProcess_Report::addException($e);
                            }

                            throw new Exception("force rollback");
                                $oDataAccess->TransactionCommit();
                            
			}
			catch (Exception $e)
			{
                             $oDataAccess->TransactionRollback();
                             Log::getLog()->log($e->__toString());

			}

                try
                {
              


               
               $sPath = FILES_BASE_PATH.'temp/';

            $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
            $sFilename	= "Collections_BatchProcess_Report_$sTimeStamp.$sFileExtension.csv";
            Logic_Collection_BatchProcess_Report::generateReport($sPath.$sFilename, "CSV");
           
            //send the email
            $sFile = file_get_contents($sPath.$sFilename);
            $oEmail	=  new Email_Notification(1);
            $oEmail->addAttachment($sFile, $sFilename, 'text/csv');
            //$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
            $oEmail->setSubject('Collections Batch Process Report');
            $oEmail->setBodyText("Report Testing");
            $oEmployee = Employee::getForId(Flex::getUserId());
		if ($oEmployee!= null && $oEmployee->email!=null)
			$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
            $oEmail->send();

                }
                catch(Exception $e)
                {
                     $sPath = FILES_BASE_PATH.'temp/';

                    $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
                    $sFilename	= "Collections_BatchProcess_Report_$sTimeStamp.$sFileExtension.csv";
                    Logic_Collection_BatchProcess_Report::generateReport($sPath.$sFilename, "CSV");

                    //send the email
                    $sFile = file_get_contents($sPath.$sFilename);
                    $oEmail	=  new Email_Notification(1);
                    $oEmail->addAttachment($sFile, $sFilename, 'text/csv');
                    //$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
                    $oEmail->setSubject('Collections Batch Process Report');
                    $oEmail->setBodyText("Report Testing");
                    $oEmployee = Employee::getForId(Flex::getUserId());
                        if ($oEmployee!= null && $oEmployee->email!=null)
                                $oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
                    $oEmail->send();
                    throw $e;
                }
            
		exit;
	}

        public function excelTest()
        {

         
        require_once $_SERVER['DOCUMENT_ROOT'].'/../lib/PHPExcel/Classes/PHPExcel.php';
      

                $aResult = array(
                                    array(
                                        'Account|int'=>'1000008822',
                                        'FNN|fnn'   =>'0405768976',
                                        'Amount|currency'=>234.44,
                                        'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008822'
                                    ),
                                    array(
                                        'Account|int'=>'1000008751',
                                        'FNN|fnn'   =>'0405768976',
                                        'Amount|currency'=>234.44,
                                        'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008751'
                                    )
                            );

                $iNumRecs = count($aResult);
        if ($iNumRecs>0)
        {
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            $sheet = $objPHPExcel->getActiveSheet();
            $aRawColumns = array_keys($aResult[0]);
            $aColumns = array();
            $aColumnFormatting = array();

            foreach ($aRawColumns as $sColumn)
            {
                $aColumnParts = explode('|', $sColumn);
                $aColumns[] = $aColumnParts[0];
                if (count($aColumnParts)>1)
                {
                    $aFormat = explode('#', $aColumnParts[1]);
                    $aColumnFormatting[] = $aFormat[0];

                }
                else
                {
                    $aColumnFormatting[] = null;
                }
                 
            }
            $starting_pos = ord('A');
            $index_pos = 0;
            foreach($aColumns as $sColumn)
            {
                $mStyle = $aColumnFormatting[$index_pos];
                $iCol = chr($starting_pos+$index_pos);
                $iNumRows = $iNumRecs+1;
                $sRange = $iCol.'2:'."$iCol$iNumRows";
                $oStyle = $sheet->getStyle($sRange);
                
                switch ($mStyle) {
                    case 'int':
                        $oNumberFormat = $oStyle->getNumberFormat();
                         $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                       break;
                    case 'fnn':
                        $oNumberFormat = $oStyle->getNumberFormat();
                         $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                       break;
                   case 'currency':
                        $oNumberFormat = $oStyle->getNumberFormat();
                        $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                       break;
                   case 'url':
                       $oColor = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLUE);
                       $oFontFormat = $oStyle->getFont();
                       $oFontFormat->setColor($oColor);
                       $oFontFormat->setUnderline( PHPExcel_Style_Font::UNDERLINE_SINGLE);
                      
                            //$oFontFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    default:
                        break;
                }     


                $sheet->setCellValue(chr($starting_pos+$index_pos) . '1',$sColumn);
                $index_pos++;
            }


            $iRow = 2;
            foreach ($aResult as $aRecord)
            {
                $starting_pos = ord('A');
                $index_pos = 0;
                foreach ($aRecord as $sCol=>$mValue)
                {
                    $mStyle = $aColumnFormatting[$index_pos];
                    switch ($mStyle) {

                    case 'fnn':
                        $sheet->setCellValueExplicit(chr($starting_pos+$index_pos). $iRow, $mValue, PHPExcel_Cell_DataType::TYPE_STRING);
                       break;
                   case 'url':
                       $aColumnParts = explode("#", $sCol);
                       $sURL = str_replace ( "{".$aColumns[$index_pos]."}" , $mValue , $aColumnParts[1] );

                       $sheet->   setCellValue(chr($starting_pos+$index_pos) . $iRow,$mValue);
                       $sheet->getCell(chr($starting_pos+$index_pos) . $iRow)->getHyperlink()->setUrl( $sURL);
                     //  $sheet->getCell(chr($starting_pos+$index_pos) . $iRow)->getHyperlink()->setTooltip('View Account in Flex');

                    default:
                        $sheet->   setCellValue(chr($starting_pos+$index_pos) . $iRow,$mValue);
                        break;
                }

                    $index_pos++;
                    
                }
                $iRow++;
            }

//            $starting_pos = ord('A');
//            $index_pos = 0;
//            foreach($aColumns as $sColumn)
//            {
//                $iCol = chr($starting_pos+$index_pos);
//                $oStyle = $sheet->getStyle($iCol);
//                $oNumberFormat = $oStyle->getNumberFormat();
//                  $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
//                $index_pos++;
//            }




            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="CollectionsReport.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;

        }





	
	// 
	// END: COLLECTIONS LOGIC TESTING
	//	
}

        public function excelClassTest()
        {
            $aResult = array(
                            array(
                                'Account|int'=>'1000008822',
                                'FNN|fnn'   =>'0405768976',
                                'Amount|currency'=>234.44,
                                'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008822',
                                'Whatever'=>'blah'
                            ),
                            array(
                                'Account|int'=>'1000008751',
                                'FNN|fnn'   =>'0405768976',
                                'Amount|currency'=>234.44,
                                'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008751',
                                'Whatever'=>'blah'
                            )
                        );



            $iNumRecs = count($aResult);
            if ($iNumRecs>0)
            {          
             
                $oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult);
                // Redirect output to a client’s web browser (Excel2007)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="CollectionsReport.xlsx"');
                header('Cache-Control: max-age=0');
                $oSpreadsheet->save('php://output');

            }
            exit;

        }

         public function excelClassTestSaveAs()
        {
            $aResult = array(
                            array(
                                'Account|int'=>'1000008822',
                                'FNN|fnn'   =>'0405768976',
                                'Amount|currency'=>234.44,
                                'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008822'
                            ),
                            array(
                                'Account|int'=>'1000008751',
                                'FNN|fnn'   =>'0405768976',
                                'Amount|currency'=>234.44,
                                'Account|url#https://collections-reengineer.jvanderbreggen.ybs.net.au/admin/flex.php/Account/Overview/?Account.Id={Account}'=>'1000008751'
                            )
                        );



            $iNumRecs = count($aResult);
            if ($iNumRecs>0)
            {

                $oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult);
                // Redirect output to a client’s web browser (Excel2007)
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment;filename="CollectionsReport.csv"');
                header('Cache-Control: max-age=0');
                $oSpreadsheet->saveAs('php://output', 'CSV');

            }
            exit;

        }

        public function testGetFor()
        {
            $x = Collection_Event::getForType(1);
            foreach($x as $object)
            {
                print_r($object->toArray());
            }

            exit;
        }






	//
	// END: COLLECTIONS LOGIC TESTING
	//

}

?>