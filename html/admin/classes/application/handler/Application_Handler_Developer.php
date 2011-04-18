<?php
class Application_Handler_Developer extends Application_Handler
{
	const	URL_TYPE_JS		= 'onclick';
	const	URL_TYPE_HREF	= 'href';
	
	public function correspondence()
	{
		Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
		Log::setDefaultLog('Developer_CollectionsLogic');
		Correspondence_Logic_Run::sendWaitingRuns();
		die;

	}

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

			/*$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Manage Account Links',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(
																						["dataset_ajax.js", 
																						"filter.js", 
																						"control_field.js", 
																						"control_field_checkbox.js", 
																						"control_field_text_ajax.js", 
																						"control_field_select.js", 
																						"control_field_text.js",
																						"control_field_hidden.js",
																						"component_date_picker.js",
																						"control_field_date_picker.js",
																						"component_section.js",
																						"component_account_links.js", 
																						"component_account_merge_contacts_list.js", 
																						"component_account_merge_contacts.js"], 
																						function() {
																							var oPopup = Component_Account_Links.createAsPopup({iAccountId:1000182292,fnOnReady:function(){oPopup.display();}});
																						}, 
																						true
																					);'
																	//1000182292, 1000155184
																)
													);*/
			
			/*$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Account Activity Log',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["component_section.js","component_account_activity_log.js"], function(){var oPopup = Component_Account_Activity_Log.createAsPopup({iAccountId:1000154811, fnOnReady:function(){oPopup.display();}});}, true);'
																)
													);*/
			
			$arrFunctions[]	= self::_stdClassFactory(
				array (
					'strName'	=> 'Testing Interface',
					'strType'	=> self::URL_TYPE_JS,
					'strURL'	=> 'JsAutoLoader.loadScript(
										["control_field.js", 
										"control_field_checkbox.js", 
										"control_field_textarea.js",
										"control_field_number.js",
										"component_section.js", 
										"reflex_breadcrumb_select.js",
										"component_test_run.js"], 
										function() {
											var oPopup = Component_Test_Run.createAsPopup(
												{
													fnOnReady: function() {
														oPopup.display();
													}
												}
											);
										}, 
										true
									);'
				)
			);
			
			$arrFunctions[]	= self::_stdClassFactory(
				array (
					'strName'	=> 'Dataset AJAX Table Component Testing',
					'strType'	=> self::URL_TYPE_JS,
					'strURL'	=> 'JsAutoLoader.loadScript(
										["control_field.js", 
										"control_field_checkbox.js", 
										"control_field_text.js", 
										"control_field_select.js",
										"control_field_date_picker.js",
										"sort.js",
										"filter.js",
										"component_section.js",
										"reflex_loading_overlay.js",
										"customer_group.js",
										"component_dataset_ajax_table.js"], 
										function() {
											Component_Dataset_AJAX_Table.test();
										}, 
										true
									);'
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

		public function directDebit()
		{
			Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
			Log::setDefaultLog('Developer_CollectionsLogic');

			//$oDataAccess = DataAccess::getDataAccess();
			//$oDataAccess->TransactionStart();

			try
			{
				Log::getLog()->log("");
				Log::getLog()->log("Overdue Balance Direct Debits");
				Log::getLog()->log("");

				// Eligible for direct debits today, list the invoices that are eligible
				$oCollectionsConfig = Collections_Config::get();
				if (!$oCollectionsConfig || $oCollectionsConfig->direct_debit_due_date_offset === null)
				{
					throw new Exception("There is no direct debit due date offset configured in collections_config.");
				}

				// Get Account records
				Log::getLog()->log("Getting accounts that are eligible");
				$mResult = Query::run("	SELECT	i.Id,
												a.Id 					AS account_id,
												pt.direct_debit_minimum	AS direct_debit_minimum,
												bt.description 			AS billing_type_description,
												i.invoice_run_id 		AS latest_invoice_run_id
										FROM	Account a
										JOIN	Invoice i ON (
													i.Account = a.Id
													AND i.CreatedOn = (
														SELECT  MAX(CreatedOn)
														FROM    Invoice
														WHERE   Account = a.Id
														AND     NOW() > DATE_ADD(DueOn, INTERVAL {$oCollectionsConfig->direct_debit_due_date_offset} DAY)
													)
												)
										JOIN 	payment_terms pt ON (pt.customer_group_id = a.CustomerGroup)
										JOIN 	billing_type bt ON (bt.id = a.BillingType)
										WHERE	NOW() > DATE_ADD(DueOn, INTERVAL {$oCollectionsConfig->direct_debit_due_date_offset} DAY)
										AND 	a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
										AND 	pt.id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id = a.CustomerGroup)
										AND 	bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.") LIMIT 1;");

				Log::getLog()->log("Got accounts");
				while ($aRow = $mResult->fetch_assoc())
				{

					// Check if the account has already been processed, potentially useless
			// but is here just to be sure that accounts aren't charged more than once
			$iAccountId	= $aRow['account_id'];
			if ($aAccountsApplied[$iAccountId])
			{
				$iDoubleUpsCount++;
				Log::getLog()->log("Already applied to account {$iAccountId}");
				continue;
			}
			$aAccountsApplied[$iAccountId]	= true;

			// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
			$oAccount				= Account::getForId($aRow['account_id']);
			$oPaymentMethodDetail	= $oAccount->getPaymentMethodDetails();
			$bDirectDebitable		= false;
			$iPaymentType			= null;
			$sOriginIdType			= null;
			$mOriginId				= null;
			switch($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
					$oDirectDebit	= DirectDebit::getForId($oAccount->DirectDebit);
					if ($oDirectDebit)
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::BANK_ACCOUNT_NUMBER;
						$mOriginId			= $oPaymentMethodDetail->AccountNumber;
					}
					else
					{
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT]++;
					}
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
					$oCreditCard	= Credit_Card::getForId($oAccount->CreditCard);
					$sExpiry		= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01";
					$sCompareExpiry	= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01 + 1 month";
					$iExpiry		= strtotime($sCompareExpiry);
					$iNow			= time();
					if ($oCreditCard && ($iNow < $iExpiry))
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::CREDIT_CARD_NUMBER;
						$mOriginId			= Credit_Card::getMaskedCardNumber(Decrypt($oPaymentMethodDetail->CardNumber));
					}
					else if ($iNow >= $iExpiry)
					{
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$iAccountId} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					}
					else
					{
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}



					Log::getLog()->log("Account {$aRow['account_id']}");
						$fAmount	= 5555;
						//if ($fAmount < $aRow['direct_debit_minimum'])
						//{
							// Not enough of a balance to be eligible
						//	Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
					//		$aIneligible[Cli_App_Payments::DIRECT_DEBIT_INELIGIBLE_AMOUNT]++;
					///		continue;
					//	}

						// Create Payment (using origin id, payment type, account & amount)
						$oPayment =	Logic_Payment::factory(
										$iAccountId,
										$iPaymentType,
										$fAmount,
										PAYMENT_NATURE_PAYMENT,
										'',
										$sPaidOn,
										array(
											'aTransactionData' =>	array(
																		$sOriginIdType => $mOriginId
																	)
										)
									);

						// Create payment_request (linked to the payment & invoice run id)
						$oPaymentRequest	= 	Payment_Request::generatePending(
													$oAccount->Id, 					// Account id
													$iPaymentType,					// Payment type
													$fAmount,						// Amount
													$aRow['latest_invoice_run_id'],	// Invoice run id
													Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
													$oPayment->id					// Payment id
												);

						// Update the payments transaction reference (this done separately because the transaction reference
						// is derived from the payment request)
						$oPayment->transaction_reference = Payment_Request::generateTransactionReference($oPaymentRequest);
						$oPayment->save();

						// Distribute the payment
						$oPayment->distribute();

						Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}");

						$iAppliedCount++;
					}
					Log::getLog()->log("APPLIED: {$iAppliedCount}");
				Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));
				Log::getLog()->log("DOUBLE-UPS: {$iDoubleUpsCount} (This should always be zero)");
				

				

			}
			catch(Exception $e)
			{
				//$oDataAccess->TransactionRollback();
			}

			//$oDataAccess->TransactionRollback();
			die;
		}

        public function balanceRedistribution()
        {
            
            Log::registerFunctionLog('Developer_Balance_Redistribute', 'logMessage', 'Application_Handler_Developer');
            Log::setDefaultLog('Developer_Balance_Redistribute');
            $oDataAccess	= DataAccess::getDataAccess();
            
			
			try
			{
				$aAccounts = Account::getForBalanceRedistribution();
				Logic_Account::batchRedistributeBalances($aAccounts);

			foreach ($aAccounts as $oAccount)
			{
				Log::getLog()->log($oAccount->Id);
			}

			
			
			}
			catch(Exception $e)
			{
				throw $e;
			   // $oDataAccess->TransactionRollback();
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

	public function deleteTest($aParams)
	{

		Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
		Log::setDefaultLog('Developer_CollectionsLogic');
		$aAccounts = Account::getForBalanceRedistribution(Account::BALANCE_REDISTRIBUTION_FORCED);
		$oDataAccess = DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		$iCount = 0;
		$bQuick = $aParams[0] > 0 ? TRUE : FALSE;
		$sQuick = $bQuick ? NULL : "NOT";
		$fTotalTime = 0.0;
		Log::getLog()->log("Deleting {$sQuick} using QUICK");
		try
		{
			
			foreach ($aAccounts as $oAccount)
			{				
				Logic_Stopwatch::getInstance()->start();
				Collectable_Adjustment::deleteForAccount($oAccount->id, $bQuick);
				Collectable_Payment::deleteForAccount($oAccount->id, $bQuick);
				Collectable_Transfer_Balance::deleteForAccount($oAccount->id, $bQuick);
				$fTime = Logic_Stopwatch::getInstance()->split();
				$fTotalTime += $fTime;
				//Log::getLog()->log("Account {$oAccount->id} (".++$iCount."),".$fTime);
			}
			Log::getlog()->log("Processed ".count($aAccounts)." accounts");
			Log::getLog()->log("Total Time: {$fTotalTime}");
			Log::getLog()->log("Average Time: ".$fTotalTime/count($aAccounts));
		}
		catch(Exception $e)
		{
			 $oDataAccess->TransactionRollback();
		}

		$oDataAccess->TransactionRollback();
		die;
	}

	public function testFileName()
	{
		$sPDFFilePath =  "data/www/jvanderbreggen.ybs.net.au/files/friendly_reminder/pdf/20110330/telco_blue/1000009353.pdf";
		$sFileName = basename ( $sPDFFilePath );//substr ($sPDFFilePath , strrpos ( $sPDFFilePath, "/" )+1 );
		die;
	}

	public function testGetForRedistribute()
	{
		$aAccounts = Account::getForBalanceRedistribution(Account::BALANCE_REDISTRIBUTION_FORCED_INCLUDING_ARCHIVED, 1000180081);
		die;
	}

    public function CollectionsLogic()
	{
			Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
			Log::setDefaultLog('Developer_CollectionsLogic');
			try
			{
				$iAccountId = 1000006461;
				$aPromises;
				$aActiveSuspensions;
				$aAccounts;

				if ($iAccountId != NULL)
				{
					
					Log::getLog()->log("Processing Collections for Account $iAccountId, starting with balance redistribution for this account only.");

					//$aAccountsForRedistribution = Account::getForBalanceRedistribution();
					//Logic_Account::batchRedistributeBalances($aAccountsForRedistribution);
					
					Logic_Account::getInstance($iAccountId)->redistributeBalances();
					
					
				}
				else
				{
					Log::getLog()->log("Processing Collections in batch for all accounts, starting with balance redistribution for all accounts that need it.");
					$aAccountsForRedistribution = Account::getForBalanceRedistribution();
					Logic_Account::batchRedistributeBalances($aAccountsForRedistribution);
				}


				Logic_Account::clearCache();
				$iAccountsBatchProcessIteration = 1;

				if ($iAccountId !== null)
				{
					$oPromise =	Logic_Account::getInstance($iAccountId)->getActivePromise();
					$aPromises = $oPromise === null ? array() : array($oPromise);
					$oSuspension =	Collection_Suspension::getActiveForAccount($iAccountId);
					$aActiveSuspensions = $oSuspension === null ? array() : array($oSuspension);
					Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_ACCOUNT);
				}
				else
				{
					$aPromises =	Logic_Collection_Promise::getActivePromises();
					$aActiveSuspensions = Collection_Suspension::getActive();
					Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_BATCH);
				}

				Logic_Account::clearCache();

				try
				{
					Logic_Collection_Promise::batchProcess($aPromises);
				}
				catch (Exception $e)
				{
					Logic_Collection_BatchProcess_Report::addException($e);
					Log::getLog($e->__toString());

					if ($e instanceof Exception_Database)
					{
						throw $e;
					}
				}

				Logic_Account::clearCache();
				try
				{
					Logic_Collection_Suspension::batchProcess($aActiveSuspensions);

				}
				catch(Exception $e)
				{

					Logic_Collection_BatchProcess_Report::addException($e);

					if ($e instanceof Exception_Database)
					{
						throw $e;
					}
					Log::getLog()->log($e->__toString());
				}

				Logic_Account::clearCache();

				try
				{
					if (Collections_Schedule::getEligibility())
					{
						if ($iAccountId === null)
						{
							$aExcludedAccounts = Logic_Collection_BatchProcess_Report::getAccountsWithExceptions();
							$aAccounts = Logic_Account::getForBatchCollectionProcess($aExcludedAccounts);
						}
						else
						{

							$oAccount = Logic_Account::getInstance($iAccountId);
							$aAccounts = array($oAccount);
						}

						$iCompletedInstances = 0;

						Logic_Stopwatch::getInstance()->start();
						//Check if there are any uncompleted automated events left over from last time......
						Logic_Collection_Event_Instance::completeScheduledInstancesForAccounts($aAccounts);
						$iIteration = 1;
						$oDataAccess = DataAccess::getDataAccess();
						$oDataAccess->TransactionStart();
						do
						{
							Log::getLog()->log("About to process ".count($aAccounts)." In Batch.");
							$iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
							Log::getlog()->log("Completed Scheduled Events In : ".Logic_Stopwatch::getInstance()->lap());
							Log::getLog()->log("-------End Account Batch Collections Process Iteration $iIteration -------------------------");
							$iIteration++;
						}
						while ($iCompletedInstances > 0);
						$oDataAccess->TransactionRollback();

						Log::getLog()->log("Total Time: ".Logic_Stopwatch::getInstance()->split());

					}
					else
					{
						throw new Exception("The Collections Batch Process is not eligible to run today.");
					}
				}
				catch (Exception $e)
				{

					if ($e instanceof Exception_Database)
					{
						Logic_Collection_BatchProcess_Report::addException($e);
						throw $e;
					}
					Log::getLog()->log($e->__toString());
					Logic_Collection_BatchProcess_Report::addException($e);
				}





				$sPath = FILES_BASE_PATH.'temp/';
				$bPathExists = file_exists ($sPath);
				if (!$bPathExists)
				{
					$bPathExists = mkdir ($sPath , 0777 , true);
				}

				Logic_Collection_BatchProcess_Report::emailReport();




				
				die;

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
            
		die;
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


	public function jan()
	{
		Log::registerFunctionLog('Developer_CollectionsLogic', 'logMessage', 'Application_Handler_Developer');
		Log::setDefaultLog('Developer_CollectionsLogic');
		$b = new b();
		$aAs = $b->getAs();

		$oObject = $aAs[3];

		$oObject->refreshData(array('a'=>11,'b'=> 12));
		$oObject2 = a::getInstance(3);

		echo $oObject2->array['b'];


		$oAccount = Logic_Account::getInstance(1000181544);

		$oPromise = Logic_Collection_Promise::getForAccount($oAccount);
		$aPromiseCollectables = $oPromise->getCollectables();
		foreach($aPromiseCollectables as $oCollectable)
		{
			Log::getLog()->Log("$oCollectable->id , $oCollectable->balance");
		}

		$aAccountCollectables = $oAccount->getCollectables();
		foreach ($aAccountCollectables as $oCollectable)
		{
			$oCollectable->balance = 555;
			//$oCollectable->save();
		}
		Logic_Collectable::refreshCache();
		//$oAccount->redistributeBalances();
		//$oAccount->getCollectables(Logic_Collectable::DEBIT, TRUE);
		Log::getLog()->log("After");

		foreach($aPromiseCollectables as $oCollectable)
		{
			Log::getLog()->log("$oCollectable->id , $oCollectable->balance");
		}

		die;

	}





	//
	// END: COLLECTIONS LOGIC TESTING
	//

}

class b
{
	public $aAs;

	public function getAs()
	{
		if ($aAs === NULL)
		{
			for ($a=0;$a<5;$a++)
			{
				$aObjects[] = a::getInstance($a);
			}

			$this->aAs = $aObjects;
		}

		return $this->aAs;
	}
}

class a
{
	public static $instances = array();

	public $array = array('a'=>1, 'b'=>2);
	public $id;

	public static function getInstance($id)
	{
		if (!array_key_exists($id, self::$instances))
				self::$instances[$id] = new self($id);
		return self::$instances[$id];
	}

	private function __construct($datamember)
	{
		$this->datamember = $datamember;
	}

	public function refreshData($aNewArray)
	{
		$this->array = $aNewArray;
	}
}

?>