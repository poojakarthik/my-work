<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// summary.php
//----------------------------------------------------------------------------//
/**
 * summary
 *
 * HTML Template for the Summary HTML object
 *
 * HTML Template for the summary HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all rategroups relating to a summary
 *
 * @file		summary.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd


//----------------------------------------------------------------------------//
// HtmlTemplateRateSummary
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateSummary
 *
 * HTML Template class for the Rate Group Summary HTML object
 *
 * HTML Template class for the Rate Group Summary HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateRateSummary
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateSummary extends HtmlTemplate
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
		// Retrieve the Rate Summary
		// This is in the format:
		// $arrRateSummary[weekday][interval] = ALLOCATED | OVER-ALLOCATED | UNDER-ALLOCATED
		$arrRateSummary = DBO()->RateSummary->ArrSummary->Value;

		echo "<table border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td>&nbsp;</td>";
		for ($i = 0; $i<24; $i++)
		{
			echo "<td colspan='4' align='center' style='font-size: xx-small; border-left-width: thin; border-left-style: solid; border-left-color: #C4C4C4;' width='4%'>".$i."</td>";
		}
		echo "</tr>\n";
		foreach ($arrRateSummary as $strWeekday=>$arrIntervals)
		{
			echo "<tr><td style='font-size: xx-small; border-top-width: thin; border-top-style: solid; border-top-color: #C0C0C0'>". $strWeekday ."</td>";
			for ($i=0; $i < count($arrIntervals); $i++)
			{
				switch ($arrIntervals[$i])
				{
					case RATE_ALLOCATION_STATUS_UNDER_ALLOCATED:
						$strCellColor = "#FFFFFF";
						break;
					case RATE_ALLOCATION_STATUS_OVER_ALLOCATED:
						$strCellColor = "#FF0000";
						break;
					case RATE_ALLOCATION_STATUS_ALLOCATED:
						$strCellColor = "#00FF00";
						break;
					default:
						$strCellColor = "#FFFFFF";
						break;
				}
				
				if ($i % 4 == 0)
				{
					$strBorderLeft = "border-left-width: thin; border-left-style: solid; border-left-color: #C4C4C4;";
				}
				else
				{
					$strBorderLeft = "";
				}
				
				$strCellStyle = "style='border-left-width: thin; border: solid thin #C0C0C0; border-left-style: none; border-bottom-style:". ($i != count($arrIntervals)? "none" : "solid") ."; background-color:$strCellColor; $strBorderLeft;'";
				echo "<td $strCellStyle>&nbsp;</td>";
			}
			echo "</tr>\n";
		}
		echo "<tr><td style='border-top-width: thin; border-top-style: solid; border-top-color: #C0C0C0'>&nbsp;</td><td colspan='96' style='border-top-color: #C0C0C0; border-top-width: thin; border-top-style: solid'>&nbsp;</td></tr>\n";
		echo "</table>\n";
		
		echo "<div class='NarrowContent'>\n";
		echo "<table border='0' cellpadding='3' cellspacing='3' width='100%'>";
		echo "<tr><td bgcolor='#00FF00' width='40px' style='border-style: solid; border-width: thin'>&nbsp;</td><td width='25%'><span class='DefaultOutputSpan'>Allocated</span></td>";
		echo "<td bgcolor='#FF0000' width='40px' style='border-style: solid; border-width: thin'>&nbsp;</td><td width='25%'><span class='DefaultOutputSpan'>Over allocated</span></td>";
		echo "<td bgcolor='#FFFFFF' width='40px' style='border-style: solid; border-width: thin'>&nbsp;</td><td width='25%'><span class='DefaultOutputSpan'>Under allocated</span></td></tr>";
		echo "</table>\n";
		echo "</div\n";
		
		echo "<div class='SmallSeperator'></div>";
		// Convert new line chars to <br> tags and tab chars to &nbsp;
		$strProblemReport = nl2br(DBO()->RateSummary->ProblemReport->Value);
		$strProblemReport = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $strProblemReport);
		
		// Display the Textual Rate Summary contained in DBO()->RateSummary->ProblemReport
		echo "<div id='ContainerDiv_ContainerDiv_RateGroupSummary' class='NarrowContent' style='padding: 5px 5px 5px 5px'>\n";
		echo "<div id='ContainerDiv_RateGroupSummary' class='PopupLarge' style='overflow:auto; height:100px; width:auto;'>\n";
		echo "<span class='DefaultOutputSpan' style='line-height: 1.2;'>";
		echo $strProblemReport;
		echo "</span>";
		echo "</div>"; // ContainerDiv_RateGroupSummary
		echo "</div>"; // ContainerDiv_ContainerDiv_RateGroupSummary
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		
	}
}

?>
