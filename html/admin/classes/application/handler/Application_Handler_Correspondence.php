<?php

class Application_Handler_Correspondence extends Application_Handler
{
	public function DownloadCSVErrorFile($aSubPath)
	{
		// TODO: Check permissions

		if (!$aSubPath[0])
		{
			throw new Exception('Invalid error file path supplied');
		}

		$sFileBaseName	= urldecode($aSubPath[0]);
		$sFilePath		= FILES_BASE_PATH."temp/{$sFileBaseName}";

		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="'.$sFileBaseName.'"');
		echo @file_get_contents($sFilePath);
		die;
	}


	public function CreateFromCSV($subPath)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		$sLog	= '';
		//Log::registerLog('CorrespondenceCreateLog', Log::LOG_TYPE_STRING, $sLog);
		//Log::setDefaultLog('CorrespondenceCreateLog');

		$aOutput	= array();
		try
		{
			// Validate input before proceeding
			$aErrors	= array();

			// Delivery date time
			$iDeliveryDateTime	= null;
			if (!isset($_POST['delivery_datetime']))
			{
				// Missing
				$aErrors[]	= "No delivery date time supplied.";
			}
			else
			{
				// Given, validate the date string (should be Y-m-d H:i:s)
				$iDeliveryDateTime	= strtotime($_POST['delivery_datetime']);
				if ($iDeliveryDateTime === false)
				{
					// Invalid date string
					$aErrors[]	= "Invalid delivery date time supplied ('".$_POST['delivery_datetime']."').";
				}
			}

			// CSV file
			$aFileInfo	= null;
			if (!isset($_FILES['csv_file']))
			{
				// Missing
				$aErrors[]	= 'No CSV file supplied.';
			}
			else
			{
				// Check error code
				$aFileInfo	= $_FILES['csv_file'];
				switch ($aFileInfo['error'])
				{
					case UPLOAD_ERR_OK:
						// All good
						break;
					case UPLOAD_ERR_INI_SIZE:
						$aErrors[]	= "The CSV file you supplied is too large. Maximum size is ".ini_get('upload_max_filesize').".";
					// No MAX_FILE_SIZE supplied with form
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_FILE:
						$aErrors[]	= 'No CSV file supplied.';
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						$aErrors[]	= 'Unable to receive your CSV file due a server error. Please contact YBS for assistance.';
				}
			}

			// Correspondence_Template id
			$iCorrespondenceTemplateId	= null;
			$oTemplateORM				= null;
			if (!isset($_POST['correspondence_template_id']))
			{
				// Missing
				$aErrors[]	= "No Correspondence Template Id supplied.";
			}
			else
			{
				try
				{
					// Try and load it
					$oTemplateORM	= Correspondence_Template::getForId($_POST['correspondence_template_id']);

					// All good
					$iCorrespondenceTemplateId	= (int)$_POST['correspondence_template_id'];
				}
				catch (Exception $oEx)
				{
					// Invalid
					$sId		= $_POST['correspondence_template_id'];
					$aErrors[]	= "Invalid Correspondence Template Id supplied (".($sId == '' ? 'Not supplied' : "'{$sId}'").")";
				}
			}

			if (count($aErrors) > 0)
			{
				// Got errors, add them to the output array and throw exception
				$aOutput['aErrors']	= $aErrors;
				throw new Exception("There was errors in the form information.");
			}

			try
			{
				$oTemplate	= Correspondence_Logic_Template::getForId($iCorrespondenceTemplateId);
				$oTemplate->createRun(false, $aFileInfo, date('Y-m-d H:i:s', $iDeliveryDateTime), true);
			}
			catch (Correspondence_DataValidation_Exception $oEx)
			{
				// Invalid CSV file, build an error message
				$oEx->sFileName	= basename($oEx->sFileName);
				$aOutput['oException'] = $oEx;
				throw new Exception();
			}

			$aOutput['bSuccess']	= true;
		}
		catch (Exception $e)
		{
			$aOutput['bSuccess'] = false;
			$aOutput['sMessage'] = "Unsupported Exception";
			$aOutput['log_reference'] = Flex::unsupportedException($e, array(
				'csv_file' => (isset($_FILES['csv_file']) ? $_FILES['csv_file'] : null)
			));
		}
		
		if ($bUserIsGod)
		{
			$aOutput['sDebug']	= $sLog;
		}
		
		echo JSON_Services::instance()->encode($aOutput);
		die;
	}

	public function ExportRunToCSV($aSubPath)
	{
		try
		{
			// Proper admin required
			AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

			// Get the correspondence item for the run
			$iCorrespondenceRunId	= (int)$aSubPath[0];
			$oRun					= Correspondence_Logic_Run::getForId($iCorrespondenceRunId);

			// Build the list of columns for the csv file
			$aAdditionalColumns	= $oRun->getAdditionalColumns(0);
			$aColumns			= 	array(
										'Customer Group',
										'Account Id',
										'Account Name',
										'Addressee Title',
										'Addressee First Name',
										'Addressee Last Name',
										'Address Line 1',
										'Address Line 2',
										'Suburb',
										'Postcode',
										'State',
										'Email Address',
										'Mobile',
										'Landline',
										'Delivery Method'
									);

			foreach ($aAdditionalColumns as $sColumn)
			{
				$aColumns[]	= $sColumn;
			}

			// Create File_CSV to do the file creation
			$oFile	= new File_CSV();
			$oFile->setColumns($aColumns);

			// Determine the contents of the file
			if (isset($_GET['items']))
			{
				// Individual Correspondence ids supplied
				$bUsingLogic		= false;
				$aCorrespondence	= explode(',', $_GET['items']);
			}
			else
			{
				// Use all of the Correspondence in the run
				$bUsingLogic		= true;
				$aCorrespondence	= $oRun->getCorrespondence();
			}

			// Build list of lines for the file
			$aLines	= array();
			foreach ($aCorrespondence as $mCorrespondence)
			{
				if ($bUsingLogic)
				{
					$oCorrespondence	= $mCorrespondence;
				}
				else
				{
					$oCorrespondence	= new Correspondence_Logic(Correspondence::getForId($mCorrespondence));
				}

				$sDeliveryMethod	= Correspondence_Delivery_Method::getForId($oCorrespondence->correspondence_delivery_method_id)->name;
				$aLine				=	array
										(
											'Customer Group'		=> $oCorrespondence->customer_group_id,
											'Account Id'			=> $oCorrespondence->account_id,
											'Account Name'			=> $oCorrespondence->account_name,
											'Addressee Title'		=> $oCorrespondence->title,
											'Addressee First Name'	=> $oCorrespondence->first_name,
											'Addressee Last Name'	=> $oCorrespondence->last_name,
											'Address Line 1'		=> $oCorrespondence->address_line_1,
											'Address Line 2'		=> $oCorrespondence->address_line_2,
											'Suburb'				=> $oCorrespondence->suburb,
											'Postcode'				=> $oCorrespondence->postcode,
											'State'					=> $oCorrespondence->state,
											'Email Address'			=> $oCorrespondence->email,
											'Mobile'				=> $oCorrespondence->mobile,
											'Landline'				=> $oCorrespondence->landline,
											'Delivery Method'		=> $sDeliveryMethod
										);

				// Additional column values
				$aItem	= $oCorrespondence->toArray();
				foreach ($aAdditionalColumns as $sColumn)
				{
					$aLine[$sColumn]	= $aItem[$sColumn];
				}
				$aLines[]	= $aLine;
			}

			// Add the lines to the file
			foreach ($aLines as $aLine)
			{
				$oFile->addRow($aLine);
			}

			// Output for download
			header('Content-type: text/csv');
			header("Content-Disposition: attachment; filename=\"correspondence-run-export-$iCorrespondenceRunId-".date('YmdHis').".csv\"");
			echo $oFile->save();
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			echo $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
		}

		die;
	}

	public function createRun()
	{
		$oSource = new Correspondence_Logic_Source_Csv(file_get_contents(dirname(__FILE__).'/sample_csv.csv'));
		$iCarrierId = 39;
		$aColumns = array(

							array('id'=>null, 'name'=>'abn' ,						'description'=> 'abn', 	'column_index'=>1 ,'correspondence_template_id' => null ),
							array('id'=>null, 'name'=>'fnn' ,								'description'=> 'service fnn', 		'column_index'=>2 ,'correspondence_template_id' => null ),
							array('id'=>null, 'name'=>'plan' ,								'description'=> 'service rateplan', 'column_index'=>3 ,'correspondence_template_id' => null ),

		);
		$oTemplate = Correspondence_Logic_Template::create('motorpass correspondence', 'blah blah',$aColumns, $iCarrierId, $oSource);
		$oRun = $oTemplate->createRun();
		$oTemplate->save();
		$oRun->save();


		echo 'all done';
		die;

	}

	public function testRunEmail()
	{
		$aCorrespondence = Correspondence_Run::getAll();
		foreach ($aCorrespondence as $oCorrespondence)
		{
			if ($oCorrespondence->data_file_export_id != null && $oCorrespondence->file_import_id!=null)
				Correspondence_Logic_Run::getForId($oCorrespondence->id, true)->sendDispatchEmail();
		}
		die;
	}

	public function testBatchEmail()
	{
		$aCorrespondence = Correspondence_Run::getAll();
		$aObjects = array();
		$oBatch = Correspondence_Run_Batch::getForId(1);
		foreach ($aCorrespondence as $oCorrespondence)
		{
			//if ($oCorrespondence->data_file_export_id != null)
				$aObjects[]=Correspondence_Logic_Run::getForId($oCorrespondence->id, true);
		}
		Correspondence_Dispatcher::sendBatchDeliveryEmail($oBatch,$aObjects);
		die;
	}

	public function invoice()
	{
		Invoice_Run::getForId(4197)->deliver();
	}

	public function sendWaitingRuns()
	{
		Correspondence_Dispatcher::sendWaitingRuns();
		die;
	}

	public function getForBatch()
	{
		$aRuns = Correspondence_Logic_Run::getForBatchId(49, true);
		die;
	}

	public function getForAccount($iAccountId)
	{
		$aRuns = Correspondence_Logic::getForAccountId(1000179892, true);
		die;
	}
}


?>
