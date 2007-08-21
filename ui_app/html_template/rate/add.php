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
		// define javascript to be triggered when the Cap and Excess radiobuttons changed
		$strRateCapOnClick = 
		"switch (this.value)
		{
			case '". RATE_CAP_NO_CAP ."':
				// hide any details not required for a no cap
				document.getElementById('CapDetailDiv').style.display='none';
				//document.getElementById('ExcessDetailDiv').style.display='none';
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
				document.getElementById('ExcessDetailDiv').style.display='inline';
				break;
		}";
	
		echo "<div class='NarrowContent'>\n";
		$this->FormStart("AddRate", "Rate", "Add");
		
		// Load the RecordType record relating to this rate
		DBO()->RecordType->Load();
		
		DBO()->Rate->ServiceType->RenderHidden();
		DBO()->RecordType->Id->RenderHidden();
		
		DBO()->Rate->Name->RenderInput();
		DBO()->Rate->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);
		DBO()->RecordType->Name->RenderOutput();
		
		echo "<div class='Seperator'></div>\n";
		
		DBO()->Rate->StartTime->RenderInput();
		DBO()->Rate->EndTime->RenderInput();

		echo "<div class='Seperator'></div>\n";
		
		echo "<table border=1 cellpadding=3 cellspacing=0>\n";
		echo "	<tr>\n";
		echo "		<td>MON";
		echo "		</td>\n";
		echo "		<td>TUE";
		echo "		</td>\n";
		echo "		<td>WED";
		echo "		</td>\n";
		echo "		<td>THU";
		echo "		</td>\n";
		echo "		<td>FRI";
		echo "		</td>\n";
		echo "		<td>SAT";
		echo "		</td>\n";
		echo "		<td>SUN";
		echo "		</td>";
		echo "	</tr>\n";
		echo "	<tr>";
		echo "		<td>";
		echo "			<input type='checkbox' id='Rate.Monday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Tuesday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Wednesday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Thursday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Friday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Saturday' $strChecked $strDisabled></input>";
		echo "		</td><td>";
		echo "			<input type='checkbox' id='Rate.Sunday' $strChecked $strDisabled></input>";
		echo "		</td>";
		echo "	</tr>";
		echo "</table>";
		
		echo "<div class='Seperator'></div>\n";		
		DBO()->Rate->StdUnits->RenderInput();	

		echo "<input type='radio' name='Rate.ChargeType' value='Rate.StdRatePerUnit' checked>";
		DBO()->Rate->StdRatePerUnit->RenderInput();
		echo "<input type='radio' name='Rate.ChargeType' value='Rate.StdMarkup'>";
		DBO()->Rate->StdMarkup->RenderInput();
		echo "<input type='radio' name='Rate.ChargeType' value='Rate.StdPercentage'>";
		DBO()->Rate->StdPercentage->RenderInput();
		
		echo "<div class='Seperator'></div>\n";	
		DBO()->Rate->StdMinCharge->RenderInput();
		DBO()->Rate->StdFlagFall->RenderInput();
		
		echo "<div class='Seperator'></div>\n";		
	
		echo "<input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_NO_CAP."' checked onchange=\"$strRateCapOnClick\">No Cap<br>";
		echo "<input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_UNITS."' onchange=\"$strRateCapOnClick\">";		
		DBO()->Rate->CapUnits->RenderInput();
		echo "<input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_COST."' onchange=\"$strRateCapOnClick\">";
		DBO()->Rate->CapCost->RenderInput();

			// cap usage and cap limit specific detail
			echo "<div id='CapDetailDiv' style='display:none'>\n";
			echo "<div class='Seperator'></div>\n";		
	
			echo "<input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_NO_CAP_LIMITS."' checked onchange=\"$strRateCapOnClick\">No Cap Limits<br>";
			echo "<input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_LIMIT."' onchange=\"$strRateCapOnClick\">";		
			DBO()->Rate->CapLimit->RenderInput();
			echo "<input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_USAGE."' onchange=\"$strRateCapOnClick\">";
			DBO()->Rate->CapUsage->RenderInput();		
			echo "</div>\n";

			// excess rate and markup specific detail
			echo "<div id='ExcessDetailDiv' style='display:none'>\n";
			echo "<div class='Seperator'></div>\n";		
	
			DBO()->Rate->ExsUnits->RenderInput();
			echo "<input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_RATE_PER_UNIT."' checked>No Cap Limits<br>";
			DBO()->Rate->ExsRatePerUnit->RenderInput();
			echo "<input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_MARKUP."'>";		
			DBO()->Rate->ExsMarkup->RenderInput();
			echo "<input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_PERCENTAGE."'>";
			DBO()->Rate->ExsPercentage->RenderInput();	
			DBO()->Rate->ExsFlagfall->RenderInput();	
			echo "</div>\n";

		echo "<div class='Seperator'></div>\n";	

		DBO()->Rate->Prorate->RenderInput();
		DBO()->Rate->Fleet->RenderInput();
		DBO()->Rate->Uncapped->RenderInput();		
		
		echo "</div>\n";
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Add");
		echo "</div>\n";
		
		$this->FormEnd();
	}
}

?>
