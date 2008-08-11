<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// misc
//----------------------------------------------------------------------------//
/**
 * misc
 *
 * contains all miscellaneous Application functionality
 *
 * contains all miscellaneous Application functionality
 *
 * @file		misc.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateMisc
//----------------------------------------------------------------------------//
/**
 * AppTemplateMisc
 *
 * The AppTemplateMisc class
 *
 * The AppTemplateMisc class.
 *
 *
 * @package	ui_app
 * @class	AppTemplateMisc
 * @extends	ApplicationTemplate
 */
class AppTemplateMisc extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// MoveDelinquentCDRs
	//------------------------------------------------------------------------//
	/**
	 * MoveDelinquentCDRs()
	 *
	 * Builds the "Move Delinquent CDRs" webpage, which allows the user to assign Delinquent CDRs to the appropriate service 
	 * 
	 * Builds the "Move Delinquent CDRs" webpage, which allows the user to assign Delinquent CDRs to the appropriate service
	 *
	 * @return		void
	 * @method		MoveDelinquentCDRs
	 */
	function MoveDelinquentCDRs()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SetCurrentPage("Delinquent CDRs");
		
		$this->LoadPage('delinquent_cdrs_move');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetDelinquentFNNs
	//------------------------------------------------------------------------//
	/**
	 * GetDelinquentFNNs()
	 *
	 * Retrieves all the FNNs that have Delinquent CDRs with StartDatetime between that of the Date Range specified 
	 * 
	 * Retrieves all the FNNs that have Delinquent CDRs with StartDatetime between that of the Date Range specified
	 * It assumes the following data is passed:
	 * 		DBO()->Delinquents->StartDate		Date Range for the StartDatetime of the Delinquent CDRs
	 * 		DBO()->Delinquents->EndDate
	 *
	 * @return		void
	 * @method		GetDelinquentFNNs
	 */
	function GetDelinquentFNNs()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$strStartDate	= DBO()->Delinquents->StartDate->Value;
		$strEndDate		= DBO()->Delinquents->EndDate->Value;
		
		if (!(Validate("ShortDate", $strStartDate) && Validate("ShortDate", $strStartDate)))
		{
			Ajax()->AddCommand("Alert", "ERROR: Dates must be specified as dd/mm/yyyy");
			return TRUE;
		}
		
		$strStartDate = ConvertUserDateToMySqlDate($strStartDate);
		$strEndDate = ConvertUserDateToMySqlDate($strEndDate);
		
		$arrColumns = Array("FNN"					=>	"FNN",
							"ServiceType"			=>	"ServiceType",
							"Carrier"				=>	"Carrier",
							"TotalCost"				=>	"SUM(Cost)",
							"EarliestStartDatetime"	=>	"MIN(StartDatetime)",
							"LatestStartDatetime"	=>	"MAX(StartDatetime)",
							"Count"					=>	"Count(Id)");
		$strWhere = "Status = ". CDR_BAD_OWNER ." AND StartDatetime BETWEEN <StartDate> AND <EndDate>";
		$strOrderBy = "FNN, ServiceType, Carrier";
		$selDelinquentCDRs = new StatementSelect("CDR", $arrColumns, $strWhere, $strOrderBy, "", "FNN, ServiceType, Carrier");
		
		$mixResult = $selDelinquentCDRs->Execute(Array("StartDate" => $strStartDate, "EndDate" => $strEndDate));
		
		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving CDRs failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		$arrRecordSet = $selDelinquentCDRs->FetchAll();
		
		$arrFNNs = Array();
		
		foreach ($arrRecordSet as $arrRecord)
		{
			$strCarrier		= GetConstantDescription($arrRecord['Carrier'], "Carrier");
			$strTotalCost	= OutputMask()->MoneyValue($arrRecord['TotalCost']);
			$strEarliest	= date("d/m/Y", strtotime($arrRecord['EarliestStartDatetime']));
			$strLatest		= date("d/m/Y", strtotime($arrRecord['LatestStartDatetime']));
			
			// If the FNN is relating to an ADSL service then append the "i" to its description
			$strFNN = ($arrRecord['ServiceType'] == SERVICE_TYPE_ADSL) ? $arrRecord['FNN'] . "i" : $arrRecord['FNN']; 
			
			// Build the description
			$strDescription  = str_pad($strFNN, 13, " ", STR_PAD_RIGHT);
			$strDescription .= str_pad(substr($strCarrier, 0, 25), 26, " ", STR_PAD_RIGHT);
			$strDescription .= str_pad($strTotalCost, 11, " ", STR_PAD_LEFT);
			$strDescription .= str_pad($arrRecord['Count'], 10, " ", STR_PAD_LEFT);
			$strDescription .= str_pad($strEarliest, 15, " ", STR_PAD_LEFT) . "  -  ";
			$strDescription .= str_pad($strLatest, 10, " ", STR_PAD_LEFT);
			
			$arrFNNs[] = Array(	"FNN"			=> $arrRecord['FNN'],
								"ServiceType"	=> $arrRecord['ServiceType'],
								"Carrier"		=> $arrRecord['Carrier'],
								"Description"	=> $strDescription);
		}
		
		// Return this Array to the client
		AjaxReply($arrFNNs);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetDelinquentCDRs
	//------------------------------------------------------------------------//
	/**
	 * GetDelinquentCDRs()
	 *
	 * Retrieves all data required to assign Delinquent CDRs to Services for a given FNN/Carrier combination 
	 * 
	 * Retrieves all data required to assign Delinquent CDRs to Services for a given FNN/Carrier combination
	 * It assumes the following data is passed:
	 * 		DBO()->Delinquents->StartDate	Date Range for the StartDatetime of the Delinquent CDRs
	 * 		DBO()->Delinquents->EndDate
	 * 		DBO()->Delinquents->FNN			The FNN of the Delinquent CDRs
	 * 		DBO()->Delinquents->ServiceType	The ServiceType of the Delinquent CDRs
	 * 		DBO()->Delinquents->Carrier		The Carrier of the Delinquent CDRs
	 *
	 * @return		void
	 * @method		GetDelinquentCDRs
	 */
	function GetDelinquentCDRs()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$strStartDate	= ConvertUserDateToMySqlDate(DBO()->Delinquents->StartDate->Value);
		$strEndDate		= ConvertUserDateToMySqlDate(DBO()->Delinquents->EndDate->Value);
		$strFNN			= DBO()->Delinquents->FNN->Value;
		$intCarrier		= DBO()->Delinquents->Carrier->Value;
		$intServiceType	= DBO()->Delinquents->ServiceType->Value;
		
		// Retrieve all the CDRs
		$strWhere			= "Status = <BadOwner> AND FNN = <FNN> AND ServiceType = <ServiceType> AND Carrier = <Carrier> AND StartDatetime BETWEEN <StartDate> AND <EndDate>";
		$arrWhere			= Array(	"BadOwner"		=> CDR_BAD_OWNER,
										"FNN"			=> $strFNN,
										"ServiceType"	=> $intServiceType,
										"Carrier"		=> $intCarrier,
										"StartDate"		=> $strStartDate,
										"EndDate"		=> $strEndDate);
		$selDelinquentCDRs	= new StatementSelect("CDR", "Id, Cost, StartDatetime", $strWhere, "StartDatetime ASC, Id ASC");
		
		$mixResult = $selDelinquentCDRs->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving CDRs failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		// Retrieve all the possible Owners of the CDRs
		$strIndialFNN	= substr($strFNN, 0, -2) . "__";
		$arrColumns		= Array(	"Id"			=> "S.Id",
									"CreatedOn"		=> "S.CreatedOn",
									"ClosedOn"		=> "S.ClosedOn",
									"Indial100"		=> "S.Indial100",
									"Status"		=> "S.Status",
									"Account"		=> "A.Id",
									"AccountName"	=> "CASE WHEN A.BusinessName = \"\" THEN A.TradingName ELSE A.BusinessName END",
								);
								
		if ($intServiceType == SERVICE_TYPE_ADSL)
		{
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "S.FNN = '{$strFNN}i'";
		}
		else
		{
			$strWhere = "(S.FNN = <FNN> OR (S.Indial100 = TRUE AND S.FNN LIKE <IndialFNN>))";
		}
		
		$arrWhere	= Array("FNN" => $strFNN, "IndialFNN" => $strIndialFNN);
		$strTables	= "Service AS S INNER JOIN Account AS A ON S.Account = A.Id";
		$strOrderBy	= "(S.ClosedOn IS NULL) DESC, S.CreatedOn DESC";
		$selPossibleOwners = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "");
		
		$mixResult = $selPossibleOwners->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving potential owner services failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		// Prepare the data to be sent to the client
		$arrServices = Array();
		$arrRecordSet = $selPossibleOwners->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$strCreatedOn = substr($arrRecord['CreatedOn'], 8, 2) ."/". substr($arrRecord['CreatedOn'], 5, 2) ."/". substr($arrRecord['CreatedOn'], 0, 4);
			if ($arrRecord['ClosedOn'] != NULL)
			{
				$strClosedOn = substr($arrRecord['ClosedOn'], 8, 2) ."/". substr($arrRecord['ClosedOn'], 5, 2) ."/". substr($arrRecord['ClosedOn'], 0, 4);
			}
			else
			{
				$strClosedOn = "[Still Open]";
			}
			$strClosedOn = str_pad($strClosedOn, 12, " ", STR_PAD_RIGHT);
			
			$strServiceId = str_pad($arrRecord['Id'], 11, " ", STR_PAD_LEFT);
			
			$strAccountName = ($arrRecord['AccountName'] != "") ? substr($arrRecord['AccountName'], 0, 28) : "[No Name]";
			$strAccountName = str_pad($strAccountName, 28, " ", STR_PAD_RIGHT);
			if (strlen($arrRecord['AccountName']) > 28)
			{
				// Show that the account name has been truncacted
				$strAccountName = substr($strAccountName, 0, -3) . "...";
			}
			
			$strIndial = ($arrRecord['Indial100']) ? "(Indial) " : "         ";
			
			$strStatus = GetConstantDescription($arrRecord['Status'], "service_status");
			$strStatus = str_pad($strStatus, 13, " ", STR_PAD_RIGHT);
						
			// Build a description for the Service
			$strAccountDescription				= "{$arrRecord['Account']} - $strAccountName";
			$strDescription						= "{$arrRecord['Account']} $strAccountName $strIndial $strStatus $strCreatedOn - $strClosedOn $strServiceId";
			
			$arrRecord['Description']			= htmlspecialchars($strDescription, ENT_QUOTES);
			$arrRecord['AccountDescription']	= htmlspecialchars($strAccountDescription, ENT_QUOTES);
			$arrRecord['DateRange']				= "$strCreatedOn - $strClosedOn";
			
			$arrServices[$arrRecord['Id']] = $arrRecord;
		}
		
		// Process the retrieved CDRs
		$arrCDRs = Array();
		$arrRecordSet = $selDelinquentCDRs->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$strStartDatetime	= date("H:i:s d/m/Y", strtotime($arrRecord['StartDatetime']));
			$strCost			= OutputMask()->MoneyValue($arrRecord['Cost']);
			
			$arrCDRs[] = Array(	"Id"	=> $arrRecord['Id'],
								"Time"	=> $strStartDatetime,
								"Cost"	=> $strCost);
		}

		// Build the Html required of the Service Selector popup
		$strServiceSelectorHtml = $this->_RenderDelinquentCDRServiceSelector($arrServices);
		
		// Return data to the client
		$arrReturnData['Services']				= $arrServices;
		$arrReturnData['CDRs']					= $arrCDRs;
		$arrReturnData['ServiceSelectorHtml']	= $strServiceSelectorHtml;
		
		AjaxReply($arrReturnData);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// AssignCDRsToServices
	//------------------------------------------------------------------------//
	/**
	 * AssignCDRsToServices()
	 *
	 * Assigns the passed delinquent CDRs to their respective Services 
	 * 
	 * Assigns the passed delinquent CDRs to their respective Services
	 * It assumes the following data is passed:
	 * 		DBO()->Delinquents->FNN			The FNN of the Delinquent CDRs
	 * 		DBO()->Delinquents->Carrier		The Carrier of the Delinquent CDRs
	 * 		DBO()->Delinquents->ServiceType	The ServiceType of the Delinquent CDRs
	 * 		DBO()->Delinquents->CDRs		array of objects of the form:
	 * 											arrCDRs[i]->Id		: CDR's Id
	 * 											arrCDRs[i]->Service	: Id of the Service to assign the CDR to
	 * 											arrCDRs[i]->Record	: The record number that the CDR is assigned in the table on the Delinquent CDRs webpage
	 *
	 * @return		void
	 * @method		AssignCDRsToServices
	 */
	function AssignCDRsToServices()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$strFNN			= DBO()->Delinquents->FNN->Value;
		$intCarrier		= DBO()->Delinquents->Carrier->Value;
		$intServiceType	= DBO()->Delinquents->ServiceType->Value;
		$arrCDRs		= DBO()->Delinquents->CDRs->Value;
		
		$arrSuccessfulCDRs = Array();
		
		// Retrieve all possible owners for the CDRs
		if ($intServiceType == SERVICE_TYPE_ADSL)
		{
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "FNN = '{$strFNN}i'";
		}
		else
		{
			$strWhere = "(FNN = <FNN> OR (Indial100 = TRUE AND FNN LIKE <IndialFNN>))";
		}
		
		$strIndialFNN	= substr($strFNN, 0, -2) . "__";
		$selServices	= new StatementSelect("Service", "*", $strWhere);
		if ($selServices->Execute(Array("FNN"=>$strFNN, "IndialFNN"=>$strIndialFNN)) === FALSE)
		{
			$arrReturnObject["Success"]		= FALSE;
			$arrReturnObject["ErrorMsg"]	= "ERROR: Retrieving the services from the database failed, unexpectedly. Operation aborted.  Please notify your system administrator";
			AjaxReply($arrReturnObject);
			return TRUE;
		}
		$arrRecordSet	= $selServices->FetchAll();
		$arrServices	= Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrServices[$arrRecord['Id']]									= $arrRecord;
			$arrServices[$arrRecord['Id']]['NextBillDate']					= GetNextBillDate($arrRecord['Account']);
			$arrServices[$arrRecord['Id']]['EarliestAllowableCDRStartDate']	= date("Y-m-d", strtotime("-185 days ". $arrServices[$arrRecord['Id']]['NextBillDate']));
		}
		
		// Build the Database objects required
		$selCDR = new StatementSelect("CDR", "Id, FNN, Service, Account, AccountGroup, Status, StartDatetime", "Id = <Id>");
		
		$arrUpdateColumns = Array("Service"=>NULL, "Account"=>NULL, "AccountGroup"=>NULL, "Status"=>NULL);
		$updCDR = new StatementUpdateById("CDR", $arrUpdateColumns);
		
		// Process the CDRs
		$strErrorMsg = "";
		TransactionStart();
		foreach ($arrCDRs as $objCDR)
		{
			if (!isset($arrServices[$objCDR->Service]))
			{
				// The Service to assign the CDR to is not in the list of allowable services (it must have a different FNN)
				$strErrorMsg = "ERROR: Could not find the assigned service for record {$objCDR->Record}. Operation aborted.";
				break;
			}
			$arrService = $arrServices[$objCDR->Service];
			
			// Retrieve the CDR record
			if ($selCDR->Execute(Array("Id" => $objCDR->Id)) != 1)
			{
				// Could not retrieve the CDR record
				$strErrorMsg = "ERROR: Could not retrieve CDR {$objCDR->Record} from the database (CDR Id = {$objCDR->Id}). Operation aborted.  Please notify your system administrator";
				break;
			}
			$arrCDRRecord = $selCDR->Fetch();
			
			$strStartDate = substr($arrCDRRecord['StartDatetime'], 0, 10);
			
			// Check that the CDR's StartDatetime is within 185 days of the next bill date of the account that the CDR will be allocated to
			if ($strStartDate < $arrService['EarliestAllowableCDRStartDate'])
			{
				// CDR is too old
				$strStartTime = date("H:i:s d/m/Y", strtotime($arrCDRRecord['StartDatetime']));
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} with start time: $strStartTime is considered too old to be billed to this customer.  Operation aborted.";
				break;
			}
			
			// Check the FNNs match
			if ($strFNN != $arrCDRRecord['FNN'])
			{
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have FNN $strFNN. Operation Aborted.";
				break;
			}
			
			// Check the FNN has Status == CDR_BAD_OWNER
			if ($arrCDRRecord['Status'] != CDR_BAD_OWNER)
			{
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have 'Bad Owner' status.  Operation Aborted.";
				break;
			}
			
			// Everything is valid.  Update the FNN
			$arrUpdateColumns['Id']				= $objCDR->Id;
			$arrUpdateColumns['Service']		= $arrService['Id'];
			$arrUpdateColumns['Account']		= $arrService['Account'];
			$arrUpdateColumns['AccountGroup']	= $arrService['AccountGroup'];
			$arrUpdateColumns['Status']			= CDR_NORMALISED;
			
			if ($updCDR->Execute($arrUpdateColumns) === FALSE)
			{
				// Updating the CDR failed
				$strErrorMsg = "ERROR: Updating the CDR {$objCDR->Record} (CDR Id: $objCDR->Id) failed, unexpectedly.  Operation Aborted.  Please notify your system administrator.";
				break;
			}
			
			// Add the CDR to the list of successfully owned CDRs
			$arrSuccessfulCDRs[] = $objCDR->Id;
		}
		
		if ($strErrorMsg != "")
		{
			// An error occurred
			TransactionRollback();
			$arrReturnObject["Success"]		= FALSE;
			$arrReturnObject["ErrorMsg"]	= $strErrorMsg;
		}
		else
		{
			// Everything worked out
			TransactionCommit();
			$arrReturnObject["Success"]			= TRUE;
			$arrReturnObject["SuccessfulCDRs"]	= $arrSuccessfulCDRs;			
		}
		
		AjaxReply($arrReturnObject);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// _RenderDelinquentCDRServiceSelector
	//------------------------------------------------------------------------//
	/**
	 * _RenderDelinquentCDRServiceSelector()
	 *
	 * Compiles the HTML code required of the Service Selector Popup, to assign CDRs to a Service 
	 * 
	 * Compiles the HTML code required of the Service Selector Popup, to assign CDRs to a Service
	 *
	 * @param	array	$arrServices	Contains all required data to describe a service
	 *
	 * @return	string					html code to be used as the contents of the Service Selector Popup
	 * @method	_RenderDelinquentCDRServiceSelector
	 */
	private function _RenderDelinquentCDRServiceSelector($arrServices)
	{
		// Build all the options
		$strOptions = "<option value='0' style='color:red;'>[No Service Specified]</option>";
		foreach ($arrServices as $arrService)
		{
			$strTitle = "";
			if ($arrServices['CompanyName'] != "")
			{
				$strTitle = "title='". htmlspecialchars($arrServices['CompanyName'], ENT_QUOTES) ."'";
			}
			 
			$strOptions .= "<option value='{$arrService['Id']}' $strTitle style='white-space:pre'>{$arrService['Description']}</option>";
		}
		
		$strHtml = "<div id='PopupPageBody'>
						<div class='GroupedContent'>
							<span style='white-space:pre;font-family:Courier New, monospace;padding-left:4px'>Account    Name                                   Status        Created      Closed         ServiceId</span>
							<select id='ServiceSelectorControl' size='6' style='width:100%; border-color:#D1D1D1; font-family:Courier New, monospace' onDblClick='Vixen.DelinquentCDRs.SetService(this.value)'>$strOptions</select>
						</div>
						<div class='ButtonContainer'>
							<div class='Right'>
								<input type='button' value='Cancel' onClick='Vixen.Popup.Close(this)' />
							</div>
						</div>
					</div>";
					
		return $strHtml;
	}

    //----- DO NOT REMOVE -----//
}
?>
