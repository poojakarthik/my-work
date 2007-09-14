<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// grouplist.php
//----------------------------------------------------------------------------//
/**
 * grouplist
 *
 * HTML Template for the Group List HTML object
 *
 * HTML Template for the Group List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all rategroups relating to a service and can be embedded in
 * various Page Templates
 *
 * @file		grouplist.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateGroupList
 *
 * HTML Template class for the Group List HTML object
 *
 * HTML Template class for the Group List HTML object
 * Lists all rategrops related to a service
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
		$this->LoadJavascript("tooltip");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	//function Render()
	//{
		
		
	//}

	/*function Temporary_Render()
	{
		$arrRatePlanRateGroupColumns = Array("RateGroupId"=>"RateGroup.Id", "RateGroupName"=>"RateGroup.Name", "RateGroupDescription"=>"RateGroup.Description", "RateGroupRecordType"=>"RateGroup.RecordType");
		$selRatePlanRateGroup = new StatementSelect("RateGroup, RatePlanRateGroup", $arrRatePlanRateGroupColumns, "RateGroup.Id=RatePlanRateGroup.RateGroup AND RatePlanRateGroup.RatePlan = (SELECT RatePlan FROM ServiceRatePlan WHERE NOW( ) BETWEEN StartDatetime AND EndDatetime AND Service =<Service> ORDER BY CreatedOn DESC LIMIT 0, 1)", "RateGroupId");
		$selRatePlanRateGroup->Execute(Array('Service' => DBO()->Service->Id->Value));
		$arrRatePlanRateGroups = $selRatePlanRateGroup->FetchAll();

		$arrServiceRateGroupColumns = Array("RateGroupId"=>"RateGroup.Id", "RateGroupName"=>"RateGroup.Name", "RateGroupDescription"=>"RateGroup.Description", "RateGroupRecordType"=>"RateGroup.RecordType");
		$selServiceRateGroup = new StatementSelect("RateGroup, ServiceRateGroup", $arrServiceRateGroupColumns, "NOW() BETWEEN StartDatetime AND EndDatetime AND RateGroup.Id = ServiceRateGroup.RateGroup AND ServiceRateGroup.Service=<Service>", "RateGroup.Id");
		$selServiceRateGroup->Execute(Array('Service' => DBO()->Service->Id->Value));
		$arrServiceRateGroups = $selServiceRateGroup->FetchAll();
		
		// Loop through each RateGroup belonging to the Service and find out which ones actually belong to the RatePlan and which ones are OverRiders
		foreach ($arrServiceRateGroups as &$arrServiceRateGroup)
		{
			// initialise the "IsPartOfRatePlan" flag to FALSE
			$arrServiceRateGroup['IsPartOfRatePlan'] = FALSE;
			
			// Try and find the ServiceRateGroup in the list of RateGroups belonging to the RatePlan
			foreach ($arrRatePlanRateGroups as $arrRatePlanRateGroup)
			{
				if ($arrServiceRateGroup['RateGroupId'] == $arrRatePlanRateGroup['RateGroupId'])
				{
					// This RateGroup belongs to the RatePlan; flag it as such
					$arrServiceRateGroup['IsPartOfRatePlan'] = TRUE;
					break;
				}
			}
		}
	}*/

	//------------------------------------------------------------------------//
	// _RenderNormalDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderNormalDetail()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{	
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Rate Groups</h2>\n";
		//echo "<div class='NarrowColumn'>\n";
		
		//$this->Temporary_Render();
		
		Table()->ServiceRateGroupTable->SetHeader("Rate Group", "Description", "Fleet", "Record Type", "Part Of RatePlan");
		Table()->ServiceRateGroupTable->SetWidth("25%", "35%", "5%", "20%", "15%");
		Table()->ServiceRateGroupTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		
		foreach (DBL()->ServiceRateGroup as $dboServiceRateGroup)
		{
			// match the RecordType Id with the Id in the ServiceRateGroup table
			// and setup to display the actual RecordType not a number
			DBO()->RecordType->Id = $dboServiceRateGroup->RecordType->Value;
			DBO()->RecordType->Load();
			// Add this row to Invoice table
			Table()->ServiceRateGroupTable->AddRow($dboServiceRateGroup->Name->AsValue(), 
												$dboServiceRateGroup->Description->AsValue(), 
												$dboServiceRateGroup->Fleet->AsValue(),
												DBO()->RecordType->Name->AsValue(),
												$dboServiceRateGroup->IsPartOfRatePlan->AsValue());
			
			//Retrieve the Rate information for this RateGroup
			$strWhere = "Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroupId>)";
			DBL()->Rate->Where->Set($strWhere, Array('RateGroupId' => $dboServiceRateGroup->Id->Value));
			DBL()->Rate->SetLimit(11);
			DBL()->Rate->Load();
			if (DBL()->Rate->RecordCount() <= 10)
			{
				// Add the rate information to the DropDown div for the row
				// Set the drop down detail
				$strDetailHtml = "<div class='VixenTableDetail'>\n";
				$strDetailHtml .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
				$strDetailHtml .= "<tr bgcolor='#C0C0C0'><td><font size='2'>Rate Name</font></td><td><font size='2'>Days Available</font></td><td><font size='2'>Start Time</font></td><td><font size='2'>End Time</font></td></tr>\n";
				
				foreach (DBL()->Rate as $dboRate)
				{
					$strDetailHtml .= "   <tr>\n";
					$strDetailHtml .= "      <td width='35%'>\n";
					$strDetailHtml .= $dboRate->Name->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td width='35%'>\n";
					$strDetailHtml .= $dboRate->Monday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Tuesday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Wednesday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Thursday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Friday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Saturday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "&nbsp;";
					$strDetailHtml .= $dboRate->Sunday->AsValue(CONTEXT_DEFAULT,TRUE);
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->StartTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td>\n";
					$strDetailHtml .= $dboRate->EndTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					
					$strDetailHtml .= "   </tr>\n";
				}
				$strDetailHtml .= "</table>\n";
				$strDetailHtml .= "</div>\n";
				
				Table()->ServiceRateGroupTable->SetDetail($strDetailHtml);	
			}
			else
			{
				// there is more than 10 rate plans shown
				$intRateGroupId = $dboServiceRateGroup->Id->Value;
				
				$strOnClick = "javascript:
							var objObject = {};
							objObject.Objects = {};
							objObject.Objects.RateGroup = {};
							objObject.Objects.Rate = {};
							objObject.Objects.Rate.SearchString = document.getElementById('SearchString_$intRateGroupId').value;
							objObject.Objects.RateGroup.Id = $intRateGroupId;
							Vixen.Popup.ShowAjaxPopup('RateGroupSearchId', 'large', null, 'Service', 'ViewRates', objObject);
							";
				
				$strBasicDetailHtml =  "<div class='VixenTableDetail'>\n";
				$strBasicDetailHtml .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
				$strBasicDetailHtml .= "	<tr>\n";
				$strBasicDetailHtml .= "		<td width='15%' alignment='left'>\n";
				$strBasicDetailHtml .= "			<span class='DefaultOutputSpan Default'>Search through rates:</span>";
				$strBasicDetailHtml .= "		</td>\n";
				$strBasicDetailHtml .= "		<td width='25%' alignment='left'>\n";			
				$strBasicDetailHtml .= "			<input type=text size=10 id='SearchString_$intRateGroupId' class='DefaultOutputSpan Default' style='width:200px'>\n";
				$strBasicDetailHtml .= "		</td>\n";
				$strBasicDetailHtml .= "		<td width='60%' alignment='left'>\n";
				$strBasicDetailHtml .= "			<input type='button' value='View Rates' class='InputSubmit' onclick=\"$strOnClick\"></input>\n";
				$strBasicDetailHtml .= "		</td>\n";
				$strBasicDetailHtml .= "	</tr>\n";					
				$strBasicDetailHtml .= "</table>\n";
				$strBasicDetailHtml .= "</div>\n";
				
				Table()->ServiceRateGroupTable->SetDetail($strBasicDetailHtml);
			}
		}
		
		Table()->ServiceRateGroupTable->Render();
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
