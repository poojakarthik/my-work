<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// monitor_page
//----------------------------------------------------------------------------//
/**
 * monitor_page
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		monitor_page.php
 * @language	PHP
 * @package		monitor_application
 * @author		Jared 'flame' Herbohn
 * @version		7.02
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

 // MonitorPage
 class MonitorPage extends VixenPage
 {
 	function __construct($arrConfig)
	{
		parent::__construct($arrConfig);
		
		// Create an Instance of the Monitor App
		$this->appMonitor = new ApplicationMonitor($arrConfig);
		
		// Create an Instance of the Rating App
		$this->appRating = new ApplicationRating($arrConfig);
	}
	
	
 	function ShowCDRStatusList($arrPeriod = NULL)
	{
		// get CDR Status list
		$arrStatus = $this->appMonitor->CountCDRStatus(FALSE, $arrPeriod);
		if (is_array($arrStatus))
		{
			// title
			$this->AddTitle('CDRs by Status');
			
			// table
			$tblMenu = $this->NewTable('Border');
			$tblMenu->AddRow(Array('Code', 'Status', 'Count', 'Cost', 'Charge'));
			$tblMenu->Align(Array('Right', '', 'Right', 'Right', 'Right'));
			foreach($arrStatus AS $intStatus=>$arrDetails)
			{
				$strStatus 	= GetConstantDescription($intStatus, 'CDR');
				$arrRow = Array($intStatus, $strStatus, $arrDetails['Count'], $arrDetails['Cost'], $arrDetails['Charge']);
				$tblMenu->AddRow($arrRow, "cdr_list.php?Status=$intStatus");
			}
			$this->AddTable($tblMenu);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowCDRStatusRecordTypeList()
	{
		// get CDR Status, RecordType List
		$arrStatusRecordType = $this->appMonitor->CountCDRStatusRecordType();
		if (is_array($arrStatusRecordType))
		{
			// title
			$this->AddTitle('CDRs by Status, RecordType');
			
			// table
			$tblMenu = $this->NewTable('Border');
			$tblMenu->AddRow(Array('Code', 'Status', 'RecordType', 'Count', 'Cost', 'Charge'));
			$tblMenu->Align(Array('Right', '', '', 'Right', 'Right', 'Right'));
			foreach($arrStatusRecordType AS $intStatus=>$arrRecordType)
			{
				$strStatus 	= GetConstantDescription($intStatus, 'CDR');
				foreach($arrRecordType AS $intRecordType=>$arrDetails)
				{
					$intRecordType = (int)$intRecordType;
					$strRecordType = $this->appMonitor->arrRecordType[$intRecordType]['Name'];
					$arrRow = Array($intStatus, $strStatus, "$intRecordType - $strRecordType", $arrDetails['Count'], $arrDetails['Cost'], $arrDetails['Charge']);
					$tblMenu->AddRow($arrRow, "cdr_list.php?Status=$intStatus&RecordType=$intRecordType");
				}
			}
			$this->AddTable($tblMenu);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowCDRRateList()
	{
		// get CDR Status list
		$arrRates = $this->appMonitor->CountCDRRate();
		if (is_array($arrRates))
		{
			// title
			$this->AddTitle('CDRs by Rate');
			
			// table
			$tblMenu = $this->NewTable('Border');
			$tblMenu->AddRow(Array('Rate', 'Name', 'Description', 'Count', 'Cost', 'Charge', 'Compare'));
			$tblMenu->Align(Array('Right', '', '', 'Right', 'Right', 'Right'));
			foreach($arrRates AS $intRate=>$arrDetails)
			{
				$arrRow = Array($intRate, $arrDetails['Name'], $arrDetails['Description'], $arrDetails['Count'], $arrDetails['Cost'], $arrDetails['Charge'], Array('Value'=>'etech', 'Href'=>"cdr_list.php?Rate=$intRate&Compare=Etech"));
				$tblMenu->AddRow($arrRow, "cdr_list.php?Rate=$intRate");
			}
			$this->AddTable($tblMenu);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowCDRRateCompareList($strCompare)
	{
		// get CDR Status list
		$arrRates = $this->appMonitor->CountCompareCDRRate($strCompare);
		if (is_array($arrRates))
		{
			// title
			$this->AddTitle('CDRs by Rate');
			
			// table
			$tblMenu = $this->NewTable('Border');
			$tblMenu->AddRow(Array('Rate', 'Name', 'Description', 'Count', "$strCompare Count", 'Cost', 'Charge', "$strCompare Charge", 'Compare'));
			$tblMenu->Align(Array('Right', '', '', 'Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrRates AS $intRate=>$arrDetails)
			{
				$arrRow = Array($intRate, $arrDetails['Name'], $arrDetails['Description'], $arrDetails['Count'], $arrDetails['CompareCount'], $arrDetails['Cost'], $arrDetails['Charge'], $arrDetails['CompareCharge'], Array('Value'=>$strCompare, 'Href'=>"cdr_list.php?Rate=$intRate&Compare=$strCompare"));
				$tblMenu->AddRow($arrRow, "cdr_list.php?Rate=$intRate");
			}
			$this->AddTable($tblMenu);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowCDRList($arrWhere, $intStart, $intLimit, $bolReRate=FALSE)
	{
		$arrCDRs = $this->appMonitor->ListCDR($arrWhere, $intStart, $intLimit);
		if (is_array($arrCDRs))
		{
			// table
			$tblCDR = $this->NewTable('Border');
			$arrRow = Array('Id', 'Account', 'Service', 'FNN', 'Source', 'Destination', 'Description', 'Units', 'Cost', 'Charge', 'Credit', 'Rate', 'Dest.', 'ServiceType', 'RecordType', 'Status', 'Carrier', 'Start', 'End');
			$arrAlign = Array('Right', 'Right', 'Right', 'Right', 'Right', 'Right', '', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Left');
			if ($bolReRate === TRUE)
			{
				$arrRow[] = 'ReRate';
				$arrAlign[] = 'Right';
			}
			$tblCDR->AddRow($arrRow);
			$tblCDR->Align($arrAlign);
			foreach($arrCDRs AS $arrCDR)
			{
				$intMaxId = max($intMaxId, $arrCDR['Id']);
				$arrRow = Array($arrCDR['Id'], $arrCDR['Account'], $arrCDR['Service'], $arrCDR['FNN'], $arrCDR['Source'], $arrCDR['Destination'], $arrCDR['Description'], $arrCDR['Units'], $arrCDR['Cost'], $arrCDR['Charge'], $arrCDR['Credit'], $arrCDR['Rate'], $arrCDR['DestinationCode'], $arrCDR['ServiceType'], $arrCDR['RecordType'], $arrCDR['Status'], $arrCDR['Carrier'], $arrCDR['StartDatetime'], $arrCDR['EndDatetime']);
				if ($bolReRate === TRUE)
				{
					$arrRow[] = money_format('%i',$this->appRating->RateCDR($arrCDR));
				}
				$tblCDR->AddRow($arrRow, "cdr_view.php?Id={$arrCDR['Id']}");
			}
			$this->AddTable($tblCDR);
			
			// pagination ('previous' button won't work properly)
			$intPaginateStart = $intMaxId - $intLimit;
			$arrOptions = Array();
			foreach($arrWhere AS $strKey=>$strValue)
			{
				$arrOptions[] = "$strKey=$strValue";
			}
			$strOptions = implode('&', $arrOptions);
			$this->AddPagination("cdr_list.php", $strOptions, $intPaginateStart, $intLimit);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowCDRCompareList($arrWhere, $strCompare, $intStart, $intLimit, $bolReRate=FALSE)
	{
		
		$arrCDRs = $this->appMonitor->ListCompareCDR($arrWhere, $strCompare, $intStart, $intLimit);
		if (is_array($arrCDRs))
		{
			// table
			$tblCDR = $this->NewTable('Border');
			$arrRow = Array('Id', 'Account', 'Service', 'FNN', 'Source', 'Destination', 'Desc.', " $strCompare Desc.", 'Units', 'Cost', 'Charge', " $strCompare Charge", 'Diff.', 'Credit', 'Rate', 'Dest.', 'SvcType', 'RecType', " $strCompare RecType", 'Status', 'Carrier', 'Start');
			$arrAlign = Array('Right', 'Right', 'Right', 'Right', 'Right', 'Right', '', '', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right');
			if ($bolReRate === TRUE)
			{
				$arrRow[] = 'ReRate';
				$arrAlign[] = 'Right';
			}
			$tblCDR->AddRow($arrRow);
			$tblCDR->Align($arrAlign);
			foreach($arrCDRs AS $arrCDR)
			{
				$intMaxId = max($intMaxId, $arrCDR['Id']);
				$strDifference = '';
				
				$fltDifference = (float)$arrCDR['Charge'] - (float)$arrCDR['CompareCharge'];
				$strDifference = number_format($fltDifference, 4);
				
				// colours
				if ($fltDifference >= -0.012 && $fltDifference <= 0.012)
				{
					$strDifference = "<b><font color='#00AA00'>".$strDifference."</font></b>";
				}
				elseif ($fltDifference >= -1 && $fltDifference < 0)
				{
					$strDifference = "<b><font color='#FF8800'>".$strDifference."</font></b>";
				}
				elseif ($fltDifference > 0 && $fltDifference <= 1)
				{
					$strDifference = "<b><font color='#FF0088'>".$strDifference."</font></b>";
				}
				else
				{
					$strDifference = "<b><font color='#FF0000'>".$strDifference."</font></b>";
				}
				
				$arrRow = Array($arrCDR['Id'], $arrCDR['Account'], $arrCDR['Service'], $arrCDR['FNN'], $arrCDR['Source'], $arrCDR['Destination'], $arrCDR['Description'], $arrCDR['CompareDescription'], $arrCDR['Units'], $arrCDR['Cost'], $arrCDR['Charge'], $arrCDR['CompareCharge'], $strDifference, $arrCDR['Credit'], $arrCDR['Rate'], $arrCDR['DestinationCode'], $arrCDR['ServiceType'], $arrCDR['RecordType'], $arrCDR['CompareRecordType'], $arrCDR['Status'], $arrCDR['Carrier'], $arrCDR['StartDatetime']);
				if ($bolReRate === TRUE)
				{
					$arrRow[] = money_format('%i',$this->appRating->RateCDR($arrCDR));
				}
				$tblCDR->AddRow($arrRow, "cdr_view.php?Id={$arrCDR['Id']}");
			}
			$this->AddTable($tblCDR);
			
			// pagination ('previous' button won't work properly)
			$intPaginateStart = $intMaxId - $intLimit;
			$arrOptions = Array();
			foreach($arrWhere AS $strKey=>$strValue)
			{
				$arrOptions[] = "$strKey=$strValue";
			}
			$arrOptions[] = "Compare=$strCompare";
			$strOptions = implode('&', $arrOptions);
			$this->AddPagination("cdr_list.php", $strOptions, $intPaginateStart, $intLimit);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function DebugBadNormalise($intStatus)
	{
		
	}
	
	function ShowNormalisedCDR($intCDR)
	{
		if (!$intCDR)
		{
			$this->AddError("NO CDR Requested");
		}
		else
		{
			// Create an instance of each Normalisation module
			$arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
			$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
			$arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
			$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
			$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
		
			// get CDR
			$arrCDR = $this->appMonitor->GetCDR($intCDR);
			if (!$arrCDR)
			{
				$this->AddError("CDR Not Found");
				return FALSE;
			}
			else
			{
				// Check for a Normalisation Module
				if (!$arrNormalisationModule[$arrCDR['FileType']])
				{
					$this->AddError("Missing CDR Normalisation Module");
					return FALSE;
				}
				else
				{
					// normalise CDR
					$mixReturn = $arrNormalisationModule[$arrCDR['FileType']]->Normalise($arrCDR);
					
					// debug CDR
					$arrOutput = $arrNormalisationModule[$arrCDR['FileType']]->DebugCDR();
				}
			}
		}
		
		
		// Display CDR
		if ($arrOutput)
		{
			if (is_array($arrOutput['Normalised']))
			{
				// menu items
				if ($arrOutput['Normalised']['Service'])
				{
					$this->AddForwardLink("service_rategroup_list.php?Service={$arrOutput['Normalised']['Service']}","[ RateGroups for Service ]");
					$this->AddForwardLink("service_rateplan_list.php?Service={$arrOutput['Normalised']['Service']}","[ RatePlans for Service ]");
				}
				
				// title
				$this->AddTitle("Normalised CDR");
				
				// table
				$tblCDR = $this->NewTable('Border');
				foreach($arrOutput['Normalised'] AS $strKey=>$strValue)
				{
					$arrRow = Array($strKey, $strValue);
					$tblCDR->AddRow($arrRow);
				}
				$this->AddTable($tblCDR);
			}
			
			if (is_array($arrOutput['Raw']))
			{
				// title
				$this->AddTitle("Raw CDR");
				
				// table
				$tblCDR = $this->NewTable('Border');
				foreach($arrOutput['Raw'] AS $strKey=>$strValue)
				{
					$arrRow = Array($strKey, $strValue);
					$tblCDR->AddRow($arrRow);
				}
				$this->AddTable($tblCDR);
			}
		}
	return TRUE;
	}
	
	function ShowServiceRatePlanList($intService)
	{
		// get Rate Plan list
		$arrWhere = Array();
		if ($intService)
		{
			$arrRatePlans = $this->appMonitor->ListServiceRatePlan($intService);
			if (is_array($arrRatePlans))
			{
				$this->AddTitle("RatePlans for Service: $intService");
			
				// table
				$tblRatePlan = $this->NewTable('Border');
				$tblRatePlan->AddRow(Array('Id', 'Name', 'Description', 'Shared', 'Min Montly', 'ChargeCap', 'UsageCap', 'Start Date', 'End Date'));
				$tblRatePlan->Align(Array('Right', '', '', 'Right', 'Right', 'Right', 'Right'));
				foreach($arrRatePlans AS $arrRatePlan)
				{
					$arrRow = Array($arrRatePlan['Id'], $arrRatePlan['Name'], $arrRatePlan['Description'], $arrRatePlan['Shared'], $arrRatePlan['MinMonthly'], $arrRatePlan['ChargeCap'], $arrRatePlan['UsageCap'], $arrRatePlan['StartDateTime'], $arrRatePlan['EndDateTime']);
					$tblRatePlan->AddRow($arrRow);
				}
				$this->AddTable($tblRatePlan);
			}
			else
			{
				$this->AddError("NO RatePlans FOUND");
				return FALSE;
			}
		}
		else
		{
			$this->AddError("NO Service Selected");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowServiceRateGroupList($intService)
	{
		// get Rate Group list
		$arrWhere = Array();
		if ($intService)
		{
			$arrRateGroups = $this->appMonitor->ListServiceRateGroup($intService);
			if (is_array($arrRateGroups))
			{
				$this->AddTitle("RateGroups for Service: $intService");
			
				// table
				$tblRateGroup = $this->NewTable('Border');
				$tblRateGroup->AddRow(Array('Id', 'Name', 'Description', 'RecordType', 'Fleet', 'Start Date', 'End Date'));
				$tblRateGroup->Align(Array('Right', '', '', '', 'Right'));
				foreach($arrRateGroups AS $arrRateGroup)
				{
					$intRecordType = $arrRateGroup['RecordType'];
					$strRecordType = "$intRecordType - ".$this->appMonitor->arrRecordType[$intRecordType]['Name'];
					$arrRow = Array($arrRateGroup['Id'], $arrRateGroup['Name'], $arrRateGroup['Description'], $strRecordType, $arrRateGroup['Fleet'], $arrRateGroup['StartDateTime'], $arrRateGroup['EndDateTime']);
					$tblRateGroup->AddRow($arrRow);
				}
				$this->AddTable($tblRateGroup);
			}
			else
			{
				$this->AddError("NO RateGroups FOUND");
				return FALSE;
			}
		}
		else
		{
			$this->AddError("NO Service Selected");
			return FALSE;
		}
		return TRUE;
	}
	
	//TODO!rich! when you add the following...
	// return true on success
	// set an error and return false on error
	
	function ShowRate($intRate)
	{
		$intRate = (int)$intRate;
		$arrRate = $this->appMonitor->GetRate($intRate);
		$tblRate = $this->NewTable('Border');
		foreach($arrRate AS $strKey=>$mixValue)
		{
			$tblRate->AddRow(Array($strKey, $mixValue));
		}
		$this->AddTable($tblRate);
		return TRUE;
	}
	
	function ShowRateSummary($intRate)
	{
		$intRate = (int)$intRate;
		$arrRate = $this->appMonitor->GetRate($intRate);
		$tblRate = $this->NewTable('Border');
		$tblRate->AddRow(Array('Id', 'Name', 'Description', 'RecordType'));
		$tblRate->Align(Array('Right', '', '', '', 'Right'));
		
		$arrRow = Array($arrRate['Id'], $arrRate['Name'], $arrRate['Description'], $arrRate['RecordType']);
		$tblRate->AddRow($arrRow);
		
		$this->AddTable($tblRate);
		return TRUE;
	}
	
	function ShowRateList($intRateGroup)
	{
		//TODO!rich! show a list of Rates for a RateGroup
	}
	
	function ShowRateGroupList($intRatePlan)
	{
		//TODO!rich! show a list of RateGroups for a RatePlan
	}
	
	function ShowService($intService)
	{
		//TODO!rich! show details of a service
	}
	
	function ShowServiceList($intAccount)
	{
		//TODO!rich! show a list of Services for an account
	}
	
	function ShowAccount($intAccount)
	{
		//TODO!rich! show details of an account
	}
	
	function ShowAccountList($intAccountGroup)
	{
		//TODO!rich! show a list of Accounts for an AccountGroup
	}
	
	// return a list of Etech CDRs
	function ShowEtechCDRList($strBillingPeriod, $intStart, $intLimit)
	{
		$arrCDRs = $this->appMonitor->ListEtechCDRs($strBillingPeriod, $intStart, $intLimit);
		if (is_array($arrCDRs))
		{
			// Status Descriptions & Colour Code the Differences
			foreach ($arrCDRs as &$arrCDR)
			{
				// status
				if ($arrCDR['Status'] != CDR_ETECH_NO_MATCH)
				{
					$fltDifference = (float)$arrCDR['Difference'];
				}
				else
				{
					$fltDifference = "";
				}
				$arrCDR['Status'] = GetConstantDescription($arrCDR['Status'], 'CDR');
								
				// colours
				if ($fltDifference == 0.0)
				{
					$arrCDR['Difference'] = "<b><font color='#00AA00'>".$arrCDR['Difference']."</font></b>";
				}
				elseif ($fltDifference >= -1 && $fltDifference <= 1)
				{
					$arrCDR['Difference'] = "<b><font color='#FF8800'>".$arrCDR['Difference']."</font></b>";
				}
				else
				{
					$arrCDR['Difference'] = "<b><font color='#FF0000'>".$arrCDR['Difference']."</font></b>";
				}
			}
			
			unset($arrCDR);

			// table
			$tblCDR = $this->NewTable('Border');
			$tblCDR->AddRow(Array('Etech CDR Id', 'Vixen CDR Id', 'Account', 'FNN', 'Destination', 'Description', 'Units', 'Charge', 'Charge', 'Cost', 'Credit', 'ServiceType', 'RecordType', 'Status', 'Start', 'Rate', 'Difference'), FALSE, TRUE);
			$tblCDR->Align(Array('Right', 'Right', 'Right', 'Right', '', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrCDRs AS $arrCDR)
			{
				$intMaxId = max($intMaxId, $arrCDR['Id']);
				$arrRow = Array($arrCDR['Id'], $arrCDR['VixenCDR'],  $arrCDR['Account'], $arrCDR['FNN'], $arrCDR['Destination'], $arrCDR['Description'], $arrCDR['Units'], $arrCDR['Charge'], $arrCDR['CDRCharge'], $arrCDR['CDRCost'], $arrCDR['Credit'], $arrCDR['ServiceType'], $arrCDR['RecordTypeName'], "<b>".$arrCDR['Status']."</b>", $arrCDR['StartDatetime'], $arrCDR['RateName'], $arrCDR['Difference']);
				$tblCDR->AddRow($arrRow, "cdr_compare_etech.php?Id={$arrCDR['Id']}");
			}
			$this->AddTable($tblCDR);
			
			// pagination ('previous' button won't work properly)
			$intPaginateStart = $intMaxId - $intLimit;
			$this->AddPagination("cdr_list_etech.php", "period=$strBillingPeriod", $intPaginateStart, $intLimit);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	// return a viXen/Etech invoice list
	function ShowEtechInvoiceList()
	{
		// Get Invoice List
		$arrInvoices = $this->appMonitor->ListEtechInvoice();
		
		if (count($arrInvoices) == 0)
		{
			$this->AddError("There are no Etech invoices in the system.  Please run the Etech bill importer first!");
			return;
		}
		
		$this->AddTitle("Etech Invoices");
		
		// Generate page
		foreach ($arrInvoices as $arrInvoice)
		{
			$strBillingPeriodURL = str_replace(" ", "%20", $arrInvoice['InvoiceRun']);
			$this->AddLink("cdr_list_etech.php?period=$strBillingPeriodURL", date("F Y", strtotime($arrInvoice['InvoiceRun'])));
		}
	}
	
	// return a viXen/Etech CDR comparison
	function ShowEtechCDR($intEtechCDR)
	{
		// Get the CDR
		if (!($arrCDR = $this->appMonitor->GetEtechCDR($intEtechCDR)))
		{
			$this->AddError("Cannot find Etech CDR with id $intEtechCDR");
			return FALSE;
		}
		
		// Etech CDR
		$tblCDR = $this->NewTable('Border');
		$arrRows[] = Array("", "<B>Etech CDR</B>", "<B>viXen CDR</B>");
		foreach ($arrCDR[0] as $strKey=>$mixValue)
		{
			$arrRows[] = Array("<B>$strKey</B>", $mixValue);
		}
		
		// Vixen CDR
		$i = 0;
		foreach ($arrCDR[1] as $strKey=>$mixValue)
		{
			$i++;
			$arrRows[$i][] = $mixValue;
		}
		
		// add the rows
		foreach ($arrRows as $arrRow)
		{
			$tblCDR->AddRow($arrRow);
		}
		
		$this->AddTable($tblCDR);
		
		return $arrCDR[1]['Id'];
	}
	
	
	function ShowInvoiceCompareList($intMinDifference = 0)
	{
		$intMinDifference = (int)$intMinDifference;
		

		// get Invoice Compare list
		$arrRecords = $this->appMonitor->GetInvoiceCompare();
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('Compare Invoices');
			
			// table
			$tblTable = $this->NewTable('Border');
			$tblTable->AddRow(Array('#', 'Account', 'Vixen', 'Etech', 'Difference'));
			$tblTable->Align(Array('Right', 'Right', 'Right', 'Right', 'Right'));
			$intCount = 0;
			foreach($arrRecords AS $intRecord=>$arrDetails)
			{
				if (abs($arrDetails['Dif']) > $intMinDifference)
				{
					$intCount++;
					$arrRow = Array($intCount, $arrDetails['Account'], number_format($arrDetails['VixenTotal'],2), $arrDetails['EtechTotal'], number_format($arrDetails['Dif'],2));
					$tblTable->AddRow($arrRow, "invoice_compare.php?Account={$arrDetails['Account']}&InvoiceRun={$arrDetails['InvoiceRun']}&Etech={$arrDetails['EtechTotal']}");
				}
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddError("NO Invoices FOUND");
			return FALSE;
		}
		return TRUE;
	}
	
	function ShowAccountServiceTotals($intAccount, $strInvoiceRun)
	{
		// get service totals
		$arrRecords = $this->appMonitor->GetAccountServiceTotals($intAccount, $strInvoiceRun);
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('Service Totals');
			
			// table
			$tblTable = $this->NewTable('Border');
			if (current($arrRecords))
			{
				$tblTable->AddRow(Array_keys(current($arrRecords)));
			}
			//$tblTable->Align(Array('Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrRecords AS $intRecord=>$arrDetails)
			{
				$arrRow = Array();
				foreach($arrDetails AS $mixValue)
				{
					$arrRow[] = $mixValue;
				}
				$tblTable->AddRow($arrRow);
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddTitle('No Service Totals Found');
		}
		return TRUE;
	}
	
	function ShowAccountCDRTotals($intAccount, $strInvoiceRun)
	{
		// get service totals
		$arrRecords = $this->appMonitor->GetAccountCDRTotals($intAccount, $strInvoiceRun);
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('CDR Totals');
			
			// table
			$tblTable = $this->NewTable('Border');
			$tblTable->AddRow(Array('Type','Count', 'Cost', 'Charge'));
			$tblTable->Align(Array('Left', 'Right', 'Right', 'Right'));
			foreach($arrRecords AS $strRecord=>$arrDetails)
			{
				$arrRow = Array($strRecord, $arrDetails['Count'], $arrDetails['Cost'], $arrDetails['Charge']);
				$tblTable->AddRow($arrRow);
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddTitle('No CDRs Found');
		}
		return TRUE;
	}
	
	function ShowAccountChargeTotals($intAccount, $strInvoiceRun)
	{
		// get service totals
		$arrRecords = $this->appMonitor->GetAccountChargeTotals($intAccount, $strInvoiceRun);
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('Adjustment Totals');
			
			// table
			$tblTable = $this->NewTable('Border');
			$tblTable->AddRow(Array('Type', 'Count', 'Charge'));
			$tblTable->Align(Array('Left', 'Right'));
			foreach($arrRecords AS $strRecord=>$arrDetails)
			{
				$arrRow = Array($strRecord, $arrDetails['Count'], $arrDetails['Amount']);
				$tblTable->AddRow($arrRow);
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddTitle('No Adjustments Found');
		}
		return TRUE;
	}
	
	function ShowAccountCharges($intAccount, $strInvoiceRun)
	{
		// get service totals
		$arrRecords = $this->appMonitor->GetAccountCharges($intAccount, $strInvoiceRun);
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('Adjustments');
			
			// table
			$tblTable = $this->NewTable('Border');
			if (current($arrRecords))
			{
				$tblTable->AddRow(Array_keys(current($arrRecords)));
			}
			//$tblTable->Align(Array('Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrRecords AS $intRecord=>$arrDetails)
			{
				$arrRow = Array();
				foreach($arrDetails AS $mixValue)
				{
					$arrRow[] = $mixValue;
				}
				$tblTable->AddRow($arrRow);
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddTitle('No Adjustments Found');
		}
		return TRUE;
	}
	
	function ShowTempInvoice($intAccount, $strInvoiceRun)
	{
		// get service totals
		$arrRecords = $this->appMonitor->GetTempInvoice($intAccount, $strInvoiceRun);
		if (is_array($arrRecords))
		{
			// title
			$this->AddTitle('Invoice');
			
			// table
			$tblTable = $this->NewTable('Border');
			if (current($arrRecords))
			{
				$tblTable->AddRow(Array_keys(current($arrRecords)));
			}
			//$tblTable->Align(Array('Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrRecords AS $intRecord=>$arrDetails)
			{
				$arrRow = Array();
				foreach($arrDetails AS $mixValue)
				{
					$arrRow[] = $mixValue;
				}
				$tblTable->AddRow($arrRow);
			}
			$this->AddTable($tblTable);
		}
		else
		{
			$this->AddTitle('No Invoice Found');
		}
		return TRUE;
	}
	
	
	
 }
 
?>
