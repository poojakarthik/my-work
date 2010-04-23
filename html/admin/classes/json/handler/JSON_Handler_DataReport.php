<?php

class JSON_Handler_DataReport extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	protected	$_aPermissions	= array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS);
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll()
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to get the list of data reports.'));
			}
			
			// Retrieve the datareports & convert response to std classes
			$aDataReports	= DataReport::getForEmployeeId(Flex::getUserId());
			//$aDataReports	= DataReport::getAll(); // -- JUST for testing, REMOVE ME
			
			foreach ($aDataReports as $iId => $oDataReport)
			{
				$aDataReports[$iId]	= $oDataReport->toStdClass();
				
				// Not sure if this is still needed. rmctainsh
				if ($oDataReport->Priviledges & PERMISSION_DEBUG)
				{
					$aDataReports[$iId]->bHidden	= true;
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"	=> true,
						"aRecords"	=> $aDataReports,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (JSON_Handler_DataReport_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getForId($iId)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to view this data report.'));
			}
			
			// Get the datareport orm object
			$oDataReport			= DataReport::getForId($iId);
			$oStdClassDataReport	= $oDataReport->toStdClass();
			
			// DEPRECATED: REMOVE ME: Check permissions against the reports priviledges (OLD PERMISSIONS)
			if (!AuthenticatedUser()->UserHasPerm($oDataReport->Priviledges))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to retrieve the report.'));
			}
			
			// Check permission to access the report (NEW PERMISSION METHOD)
			if (!$oDataReport->UserHasPerm(Flex::getUserId()))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to retrieve the report.'));
			}
			
			if ($oDataReport->RenderMode == REPORT_RENDER_EMAIL)
			{
				// Email report, so check that the authenticated user has an email address
				$oEmployee	= Employee::getForId(Flex::getUserId());			
				
				if (is_null($oEmployee->email) || ($oEmployee->email == ''))
				{
					throw(new JSON_Handler_DataReport_Exception('Please set your email address in your account Preferences, you cannot run an email report without doing so'));
				}
			}
			
			// Unserialize the serialized data
			$oStdClassDataReport->SQLSelect2 = $oStdClassDataReport->SQLSelect;
			$oStdClassDataReport->SQLSelect	= unserialize($oStdClassDataReport->SQLSelect);
			$aSQLFields	= unserialize($oStdClassDataReport->SQLFields);
			
			// Parse the SQLFields and define input information
			$aInputData = array();
			
			if (is_array($aSQLFields))
			{
				$oStdClassDataReport->SQLFields	= $aSQLFields;
				
				foreach ($aSQLFields as $sName => $aField)
				{
					$sType 	= $aField['Type'];
					
					// Get documentation label and create an array to represent each input
					$sDocumentationField	= $aField['Documentation-Field'];
					$oDocumentation			= Documentation::getForEntityAndField($aField['Documentation-Entity'], $sDocumentationField);
					$aNewInput 				= 	array(
												'sType'			=> $sType, 
												'sLabel' 		=> $oDocumentation->Label,
												'sFieldName'	=> $sDocumentationField,
												'sName'			=> $sName
											);
					
					if (array_key_exists('DBSelect', $aField))
					{
						// Statement select results
						$aNewInput['aOptions']	= array();
						
						// Check for ALL/IGNORE option
						if (is_array($aField['DBSelect']['IgnoreField']))
						{
							$aIgnoreField	= $aField['DBSelect']['IgnoreField'];
							$aNewInput['aOptions'][]	= array('sLabel' => $aIgnoreField['Label'], 'mValue' => $aIgnoreField['Value']);
						}
						
						// Execute the query and list the results
						$oStatement	= new StatementSelect(
													$aField['DBSelect']['Table'], 
													$aField['DBSelect']['Columns'], 
													$aField['DBSelect']['Where'], 
													$aField['DBSelect']['OrderBy'], 
													$aField['DBSelect']['Limit'], 
													$aField['DBSelect']['GroupBy']
												);
						
						$oStatement->Execute();
						while ($aStatement = $oStatement->Fetch())
						{
							$mValue	= $aStatement['Value'];
							$sLabel	= $aStatement['Label'];
							
							switch ($aField['DBSelect']['ValueType'])
							{
								case 'dataInteger':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => (int)$mValue);
									break;
								case 'dataString':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataBoolean':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataFloat':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => (float)$mValue);
									break;
								case 'dataDate':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataDatetime':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								default:
									throw (new Exception("Unsupported Constraint Type ::'{$aField['DBSelect']['ValueType']}'"));
							}
						}
					}
					else if (array_key_exists('DBQuery', $aField))
					{
						// Query results
						$aNewInput['aOptions']	= array();
						$oQuery 				= new Query();
						$oRecordSet 			= $oQuery->Execute($aField['DBQuery']['Query']);
						
						if ($oRecordSet === false)
						{
							throw new Exception("Failed to retrieve values for DataReport constraint field: $sName. Error: ". $oQuery->Error());
						}
						
						// Check for ALL/IGNORE option
						if (is_array($aField['DBQuery']['IgnoreField']))
						{
							$aIgnoreField = $aField['DBQuery']['IgnoreField'];
							$aNewInput['aOptions'][]	= array('sLabel' => $aIgnoreField['Label'], 'mValue' => $aIgnoreField['Value']);
						}
						
						while ($aRecord = $oRecordSet->fetch_assoc())
						{
							$mValue	= $aRecord['Value'];
							$sLabel	= $aRecord['Label'];
							
							switch ($aField['DBQuery']['ValueType'])
							{
								case 'dataInteger':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => (int)$mValue);
									break;
								case 'dataString':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataBoolean':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataFloat':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => (float)$mValue);
									break;
								case 'dataDate':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								case 'dataDatetime':
									$aNewInput['aOptions'][]	= array('sLabel' => $sLabel, 'mValue' => $mValue);
									break;
								default:
									throw (new Exception("Unsupported Constraint Type ::'{$aField['DBQuery']['ValueType']}'"));
							}
						}
					}
					
					$aInputData[] = $aNewInput;
				}
			}
			
			$oStdClassDataReport->aInputData = $aInputData;
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"		=> true,
						"oDataReport"	=> $oStdClassDataReport,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (JSON_Handler_DataReport_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function executeReport($aReportData)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to execute this report'));
			}
			
			// Get the report details
			$oDataReport	= DataReport::getForId($aReportData->iId);
			
			// DEPRECATED: REMOVE: Check permissions against the reports priviledges (OLD PERMISSION METHOD)
			if (!AuthenticatedUser()->UserHasPerm($oDataReport->Priviledges))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to execute the report'));
			}
			
			// Check that the authenticated user has an email address
			$iLoggedInUserId	= Flex::getUserId();
			$oEmployee			= Employee::getForId(Flex::getUserId());	
			
			// Check permission to access the report (NEW PERMISSION METHOD)
			if (!$oDataReport->UserHasPerm($iLoggedInUserId))
			{
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to execute the report'));
			}
			
			// Build an array of data to insert into 'DataReportSchedule' (for email), also used to generate the xls/csv file
			$aInsertData 					= array();
			$aInsertData['DataReport']		= $aReportData->iId;
			$aInsertData['Employee']		= $iLoggedInUserId;
			$aInsertData['CreatedOn']		= new MySQLFunction("NOW()");
			$aInsertData['SQLSelect']		= serialize($aReportData->aSelect);
			$aInsertData['SQLWhere']		= serialize($oDataReport->convertInput((array)$aReportData->hInput));
			$aInsertData['SQLLimit']		= serialize((int)$aReportData->sLimit);
			$aInsertData['RenderTarget']	= ($aReportData->iOutputCSV == 1) ? REPORT_TARGET_CSV : REPORT_TARGET_XLS;
			$aInsertData['Status']			= REPORT_WAITING;
			
			// Check the RenderMode
			if ($oDataReport->RenderMode == REPORT_RENDER_EMAIL)
			{
				// Generated later and email sent with result
				// Add a record to 'DataReportSchedule'
				$oDataReportSchedule = new StatementInsert("DataReportSchedule", $aInsertData);
				$oDataReportSchedule->Execute($aInsertData);
				
				return array(
						"Success"		=> true,
						"sEmail"		=> $oEmployee->email,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
			}
			else
			{
				// Get the reports result data
				$oResult = $oDataReport->execute($aReportData->aSelect, (array)$aReportData->hInput, (int)$aReportData->sLimit);
				if ($aResult = $oResult->FetchAll())
				{
					// Immediately generated and return the path to the file
					
					// Include XLS generator
					require_once('Spreadsheet/Excel/Writer.php');
	
					// Create an ApplicationReport
					$aConfig 	= LoadApplication("lib/report");
					$oAppReport	= new ApplicationReport(Array('Display' => FALSE));
					
					// Prepare the select columns
					$aDataReport	= $oDataReport->toArray();
					
					// Check the output format & generate the report
					$sPath 	= $aReportData->iOutputCSV;
					
					if ($aReportData->iOutputCSV == 1)
					{
				        // CSV file
				       	$aCSV 	= $oAppReport->ExportCSV($aResult, $aDataReport, $aInsertData, true);
				        $sPath 	= $aCSV['FileName'];
					}
					else
					{
						// XLS file
						$sPath	= $oAppReport->ExportXLS($aResult, $aDataReport, $aInsertData, true);
					}
					
					// Return the path to the generated file
					return array(
							"Success"		=> true,
							"sEmail"		=> false,
							"sPath"			=> basename($sPath),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
				}
				else
				{
					// No data in the report, return 'bNoRecords' to imply this
					return array(
							"Success"		=> true,
							"sEmail"		=> false,
							"bNoRecords"	=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
				}
			}
		}
		catch (JSON_Handler_DataReport_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

class JSON_Handler_DataReport_Exception extends Exception
{
	// No changes
}

?>