<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_details.php
//----------------------------------------------------------------------------//
/**
 * rate_group_details
 *
 * HTML Template for the Plan's RateGroup Details HTML object
 *
 * HTML Template for the Plan's RateGroup Details HTML object
 *
 * @file		rate_group_details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplatePlanRateGroupDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanRateGroupDetails
 *
 * HTML Template class for the Plan's RateGroup Details HTML object
 *
 * HTML Template class for the Plan's RateGroup Details HTML object
 * Lists all RateGroups belonging to a RatePlan, in the one table
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanRateGroupDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanRateGroupDetails extends HtmlTemplate
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

		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
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
		// Retrieve a list of Record Types applicable to the RatePlan
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->OrderBy("Description");
		DBL()->RecordType->Load();
	
		// Build an array to store all the RateGroup information to display, having the key be the record type
		$arrRateGroups = array();
		foreach (DBL()->RateGroup as $dboRateGroup)
		{
			$intRecordType = $dboRateGroup->RecordType->Value;
			
			if (!isset($arrRateGroups[$intRecordType]))
			{
				// The array of RateGroups belonging to the RecordType has not yet been declared
				$arrRateGroups[$intRecordType] = array();
			}
			
			// Append the RateGroup's details to the list
			$arrRateGroups[$intRecordType][] = array(	"Id"			=>	$dboRateGroup->Id->Value,
														"Name"			=>	htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES),
														"Description"	=>	htmlspecialchars($dboRateGroup->Description->Value, ENT_QUOTES),
														"Fleet"			=>	$dboRateGroup->Fleet->Value,
														"Archived"		=>	$dboRateGroup->Archived->Value
													);
		}
	
	
		echo "<h2 class='Plan'>Rate Groups</h2>\n";
		
		Table()->RateGroups->SetHeader("Call Type", "Rate Group", "Description", "Fleet");
		Table()->RateGroups->SetWidth("25%", "35%", "35%", "5%");
		Table()->RateGroups->SetAlignment("Left", "Left", "Left", "Center");
		
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$bolFoundNormalRateGroup	= FALSE;
			$intRecordType		= $dboRecordType->Id->Value;
			$strRecordType		= $dboRecordType->Description->Value;
			
			if (isset($arrRateGroups[$intRecordType]))
			{
				// RateGroups of this RecordType, have been specified
				foreach ($arrRateGroups[$intRecordType] as $arrRateGroup)
				{
					// Build the name cell
					$strRateGroupLink	= Href()->ViewRateGroup($arrRateGroup['Id'], FALSE);
					$strNameCell		= "<a href='$strRateGroupLink' >{$arrRateGroup['Name']}</a>";
					//$strDescriptionCell	= "<a href='$strRateGroupLink' style='color:black;'>{$arrRateGroup['Description']}</a>";
					//$strNameCell		= $arrRateGroup['Name'];
					$strDescriptionCell	= $arrRateGroup['Description'];
					
					switch ($arrRateGroup['Archived'])
					{
						case RATE_STATUS_DRAFT:
							$strNameCell = "DRAFT - $strNameCell";
							break;
						case RATE_STATUS_ARCHIVED:
							$strNameCell = "ARCHIVED - $strNameCell";
							break;
					}
					
					$strFleetCell = ($arrRateGroup['Fleet']) ? "<img src='img/template/tick.png' />" : "";
					
					Table()->RateGroups->AddRow($strRecordType, $strNameCell, $strDescriptionCell, $strFleetCell);
					$bolFoundNormalRateGroup = (!$arrRateGroup['Fleet']) ? TRUE : $bolFoundNormalRateGroup;
					
					//Table()->RateGroups->SetOnClick($strRateGroupLink);  
				}
				
			}
			
			// Check if we have found a non-fleet RateGroup
			if (!$bolFoundNormalRateGroup)
			{
				if ($dboRecordType->Required->Value)
				{
					// A RateGroup is required
					Table()->RateGroups->AddRow($strRecordType, "<span class='Red'>No normal RateGroup has been specified, yet one is required</span>");
					Table()->RateGroups->SetRowColumnSpan(1,3);
				}
				else
				{
					// A RateGroup is not required
					Table()->RateGroups->AddRow($strRecordType, "<span class='Red'>No normal RateGroup has been specified.  It is not required to specify one</span>");
					Table()->RateGroups->SetRowColumnSpan(1,3);
				}
			}
		}
		
		//Table()->RateGroups->RowHighlighting = TRUE;
		Table()->RateGroups->Render();

		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>
