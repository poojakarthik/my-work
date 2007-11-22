<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// grouplist.php DEPRICATED
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
		//$this->LoadJavascript("tooltip");
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
	function Render()
	{	
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Rate Groups</h2>\n";
		//echo "<div class='NarrowColumn'>\n";
		
		//$this->Temporary_Render();
		
		Table()->ServiceRateGroupTable->SetHeader("Record Type", "Rate Group", "Fleet", "Std Rate", "Start Date", "End Date");
		Table()->ServiceRateGroupTable->SetWidth("30%", "30%", "10%", "10%", "10%", "10%");
		Table()->ServiceRateGroupTable->SetAlignment("Left", "Left", "Left", "Center", "Left", "Left");
		
		foreach (DBL()->ServiceRateGroup as $dboServiceRateGroup)
		{
		
			// match the RecordType Id with the Id in the ServiceRateGroup table
			// and setup to display the actual RecordType not a number
			DBO()->RecordType->Id = $dboServiceRateGroup->RecordType->Value;
			DBO()->RecordType->Load();
			// Add this row to Invoice table

			if ($dboServiceRateGroup->EndDatetime->Value == END_OF_TIME)
			{
				$strEndDatetime = "<span>Indefinate</span>";
			}
			else
			{
				$strEndDatetime = $dboServiceRateGroup->EndDatetime->AsValue();
			}
			
			Table()->ServiceRateGroupTable->AddRow(DBO()->RecordType->Name->AsValue(),
												$dboServiceRateGroup->Name->AsValue(), 
												$dboServiceRateGroup->Fleet->AsValue(),
												$dboServiceRateGroup->IsPartOfRatePlan->AsValue(),
												$dboServiceRateGroup->StartDatetime->AsValue(),
												$strEndDatetime);
			
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
				$strDetailHtml .= "<tr><td colspan='5'><span>Rate Group Description : " . $dboServiceRateGroup->Description->AsValue() . "</span></td></tr>\n";
				$strDetailHtml .= "<tr bgcolor='#C0C0C0'><td><font size='2'>Rate Name</font></td><td><font size='2'>Description</font></td><td><font size='2'>Days Available</font></td><td><font size='2'>Start Time</font></td><td><font size='2'>End Time</font></td></tr>\n";
				
				foreach (DBL()->Rate as $dboRate)
				{
					$strViewRateLink = Href()->ViewRate($dboRate->Id->Value);
				
					//removed the fixed width of the table rows as without they automatically resize and look cleaner
				
					$strDetailHtml .= "   <tr>\n";
					$strDetailHtml .= "      <td width='27%'>\n";
					$strDetailHtml .= "<a href='$strViewRateLink'>" . $dboRate->Name->AsValue() . "</a>";
					//$strDetailHtml .= $dboRate->Name->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "		<td width='37%'>" . $dboServiceRateGroup->Description->AsValue() . "</td>";
					$strDetailHtml .= "      <td>\n";
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
					$strDetailHtml .= "      <td width='8%'>\n";
					$strDetailHtml .= $dboRate->StartTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "      <td width='8%'>\n";
					$strDetailHtml .= $dboRate->EndTime->AsValue();
					$strDetailHtml .= "      </td>\n";
					$strDetailHtml .= "   </tr>\n";
				}
				
				//$strDetailHtml .= "<tr><td colspan='4'><a href='javascript:Vixen.Popup.ShowAjaxPopup(\"ViewRatePopupId\", \"large\", null, \"Rate\", \"View\", {\"Objects\":{\"Rate\":{\"Id\":12}}})'>Temporary Link Click here</a></td></tr>\n";
				
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

		if (DBL()->ServiceRateGroup->RecordCount() == 0)
		{
			// There are no invoices to stick in this table
			Table()->ServiceRateGroupTable->AddRow("<span class='DefaultOutputSpan Default'>No Rate Groups to display</span>");
			Table()->ServiceRateGroupTable->SetRowAlignment("left");
			Table()->ServiceRateGroupTable->SetRowColumnSpan(6);
		}

		Table()->ServiceRateGroupTable->Render();
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
