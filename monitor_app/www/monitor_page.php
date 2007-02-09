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
	}
	
	
 	function ShowCDRStatusList()
	{
		// get CDR Status list
		$arrStatus = $this->appMonitor->CountCDRStatus();
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
	
	function ShowCDRList($arrWhere, $intStart, $intLimit)
	{
		$arrCDRs = $this->appMonitor->ListCDR($arrWhere, $intStart, $intLimit);
		if (is_array($arrCDRs))
		{
			// table
			$tblCDR = $this->NewTable('Border');
			$tblCDR->AddRow(Array('Id', 'Account', 'Service', 'FNN', 'Source', 'Destination', 'Description', 'Units', 'Cost', 'Charge', 'Credit', 'Rate', 'Dest.', 'ServiceType', 'RecordType', 'Status', 'Carrier', 'Start', 'End'));
			$tblCDR->Align(Array('Right', 'Right', 'Right', 'Right', 'Right', 'Right', '', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right', 'Right'));
			foreach($arrCDRs AS $arrCDR)
			{
				$intMaxId = max($intMaxId, $arrCDR['Id']);
				$arrRow = Array($arrCDR['Id'], $arrCDR['Account'], $arrCDR['Service'], $arrCDR['FNN'], $arrCDR['Source'], $arrCDR['Destination'], $arrCDR['Description'], $arrCDR['Units'], $arrCDR['Cost'], $arrCDR['Charge'], $arrCDR['Credit'], $arrCDR['Rate'], $arrCDR['DestinationCode'], $arrCDR['ServiceType'], $arrCDR['RecordType'], $arrCDR['Status'], $arrCDR['Carrier'], $arrCDR['StartDatetime'], $arrCDR['EndDatetime']);
				$tblCDR->AddRow($arrRow, "cdr_view.php?Id={$arrCDR['Id']}");
			}
			$this->AddTable($tblCDR);
			
			// pagination ('previous' button won't work properly)
			$intPaginateStart = $intMaxId - $intLimit;
			$this->AddPagination("cdr_list.php", "Status=$intStatus", $intPaginateStart, $intLimit);
		}
		else
		{
			$this->AddError("NO CDRs FOUND");
			return FALSE;
		}
		return TRUE;
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
		//TODO!rich! show details of a rate
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
	
	// return a viXen/Etech invoice comparison
	function ShowEtechInvoice($strBillingPeriod)
	{
		// TODO
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
			$this->AddLink("invoice_view_etech.php?period=$strBillingPeriodURL", date("F Y", strtotime($arrInvoice['InvoiceRun'])));
		}
	}
	
	// return a viXen/Etech CDR comparison
	function ShowEtechCDR($intEtechCDR)
	{
		// Get the CDR
		if (!($arrCDR = $this->appMonitor->GetEtechCDR($intEtechCDR)))
		{
			$this->AddError("Cannot find Etech CDR with id $intEtechCDR");
			return;
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
	}
	
 }
 
?>
