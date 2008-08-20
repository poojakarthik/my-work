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
		
		$this->LoadJavascript("service_rate_groups");
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
		// Build an array storing the ids of all the RateGroups belonging to the plans associated with this account
		$arrPlanRateGroups = Array();
		foreach (DBL()->PlanRateGroup as $dboRateGroup)
		{
			$arrPlanRateGroups[] = $dboRateGroup->Id->Value;
		}
		
		$intCurrentBillPeriodStartDatetime = GetStartDateTimeForBillingPeriod();
		
		// This will list the Start and End times for all shown RateGroups for a given RecordType.  If a RateGroup's Start and End times
		// are completely overridden by one of higher precedence, then it will not be shown.
		$arrShownRateGroups = Array();
	
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
	
		$intNow = strtotime(GetCurrentDateAndTimeForMySQL());
	
		echo "<h2 class='Plan'>Rate Groups</h2>\n";
		
		echo "<div class='GroupedContent' style='text-align:center'>Rate Groups coloured green are those currently being used</div>\n";
		echo "<div class='SmallSeperator'></div>\n";
		
		foreach (DBL()->RecordType as $dboRecordType)
		{
			// Build the name of the table
			$strTableName = "RecordType_{$dboRecordType->Id->Value}";
			
			$strOverrideRateGroup = "&nbsp;";
			if ($bolUserHasAdminPerm)
			{	
				// The user has permission to override RateGroups.  Build the link to the RateGroup Override popup
				$strOverrideRateGroupHref	= Href()->OverrideRateGroup(DBO()->Service->Id->Value, $dboRecordType->Id->Value);
				$strOverrideRateGroup		= "<img src='img/template/edit.png' title='Declare an Overriding RateGroup' onclick='$strOverrideRateGroupHref'></img>";
			}
			$strRecordTypeCell = $dboRecordType->Description->Value;
			
			Table()->$strTableName->SetHeader("&nbsp;", $strRecordTypeCell, "&nbsp;", "&nbsp;", "&nbsp", "&nbsp;", $strOverrideRateGroup);
			Table()->$strTableName->SetWidth("3%", "65%", "5%", "10%", "2%", "10%", "5%");
			Table()->$strTableName->SetAlignment("Left", "Left", "Center", "Left", "Left", "Left", "Right");
			
			// List all the Fleet Rate Groups for this record type in Descending order of precedence (CreateOn determines precedence)
			$intPrecedence = 1;
			$bolFoundCurrentFleetRateGroup = FALSE;
			$arrShownRateGroups = Array();
			foreach (DBL()->CurrentServiceRateGroup as $dboRateGroup)
			{
				// Make sure that the RateGroup relates to this RecordType and IS Fleet
				if ($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value && $dboRateGroup->Fleet->Value)
				{
					// The RateGroup is a fleet RateGroup and is of the correct RecordType
					// Initilise variables
					$bolIsCurrent		= FALSE;
					$arrRateGroupTimeRange['Start']	= strtotime($dboRateGroup->StartDatetime->Value);
					$arrRateGroupTimeRange['End']	= strtotime($dboRateGroup->EndDatetime->Value);
					$arrRateGroupTimeRange['IsIndefinite'] = (bool)($dboRateGroup->EndDatetime->Value == END_OF_TIME);
					
					// Check if the RateGroup should be displayed
					foreach ($arrShownRateGroups as $arrHigherRateGroup)
					{
						if ($arrHigherRateGroup['Start'] <= $arrRateGroupTimeRange['Start'])
						{
							if 	(	($arrHigherRateGroup['IsIndefinite']) 
									||
									(!$arrRateGroupTimeRange['IsIndefinite'] && $arrHigherRateGroup['End'] >= $arrRateGroupTimeRange['End'])
								)
							{
								// A higher precedence RateGroup completely overrides $dboRateGroup
								// Move on to the next $dboRateGroup
								continue 2;
							}
						}
					}
					
					// Add the RateGroup TimeRange details to the array of shown RateGroups
					$arrShownRateGroups[] = $arrRateGroupTimeRange;
					
					// Check if it is the RateGroup that is currently in use
					if (!$bolFoundCurrentFleetRateGroup && $arrRateGroupTimeRange['Start'] <= $intNow && ($arrRateGroupTimeRange['IsIndefinite'] || $arrRateGroupTimeRange['End'] > $intNow))
					{
						// This RateGroup is currently being used
						$bolFoundCurrentFleetRateGroup	= TRUE;
						$bolIsCurrent					= TRUE;
					}
					
					// Check if the RateGroup is not part of the plans
					$strNotPartOfPlan = "&nbsp;";  // Default
					if (!in_array($dboRateGroup->RateGroup->Value, $arrPlanRateGroups))
					{
						// The RateGroup is not in any of the plans associated with this service
						$strNotPartOfPlan = "<img src='img/template/flag_yellow.png' title='RateGroup is not standard part of the plan'></img>";
					}
					
					// Prepare the Start Cell
					$intStart		= strtotime($dboRateGroup->StartDatetime->Value);
					$strStartTime	= date("g:i:s A", $intStart);
					$strStartDate	= "<span title='$strStartTime'>". date("M j, Y", $intStart) ."</span>";
					
					// Prepare the End Cell
					if ($dboRateGroup->EndDatetime->Value == END_OF_TIME)
					{
						$strEndDate = "Indefinite";
					}
					else
					{
						$intEnd		= strtotime($dboRateGroup->EndDatetime->Value);
						$strEndTime	= date("g:i:s A", $intEnd);
						$strEndDate	= "<span title='$strEndTime'>". date("M j, Y", $intEnd) ."</span>";
					}

					$strViewRateGroupLink	= Href()->ViewRateGroup($dboRateGroup->RateGroupId->Value, FALSE);

					// Escape all special chars from the name and description
					$dboRateGroup->Name = htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
					$dboRateGroup->Description = htmlspecialchars($dboRateGroup->Description->Value, ENT_QUOTES);
					
					// Prepare the RateGroup Cell
					$strRateGroupName = ($bolIsCurrent) ? "<span class='Green'>{$dboRateGroup->Name->Value}</span>" : $dboRateGroup->Name->Value;
					$strRateGroupCell  = "Fleet: &nbsp;";
					$strRateGroupCell .= "<a href='$strViewRateGroupLink' title='{$dboRateGroup->Description->Value}' style='color:black;'>$strRateGroupName</a>";
					
					// Prepare the RemoveRateGroup Cell
					$strRemoveRateGroup = "&nbsp;";
					/* The following commented out code supplies a link to remove 
					 * a RateGroup Override.  We can't currently allow this because
					 * CDRs might have already been invoiced using this RateGroup and if
					 * it is removed then we have not retained an accurate history of the 
					 * Rates that apply to the Service at any given time throughout the
					 * life of the service.
					 * 
					 * As soon as we can tell when the last time an overriding RateGroup was 
					 * used to charge a CDR, then we can allow the user to delete 
					 * an overriding RateGroup backdated to this time.  We can then
					 * Allow them to specify a new RateGroup to use and back date it
					 * to this time. 
					 */
					/*
					if ($bolUserHasAdminPerm && $intStart >= $intCurrentBillPeriodStartDatetime)
					{	
						// The RateGroup can be removed, so long as it doesn't cause a gap in the Plan history of the service
						$strRemoveRateGroupJsCode	= "javascript: Vixen.ServiceRateGroups.RemoveRateGroup({$dboRateGroup->Id->Value}, '{$dboRateGroup->Name->Value}')";
						$strRemoveRateGroupJsCode	= htmlspecialchars($strRemoveRateGroupJsCode, ENT_QUOTES);
						$strRemoveRateGroup			= "<a href='$strRemoveRateGroupJsCode'><img src='img/template/delete.png' title='Remove RateGroup'></img></a>";
					}
					*/
					
					// Add the Row
					Table()->$strTableName->AddRow($intPrecedence, $strRateGroupCell, $strNotPartOfPlan, $strStartDate, "-", $strEndDate, $strRemoveRateGroup);
					
					// Increment the precedence counter
					$intPrecedence++;
				}
			}
			
			// List all the Normal Rate Groups for this record type in Descending order of precedence (CreateOn determines precedence)
			$bolFoundCurrentNormalRateGroup = FALSE;
			$arrShownRateGroups = Array();
			foreach (DBL()->CurrentServiceRateGroup as $dboRateGroup)
			{
				// Make sure that the RateGroup relates to this RecordType and is not Fleet
				if ($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value && $dboRateGroup->Fleet->Value == 0)
				{
					// The RateGroup is a standard RateGroup and is of the correct RecordType
					// Initilise variables
					$bolIsCurrent		= FALSE;
					$arrRateGroupTimeRange['Start']	= strtotime($dboRateGroup->StartDatetime->Value);
					$arrRateGroupTimeRange['End']	= strtotime($dboRateGroup->EndDatetime->Value);
					$arrRateGroupTimeRange['IsIndefinite'] = ($dboRateGroup->EndDatetime->Value == END_OF_TIME)? TRUE : FALSE;
					
					// Check if the RateGroup should be displayed
					foreach ($arrShownRateGroups as $arrHigherRateGroup)
					{
						if ($arrHigherRateGroup['Start'] <= $arrRateGroupTimeRange['Start'])
						{
							if 	(	($arrHigherRateGroup['IsIndefinite']) 
									||
									(!$arrRateGroupTimeRange['IsIndefinite'] && $arrHigherRateGroup['End'] >= $arrRateGroupTimeRange['End'])
								)
							{
								// A higher precedence RateGroup completely overrides $dboRateGroup
								// Move on to the next $dboRateGroup
								continue 2;
							}
						}
					}
					
					// Add the RateGroup TimeRange details to the array of shown RateGroups
					$arrShownRateGroups[] = $arrRateGroupTimeRange;
					
					// Check if it is the RateGroup that is currently in use
					if (!$bolFoundCurrentNormalRateGroup && $arrRateGroupTimeRange['Start'] <= $intNow && ($arrRateGroupTimeRange['IsIndefinite'] || $arrRateGroupTimeRange['End'] > $intNow))
					{
						// This RateGroup is currently being used
						$bolFoundCurrentNormalRateGroup	= TRUE;
						$bolIsCurrent					= TRUE;
					}
					
					// Check if the RateGroup is not part of the plan
					$strNotPartOfPlan = "&nbsp;";  // Default
					if (!in_array($dboRateGroup->RateGroup->Value, $arrPlanRateGroups))
					{
						// The RateGroup is not in any of the plans associated with this service
						$strNotPartOfPlan = "<img src='img/template/flag_yellow.png' title='RateGroup is not standard part of the plan'></img>";
					}
					
					// Prepare the Start Cell
					$intStart		= strtotime($dboRateGroup->StartDatetime->Value);
					$strStartTime	= date("g:i:s A", $intStart);
					$strStartDate	= "<span title='$strStartTime'>". date("M j, Y", $intStart) ."</span>";
					
					// Prepare the End Cell
					if ($dboRateGroup->EndDatetime->Value == END_OF_TIME)
					{
						$strEndDate = "Indefinite";
					}
					else
					{
						$intEnd		= strtotime($dboRateGroup->EndDatetime->Value);
						$strEndTime	= date("g:i:s A", $intEnd);
						$strEndDate	= "<span title='$strEndTime'>". date("M j, Y", $intEnd) ."</span>";
					}
					
					$strViewRateGroupLink	= Href()->ViewRateGroup($dboRateGroup->RateGroupId->Value, FALSE);

					// Escape all special chars from the name and description
					$dboRateGroup->Name = htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
					$dboRateGroup->Description = htmlspecialchars($dboRateGroup->Description->Value, ENT_QUOTES);

					// Prepare the RateGroup Cell
					$strRateGroupName = ($bolIsCurrent) ? "<span class='Green'>{$dboRateGroup->Name->Value}</span>" : $dboRateGroup->Name->Value;
					$strRateGroupCell = "<a href='$strViewRateGroupLink' title='{$dboRateGroup->Description->Value}' style='color:black;'>$strRateGroupName</a>";
					
					// Prepare the RemoveRateGroup Cell
					$strRemoveRateGroup = "&nbsp;";
					/* The following commented out code supplies a link to remove 
					 * a RateGroup Override.  We can't currently allow this because
					 * CDRs might have already been invoiced using this RateGroup and if
					 * it is removed then we have not retained an accurate history of the 
					 * Rates that apply to the Service at any given time throughout the
					 * life of the service.
					 * 
					 * As soon as we can tell when the last time an overriding RateGroup was 
					 * used to charge a CDR, then we can allow the user to delete 
					 * an overriding RateGroup backdated to this time.  We can then
					 * Allow them to specify a new RateGroup to use and back date it
					 * to this time. 
					 */
					/*
					if ($bolUserHasAdminPerm && $intStart >= $intCurrentBillPeriodStartDatetime)
					{	
						// The RateGroup can be removed, so long as it doesn't cause a gap in the Plan history of the service
						$strRemoveRateGroupJsCode	= "javascript: Vixen.ServiceRateGroups.RemoveRateGroup({$dboRateGroup->Id->Value}, '{$dboRateGroup->Name->Value}')";
						$strRemoveRateGroupJsCode	= htmlspecialchars($strRemoveRateGroupJsCode, ENT_QUOTES);
						$strRemoveRateGroup			= "<a href='$strRemoveRateGroupJsCode'><img src='img/template/delete.png' title='Remove RateGroup'></img></a>";
					}
					*/
					
					// Add the Row
					Table()->$strTableName->AddRow($intPrecedence, $strRateGroupCell, $strNotPartOfPlan, $strStartDate, "-", $strEndDate, $strRemoveRateGroup);
					
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
				Table()->$strTableName->SetRowColumnSpan(1, 6);
			}
			
			// Draw the table
			Table()->$strTableName->Render();
			echo "<div class='TinySeperator'></div>\n";
		}
		echo "<div class='Seperator'></div>\n";
		
		// Initialise the ServiceRateGroups object and register the OnServiceRateGroupsUpdate Listener
		$intServiceId = DBO()->Service->Id->Value;
		$strJavascript = "Vixen.ServiceRateGroups.Initialise($intServiceId, '{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}
}

?>
