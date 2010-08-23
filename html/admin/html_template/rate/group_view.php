<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// group_view.php
//----------------------------------------------------------------------------//
/**
 * group_view
 *
 * HTML Template for displaying the static details of a rate group, in a popup
 *
 * HTML Template for displaying the static details of a rate group, in a popup
 *
 * @file		group_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupView
 *
 * HTML Template class for the RateGroupView HTML object
 *
 * HTML Template class for the RateGroupView HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupView
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupView extends HtmlTemplate
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
		// Render the RateGroup Details
		echo "<div class='GroupedContent'>\n";
		
		// Handle the Archived property
		if (DBO()->RateGroup->Archived->Value)
		{
			if (DBO()->RateGroup->Archived->Value == RATE_STATUS_DRAFT)
			{
				// The RateGroup is currently saved as a draft
				echo "<span class='Red'><center>This rate group is currently saved as a draft</center></span>\n";
			}
			else
			{
				// The RateGroup must be archived
				echo "<span class='Red'><center>This rate group has been archived</center></span>\n";
			}
			echo "<div class='ContentSeparator'></div>\n";
		}
		
		DBO()->RateGroup->Name->RenderOutput();
		DBO()->RateGroup->Description->RenderOutput();
		DBO()->RateGroup->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);
		DBO()->RecordType->Name->RenderOutput();
		DBO()->RateGroup->Fleet->RenderOutput();
		
		$fltCapLimit = DBO()->RateGroup->CapLimit->Value;
		if ($fltCapLimit !== NULL)
		{
			// HACK! HACK! HACK!
			// This isn't really a hack, it's just REALLY ugly code to get the label to have '($)' appended to it
			$fltCapLimit = OutputMask()->FormatFloat($fltCapLimit, 2, 2);
			DBO()->RateGroup->{'CapLimit ($)'} = $fltCapLimit;
			DBO()->RateGroup->{'CapLimit ($)'}->RenderOutput();
			// HACK! HACK! HACK!
			//DBO()->RateGroup->CapLimit->RenderArbitrary($fltCapLimit, RENDER_OUTPUT);
		}
		
		$iRetrievedRatesCount = DBL()->Rate->RecordCount();
		
		// Draw the search components
		if (DBO()->RateGroup->TotalRateCount->Value > 10)
		{
			$this->FormStart("SearchRateGroupRates", "RateGroup", "View");
			$strSearchString = DBO()->Rate->SearchString->Value;
			DBO()->RateGroup->Id->RenderHidden();
			
			// Create a combobox containing all the filter options
			echo "<div style='height:25px'>\n";
			echo "<div class='Left'>\n";
			echo "   <span>&nbsp;&nbsp;Rate Search : </span>\n";
			echo "   <span><input type='text' class='DefaultInputText' style='left:0px;width:200px;margin-left:107px;margin-right:15px;' name='Rate.SearchString' value='$strSearchString'></input></span>";
			echo "</div><div class='Right'>\n";
			$this->AjaxSubmit("Search");
			echo "</div></div>\n";
			
			// Hidden values to enable pagination
			echo "<input type='hidden' name='Pagination.PageNumber' value='".(string)DBO()->Pagination->PageNumber->Value."'/>";
			echo "<input type='hidden' name='Pagination.Limit' value='".(string)DBO()->Pagination->Limit->Value."'/>";
			echo "<input type='hidden' name='Pagination.TotalSearchCount' value='".(string)DBO()->Pagination->TotalSearchCount->Value."'/>";
			
			// DEBUGGING INFORMATION FOR PAGINATION
			/*
			echo "Page : ".(string)DBO()->Pagination->PageNumber->Value."<br/>";
			echo "Limit : ".(string)DBO()->Pagination->Limit->Value."<br/>";
			echo "Offset : ".(string)DBO()->Pagination->Offset->Value."<br/>";
			echo "Retrieved Count : {$iRetrievedRatesCount}<br/>";
			echo "Total Search Count : ".(string)DBO()->Pagination->TotalSearchCount->Value."<br/>";
			echo "Total Rate Count : ".(string)DBO()->RateGroup->TotalRateCount->Value."<br/>";
			echo "Shown Up To: ".(DBO()->Pagination->Offset->Value + $iRetrievedRatesCount);
			*/
			
			$this->FormEnd();
		}
		echo "<div class='SmallSeperator'></div>\n";
		
		// Number of results will never exceed 10, the limit is set to 10
		/*
		// Only draw the scrollable div container if there are more than 10 Rates to display
		if ($iRetrievedRatesCount > 10)
		{
			echo "<div id='Container_ScrollableContainer_RateGroupRates' class='GroupedContent'>\n";
			echo "<div id='ScrollableContainer_RateGroupRates' style='padding: 0px 3px 0px 3px;overflow:auto; height:300px;'>\n";
		}
		*/
	
		Table()->RateTable->SetHeader("Rates");
		Table()->RateTable->SetAlignment("Left");
		Table()->RateTable->SetWidth("100%");
	
		foreach (DBL()->Rate as $dboRate)
		{
			$strViewRateLink = Href()->ViewRate($dboRate->Id->Value, FALSE);
			$strRateName = htmlspecialchars($dboRate->Name->Value, ENT_QUOTES);
			$strRateDescription = htmlspecialchars($dboRate->Description->Value, ENT_QUOTES);
			
			switch ($dboRate->Archived->Value)
			{
				case RATE_STATUS_DRAFT:
					$strNamePrefix = "DRAFT - ";
					break;
				case RATE_STATUS_ARCHIVED:
					$strNamePrefix = "ARCHIVED -";
					break;
				default:
					$strNamePrefix = "";
			}
			
			Table()->RateTable->AddRow("$strNamePrefix<a href='$strViewRateLink' title='$strRateDescription'>$strRateName</a>");
		}
		
		Table()->RateTable->Render();
		
		// Number of results will never exceed 10, the limit is set to 10
		/*
		if ($iRetrievedRatesCount > 10)
		{
			// End the scrollable container divs
			echo "</div></div>\n";
		}
		*/
		
		// Display details of how many records are being shown
		if (DBO()->RateGroup->TotalRateCount->Value > 10)
		{
			echo "<div class='TinySeperator'></div>";
			
			// Record summary
			if (DBO()->Pagination->TotalSearchCount->Value == 0)
			{
				$sRecordSummary = "There are no Rates that match the search";
			}
			else
			{
				$sRecordSummary = "Showing ".(DBO()->Pagination->Offset->Value + 1)." to ".(DBO()->Pagination->Offset->Value + $iRetrievedRatesCount)." of ". DBO()->Pagination->TotalSearchCount->Value ." Rates";
			}
			echo "<div class='rate-group-search'><div class='record-summary'>$sRecordSummary</div>";
			
			// Pagination buttons
			if (DBO()->Pagination->TotalSearchCount->Value > 0)
			{
				echo "<div class='pagination'>";
				$bNotFirstPage	= DBO()->Pagination->PageNumber->Value > 0;
				$this->_generatePaginationButton('First', $bNotFirstPage);
				$this->_generatePaginationButton('Previous', $bNotFirstPage);
				
				$iTotalShown	= DBO()->Pagination->Offset->Value + DBO()->Pagination->Limit->Value;
				$bNotLastPage	= $iTotalShown < DBO()->Pagination->TotalSearchCount->Value;
				$this->_generatePaginationButton('Next', $bNotLastPage);
				$this->_generatePaginationButton('Last', $bNotLastPage);
				echo "</div></div>\n";
			}
		}
		
		echo "</div>\n"; // GroupedContent
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		
		// If there is only 1 rate then open the Rate Details popup to display the rate
		if (DBO()->RateGroup->TotalRateCount->Value == 1)
		{
			DBL()->Rate->rewind();
			$dboRate = DBL()->Rate->current();
			$strDisplayRatePopup = Href()->ViewRate($dboRate->Id->Value, FALSE);
			echo "<script type='text/javascript'>$strDisplayRatePopup</script>\n";
		}
	}
	
	private function _generatePaginationButton($sPage, $bEnabled=true)
	{
		$sTarget 	= '';
		$sId 		= '';
		$sSize 		= '';
		$sTemplate	= $this->_strTemplate;
		$sMethod 	= "View{$sPage}Page";
		
		if (is_object($this->_objAjax))
		{
			$sTarget 	= $this->_objAjax->TargetType;
			$sId 		= $this->_objAjax->strId;
			$sSize 		= $this->_objAjax->strSize;
		}
		
		$sImageSrc	= strtolower($sPage);
		echo "<button ".($bEnabled ? '' : "disabled='disabled'")." class='$sStyleClass' id='$sButtonId' name='VixenButtonId' onclick=\"Vixen.Ajax.SendForm('{$this->_strForm}', '$sLabel','$sTemplate', '$sMethod', '$sTarget', '$sId', '$sSize', '{$this->_strContainerDivId}')\"><img src='../admin/img/template/resultset_{$sImageSrc}.png'/></button>\n";
	}
}

?>
