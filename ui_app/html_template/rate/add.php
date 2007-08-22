<?php
//----------------------------------------------------------------------------//
// HtmlTemplaterateadd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplaterateadd
 *
 * A specific HTML Template object
 *
 * An rate add HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplaterateadd
 * @extends	HtmlTemplate
 */
class HtmlTemplaterateadd extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		// define javascript to be triggered when the Cap and Excess radiobuttons change
		$strRateCapOnClick = 
		"switch (this.value)
		{
			case '". RATE_CAP_NO_CAP ."':
				// hide any details not required for a no cap
				document.getElementById('CapDetailDiv').style.display='none';
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;
			case '". RATE_CAP_CAP_UNITS ."':
				// show any details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				// check if the capusage is clicked if it is display the excessdetail div
				break;
			case '". RATE_CAP_CAP_COST ."':
				// show any details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				// check if the capusage is clicked if it is display the excessdetail div
				break;
			case '". RATE_CAP_NO_CAP_LIMITS ."':
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;	
			case '". RATE_CAP_CAP_LIMIT ."':
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;							
			case '". RATE_CAP_CAP_USAGE ."':
				// show any details required for a cap
				//document.getElementById('Rate.ExsChargeType').". RATE_CAP_EXS_CHARGE_TYPE .".checked=true;
				document.getElementById('ExcessDetailDiv').style.display='inline';
				break;
		}";
	
		$this->FormStart("AddRate", "Rate", "Add");
		
		// Load the RecordType record relating to this rate
		DBO()->RecordType->Load();
		
		DBO()->Rate->ServiceType->RenderHidden();
		DBO()->RecordType->Id->RenderHidden();
		
		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td width='98%'>".DBO()->Rate->Name->AsInput()."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->Description->AsInput()."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT)."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->RecordType->Name->AsOutput()."</td></tr>\n";
		echo "</table>\n";
				
    	echo "<div class='Seperator'></div>\n";

		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td>".DBO()->Rate->StartTime->AsInput()."</td></tr>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td>".DBO()->Rate->EndTime->AsInput()."</td></tr>\n";
		echo "</table>\n";

		echo "<div class='Seperator'></div>\n";

		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='12%'>&nbsp;</td><td>\n";
		
			echo "<table border=1 cellpadding=3 cellspacing=0>\n";
			echo "<tr><td><span class='DefaultOutputSpan'>MON</span></td><td><span class='DefaultOutputSpan'>TUE</span></td>";
			echo "<td><span class='DefaultOutputSpan'>WED</span></td><td><span class='DefaultOutputSpan'>THU</span></td><td>";
			echo "<span class='DefaultOutputSpan'>FRI</span></td><td><span class='DefaultOutputSpan'>SAT</span></td><td>";
			echo "<span class='DefaultOutputSpan'>SUN</span></td></tr>\n";
			
			echo "<tr><td><input type='checkbox' id='Rate.Monday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Tuesday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Wednesday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Thursday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Friday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Saturday' $strChecked $strDisabled></input>";
			echo "</td><td><input type='checkbox' id='Rate.Sunday' $strChecked $strDisabled></input>";
			echo "</td></tr>";
			echo "</table>\n";
					
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "</div>\n";	
	
		echo "<div class='Seperator'></div>\n";	
		
		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td width='40%'>".DBO()->Rate->StdUnits->AsInput()."</td><td width='58%'>&nbsp;</td></tr>\n";
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_RATE_PER_UNIT."' checked></td><td>".DBO()->Rate->StdRatePerUnit->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_MARKUP."'></td><td>".DBO()->Rate->StdMarkup->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_PERCENTAGE."'></td><td>".DBO()->Rate->StdPercentage->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->StdMinCharge->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->StdFlagFall->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		
		echo "<div class='Seperator'></div>\n";		

		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_NO_CAP."' checked onchange=\"$strRateCapOnClick\"></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Cap</span></td><td width='58%'>&nbsp;</td></tr>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_UNITS."' onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapUnits->AsInput()."</td><td width='58%'>&nbsp;</td></tr>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_COST."' onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapCost->AsInput()."</td><td width='58%'>&nbsp;</td></tr>\n";
		echo "</table>\n";
			
		if ((DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST)||(DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS))
		{	
			$mixCapStatus = DBO()->Rate->CapLimitting->Value;
			echo "<div id='CapDetailDiv' style='display:inline'>\n";		
		}
		else
		{
			$mixCapStatus = RATE_CAP_NO_CAP_LIMITS;
			echo "<div id='CapDetailDiv' style='display:none'>\n";
		}
			// cap usage and cap limit specific detail
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_NO_CAP_LIMITS."'". ($mixCapStatus == RATE_CAP_NO_CAP_LIMITS ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Cap Limits</span></td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_LIMIT."'". ($mixCapStatus == RATE_CAP_CAP_LIMIT ? "checked='checked'" : "-") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapLimit->AsInput()."</td></tr>\n";		
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_USAGE."'". ($mixCapStatus == RATE_CAP_CAP_USAGE ? "checked='checked'" : "-") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapUsage->AsInput()."</td></tr>\n";
				echo "</table>\n";		
			echo "</div>\n";	

			// excess rate and markup specific detail
			echo "<div id='ExcessDetailDiv' style='display:none'>\n";
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'>&nbsp;</td><td>".DBO()->Rate->ExsUnits->AsInput()."</td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_RATE_PER_UNIT."' checked></td><td>".DBO()->Rate->ExsRatePerUnit->AsInput()."</td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_MARKUP."'></td><td>".DBO()->Rate->ExsMarkup->AsInput()."</td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_PERCENTAGE."'></td><td>".DBO()->Rate->ExsPercentage->AsInput()."</td></tr>\n";
				echo "<tr><td width='2%'>&nbsp;</td><td>&nbsp;&nbsp;".DBO()->Rate->ExsFlagfall->AsInput()."</td></tr>\n";	
				echo "</table>\n";	
			echo "</div>\n";
		
		//echo "</div>\n";		
		echo "</div>\n";

		echo "<div class='Seperator'></div>\n";	

		echo "<div class='NarrowContent'>\n";
			echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
			echo "<tr><td>".DBO()->Rate->Prorate->AsInput()."</td></tr>\n";
			echo "<tr><td>".DBO()->Rate->Fleet->AsInput()."</td></tr>\n";
			echo "<tr><td>".DBO()->Rate->Uncapped->AsInput()."</td></tr>\n";
			echo "</table>\n";	
		echo "</div>\n";	
		
		echo "<div class='Right'>\n";
			$this->AjaxSubmit("Add");
		echo "</div>\n";
	
		$this->FormEnd();
	}
}

?>
