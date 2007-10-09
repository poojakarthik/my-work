<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanList
 *
 * A specific HTML Template object
 *
 * An Plan HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanList
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanList extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_LIST_DETAIL:
				$this->RenderListDetail();
				break;
			default:
				$this->RenderDefault();
				break;
		}
	}

	function RenderListDetail()
	{
		// Render each of the account invoices
		echo "<div class='PopupLarge'>\n";
			echo "<div  style='overflow:auto; height:300px'>\n";
				//echo "<div class='NarrowColumn'>\n";
				
				//$this->Temporary_Render();
				
				Table()->RateGroupTable->SetHeader("Charges");
				Table()->RateGroupTable->SetAlignment("Left");
				
				foreach (DBL()->RatePlanRateGroup as $dboRateGroup)
				{
					Table()->RateGroupTable->AddRow($dboRateGroup->RateGroupName->AsValue());	
					
					$strWhere = "Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroupId>)";
					DBL()->Rate->Where->Set($strWhere, Array('RateGroupId' => $dboRateGroup->RateGroupId->Value));
					DBL()->Rate->Load();
			
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
				
				Table()->RateGroupTable->SetDetail($strDetailHtml);	
					
				}
				Table()->RateGroupTable->Render();
				//echo "</div>\n";
				//echo "<div class='Seperator'></div>\n";
		echo "</div>\n";		
				
				echo "<div class='Right'>\n";
					$this->Button("Close", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
				echo "</div>\n";
				
				//echo "</div>\n";
		echo "</div>\n";
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
	function RenderDefault()
	{
		// RatePlan filtering functionality is not currently implemented
		// Set up Payment Type combobox
		
		// Add the Filter Combobox
		$strOnFilterChange = "window.location=\"vixen.php/Plan/AvailablePlans/?RatePlan.ServiceType=\" + this.value;";
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Filter :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='FilterCombo' onchange='$strOnFilterChange'>\n";
		// Add the blank option to the Filter combobox
		$strSelected = (!DBO()->RatePlan->ServiceType->Value) ? "selected='selected'" : "";
		echo "         <option value='0' $strSelected>All Rate Plans</option>";
		
		// Add each ServiceType to the Filter combobox
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{
			$strDescription = $arrServiceType['Description'];
			$strSelected = (DBO()->RatePlan->ServiceType->Value == $intServiceType) ? "selected='selected'" : "";
			echo "         <option value='$intServiceType' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";		

		// Render the "Add New Plan" button
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Add New Plan", "window.location=\"" . Href()->AddRatePlan(NULL, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value)) . "\"");
		echo "</div></div>\n";
		echo "<div class='SmallSeperator'></div>";

		Table()->PlanTable->SetHeader("Type", "Name", "Description", "Shared", "Min Monthly", "Charge Cap", "Usage Cap", "Carrier Full Service", "Carrier Pre selection", "Status", "&nbsp;", "&nbsp;");
		Table()->PlanTable->SetWidth("8%", "20%", "8%", "8%", "8%", "8%", "8%", "8%", "8%", "8%", "4%", "4%");
		Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Left", "Right", "Right", "Right", "Left", "Left", "Left", "Center", "Center");

		foreach (DBL()->RatePlan as $dboRatePlan)
		{
			// Build the Edit Rate Plan link, if the RatePlan is currently a draft
			$strEditCell = "&nbsp;";
			if ($dboRatePlan->Archived->Value == ARCHIVE_STATUS_DRAFT)
			{
				$strEditPlanLink	= Href()->EditRatePlan($dboRatePlan->Id->Value, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value));
				$strEditCell		= "<a href='$strEditPlanLink' title='Edit'><span class='DefaultOutputSpan'>Edit</span></a>";
			}
			
			// Build the "Add Rate Plan Based On Existing" link
			$strAddPlanLink	= Href()->AddRatePlan($dboRatePlan->Id->Value, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value));
			$strAddCell = "<a href='$strAddPlanLink' title='Create a new plan based on this one'><span class='DefaultOutputSpan'>New</span></a>";
			
			// Workout the status of the Rate Plan
			// Note these constants will eventually be declared in vixen/framework/definitions and you will be able to use the GetConstantDescription() function
			switch ($dboRatePlan->Archived->Value)
			{
				case ARCHIVE_STATUS_ACTIVE:
					$strStatusCell = "<span class='DefaultOutputSpan'>Active</span>";
					break;
				case ARCHIVE_STATUS_DRAFT:
					$strStatusCell = "<span class='DefaultOutputSpan'>Draft</span>";
					break;
				default:
					$strStatusCell = "Value = " . $dboRatePlan->Archived->Value;
			}
			
			// Format the RatePlan->Shared boolean
			$strSharedCell = ($dboRatePlan->Shared->Value) ? "Yes" : "No";
			$strSharedCell = "<span class='DefaultOutputSpan'>$strSharedCell</span>";
			
			// Add the Rate Plan to the VixenTable
			Table()->PlanTable->AddRow(	$dboRatePlan->ServiceType->AsCallBack("GetConstantDescription", Array('ServiceType')),
										$dboRatePlan->Name->AsValue(),
										$dboRatePlan->Description->AsValue(),
										$strSharedCell,
										$dboRatePlan->MinMonthly->AsValue(),
										$dboRatePlan->ChargeCap->AsValue(),
										$dboRatePlan->UsageCap->AsValue(),
										$dboRatePlan->CarrierFullService->AsCallBack("GetConstantDescription", Array('Carrier')),
										$dboRatePlan->CarrierPreselection->AsCallBack("GetConstantDescription", Array('Carrier')),
										$strStatusCell,
										$strAddCell,
										$strEditCell);									
		}
		
		// Check if the table is empty
		if (Table()->PlanTable->RowCount() == 0)
		{
			// There are no RatePlans to stick in this table
			Table()->PlanTable->AddRow("<span class='DefaultOutputSpan Default'>No Rate Plans to display</span>");
			Table()->PlanTable->SetRowAlignment("left");
			Table()->PlanTable->SetRowColumnSpan(12);
		}
		
		Table()->PlanTable->Render();
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Add New Plan", "window.location=\"" . Href()->AddRatePlan(NULL, Href()->AvailablePlans()) . "\"");
		echo "</div></div>\n";
	}
}

?>
