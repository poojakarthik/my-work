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
// HtmlTemplateRateList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateList
 *
 * HTML Template class for the RateList HTML object
 *
 * HTML Template class for the RateList HTML object
 * Lists all rategrops related to a service
 *
 * @package	ui_app
 * @class	HtmlTemplateRateList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateList extends HtmlTemplate
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
		echo "<div class='PopupLarge'>\n";
		echo "<div  style='overflow:auto; height:300px'>\n";
	
		Table()->RateTable->SetHeader("Name", "Days Available", "Start Time", "End Time");
		Table()->RateTable->SetAlignment("Left", "Left", "Left", "Left");
		Table()->RateTable->SetWidth("49%", "30%", "11%", "10%");
	
		foreach (DBL()->Rate as $dboRate)
		{
			$strDaysAvailable = $dboRate->Monday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Tuesday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Wednesday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Thursday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Friday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Saturday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Sunday->AsValue(CONTEXT_DEFAULT,TRUE);
								
			Table()->RateTable->AddRow(	$dboRate->Name->AsValue(),
										$strDaysAvailable,
										$dboRate->StartTime->AsValue(), 
										$dboRate->EndTime->AsValue());
			
			//drop down div component for each row
			$strBasicDetailHtml =  "<div class='VixenTableDetail'>\n";
			$strBasicDetailHtml .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
			$strBasicDetailHtml .= "	<tr>\n";
			$strBasicDetailHtml .= "		<td><font size='2'>\n";
			
			$strBasicDetailHtml .= "Description : ". $dboRate->Description->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Standard Units : ". $dboRate->StdUnits->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Standard Rate Per Unit : ". $dboRate->StdRatePerUnit->AsValue();
			$strBasicDetailHtml .= "<br>\n";	
			
			if ($dboRate->StdMarkup->Value != 0)
			{
				$strBasicDetailHtml .= "Standard Markup : ". $dboRate->StdMarkup->AsValue();
				$strBasicDetailHtml .= "<br>\n";
			}
		
			if ($dboRate->StdPercentage->Value != 0)
			{
				$strBasicDetailHtml .= "Standard Percentage : ". $dboRate->StdPercentage->AsValue();
				$strBasicDetailHtml .= "<br>\n";
			}
			
			if ($dboRate->StdMinCharge->Value != 0)
			{

			}
		
			$strBasicDetailHtml .= "Standard Flagfall : ". $dboRate->StdFlagfall->AsValue();
			$strBasicDetailHtml .= "<p>\n";	


			if ($dboRate->CapUnits->Value != 0)
			{
				$strBasicDetailHtml .= "Cap Units : ". $dboRate->CapUnits->AsValue();
				$strBasicDetailHtml .= "<br>\n";
			}

			if ($dboRate->CapCost->Value != 0)
			{
				$strBasicDetailHtml .= "Cap Cost : ". $dboRate->CapCost->AsValue();
				$strBasicDetailHtml .= "<br>\n";
			}
			
			//if (($dboRate->CapUnits->Value == 0) && ($dboRate->CapCost->Value == 0))
			//{
			//	$strBasicDetailHtml .="No Cap";
			//	$strBasicDetailHtml .= "<br>\n";				
			//}

			if ($dboRate->CapLimit->Value != 0)
			{
				$strBasicDetailHtml .= "Cap Limit : ". $dboRate->CapLimit->AsValue();
				$strBasicDetailHtml .= "<br>\n";			
			}
			if ($dboRate->CapUsage->Value != 0)
			{
				$strBasicDetailHtml .= "Cap Usage : ". $dboRate->CapUsage->AsValue();
				$strBasicDetailHtml .= "<br>\n";			
			}
	
			if (($dboRate->ExsRatePerUnit->Value != 0) || ($dboRate->ExsMarkup->Value != 0) || ($dboRate->ExsPercentage->Value != 0))
			{
				$strBasicDetailHtml .= "Excess Units : ". $dboRate->ExsUnits->AsValue();
				$strBasicDetailHtml .= "<br>\n";			
		
				if ($dboRate->ExsRatePerUnit->Value != 0)
				{
					$strBasicDetailHtml .= "Excess Rate Per Unit : ". $dboRate->ExsRatePerUnit->AsValue();
					$strBasicDetailHtml .= "<br>\n";			
				}
				if ($dboRate->ExsMarkup->Value != 0)
				{		
					$strBasicDetailHtml .= "Excess Markup : ". $dboRate->ExsMarkup->AsValue();
					$strBasicDetailHtml .= "<br>\n";			
				}
				if ($dboRate->ExsPercentage->Value != 0)
				{		
					$strBasicDetailHtml .= "Excess Percentage : ". $dboRate->ExsPercentage->AsValue();
					$strBasicDetailHtml .= "<br>\n";			
				}

				$strBasicDetailHtml .= "Excess Flagfall : ". $dboRate->ExsFlagfall->AsValue();
				$strBasicDetailHtml .= "<p>\n";			
			}
			
			$strBasicDetailHtml .= "Pro Rate : ". $dboRate->Prorate->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Fleet Rate : ". $dboRate->Fleet->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Uncapped : ". $dboRate->Uncapped->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Archived : ". $dboRate->Archived->AsValue();
			$strBasicDetailHtml .= "<br>\n";			
			$strBasicDetailHtml .= "Pass Through : ". $dboRate->PassThrough->AsValue();

			$strBasicDetailHtml .= "		</font></td>\n";
			$strBasicDetailHtml .= "	</tr>\n";			
			$strBasicDetailHtml .= "</table>\n";
			$strBasicDetailHtml .= "</div>\n";
				
			Table()->RateTable->SetDetail($strBasicDetailHtml);												
		}
		
		Table()->RateTable->Render();
		echo "</div>\n";
		echo "<div class='SmallSeperator'></div>";
		echo "</div>\n";
	}
}

?>
