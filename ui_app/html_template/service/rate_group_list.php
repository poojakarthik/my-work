<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_list.php
//----------------------------------------------------------------------------//
/**
 * rate_group_list
 *
 * HTML Template for the ServiceRateGroupList HTML object
 *
 * HTML Template for the ServiceRateGroupList HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all RateGroups relating to a service
 *
 * @file		rate_group_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceRateGroupList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceRateGroupList
 *
 * HTML Template for the ServiceRateGroupList HTML object
 *
 * HTML Template for the ServiceRateGroupList HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceRateGroupList
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceRateGroupList extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
		
		// Setup the object used to retrieve Rates for a given RateGroup
		DBL()->Rate->Where->SetString("Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = <RateGroup>)");
		DBL()->Rate->OrderBy("Name");
		DBL()->Rate->SetLimit(11);
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
		$intNow = strtotime(GetCurrentDateAndTimeForMySQL());
		$strStandardHeaderCell	= "<span title='RateGroup is standard Part Of Plan'>PoP</span>";
		$strFleetHeaderCell		= "<span title='Fleet Rates take precedence over normal Rates'>Fleet</span>";
	
		echo "<h2 class='Plan'>Rate Groups</h2>\n";
		
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$bolHasFleetRateGroups = FALSE;
			
			// Build the name of the table
			$strTableName = "RecordType_{$dboRecordType->Id->Value}";
			
			// Build the link to the RateGroup Override popup
			$strOverrideRateGroupHref	= Href()->OverrideRateGroup(DBO()->Service->Id->Value, $dboRecordType->Id->Value);
			$strRecordTypeCell			= "<a href='$strOverrideRateGroupHref' title='Declare Override RateGroup'><span>{$dboRecordType->Description->Value}</span></a>";
			
			
			
			Table()->$strTableName->SetHeader("&nbsp;", $strRecordTypeCell, $strStandardHeaderCell, $strFleetHeaderCell, "&nbsp;", "&nbsp", "&nbsp;");
			Table()->$strTableName->SetWidth("3%", "65%", "5%", "5%", "10%", "2%", "10%");
			Table()->$strTableName->SetAlignment("Left", "Left", "Center", "Center", "Left", "Left", "Left");
			
			// Find the RatePlan's Standard RateGroup and Standard Fleet RateGroup for this RecordType
			$dboStandardRateGroup		= NULL;
			$dboStandardFleetRateGroup	= NULL;
			foreach (DBL()->CurrentPlanRateGroup as $dboRateGroup)
			{
				if ($dboRecordType->Id->Value == $dboRateGroup->RecordType->Value)
				{	
					if ($dboRateGroup->Fleet->Value)
					{
						// We have found the Plan's Fleet Rate Group for this RecordType
						$dboStandardFleetRateGroup	= $dboRateGroup;
					}
					else
					{
						// We have found the Plan's standard Rate Group for this RecordType
						$dboStandardRateGroup		= $dboRateGroup;
					}
				}
			}
			
			// List all the Fleet Rate Groups for this record type in Descending order of precedence (CreateOn determines precedence)
			$intPrecedence = 1;
			$bolFoundCurrentFleetRateGroup = FALSE;
			foreach (DBL()->CurrentServiceRateGroup as $dboRateGroup)
			{
				// Make sure that the RateGroup relates to this RecordType and IS Fleet
				if ($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value && $dboRateGroup->Fleet->Value)
				{
					// The RateGroup is a fleet RateGroup and is of the correct RecordType
					// Initilise variables
					$bolIsCurrent		= FALSE;
					
					// Check if it is the RateGroup that is currently in use
					if (!$bolFoundCurrentFleetRateGroup && strtotime($dboRateGroup->StartDatetime->Value) <= $intNow)
					{
						// This RateGroup is currently being used
						$bolFoundCurrentFleetRateGroup	= TRUE;
						$bolIsCurrent					= TRUE;
					}
					
					// Check if the RateGroup is a standard part of the plan
					$strPartOfPlanCell = "<span>&nbsp;</span>";  // Default
					if ($dboRateGroup->RateGroup->Value == $dboStandardFleetRateGroup->Id->Value)
					{
						// The RateGroup is a standard part of the plan
						$strPartOfPlanCell = "<span><img src='img/template/tick.png' title='This is the standard Fleet RateGroup for this Plan'></img></span>";
					}
					
					// Prepare the Start Cell
					$intStart		= strtotime($dboRateGroup->StartDatetime->Value);
					$strStartTime	= date("g:i:s A", $intStart);
					$strStartDate	= "<span title='$strStartTime'>". date("M j, Y", $intStart) ."</span>";
					
					// Prepare the End Cell
					if ($dboRateGroup->EndDatetime->Value == END_OF_TIME)
					{
						$strEndDate = "<span>Indefinite</span>";
					}
					else
					{
						$intEnd		= strtotime($dboRateGroup->EndDatetime->Value);
						$strEndTime	= date("g:i:s A", $intEnd);
						$strEndDate	= "<span title='$strEndTime'>". date("M j, Y", $intEnd) ."</span>";
					}
					
					// Prepare the RateGroup Cell
					$strRateGroupCell  = "<span ". (($bolIsCurrent) ? "class='Green' " : "");
					$strRateGroupCell .= "title='{$dboRateGroup->Description->Value}'>{$dboRateGroup->Name->Value}</span>";
					
					// Add the Row
					Table()->$strTableName->AddRow(	"<span>$intPrecedence</span>", $strRateGroupCell, $strPartOfPlanCell, "<span><img src='img/template/tick.png'></img></span>", $strStartDate, "<span>-</span>", $strEndDate);
					
					// Add Dropdown details (describing the rates of the RateGroup)
					$strDropDownDetail = $this->_BuildDropDownDetail($dboRateGroup->RateGroup->Value);
					Table()->$strTableName->SetDetail($strDropDownDetail);
					
					// Increment the precedence counter
					$intPrecedence++;
				}
			}
			
			// List all the Normal Rate Groups for this record type in Descending order of precedence (CreateOn determines precedence)
			$bolFoundCurrentNormalRateGroup = FALSE;
			foreach (DBL()->CurrentServiceRateGroup as $dboRateGroup)
			{
				// Make sure that the RateGroup relates to this RecordType and is not Fleet
				if ($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value && $dboRateGroup->Fleet->Value == 0)
				{
					// The RateGroup is a standard RateGroup and is of the correct RecordType
					// Initilise variables
					$bolIsCurrent		= FALSE;
					
					// Check if it is the RateGroup that is currently in use
					if (!$bolFoundCurrentNormalRateGroup && strtotime($dboRateGroup->StartDatetime->Value) <= $intNow)
					{
						// This RateGroup is currently being used
						$bolFoundCurrentNormalRateGroup	= TRUE;
						$bolIsCurrent					= TRUE;
						//$strCurrentFlag					= "<span>Currently Used&nbsp;</span>";
					}
					
					// Check if the RateGroup is a standard part of the plan
					$strPartOfPlanCell = "<span>&nbsp;</span>";  // Default
					if ($dboRateGroup->RateGroup->Value == $dboStandardRateGroup->Id->Value)
					{
						// The RateGroup is a standard part of the plan
						$strPartOfPlanCell = "<span><img src='img/template/tick.png'></img></span>";
					}
					
					// Prepare the Start Cell
					$intStart		= strtotime($dboRateGroup->StartDatetime->Value);
					$strStartTime	= date("g:i:s A", $intStart);
					$strStartDate	= "<span title='$strStartTime'>". date("M j, Y", $intStart) ."</span>";
					
					// Prepare the End Cell
					if ($dboRateGroup->EndDatetime->Value == END_OF_TIME)
					{
						$strEndDate = "<span>Indefinite</span>";
					}
					else
					{
						$intEnd		= strtotime($dboRateGroup->EndDatetime->Value);
						$strEndTime	= date("g:i:s A", $intEnd);
						$strEndDate	= "<span title='$strEndTime'>". date("M j, Y", $intEnd) ."</span>";
					}
					
					// Prepare the RateGroup Cell
					$strRateGroupCell  = "<span ". (($bolIsCurrent) ? "class='Green' " : "");
					$strRateGroupCell .= "title='{$dboRateGroup->Description->Value}'>{$dboRateGroup->Name->Value}</span>";
					
					// Add the Row
					Table()->$strTableName->AddRow(	"<span>$intPrecedence</span>", $strRateGroupCell, $strPartOfPlanCell, "<span>&nbsp;</span>", $strStartDate, "<span>-</span>", $strEndDate);
					
					// Add Dropdown details (describing the rates of the RateGroup)
					$strDropDownDetail = $this->_BuildDropDownDetail($dboRateGroup->RateGroup->Value);
					Table()->$strTableName->SetDetail($strDropDownDetail);
					
					// Increment the precedence counter
					$intPrecedence++;
				}
			}
			
			
			// Check if there were any Normal RateGroups added to the table
			if (!$bolFoundCurrentNormalRateGroup)
			{
				// Check if the RecordType is a required RecordType
				if ($dboRecordType->Required->Value)
				{
					// A Normal RateGroup is required
					$strMessage = "<span class='Red'>Currently there is no active, normal RateGroup for this call type, yet one is required.  Please perform a RateGroup Override.</span>";
				}
				else
				{
					$strMessage = "<span>Currently there is no active, normal RateGroup for this call type.  It is not required that you specify one.</span>";
				}
				
				Table()->$strTableName->AddRow("<span>&nbsp;</span>", $strMessage);
				Table()->$strTableName->SetRowColumnSpan(1, 5);
			}
			
			// Draw the table
			Table()->$strTableName->Render();
			echo "<div class='TinySeperator'></div>\n";
		}
		
		
		
		echo "<div class='Seperator'></div>\n";
		
		
		// Render the js function used by the Rate Search controls
		$strJsCode = "	function VixenOpenRatesSearchResultsPopup(intRateGroupId, strSearchStringTextboxId)
						{
							var objObject = {};
							objObject.Objects = {};
							objObject.Objects.RateGroup = {};
							objObject.Objects.Rate = {};
							objObject.Objects.Rate.SearchString = document.getElementById(strSearchStringTextboxId).value;
							objObject.Objects.RateGroup.Id = intRateGroupId;
							Vixen.Popup.ShowAjaxPopup('RateGroupSearchId', 'large', null, 'Service', 'ViewRates', objObject);
						}
						";
		echo "<script type='text/javascript'>$strJsCode</script>\n";
	}
	
	
	// Returns the html code that should go in the drop down detail of a row of the table defining a RateGroup
	private function _BuildDropDownDetail($intRateGroupId)
	{
		// Retrieve all the Rates belonging to the RateGroup
		DBL()->Rate->Where->RateGroup = $intRateGroupId;
		DBL()->Rate->Load();
		
		// Check how many rates were returned
		if (DBL()->Rate->RecordCount() > 10)
		{
			// Don't display the Rates.  Display search controls
			// there is more than 10 rate plans shown
			
			$strSearchStringTextboxId = "RateSearch_". uniqid(rand());
			$strDetailHtml =  "<div class='VixenTableDetail'>\n";
			$strDetailHtml .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
			$strDetailHtml .= "	<tr>\n";
			$strDetailHtml .= "		<td width='15%' alignment='left'>\n";
			$strDetailHtml .= "			<span>Search through rates:</span>";
			$strDetailHtml .= "		</td>\n";
			$strDetailHtml .= "		<td width='25%' alignment='left'>\n";			
			$strDetailHtml .= "			<input type=text  id='$strSearchStringTextboxId' class='DefaultInputText' style='left:0px'>\n";
			$strDetailHtml .= "		</td>\n";
			$strDetailHtml .= "		<td width='60%' alignment='left'>\n";
			$strDetailHtml .= "			<input type='button' value='View Rates' class='InputSubmit' onclick='VixenOpenRatesSearchResultsPopup($intRateGroupId, \"$strSearchStringTextboxId\")'></input>\n";
			$strDetailHtml .= "		</td>\n";
			$strDetailHtml .= "	</tr>\n";					
			$strDetailHtml .= "</table>\n";
			$strDetailHtml .= "</div>\n";
		}
		else
		{
			// Display the Rates
			$strDetailHtml = "<div class='VixenTableDetail'>\n";
			$strDetailHtml .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
			$strDetailHtml .= "<tr bgcolor='#C0C0C0'><td width='64%'><span>Rate Name</span></td><td width='20%'><span>Days Available</span></td><td width='16%'><span>Time Range</span></td></tr>\n";
			
			foreach (DBL()->Rate as $dboRate)
			{
				$strViewRateLink = Href()->ViewRate($dboRate->Id->Value);
			
				$strDetailHtml .= "   <tr>\n";
				$strDetailHtml .= "      <td>\n";
				$strDetailHtml .= "<a href='$strViewRateLink' title='{$dboRate->Description->Value}'>{$dboRate->Name->AsValue()}</a>";
				//$strDetailHtml .= $dboRate->Name->AsValue();
				$strDetailHtml .= "      </td>\n";
				$strDetailHtml .= "      <td>\n";
				$strDetailHtml .= $dboRate->Monday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Tuesday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Wednesday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Thursday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Friday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Saturday->AsValue(CONTEXT_DEFAULT,TRUE) . "&nbsp;";
				$strDetailHtml .= $dboRate->Sunday->AsValue(CONTEXT_DEFAULT,TRUE);
				$strDetailHtml .= "      </td>\n";
				$strDetailHtml .= "      <td><span>{$dboRate->StartTime->Value} - {$dboRate->EndTime->Value}</span></td>";
				$strDetailHtml .= "   </tr>\n";
			}
			$strDetailHtml .= "</table>\n";
			$strDetailHtml .= "</div>\n";

		}
		
		return $strDetailHtml;
	}
}

?>
