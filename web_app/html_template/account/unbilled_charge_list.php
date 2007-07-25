<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountUnbilledChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountUnbilledChargeList
 *
 * HTML Template object for the client app, List of all Unbilled charges for account
 *
 * HTML Template object for the client app, List of all Unbilled charges for account
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountUnbilledChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountUnbilledChargeList extends HtmlTemplate
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
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
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
		echo "<div class='WideContent'>\n";
				
		// User cannot delete adjustments
		Table()->AdjustmentTable->SetHeader("Date", "Code", "Description", "Nature", "Status", "Amount (inc GST)");
		Table()->AdjustmentTable->SetWidth("10%", "15%", "30%", "10%", "15%", "20%");
		Table()->AdjustmentTable->SetAlignment("left", "left", "left", "left", "right", "left");
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			Table()->AdjustmentTable->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Nature->AsValue(),
											$dboCharge->Status->AsCallback("GetConstantDescription", Array("ChargeStatus")),
											$dboCharge->Amount->AsCallback("AddGST"));
		}
		
		Table()->AdjustmentTable->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
