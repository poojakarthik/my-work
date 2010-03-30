<?php

class JSON_Handler_DataReport extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Retrieve the datareports & convert response to std classes
			$aDataReports 			= DataReport::getAll();
			$aStdClassDataReports 	= array();
			
			foreach ($aDataReports as $iId => $oDataReport)
			{
				$aStdClassDataReports[$iId]	= $oDataReport->toStdClass();				
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"	=> true,
						"aRecords"	=> $aStdClassDataReports,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getForId($iId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Get the datareport orm object
			$oDataReport			= DataReport::getForId($iId);
			$oStdClassDataReport	= $oDataReport->toStdClass();
			
			// Unserialize the serialized data
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
												'sType'		=> $sType, 
												'sLabel' 	=> $oDocumentation->Label,
												'sFieldName'	=> $sDocumentationField
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
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function runReport($iId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			$bDummy = false; // Delete me
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

?>