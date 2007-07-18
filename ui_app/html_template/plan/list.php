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
 * @class	HtmlTemplateAccountDetails
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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
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
		echo "<h2 class='Plan'>Available Plans</h2>\n";
		echo "<br>";

		// Declare the start of the form
				
		$this->FormStart('RatePlanFilter', 'Plan', 'View', $_POST);
		DBO()->RatePlan->Name->RenderInput();
		// Payment Type combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Service Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='RatePlan.ServiceType' name='RatePlan.ServiceType'>\n";
		echo "<option id='RatePlan.All' value='All' $strSelected>All</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{
			$strDescription = $arrServiceType['Description'];

			/*// Check if this Payment Type was the last one selected
			if ($intPaymentType == DBO()->Payment->PaymentType->Value)
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}*/
			
			echo "         <option id='RatePlan.$intServiceType' value='$intServiceType' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
			echo "<div class='Seperator'></div>\n";

		$this->Submit("Filter");
		$this->FormEnd();
		echo "<div class='Seperator'></div>\n";
		
		Table()->PlanTable->SetHeader("Name", "Service Type", "&nbsp;");
		Table()->PlanTable->SetWidth("57%", "37%", "6%");
		Table()->PlanTable->SetAlignment("Left", "Left", "Center");

		foreach (DBL()->RatePlan as $dboPlan)
		{
			//build Rates Summary link
			$strRatesHref = Href()->RatesList($dboPlan->Id->Value);
			$strRatesLabel = "<span class='DefaultOutputSpan Default'><a href='$strRatesHref'><img src='img/template/charge.png' title='Rates Summary' /></a></span>";
			
			Table()->PlanTable->AddRow($dboPlan->Name->AsValue(), GetConstantDescription($dboPlan->ServiceType->Value, 'ServiceType'), $strRatesLabel);									
		
		
			// Set the drop down detail
			$strDetailHtml = "<div class='VixenTableDetail'>\n";
			$strDetailHtml .= $dboPlan->Description->AsOutput();
			$strDetailHtml .= $dboPlan->ServiceType->AsCallBack('GetConstantDescription', Array('ServiceType'), RENDER_OUTPUT);
			$strDetailHtml .= $dboPlan->ChargeCap->AsOutput();
			$strDetailHtml .= $dboPlan->UsageCap->AsOutput();
			$strDetailHtml .= $dboPlan->MinMonthly->AsOutput();
			$strDetailHtml .= $dboPlan->Archived->AsOutput();
			$strDetailHtml .= $dboPlan->Shared->AsOutput();
			$strDetailHtml .= "</div>\n";
			
			Table()->PlanTable->SetDetail($strDetailHtml);
		}
		
		Table()->PlanTable->RowHighlighting = TRUE;
		Table()->PlanTable->Render();
		// Render the properties that can be changed
		/*DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		
		// Render the submit button
		echo "<div class='Right'>\n";
		//echo "   <input type='submit' class='input-submit' value='Apply Changes' />\n";
		//$this->AjaxSubmit("Apply Changes");
		$this->Submit("Apply Changes");
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		// Declare the end of the form
		$this->FormEnd();*/
	}


}

?>
