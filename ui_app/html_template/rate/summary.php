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
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateRateSummary
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateSummary
 *
 * HTML Template class for the Summary HTML object
 *
 * HTML Template class for the Summary HTML object
 * Lists all rategrops related to a service
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
	function Render()
	{
		// Retrieve the Rate Summary
		// This is in the format:
		// $arrRateSummary[weekday][interval] = ALLOCATED | OVER-ALLOCATED | UNDER-ALLOCATED
		$arrRateSummary = DBO()->RateSummary->ArrSummary->Value;

	
	
		// Render each of the account invoices
		$strCellColor = "#FFFFFF";
		
		echo "<div class='PopupLarge' style='overflow:auto; height:300px; width:auto;'>\n";
		//echo "<div class='NarrowColumn'>\n";
Debug($arrRateSummary);
return;
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td>&nbsp;</td>";
		for ($i = 0; $i<24; $i++)
		{
			echo "<td colspan='4' align='center' style='font-size: xx-small;' width='4%'>".$i."</td>";
		}
		echo "</tr>\n";
		foreach ($arrRateSummary as $strWeekday=>$arrIntervals)
		{
			echo "<tr><td style='font-size: xx-small;'>". $strWeekday ."</td>";
			foreach ($arrIntervals as $intIntervalStatus)
			{
				switch ($intIntervalStatus)
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
				//$strCellStyle = "style='border: solid thin #C0C0C0; border-left-style: none; border-bottom-style:". ($intKey != count($arrWeekdays)? "none" : "solid");
				$strCellStyle = "style='border: solid thin #C0C0C0; border-left-style: solid; border-bottom-style:solid; background-color:$strCellColor;'";
				echo "<td $strCellStyle>&nbsp;</td>";
			}
			echo "</tr>\n";
		}
		echo "<tr><td>&nbsp;</td><td colspan='96' style='border-top-color: #C0C0C0; border-top-width: thin; border-top-style: solid'>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</div>\n";
	}
}

?>
